<?php
namespace app\api\controller;

use think\Db;
use app\api\model\MMember;
use think\Request;
use think\Exception;
use app\admin\model\MMemberBalanceLog;
use think\facade\Cache;
use app\api\model\MConfig;
use app\api\validate\TelVal;
use app\admin\model\MMemberLevel;

class Mutualaidorder extends Common
{   
    
    private function makeRand($num = 9)
    {
        mt_srand((double)microtime() * 1000000);//用 seed 来给随机数发生器播种。
        $strand = str_pad(mt_rand(1, 99999), $num, "0", STR_PAD_LEFT);
        if (Db::name('member_mutualaid')->where('orderNo', 'MT' . date('Ymd') . $strand)->count() == 0) {
            return 'MT' . date('Ymd') . $strand;
        }
        $this->makeRand();
    }
    
    //编号
    private function makeRandCW()
    {
        $list = Db::name('member_mutualaid')->where('purchase_no != ""')->order('purchase_no desc')->select();
        if (empty($list)) {
            return 'CW' . date('Ymd') . '00001';
        }else{
            $num = intval(substr($list[0]['purchase_no'],2))+1;
            $num = intval(substr_replace($num, date('md',time()), 4, 4));
            return 'CW' .$num;
        }
        $this->makeRandCW();
    }
    
   
    /** 资产数据渲染
     */
    public function assetsIndexCensus(Request $request)
    {   
        $u_id = $this->userinfo['user_id'];       
        $MMember = new MMember();
        $user_info = $MMember->getInfo(['id'=>$u_id]);
        
        $userInfo['all_assets'] = Db::name('user_purchase')->where('uid', $u_id)->where('compose_status in (0,2) and status in (1,2,3) and is_exist = 1')->sum('new_price');
        $userInfo['all_reward'] = round($user_info['census_profit_deposit'] + $user_info['census_profit_recom'] + $user_info['census_profit_team'], 2);
        $userInfo['balance'] = $user_info['balance'];
        //census_profit_deposit census_profit_recom census_profit_team
        return json(['code' => 1, 'msg' => 'success', 'data'=>$userInfo]);
    }
    
    
    
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////                         总收益                                                       //////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////   
    
    /** 收益明细 
     * @param Request $request
     */
    public function accountLog(Request $request)
    {   
        $MMember = new MMember();
        $u_id = $this->userinfo['user_id'];
        
        $member_info = $MMember->getInfo(['id'=>$u_id]);
        
        // 8升值收益   4推荐收益   5团队收益 7全部
        $page = intval($request->post('page', 1));
        $type = intval($request->post('type', 8));
        if ($page <= 0 || !in_array($type, [1, 2, 3, 4, 5, 6, 7,8 ])) return json(['code' => SIGN_ERROR, 'msg' => '参数错误']); 
        $page_size = 10;
        $type = $type == 5 ? 6 : $type;
        $type = $type == 4 ? 5 : $type;
        $where = 'type =' . $type . ' and u_id=' . $u_id;
        
        $MMemberBalanceLog = new MMemberBalanceLog();
        
        if ($type == 7) {
            $where = 'type in (4,5,6,8) and u_id=' . $u_id;
        }

        $data = $this->commonLog($page, $page_size, $where);
        
        //可兑换收益
        $data['assets']['all_assets'] = round($member_info['profit_deposit'] + $member_info['profit_recom'] + $member_info['profit_team'], 2);
        //$data['assets']['profit_deposit'] = $member_info['profit_deposit'];
        //推荐收益
        $data['assets']['profit_recom'] = $member_info['profit_recom'];
        //团队收益
        $data['assets']['profit_team'] = $member_info['profit_team'];
        //$data['assets']['balance'] = $member_info['balance'];
        //$data['assets']['icon'] = $member_info['icon'];
        //升值收益
        $data['assets']['zzReward'] = $MMemberBalanceLog->where('u_id', $u_id)->where('type = 8')->sum('change_money');
        //昨日收益
        $data['assets']['yesterdayReward'] = $MMemberBalanceLog->where('u_id', $u_id)->where('type in (4,5,6,8)')->whereTime('bo_time', 'yesterday')->sum('change_money');
        //今日收益
        $data['assets']['todayReward'] = $MMemberBalanceLog->where('u_id', $u_id)->where('type in (4,5,6,8)')->whereTime('bo_time', 'today')->sum('change_money');
        //累计收益
        $data['assets']['allReward'] = $MMemberBalanceLog->where('u_id', $u_id)->where('type in (4,5,6,8)')->sum('change_money');        
        
        return json(['code' => 1, 'msg' => 'success', 'data'=>$data]);
    }
    
    public function commonLog($page, $page_size, $where, $order = 'id desc', $field = 'change_money,message,bo_time')
    {
        $count = Db::name('member_balance_log')->where($where)->count();
        $pages = ceil($count / $page_size);
        $offset = ($page - 1) * $page_size;
        $list = Db::name('member_balance_log')->where($where)
        ->order($order)
        ->field($field)
        ->limit($offset, $page_size)
        ->select();
        foreach ($list as $k => $v) {
            $list[$k]['time'] = date('Y-m-d H:i:s', $v['bo_time']);
            $list[$k]['sign'] = $v['change_money'] >= 0 ? 1 : 2;
            $list[$k]['num'] = abs($v['change_money']);
            unset($list[$k]['change_money']);
        }
        return [
            'count' => $count,
            'pages' => $pages,
            'list' => $list,
        ];
    }
    
    
    //收益兑换出售渲染
    public function exchangeProfit()
    {
        $list = Db::name('mutualaid_list')->where('status = 1')->field('id,name,min_price,max_price')->select();
        $purchase = [];
        foreach ($list as $k => $v) {
            $purchase[] = ['id' => $v['id'], 'name' => $v['name']. '('.floatval($v['min_price']).' - '.floatval($v['max_price']).')', 'min_price' => floatval($v['min_price']), 'max_price' => floatval($v['max_price'])];
        }
        
        //$account = [1 => '收益转存', 2 => '推荐收益', 3 => '团队收益'];
        //$data['account'] = $account;
        $data['purchase'] = $purchase;
        return json(['code' => 1, 'msg' => [], 'data'=>$data]);
    }
    
    
    
