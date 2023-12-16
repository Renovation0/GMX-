<?php


namespace app\admin\model;

use module\Redis;
use think\Db;
use think\Model;

class OrderCoinList extends Model
{
    public function getLists($where, $pageSize, $allParams,$order){
        $list = $this->alias('order')
            ->where($where)
            ->order($order)
            ->paginate($pageSize, false, $allParams);
        $buy_id = array_column($list->items(), 'buy_id');
        $sell_id = array_column($list->items(), 'sell_id');
        //合并
        $user_list = array_merge($buy_id, $sell_id);
        //去空
        $user_list = array_filter($user_list);
        //去重
        $user_list = array_unique($user_list);
        $user_ids = implode(",", $user_list);
        $members = array();
        if ($user_ids) {
            $user = DB::name('member_list')
                ->field('id,tel')
                ->whereIn('id', $user_ids)
                ->select();
            foreach ($user as $key => $val) {
                $members[$val['id']] = $val;
            }

        }
        $resut = array(
            'list' => $list,
            'member' => $members,
        );
        return $resut;
    }
    //交易大厅数据balance
    public function getHall()
    {
        /*60条记录*/
        $SystemModule = new SystemModule();
        //5.9.单个交易数量的当天成交数量，超过成交数量，对应数量的订单隐藏
        $config = $SystemModule->readConfig(['CS_NUM', 'CS_NUM_ALL', 'DAYS_SELL'], 2);
        $num = explode(',', $config[0]);
        $num_all = explode(',', $config[1]);
        if (count($num) != count($num_all)) { // 参数配置错误
            return false;
        }
        $transcend = [];
        $lc_time = strtotime(date('Y-m-d'));
        for ($i = 0; $i < count($num); $i++) {
            if (Db::name('order_list')->where('num', $num[$i])->where('e_time', '>', $lc_time)->whereIn('status', [5, 13])->sum('num') < $num_all[$i]) {
                $transcend[] = $num[$i];//未超过成交数量的单价数组
            }
        }
        $arr_tran = implode(',',$transcend);
        $lists = Db::name('order_list')->alias('o')
            ->leftJoin('member_list m', 'm.id = o.buy_id')
            ->leftJoin('member_list l', 'l.id = o.sell_id')
            ->leftJoin('member_level le', 'le.id = m.level')
            ->order('m.first_blood, o.u_time desc')
            ->where('o.display = 0 and o.status = 0 and m.status = 1 and o.num in ('.$arr_tran.') and o.u_time <='.time().' and m.level = 0')
            ->whereOr('o.display = 0 and o.status = 0 and m.status = 1 and o.num in ('.$arr_tran.') and o.u_time <='.time().' and le.order_hide = 2 and m.level > 0')
            //->order('o.num')// 排序
            //->order('m.first_blood, o.num')
//            ->where('o.display', 0)// 未被后台隐藏的
//            ->where('o.status', 0)// 状态为 0挂单中 1交易中 3已付款的
//            ->where('lo.order_hide', 2)// 等级配置开启显示的
//            ->where('m.status', 1)// 用户未被冻结的
//            ->where('o.num', 'in', $transcend)// 挂单数量符合条件的
//            ->where('o.u_time', '<=', time())// 排队符合条件的
            ->group('o.buy_id')// 一个用户只显示一个订单
            ->field('o.*,m.level,m.real_name_time,m.first_blood,m.tel as b_tel,l.tel as s_tel')// 需查的数据
            ->limit(60)// 查询数量（60 + 单个用户允许挂单数） + $config[2]
            ->select();
        if (!empty($lists)){
            foreach ($lists as $k => $v){
                if ($v['level'] == 0 && $v['real_name_time'] == 0){//普通会员
                    $lists[$k]['level'] = 97;
                }
                if ($v['level'] == 0 && $v['real_name_time'] != 0){//普通会员
                    $lists[$k]['level'] = 98;
                }
                if ($v['level'] == 0 && $v['first_blood'] == 2){//有效会员
                    $lists[$k]['level'] = 99;
                }
            }
            //获取隐藏订单的等级
            $order_hide = Db::name('member_level')->where('order_hide = 1')->field('id')->select();
            if (!empty($order_hide)){
                foreach ($lists as $kk=>$vv){
                    if (in_array($v['level'],$order_hide)){
                        unset($lists[$k]);
                    }
                }
            }
            //获取已显示的订单id
            $lists_order_id = array_column($lists,'id');
            $count = count($lists_order_id);//已存在的订单数
            $count = 60 - $count;//所需要的订单数
            $not_in_order = implode(',',$lists_order_id);
            if ($count > 0){
                $list2 = Db::name('order_list')->alias('o')
                    ->leftJoin('member_list m', 'm.id = o.buy_id')
                    ->leftJoin('member_list l', 'l.id = o.sell_id')
                    ->leftJoin('member_level le', 'le.id = m.level')
                    ->order('o.sort_time asc,o.u_time desc')
                    ->where('o.status', 0)// 状态为 0挂单中 1交易中 3已付款的
                    ->where('m.status', 1)// 用户未被冻结的
                    ->where('o.display',0)//隐藏订单的放在最后
                    ->where('le.order_hide = 2')// 等级配置开启显示的
                    ->where('o.u_time', '<=', time())// 排队符合条件的
                    ->whereNotIn('o.id',$not_in_order)
                    ->field('o.*,m.first_blood,m.tel as b_tel,l.tel as s_tel')// 需查的数据
                    ->limit($count)// 查询数量（60 + 单个用户允许挂单数）
                    ->select();
                $lists = array_merge($lists,$list2);
            }

            //如果还没有满足60条
            if (count($lists) < 60){
                $lists_order_id = array_column($lists, 'id');
                $count = count($lists_order_id);//已存在的订单数
                $count = 60 - $count;//所需要的订单数
                $not_in_order = implode(',', $lists_order_id);
                $list2 = Db::name('order_list')->alias('o')
                    ->leftJoin('member_list m', 'm.id = o.buy_id')
                    ->leftJoin('member_list l', 'l.id = o.sell_id')
                    ->order('o.u_time desc')
                    ->where('o.status', 0)// 状态为 0挂单中 1交易中 3已付款的
                    ->where('m.status', 1)// 用户未被冻结的
                    ->where('o.u_time', '<=', time())// 排队符合条件的
                    ->whereNotIn('o.id', $not_in_order)
                    ->field('o.*,m.first_blood,m.tel as b_tel,l.tel as s_tel')// 需查的数据
                    ->limit($count)// 查询数量（60 + 单个用户允许挂单数）
                    ->select();
                if (!empty($list2)){
                    $lists = array_merge($lists, $list2);
                }
            }
        }
        if (!empty($lists)) {
            $res = [];
            foreach ($lists as $k => $v) {
                $v['price'] = floatval($v['price']);
                $v['num'] = floatval($v['num']);
                $v['bo_money'] = floatval($v['bo_money']);
                $v['all_money'] = floatval($v['bo_money']) * 7;
                $v['rechange'] = floatval($v['rechange']);
                $v['pounage'] = floatval($v['rechange']) / floatval($v['num']) * 100;
                $v['rechange'] = floatval($v['rechange']);
                $res[] = $v;
            }
            for ($i=0;$i<count($res);$i++){
                $res[$i]['new'] = $res[$i]['first_blood'] == 1 ? 1 : 0;
                $res[$i]['xl_id'] = $i++;
            }
            return $res;
        } else {
            return [];
        }
    }

