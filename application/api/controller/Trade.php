<?php
namespace app\api\controller;

use think\facade\Cache;
use think\Exception;
use think\Db;
use think\Request;
use app\api\model\MMember;
use app\api\model\MConfig;
use app\admin\model\MMutualAidOrder;
use app\api\model\DesignatedTransfer;
use app\api\model\MMutualAidLog;

class Trade extends Common
{

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////                         转让记录                                                    //////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // 转让记录
    public function transferList(Request $request)
    {   
        $MMember = new MMember();
        $MConfig = new MConfig();
        $MMutualAidOrder = new MMutualAidOrder();
        $u_id = $this->userinfo['user_id'];
        if(empty($u_id)){
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'出错啦，找不到用户']);
        }
        $config_val = $MConfig->readConfig(['huzhuVoucher','huzhuConfirm','Paidtransfers'],2);
        
        $page = intval($request->post('page', 1));// 列表页码
        //0挂单中1已匹配2已上传凭证3已完成4申诉中5申诉成功6申诉失败
        $type = intval($request->post('type', 1));//1待转让 2转让中 3已转让 4取消/申诉
        if ($page <= 0 || !in_array($type, [1, 2, 3, 4])) 
            return json(['code' => 2, 'msg' => '参数错误']);

        switch ($type) {
            case 1:
                $where = 'status in (0,9) and is_exist = 1 and sell_uid=' . $u_id;//未匹配
                break;
            case 2:
                $where = 'status in (1,2) and is_exist = 1 and sell_uid=' . $u_id;//已支付
                break;
            case 3:
                $where = 'status = 3 and is_exist = 1 and sell_uid=' . $u_id;//已完成
                break;
            case 4:
                $where = 'status = 4 and is_exist = 1 and sell_uid=' . $u_id;//申诉中
                break;
        }
        $page_size = 5;
        $count = $MMutualAidOrder->where($where)->count();
        $pages = ceil($count / $page_size);
        $offset = ($page - 1) * $page_size;
        $list = Db::name('mutualaid_order')
        ->field('id,price,create_time,purchase_id,recevice_time,pay_time,end_time,status,buy_uid,sell_uid,is_overtime,p_id,apply_status')
        ->where($where)
        ->order('id desc')
        ->limit($offset, $page_size)
        ->select();             
        $purchaseList = Db::name('mutualaid_list')->field('id,logo,name,min_price,max_price,days,type,rate,sale_expend')->select();
        foreach ($list as $k => $v) {
            $membr_mu_info = Db::name('member_mutualaid')->where('id',$v['p_id'])->field('sta_time,end_time,status')->find();
            foreach ($purchaseList as $kk => $vv) {
                if ($v['purchase_id'] == $vv['id']) {//匹配成功
                    $list[$k]['name'] = $vv['name'];
                    $list[$k]['devote'] = round($v['price'] - $vv['min_price'], 2);
                    $list[$k]['type'] = $vv['type'];
                    $list[$k]['reward'] = round($vv['days'] * $v['price'] * $vv['rate'] / 100, 2); //利润
                    $list[$k]['in_days'] = $vv['days'];      //升值天数
                    $list[$k]['price'] = floatval($v['price']);   //价值
                    //$list[$k]['sale_expend'] = $vv['sale_expend'];//出售消耗积分
                    if ($vv['type'] == 1) {//普通2合约
                        $list[$k]['rate'] = $vv['days'] . '天/' . round($vv['days'] * $vv['rate']) . '%';  //普通收益率
                    } else {
                        $list[$k]['rate'] = $vv['days'] . '天/每天' . $vv['rate'] . '%'; //合约收益率
                    }
                    if ($v['status'] == 1) {//上传凭证倒计时
                        $list[$k]['deal_time'] = $config_val[0] * 60 - (time() - $v['recevice_time']) > 0 ? $config_val[0] * 60 - (time() - $v['recevice_time']) : 0;
                    } elseif ($v['status'] == 2) {//确认订单倒计时
                        $list[$k]['deal_time'] = $config_val[1] * 60 - (time() - $v['pay_time']) > 0 ? $config_val[1] * 60 - (time() - $v['pay_time']) : 0;
                    }
                    
                    $list[$k]['logo'] = 'http://' . $_SERVER['HTTP_HOST'] . $vv['logo'];
                    //上传凭证按钮 确认订单按钮 申诉按钮
                    $list[$k]['voucher_button'] = $v['status'] == 1 && $u_id == $v['buy_uid'] ? 1 : 0;
                    $list[$k]['confirm_button'] = $v['status'] == 2 && $u_id == $v['sell_uid'] ? 1 : 0;
                    $list[$k]['reply_button'] = $v['status'] == 2 ? 1 : 0;
                    $list[$k]['end_time'] = date('Y-m-d ', $v['create_time'] + ($vv['days'] * 86400));
                    //未匹配的可指定
                    if($type == 1){
                        $list[$k]['is_appoint'] = 1; 
                    }
                    if($v['status'] == 9){
                        $list[$k]['sale_expend'] = $vv['sale_expend']; 
                    }
                }
            }
            $list[$k]['sta_time'] = date('m-d H:i', $membr_mu_info['sta_time']);
            /* if(!empty($membr_mu_info['end_time'])){
                $list[$k]['end_time'] = date('m-d H:i', $membr_mu_info['end_time']);
            }else{
                $list[$k]['end_time'] = '';
            } */
            if ($v['status'] == 1) {
                $list[$k]['create_time'] = date('m-d H:i', $v['recevice_time']);
            } elseif ($v['status'] == 2) {
                $list[$k]['create_time'] = date('m-d H:i', $v['pay_time']);
            } elseif ($v['status'] == 3) {
                $list[$k]['create_time'] = date('m-d H:i', $v['end_time']);
            } else {
                $list[$k]['create_time'] = date('m-d H:i', $v['create_time']);
            }
            unset($list[$k]['recevice_time']);
            unset($list[$k]['pay_time']);
            unset($list[$k]['buy_uid']);
            unset($list[$k]['sell_uid']);
        }
        $data = [
            'count' => $count,
            'pages' => $pages,
            'list' => $list,
            //'time' => 10
        ];
        if($type == 1){
            $data['witch'] = $config_val[2];
        }
        
        return json(['code' => 1, 'msg' => 'success', 'data'=>$data]);
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
        if($type != 1 || $type != 2){
            return json(['code'=>SIGN_ERROR,'msg'=>'兑换类型错误']);
        }
        
        $password = getValue($request->post('password', ''));//密码
        $member_info = $MMember->getInfo(['id'=>$u_id]);
        
        if($member_info['pay_pass'] != md5($password.'pay_passwd')){
            return json(['code'=>SIGN_ERROR,'msg'=>'密码错误']);
        }
        
        $info = Db::name('mutualaid_order')
        ->where(['id'=>$id])->field('orderNo,status,price,p_id')
        ->find();
        
        if(empty($info) || $info['status'] != 0){
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
            Db::name('mutualaid_order')->where('id',$id)->update(['is_exist'=>0,'status'=>11]);
            
            Db::name('member_mutualaid')->where('id',$info['p_id'])->update(['is_exist'=>0,'deal_type'=>11,'status'=>11]);
            
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
            return json(['code' => 1,'msg' => '兑换微分成功']);
        } catch (Exception $exception) {
            Db::rollback();
            //Cache::set('shelves'.$u_id,0,1);
            return json(['code' => 2,'msg' => '兑换微分失败'.$exception->getMessage()]);
        }
    }
    
    
    //冻结宠物上架
    public function shelves(Request $request){
        $MMember = new MMember();
        $MConfig = new MConfig();
        $MConfig_val = $MConfig->readConfig('mainCurrency');
        
        $u_id = $this->userinfo['user_id'];
        
        $id = intval($request->post('id', 0));//指定ID
        if(empty($id)){
            return json(['code'=>SIGN_ERROR,'msg'=>'参数错误']);
        }
        $member_info = $MMember->getInfo(['id'=>$u_id]);
        
        $info = Db::name('mutualaid_order')->alias('a')
        ->join('zm_mutualaid_list b','a.purchase_id = b.id','left')
        ->where('a.id',$id)->field('a.orderNo,a.status,b.sale_expend')
        ->find();
        
        if(empty($info) || $info['status'] != 9){
            return json(['code'=>SIGN_ERROR,'msg'=>'订单状态出错啦']);
        }

        if($member_info['balance'] < $info['sale_expend']){
            return json(['code'=>SIGN_ERROR,'msg'=>$MConfig_val.'余额不足']);
        }
        
        if (Cache::get('shelves'.$u_id) == 2){
            return json(['code' => 2, 'msg' => '请勿频繁操作', 'data'=>[]]);
        }
        Cache::set('shelves'.$u_id,2,10);       
        
        try {
            Db::startTrans();
            //修改互助订单状态
            Db::name('mutualaid_order')->where('id',$id)->update(['status'=>0]);
            
            Db::name('member_list')->where('id', $u_id)->update([
                'balance'=>Db::raw('balance -'.$info['sale_expend'])
            ]);
            
            //生成
            Db::name('member_balance_log')->insert([
                'u_id' => $u_id,
                'tel' => $member_info['tel'],
                'o_id' => $id,
                'change_money' => $info['sale_expend'],
                'former_money' => $member_info['balance'],
                'after_money' => $member_info['balance']-$info['sale_expend'],
                'message' => '解冻订单上架扣除，订单编号'.$info['orderNo'],
                'type' => 2,
                'bo_time' => time(),
                'status' => 237
            ]);
            
            Db::commit();
            Cache::set('shelves'.$u_id,0,1);
            return json(['code' => 1,'msg' => '激活成功']);
        } catch (Exception $exception) {
            Db::rollback();
            Cache::set('shelves'.$u_id,0,1);
            return json(['code' => 2,'msg' => '激活失败'.$exception->getMessage()]);
        }
        
        var_dump($info);
    }
    
    //指定待转
    public function designatedTransfer(Request $request){                
        $u_id = $this->userinfo['user_id'];
        
        $page = intval($request->post('page', 1));// 列表页码
        
        $page_size = 10;
        
        $DesignatedTransfer = new DesignatedTransfer();
        
        $list = $DesignatedTransfer->getPage($page,$page_size,'u_id='.$u_id,'','a.*,b.name');
               
        return json(['code' => 1, 'msg' => 'success', 'data'=>$list]);        
    }
    
    
    //指定待转
    public function designatedTransferPost(Request $request){
        $MMutualAidOrder = new MMutualAidOrder();
        $MMutualAidLog = new MMutualAidLog();
        $MMember = new MMember();
        $DesignatedTransfer = new DesignatedTransfer();
        $MConfig = new MConfig();
        
        $u_id = $this->userinfo['user_id'];
        $id = intval($request->post('id', 0));//指定ID
        $phone = intval($request->post('phone', 0));//指定用户
        $password = getValue($request->post('password', ''));//密码
        if(empty($id) || empty($phone) || $password == ''){
            return json(['code'=>SIGN_ERROR,'msg'=>'参数错误']);
        }        
        
        $config_val = $MConfig->readConfig(['Paidtransfers','Trandeadline'],2);
        if($config_val[0] == 2)
            return json(['code'=>SIGN_ERROR,'msg'=>'转让功能暂未开放，请联系客服']);
        
        $mutu_order_info = $MMutualAidOrder->getInfo(['id'=>$id]);
            
        $mutu_info = Db::name('mutualaid_list')->where('id',$mutu_order_info['purchase_id'])->field('sta_time,sale_expend')->find();
        
        //时间比对
        $now_time = strtotime(date('Ymd His', time())) - strtotime(date('Ymd', time()));//当前时分秒时间戳       
        $sta_time = date('Y-m-d H:i:s', $mutu_info['sta_time']-3600);
        $abc=substr($sta_time,-8);
        $sta = strtotime(date('Y-m-d '.$abc, time())) - strtotime(date('Y-m-d', time()));
        if($sta < $now_time){
            return json(['code' => SIGN_ERROR, 'msg' => '请在开抢前'.$config_val[1].'小时操作']);
        }

        if($mutu_order_info['status'] != 0){
            return json(['code'=>2,'msg'=>'该订单已在交易中，无法转让']);
        }

        $use_info = $MMember->where('tel',$phone)->field('id,tel,status,level')->find();
        if(!$use_info)
            return json(['code'=>2,'msg'=>'该用户不存在']);
            
        if($use_info['status'] == 3)  return json(['code'=>SIGN_ERROR,'msg'=>'该用户已被冻结，无法进行交易']);
        if($use_info['status'] == 1)  return json(['code'=>SIGN_ERROR,'msg'=>'该用户暂未激活，无法进行交易']);

        $mutu_log = $MMutualAidLog->whereTime('time','today')->where('uid ='.$use_info['id'].' AND p_id = '.$mutu_order_info['purchase_id'].' AND status = 0 AND (purchase_status = 1 OR order_status = 1)')->find();
        if(empty($mutu_log)){
            return json(['code'=>2,'msg'=>'该用户未预约']);
        }
        
        $member_info = $MMember->getInfo(['id'=>$u_id],'id,pay_pass,tel,balance');
        if($member_info['pay_pass'] != md5($password.'pay_passwd')){
            return json(['code'=>2,'msg'=>'交易密码错误']);
        }
        
        if (Cache::get('designatedTransferPost'.$u_id) == 2){
            return json(['code' => 2, 'msg' => '请勿频繁操作', 'data'=>[]]);
        }
        Cache::set('designatedTransferPost'.$u_id,2,10);
        
/*         //交易扣费率
        $sell_rate = Db::name('member_level')->where('id',$use_info['level'])->value('sell_rate');
        $new_price = Db::name('member_mutualaid')->where('id',$use_info['p_id'])->value('new_price');        
        $deduction = $new_price*$sell_rate/100; */
        
        //根据订单编号查找会员互助ID
        $member_mutu_id = Db::name('member_mutualaid')->where(['orderNo'=>$mutu_order_info['orderNo']])->value('id');
                
        try {
            Db::startTrans();
            //修改互助订单状态
            $MMutualAidOrder->where('id',$id)->update([
                'buy_uid'=>$use_info['id'],
                'buy_user'=>$use_info['tel'],
                'status'=>1,
                'recevice_time'=> time(),
                'p_id'=>$member_mutu_id
            ]);
            //修改预约表 为已匹配
            Db::name('mutualaid_log')->whereIn('id', $mutu_log['id'])->setField('status', 1);
            //修改会员互助状态
            Db::name('member_mutualaid')->where('id', $member_mutu_id)->setField('status', 3);

            Db::name('member_list')->where('id', $use_info['id'])->setField('fail_num', 0);
            //Db::name('member_list')->where('id', $use_info['id'])->setField('balance', $deduction);
            //生成
            $DesignatedTransfer->insert([
                'u_id' => $u_id,
                'tel' => $member_info['tel'],
                'p_id' => $id,//$mutu_order_info['purchase_id'],
                'zd_id' => $use_info['id'],
                'zd_tel' => $use_info['tel'],
                'time' => time(),
                'status' => 0,
                'message' => ''
            ]);
            
            
            Db::commit();
            Cache::set('designatedTransferPost'.$u_id,0,1);
            return json(['code' => 1,'msg' => '转让成功']);
        } catch (Exception $exception) {
            Db::rollback();
            Cache::set('designatedTransferPost'.$u_id,0,1);
            return json(['code' => 2,'msg' => '转让失败'.$exception->getMessage()]);
        }
    }
    
    
    
    // 查看凭证 // 上传凭证
    
    public function uploadVoucherIndex(Request $request)
    {   
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig('huzhuVoucher');
        
        $u_id = $this->userinfo['user_id'];
        $id = intval($request->post('id', 0));//指定ID

        if (!$id)
            return json(['code' => 2, 'msg' => '参数错误', 'data'=>[]]);
        
        $order = Db::name('mutualaid_order')->where('id =' . $id . ' and is_exist = 1')->find();
        if (!$order)
            return json(['code' => 2, 'msg' => '该订单不存在', 'data'=>[]]);
            
        $config_val1 = $MConfig->readConfig('LOCK_ORDER');
        $config_val2 = $MConfig->readConfig('DEFAULT_LEVEL_IMG');
            
        if(($order['recevice_time']+$config_val1*60) - time() > 0){
            $times = ($order['recevice_time']+$config_val1*60) - time();
            if($times > 60){
                $time = intval($times/60);
                $str = $time.'分钟';
            }else{
                $str = $times.'秒';
            }
            return json(['code' => 2, 'msg' => '该订单正在核对数据，请于'.$str.'后进入']);
        }
            
        $purchaseList = Db::name('mutualaid_list')->field('id,logo,name,min_price,max_price,days,type,rate')->select();
        //查询买家会员信息
        if ($order['buy_uid'] == $u_id) {//自己是买家 查看卖家的信息 需要上传凭证
            $userId = $order['sell_uid'];
            $other_userId = $order['buy_uid'];
            $data['status'] = 'buy';
        } elseif ($order['sell_uid'] == $u_id) {
            $userId = $order['buy_uid'];
            $other_userId = $order['sell_uid'];
            $data['status'] = 'sell';
        } else {
            return json(['code' => 2, 'msg' => '参数错误', 'data'=>[]]);
        }
        $userInfo = Db::name('member_list')->where('id', $userId)->field('user,u_img,level,tel,urgent_mobile')->find();
        $userInfo_realname = Db::name('real_name_log')->where('u_id', $userId)->where('status = 1')->field('real_name,urgent_man')->find();
        $other_userInfo = Db::name('member_list')->where('id', $other_userId)->field('tel,real_name')->find();
        foreach ($purchaseList as $k => $v) {
            if ($order['purchase_id'] == $v['id']) {
                $data['purchase']['name'] = $v['name'];
                $data['purchase']['days'] = $v['days'];
                $data['purchase']['create_time'] = date('Y-m-d H:i:s',$order['recevice_time']);
                $data['purchase']['reward'] = round($v['days'] * $v['rate'] * $order['price'] / 100, 2);
                $data['purchase']['status'] = $order['status'];
                if ($v['type'] == 1) {//普通2合约
                    $data['purchase']['rate'] = $v['days'] . '天/' . round($v['days'] * $v['rate']) . '%';
                } else {
                    $data['purchase']['rate'] = $v['days'] . '天/每天' . $v['rate'] . '%';
                }
                $data['purchase']['type'] = $v['type'];//1普通合约 2合约宠物
                $data['purchase']['price'] = floatval($order['price']);
                $data['purchase']['logo'] = 'http://' . $_SERVER['HTTP_HOST'] . $v['logo'];
            }
        }
        $receive = Db::name('paymant_binding')->where('u_id =' . $order['sell_uid'])->field('name,account_num,bank_num,receive_qrcode,status')->select();
        $data_receive = [];
        foreach ($receive as $k => $v) {
            $data_receive[$v['status']] = ['name' => $v['name'], 'account_num' => $v['account_num'], 'bank_num' => $v['bank_num'], 'receive_qrcode' => 'http://' . $_SERVER['HTTP_HOST'] . $v['receive_qrcode']];
        }
        //$data_receive = array_values($data_receive);
        // var_dump($data_receive);die;
        $data['receive'] = $data_receive;
        $data['voucher'] = $order['voucher'] == '' ? '' : 'http://' . $_SERVER['HTTP_HOST'] . $order['voucher'];
        $data['userInfo']['urgent_tel'] = $userInfo_realname['urgent_man'];//$userInfo['urgent_mobile'];
        $data['userInfo']['username'] = $userInfo['user'];
        $data['userInfo']['tel'] = $userInfo['tel'].'('.$userInfo_realname['real_name'].')';
        $level_img = $userInfo['level'] == 0 ? $config_val2 : Db::name('member_level')->where('id', $userInfo['level'])->value('level_logo');
        $data['userInfo']['level'] = 'http://' . $_SERVER['HTTP_HOST'] . $level_img;
        $data['userInfo']['order_id'] = $order['id'];
        $data['userInfo']['avatar'] = 'http://' . $_SERVER['HTTP_HOST'] . $userInfo['u_img'];
        $data['userInfo']['deal_time'] = $config_val * 60 - (time() - $order['recevice_time']) > 0 ? $config_val * 60 - (time() - $order['recevice_time']) : 0;
        
        $data['userInfo']['other_tel'] = $other_userInfo['tel'].'('.$other_userInfo['real_name'].')';

        return json(['code' => 1,'msg' => '','data'=>$data]);
    }
    
    
    /** 上传凭证
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
/*     public function uploadVoucherPost(Request $request)
    {   
        $u_id = 2;//$this->userinfo['user_id'];
        $img = $request->post('voucher');
        $orderId = $request->post('id');
        if (!$img || !$orderId)
            return json(['code' => 2, 'msg' => '参数错误', 'data'=>[]]);
        $order = Db::name('purchase_order')->where('id =' . $orderId . ' and is_exist = 1')->find();
        if (!$order || $order['status'] != 1 || $order['buy_uid'] != $u_id)
            return json(['code' => 2, 'msg' => '该订单不存在', 'data'=>[]]);
        $data['voucher'] = $img;
        $data['status'] = 2;
        $data['pay_time'] = time();
        try {
            Db::startTrans();
            Db::name('mutualaid_order')->where('id', $orderId)->update($data);
            Db::name('mutualaid_order_log')->insert([
                'uid' => $u_id,
                'message' => '上传凭证',
                'orderNo' => $order['orderNo'],
                'time' => time()
            ]);
            Db::commit();
            // $sms = new Sms();
            // $sms->sendPostNew($order['sell_user'],3);
            return json(['code' => 1,'msg' => '上传成功']);
        } catch (Exception $exception) {
            Db::rollback();
            return json(['code' => 2,'msg' => '上传失败'.$exception->getMessage()]);
        }
    }
 */    
    
    
    
    /** 确认订单渲染
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function confirmOrderIndex(Request $request)
    {   
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig('huzhuConfirm');
        
        $u_id = $this->userinfo['user_id'];
        
        $id = $request->post('id');        
        if (!$id)
            return json(['code' => 2, 'msg' => '参数错误', 'data'=>[]]);
        
        $order = Db::name('mutualaid_order')->where('id =' . $id . ' and is_exist = 1')->find();
        if (!$order)
            return json(['code' => 2, 'msg' => '该订单不存在', 'data'=>[]]);
        
        $purchaseList = Db::name('mutualaid_list')->field('id,logo,name,min_price,max_price,days,type,rate')->select();
        //查询买家会员信息
        if ($order['buy_uid'] == $u_id) {//自己是买家 查看卖家的信息 需要上传凭证
            $userId = $order['sell_uid'];
            $data['status'] = 'buy';
        } elseif ($order['sell_uid'] == $u_id) {
            $userId = $order['buy_uid'];
            $data['status'] = 'sell';
        } else {
            return json(['code' => 2, 'msg' => '参数错误', 'data'=>[]]);
        }
        $userInfo = Db::name('member_list')->where('id', $userId)->field('user,u_img,level,tel,urgent_mobile')->find();
        $userInfo_realname = Db::name('real_name_log')->where('u_id', $userId)->where('status = 1')->field('real_name,urgent_man')->find();
        foreach ($purchaseList as $k => $v) {
            if ($order['purchase_id'] == $v['id']) {
                $data['purchase']['name'] = $v['name'];
                $data['purchase']['order_id'] = $order['id'];
                $data['purchase']['days'] = $v['days'];
                $data['purchase']['status'] = $order['status'];
                $data['purchase']['create_time'] = date('Y-m-d H:i:s',$order['pay_time']);
                $data['purchase']['reward'] = round($v['days'] * $v['rate'] * $order['price'] / 100, 2);
                if ($v['type'] == 1) {//普通2合约
                    $data['purchase']['rate'] = $v['days'] . '天/' . $v['days'] * $v['rate'] . '%';
                } else {
                    $data['purchase']['rate'] = $v['days'] . '天/每天' . $v['rate'] . '%';
                }
                $data['purchase']['type'] = $v['type'];//1普通合约 2合约宠物
                $data['purchase']['price'] = $order['price'];
                $data['purchase']['logo'] = 'http://' . $_SERVER['HTTP_HOST'] . $v['logo'];
                $data['userInfo']['deal_time'] = $config_val * 60 - (time() - $order['pay_time']) > 0 ? $config_val * 60 - (time() - $order['pay_time']) : 0;
                break;                          //Config::get('site.huzhuConfirm')
            }
        }
        $data['voucher'] = 'http://' . $_SERVER['HTTP_HOST'] . $order['voucher'];
        $data['userInfo']['urgent_tel'] = $userInfo_realname['urgent_man'];//$userInfo['urgent_mobile'];
        $data['userInfo']['username'] = $userInfo['user'];
        $data['userInfo']['tel'] = $userInfo['tel'].'('.$userInfo_realname['real_name'].')';
        $data['userInfo']['order_id'] = $order['id'];
        $data['userInfo']['avatar'] = 'http://' . $_SERVER['HTTP_HOST'] . $userInfo['u_img'];       
        
        return json(['code' => 1,'msg' => '','data'=>$data]);
    }
    
    /** 订单申诉
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function applyOrder(Request $request)
    {   
        $u_id = $this->userinfo['user_id'];
        
        $imgs = $request->post('apply/a');
        $orderId = $request->post('id');
        $content = getValue($request->post('content'));
        if (!$orderId)
            return json(['code' => 2, 'msg' => '参数错误', 'data'=>[]]);
        if (!$imgs || !$content) 
            return json(['code' => 2, 'msg' => '请填写完整的信息', 'data'=>[]]);
        
        $order = Db::name('mutualaid_order')->where('id =' . $orderId . ' and is_exist = 1')->find();
        if (!$order || $order['status'] != 2 || $order['sell_uid'] != $u_id)
            return json(['code' => 2, 'msg' => '该订单不存在', 'data'=>[]]);
        
        $data['appeal_imgs'] = implode(',', $imgs);
        $data['appeal_content'] = $content;
        $data['status'] = 4;
        $data['appeal_time'] = time();
        try {
            Db::startTrans();
            Db::name('mutualaid_order')->where('id', $orderId)->update($data);
            Db::name('mutualaid_order_log')->insert([
                'uid' => $u_id,
                'message' => '订单申诉',
                'orderNo' => $order['orderNo'],
                'time' => time(),
                'order_id' => $orderId
            ]);
            Db::commit();
            return json(['code' => 1,'msg' => '申诉成功',]);
        } catch (Exception $exception) {
            Db::rollback();
            return json(['code' => 2,'msg' => '申诉失败'.$exception->getMessage()]);
        }
    }
    
    /** 确认订单
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function confirmOrder(Request $request)
    {   
        $MMember = new MMember();
        $u_id = $this->userinfo['user_id'];
        $orderId = $request->post('id');
        if (!$orderId)
            return json(['code' => 2, 'msg' => '参数错误', 'data'=>[]]);

        $Mconfig = new MConfig();
        
        //$config_val = $Mconfig->readConfig(['huzhuVoucher','huzhuConfirm'],2); var_dump($config_val);exit();
        $config_val = $Mconfig->readConfig(['mainCurrency','voucherTime','voucherSong','auxiliaryCurrency'],2);
        $cv0 = $config_val[3];//$config_val[0] == ''?$config_val[3]:$config_val[0];
        
        $member_info = $MMember->getinfo(['id'=>$u_id]);

        if (Cache::get('confirmOrder'.$u_id) == 2){
            return json(['code' => 2, 'msg' => '请勿频繁操作', 'data'=>[]]);
        }
        Cache::set('confirmOrder'.$u_id,2,10);
        
        $order = Db::name('mutualaid_order')->where('id =' . $orderId . ' and is_exist = 1')->find();
        if (!$order || $order['status'] != 2 || $order['sell_uid'] != $u_id)
            return json(['code' => 2, 'msg' => '该订单不存在', 'data'=>[]]);
        
            
        //判断是否指定订单
        $designated = 0;
        $designated_transfer = Db::name('designated_transfer')->where(['p_id'=>$orderId])->find();
        if($designated_transfer){
            $designated = 1;
        }
        
        $log = Db::name('mutualaid_log')->where(['jy_id'=>$orderId])->find();

        $data['status'] = 3;
        $data['end_time'] = time();
        try {
            Db::startTrans();
            //订单处理
            Db::name('mutualaid_order')->where('id', $orderId)->update($data);
            if(!empty($log)){
                Db::name('mutualaid_log')->where(['jy_id'=>$orderId,'uid'=>$order['buy_uid']])->update(['jy_status'=>2]);
            }
            Db::name('mutualaid_order_log')->insert([
                'uid' => $u_id,
                'message' => '确认订单',
                'orderNo' => $order['orderNo'],
                'time' => time(),
                'order_id' => $orderId
            ]);
            if($designated == 1){
                Db::name('designated_transfer')->where(['p_id'=>$orderId])->update(['status' => 1]);
            }
            //买家获得产品 升值中
            $purchaseList = Db::name('mutualaid_list')
            ->where('id', $order['purchase_id'])
            ->field('id,logo,name,min_price,max_price,days,type,rate,give_balance,give_bonus,sale_expend')->find();
            //            $purchase_id = 0;
            //            $purchase_name = '';
            //            foreach ($purchaseList as $k => $v) {
            //                if ($order['price'] >= $v['min_price'] && $order['price'] <= $v['max_price']) {
            //                    $purchase_id = $v['id'];
            //                    $purchase_name = $v['name'];
            //                    $data['days'] = $v['days'];
            //                    $data['rate'] = $v['rate'];
            //                    $data['type'] = $v['type'];//1普通合约 2合约宠物
            //                    break;
            //                }
            //            }
            if (!$purchaseList)
                return json(['code' => 2, 'msg' => '订单无匹配', 'data'=>[]]);
            
            Db::name('member_mutualaid')->insert([
                'uid' => $order['buy_uid'],
                'purchase_id' => $order['purchase_id'],
                'tel' => $order['buy_user'],
                'orderNo' => $this->makeRand(),
                'purchase_no' => $order['purchase_no'],
                'get_price' => $order['price'],
                'new_price' => $order['price'],
                'rate' => $purchaseList['rate'],
                'type' => $purchaseList['type'],
                'days' => $purchaseList['days'],
                'sta_time' => time(),
                'is_overtime' => $order['is_overtime']
            ]);
            
            //卖家宠物状态改变
            Db::name('member_mutualaid')->where('orderNo', $order['orderNo'])->update([
                'status' => 4,
                'is_exist' => 0
            ]);
            //上级获得碎片
            $buy_user = Db::name('member_list')->where('id', $order['buy_uid'])->field('f_uid,level,pets_assets_history,zt_yx_num,yx_team,is_effective,f_uid_all')->find();
            $count_frag = Db::name('frag_log')->where('by_uid ='.$order['buy_uid'].' and p_id ='.$order['purchase_id'])->count();
            if ($buy_user['f_uid'] != 0 && $count_frag == 0) {
                $f_buy_user = Db::name('member_list')->where('id', $buy_user['f_uid'])->field('id,user')->find();
                $this->sendFrag($order['buy_uid'],$f_buy_user['id'], $f_buy_user['user'], $order['purchase_id'], $purchaseList['name']);
            }
            $level = Db::name('member_level')->order('id desc')->select();
            $upLevel = new MMember();
            $upLevel->upLevel($level, $order['buy_uid'], $buy_user['level'], $buy_user['pets_assets_history'], $buy_user['zt_yx_num'], $buy_user['yx_team']);
            //买家判断升级有效会员
//             if ($buy_user['is_effective'] == 0) {
//                 $this->upEffective($order['buy_uid']);
//             }
            
             //获得
            if ($purchaseList['give_balance'] > 0) {
                Db::name('member_list')->where('id', $order['buy_uid'])->update([
                    'coin'=> Db::raw('coin +' .$purchaseList['give_balance'])
                ]);//->setInc('balance', $purchaseList['give_balance']);
                Db::name('member_balance_log')->insert([                    
                    'u_id' => $order['buy_uid'],
                    'tel' => $order['buy_user'],
                    'former_money' => $member_info['coin'],
                    'change_money' => $purchaseList['give_balance'],
                    'after_money' => $member_info['coin'] + $purchaseList['give_balance'],
                    'type' => 11,
                    'status' => 102,
                    'message' => '确认订单赠送'. $cv0,//Config::get('site.mainCurrency'),
                    'bo_time' => time()
                ]);
            }
            /*            $voucherTime = $config_val[1];//Config::get('site.voucherTime');
            $voucherSong = $config_val[2];//Config::get('site.voucherSong');
            if (time() - $order['pay_time'] <= $voucherTime * 60 && $voucherSong > 0) {
                Db::name('member_list')->where('id', $order['buy_uid'])->setInc('profit_recom', $voucherSong);
                Db::name('member_balance_log')->insert([
                    'u_id' => $order['buy_uid'],
                    'tel' => $order['buy_user'],
                    'former_money' => $member_info['profit_recom'],
                    'change_money' => $voucherSong,
                    'after_money' => $member_info['profit_recom'] + $voucherSong,
                    'type' => 4,
                    'status' => 441,
                    'message' => '付款奖励',
                    'bo_time' => time()
                ]);
            }  
 */            
            
            //宠物资产
            Db::name('member_list')->where('id', $order['buy_uid'])->update([
                'pets_assets'=> Db::raw('pets_assets +' .$order['price']),
                'pets_assets_history'=> Db::raw('pets_assets +' .$order['price']),
            ]);
            Db::name('member_list')->where('id', $order['sell_uid'])->setDec('pets_assets', $order['price']);
            Db::commit();
            Cache::set('confirmOrder'.$u_id,0,1);
            return json(['code' => 1,'msg' => '确认成功']);
        } catch (Exception $exception) {
            Db::rollback();
            Cache::set('confirmOrder'.$u_id,0,1);
            return json(['code' => 2,'msg' => '确认失败'.$exception->getMessage()]);
        }

    }
    
    
    
    
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////                         抢购记录                                                    //////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
     
    
    // 抢购
    public function adoptList(Request $request)
    {   
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig(['upVoucherTime','huzhuVoucher','huzhuConfirm','LOCK_ORDER'],2);
        
        $u_id = $this->userinfo['user_id'];
        $page = intval($request->post('page', 1));// 列表页码
        //0挂单中1已匹配2已上传凭证3已完成4申诉中5申诉成功6申诉失败
        $type = intval($request->post('type', 2));//1待领养 2领养中 3已领养 4取消/申诉
        if ($page <= 0 || !in_array($type, [1, 2, 3, 4]))
                return json(['code' => 2, 'msg' => '参数错误']);
        
        switch ($type) {
            case 1:
                $status = 1;//已匹配
                break;
            case 2:
                $status = 2;//已支付
                break;
            case 3:
                $status = 3;//已完成
                break;
            case 4:
                $status = 4;//申诉中
                break;
        }
        $now_time = time();
        $page_size = 10;
        if (in_array($status, [1, 2, 3, 4])) {//交易记录
            $count = Db::name('mutualaid_order')->where('buy_uid =' . $u_id . ' and status =' . $status . ' and is_exist = 1')->count();
            $pages = ceil($count / $page_size);
            $offset = ($page - 1) * $page_size;
            $list = Db::name('mutualaid_order')
            ->field('id,purchase_id,price,create_time,recevice_time,pay_time,is_overtime,status,end_time')
            ->where('buy_uid =' . $u_id . ' and status =' . $status . ' and is_exist = 1')
            ->order('create_time desc')
            ->limit($offset, $page_size)
            ->select();
            foreach ($list as $k => $v) {
                if(($v['recevice_time']+$config_val[3]*60) - $now_time > 0){
                    $list[$k]['lock_time'] = ($v['recevice_time']+$config_val[3]*60) - $now_time;
                    $list[$k]['lock'] = 1;
                }else{
                    $list[$k]['lock'] = 0;
                    $list[$k]['lock_time'] = 0;
                }
                
                $list[$k]['now_status'] = 0;
                $purchase = Db::name('mutualaid_list')->where('id',$v['purchase_id'])->field('days')->find();
                $list[$k]['in_days'] = $purchase['days'];
                if ($v['status'] == 1) {
                    $list[$k]['create_time'] = date('Y-m-d H:i', $v['recevice_time']);
                    $list[$k]['end_time'] = date('Y-m-d', $v['recevice_time'] + (1+$purchase['days'] * 86400));
                } elseif ($v['status'] == 2) {
                    $list[$k]['create_time'] = date('Y-m-d H:i', $v['pay_time']);
                    $list[$k]['end_time'] = date('Y-m-d', $v['pay_time'] + (1+$purchase['days'] * 86400));
                } elseif ($v['status'] == 3) {
                    $list[$k]['create_time'] = date('Y-m-d H:i', $v['end_time']);
                    $list[$k]['end_time'] = date('Y-m-d', $v['end_time'] + (1+$purchase['days'] * 86400));
                } else {
                    $list[$k]['create_time'] = date('Y-m-d H:i', $v['create_time']);
                    $list[$k]['end_time'] = date('Y-m-d', $v['create_time'] + (1+$purchase['days'] * 86400));
                }
                $list[$k]['less_days'] = 0;
            }
        } else {//持有记录
            $count = Db::name('member_mutualaid')->where('uid =' . $u_id . ' and deal_type = 1 and is_exist = 1')->count();
            $pages = ceil($count / $page_size);
            $offset = ($page - 1) * $page_size;
            $list = Db::name('member_mutualaid')
            ->field('id,get_price,purchase_id,is_overtime,new_price,days,sta_time,up_status,type,examine_status,up_time,is_overtime')
            ->where('uid =' . $u_id . ' and deal_type = 1 and is_exist = 1')
            ->order('sta_time desc')
            ->limit($offset, $page_size)
            ->select();
            foreach ($list as $k => $v) {
                $list[$k]['price'] = $v['get_price'];//用获取的价格去匹配
                $list[$k]['recevice_time'] = $v['sta_time'];
                $list[$k]['create_time'] = date('Y-m-d H:i', $v['sta_time']);//用获取的价格去匹配
                $list[$k]['end_time'] = date('Y-m-d', $v['sta_time'] + (1+$v['days'] * 86400));
                $list[$k]['less_days'] = $v['days'] - $v['up_time'];//用获取的价格去匹配
                if ($v['up_status'] == 0) {//冻结
                    $list[$k]['now_status'] = 1;//已禁止
                } else {
                    if ($v['type'] == 2) {//合约
                        if ($v['examine_status'] == 1) {//审核中
                            $list[$k]['now_status'] = 2;//审核中
                        } else {
                            $list[$k]['now_status'] = 3;//取消合约按钮
                        }
                    } else {
                        $list[$k]['now_status'] = 4;//增值中按钮
                    }
                }
            }
        }
        $purchaseList = Db::name('mutualaid_list')->field('id,logo,name,min_price,max_price,days,type,rate')->select();
        foreach ($list as $k => $v) {
            foreach ($purchaseList as $kk => $vv) {
                if ($v['purchase_id'] == $vv['id']) {//匹配成功
                    
                    $list[$k]['upvoucher'] = 1;
                    if ($config_val[0] * 60 > time() - $v['recevice_time']){ //Config::get('site.upVoucherTime')
                        $list[$k]['upvoucher'] = 0;
                    }
                    
                    $list[$k]['name'] = $vv['name'];
                    $list[$k]['type'] = $vv['type'];
                    $list[$k]['reward'] = round($vv['days'] * $v['price'] * $vv['rate'] / 100, 2);
                    if ($vv['type'] == 1) {//普通2合约
                        $list[$k]['rate'] = $vv['days'] . '天/' . round($vv['days'] * $vv['rate']) . '%';
                    } else {
                        $list[$k]['rate'] = $vv['days'] . '天/每天' . $vv['rate'] . '%';
                    }
                    if ($status == 1) {//上传凭证倒计时 超时时间  Config::get('site.huzhuVoucher')
                        $list[$k]['deal_time'] = $config_val[1] * 60 - (time() - $v['recevice_time']) > 0 ? $config_val[1] * 60 - (time() - $v['recevice_time']) : 0;
                        // $list[$k]['over_time'] = Config::get('site.mutualTimeOut') * 60 - (time() - $v['recevice_time']) > 0 ? Config::get('site.mutualTimeOut') * 60 - (time() - $v['recevice_time']) : 0;
                    } elseif ($status == 2) {//确认订单倒计时 Config::get('site.huzhuConfirm')
                        $list[$k]['deal_time'] = $config_val[2] * 60 - (time() - $v['pay_time']) > 0 ? $config_val[2] * 60 - (time() - $v['pay_time']) : 0;
                    }
                    if (isset($list[$k]['new_price'])) {
                        $list[$k]['deal_time'] = '增值中';
                        $list[$k]['price'] = floatval($v['get_price']);
                        unset($list[$k]['get_price']);
                        unset($list[$k]['new_price']);
                    } else {
                        $list[$k]['up_status'] = 1;//增值中
                        $list[$k]['days'] = $vv['days'];
                    }
                    $list[$k]['logo'] = 'http://' . $_SERVER['HTTP_HOST'] . $vv['logo'];
                }
            }
            unset($list[$k]['sta_time']);
        }
        $data = [
            'count' => $count,
            'pages' => $pages,
            'list' => $list,
            'time' => $config_val[0]//Config::get('site.upVoucherTime')
        ];
        
        return json(['code' => 1,'msg' => '','data'=>$data]);
    }
    
    
    // 上传凭证
    public function uploadVoucher(Request $request)
    {   
        $u_id = $this->userinfo['user_id'];
        $img = $request->post('voucher');
        $orderId = $request->post('id');
        if (!$img || !$orderId)
            return json(['code' => 2, 'msg' => '参数错误']);
        
        $order = Db::name('mutualaid_order')->where('id =' . $orderId . ' and is_exist = 1')->find();
        if (!$order || $order['status'] != 1 || $order['buy_uid'] != $u_id){
            return json(['code' => 2, 'msg' => '该订单不存在']);
        }
        
        $MConfig = new MConfig();
        $MConfig_val = $MConfig->readConfig('REWARD_TURNTABLE');

        
        $lucky = 0;
        $time = (time()-$order['recevice_time'])/60;
        if($time < $MConfig_val){
            $lucky = 1;
        }
        
        //消除反斜杠
        $img = $this->updatexg($img);
        
        $data['voucher'] = $img;
        $data['status'] = 2;
        $data['pay_time'] = time();
        try {
            Db::startTrans();
            if($lucky == 1){
                Db::name('member_list')->where('id', $u_id)->setInc('luck_num', 1);
            }
            Db::name('mutualaid_order')->where('id', $orderId)->update($data);
            Db::name('mutualaid_order_log')->insert([
                'uid' => $u_id,
                'message' => '上传凭证',
                'orderNo' => $order['orderNo'],
                'time' => time(),
                'order_id' => $orderId
            ]);
            Db::commit();
            $Login = new Login();
            $Login->sendPostNew($order['sell_user'],3);
            //$sms->sendPostNew($order['sell_user'],3);
            return json(['code' => 1,'msg' => '上传成功']);
        } catch (Exception $exception) {
            Db::rollback();
            //$this->error('上传失败' . $exception->getMessage());
            return json(['code' => 2,'msg' => '上传失败'.$exception->getMessage()]);
        }
    }
    
    
    
    
    
    /** 我的碎片
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function myFrag()
    {
        $list = Db::name('purchase_list')->where('status = 1')->field('id,name,logo,star_log,level')->select();
        $myFrag = Db::name('purchase_frag')->where('uid', $this->auth->id)->select();
        $data = [];
        foreach ($list as $k => $v) {
            $data[$v['id']] = [
                'name' => $v['name'],
                'num' => 0,
                'level' => $v['level'],
                'frag_log' => 'http://' . $_SERVER['HTTP_HOST'] . $v['logo'],
                //                'star_log' => 'http://' . $_SERVER['HTTP_HOST'] . $v['star_log'],
                'all_num' => 50
            ];
            foreach ($myFrag as $kk => $vv) {
                if ($vv['p_id'] == $v['id']) {
                    $data[$v['id']]['num'] = $vv['num'];
                }
            }
        }
        $allList = [];
        foreach ($data as $k => $v) {
            $allList[] = $v;
        }
        $this->success('ok', $allList);
    }
    
    /** 碎片来源
     * @param Request $request
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function fragLog(Request $request)
    {
        $page = intval($request->post('page', 1));// 列表页码
        if ($page <= 0) $this->error('参数错误');
        $count = Db::name('frag_log')->where('uid =' . $this->auth->id . ' and type = 2')->count();
        $page_size = 10;
        $pages = ceil($count / $page_size);
        $offset = ($page - 1) * $page_size;
        $list = Db::name('frag_log')
        ->where('uid =' . $this->auth->id . ' and type = 2')
        ->order('time desc')
        ->limit($offset, $page_size)
        ->select();
        foreach ($list as $k => $v) {
            $list[$k]['time'] = date('Y-m-d H:i:s', $v['time']);
            unset($list[$k]['id']);
            unset($list[$k]['uid']);
        }
        $this->success('ok', [
            'count' => $count,
            'pages' => $pages,
            'list' => $list
        ]);
    }
    
    
    /** 碎片兑换
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function fragExchange()
    {
        $purchaseList = Db::name('purchase_list')->where('status = 1')->select();
        if (empty($purchaseList)) $this->error('宠物更新中');
        $fragList = Db::name('purchase_frag')->where('uid =' . $this->auth->id . ' and num > 0')->select();
        foreach ($purchaseList as $k => $v) {//验证碎片是否足够
            $is_user = 0;
            foreach ($fragList as $kk => $vv) {
                if ($v['id'] == $vv['p_id']) {
                    $is_user = 1;
                    break;
                }
            }
            if ($is_user == 0) $this->error('碎片不足');
        }
        //是否存在指定合成
        $compose_purchase = Db::name('purchase_list')->where('is_compose = 1 and compose_price > 0')->find();
        if (empty($compose_purchase)) $this->error('碎片合成暂未开启,请联系客服');
        try {
            Db::startTrans();
            //增加会员持有资产
            Db::name('user')->where('id ='.$this->auth->id)->update([
                'pets_assets_history' =>  Db::raw('pets_assets_history + '.$compose_purchase['compose_price']),
                'pets_assets' =>  Db::raw('pets_assets + '.$compose_purchase['compose_price'])
            ]);
            //处理碎片
            $pur_arr = array_column($purchaseList, 'id');
            //扣除所有碎玉 1枚
            Db::name('purchase_frag')->where('uid =' . $this->auth->id)->whereIn('p_id', $pur_arr)->setDec('num');
            Db::name('frag_log')->insert([
                'uid' => $this->auth->id,
                'by_uid' => $this->auth->id,
                'p_id' =>   $compose_purchase['id'],
                'name' => $compose_purchase['name'],
                'message' => '合成' . $compose_purchase['name'] . '成功',
                'num' => count($purchaseList),
                'time' => time(),
                'type' => 2
            ]);
            //增加会员持有
            $orderNo = $this->makeRand();
            $user_purchase = Db::name('user_purchase')->insertGetId([
                'uid' => $this->auth->id,
                'tel' => $this->auth->mobile,
                'purchase_id' => $compose_purchase['id'],
                'orderNo' => $orderNo,
                'days' => $compose_purchase['days'],
                'get_price' => $compose_purchase['compose_price'],
                'new_price' => $compose_purchase['compose_price'],
                'rate' => $compose_purchase['rate'],
                'deal_type' => 5,
                'status' => 2,
                'compose_status' => 1,
                'compose_sta' => time(),
                'sta_time' => time()
            ]);
            //审核通过 增加订单
            Db::name('purchase_order')->insert([
                'purchase_id' => $compose_purchase['id'],
                'p_id' => $user_purchase,
                'orderNo' => $orderNo,
                'sell_uid' => $this->auth->id,
                'sell_user' => $this->auth->mobile,
                'price' => $compose_purchase['compose_price'],
                'status' => -1,
                'create_time' => time()
            ]);
            //买家判断升级有效会员
//             if ($this->auth->is_effective == 0) {
//                 $this->upEffective($this->auth->id);
//             }
            $return = [
                'logo' => 'http://' . $_SERVER['HTTP_HOST'] . $compose_purchase['logo'],
                'name' => $compose_purchase['name'],
                'price' => $compose_purchase['compose_price']
            ];
            Db::commit();
            
            $this->success('碎片合成成功', $return);
        } catch (Exception $exception) {
            Db::rollback();
            $this->error('碎片合成失败' . $exception->getMessage());
        }
    }
    
    
    
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////                         预约记录                                                    //////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    
    // 预约记录

    public function appointLog(Request $request)
    {   
        $user_id = $this->userinfo['user_id'];
        
        $page = $request->param('page', 1);
        if ($page <= 0)
            return json(['code' => 2, 'msg' => '参数错误', 'data'=>[]]);//0全部 1预约成功 2预约失败
        $page_size = 10;
        $count = Db::name('mutualaid_log')->where('uid =' . $user_id)->count();
        $pages = ceil($count / $page_size);
        $offset = ($page - 1) * $page_size;
        $list = Db::name('mutualaid_log')->alias('a')
        ->join('mutualaid_list b', 'a.p_id=b.id', 'left')
        ->field('a.id,a.message,a.time,a.status,b.name,a.purchase_status,a.order_status')
        ->where('a.uid =' . $user_id)
        ->order('a.time desc')
        ->limit($offset, $page_size)
        ->select();
        foreach ($list as $k => $v) {
            $list[$k]['time'] = date('Y-m-d H:i:s', $v['time']);
            // $list[$k]['message'] = '预约' . $v['name'];
            //$list[$k]['num'] = floatval($v['num']);
            unset($list[$k]['name']);
            if($v['purchase_status'] != 0 || $v['order_status'] != 0){
                $list[$k]['appoint'] = 1;
                
            }
            unset($list[$k]['purchase_status']);
            unset($list[$k]['order_status']);
        }
        $data= [
            'count' => $count,
            'pages' => $pages,
            'list' => $list,
        ];
        
        return json(['code' => 1, 'msg' => 'success', 'data'=>$data]);
    }
    
    
    
    
    
    
    
    
    
    //生成领养记录
    public function sendFrag($uid,$f_uid, $f_username, $purchase_id, $purchase_name)
    {
        $log = Db::name('mutualaid_frag')->where('uid =' . $f_uid . ' and p_id =' . $purchase_id)->find();
        if (!$log) {
            Db::name('mutualaid_frag')->insert([
                'uid' => $f_uid,
                'p_id' => $purchase_id,
                'num' => 1
            ]);
        } else {
            Db::name('mutualaid_frag')->where('uid =' . $f_uid . ' and p_id =' . $purchase_id)->setInc('num');
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
        $user_id = $this->userinfo['user_id'];
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig('effectiveUserAssets');
        $site_asstes = $config_val;//Config::get('site.effectiveUserAssets');
        $level = Db::name('member_level')->order('id desc')->select();
        $UpLevel = new MMember();//UpLevel();
        //升级有效用户Db::name('user')
        $user = $UpLevel->where('id', $uid)->field('is_effective,f_uid,f_uid_all,pets_assets')->find();
        if ($user['is_effective'] == 0) {//第一次升级有效会员
            $assets = Db::name('member_mutualaid')->where('uid =' . $user_id . ' and status in (1,2,3)')->sum('new_price');
            if ($assets >= $site_asstes) {//$user['pets_assets']
                $UpLevel->where('id', $uid)->setField('is_effective', 1);
                if ($user['f_uid'] != 0) {
                    //团队 及直推
                    $UpLevel->where('id', $user['f_uid'])->setInc('zt_yx_num');
                    $UpLevel->where('id in (' . $user['f_uid_all'] . ')')->setInc('yx_team');
                    $team_list = $UpLevel->where('id in ('.$user['f_uid_all'].')')
                    ->field('id,level,pets_assets_history,zt_yx_num,yx_team,level')->select();
                    foreach ($team_list as $k => $v) {//判断
                        $UpLevel->upLevel($level, $v['id'], $v['level'], $v['pets_assets_history'], $v['zt_yx_num'], $v['yx_team']);
                    }
                }
            }
        }
    }
    
    
    /**
     * @param int $num
     * @return string
     * @throws Exception
     */
    private function makeRand($num = 9)
    {
        mt_srand((double)microtime() * 1000000);//用 seed 来给随机数发生器播种。
        $strand = str_pad(mt_rand(1, 99999), $num, "0", STR_PAD_LEFT);
        if (Db::name('member_mutualaid')->where('orderNo', 'MT' . date('Ymd') . $strand)->count() == 0) {
            return 'MT' . date('Ymd') . $strand;
        }
        $this->makeRand();
    }
    
    
        
    
    ///////////////////////////////////////////////////////  续约      ///////////////////////////////////////////////////////////////////
    
    /**
     * 续约
     * @param Request $request
     */
    public function renewal(Request $request)
    { 
        $user_id = $this->userinfo['user_id'];
        $order_id = getValue($request->param('id',''));
        
        $order_info = Db::name('mutualaid_order')->where(['id'=>$order_id])->find();
        if(empty($order_info)){
            return json(['code' => 2,'msg' => '订单信息错误']);
        }
        
        $mm_info = Db::name('member_mutualaid')->where(['id'=>$order_info['p_id']])->find();
        if(!empty($mm_info) && $mm_info['status'] == 2){

            $ml_info = Db::name('mutualaid_list')->where(['id'=>$mm_info['purchase_id']])->field('days')->find();
            
            $count =  Db::name('mutualaid_examine')->where(['order_id'=>$order_id,'uid'=>$user_id])->count();
            if($count >= 1){
                return json(['code' => 2,'msg' => '该订单已提交续约申请，请耐心等待审核']);
            }
            
            try {
                Db::startTrans();
                
                Db::name('mutualaid_examine')->insert([
                    'uid'     => $user_id,
                    'order_id'=> $order_id,
                    'p_id'    => $order_info['purchase_id'],
                    'sta_time'=> time()
                ]);
                
                /*Db::name('member_mutualaid')->where(['id'=>$order_info['p_id']])->update([
                    'days'   => Db::raw('days +'.$ml_info['days']),
                    'deal_type' => 1,
                    'status' => 1
                ]);
                
                Db::name('mutualaid_order')->where(['id'=>$order_id])->update(['is_exist'=>0]);*/
                
                Db::name('mutualaid_order')->where(['id'=>$order_id])->update(['apply_status'=>1]);
                
                Db::commit();
                return json(['code' => 1,'msg' => '续约成功']);
            } catch (Exception $exception) {
                Db::rollback();
                return json(['code' => 2,'msg' => '续约失败'.$exception->getMessage()]);
            }
            
        }else{
            return json(['code' => 2,'msg' => '数据错误']);
        }
    }
    
    
    
    
}