    /** 收益兑换 兑换宠物
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function exchangePurchase(Request $request)
    {   
        $MMember = new MMember();
        $u_id = $this->userinfo['user_id'];
        $userInfo = $MMember->where('id', $u_id)->find();
        //$account = $request->post('account', 1);
        $purchase_id = $request->post('purchase_id');
        $num = abs($request->post('num', 0));
        $payPass = $request->post('password');
        $price = $request->post('price');
        
        if (!$purchase_id || $num <= 0 || $price <= 0) {
            return json(['code' => 2, 'msg' => '参数错误', 'data'=>[]]);
        }
        
        if (Cache::get('exchangePurchase'.$u_id) == 2){
            return json(['code' => 2, 'msg' => '兑换中', 'data'=>[]]);
        }
        Cache::set('exchangePurchase'.$u_id,2,10); 
        
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig(['mutualOpenTime','mutualEndTime','firstMutualNum','secondMutualNum','thirdMutualNum','foreverMutualNum','allAssets'],2);

        //开启时间验证
        $open_time = $config_val[0];//Config::get('site.mutualOpenTime');
        $end_time = $config_val[1];//Config::get('site.mutualEndTime');
        $now_time = strtotime(date('Ymd'));//今日凌晨时间戳
        if (empty($payPass)){
            Cache::set('exchangePurchase'.$u_id,0);
            return json(['code' => 2, 'msg' => '请输入支付密码', 'data'=>[]]);
        }
        if ($userInfo['pay_pass'] != md5($payPass.'pay_passwd')){
            Cache::set('exchangePurchase'.$u_id,0);
            return json(['code' => 2, 'msg' => '支付密码不匹配', 'data'=>[]]);
        }
        if (time() - $now_time < $open_time * 3600 || time() - $now_time > $end_time * 3600) {
            Cache::set('exchangePurchase'.$u_id,0); 
            return json(['code' => 2, 'msg' => '兑换开启时间为' . $open_time . '点 - ' . $end_time . '点', 'data'=>[]]);
        }

        //购买单价限制
        switch ($userInfo['mutual_sell_time']) {
            case 0;
            $limit_price = $config_val[2];//Config::get('site.firstMutualNum');
            break;
            case 1;
            $limit_price = $config_val[3];//Config::get('site.secondMutualNum');
            break;
            case 2;
            $limit_price = $config_val[4];//Config::get('site.thirdMutualNum');
            break;
            default;
            $limit_price = $config_val[5];//Config::get('site.foreverMutualNum');
        }
        $limit_price_section = explode('-', $limit_price);
        if ($price < $limit_price_section[0] || $price > $limit_price_section[1]) {
            Cache::set('exchangePurchase'.$u_id,0); 
            return json(['code' => 2, 'msg' => '限制兑换价格为 ' . $limit_price_section[0] . ' - ' . $limit_price_section[1], 'data'=>[]]);
        }
        //单天次数验证
        //$Redis = new Redis();
        //        if (in_array($account, [1, 2])) {
        //            $buy_time = $Redis->get('TRRecond' . $this->auth->id);
        //            if ($buy_time >= Config::get('site.TRRecond')) $this->error('购买次数超过限制');
        //        }
        //        if ($account == 3) {
        //            $buy_time = $Redis->get('profitSellNumDays' . $this->auth->id);
        //            if ($buy_time >= Config::get('site.profitSellNumDays')) $this->error('购买次数超过限制');
        //        }

        //$all_assets = $userInfo['profit_deposit'] + $userInfo['profit_recom'] + $userInfo['profit_team'];
        $all_assets = Db::name('member_mutualaid')->where('uid', $u_id)->where('compose_status in (0,2) and status in (1,2,3) and is_exist = 1')->sum('new_price');
        if ($all_assets < $config_val[6]){
            return json(['code' => 2, 'msg' => '兑换总资产不得低于' . $config_val[6], 'data'=>[]]);//Config::get('site.allAssets') Config::get('site.allAssets')
        }
        $purchase = Db::name('mutualaid_list')->where('id', $purchase_id)->where('status = 1')->find();
        if (!$purchase) {

            Cache::set('exchangePurchase'.$u_id,0); 
            return json(['code' => 2, 'msg' => '设备不存在', 'data'=>[]]);
        }
        if ($price < $purchase['min_price'] || $price > $purchase['max_price']) {
            Cache::set('exchangePurchase'.$u_id,0); 
            return json(['code' => 2, 'msg' => '单价不在区间内', 'data'=>[]]);
        }
        if ($userInfo['profit_deposit'] + $userInfo['profit_recom'] + $userInfo['profit_team'] < $num * $price) {
            Cache::set('exchangePurchase'.$u_id,0); 
            return json(['code' => 2, 'msg' => '可兑换资金不足', 'data'=>[]]);
        }
        
         
        try {
            
            Db::startTrans();
            //处理金额 及购买次数
            Db::name('member_list')->where('id', $userInfo['id'])->update([
                'mutual_sell_time' => Db::raw('mutual_sell_time + 1'),
                //                $money => Db::raw('' . $money . ' -' . $price * $num),
                'reward_census' => Db::raw('reward_census +' . $price * $num)
            ]);
            $lessMoney = $num * $price;
            $insert = [];
            if ($userInfo['profit_deposit'] <= $lessMoney){
                $decMoney = $lessMoney >= $userInfo['profit_deposit'] ? $userInfo['profit_deposit'] : $lessMoney;
                if ($decMoney > 0){
                    $MMember->where('id', $userInfo['id'])->setDec('profit_deposit',$decMoney);
                    $insert[] = [
                        'u_id' => $userInfo['id'],
                        'tel' => $userInfo['tel'],
                        'former_money' => $userInfo['profit_deposit'],
                        'change_money' => -$decMoney,
                        'after_money' => $userInfo['profit_deposit'] - $decMoney,
                        'message' => '兑换产品',
                        'type' => 4,
                        'bo_time' => time(),
                        'status' => 20
                    ];
                }
                $lessMoney -= $decMoney;
                if ($lessMoney > 0){
                    $decMoney = $lessMoney >= $userInfo['profit_recom'] ? $userInfo['profit_recom'] : $lessMoney;
                    $MMember->where('id', $userInfo['id'])->setDec('profit_recom',$decMoney);
                    $insert[] = [
                        'u_id' => $userInfo['id'],
                        'tel' => $userInfo['tel'],
                        'former_money' => $userInfo['profit_recom'],
                        'change_money' => -$decMoney,
                        'after_money' => $userInfo['profit_recom'] - $decMoney,
                        'message' => '兑换产品',
                        'type' => 5,
                        'bo_time' => time(),
                        'status' => 20
                    ];
                    $lessMoney -= $decMoney;
                    if ($lessMoney > 0){
                        $decMoney = $lessMoney >= $userInfo['profit_team'] ? $userInfo['profit_team'] : $lessMoney;
                        $MMember->where('id', $userInfo['id'])->setDec('profit_team',$decMoney);
                        $insert[] = [
                            'u_id' => $userInfo['id'],
                            'tel' => $userInfo['tel'],
                            'former_money' => $userInfo['profit_team'],
                            'change_money' => -$decMoney,
                            'after_money' => $userInfo['profit_team'] - $decMoney,
                            'message' => '兑换产品',
                            'type' => 6,
                            'bo_time' => time(),
                            'status' => 20
                        ];
                        $lessMoney -= $decMoney;
                    }
                }
            }else{
                $decMoney = $lessMoney;
                if ($decMoney > 0) {
                    $MMember->where('id', $userInfo['id'])->setDec('profit_deposit',$decMoney);
                    $insert[] = [
                        'u_id' => $userInfo['id'],
                        'tel' => $userInfo['tel'],
                        'former_money' => $userInfo['profit_deposit'],
                        'change_money' => -$decMoney,
                        'after_money' => $userInfo['profit_deposit'] - $decMoney,
                        'message' => '兑换产品',
                        'type' => 4,
                        'bo_time' => time(),
                        'status' => 20
                    ];
                    $lessMoney -= $decMoney;
                }
            }
            if ($lessMoney != 0) throw new Exception('兑换失败');
            Db::name('member_balance_log')->insertAll($insert);
            //增加会员持有
            for ($i = 0; $i < $num; $i++) {
                $orderNo = $this->makeRand();
                $purchase_no = $this->makeRandCW();
                // var_dump($purchase_id);die;
                $p_id = Db::name('member_mutualaid')->insertGetId([
                    'uid' => $userInfo['id'],
                    'purchase_id' => $purchase_id,
                    'tel' => $userInfo['tel'],
                    'orderNo' => $orderNo,
                    'purchase_no' => $purchase_no,
                    'get_price' => $price,
                    'new_price' => $price,
                    'days' => $purchase['days'],
                    'rate' => $purchase['rate'],
                    'deal_type' => 4,
                    'status' => 2,
                    'sta_time' => time()
                ]);
                //增加订单
                Db::name('mutualaid_order')->insert([
                    'p_id' => $p_id,
                    'purchase_id' => $purchase_id,
                    'orderNo' => $orderNo,
                    'sell_uid' => $userInfo['id'],
                    'sell_user' => $userInfo['tel'],
                    'is_overtime' => 1,
                    'price' => $price,
                    'status' => 0,
                    'create_time' => time()
                ]);
            }
/*             //买家判断升级有效会员
            if ($userInfo['is_effective'] == 0) {
                $this->upEffective($userInfo['id']);
            }
            $level = Db::name('member_level')->order('id desc')->select();
            //$upLevel = new UpLevel();            
            $MMember->upLevel($level,$u_id,$userInfo['level'],$userInfo['pets_assets_history'],$userInfo['zt_yx_num'],$userInfo['yx_team']);//判断升级
*/
            Db::commit();
            Cache::set('exchangePurchase'.$u_id,0); 
            return json(['code' => 1,'msg' => '兑换成功']);
        } catch (Exception $exception) {
            Db::rollback();
            Cache::set('exchangePurchase'.$u_id,0); 
            return json(['code' => 2,'msg' => '兑换失败'.$exception->getMessage()]);            
        }
    }
    
    /**有效会员升级
     * @throws Exception
     */
    public function upEffective($uid)
    {   
        $MMember = new MMember();
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig('effectiveUserAssets',1);
        $site_asstes = $config_val;//Config::get('site.effectiveUserAssets');
        $level = Db::name('member_level')->order('id desc')->select();
        //$UpLevel = new UpLevel();
        //升级有效用户Db::name('user')
        $user = $MMember->where('id', $uid)->field('is_effective,f_uid,f_uid_all,pets_assets')->find();
        if ($user['is_effective'] == 0) {//第一次升级有效会员
            $assets = Db::name('member_mutualaid')->where('uid =' . $uid . ' and status in (1,2,3)')->sum('new_price');
            if ($assets >= $site_asstes) {//$user['pets_assets']
                $MMember->where('id', $uid)->setField('is_effective', 1);
                if ($user['f_uid'] != 0) {
                    //团队 及直推
                    $MMember->where('id', $user['f_uid'])->setInc('zt_yx_num');
                    $MMember->where('id in (' . $user['f_uid_all'] . ')')->setInc('yx_team');
                    $team_list = $MMember->where('id in ('.$user['f_uid_all'].')')
                    ->field('id,level,pets_assets_history,zt_yx_num,yx_team,level')->select();
                    foreach ($team_list as $k => $v) {//判断
                        $MMember->upLevel($level, $v['id'], $v['level'], $v['pets_assets_history'], $v['zt_yx_num'], $v['yx_team']);
                    }
                }
            }
        }
    }
    
    
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////                         总资产                                                         //////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    /** 总资产页面 ok
     * @param Request $request
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function assetsIndex(Request $request)
    {   
        $MMember = new MMember();
        $u_id = $this->userinfo['user_id'];
        if(empty($u_id)){
            return json(['code' => 2,'msg' => '出错啦，找不到用户']);
        }   
        $page = $request->post('page', 1);
        if ($page <= 0) return json(['code' => 2,'msg' => '参数错误']);
        
        $user = $MMember->where('id', $u_id)->field('user,level,profit_deposit,profit_recom,profit_team')->find();
        $userInfo['all_assets'] = Db::name('member_mutualaid')->where('uid', $u_id)->where('compose_status in (0,2) and status in (1,2,3) and is_exist = 1')->sum('new_price');
        $userInfo['profit_deposit'] = $user['profit_deposit'];
        $userInfo['profit_recom'] = $user['profit_recom'];
        $userInfo['profit_team'] = $user['profit_team'];
        
        $count = Db::name('member_mutualaid')->where('compose_status in (0,2) and uid ='. $u_id.' and is_exist = 1')->whereIn('status', [1, 2, 3])->count();
        $page_size = 10;
        $pages = ceil($count / $page_size);
        $offset = ($page - 1) * $page_size;
        $list = Db::name('member_mutualaid')//价值 时间 模式 状态
        ->field('id,new_price,get_price as price,status,days,rate,up_time,purchase_id,sta_time')
        ->where('compose_status in (0,2) and uid ='. $u_id.' and is_exist = 1')
        ->whereIn('status', [1, 2, 3])
        ->order('id desc')
        ->limit($offset, $page_size)
        ->select();
        
        $purchaseList = Db::name('mutualaid_list')->field('id,logo,frag_log,level,name,min_price,max_price,sta_time,end_time,type,rate')->select();
        foreach ($list as $k => $v) {//1升值中 2待转让 3转让中 4已转让
            foreach ($purchaseList as $kk => $vv) {
                if ($v['purchase_id'] == $vv['id']) {
                    $list[$k]['logo'] = 'http://' . $_SERVER['HTTP_HOST'] . $vv['logo'];
                    unset($list[$k]['frag_log']);
                    $list[$k]['name'] = $vv['name'];
                    $list[$k]['rate'] = $v['days'] .'天/'.$v['rate'] * $v['days'].'%';
                    $list[$k]['level'] = $vv['level'];
                    $list[$k]['less_days'] = $v['days'] - $v['up_time'];
                    $list[$k]['staticReward'] = round($v['new_price'] - $v['price'], 2);
                    $list[$k]['reward'] = round($v['price'] * $v['rate'] * $v['days'] / 100, 2);
                    $list[$k]['time'] = date('i:s', $vv['sta_time']) . '-' . date('i:s', $vv['end_time']);
                    $list[$k]['type'] = floatval($vv['type']);
                    $list[$k]['price'] = $v['new_price'];
                    //                    $list[$k]['status'] = $v['status'] == 2 ? '领养中' : '转让中';
                    $list[$k]['create_time'] = date('m-d H:i', $v['sta_time']);
                    $list[$k]['end_time'] = date('m-d H:i', $v['sta_time'] + (1+$v['days'] * 86400));
                    $list[$k]['profit'] = $v['new_price']*$v['rate']*$v['days']/100;
                    continue;
                }
            }
        }
        $data= [
            'count' => $count,
            'pages' => $pages,
            'list' => $list,
            'user' => $userInfo
        ];
        return json(['code' => 1,'msg' => 'success', 'data'=>$data]);
    }

    
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////                         主币明细                                                         //////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    
    
    /**   212邀请奖励  210预约奖励  211直推预约奖励
     * @param Request $request
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function accountIndex(Request $request)
    {   
        $MMember = new MMember();
        $u_id = $this->userinfo['user_id'];
        if(empty($u_id)){
            return json(['code' => 2,'msg' => '出错啦，找不到用户']);
        } 
        $balance = $MMember->getValue(['id'=>$u_id], 'balance');
        
        $page = $request->post('page', 1);
        $type = $request->post('type', 2);
        $status = $request->post('status', 212);//210预约奖励  211直推预约奖励  212邀请奖励
        if ($page <= 0) return json(['code' => 2,'msg' => '参数错误']);
/*         if ($type == 1) {
            $userInfo['all'] = floatval($this->auth->balance);
        } elseif ($type == 2) {
            $userInfo['all'] = floatval($this->auth->energy);
        } else {
            $userInfo['all'] = floatval($this->auth->devote);
        } */
        if($status == 212){
            $status = ' status in (100,101,102,202,203,121,206,207,210,211,212)';
        }else{
            $status = ' status = '.$status;
        }
        $count = Db::name('member_balance_log')->where('u_id', $u_id)->where('type', $type)->where($status)->count();

        $page_size = 3;
        $pages = ceil($count / $page_size);
        $offset = ($page - 1) * $page_size;
        $list = Db::name('member_balance_log')
        ->field('change_money,message,bo_time')
        ->where('u_id', $u_id)
        ->where('type', $type)
        ->where($status)//'status', 
        ->order('id desc')
        ->limit($offset, $page_size)
        ->select();
        foreach ($list as $k => $v) {
            $list[$k]['time'] = date('Y-m-d H:i:s', $v['bo_time']);
            $list[$k]['num'] = $v['change_money'] > 0 ? '+' . $v['change_money'] : $v['change_money'];
            unset($list[$k]['change_money']);
        }
        $data = [
            'count' => $count,
            'pages' => $pages,
            'list' => $list,
            'balance' => $balance
        ];
        
        return json(['code' => 1,'msg' => 'success', 'data'=>$data]);
    }
    
    //转出对象信息
    public function transferOut(Request $request){
        $MMember = new MMember();
        $MConfig = new MConfig();
        $MMemberLevel = new MMemberLevel();
        
        $mobile = getValue($request->param('mobile',''));//转出手机号
        
        $member_info = $MMember->getInfo(['tel'=>$mobile],'real_name,level,tel');
        $member_info['real_name'] = $member_info['tel'].'('.$member_info['real_name'].')';
        if($member_info['level'] == 0){
            $member_info['level_img'] = 'http://' . $_SERVER['HTTP_HOST'] . $MConfig->readConfig('DEFAULT_LEVEL_IMG');
        }else{
            $member_info['level_img'] = 'http://' . $_SERVER['HTTP_HOST'] . $MMemberLevel->getInfo(['id'=>$member_info['level']],'level_logo');
        }
        unset($member_info['tel']);
        return json(['code' => 1,'msg' => 'success', 'data'=>$member_info]);
    }
    
    /** 转让 微分申请 
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function energyTransfer(Request $request)
    {   
        $KYB = Db::name('system_config')->where(['key'=>'mainCurrency'])->field('value')->find();

        $MMember = new MMember();
        $MConfig = new MConfig();
        $u_id = $this->userinfo['user_id'];
        if(empty($u_id)){
            return json(['code' => 2,'msg' => '出错啦，找不到用户']);
        } 
        $kyb = $KYB['value'];//$this->userinfo['kyb'];
        $member_info = $MMember->getInfo(['id'=>$u_id]);
        
        $mobile = getValue($request->param('mobile',''));//转出手机号
        $num = getValue($request->param('num', 0),'int');//intval($request->get('num'));//转出金额
        $payPass = getValue($request->param('paypass', ''));//trim($request->post('paypass'));

        if (empty($mobile))  return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => '请输入账号']);
        if (empty($num))  return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => '请输入转出金额']);
        if (!$payPass)  return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => '请输入密码']);


        $Validate = new TelVal();
        $data['tel'] = $mobile;
        if(!$Validate->check($data)) return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => $Validate->getError()]);
        
        if ($mobile == $member_info['tel'])  return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => '不能转给自己']);
        $type = intval($request->post('type', 2));//1查看信息 2转让
        if (!in_array($type, [1, 2]))  return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => '参数错误']);
        
        $config_val = $MConfig->readConfig(['minTransferNum','transferRate','transferBe','energySurplus'],2);
        //var_dump($config_val);exit();
        $transferNum = $config_val[0];//Config::get('site.minTransferNum');
        $transferRate = $config_val[1];//Config::get('site.transferRate');
        $transferBe = $config_val[2];//Config::get('site.transferBe');
       //转出者信息
        $user = Db::name('member_list')->where('tel', $mobile)->field('id,tel,user,status,level,f_uid_all,balance')->find();

        if (!$user) return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => '该会员不存在']);
        //$to_user = Db::name('user')->where('mobile', $mobile)->field('id,mobile,f_uid_all')->find();
        /* $to_arr = $user['f_uid_all'] == '' ? [] : explode(',', $user['f_uid_all']);
        if (!in_array($u_id, $to_arr)) {//!in_array($to_user['id'], $my_arr)
            return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => '该账号不属于同一个团队2']);
        } */
        
        $zr_count = Db::name('member_balance_log')->where(['u_id'=>$user['id'],'type'=>2,'status'=>203])->whereTime('bo_time', 'today')->count();
        if($zr_count >= 5){
            return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => '该账号接受转赠次数已满']);
        }

        
        $to_arr = $user['f_uid_all'] == '' ? [] : explode(',', $user['f_uid_all']);
        $f_to_arr = $member_info['f_uid_all'] == '' ? [] : explode(',', $member_info['f_uid_all']);
        //!in_array($user['id'], $f_to_arr) &&
        if ( !in_array($u_id, $to_arr)) {//!in_array($to_user['id'], $my_arr)
            return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => '该账号不属于同一个团队']);
        }

