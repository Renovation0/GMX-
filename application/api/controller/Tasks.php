<?php

namespace app\api\controller;

use think\Config;
use think\Db;
use think\Exception;
use app\api\model\MConfig;
use think\facade\Cache;

class Tasks extends Base
{       
    //压单
    public function singleField(){
        $this->wlog('开始匹配压单' . date('Y-m-d H:i:s', time()), 'singleField');
        
        $mu_info = Db::name('mutualaid_list')->where('single_field = 2')->find();
        
        if(!empty($mu_info)){
            $result = Db::name('mutualaid_order')->where('status = 0 AND is_exist = 1 AND is_overtime = 1')->update(['is_overtime'=>0]);
            if($result){
                $this->wlog('释放上次压单成功' . date('Y-m-d H:i:s', time()), 'singleField');
            }else{
                $this->wlog('释放上次压单失败' . date('Y-m-d H:i:s', time()), 'singleField');
            }
            
            $order_list = Db::name('mutualaid_order')->where('status = 0 AND is_exist = 1 AND purchase_id ='.$mu_info['id'])->field('sell_uid')->group('sell_uid')->select();
//             var_dump(Db::name('mutualaid_order')->getLastSql());
//             echo '<pre/>';
//             var_dump($order_list); echo '<br/>';
            
            $id_arr = array_column($order_list, 'sell_uid');
            var_dump($id_arr);
            
            $results = Db::name('mutualaid_order')->whereNotIn('sell_uid', $id_arr)->where('status = 0 AND is_exist = 1')->update(['is_overtime'=>1]);
            if($results){
                $this->wlog('压单成功' . date('Y-m-d H:i:s', time()), 'singleField');
            }else{
                $this->wlog('压单失败' . date('Y-m-d H:i:s', time()), 'singleField');
            }
        }else{
            Db::name('mutualaid_order')->where('status = 0 AND is_exist = 1 AND is_overtime = 1')->update(['is_overtime'=>0]);
            
            $this->wlog('未检测到压单' . date('Y-m-d H:i:s', time()), 'singleField');
        }
    }

    //匹配失败退还能量
    public function ReturnOfFailedAppointment(){
        set_time_limit(0);
        //运行计时器 开始时间
        $stime=microtime(true);
        
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig('mainCurrency');
        
        //查询当天所有的匹配失败的会员预购记录
        $this->wlog('开始匹配失败信息查询' . date('Y-m-d H:i:s', time()), 'ReturnOfFailedAppointment');
        
        $logList = Db::name('mutualaid_log')->alias('a')
        ->join('member_list b', 'a.uid=b.id', 'left')
        //->join('mutualaid_list c', 'a.p_id=c.id', 'left')
        //->where('a.uid', $this->auth->id)
        ->whereTime('a.time', 'today')
        ->where(' a.status = 2 And a.is_return = 3')->field('a.*,b.id as user_id,b.tel,b.balance,b.f_uid')//,c.award,c.f_award
        ->select();
        
        if(empty($logList)){
            $this->wlog('无匹配失败信息。' . date('Y-m-d H:i:s', time()), 'ReturnOfFailedAppointment');exit();
        }
        
        $arr_id = array_column($logList, 'id');

        $data_insert = [];
        
        $auxiliaryCurrency = $config_val;//Config::get('site.auxiliaryCurrency');
        $count = 0;
        try {
            Db::startTrans();
            foreach($logList as $k=>$v){
                //预约失败返还微分
                Db::name('member_list')->where('id', $v['uid'])->update([
                  'balance' => Db::raw('balance +' . $v['num']),
                ]);
                $data_insert[] = [
                    'u_id' => $v['uid'],
                    'tel' => $v['tel'],
                    'former_money' => $v['balance'],
                    'change_money' => $v['num'],
                    'after_money' => $v['balance'] + $v['num'],
                    'message' => '匹配失败退还'.$auxiliaryCurrency,
                    'type' => 2,
                    'bo_time' => time(),
                    'status' => 210
                ];
                
/*                 if(!empty($v['f_uid'])){
                    //推荐人获赠
                    Db::name('member_list')->where('id', $v['f_uid'])->update([
                        'balance' => Db::raw('balance +' . $v['f_award']),
                    ]);
                    $f_use = Db::name('member_list')->where('id', $v['f_uid'])->field('id,tel,balance')->find();
                    
                    $data_inserts[] = [
                        'u_id' => $f_use['id'],
                        'tel' => $f_use['tel'],
                        'former_money' => $f_use['balance'],
                        'change_money' => $v['f_award'],
                        'after_money' => $f_use['balance'] + $v['f_award'],
                        'message' => '直推预约失败赠送'.$v['f_award'].$auxiliaryCurrency,
                        'type' => 2,
                        'bo_time' => time(),
                        'status' => 211
                    ];
                }
 */                $count++;
            }
            Db::name('member_balance_log')->insertAll($data_insert);
            //Db::name('member_balance_log')->insertAll($data_inserts);
            Db::name('mutualaid_log')->whereIn('id', $arr_id)->setField('is_return',1);
            
            Db::commit();
            $this->wlog('返还金额结束,成功匹配' . $count . '条记录'. date('Y-m-d H:i:s', time()), 'ReturnOfFailedAppointment');
        } catch (Exception $exception) {
            Db::rollback();
            $this->wlog('返还金额结束,返还失败' . $exception->getMessage() . date('Y-m-d H:i:s', time()), 'ReturnOfFailedAppointment');
        }            
    }
    
