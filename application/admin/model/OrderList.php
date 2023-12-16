<?php


namespace app\admin\model;


use module\Redis;
use think\Db;
use think\Model;

class OrderList extends MCommon
{   
    public $table = "zm_order_list";
    
    public function getLists($where, $pageSize, $allParams,$orders){
        $list = $this->alias('order')
            ->where($where)
            ->order($orders)
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

    //获取订单信息
    public function getOrder($where,$filed = '*'){
        return $this->where($where)->field($filed)->find();
    }

    //改变订单状态
    public function updataOrder($where,$data){
        return $this->where($where)->update($data);
    }

    public function orderCensus(){
        $Redis = new Redis();
        $redis = $Redis->redis();
        //交易累计
        $sum_num = $this->where('status in (5,13)')->sum('num');
        $redis->hSet('census','sum_num',$sum_num);
        
        $beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $beginYesterday = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
        $endYesterday = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
        //昨日交易量
        $yesterday_num = $this->where("type = 1 AND (status = 5 || status = 13) AND e_time > ".$beginYesterday." AND e_time < ".$endYesterday)->sum('num');
        $redis->hSet('census','yesterday_num',$yesterday_num);
        //今日交易量
        $today_num = $this->where("type = 1 AND (status = 5 || status = 13) AND e_time > ".$beginToday." AND e_time < ".$endToday)->sum('num');
        $redis->hSet('census','today_num',$today_num);        
        
        //挂单中
        $order = $this->where('status = 0')->sum('num');
        $redis->hSet('census','in_num',$order);
        //交易进行中
        $order = $this->where('status in (1,3,11)')->sum('num');
        $redis->hSet('census','num',$order);
        //已完成金额
        $order = $this->where('status in (5,13)')->sum('bo_money');
        $redis->hSet('census','bo_money',$order);
        //已完成手续费
        $order = $this->where('status in (5,13)')->sum('rechange');
        $redis->hSet('census','rechange',$order);
        $redis->hSet('census','order_refresh_time',time());
        return true;
    }

    //余额订单列表
    public function balance_order_log($where, $pageSize, $allParams, $orders)
    {
        // 获取订单列表
        $OrderList = new OrderLog();
        return $OrderList->getLists($where, $pageSize, $allParams, $orders);
    }

    //取消余额订单
    public function balance_forceCancel($order_id)
    {
        if ($order_id) {
            $MCommon = new MCommon();
            $order = $MCommon->getField('order_list', 'id=' . $order_id);
            if ($order && $order['status'] == 0) {
                //改变订单状态
                try {
                    Db::startTrans();
                    $data = ['status' => 15, 'e_time' => time()];
                    $condition = 'id=' . $order_id;
                    $orderupdata = $MCommon->getUpdata('order_list', $condition, $data);
                    if (!$orderupdata) {
                        throw new \Exception("改变订单状态失败");
                    }
                    //修改排序
                    if ($MCommon->getCounts('order_list','status = 0 and u_time <'.time().' and buy_id='.$order['buy_id']) > 0){//如果还存在其他的订单
                        if (!$MCommon->getUpdata('order_list','status = 0 and u_time <'.time().' and buy_id='.$order['buy_id'],['sort_time' => time()])){
                            $message = '修改排序状态失败';
                            throw new \Exception($message);
                        }
                    }
                    //增加订单记录
                    $data2 = [
                        'u_id' => $order['buy_id'],
                        'order_id' => $order['id'],
                        'message' => '强制取消订单,编号' . $order['orderNo'],
                        'time' => time()
                    ];
                    $logadd = $MCommon->getInsert('order_log', $data2);
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


    //balance同意申诉
    public function balance_agreeAppeal($order_id)
    {
        $Redis = new Redis();
        $redis = $Redis->redis();
        if ($redis->get('balance_agreeAppeal_' . $order_id)) {
            return json(['code' => 2, 'msg' => '正在处理,稍后再来']);
        }
        $redis->set('balance_agreeAppeal_' . $order_id, 999, 60);
        if ($order_id) {
            $MCommon = new MCommon();
            $condition = 'id=' . $order_id;
            $order = $MCommon->getField('order_list', $condition);
            if ($order && $order['status'] == 11) {
                //改变订单状态
                try {
                    Db::startTrans();
                    $data = ['status' => 9, 'e_time' => time()];
                    //更改订单状态
                    $res1 = $MCommon->getUpdata('order_list', $condition, $data);
                    if (!$res1) {
                        throw new \Exception("更改订单状态失败");
                    }
                    //余额返还sell_id
                    $condition_sell = 'id=' . $order['sell_id'];
                    $balance = $MCommon->getValue('member_list', $condition_sell, 'balance');
                    $sell_limit = $MCommon->getValue('member_list', $condition_sell, 'sell_limit');
                    $res2 = $MCommon->getIncrease('member_list', $condition_sell, 'balance', ($order['num'] + $order['rechange']));//可售余额
                    $res3 = $MCommon->getIncrease('member_list', $condition_sell, 'sell_limit', $order['num']);//可售额度
                    if (!$res2) {
                        throw new \Exception("可售余额返还失败");
                    }
                    if (!$res3) {
                        throw new \Exception("可售额度返还失败");
                    }
                    //增加余额记录
                    $data4 = [
                        'u_id' => $order['sell_id'],
                        'o_id' => $order['id'],
                        'bo_money' => $order['num'] + $order['rechange'],
                        'former_money' => $balance,
                        'type' => 105,
                        'message' => '申诉成功,可售余额返还',
                        'bo_time' => time(),
                        'status' => 1
                    ];
                    $data5 = [
                        'u_id' => $order['sell_id'],
                        'o_id' => $order['id'],
                        'bo_money' => $order['num'],
                        'former_money' => $sell_limit,
                        'type' => 404,
                        'message' => '申诉成功,可售额度返还',
                        'bo_time' => time(),
                        'status' => 1
                    ];
                    $res4 = $MCommon->getInsert('member_balance_log', $data4);
                    $res5 = $MCommon->getInsert('member_sell_limit_log', $data5);
                    if (!$res4) {
                        throw new \Exception("增加可售余额记录失败");
                    }
                    if (!$res5) {
                        throw new \Exception("增加可售额度记录失败");
                    }
                    //增加订单记录
                    $data6 = [
                        'u_id' => $order['sell_id'],
                        'order_id' => $order['id'],
                        'message' => '申诉:订单失效,编号' . $order['orderNo'],
                        'time' => time()
                    ];
                    $data7 = [
                        'u_id' => $order['buy_id'],
                        'order_id' => $order['id'],
                        'message' => '申诉:订单失效,账户冻结,编号' . $order['orderNo'],
                        'time' => time()
                    ];
                    $res6 = $MCommon->getInsert('order_log', $data6);
                    $res7 = $MCommon->getInsert('order_log', $data7);
                    if (!$res6) {
                        throw new \Exception("卖家订单日志添加失败");
                    }
                    if (!$res7) {
                        throw new \Exception("买家订单日志添加失败");
                    }
                    //冻结账户
                    $data8 = ['status' => 2];
                    $condition_buy = 'id=' . $order['buy_id'];
                    $buy_user = $MCommon->getValue('member_list', $condition_buy, 'status');
                    if ($buy_user == 1) {
                        $res8 = $MCommon->getUpdata('member_list', $condition_buy, $data8);
                        if (!$res8) {
                            throw new \Exception("冻结买家账户失败");
                        }
                    }
                    Db::commit();
                    $redis->delete('balance_agreeAppeal_' . $order_id);
                    return ['code' => 1, 'msg' => '同意申诉成功,订单失效'];
                } catch (\Exception $e) {
                    Db::rollback();
                    $redis->delete('balance_agreeAppeal_' . $order_id);
                    return ['code' => 2, 'msg' => '同意申诉失败'];
                }
            } else {
                return ['code' => 2, 'msg' => '参数错误'];
            }
        } else {
            return ['code' => 2, 'msg' => '参数错误'];
        }
    }

    //拒绝申诉balance
    public function balance_refuseAppeal($order_id)
    {
        $MCommon = new MCommon();
        $condition = 'id=' . $order_id;
        $order = $MCommon->getField('order_list', $condition, 'id,orderNo,num,status,price,sell_id,buy_id');
        if ($order && $order['status'] == 11) {
            try {
                Db::startTrans();
                $data1 = ['status' => 13, 'e_time' => time()];
                //更改订单状态
                $res1 = $MCommon->getUpdata('order_list', $condition, $data1);
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
                $MCommon = new MCommon();
                $res2 = $MCommon->getInsert('order_log', $data2);
                $res3 = $MCommon->getInsert('order_log', $data3);
                if (!$res2) {
                    throw new \Exception("卖家订单日志添加失败");
                }
                if (!$res3) {
                    throw new \Exception("买家订单日志添加失败");
                }
                $condition_buy = 'id=' . $order['buy_id'];
                $user = $MCommon->getField('member_list', $condition_buy, 'id,level_last,mac_wallet,mac_assets,yx_zt_num,partner,balance_num,balance,coin,sell_limit,level,first_blood,sell_limit,f_uid,f_uid_all');
                $res4 = $MCommon->getIncrease('member_list', $condition_buy, 'mac_wallet', $order['num']);//买家加钱
                if (!$res4) {
                    throw new \Exception("买家加钱失败");
                }
                $res5 = $MCommon->getIncrease('member_list', $condition_buy, 'balance_num', $order['num']);//买家累计购买量
                if (!$res5) {
                    throw new \Exception("买家增加累计购买量失败");
                }
                //累计卖币统计
                $add2 = $MCommon->getIncrease('member_list', $condition_buy, 'sell_coin_money', ($order['num'] * $order['price']));//买家累计购买量
                if (!$add2) {
                    $message = '累计卖币统计增加失败';
                    throw new \Exception($message);
                }
                $System = new SystemModule();
                $coin_p = $System->readConfig('GM_REWARD');
                //买币赠送子币
                if (floatval($coin_p) > 0) {
                    $res11 = $MCommon->getIncrease('member_list', $condition_buy, 'coin', $order['num'] * $coin_p / 100);
                    if (!$res11) {
                        throw new \Exception("买币赠送WGT失败");
                    }
                    $data10 = [
                        'u_id' => $order['buy_id'],
                        'o_id' => $order['id'],
                        'bo_money' => $order['num'] * $coin_p / 100,
                        'former_money' => $user['coin'],
                        'type' => 501,
                        'message' => '买币赠送WGT',
                        'bo_time' => time()
                    ];
                    $res10 = $MCommon->getInsert('member_coin_log', $data10);
                    if (!$res10) {
                        throw new \Exception("买币赠送WGT记录添加失败");
                    }
                }
                $quota_reward = $MCommon->readLevel( $user['id'], 'quota_reward');
                $quota_reward = $quota_reward['quota_reward'];
                $data6 = [
                    'u_id' => $order['buy_id'],
                    'o_id' => $order['id'],
                    'bo_money' => $order['num'],
                    'former_money' => $user['mac_wallet'],
                    'type' => 308,
                    'message' => '购买订单完成',
                    'bo_time' => time()
                ];
                $res6 = $MCommon->getInsert('member_mac_wallet_log', $data6);
                if (!$res6) {
                    throw new \Exception("买家钱包记录添加失败");
                }
                //判断买家用户是不是第一次
                if ($user['first_blood'] == 1) {//买家第一次还在
                    //执行第一次奖励,第一次状态改变 0首购允许数量 1首购可售额度配送倍数 2首购矿池资产配送倍数 3升级合伙人所需直推
                    $config_f = $System->readConfig(['FRIST_PURCHASE_NUM', 'FRIST_PURCHASE_SELL_LIMIT', 'FRIST_PURCHASE_ASSETS', 'PARTNER_ZT'], 2);
                    $num = explode(',', $config_f[0]);
                    if (in_array($order['num'], $num)) {//是首购数量
                        //首购可售额度增加
                        if (floatval($config_f[1]) > 0) {
                            $user1 = $MCommon->getIncrease('member_list', $condition_buy, 'sell_limit', $order['num'] * $config_f[1]);//买家
                            if (!$user1) {
                                $message = '买家首购可售额度加钱失败';
                                throw new \Exception($message);
                            }
                            $first1 = [
                                'u_id' => $order['buy_id'],
                                'o_id' => $order['id'],
                                'bo_money' => $order['num'] * $config_f[1],
                                'former_money' => $user['sell_limit'],
                                'type' => 407,
                                'message' => '首购赠送可售额度',
                                'bo_time' => time()
                            ];
                            $first_res1 = $MCommon->getInsert('member_sell_limit_log', $first1);//可售额度记录
                            if (!$first_res1) {
                                $message = '增加买家首购可售额度记录失败';
                                throw new \Exception($message);
                            }
                        }
                        //首购矿池资产赠送
                        if (floatval($config_f[2]) > 0) {
                            $user2 = $MCommon->getIncrease('member_list', $condition_buy, 'mac_assets', $order['num'] * $config_f[2]);//买家累计购买量
                            if (!$user2) {
                                $message = '买家首购矿池资产加钱失败';
                                throw new \Exception($message);
                            }
                            $first2 = [
                                'u_id' => $order['buy_id'],
                                'o_id' => $order['id'],
                                'bo_money' => $order['num'] * $config_f[2],
                                'former_money' => $user['mac_assets'],
                                'type' => 208,
                                'message' => '首购赠送矿池资产',
                                'bo_time' => time()
                            ];
                            $first_res2 = $MCommon->getInsert('member_mac_assets_log', $first2);//矿池资产
                            if (!$first_res2) {
                                $message = '增加买家首购矿池资产记录失败';
                                throw new \Exception($message);
                            }
                        }
                        //用户修改首购状态
                        $user3 = ['first_blood' => 2];
                        $first_res3 = $MCommon->getUpdata('member_list', $condition_buy, $user3);
                        if (!$first_res3) {
                            $message = '更新用户信息失败';
                            throw new \Exception($message);
                        }
                        //首购记录
                        $user4 = ['u_id' => $order['buy_id'], 'time' => time()];
                        $first_res4 = $MCommon->getInsert('blood_log', $user4);
                        if (!$first_res4) {
                            $message = '首购记录失败';
                            throw new \Exception($message);
                        }
                        //有效直推团队有效+1
                        if ($user['f_uid'] != 0) {
                            //有效直推+1
                            $user5 = $MCommon->getIncrease('member_list', 'id = ' . $user['f_uid'], 'yx_zt_num', 1);
                            if (!$user5) {
                                $message = '有效直推增加失败';
                                throw new \Exception($message);
                            }
                            //有效团队+1
//                            $f_uid_arr = explode(',', $user['f_uid_all']);
//                            $f_uid_arr = array_reverse($f_uid_arr);//反转
//                            $arr = array_shift($f_uid_arr);//删除自己
                            $arr = explode(',', $user['f_uid_all']);
                            if (!empty($arr)) {//存在除了自己的团队
                                if (count($arr) > 1){
                                    $arr_where = implode(',', $arr);
                                    $user6 = $MCommon->getIncrease('member_list', 'id in ('. $arr_where.')', 'yx_team', 1);
                                }else{
                                    $user6 = $MCommon->getIncrease('member_list', 'id ='. $arr[0], 'yx_team', 1);
                                }
                                if (!$user6) {
                                    $message = '有效团队增加失败';
                                    throw new \Exception($message);
                                }
                            }
                            //判断上级是否达到合伙人
                            $f_user = $MCommon->getField('member_list', 'id =' . $user['f_uid'], 'level,yx_zt_num,partner');
                            if ($f_user['partner'] == 0 && $f_user['level'] >= 3 && $f_user['yx_zt_num'] >= $config_f[3]) {
                                $user7 = $MCommon->getUpdata('member_list', 'id =' . $user['f_uid'], ['partner' => 1,'partner_time' => time()]);
                                if (!$user7) {
                                    $message = '升级合伙人失败';
                                    throw new \Exception($message);
                                }
                            }
                        }
                    }
                } else {//可售额度按正常额度
                    //增加可售额度记录
                    if (floatval($quota_reward) > 0) {
                        $res8 = $MCommon->getIncrease('member_list', $condition_buy, 'sell_limit', ($order['num'] * floatval($quota_reward)));//买家可售额度
                        if (!$res8) {
                            throw new \Exception("买币赠送增加可售额度失败");
                        }
                        $data7 = [
                            'u_id' => $order['buy_id'],
                            'o_id' => $order['id'],
                            'bo_money' => $order['num'] * floatval($quota_reward),
                            'former_money' => $user['sell_limit'],
                            'type' => 405,
                            'message' => '买币赠送可售额度',
                            'bo_time' => time()
                        ];
                        $res7 = $MCommon->getInsert('member_sell_limit_log', $data7);
                        if (!$res7) {
                            throw new \Exception("买币赠送可售额度记录添加失败");
                        }
                    }
                }
                //判断等级是否升级
                if ($user['level'] < 4) {//1~3级用户,无需判断降级
                    //查询等级表
                    $need_level = Db::name('member_level')->where('id in (1,2,3,4)')->field('id,buy_coin,level_machine')->select();
                    //升四级
                    if ($user['level'] < 4 && ($user['balance_num'] + $order['num']) >= $need_level[3]['buy_coin'] && Db::name('machine_order')->where('status = 1 and mac_id =' . $need_level[3]['level_machine'])->count() > 0) {
                        //更改历史最高等级
                        if ($user['level_last'] < $need_level[3]['id']) {
                            $res = $MCommon->getUpdata('member_list', 'id =' . $user['id'], ['level_last' => $need_level[3]['id']]);
                            if (!$res) {
                                $message = '更新历史等级失败';
                                throw new \Exception($message);
                            }
                        }
                        //直推奖励
                        if ($user['level_last'] < 1) {
                            //有效升级--直推得奖励
                            $f_level = $MCommon->getValue('member_list', 'id =' . $user['f_uid'], 'level');//父等级
                            if ($f_level != 0) {
                                $f_info = $MCommon->getValue('member_level', 'id =' . $f_level, 'direct_assets');//父等级
                                $mac_assets = $MCommon->getValue('member_list', 'id =' . $user['f_uid'], 'mac_assets');
                                $res = $MCommon->getIncrease('member_list', 'id =' . $user['f_uid'], 'mac_assets', $f_info);
                                if ($res) {
                                    $data = [
                                        'u_id' => $user['f_uid'],
                                        'bo_money' => $f_info,
                                        'former_money' => $mac_assets,
                                        'type' => 204,
                                        'message' => '下级升级为一级奖励',
                                        'bo_time' => time(),
                                        'status' => 1
                                    ];
                                    $res = $MCommon->getInsert('member_mac_assets_log', $data);
                                    if (!$res) {
                                        $message = '写入日志失败';
                                        throw new \Exception($message);
                                    }
                                }
                                // 获取上级购买直通车记录(未到延迟赠送时间的，且未延迟赠送过的)
                                $rain_log = Db::name('rain_log')->where('u_id', $user['f_uid'])->where('end_time', '>=', time())->where('status', 0)->field('*')->find();
                                $sell_limit = $MCommon->getValue('member_list', 'id =' . $user['f_uid'], 'sell_limit');
                                if ($rain_log) {
                                    // 增加人数
                                    Db::name('rain_log')->where('id', $rain_log['id'])->setInc('t');
                                    // 判断到达条件否
                                    if (($rain_log['t'] + 1) >= $rain_log['t_1']) {
                                        // 修改为已经赠送
                                        Db::name('rain_log')->where('id', $rain_log['id'])->setField('status', 1);
                                        // 发放赠送
                                        if ($rain_log['sell_limit_wait'] > 0) {
                                            Db::name('member_list')->where('id', $user['f_uid'])->setInc('sell_limit', $rain_log['sell_limit_wait']);
                                            Db::name('member_sell_limit_log')->insert([
                                                'u_id' => $user['f_uid'],
                                                'o_id' => $rain_log['id'],
                                                'bo_money' => $rain_log['sell_limit_wait'],
                                                'former_money' => $sell_limit,
                                                'type' => 409,
                                                'message' => '认筹三级直通车延长赠送',
                                                'bo_time' => time(),
                                                'status' => 1,
                                                'is_look' => 0,
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                        //执行升级
                        $level = $MCommon->getUpdata('member_list', 'id =' . $user['id'], ['level' => $need_level[3]['id']]);
                        if (!$level) {
                            $message = '用户升级失败';
                            throw new \Exception($message);
                        }
                        //升四级判断是否成为合伙人
                        $config = $System->readConfig('PARTNER_ZT');
                        if ($user['yx_zt_num'] >= $config && $user['partner'] != 1) {
                            //检查我的有效直推
                            $user9 = $MCommon->getUpdata('member_list', 'id =' . $user['id'], ['partner' => 1]);
                            if (!$user9) {
                                $message = '升级合伙人失败';
                                throw new \Exception($message);
                            }
                        }
                        //用户升级到4级,判断上级能否升级
                        $f_uid = $user['f_uid'];
                        for ($i = 5; $i <= 7; $i++) {
                            $direct_num = $MCommon->getValue('member_level', 'id = ' . $i, 'direct_num');
                            $count = $MCommon->getCounts('member_list', 'f_uid = ' . $f_uid . ' and level >= ' . ($i - 1));
                            if ($count >= $direct_num) {
                                $f_user = $MCommon->getField('member_list', 'id =' . $f_uid, 'id,level');
                                if ($f_user['level'] == ($i - 1)) {//无法跳级,只能当父级用户等级为4时才能升5
                                    $level = $MCommon->getUpdata('member_list', ['id' => $f_uid], ['level' => $i]);
                                    if (!$level) {
                                        $message = '用户升级等级' . $i . '失败';
                                        throw new \Exception($message);
                                    }
                                    //如果升级5成功，判断上级升67
                                    $f_uid = $MCommon->getValue('member_list', 'id =' . $f_uid, 'f_uid');
                                }
                            } else {
                                break;
                            }
                        }
                        //如果能够升级至3级
                    } elseif ($user['level'] < 3 && ($user['balance_num'] + $order['num']) >= $need_level[2]['buy_coin'] && Db::name('machine_order')->where('status = 1 and mac_id =' . $need_level[2]['level_machine'])->count() > 0) {
                        //更改历史最高等级
                        if ($user['level_last'] < $need_level[2]['id']) {
                            $res = $MCommon->getUpdata('member_list', 'id =' . $user['id'], ['level_last' => $need_level[2]['id']]);
                            if (!$res) {
                                $message = '更新历史等级失败';
                                throw new \Exception($message);
                            }
                        }
                        //直推奖励
                        if ($user['level_last'] < 1) {
                            //有效升级--直推得奖励
                            $f_level = $MCommon->getValue('member_list', 'id =' . $user['f_uid'], 'level');//父等级
                            if ($f_level != 0) {
                                $f_info = $MCommon->getValue('member_level', 'id =' . $f_level, 'direct_assets');//父等级
                                $mac_assets = $MCommon->getValue('member_list', 'id =' . $user['f_uid'], 'mac_assets');
                                $res = $MCommon->getIncrease('member_list', 'id =' . $user['f_uid'], 'mac_assets', $f_info);
                                if ($res) {
                                    $data = [
                                        'u_id' => $user['f_uid'],
                                        'bo_money' => $f_info,
                                        'former_money' => $mac_assets,
                                        'type' => 204,
                                        'message' => '下级升级为一级奖励',
                                        'bo_time' => time(),
                                        'status' => 1
                                    ];
                                    $res = $MCommon->getInsert('member_mac_assets_log', $data);
                                    if (!$res) {
                                        $message = '写入日志失败';
                                        throw new \Exception($message);
                                    }
                                }
                                // 获取上级购买直通车记录(未到延迟赠送时间的，且未延迟赠送过的)
                                $rain_log = Db::name('rain_log')->where('u_id', $user['f_uid'])->where('end_time', '>=', time())->where('status', 0)->field('*')->find();
                                $sell_limit = $MCommon->getValue('member_list', 'id =' . $user['f_uid'], 'sell_limit');
                                if ($rain_log) {
                                    // 增加人数
                                    Db::name('rain_log')->where('id', $rain_log['id'])->setInc('t');
                                    // 判断到达条件否
                                    if (($rain_log['t'] + 1) >= $rain_log['t_1']) {
                                        // 修改为已经赠送
                                        Db::name('rain_log')->where('id', $rain_log['id'])->setField('status', 1);
                                        // 发放赠送
                                        if ($rain_log['sell_limit_wait'] > 0) {
                                            Db::name('member_list')->where('id', $user['f_uid'])->setInc('sell_limit', $rain_log['sell_limit_wait']);
                                            Db::name('member_sell_limit_log')->insert([
                                                'u_id' => $user['f_uid'],
                                                'o_id' => $rain_log['id'],
                                                'bo_money' => $rain_log['sell_limit_wait'],
                                                'former_money' => $sell_limit,
                                                'type' => 409,
                                                'message' => '认筹三级直通车延长赠送',
                                                'bo_time' => time(),
                                                'status' => 1,
                                                'is_look' => 0,
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                        //执行升级
                        $level = $MCommon->getUpdata('member_list', 'id =' . $user['id'], ['level' => $need_level[2]['id']]);
                        if (!$level) {
                            $message = '用户升级失败';
                            throw new \Exception($message);
                        }
                        //升三级判断是否成为合伙人
                        $config = $System->readConfig('PARTNER_ZT');
                        if ($user['yx_zt_num'] >= $config && $user['partner'] != 1) {
                            //检查我的有效直推
                            $user9 = $MCommon->getUpdata('member_list', 'id =' . $user['id'], ['partner' => 1]);
                            if (!$user9) {
                                $message = '升级合伙人失败';
                                throw new \Exception($message);
                            }
                        }
                        //如果能够升级至2级
                    } elseif ($user['level'] < 2 && ($user['balance_num'] + $order['num']) >= $need_level[1]['buy_coin'] && Db::name('machine_order')->where('status = 1 and mac_id =' . $need_level[1]['level_machine'])->count() > 0) {
                        //更改历史最高等级
                        if ($user['level_last'] < $need_level[1]['id']) {
                            $res = $MCommon->getUpdata('member_list', 'id =' . $user['id'], ['level_last' => $need_level[1]['id']]);
                            if (!$res) {
                                $message = '更新历史等级失败';
                                throw new \Exception($message);
                            }
                        }
                        //直推奖励
                        if ($user['level_last'] < 1) {
                            //有效升级--直推得奖励
                            $f_level = $MCommon->getValue('member_list', 'id =' . $user['f_uid'], 'level');//父等级
                            if ($f_level != 0) {
                                $f_info = $MCommon->getValue('member_level', 'id =' . $f_level, 'direct_assets');//父等级
                                $mac_assets = $MCommon->getValue('member_list', 'id =' . $user['f_uid'], 'mac_assets');
                                $res = $MCommon->getIncrease('member_list', 'id =' . $user['f_uid'], 'mac_assets', $f_info);
                                if ($res) {
                                    $data = [
                                        'u_id' => $user['f_uid'],
                                        'bo_money' => $f_info,
                                        'former_money' => $mac_assets,
                                        'type' => 204,
                                        'message' => '下级升级为一级奖励',
                                        'bo_time' => time(),
                                        'status' => 1
                                    ];
                                    $res = $MCommon->getInsert('member_mac_assets_log', $data);
                                    if (!$res) {
                                        $message = '写入日志失败';
                                        throw new \Exception($message);
                                    }
                                }
                                // 获取上级购买直通车记录(未到延迟赠送时间的，且未延迟赠送过的)
                                $rain_log = Db::name('rain_log')->where('u_id', $user['f_uid'])->where('end_time', '>=', time())->where('status', 0)->field('*')->find();
                                $sell_limit = $MCommon->getValue('member_list', 'id =' . $user['f_uid'], 'sell_limit');
                                if ($rain_log) {
                                    // 增加人数
                                    Db::name('rain_log')->where('id', $rain_log['id'])->setInc('t');
                                    // 判断到达条件否
                                    if (($rain_log['t'] + 1) >= $rain_log['t_1']) {
                                        // 修改为已经赠送
                                        Db::name('rain_log')->where('id', $rain_log['id'])->setField('status', 1);
                                        // 发放赠送
                                        if ($rain_log['sell_limit_wait'] > 0) {
                                            Db::name('member_list')->where('id', $user['f_uid'])->setInc('sell_limit', $rain_log['sell_limit_wait']);
                                            Db::name('member_sell_limit_log')->insert([
                                                'u_id' => $user['f_uid'],
                                                'o_id' => $rain_log['id'],
                                                'bo_money' => $rain_log['sell_limit_wait'],
                                                'former_money' => $sell_limit,
                                                'type' => 409,
                                                'message' => '认筹三级直通车延长赠送',
                                                'bo_time' => time(),
                                                'status' => 1,
                                                'is_look' => 0,
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                        //执行升级
                        $level = $MCommon->getUpdata('member_list', 'id =' . $user['id'], ['level' => $need_level[1]['id']]);
                        if (!$level) {
                            $message = '用户升级失败';
                            throw new \Exception($message);
                        }
                        //如果能够升级至1级
                    } elseif ($user['level'] < 1 && ($user['balance_num'] + $order['num']) >= $need_level[0]['buy_coin'] && Db::name('machine_order')->where('status = 1 and mac_id =' . $need_level[0]['level_machine'])->count() > 0) {
                        //更改历史最高等级
                        if ($user['level_last'] < $need_level[0]['id']) {
                            $res = $MCommon->getUpdata('member_list', 'id =' . $user['id'], ['level_last' => $need_level[0]['id']]);
                            if (!$res) {
                                $message = '更新历史等级失败';
                                throw new \Exception($message);
                            }
                            //有效升级--直推得奖励
                            $f_level = $MCommon->getValue('member_list', 'id =' . $user['f_uid'], 'level');//父等级
                            if ($f_level != 0) {
                                $f_info = $MCommon->getValue('member_level', 'id =' . $f_level, 'direct_assets');//父等级
                                $mac_assets = $MCommon->getValue('member_list', 'id =' . $user['f_uid'], 'mac_assets');
                                $res = $MCommon->getIncrease('member_list', 'id =' . $user['f_uid'], 'mac_assets', $f_info);
                                if ($res) {
                                    $data = [
                                        'u_id' => $user['f_uid'],
                                        'bo_money' => $f_info,
                                        'former_money' => $mac_assets,
                                        'type' => 204,
                                        'message' => '下级升级为一级奖励',
                                        'bo_time' => time(),
                                        'status' => 1
                                    ];
                                    $res = $MCommon->getInsert('member_mac_assets_log', $data);
                                    if (!$res) {
                                        $message = '写入日志失败';
                                        throw new \Exception($message);
                                    }
                                }
                            }
                            // 获取上级购买直通车记录(未到延迟赠送时间的，且未延迟赠送过的)
                            $rain_log = Db::name('rain_log')->where('u_id', $user['f_uid'])->where('end_time', '>=', time())->where('status', 0)->field('*')->find();
                            $sell_limit = $MCommon->getValue('member_list', 'id =' . $user['f_uid'], 'sell_limit');
                            if ($rain_log) {
                                // 增加人数
                                Db::name('rain_log')->where('id', $rain_log['id'])->setInc('t');
                                // 判断到达条件否
                                if (($rain_log['t'] + 1) >= $rain_log['t_1']) {
                                    // 修改为已经赠送
                                    Db::name('rain_log')->where('id', $rain_log['id'])->setField('status', 1);
                                    // 发放赠送
                                    if ($rain_log['sell_limit_wait'] > 0) {
                                        Db::name('member_list')->where('id', $user['f_uid'])->setInc('sell_limit', $rain_log['sell_limit_wait']);
                                        Db::name('member_sell_limit_log')->insert([
                                            'u_id' => $user['f_uid'],
                                            'o_id' => $rain_log['id'],
                                            'bo_money' => $rain_log['sell_limit_wait'],
                                            'former_money' => $sell_limit,
                                            'type' => 409,
                                            'message' => '认筹三级直通车延长赠送',
                                            'bo_time' => time(),
                                            'status' => 1,
                                            'is_look' => 0,
                                        ]);
                                    }
                                }
                            }
                        }
                        //执行升级
                        $level = $MCommon->getUpdata('member_list', 'id =' . $user['id'], ['level' => $need_level[0]['id']]);
                        if (!$level) {
                            $message = '用户升级失败';
                            throw new \Exception($message);
                        }

                    }
                }
                Db::commit();
                return ['code' => 1, 'msg' => '拒绝申诉成功,交易完成'];
            } catch (\Exception $e) {
                Db::rollback();
                return ['code' => 2, 'msg' => '拒绝申诉失败' . $e->getMessage()];
            }
        } else {
            return ['code' => 2, 'msg' => '订单不存在'];
        }
    }
}