/*         if (Cache::get('energyTransfer'.$u_id) == 2){
            return json(['code' => 2, 'msg' => '转让冷却中', 'data'=>[]]);
        }
        Cache::set('energyTransfer'.$u_id,2,60); */

        $data = [];
        if ($type == 1) {//返还信息
            if ($user['status'] == 3) return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => '该账号已被冻结']);
            $data['level'] = 'http://' . $_SERVER['HTTP_HOST'] . Db::name('member_level')->where('id', $user['level'])->value('level_logo');
            $data['balance'] = $member_info['balance'];
            $data['username'] = $user['user'];
            
            return json(['code' => 1,'msg' => 'success', 'data'=>$data]);
        } else {            
            if ($member_info['pay_pass'] != md5($payPass.'pay_passwd')) return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => '支付密码不匹配']);
            if (!$num || $num <= 0) return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => '请输入转出数量']);
           
            if(!empty($transferRate)){
                $all_money = round($num + $num * $transferRate / 100, 2);
            }else{
                $all_money = $num;
            }

            if ($num < intval($transferNum)) return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => '最低转出数量为'. $transferNum]); 

            if ($transferBe > 0) {
                if ($num % $transferBe != 0) return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => '数量请输入' . $transferBe . '的倍数']); 
            }   //$this->auth->energy
            if ($member_info['balance'] < $all_money) return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => $kyb.'不足']);
            $energySurplus = $config_val[3];//Config::get('site.energySurplus');
            if ($member_info['balance'] - $all_money < $energySurplus) return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => $kyb.'转让最低剩余' . $energySurplus]);

            try {
                Db::startTrans();
                if ($num > 0) {
                    Db::name('member_list')->where('id', $u_id)->setDec('balance', $all_money);
                    Db::name('member_list')->where('tel', $mobile)->setInc('balance', $num);
                    Db::name('member_balance_log')->insert([
                        'u_id' => $u_id,
                        'tel' => $member_info['tel'],
                        'former_money' => $member_info['balance'],
                        'change_money' => -$all_money,
                        'after_money' => $member_info['balance'] - $all_money,
                        'message' => '转出至' . $mobile . '扣除'.$kyb,
                        'type' => 2,
                        'bo_time' => time(),
                        'status' => 202
                    ]);
                    $jm_mobile = substr_replace($member_info['tel'], '****', 3, 4);
                    Db::name('member_balance_log')->insert([
                        'u_id' => $user['id'],
                        'tel' => $user['tel'],
                        'former_money' => $user['balance'],
                        'change_money' => $num,
                        'after_money' => $user['balance'] + $num,
                        'message' => $jm_mobile . '转入'.$kyb,
                        'type' => 2,
                        'bo_time' => time(),
                        'status' => 203
                    ]);
                }
                Db::commit();
                //Cache::set('energyTransfer'.$u_id,0);
                return json(['code' => 1,'msg' => '转出成功']);
            } catch (Exception $exception) {
                Db::rollback();
                //Cache::set('energyTransfer'.$u_id,0);
                return json(['code' => 2,'msg' => '转出失败'.$exception->getMessage()]);      
            }
        }        
    }
    
    
    
    /** 转让记录 ok
     * @param Request $request
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
/*     public function transferList(Request $request)
    {
        $page = intval($request->post('page', 1));// 列表页码
        //0挂单中1已匹配2已上传凭证3已完成4申诉中5申诉成功6申诉失败
        $type = intval($request->post('type', 1));//1待转让 2转让中 3已转让 4取消/申诉
        if ($page <= 0 || !in_array($type, [1, 2, 3, 4])) $this->error('参数错误');
        switch ($type) {
            case 1:
                $where = 'status = 0 and is_exist = 1 and sell_uid=' . $this->auth->id;//未匹配
                break;
            case 2:
                $where = 'status in (1,2) and is_exist = 1 and sell_uid=' . $this->auth->id;//已支付
                break;
            case 3:
                $where = 'status = 3 and is_exist = 1 and sell_uid=' . $this->auth->id;//已完成
                break;
            case 4:
                $where = 'status = 4 and is_exist = 1 and sell_uid=' . $this->auth->id;//申诉中
                break;
        }
        $page_size = 10;
        $count = Db::name('purchase_order')->where($where)->count();
        $pages = ceil($count / $page_size);
        $offset = ($page - 1) * $page_size;
        $list = Db::name('purchase_order')
        ->field('id,price,create_time,purchase_id,recevice_time,pay_time,end_time,status,buy_uid,sell_uid,is_overtime')
        ->where($where)
        ->order('id desc')
        ->limit($offset, $page_size)
        ->select();
        $purchaseList = Db::name('purchase_list')->field('id,logo,name,min_price,max_price,days,type,rate')->select();
        foreach ($list as $k => $v) {
            foreach ($purchaseList as $kk => $vv) {
                if ($v['purchase_id'] == $vv['id']) {//匹配成功
                    $list[$k]['name'] = $vv['name'];
                    $list[$k]['devote'] = round($v['price'] - $vv['min_price'], 2);
                    $list[$k]['type'] = $vv['type'];
                    $list[$k]['reward'] = round($vv['days'] * $v['price'] * $vv['rate'] / 100, 2);
                    $list[$k]['in_days'] = $vv['days'];
                    $list[$k]['price'] = floatval($v['price']);
                    if ($vv['type'] == 1) {//普通2合约
                        $list[$k]['rate'] = $vv['days'] . '天/' . $vv['days'] * $vv['rate'] . '%';
                    } else {
                        $list[$k]['rate'] = $vv['days'] . '天/每天' . $vv['rate'] . '%';
                    }
                    if ($v['status'] == 1) {//上传凭证倒计时
                        $list[$k]['deal_time'] = Config::get('site.huzhuVoucher') * 60 - (time() - $v['recevice_time']) > 0 ? Config::get('site.huzhuVoucher') * 60 - (time() - $v['recevice_time']) : 0;
                    } elseif ($v['status'] == 2) {//确认订单倒计时
                        $list[$k]['deal_time'] = Config::get('site.huzhuConfirm') * 60 - (time() - $v['pay_time']) > 0 ? Config::get('site.huzhuConfirm') * 60 - (time() - $v['pay_time']) : 0;
                    }
                    $list[$k]['logo'] = 'http://' . $_SERVER['HTTP_HOST'] . $vv['logo'];
                    //上传凭证按钮 确认订单按钮 申诉按钮
                    $list[$k]['voucher_button'] = $v['status'] == 1 && $this->auth->id == $v['buy_uid'] ? 1 : 0;
                    $list[$k]['confirm_button'] = $v['status'] == 2 && $this->auth->id == $v['sell_uid'] ? 1 : 0;
                    $list[$k]['reply_button'] = $v['status'] == 2 ? 1 : 0;
                    $list[$k]['end_time'] = date('Y-m-d', $v['create_time'] + ($vv['days'] * 86400));
                }
            }
            if ($v['status'] == 1) {
                $list[$k]['create_time'] = date('Y-m-d', $v['recevice_time']);
            } elseif ($v['status'] == 2) {
                $list[$k]['create_time'] = date('Y-m-d', $v['pay_time']);
            } elseif ($v['status'] == 3) {
                $list[$k]['create_time'] = date('Y-m-d', $v['end_time']);
            } else {
                $list[$k]['create_time'] = date('Y-m-d', $v['create_time']);
            }
            unset($list[$k]['recevice_time']);
            unset($list[$k]['pay_time']);
            unset($list[$k]['buy_uid']);
            unset($list[$k]['sell_uid']);
        }
        $this->success('ok', [
            'count' => $count,
            'pages' => $pages,
            'list' => $list,
            'time' => 10
        ]);
    }
 */    
    
    //兑换微分单价渲染
    public function convertInfo(){
        $list = Db::name('K')->where('1=1')->field('value')->order('id desc')->select();
        
        return json(['code'=>1,'msg'=>'success','data'=>$list[0]['value']]);
    }
    
    //兑换微分
    public function convertPost(Request $request){
        $list = Db::name('K')->where('1=1')->field('value')->order('id desc')->select();
        $price = $list[0]['value'];//单价
        $num = getValue(abs($request->param('num', 0)),'int');
        $pass = trim($request->param('password', ''));
        
        $MConfig = new MConfig();
        $MConfig_val = $MConfig->readConfig('mainCurrency');
        
        $MMember = new MMember();
        $u_id = $this->userinfo['user_id'];
        if(empty($u_id)){
            return json(['code' => 2,'msg' => '出错啦，找不到用户']);
        }
        if(empty($num) || $pass == ''){
            return json(['code' => 2,'msg' => '请输入兑换数量或密码']);
        }
        $userInfo = $MMember->getInfo(['id'=>$u_id]);
        
        if($userInfo['pay_pass'] != md5($pass.'pay_passwd')){
            return json(['code' => 2,'msg' => '支付密码错误']);
        }
        
        $lessMoney = $price*$num;        
        //可兑换收益
        $all_assets = round($userInfo['profit_deposit'] + $userInfo['profit_recom'] + $userInfo['profit_team'], 2);
        if($all_assets < $lessMoney){
            return json(['code' => 2,'msg' => '可兑换收益不足，无法兑换']);
        }
        
        if (Cache::get('convertPost'.$u_id) == 2){
            return json(['code' => 2, 'msg' => '兑换执行中', 'data'=>[]]);
        }
        Cache::set('convertPost'.$u_id,2,10);
        
        try {
            Db::startTrans();
            
            $insert = [];
            if ($userInfo['profit_deposit'] <= $lessMoney){
                $decMoney = $lessMoney >= $userInfo['profit_deposit'] ? $userInfo['profit_deposit'] : $lessMoney;
                if ($decMoney > 0){
                    $MMember->where('id', $userInfo['id'])->setDec('profit_deposit',$decMoney);
                    $insert[] = [
                        'u_id' => $userInfo['id'],
                        'tel' => $userInfo['tel'],
                        'former_money' => $userInfo['profit_deposit'],
                        'change_money' => -$decMoney,
                        'after_money' => $userInfo['profit_deposit'] - $decMoney,
                        'message' => '兑换'.$MConfig_val,
                        'type' => 4,
                        'bo_time' => time(),
                        'status' => 20
                    ];
                }
                $lessMoney -= $decMoney;
                if ($lessMoney > 0){
                    $decMoney = $lessMoney >= $userInfo['profit_recom'] ? $userInfo['profit_recom'] : $lessMoney;
                    $MMember->where('id', $userInfo['id'])->setDec('profit_recom',$decMoney);
                    $insert[] = [
                        'u_id' => $userInfo['id'],
                        'tel' => $userInfo['tel'],
                        'former_money' => $userInfo['profit_recom'],
                        'change_money' => -$decMoney,
                        'after_money' => $userInfo['profit_recom'] - $decMoney,
                        'message' => '兑换'.$MConfig_val,
                        'type' => 5,
                        'bo_time' => time(),
                        'status' => 20
                    ];
                    $lessMoney -= $decMoney;
                    if ($lessMoney > 0){
                        $decMoney = $lessMoney >= $userInfo['profit_team'] ? $userInfo['profit_team'] : $lessMoney;
                        $MMember->where('id', $userInfo['id'])->setDec('profit_team',$decMoney);
                        $insert[] = [
                            'u_id' => $userInfo['id'],
                            'tel' => $userInfo['tel'],
                            'former_money' => $userInfo['profit_team'],
                            'change_money' => -$decMoney,
                            'after_money' => $userInfo['profit_team'] - $decMoney,
                            'message' => '兑换'.$MConfig_val,
                            'type' => 6,
                            'bo_time' => time(),
                            'status' => 20
                        ];
                        $lessMoney -= $decMoney;
                    }
                }
            }else{
                $decMoney = $lessMoney;
                if ($decMoney > 0) {
                    $MMember->where('id', $userInfo['id'])->setDec('profit_deposit',$decMoney);
                    $insert[] = [
                        'u_id' => $userInfo['id'],
                        'tel' => $userInfo['tel'],
                        'former_money' => $userInfo['profit_deposit'],
                        'change_money' => -$decMoney,
                        'after_money' => $userInfo['profit_deposit'] - $decMoney,
                        'message' => '兑换'.$MConfig_val,
                        'type' => 4,
                        'bo_time' => time(),
                        'status' => 20
                    ];
                    $lessMoney -= $decMoney;
                }
            }
            
            if ($lessMoney != 0) throw new Exception('兑换失败');
            Db::name('member_balance_log')->insertAll($insert);            

            Db::commit();
            Cache::set('convertPost'.$u_id,0);
            return json(['code' => 1,'msg' => '兑换成功']);
        } catch (Exception $exception) {
            Db::rollback();
            Cache::set('convertPost'.$u_id,0);
            return json(['code' => 2,'msg' => '兑换失败'.$exception->getMessage()]);
        }
    }
    
    
    /**
     * 子币明细
     * @param Request $request
     */
    public function coinList(Request $request){
        $MMember = new MMember();
        $u_id = $this->userinfo['user_id'];
        if(empty($u_id)){
            return json(['code' => 2,'msg' => '出错啦，找不到用户']);
        }
        $coin = $MMember->getValue(['id'=>$u_id], 'coin');
        
        $page = $request->post('page', 1);
        if ($page <= 0) return json(['code' => 2,'msg' => '参数错误']);

        $count = Db::name('member_balance_log')->where('u_id', $u_id)->where(['type'=>11])->count();
        
        $page_size = 3;
        $pages = ceil($count / $page_size);
        $offset = ($page - 1) * $page_size;
        $list = Db::name('member_balance_log')
        ->field('change_money,message,bo_time')
        ->where('u_id', $u_id)
        ->where(['type'=>11])
        //->where($status)//'status',
        ->order('id desc')
        ->limit($offset, $page_size)
        ->select();
        foreach ($list as $k => $v) {
            $list[$k]['time'] = date('Y-m-d H:i:s', $v['bo_time']);
            $list[$k]['num'] = $v['change_money'] > 0 ? '+' . $v['change_money'] : $v['change_money'];
            unset($list[$k]['change_money']);
        }
        $data = [
            'count' => $count,
            'pages' => $pages,
            'list' => $list,
            'coin' => $coin
        ];
        
        return json(['code' => 1,'msg' => 'success', 'data'=>$data]);
        
    }
    
        
    //匹配中订单拆分
    public function exchange(Request $request){
        $MMember = new MMember();
        $u_id = $this->userinfo['user_id'];
        
        $id = intval($request->post('id', 0));//指定ID
        $type = intval($request->post('type', 1));//兑换类型  1主币 2辅币
        if(empty($id)){
            return json(['code'=>SIGN_ERROR,'msg'=>'参数错误']);
        }
        if($type != 1 && $type != 2){
            return json(['code'=>SIGN_ERROR,'msg'=>'兑换类型错误']);
        }
        
        $password = getValue($request->post('password', ''));//密码
        $member_info = $MMember->getInfo(['id'=>$u_id]);
        
        if($member_info['pay_pass'] != md5($password.'pay_passwd')){
            return json(['code'=>SIGN_ERROR,'msg'=>'密码错误']);
        }
        
        $info = Db::name('member_mutualaid')
        ->where(['id'=>$id])->field('orderNo,status,new_price as price')
        ->find();

        if(empty($info) || $info['status'] != 2){
            return json(['code'=>SIGN_ERROR,'msg'=>'订单状态出错啦']);
        }
        
        if (Cache::get('exchange'.$u_id) == 2){
            return json(['code' => 2, 'msg' => '请勿频繁操作', 'data'=>[]]);
        }
        Cache::set('exchange'.$u_id,2,10);
        
        $MConfig = new MConfig();
        $MConfig_val = $MConfig->readConfig(['mainCurrency','auxiliaryCurrency'],2);
        
        try {
            Db::startTrans();
            //修改互助订单状态
            Db::name('mutualaid_order')->where('p_id',$id)->update(['is_exist'=>0,'status'=>11]);
            
            Db::name('member_mutualaid')->where('id',$id)->update(['is_exist'=>0,'deal_type'=>11,'status'=>11]);
            
            
            if($type == 1){
                Db::name('member_list')->where('id', $u_id)->update([
                    'balance'=>Db::raw('balance +'.$info['price'])
                ]);
                
                //生成
                Db::name('member_balance_log')->insert([
                    'u_id' => $u_id,
                    'tel' => $member_info['tel'],
                    'o_id' => $id,
                    'change_money' => $info['price'],
                    'former_money' => $member_info['balance'],
                    'after_money' => $member_info['balance']+$info['price'],
                    'message' => '订单兑换'.$MConfig_val[0].'，订单编号'.$info['orderNo'],
                    'type' => 2,
                    'bo_time' => time(),
                    'status' => 304
                ]);
            }else{
                Db::name('member_list')->where('id', $u_id)->update([
                    'coin'=>Db::raw('coin +'.$info['price'])
                ]);
                
                //生成
                Db::name('member_balance_log')->insert([
                    'u_id' => $u_id,
                    'tel' => $member_info['tel'],
                    'o_id' => $id,
                    'change_money' => $info['price'],
                    'former_money' => $member_info['coin'],
                    'after_money' => $member_info['coin']+$info['price'],
                    'message' => '订单兑换'.$MConfig_val[1].'，订单编号'.$info['orderNo'],
                    'type' => 11,
                    'bo_time' => time(),
                    'status' => 304
                ]);
            }
            
            
            Db::commit();
            //Cache::set('shelves'.$u_id,0,1);
            return json(['code' => 1,'msg' => '兑换成功']);
        } catch (Exception $exception) {
            Db::rollback();
            //Cache::set('shelves'.$u_id,0,1);
            return json(['code' => 2,'msg' => '兑换失败'.$exception->getMessage()]);
        }
    }
    
    
}