    /** 定时任务 字段匹配互助产品 1min/次 弃 手动抢购
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function AppointmentMatching()
    {           
        set_time_limit(0);
        //运行计时器 开始时间
        $stime=microtime(true);
       
        //查询所有的匹配中的会员预购记录
        $this->wlog('开始互助匹配' . date('Y-m-d H:i:s', time()), 'AppointmentMatching');
        
        if (Cache::get('AppointmentMatching') == 2){
            return json(['code' => 2, 'msg' => '请勿频繁操作', 'data'=>[]]);
        }
        Cache::set('AppointmentMatching',2,5);    


        //查询所有挂单中的设备 官网
        $j_time = time() - 3 * 3600;

        $now_time = strtotime(date('Ymd His', time())) - strtotime(date('Ymd', time()));//当前时分秒时间戳
        
        $now_time = time();
        
        $MConfig = new MConfig();
        $MConfig_val = $MConfig->readConfig(['pruchaseOpen','dayMaxPurchase'],2);
        
        $cf_time = $MConfig_val[0] * 60;//达到时间的N分钟后才触发

        $mutu_list = Db::name('mutualaid_list')->where('status = 1')->select();
        foreach ($mutu_list as $k => $v){
            
            $sta_time = date('Y-m-d H:i:s', $v['sta_time']);
            $abc=substr($sta_time,-8);
            $sta_time = strtotime(date('Y-m-d '.$abc, time())) + $cf_time;
            
            $end_time = date('Y-m-d H:i:s', $v['end_time']);
            $cba=substr($end_time,-8);
            $end_time = strtotime(date('Y-m-d '.$cba, time()));
            
            if($now_time >= $sta_time && $now_time < $end_time){
                
            }else{
                unset($mutu_list[$k]);
            }
        }
        
        $purchase = array_values($mutu_list);

        if (count($purchase) == 0) {
            $this->wlog('无进行中的设备' . date('Y-m-d H:i:s', time()), 'AppointmentMatching');
            Cache::set('AppointmentMatching',0,1); 
            die;
        }

        $pur_arr = array_column($purchase, 'id');
        //$pur_arr = array('0'=>6);
        //var_dump($pur_arr);echo '<br/>';echo '<br/>';echo '<br/>';
        
        //当天已中奖所有用户
        $dayPurchase_list = Db::name('mutualaid_log')->whereTime('time', 'today')->where('status = 1')->field('uid,count(1) AS dayPurchase')->group('uid')->select();
        
        //查询当前场次匹配中所有已抢购预约记录
        $logList = Db::name('mutualaid_log')->alias('a')
        ->join('zm_member_list b', 'a.uid=b.id', 'left')
        //->where('a.uid', $this->auth->id)
        ->where('a.p_id = '.$pur_arr[0].' AND a.status = 0 And a.order_status = 1')->field('a.*,b.id as user_id,b.tel,b.status as user_status,b.balance,b.purchase_status as user_purchase_status')
        ->select();
        
        //var_dump($logList);echo '<br/>';echo '<br/>';echo '<br/>';

        //获取当天最大中奖次数
        $maxPurchase = $MConfig_val[1];//Config::get('site.dayMaxPurchase');
        
        $id_arr = [];
        
        //组合数据
        if(!empty($logList)){
            foreach($logList as $keys =>$vaules){
                $logList[$keys]['dayPurchase'] = 0;
                foreach($dayPurchase_list as $keyss=>$vauless){
                    if($vaules['uid'] == $vauless['uid']){
                        $logList[$keys]['dayPurchase'] = $vauless['dayPurchase'];
                        //echo $vaules['uid'].'---'.$vauless['uid'].'----'.$vauless['dayPurchase'];echo '<br/>';echo '<br/>';
                        unset($dayPurchase_list[$keyss]);
                        break;
                    }
                }
                
                if ($vaules['user_status'] != 2 || $vaules['user_purchase_status'] == 3 || $logList[$keys]['dayPurchase'] >= $maxPurchase) {//一定失败
                    //-
                    $id_arr[] = $vaules['id'];
                    //Db::name('purchase_log')->where('id', $vaules['id'])->setField('status', 2);//默认匹配失败
                    unset($logList[$keys]);
                }
            }
        }       
        //var_dump($logList);exit();
        //查询所有挂单中的订单 price 匹配设备
        $order = Db::name('mutualaid_order')->alias('a')
        ->join('zm_member_list b', 'a.sell_uid=b.id', 'left')
        ->where('b.status != 3 AND a.status = 0 and a.is_exist = 1 and a.is_overtime = 0 and a.purchase_id ='.$pur_arr[0])
        ->field('a.id,a.p_id,a.purchase_id,a.price,a.sell_uid,a.appoint_log,a.purchase_no')
        ->select();        

        try {
            Db::startTrans();
           
            //匹配开始的设备 purchase_status = 1 and 
            $data_insert = [];

            if (count($logList) == 0 && count($id_arr) != 0) {

                //修改一定失败的记录和未参与抢购记录
                Db::name('mutualaid_log')->whereIn('id', $id_arr)->update(['status'=>2,'is_return'=>3]);                
                Db::commit();
                Cache::set('AppointmentMatching',0,1); 
                $this->wlog('预约记录返还' . date('Y-m-d H:i:s', time()), 'AppointmentMatching');die;
            }               

            if (count($logList) == 0) {
                Cache::set('AppointmentMatching',0,1); 
                $this->wlog('无匹配中的记录' . date('Y-m-d H:i:s', time()), 'AppointmentMatching');
                die;
            }

            //if(count($outLogList) != 0){
            if(count($id_arr) != 0){
                //修改一定失败的记录和未参与抢购记录
                Db::name('mutualaid_log')->whereIn('id', $id_arr)->update(['status'=>2,'is_return'=>3]);
            }

            // var_dump($logList);die;
            //先随机排序 再从新排序 优先级从大到小 有限匹配前面的记录
            $aa = [];
            shuffle($logList);
            if (count($logList) > 0) {
                foreach ($logList as $key => $row) {
                    if ($row['user_purchase_status'] == 1) {
                        $aa[] = $row;
                        unset($logList[$key]);
                    }
                }
            }
            if (count($logList) > 0) {
                foreach ($logList as $key => $row) {
                    $aa[] = $row;
                    unset($logList[$key]);
                }
            }
            $logList = $aa;
            
            //查询所有挂单中的订单 price 匹配设备
            //$order = Db::name('purchase_order')->where('status = 0 and is_exist = 1')->field('id,p_id,purchase_id,price,sell_uid,appoint_log')->select();
            //匹配purchase id 关联设备id
            //处理挂单 匹配用户 匹配成功修改订单信息 修改状态信息
            $count = 0;
            $verify = 0;
            $phone = '';
            
            $Success_log_id = [];
            $Success_user_pid = [];
            $Success_user_pur_uid = [];
            $Loss_log_id = [];
            
            //获取预约记录id
            $arrLogId = array_column($logList,'id');
            // var_dump($order);die;
            foreach ($logList as $k => $v) {
                if (count($order) != 0) {//如果存在还在匹配中的订单
                    foreach ($order as $kk => $vv) {
                        $verify = 0;
                        if ($vv['sell_uid'] == $v['uid']) {//不能抢购自己的宠物
                            continue;//拿新的订单去匹配
                        }
                        //预约记录存在才会跳过 否则就算指定了也匹配给其他玩家
                        //根据订单验证 是否存在匹配订单
                        if ($v['appoint_order'] != 0 && $v['appoint_order'] != $vv['id']) {//指定操作 如果有指定但是指定的不是自己
                            continue;
                        }
                        //更加预约记录验证 是否存在该预约记录
                        if ($vv['appoint_log'] != 0 && $vv['appoint_log'] != $v['id'] && in_array($vv['appoint_log'],$arrLogId)){
                            continue;
                        }                       
                        
                        if ($v['p_id'] == $vv['purchase_id']) {//匹配成功
                            $Success_log_id[] = $v['id'];
                            $Success_user_pid[] = $vv['p_id'];
                            $Success_user_pur_uid[] = $v['uid'];

                            Db::name('mutualaid_order')->where('id', $vv['id'])->update([
                                'buy_uid' => $v['uid'],
                                'buy_user' => $v['tel'],
                                'status' => 1,
                                'recevice_time' => time()
                            ]);
                            
                            Db::name('mutualaid_log')->where('id', $v['id'])->update([
                                'purchase_no' => $vv['purchase_no'],
                                'jy_status' => 1,
                                'jy_id' => $vv['id']
                            ]);
                            unset($order[$kk]);//订单表删除该条数据
                            $count++;
                            $verify = $v['id'];
                            $phone = $phone == '' ? $v['tel'] : $phone . ',' . $v['tel'];
                            break;//匹配成功跳出订单循环
                        }
                    }
                    if ($verify == 0) {//如果循环完订单还是没找到订单 则匹配失败
                        $Loss_log_id[] = $v['id'];
                        Db::name('member_list')->where('id', $v['uid'])->update([
                            'fail_num' => Db::raw('fail_num + 1')
                        ]);
                        $verify = 0;
                    }
                } else {
                    $Loss_log_id[] = $v['id'];
                    Db::name('member_list')->where('id', $v['uid'])->update([
                        'fail_num' => Db::raw('fail_num + 1')
                    ]);
                }
            }

            //修改记录
            Db::name('mutualaid_log')->whereIn('id', $Success_log_id)->setField('status', 1);//匹配成功
            Db::name('member_mutualaid')->whereIn('id', $Success_user_pid)->setField('status', 3);
            Db::name('member_list')->whereIn('id', $Success_user_pur_uid)->setField('fail_num', 0);

            Db::name('mutualaid_log')->whereIn('id', $Loss_log_id)->update(['status'=>2,'is_return'=>3]);//匹配失败'status', 2
            
            $etime=microtime(true);//获取程序执行结束的时间
            $total=$etime-$stime;
            $total=round($total,2);
            var_dump($total);
            
            Db::commit();
            Cache::set('AppointmentMatching',0,1); 
            $this->wlog('互助匹配结束,成功匹配' . $count . '条记录'.$phone . date('Y-m-d H:i:s', time()), 'AppointmentMatching');
            if ($phone != '') {
                $Login = new Login();
                $Login->sendPostNew($phone, 1);
            }
        } catch (Exception $exception) {
            Db::rollback();
            Cache::set('AppointmentMatching',0,1); 
            $this->wlog('互助匹配结束,匹配失败' . $exception->getMessage() . date('Y-m-d H:i:s', time()), 'AppointmentMatching');
        }
        
        $etime=microtime(true);//获取程序执行结束的时间
        $total=$etime-$stime;
        $total=round($total,2);
        
        $this->wlog('总用时'. $total.'秒');
    }
    
    


    /** 宠物设备升值及到期 当晚凌晨就可以升值 1天 凌晨/次
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function revaluePurchasegewqfdsa()
    {
    
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig('effectiveUserAssets');
        
/*         $xiaoshi = date("H");//当前小时
        if ($xiaoshi > 1 && $xiaoshi < 23){
            $this->wlog('错误升值' . date('Y-m-d H:i:s', time()), 'revaluePurchase');die;   360
        } */
        $now_time = time();
        $this->wlog('开始升值处理' . date('Y-m-d H:i:s', time()), 'revaluePurchase');
        