    //获取订单信息
    public function getOrder($where,$filed = '*'){
        return $this->where($where)->field($filed)->find();
    }

    //改变订单状态
    public function updataOrder($where,$data){
        return $this->where($where)->update($data);
    }

    public function orderCoinCensus(){
        $Redis = new Redis();
        $redis = $Redis->redis();
        //交易累计
        $sum_num = $this->where('status in (5,13)')->sum('num');
        $redis->hSet('census','zb_sum_num',$sum_num);
        
        $beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $beginYesterday = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
        $endYesterday = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
        //昨日交易量
        $yesterday_num = $this->where("(status = 5 || status = 13) AND e_time > ".$beginYesterday." AND e_time < ".$endYesterday)->sum('num');
        $redis->hSet('census','zb_yesterday_num',$yesterday_num);
        //今日交易量
        $today_num = $this->where("(status = 5 || status = 13) AND e_time > ".$beginToday." AND e_time < ".$endToday)->sum('num');
        $redis->hSet('census','zb_today_num',$today_num);  
        
        //挂单中
        $order = $this->where('status = 0')->sum('num');
        $redis->hSet('census','zb_in_num',$order);
        //交易进行中
        $order = $this->where('status in (1,3,11)')->sum('num');
        $redis->hSet('census','zb_num',$order);
        //已完成金额
        $order = $this->where('status in (5,13)')->sum('bo_money');
        $redis->hSet('census','zb_bo_money',$order);
        //已完成手续费
        $order = $this->where('status in (5,13)')->sum('rechange');
        $redis->hSet('census','zb_rechange',$order);
        $redis->hSet('census','coin_order_refresh_time',time());
        return true;
    }

    //子币订单列表
    public function coin_order_log($where, $pageSize, $allParams, $orders)
    {
        // 获取订单列表
        $OrderList = new OrderCoinLog();
        return $OrderList->getLists($where, $pageSize, $allParams, $orders);
    }

    //取消子币订单
    public function coin_forceCancel($order_id)
    {
        if ($order_id) {
            $MCommon = new MCommon();
            $order = $MCommon->getField('order_coin_list', 'id=' . $order_id);
            if ($order && $order['status'] == 0) {
                //改变订单状态
                try {
                    Db::startTrans();
                    $data = ['status' => 15, 'e_time' => time()];
                    $condition = 'id=' . $order_id;
                    $orderupdata = $MCommon->getUpdata('order_coin_list', $condition, $data);
                    if (!$orderupdata) {
                        throw new \Exception("改变订单状态失败");
                    }
                    //增加订单记录
                    $data2 = [
                        'u_id' => $order['buy_id'],
                        'order_id' => $order['id'],
                        'message' => '强制取消订单,编号' . $order['orderNo'],
                        'time' => time()
                    ];
                    $logadd = $MCommon->getInsert('order_coin_log', $data2);
                    if (!$logadd) {
                        throw new \Exception("增加订单记录失败");
                    }
                    Db::commit();
                    return ['code' => 1, 'msg' => '强制取消成功'];
                } catch (\Exception $e) {
                    Db::rollback();
                    return ['code' => 2, 'msg' => '强制取消失败'];
                }
            } else {
                return ['code' => 2, 'msg' => '参数错误'];
            }
        } else {
            return ['code' => 2, 'msg' => '参数错误'];
        }
    }

    //coin同意申诉
    public function coin_agreeAppeal($order_id)
    {
        $Redis = new Redis();
        $redis = $Redis->redis();
        if ($redis->get('coin_agreeAppeal_' . $order_id)) {
            return json(['code' => 2, 'msg' => '正在处理,稍后再来']);
        }
        $redis->set('coin_agreeAppeal_' . $order_id, 999, 60);
        if ($order_id) {
            $MCommon = new MCommon();
            $condition = 'id=' . $order_id;
            $order = $MCommon->getField('order_coin_list', $condition);
            if ($order && $order['status'] == 11) {
                //改变订单状态
                try {
                    Db::startTrans();
                    $data = ['status' => 9, 'e_time' => time()];
                    //更改订单状态
                    $res1 = $MCommon->getUpdata('order_coin_list', $condition, $data);
                    if (!$res1) {
                        throw new \Exception("更改订单状态失败");
                    }
                    //余额返还sell_id
                    $condition_sell = 'id=' . $order['sell_id'];
                    $coin = $MCommon->getValue('member_list', $condition_sell, 'coin');
                    $res2 = $MCommon->getIncrease('member_list', $condition_sell, 'coin', ($order['num'] + $order['rechange']));//coin
                    if (!$res2) {
                        throw new \Exception("coin返还至卖家失败");
                    }
                    //增加余额记录
                    $data3 = [
                        'u_id' => $order['sell_id'],
                        'o_id' => $order['id'],
                        'bo_money' => $order['num'] + $order['rechange'],
                        'former_money' => $coin,
                        'type' => 64,
                        'message' => '申诉成功,子币及手续费返还',
                        'bo_time' => time(),
                        'status' => 1
                    ];
                    $res3 = $MCommon->getInsert('member_coin_log', $data3);
                    if (!$res3) {

                        throw new \Exception("coin及手续费返还失败");
                    }
                    //增加订单记录
                    $data4 = [
                        'u_id' => $order['sell_id'],
                        'order_id' => $order['id'],
                        'message' => '申诉:订单失效,编号' . $order['orderNo'],
                        'time' => time()
                    ];
                    $data5 = [
                        'u_id' => $order['buy_id'],
                        'order_id' => $order['id'],
                        'message' => '申诉:订单失效,账户冻结,编号' . $order['orderNo'],
                        'time' => time()
                    ];
                    $res4 = $MCommon->getInsert('order_coin_log', $data4);
                    $res5 = $MCommon->getInsert('order_coin_log', $data5);
                    if (!$res4) {
                        throw new \Exception("卖家订单日志添加失败");
                    }
                    if (!$res5) {
                        throw new \Exception("买家订单日志添加失败");
                    }
                    //冻结账户
                    $data6 = ['status' => 2];
                    $condition_buy = 'id=' . $order['buy_id'];
                    $buy_user = $MCommon->getValue('member_list', $condition_buy, 'status');
                    if ($buy_user == 1) {
                        $res6 = $MCommon->getUpdata('member_list', $condition_buy, $data6);
                        if (!$res6) {
                            throw new \Exception("冻结买家账户失败");
                        }
                    }
                    Db::commit();
                    $redis->delete('coin_agreeAppeal_' . $order_id);
                    return ['code' => 1, 'msg' => '同意申诉成功,订单失效'];
                } catch (\Exception $e) {
                    Db::rollback();
                    $redis->delete('coin_agreeAppeal_' . $order_id);
                    return ['code' => 2, 'msg' => '同意申诉失败'];
                }
            } else {
                return ['code' => 2, 'msg' => '参数错误'];
            }
        } else {
            return ['code' => 2, 'msg' => '参数错误'];
        }
    }