        //可以升值的宠物//user_purchase  deal_type = 1 and 
        $list = Db::name('member_mutualaid')->
        where('is_exist = 1 and status = 1 and up_status = 1 and examine_status = 0  and  sta_time < ' . ($now_time - 0).' and (last_time = 0 OR last_time != 0)')
        ->select(); 
        //->whereOr('deal_type = 1 and is_exist = 1 and status = 1 and up_status = 1 and last_time != 0 and examine_status = 0  and last_time < ' . ($now_time - 3600))           
/*         var_dump(Db::name('member_mutualaid')->getLastSql());echo '<br/>';echo '<br/>';        
        var_dump(count($list));echo '<br/>';echo '<br/>'; exit(); */

        $purchase = Db::name('mutualaid_list')->field('id,name')->select();
        $count = 0;
        if (count($list) > 0) {
            try {
                Db::startTrans();
                $data = [];
                $user_arr = array_column($list, 'uid');                

                $userList = Db::name('member_list')->whereIn('id', $user_arr)->field('id,tel,balance')->select();
                $now_time = time();
                foreach ($list as $k => $v) {
                                        
                    if($v['up_time'] == 0){
                        $sz_sta_time = $now_time - $v['sta_time'];
                    }else{
                        $sz_sta_time = $now_time - ($v['sta_time']+$v['up_time']*86400);
                    }
                    //var_dump($sz_sta_time);echo '<br/>';
                    
                    //if($sz_sta_time >= 86400 && $sz_sta_time <= 88200){
                    //if(($sz_sta_time >= 86400 && $sz_sta_time <= 88205) || $sz_sta_time >= 172800){
                    if($sz_sta_time >= 86400){
                    //if($now_time-$sta_time_ <= 1800 ){
                        
                        foreach ($purchase as $kk => $vv) {
                            if ($v['purchase_id'] == $vv['id']) {
                                $v['name'] = $vv['name'];
                                //$v['up_appoint'] = $vv['up_appoint'];
                                //$v['sale_expend'] = $vv['sale_expend'];
                                //continue;
                                break;
                            }
                        }
                        foreach ($userList as $kkk => $vvv) {
                            if ($v['uid'] == $vvv['id']) {
                                $v['tel'] = $vvv['tel'];
                                $v['balance'] = $vvv['balance'];
                                //$v['profit_deposit'] = $vv['profit_deposit'];
                                //$v['pets_assets'] = $vv['pets_assets'];
                                break;
                            }
                        }

                        //非超时宠物
                        $reward = intval($v['get_price'] * $v['rate'] / 100);
                        if ($v['days'] > $v['up_time']) {//可以升值
                            //处理升值
                            Db::name('member_mutualaid')->where('id', $v['id'])->update([
                                'last_time' => time(),
                                'up_time' => Db::raw('up_time + 1'), //次数+1
                                'new_price' => Db::raw('new_price +' . $reward) //更新价格
                            ]);
                        
                            $total_price = $v['new_price'] + $reward;
                            $data[] = [
                                'u_id' => $v['uid'],
                                'tel' => $v['tel'],
                                'former_money' => $v['new_price'],//$v['pets_assets'],//$v['profit_deposit']
                                'change_money' => $reward,
                                'after_money' => $total_price,//$v['new_price'] + $reward,//$v['pets_assets'] + $reward,
                                'message' => '升值收益',
                                'message_e' => 'Appreciation gains',
                                'type' => 8,
                                'bo_time' => time(),
                                'status' => 12
                            ];
                            
                            $count++;
                        }
                        if ($v['days'] == $v['up_time'] + 1 || $v['days'] <= $v['up_time']) {//最后一次升值 或者已经升值结束  || $v['days'] <= $v['up_time']

                            Db::name('member_mutualaid')->where('id', $v['id'])->update([
                                'deal_type' => 2,
                                'status' => 2,
                                'end_time' => time()
                            ]);
                            //$order_status = 0;
                            
                            Db::name('member_list')->where('id', $v['uid'])->update([
                                'balance' => Db::raw('balance +'.$total_price)
                            ]);
                            
                            
                            $data[] = [
                                'u_id' => $v['uid'],
                                'tel' => $v['tel'],
                                'former_money' => $v['balance'],
                                'change_money' => $total_price,
                                'after_money' => $v['balance'] + $total_price,
                                'message' => '产品收益'.$total_price,
                                'message_e' => $v['name'].' Get investment income '.$total_price,
                                'type' => 2,
                                'bo_time' => time(),
                                'status' => 502
                            ];
                        }
                    
                    
                    }
                    //直推收益
                    //$getAssets = $config_val;//Config::get('site.effectiveUserAssets');
                    //$assets = Db::name('member_mutualaid')->where('uid =' . $v['uid'] . ' and status in (1,2,3)')->sum('new_price');//总资产
                    //$all_profit = $reward;
                    //if ($all_profit > 0 && $assets >= $getAssets) {
                        //三级收益
                        //$data = $this->ztReward($all_profit, $v['uid'], $data);
                        //团队收益
                        //$this->teamReward($all_profit, $v['uid']);
                    //}
                }
                //var_dump($data);
                if (count($data) > 0)  $res = Db::name('member_balance_log')->insertAll($data);
                // var_dump($res);
                // die;
                Db::commit();
                $this->wlog('升值处理成功数量:' . $count .'，时间：'. date('Y-m-d H:i:s', time()), 'revaluePurchase');
            } catch (Exception $exception) {
                echo 'error';
                Db::rollback();
                $this->wlog('升值处理失败'.$exception->getMessage() . date('Y-m-d H:i:s', time()), 'revaluePurchase');
            }
        }
        $this->wlog('升值处理结束:' . date('Y-m-d H:i:s', time()), 'revaluePurchase');
    }


    private function makeRand($num = 9)
    {
        mt_srand((double)microtime() * 1000000);//用 seed 来给随机数发生器播种。
        $strand = str_pad(mt_rand(1, 99999), $num, "0", STR_PAD_LEFT);
        if (Db::name('member_mutualaid')->where('orderNo', 'MT' . date('Ymd') . $strand)->count() == 0) {
            return 'MT' . date('Ymd') . $strand;
        }
        $this->makeRand();
    }

    //直推奖励 三级收益
    public function ztReward($all_profit, $uid, $data)
    {   
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig(['ztOneRate','ztTwoRate','ztThreeRate','effectiveUserAssets'],2);
        $config['ztOneRate'] = $config_val[0];//Config::get('site.ztOneRate');
        $config['ztTwoRate'] = $config_val[1];//Config::get('site.ztTwoRate');
        $config['ztThreeRate'] = $config_val[2];//Config::get('site.ztThreeRate');
        //上级奖励
        $f_uid_all = Db::name('member_list')->where('id', $uid)->value('f_uid_all');
        $user_tel = Db::name('member_list')->where('id', $uid)->value('tel');
        if ($f_uid_all != '') {
            $f_user = Db::name('member_list')->where('id in (' . $f_uid_all . ')')->field('id,tel,profit_recom,level')->order('id desc')->limit(3)->select();
            for ($i = 0; $i < count($f_user); $i++) {
                /* if ($i == 0) {//diy第一代
                    $one_era = Db::name('member_level')->where(['id'=>$f_user[$i]['level']])->value('one_era');
                    $rate = $one_era;//$config['ztOneRate'];
                } elseif ($i == 1) {
                    $two_era = Db::name('member_level')->where(['id'=>$f_user[$i]['level']])->value('two_era');
                    $rate = $two_era;//$config['ztTwoRate'];
                } else {
                    $three_era = Db::name('member_level')->where(['id'=>$f_user[$i]['level']])->value('three_era');
                    $rate = $three_era;//$config['ztThreeRate'];
                    } */
                if ($i == 0) {//diy第一代
                    if($f_user[$i]['level'] != 0){
                        $one_era = Db::name('member_level')->where(['id'=>$f_user[$i]['level']])->value('one_era');
                        $rate = $one_era;//$config['ztOneRate'];
                    }else{
                        $rate = $config['ztOneRate'];
                    }
                    $str = '直推';
                } elseif ($i == 1) {
                    if($f_user[$i]['level'] != 0){
                        $two_era = Db::name('member_level')->where(['id'=>$f_user[$i]['level']])->value('two_era');
                        $rate = $two_era;//$config['ztTwoRate'];
                    }else{
                        $rate = $config['ztTwoRate'];
                    }
                    $str = '间推';
                } else {
                    if($f_user[$i]['level'] != 0){
                        $three_era = Db::name('member_level')->where(['id'=>$f_user[$i]['level']])->value('three_era');
                        $rate = $three_era;//$config['ztThreeRate'];
                    }else{
                        $rate = $config['ztThreeRate'];
                    }
                    $str = '三级';
                }
                
                $user = Db::name('member_list')->where('id', $f_user[$i]['id'])->field('id,tel,profit_recom')->find();
                //持有资产达到要求拿推荐奖励
                $getAssets = $config_val[3];//Config::get('site.effectiveUserAssets');
                $assets = Db::name('member_mutualaid')->where('uid =' . $user['id'] . ' and status in (1,2,3) and is_exist = 1')->sum('new_price');//总资产
                if ($rate > 0 && $assets >= $getAssets) {
                    $reward = $all_profit * $rate / 100;
                    if ($reward > 0) {
                        Db::name('member_list')->where('id', $user['id'])->update([
                            'profit_recom' => Db::raw('profit_recom +' .$reward),
                            'census_profit_recom' => Db::raw('census_profit_recom +' .$reward)
                            ]);
                        $data[] = [
                            'u_id' => $user['id'],
                            'tel' => $user['tel'],
                            'former_money' => $user['profit_recom'],
                            'change_money' => $reward,
                            'after_money' => $user['profit_recom'] + $reward,
                            'message' => '推荐收益,--'.substr_replace($user_tel, '****', 3, 4),
                            'type' => 5,
                            'bo_time' => time(),
                            'status' => 502
                        ];
                    }
                }
            }
        }
        return $data;
    }

    //团队奖励
    public function teamReward($reward, $uid)
    {   
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig('effectiveUserAssets');
        $userInfo = Db::name('member_list')->where('id', $uid)->field('id,f_uid_all')->find();
        
        $list = explode(',', $userInfo['f_uid_all']);
        //删除上级三代
        array_pop($list);
        array_pop($list);
        array_pop($list);
        
        $userInfo['f_uid_all'] = implode(',', $list);
        
        if ($userInfo['f_uid_all'] != '') {
            $sql = "SELECT `id`,`profit_team`,`level`,`tel` FROM zm_member_list WHERE `level` > 0 and id in (" . $userInfo['f_uid_all'] . ") ORDER BY FIND_IN_SET( id, '" . $userInfo['f_uid_all'] . "')";
            $userList = Db::query($sql);
            $base_level = 0;
            $data = [];
            $level = Db::name('member_level')->select();
            $rate = 0;
            foreach ($userList as $k => $v) {//烧伤
                //if ($v['level'] > $base_level) {
                foreach ($level as $kk => $vv) {
                    if ($vv['id'] == $v['level']) {
                        $rate = $vv['team_income_ratio'];
                        break;
                    }
                }
                $money = $reward * $rate / 100;
                //持有资产达到要求拿推荐奖励
                $getAssets = $config_val;//Config::get('site.effectiveUserAssets');
                $assets = Db::name('member_mutualaid')->where('uid =' . $v['id'] . ' and status in (1,2,3)  and is_exist = 1')->sum('new_price');//总资产
                if ($money > 0 && $assets >= $getAssets) {
                    // Db::name('user')->where('id', $v['id'])->setInc('profit_team', $money);
                    Db::name('member_list')->where('id', $v['id'])->update([
                            'profit_team' => Db::raw('profit_team +' .$money),
                            'census_profit_team' => Db::raw('census_profit_team +' .$money)
                            ]);
                    $data[] = [
                        'u_id' => $v['id'],
                        'tel' => $v['tel'],
                        'former_money' => $v['profit_team'],
                        'change_money' => $money,
                        'after_money' => $v['profit_team'] + $money,
                        'message' => '团队收益',
                        'type' => 6,
                        'bo_time' => time(),
                        'status' => 13
                    ];
                }
                //$base_level = $v['level'];
                $rate = 0;
            }
            //}
// var_dump($data);
            if (count($data) > 0) Db::name('member_balance_log')->insertAll($data);
        }
    }

    /** 法币上传凭证超时
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function CoinUploadVoucherTimeout()
    {
        $this->wlog('上传凭证超时' . date('Y-m-d H:i:s', time()), 'CoinUploadVoucherTimeout');
        $now_time = time();
        //$coinVoucher = Config::get('site.coinVoucher');
        $MConfig = new MConfig();
        $coinVoucher = $MConfig->readConfig('JY_UPVOUCHER');
        //已经上传凭证 超时 订单还原 卖家金额返还 买家账号冻结
        $list = Db::name('order_coin')->where('status=1 and recevice_time <' . ($now_time - $coinVoucher * 60))->select();
        //var_dump($list);die;
        if (count($list)) {
            $sell_user_arr = array_column($list, 'sell_uid');
            $buy_user_arr = array_column($list, 'buy_uid');
            $id_arr = array_column($list, 'id');
            $sell_user = Db::name('member_list')->whereIn('id', $sell_user_arr)->field('id,tel,balance,sell_limit')->select();
            //var_dump($sell_user);die;
            try {
                Db::startTrans();
                //冻结上传凭证超时的用户
                Db::name('member_list')->whereIn('id', $buy_user_arr)->setField('status', 3);
                //订单状态改变
                Db::name('order_coin')->whereIn('id', $id_arr)->update([
                    'sell_uid' => 0,
                    'sell_user' => '',
                    'recevice_time' => 0,
                    'status' => 0,
                    //'recharge' => 0
                ]);
                $insert = [];
                foreach ($list as $k => $v) {
                    foreach ($sell_user as $kk => $vv) {
                        if ($v['sell_uid'] == $vv['id']) {
                            $v['balance'] = $vv['balance'];
                            $v['tel'] = $vv['tel'];
                            $v['sell_limit'] = $vv['sell_limit'];
                            continue;
                        }
                    }

                    //金额返还 扣除冻结
                    $res = Db::name('member_list')->where('id', $v['sell_uid'])->update([
                        'balance' => Db::raw('balance + ' . ($v['num'] + $v['recharge'])),
                        //'sell_limit' => Db::raw('sell_limit +' . $v['num']),
                        'frozen_dot' => Db::raw('frozen_dot -' . $v['num'])
                    ]);

                    //Db::name('user')->where('id', $v['sell_uid'])->setInc('balance', $v['num'] + $v['recharge']);
                     $insert[] = [
                        'u_id' => $v['sell_uid'],
                        'tel' => $v['tel'],
                        'former_money' => $v['balance'],
                        'change_money' => $v['num'] + $v['recharge'],
                        'after_money' => $v['num'] + $v['recharge'] + $v['balance'],
                        'type' => 101,
                        'status' => 232,
                        'message' => 'YKB订单上传凭证超时返还',
                        'bo_time' => time()
                     ];
                     $insert[] = [
                         'u_id' => $v['buy_uid'],
                         'tel' => $v['buy_user'],
                         'former_money' => 0,
                         'change_money' => $v['num'],
                         'after_money' => 0,
                         'type' => 102,
                         'status' => 236,
                         'message' => 'YKB订单上传凭证超时取消',
                         'bo_time' => time()
                     ];
                     $insert_coin[] = [
                        'orderNo' => $v['orderNo'],
                        'order_id' => $v['id'],
                        'uid' => $v['sell_uid'],
                        'phone' => $v['tel'],
                        'message' => 'YKB订单上传凭证超时返还',
                        'time' => time()                        
                     ];
                     $insert_coin[] = [
                         'orderNo' => $v['orderNo'],
                         'order_id' => $v['id'],
                         'uid' => $v['buy_uid'],
                         'phone' => $v['buy_user'],
                         'message' => 'YKB订单上传凭证超时进入重新匹配',
                         'time' => time()
                     ];
                }

                if (count($insert) > 0) {
                    $res= Db::name('member_balance_log')->insertAll($insert);
                }
                if(count($insert_coin) > 0){
                    Db::name('order_coin_log')->insertAll($insert_coin);
                }
                Db::commit();
                echo 'success';
                $this->wlog('处理凭证超时成功:' . count($list) . ';' . date('Y-m-d H:i:s', time()), 'CoinUploadVoucherTimeout');
            } catch (Exception $exception) {
                Db::rollback();
                echo 'error';
                $this->wlog('处理凭证超时失败:' . $exception->getMessage() . ';' . date('Y-m-d H:i:s', time()), 'CoinUploadVoucherTimeout');
            }
        }
    }
    
    
    public function frozenRealName(){
        $this->wlog('冻结未实名' . date('Y-m-d H:i:s', time()), 'frozenRealName');
        $listArr = Db::name('user')->where("real_name_status = 0 and status = 'normal' and createtime < ".time() - 86400 * 3)->column('id');//未通过实名的
        Db::name('user')->whereIn('id',$listArr)->update(['status' => 'hidden']);
        $this->wlog('冻结未实名数量: '.count($listArr) . date('Y-m-d H:i:s', time()), 'frozenRealName');
    }

    /** 法币确认订单超时
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function CoinConfirmTimeout()
    {
        //$this->wlog('确认订单超时' . date('Y-m-d H:i:s', time()), 'CoinConfirmTimeout');
        $now_time = time();
        //$coinVoucher = Config::get('site.JY_SUB');
        $MConfig = new MConfig();
        $coinVoucher = $MConfig->readConfig('JY_SUB');
        //已经上传凭证 超时 订单还原 卖家账号冻结
        $list = Db::name('order_coin')->where('status=2 and voucher_time <' . ($now_time - $coinVoucher * 60))->select();

        if (count($list)) {
            $sell_user_arr = array_column($list, 'sell_uid');
            $id_arr = array_column($list, 'id');
            $buy_user_arr = array_column($list, 'buy_uid');
            
            try {
                Db::startTrans();
                Db::name('member_list')->whereIn('id', $sell_user_arr)->setField('status', 3);
                Db::name('order_coin')->whereIn('id', $id_arr)->update([
                    'end_time' => time(),
                    'status' => 3
                ]);
                //处理会员 卖家冻结和买家得到
                $insert = [];
                $buy_user = Db::name('member_list')->whereIn('id', $buy_user_arr)->field('id,tel,balance')->select();

                foreach ($list as $k => $v) {
                    foreach ($buy_user as $kk => $vv) {
                        if ($v['buy_uid'] == $vv['id']) {
                            $v['balance'] = $vv['balance'];
                            $v['tel'] = $vv['tel'];
                            continue;
                        }
                    }

                    Db::name('member_list')->where('id', $v['sell_uid'])->setDec('frozen_dot', $v['num']);
                    Db::name('member_list')->where('id', $v['buy_uid'])->setInc('balance', $v['num']);
                    //Db::name('user')->where('id', $v['sell_uid'])->setInc('balance', $v['num'] + $v['recharge']);
/*                     $insert[] = [
                        'uid' => $v['buy_uid'],
                        'phone' => $v['tel'],
                        'change_money' => $v['num'],
                        'surplus_money' => $v['num'] + $v['balance'],
                        'type' => 2,
                        'status' => 30,
                        'message' => '超时订单完成获得U',
                        'time' => time()
                    ]; */
                    
                    $insert[] = [
                        'u_id' => $v['buy_uid'],
                        'tel' => $v['tel'],
                        'former_money' => $v['balance'],
                        'change_money' => $v['num'] + $v['recharge'],
                        'after_money' => $v['num'] + $v['recharge'] + $v['balance'],
                        'type' => 101,
                        'status' => 232,
                        'message' => 'YKB订单超时确认自动确认',
                        'bo_time' => time()
                    ];
                    $insert[] = [
                        'u_id' => $v['sell_uid'],
                        'tel' => $v['sell_user'],
                        'former_money' => 0,
                        'change_money' => $v['num'],
                        'after_money' => 0,
                        'type' => 102,
                        'status' => 236,
                        'message' => 'YKB订单超时确认自动确认,冻结'.$v['num'].'YKB已转出',
                        'bo_time' => time()
                    ];
                    
                    //生成自动确认订单记录
                    $insert_coin[] = [
                        'orderNo' => $v['orderNo'],
                        'uid' => $v['sell_uid'],
                        'phone' => $v['sell_user'],
                        'order_id' => $v['id'],
                        'message' => '自动确认订单,订单编号'.$v['orderNo'],
                        'time' => time()
                    ];
                    $insert_coin[] = [
                        'orderNo' => $v['orderNo'],
                        'uid' => $v['buy_uid'],
                        'phone' => $v['buy_user'],
                        'order_id' => $v['id'],
                        'message' => '自动确认订单,订单编号'.$v['orderNo'],
                        'time' => time()
                    ];
                    
                }
                
                if (count($insert) > 0) {
                    Db::name('member_balance_log')->insertAll($insert);
                }
                if(count($insert_coin) > 0){
                    Db::name('order_coin_log')->insertAll($insert_coin);
                }
                
                Db::commit();
                echo 'success';
                $this->wlog('处理确认超时成功:' . count($list) . ';' . date('Y-m-d H:i:s', time()), 'CoinConfirmTimeout');
            } catch (Exception $exception) {
                Db::rollback();
                echo 'error';
                $this->wlog('处理确认超时失败:' . $exception->getMessage() . ';' . date('Y-m-d H:i:s', time()), 'CoinConfirmTimeout');
            }
        }
    }

    /** 互助上传凭证超时
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function MutualUploadVoucherTimeoutfqwasd()
    {   
        $this->wlog('上传凭证超时' . date('Y-m-d H:i:s', time()), 'MutualUploadVoucherTimeout');
        $now_time = time();
        //$coinVoucher = Config::get('site.huzhuVoucher');
        $MConfig = new MConfig();
        $coinVoucher = $MConfig->readConfig('huzhuVoucher');
        //已经上传凭证 超时 订单还原 卖家金额返还 买家账号冻结 member_mutualaid
        $list = Db::name('mutualaid_order')->where('status=1 and recevice_time <' . ($now_time - $coinVoucher * 60))->select();
        //var_dump($list);die;
        if (count($list) > 0) {
            $sell_user_arr = array_column($list, 'sell_uid');
            $buy_user_arr = array_column($list, 'buy_uid');
            $id_arr = array_column($list, 'id');

            $buy_user = Db::name('member_list')->whereIn('id', $buy_user_arr)->field('id,tel,balance')->select();
            
            try {
                Db::startTrans();
                Db::name('member_list')->whereIn('id', $buy_user_arr)->setField('status', 3);
                //订单状态改变
                Db::name('mutualaid_order')->whereIn('id', $id_arr)->update([
                    'buy_uid' => 0,
                    'buy_user' => '',
                    'recevice_time' => 0,
                    'status' => 0
                ]);
                Db::name('mutualaid_log')->whereIn('jy_id', $id_arr)->update([
                    'jy_status' => 3
                ]);
                $orderNo_arr = array_column($list, 'orderNo');
                Db::name('member_mutualaid')->whereIn('orderNo',$orderNo_arr)->update([
                    'status' => 2
                ]);
                foreach ($list as $k => $v) {
                    foreach ($buy_user as $kk => $vv) {
                        if ($v['buy_uid'] == $vv['id']) {
                            $v['balance'] = $vv['balance'];
                            $v['tel'] = $vv['tel'];
                            continue;
                        }
                    }
                    
                    $insert_log[] = [
                        'u_id' => 1,
                        'log' => $v['tel'].'互助上传凭证超时冻结账号',
                        'time' => time()
                    ];
                    
                }
                // $insert = [];
                // foreach ($list as $k => $v) {
                //     foreach ($sell_user as $kk => $vv) {
                //         if ($v['sell_uid'] == $vv['id']) {
                //             $v['balance'] = $vv['balance'];
                //             $v['mobile'] = $vv['mobile'];
                //             continue;
                //         }
                //     }
                //     //金额返还
                //     Db::name('user')->where('id', $v['sell_uid'])->setInc('dot', $v['price']);
                //     $insert[] = [
                //         'uid' => $v['sell_uid'],
                //         'phone' => $v['mobile'],
                //         'change_money' => $v['price'],
                //         'surplus_money' => $v['price'] + $v['balance'],
                //         'type' => 2,
                //         'status' => 46,
                //         'message' => '超时返还U',
                //         'time' => time()
                //     ];
                // }
                // Db::name('account_log')->insertAll($insert);
                Db::name('system_log')->insertAll($insert_log);
                Db::commit();
                echo 'success';
                $this->wlog('处理凭证超时成功:' . count($list) . ';' . date('Y-m-d H:i:s', time()), 'MutualUploadVoucherTimeout');
            } catch (Exception $exception) {
                Db::rollback();
                echo 'error';
                $this->wlog('处理凭证超时失败:' . $exception->getMessage() . ';' . date('Y-m-d H:i:s', time()), 'MutualUploadVoucherTimeout');
            }
        }
    }

    /** 互助确认订单超时
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function MutualConfirmTimeoutwqrascs()
    {   
        $this->wlog('确认订单超时' . date('Y-m-d H:i:s', time()), 'MutualConfirmTimeout');
        $now_time = time();
        //$coinVoucher = Config::get('site.huzhuConfirm');
        $MConfig = new MConfig();
        $coinVoucher = $MConfig->readConfig('huzhuConfirm');
        $auxiliaryCurrency = $MConfig->readConfig('auxiliaryCurrency');
        //$voucherTime = Config::get('site.voucherTime');
        //$voucherSong = Config::get('site.voucherSong');
        //var_dump($coinVoucher);die;
        //已经上传凭证 超时 订单还原 卖家账号冻结
        $list = Db::name('mutualaid_order')->where('status=2 and pay_time <' . ($now_time - $coinVoucher * 60))->select();
        //var_dump($list);die;
        if (count($list) > 0) {
            $sell_user_arr = array_column($list, 'sell_uid');
            $id_arr = array_column($list, 'id');

            try {
                Db::startTrans();
                // Db::name('user')->whereIn('id', $sell_user_arr)->setField('status', 'hidden');
                Db::name('mutualaid_order')->whereIn('id', $id_arr)->update([
                    'end_time' => time(),
                    'status' => 3
                ]);
                /* Db::name('mutualaid_log')->whereIn('jy_id', $id_arr)->update([
                    'jy_status' => 2
                ]); */
                foreach ($list as $k => $v) {
                    Db::name('mutualaid_log')->where('jy_id', $v['id'])->where('uid',$v['buy_uid'])->update([
                        'jy_status' => 2
                    ]);
                    $purchaseList = Db::name('mutualaid_list')
                        ->where('id', $v['purchase_id'])
                        ->field('id,logo,name,min_price,max_price,days,type,rate,give_balance')->find();

                    if ($purchaseList) {
                        Db::name('member_mutualaid')->insert([
                            'uid' => $v['buy_uid'],
                            'purchase_id' => $v['purchase_id'],
                            'tel' => $v['buy_user'],
                            'orderNo' => $this->makeRand(),
                            'purchase_no' => $v['purchase_no'],
                            'get_price' => $v['price'],
                            'new_price' => $v['price'],
                            'rate' => $purchaseList['rate'],
                            'type' => $purchaseList['type'],
                            'days' => $purchaseList['days'],
                            'sta_time' => time(),
                            'is_overtime' => $v['is_overtime']
                        ]);
                        Db::name('member_mutualaid')->where('orderNo', $v['orderNo'])->update(['status' => 4,'is_exist' => 0]);

                        //上级获得碎片
                        $buy_user = Db::name('member_list')->where('id', $v['buy_uid'])->field('f_uid,is_effective,balance,profit_recom,f_uid_all,coin')->find();
                        /* $count_frag = Db::name('frag_log')->where('by_uid ='.$v['buy_uid'].' and p_id ='.$v['purchase_id'])->count();
                        if ($buy_user['f_uid'] != 0 && $count_frag == 0) {
                            $f_buy_user = Db::name('member_list')->where('id', $buy_user['f_uid'])->field('id,username')->find();
                            $this->sendFrag($v['buy_uid'],$f_buy_user['id'], $f_buy_user['username'], $v['purchase_id'], $purchaseList['name']);
                        } */
                        $this->upLevel($v['buy_uid']);
                        //买家判断升级有效会员
                        if ($buy_user['is_effective'] == 0) {
                            $this->upEffective($v['buy_uid']);
                        }
                        //宠物资产
                        Db::name('member_list')->where('id', $v['buy_uid'])->setInc('pets_assets', $v['price']);
                        //Db::name('member_list')->where('id', $v['buy_uid'])->setInc('pets_assets_history', $v['price']);
                        Db::name('member_list')->where('id', $v['sell_uid'])->setDec('pets_assets', $v['price']);
                        
                        //获得
                        if ($purchaseList['give_balance'] > 0) {
                            Db::name('member_list')->where('id', $v['buy_uid'])->update([
                                'coin' => Db::raw('coin + ' . $purchaseList['give_balance']),
                            ]);
                            //setInc('balance', $purchaseList['give_balance']);
                            Db::name('member_balance_log')->insert([                                
                                'u_id' => $v['buy_uid'],
                                'tel' => $v['buy_user'],
                                'former_money' => $buy_user['coin'],
                                'change_money' => $purchaseList['give_balance'],
                                'after_money' => $buy_user['coin']+$purchaseList['give_balance'],
                                'type' => 11,
                                'status' => 102,
                                'message' => '确认订单赠送'.$auxiliaryCurrency,
                                'bo_time' => time()
                            ]);
                        }
                        
/*                         $voucherTime = $coinVoucher[1];//Config::get('site.voucherTime');
                        $voucherSong = $coinVoucher[2];//Config::get('site.voucherSong');
                        if (time() - $v['pay_time'] <= $voucherTime * 60 && $voucherSong > 0) {
                            Db::name('member_list')->where('id', $v['buy_uid'])->setInc('profit_recom', $voucherSong);
                            Db::name('member_balance_log')->insert([
                                'uid' => $v['buy_uid'],
                                'phone' => $v['buy_user'],
                                'change_money' => $voucherSong,
                                'surplus_money' => $buy_user['profit_recom'] + $voucherSong,
                                'type' => 4,
                                'status' => 441,
                                'message' => '付款奖励',
                                'time' => time()
                            ]);
                        } */                          
                    }
                }
                //会员获得新宠物
                Db::commit();
                $this->wlog('处理确认超时成功:' . count($list) . ';' . date('Y-m-d H:i:s', time()), 'MutualConfirmTimeout');
            } catch (Exception $exception) {
                Db::rollback();
                $this->wlog('处理确认超时失败:' . $exception->getMessage() . ';' . date('Y-m-d H:i:s', time()), 'MutualConfirmTimeout');
            }
        }
    }

    public function sendFrag($uid,$f_uid, $f_username, $purchase_id, $purchase_name)
    {
        $log = Db::name('purchase_frag')->where('uid =' . $f_uid . ' and p_id =' . $purchase_id)->find();
        if (!$log) {
            Db::name('purchase_frag')->insert([
                'uid' => $f_uid,
                'p_id' => $purchase_id,
                'num' => 1
            ]);
        } else {
            Db::name('purchase_frag')->where('uid =' . $f_uid . ' and p_id =' . $purchase_id)->setInc('num');
        }
        Db::name('frag_log')->insert([
            'uid' => $f_uid,
            'by_uid' => $uid,
            'p_id' => $purchase_id,
            'message' => $f_username . '领养了',
            'name' => $purchase_name,
            'num' => 1,
            'time' => time()
        ]);
    }

    /**有效会员升级
     * @throws Exception
     */
    public function upEffective($uid)
    {   
        $MConfig = new MConfig();
        $asstes = $MConfig->readConfig('effectiveUserAssets');
        //$asstes = Config::get('site.effectiveUserAssets');
        //升级有效用户
        $user = Db::name('member_list')->where('id', $uid)->field('is_effective,f_uid,f_uid_all,pets_assets')->find();
        if ($user['is_effective'] == 0) {//第一次升级有效会员
            $assets = Db::name('member_mutualaid')->where('uid =' . $uid . ' and status in (1,2,3)')->sum('new_price');
            if ($assets >= $asstes) {//$user['pets_assets']
                Db::name('member_list')->where('id', $uid)->setField('is_effective', 1);
                if ($user['f_uid'] != 0) {
                    //团队 及直推
                    Db::name('member_list')->where('id', $user['f_uid'])->setInc('zt_yx_num');
                    Db::name('member_list')->where('id in (' . $user['f_uid_all'] . ')')->setInc('yx_team');
                    $team_arr = explode(',', $user['f_uid_all']);
                    foreach ($team_arr as $k => $v) {
                        $this->upLevel($v);
                    }
                }
            }
        }
    }

    public function upLevel($uid)
    {
        $levelList = Db::name('member_level')->order('id desc')->select();
        $userInfo = Db::name('member_list')->where('id', $uid)->field('zt_yx_num,yx_team,level')->find();
        $assets = Db::name('member_mutualaid')->where('uid =' . $uid . ' and status in (1,2,3)')->sum('new_price');
        foreach ($levelList as $k => $v) {
            if ($userInfo['level'] < $v['id']) {//可以升级
                if ($v['direct_push'] <= $userInfo['zt_yx_num'] && $v['team_push'] <= $userInfo['yx_team'] && $assets >= $v['pet_assets']) {
                    Db::name('member_list')->where('id', $uid)->setField('level', $v['id']);
                }
            } else {
                break;
            }
        }
    }

    //清空预约人数
    public function clearPurchase(){
        $this->wlog('清空预约人数开始:' . date('Y-m-d H:i:s', time()), 'clearPurchase');
        Db::name('purchase_list')->where('1=1')->update(['purchaseNum' => 0]);
        $this->wlog('清空预约人数成功:' . date('Y-m-d H:i:s', time()), 'clearPurchase');
    }
    
    //实名导入
    public function aaa(){
        // $list = Db::name('user_purchase')->where('status in (1,2)')->order('id asc')->select();
        // foreach ($list as $k => $v){
        //     Db::name('custom_receiveways')->where('id',$v['id'])->update(['receive_qrcode' => '/'.$v['receive_qrcode']]);
        // }
        $list = Db::name('purchase_order')->order('id asc')
            ->field('sell_uid,id')->select();
        foreach ($list as $k => $v) {
            $mobile = Db::name('user')->where('id', $v['sell_uid'])->value('mobile');
            if ($mobile != '') {
                Db::name('purchase_order')->where('id', $v['id'])
                    ->update([
                        'sell_user' => $mobile
                    ]);
            }
        }
        
    }

    //日志操作
    public function wlog($content = '', $dir = 'log')
    {
        $dir_path = 'Log/' . $dir . '/';
        $filename = date('Ymd') . '.log';
        $this->sp_dir_createwrfs($dir_path);
        $log_content = date('H:i:s') . ":\n" . $content . "\n\n";
        file_put_contents($dir_path . $filename, $log_content, FILE_APPEND);
    }

    public function sp_dir_createwrfs($path, $mode = 0777)
    {
        if (is_dir($path)) return true;
        $ftp_enable = 0;
        $path = $this->sp_dir_pathwarsa($path);
        $temp = explode('/', $path);
        $cur_dir = '';
        $max = count($temp) - 1;
        for ($i = 0; $i < $max; $i++) {
            $cur_dir .= $temp[$i] . '/';
            if (@is_dir($cur_dir))
                continue;
            @mkdir($cur_dir, 0777, true);
            @chmod($cur_dir, 0777);
        }
        return is_dir($path);
    }

    public function sp_dir_pathwarsa($path)
    {
        $path = str_replace('\\', '/', $path);
        if (substr($path, -1) != '/')
            $path = $path . '/';
        return $path;
    }
}