    //拒绝申诉，冻结卖家
    public function coin_refuseAppeal($order_id)
    {
        $MCommon = new MCommon();
        $order = $MCommon->getField('order_coin_list', 'id=' . $order_id);
        if ($order && $order['status'] == 11) {
            try {
                Db::startTrans();
                $data1 = ['status' => 13, 'e_time' => time()];
                //更改订单状态
                $res1 = $MCommon->getUpdata('order_coin_list', 'id=' . $order_id, $data1);
                if (!$res1) {
                    throw new \Exception("更改订单状态失败");
                }
                //冻结账户
                $data8 = ['status' => 2];
                $condition_sell = 'id=' . $order['sell_id'];
                $buy_user = $MCommon->getValue('member_list', $condition_sell, 'status');
                if ($buy_user == 1) {
                    $res8 = $MCommon->getUpdata('member_list', $condition_sell, $data8);
                    if (!$res8) {
                        throw new \Exception("冻结卖家账户失败");
                    }
                }
                //增加订单记录
                $data2 = [
                    'u_id' => $order['sell_id'],
                    'order_id' => $order['id'],
                    'message' => '申诉:订单完成,编号' . $order['orderNo'],
                    'time' => time()
                ];
                $data3 = [
                    'u_id' => $order['buy_id'],
                    'order_id' => $order['id'],
                    'message' => '申诉:订单完成,编号' . $order['orderNo'],
                    'time' => time()
                ];
                $res2 = $MCommon->getInsert('order_coin_log', $data2);
                $res3 = $MCommon->getInsert('order_coin_log', $data3);
                if (!$res2) {
                    throw new \Exception("卖家订单日志添加失败");
                }
                if (!$res3) {
                    throw new \Exception("买家订单日志添加失败");
                }
                $condition_buy = 'id=' . $order['buy_id'];
                $user = $MCommon->getField('member_list', $condition_buy, 'coin,level');
                $res4 = $MCommon->getIncrease('member_list', $condition_buy, 'coin', $order['num']);//买家加钱
                if (!$res4) {
                    throw new \Exception("买家coin增加失败");
                }
                $res5 = $MCommon->getIncrease('member_list', $condition_buy, 'coin_num', $order['num']);//买家累计购买量
                if (!$res5) {
                    throw new \Exception("买家增加累计购买量失败");
                }
                //增加coin记录
                $data6 = [
                    'u_id' => $order['buy_id'],
                    'o_id' => $order['id'],
                    'bo_money' => $order['num'],
                    'former_money' => $user['coin'],
                    'type' => 502,
                    'message' => '购买订单完成',
                    'bo_time' => time()
                ];
                $res6 = $MCommon->getInsert('member_coin_log', $data6);//糖果钱包记录
                if (!$res6) {
                    throw new \Exception("买家子币记录添加失败");
                }
                $this->agent($order['buy_id'],$order['id'],$order['num']);
                Db::commit();
                return ['code' => 1, 'msg' => '拒绝申诉成功,交易完成'];
            } catch (\Exception $e) {
                Db::rollback();
                return ['code' => 2, 'msg' => '拒绝申诉失败'];
            }
        } else {
            return ['code' => 2, 'msg' => '订单不存在'];
        }
    }

    //代理奖励
    public function agent($uid, $order_id,$num)
    {
        $CommonModule = new MCommon();
        $f_uid = $CommonModule->getSelect('member_team', 'u_id =' . $uid);//我的上级
        if (!empty($f_uid)){
            $arr = [];
            for ($i=0;$i<count($f_uid);$i++){
                $arr[] = $f_uid[$i]['f_uid'];
            }
            $f_uid_arr = count($arr) > 0 ? implode(',', $arr) : $arr[0];
            $agent = Db::name('member_list')->where('agent in (1,2)')->where('id in (' . $f_uid_arr . ')')->order('id desc')->field('id,agent,status,coin,agent_profit')->order('id desc')->limit(2)->select();
            $sys = new SystemModule();
            $pounage = $sys->readConfig('AGENT_REWARD');//代理奖励
            if (!empty($agent)) {//如果上级存在代理
                if (count($agent) == 1) {//存在1个且为1级代理
                    if ($agent[0]['agent'] == 1){
                        $agent_coin = $CommonModule->getValue('member_list','id='.$agent[0]['id'],'coin');
                        if (!$CommonModule->getIncrease('member_list','id='.$agent[0]['id'],'coin',$num*$pounage/100)){
                            return ['code' => 2, 'msg' => '1级代理奖励失败'];
                        }
                        $data = [
                            'u_id' => $agent[0]['id'],
                            'o_id' => $order_id,
                            'bo_money' => $num*$pounage/100,
                            'former_money' => $agent_coin,
                            'type' => 512,
                            'message' => '一级代理获得买币分红',
                            'bo_time' => time()
                        ];
                        $res = $CommonModule->getInsert('member_coin_log',$data);
                        if (!$res){
                            return ['code' => 2, 'msg' => '1级代理奖励记录添加失败'];
                        }
                    }
                }
                if (count($agent) >= 2) {
//                    echo '<pre>';
//                    var_dump($agent);die;25 26
                    if ($agent[0]['agent'] == 1){
                        $agent_coin = $CommonModule->getValue('member_list','id='.$agent[1]['id'],'coin');
                        if (!$CommonModule->getIncrease('member_list','id='.$agent[1]['id'],'coin',$num*$pounage/100)){
                            return ['code' => 2, 'msg' => '1级代理奖励失败'];
                        }
                        $data = [
                            'u_id' => $agent[1]['id'],
                            'o_id' => $order_id,
                            'bo_money' => $num*$pounage/100,
                            'former_money' => $agent_coin,
                            'type' => 512,
                            'message' => '一级代理获得买币分红',
                            'bo_time' => time()
                        ];
                        $res = $CommonModule->getInsert('member_coin_log',$data);
                        if (!$res){
                            return ['code' => 2, 'msg' => '1级代理奖励记录添加失败'];
                        }

                    }elseif ($agent[0]['agent'] == 2 && $agent[1]['agent'] == 1){//0上级1
//                        echo '<pre>';
//                        var_dump($agent);die;
                        //1 1级代理
                        $agent_coin = $CommonModule->getValue('member_list','id='.$agent[1]['id'],'coin');
                        if (!$CommonModule->getIncrease('member_list','id='.$agent[1]['id'],'coin',$num*$pounage*(1-$agent[0]['agent_profit']/100)/100)){
                            return ['code' => 2, 'msg' => '1级代理奖励失败'];
                        }
                        $data = [
                            'u_id' => $agent[1]['id'],
                            'o_id' => $order_id,
                            'bo_money' => $num*$pounage*(1-$agent[0]['agent_profit']/100)/100,
                            'former_money' => $agent_coin,
                            'type' => 512,
                            'message' => '一级代理获得买币分红',
                            'bo_time' => time()
                        ];
                        $res = $CommonModule->getInsert('member_coin_log',$data);
                        if (!$res){
                            return ['code' => 2, 'msg' => '1级代理奖励记录添加失败'];
                        }
                        //0 2级代理
                        $agent_coin = $CommonModule->getValue('member_list','id='.$agent[0]['id'],'coin');
                        if (!$CommonModule->getIncrease('member_list','id='.$agent[0]['id'],'coin',$num*$pounage*($agent[0]['agent_profit']/100)/100)){
                            return ['code' => 2, 'msg' => '二级代理奖励失败'];
                        }
                        $data = [
                            'u_id' => $agent[0]['id'],
                            'o_id' => $order_id,
                            'bo_money' => $num*$pounage*($agent[0]['agent_profit']/100)/100,
                            'former_money' => $agent_coin,
                            'type' => 513,
                            'message' => '二级代理获得买币分红',
                            'bo_time' => time()
                        ];
                        $res = $CommonModule->getInsert('member_coin_log',$data);
                        if (!$res){
                            return ['code' => 2, 'msg' => '2级代理奖励记录添加失败'];
                        }
                    }
                }
            }
        }
    }
}