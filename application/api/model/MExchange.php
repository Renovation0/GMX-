<?php
namespace app\api\model;
use think\Db;
use think\facade\Cache;
use think\Exception;

class MExchange extends MCommon
{
    protected $table = "zm_order_coin";
    
    /**编号生成
     * @param int $num
     * @return string
     * @throws Exception
     */
    protected function makeRand($num = 9)
    {
        mt_srand((double)microtime() * 1000000);//用 seed 来给随机数发生器播种。
        $strand = str_pad(mt_rand(1, 99999), $num, "0", STR_PAD_LEFT);
        if (Db::name('order_coin')->where('orderNo', 'YKB' . date('Ymd') . $strand)->count() == 0) {
            return 'YKB' . date('Ymd') . $strand;
        }
        $this->makeRand();
    }
    
    /**
     * 卖出
     * @param unknown $order_id
     * @param unknown $user_id
     * @param unknown $mun
     * @param unknown $password
     * @return \think\response\Json
     */
    public function sellTo($order_id,$user_id,$mun,$password){
        //$order = Db::name('order_coin')->where('id', $id)->find();
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig('JY_commission');

        $order = $this->where('id', $order_id)->find();
        if (!$order || $order['status'] != 0)
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'该订单不存在']);
        if ($order['buy_uid'] == $user_id)
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'不能卖给自己']);
        if($order['num'] < $mun){
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'出售数量超出收购数量']);
        }
        
        $member_info = Db::name('member_list')->alias('a')
        ->join('zm_member_level b','a.level = b.id','left')
        ->field('a.*,b.sell_rate')
        ->where('a.id',$user_id)->find();
        if($member_info['level'] == 0){
            $member_info['sell_rate'] = $config_val;
        }
        
        if($member_info['status'] == 1){
            Cache::set('release'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'请先激活账号']);
        }
        if($member_info['status'] == 3){
            Cache::set('release'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'账号已被冻结，无法发布']);
        }
        
        $count = Db::name('paymant_binding')->where(['u_id'=>$user_id])->count();
        if($count < 2){
            Cache::set('release'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'请至少绑定2种交易方式']);
        }

        $sell_rate = $mun*$member_info['sell_rate']/100;
        
        if($member_info['pay_pass'] != md5($password.'pay_passwd')){
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'支付密码错误']);
        }
        if($member_info['balance'] < ($mun+$sell_rate)){
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'YKB金额不足']);
        }

        try {
            Db::startTrans();
            
            if(($order['num'] - $mun) > 0){
                $orderNo = $this->makeRand();
                //扣除相应YKB金额
                $res = Db::name('member_list')->where('id',$member_info['id'])->update([
                    'balance' =>  Db::raw('balance - '.($mun+$sell_rate)),//$mun*$order['price']+$sell_rate
                    'frozen_dot' =>  Db::raw('frozen_dot + '.($mun+$sell_rate))
                ]);
                if (!$res) {
                    throw new \Exception("扣除会员YKB失败");
                }
                
                //扣除对应购买数量
                $res = $this->where('id',$order['id'])->update([
                    'num' =>  Db::raw('num - '.$mun)
                ]);
                if (!$res) {
                    throw new \Exception("扣除订单YKB失败");
                }
                //生成子订单
                $new_order_id = $this->insertGetId([
                    'orderNo'   =>  $orderNo,
                    'buy_uid'   =>  $order['buy_uid'],
                    'buy_user'  =>  $order['buy_user'],
                    'sell_uid'  =>  $member_info['id'],
                    'sell_user' =>  $member_info['tel'],
                    'p_id'      =>  $order['id'],
                    'price'     =>  $order['price'],
                    'num'       =>  $mun,
                    'total_price'=> $order['price']*$mun,
                    'recharge'  =>  $sell_rate,
                    'status'    =>  1,
                    'start_time'=>  time(),
                    'recevice_time'=>time()
                ]);
                if (!$new_order_id) {
                    throw new \Exception("生成子订单失败");
                }
            }elseif(($order['num'] - $mun) == 0){
                //扣除对应购买数量
                $res = $this->where('id',$order['id'])->update([
                    'sell_uid'  =>  $member_info['id'],
                    'sell_user' =>  $member_info['tel'],
                    'num'       =>  Db::raw('num - '.$mun),
                    'recharge'  =>  $sell_rate,
                    'status'    =>  1,
                    'recevice_time'  =>  time()
                ]);
                if (!$res) {
                    throw new \Exception("更改订单状态失败");
                }
                $orderNo = $order['orderNo'];
                $new_order_id = $order['id'];
            }
            
            $res = Db::name('order_coin_log')->insertGetId([
                'orderNo'   =>  $orderNo,
                'order_id'  =>  $new_order_id,
                'uid'       =>  $member_info['id'],
                'phone'     =>  $member_info['tel'],
                'message'   =>  '卖出'.$mun.'YKB,求购者：'.$order['buy_user'].',订单编号：'.$orderNo,
                'time'      =>  time(),
            ]);
            if (!$res) {
                throw new \Exception("生成订单交易日志失败");
            }
            
            $res = Db::name('member_balance_log')->insertGetId([
                'u_id'       =>  $member_info['id'],
                'tel'       =>  $member_info['tel'],
                'o_id'      =>  $order['id'],
                'after_money'  =>  0,
                'change_money' =>  $mun+$sell_rate,
                'type'      =>  101,
                'message'   =>  '冻结卖出'.$mun.'YKB,手续费'.$sell_rate.'YKB求购者：'.$order['buy_user'].',订单编号：'.$orderNo,
                'bo_time'   =>  time(),
                'status'    =>  230
            ]);
            if (!$res) {
                throw new \Exception("生成冻结出售金额日志失败");
            }        
            Db::commit();
            
            Cache::set('sellOrderDetails'.$user_id,0,1); 
            return json(['code' => 1, 'msg' => '申请出售成功']);
        } catch (\Exception $e) {
            Db::rollback();
            Cache::set('sellOrderDetails'.$user_id,0,1); 
            return json(['code' => 2, 'msg' => '申请出售失败'.$e->getMessage()]);
        }
   
    }
 
    
    
    /**
     * 买入
     * @param unknown $order_id
     * @param unknown $user_id
     * @param unknown $mun
     * @param unknown $password
     * @return \think\response\Json
     */
    public function buyTo($order_id,$user_id,$mun,$password){
        //$order = Db::name('order_coin')->where('id', $id)->find();
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig('JY_commission');
        
        $order = $this->where('id', $order_id)->find();
        if (!$order || $order['status'] != 0){
            Cache::set('buyOrderDetails'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'该订单不存在']);
        }
        if ($order['sell_uid'] == $user_id){
            Cache::set('buyOrderDetails'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'不能买入自己的订单']);
        }
        if($order['num'] < $mun){
            Cache::set('buyOrderDetails'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'买入数量超出卖出数量']);
        }
        
        $member_info = Db::name('member_list')->where('id',$user_id)->find();

        $user_info = Db::name('member_list')->alias('a')
        ->join('zm_member_level b','a.level = b.id','left')
        ->field('a.level,b.sell_rate')
        ->where('a.id',$order['sell_uid'])->find();
        if($user_info['level'] == 0){
            $user_info['sell_rate'] = $config_val;
        }
        
        if($member_info['status'] == 1){
            Cache::set('buyOrderDetails'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'请先激活账号']);
        }
        if($member_info['status'] == 3){
            Cache::set('buyOrderDetails'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'账号已被冻结，无法发布']);
        }
        
        $count = Db::name('paymant_binding')->where(['u_id'=>$user_id])->count();
        if($count < 3){
            Cache::set('buyOrderDetails'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'请至少绑定3种交易方式']);
        }
        
        $sell_rate = $mun*$user_info['sell_rate']/100;
        
        if($member_info['pay_pass'] != md5($password.'pay_passwd')){
            Cache::set('buyOrderDetails'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'支付密码错误']);
        }
                
        try {
            Db::startTrans();
            
            if(($order['num'] - $mun) > 0){
                $orderNo = $this->makeRand();
/*              //变更相应YKB金额
                $res = Db::name('member_list')->where('id',$member_info['id'])->update([
                'balance' =>  Db::raw('balance + '.($mun*$order['price']-$sell_rate))
                ]);
                if (!$res) {
                throw new \Exception("变更会员YKB失败");
                } */
                
                //扣除对应购买数量
                $res = $this->where('id',$order['id'])->update([
                    'num' =>  Db::raw('num - '.$mun),
                    'recharge' =>  Db::raw('recharge - '.$sell_rate)
                ]);
                if (!$res) {
                    throw new \Exception("扣除订单YKB失败");
                }
                //生成子订单
                $new_order_id = $this->insertGetId([
                    'orderNo'   =>  $orderNo,
                    'buy_uid'   =>  $member_info['id'],
                    'buy_user'  =>  $member_info['tel'],
                    'sell_uid'  =>  $order['sell_uid'],
                    'sell_user' =>  $order['sell_user'],
                    'p_id'      =>  $order['id'],
                    'price'     =>  $order['price'],
                    'num'       =>  $mun,
                    'total_price'=> $order['price']*$mun,
                    'recharge'  =>  $sell_rate,
                    'status'    =>  1,
                    'start_time'=>  time(),
                    'recevice_time'=>time()
                ]);
                if (!$new_order_id) {
                    throw new \Exception("生成子订单失败");
                }
                //变动数量
                $all_money = $mun+$sell_rate;
                
            }elseif(($order['num'] - $mun) == 0){
                //扣除对应购买数量
                $res = $this->where('id',$order['id'])->update([
                    'sell_uid'  =>  $member_info['id'],
                    'sell_user' =>  $member_info['tel'],
                    'num'       =>  Db::raw('num - '.$mun),
                    'recharge'  =>  $sell_rate,
                    'status'    =>  1,
                    'recevice_time'  =>  time()
                ]);
                if (!$res) {
                    throw new \Exception("更改订单状态失败");
                }
                $orderNo = $order['orderNo'];
                $new_order_id = $order['id'];
                //变动数量
                $all_money = $mun;
            }
            
            $res = Db::name('order_coin_log')->insertGetId([
                'orderNo'   =>  $orderNo,
                'order_id'  =>  $new_order_id,
                'uid'       =>  $member_info['id'],
                'phone'     =>  $member_info['tel'],
                'message'   =>  '买入'.$mun.'YKB,卖出者：'.$order['sell_user'].',订单编号：'.$orderNo,
                'time'      =>  time(),
            ]);
            if (!$res) {
                throw new \Exception("生成子订单交易日志失败");
            }
            
/*             $res = Db::name('member_balance_log')->insertGetId([
                'u_id'      => $order['sell_uid'],
                'tel'       =>  $member_info['tel'],
                'o_id'      =>  $order['id'],
                'after_money'  =>  0,
                'change_money' =>  $all_money,
                'type'      =>  102,
                'message'   =>  '冻结卖出'.$mun.'YKB,手续费'.$sell_rate.'YKB,购买者：'.$member_info['tel'].',订单编号：'.$orderNo,
                'bo_time'   =>  time(),
                'status'    =>  233
            ]);
            if (!$res) {
                throw new \Exception("生成冻结卖出子订单金额日志失败");
            } */
            Db::commit();
            
            Cache::set('buyOrderDetails'.$user_id,0,1);
            return json(['code' => 1, 'msg' => '申请买入订单成功']);
        } catch (\Exception $e) {
            Db::rollback();
            Cache::set('buyOrderDetails'.$user_id,0,1);
            return json(['code' => 2, 'msg' => '申请买入订单失败'.$e->getMessage()]);
        }
                
    }
    
    
    
    //发布交易
    public function release_ykb($user_id,$type,$price,$num,$password){
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig(['JY_PRICE_RANGE','JY_MAX_NUM','JY_MIN_NUM','JY_commission'],2);
        $k_info = Db::name('k')->order('time desc')->limit(1)->field('value')->select();
        $k_price_hig = $k_info[0]['value']+$config_val[0];
        $k_price_low = $k_info[0]['value']-$config_val[0];

        if($price > $k_price_hig || $price < $k_price_low){
            Cache::set('release'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'单价不在区间内']);
        }
        
        if($num > $config_val[1] || $num < $config_val[2]){
            Cache::set('release'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'数量不在区间内']);
        }
        
        $member_info = Db::name('member_list')->alias('a')
        ->join('zm_member_level b','a.level = b.id','left')
        ->field('a.*,b.sell_rate')
        ->where('a.id',$user_id)->find();
        if($member_info['level'] == 0){
            $member_info['sell_rate'] = $config_val[3];
        }
        
        if($member_info['status'] == 1){
            Cache::set('release'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'请先激活账号']);
        }
        if($member_info['status'] == 3){
            Cache::set('release'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'账号已被冻结，无法发布']);
        }

        $count = Db::name('paymant_binding')->where(['u_id'=>$user_id])->count();
        if($count < 3){
            Cache::set('release'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'请至少绑定3种交易方式']);
        }
        
        if($member_info['pay_pass'] != md5($password.'pay_passwd')){
            Cache::set('release'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'支付密码错误']);
        }
       
        //var_dump($member_info['sell_rate']);
        $sell_rate = 0;
        if($type == 1){            
            $sell_rate = $num*$member_info['sell_rate']/100;
            if($member_info['balance'] < $num+$sell_rate){
                Cache::set('release'.$user_id,0,1);
                return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'YKB余额不足']);
            }
        }

        $orderNo = $this->makeRand();
        try {
            Db::startTrans();
            
            if($type == 1){ 
                //出售数量+手续费
                $all_money = $num+$sell_rate;
                
                //扣除对应出售数量
                $res = Db::name('member_list')->where('id',$member_info['id'])->update([
                    'balance' =>  Db::raw('balance - '.$all_money),
                    'frozen_dot' =>  Db::raw('frozen_dot + '.$all_money)
                ]);
                if (!$res) {
                    throw new \Exception("变更会员YKB失败");
                } 
                
                //生成订单
                $order_id = $this->insertGetId([
                    'orderNo'   =>  $orderNo,
                    'sell_uid'  =>  $member_info['id'],
                    'sell_user' =>  $member_info['tel'],
                    'buy_uid'   =>  0,
                    'buy_user'  =>  0,
                    'price'     =>  $price,
                    'initial_num'=>  $num,
                    'num'       =>  $num,
                    'total_price'=> $price*$num,
                    'recharge'  =>  $sell_rate,
                    'status'    =>  0,
                    'start_time'=>  time()
                ]);
                if (!$order_id) {
                    throw new \Exception("生成出售订单失败");
                }
                
                $message = '生成出售订单,出售 '.$num.'YKB,手续费'.$sell_rate;
            }else{
                
                //生成订单
                $order_id = $this->insertGetId([
                    'orderNo'   =>  $orderNo,
                    'sell_uid'  =>  0,
                    'sell_user' =>  0,
                    'buy_uid'   =>  $member_info['id'],
                    'buy_user'  =>  $member_info['tel'],
                    'price'     =>  $price,
                    'initial_num'=> $num,
                    'num'       =>  $num,
                    'total_price'=> $price*$num,
                    'status'    =>  0,
                    'start_time'=>  time()
                ]);
                if (!$order_id) {
                    throw new \Exception("生成购买订单失败");
                }
                $all_money = $num;
                $message = '生成购买订单,求购'.$num;
            }
            
            $res = Db::name('order_coin_log')->insertGetId([
                'orderNo'   =>  $orderNo,
                'order_id'  =>  $order_id,
                'uid'       =>  $member_info['id'],
                'phone'     =>  $member_info['tel'],
                'message'   =>  $message.'YKB,订单编号：'.$orderNo,
                'time'      =>  time(),
            ]);
            if (!$res) {
                throw new \Exception("生成订单交易日志失败");
            }
            if($type == 1){ 
                $res = Db::name('member_balance_log')->insertGetId([
                    'u_id'       =>  $member_info['id'],
                    'tel'       =>  $member_info['tel'],
                    'o_id'      =>  $order_id,
                    'former_money' =>  $member_info['balance'],
                    'change_money' =>  $all_money,
                    'after_money'  =>  $member_info['balance']-$all_money,
                    'type'      =>  101,
                    'message'   =>  '冻结出售订单'.$num.'YKB,手续费'.$sell_rate.'YKB,订单编号：'.$orderNo,
                    'bo_time'   =>  time(),
                    'status'    =>  230
                ]);
                if (!$res) {
                    throw new \Exception("生成冻结出售金额日志失败");
                }
            }
            
            Db::commit();
            Cache::set('release'.$user_id,0,1);
            return json(['code' => 1, 'msg' => '发布订单成功']);
        } catch (\Exception $e) {
            Db::rollback();
            Cache::set('release'.$user_id,0,1);
            return json(['code' => 2, 'msg' => '发布订单失败'.$e->getMessage()]);
        }
    }
    
    
    
    //取消交易
    public function unpublish($order_id,$user_id,$type){
        if($type == 1){
            $where_now = 'sell_uid=' . $user_id . ' AND id='.$order_id;
        }elseif($type == 2){
            $where_now = 'buy_uid=' . $user_id . ' AND id='.$order_id;
        }else{
            Cache::set('unpublish'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'参数错误']);
        }
        
        $member_info = Db::name('member_list')->where('id',$user_id)->field('balance,status')->find();           
        if($member_info['status'] == 1){
            Cache::set('release'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'请先激活账号']);
        }
        if($member_info['status'] == 3){
            Cache::set('release'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'账号已被冻结，无法发布']);
        }

        $order_coin_info = Db::name('order_coin')->field('id,orderNo,status,num,recharge,sell_uid,sell_user')->where($where_now)->find();

        if(empty($order_coin_info)){
            Cache::set('unpublish'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'订单信息错误']);
        }
        if($order_coin_info['status'] == 7){
            Cache::set('unpublish'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'该订单已取消']);
        }
        if($order_coin_info['status'] >= 1 && $order_coin_info['status'] <= 3){
            Cache::set('unpublish'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'该订单已在交易中，无法取消']);
        }
        if($order_coin_info['status'] >= 4 && $order_coin_info['status'] <= 6){
            Cache::set('unpublish'.$user_id,0,1);
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'该订单已在申诉中，无法取消']);
        }    
        
        try {
            Db::startTrans();
            //修改订单状态
            Db::name('order_coin')->where('id',$order_id)->update([
                'status'=>7,
                'cancel_time'=>time()
            ]);
            if($type == 1){
                $all_money = $order_coin_info['num']+$order_coin_info['recharge'];
                //返回相应YKB金额
                $res = Db::name('member_list')->where('id',$user_id)->update([
                    'balance' =>  Db::raw('balance + '.$all_money),//$mun*$order['price']+$sell_rate
                    'frozen_dot' =>  Db::raw('frozen_dot - '.$all_money)
                ]);
                if (!$res) {
                    throw new Exception("退回会员YKB失败");
                }
                $u_id = $order_coin_info['sell_uid'];
                $user = $order_coin_info['sell_user'];
            }else{
                $u_id = $order_coin_info['sell_uid'];
                $user = $order_coin_info['sell_user'];
                $all_money = $order_coin_info['num'];
            }
            
            $res = Db::name('order_coin_log')->insertGetId([
                'orderNo'   =>  $order_coin_info['orderNo'],
                'order_id'  =>  $order_coin_info['id'],
                'uid'       =>  $u_id,
                'phone'     =>  $user,
                'message'   =>  '取消订单：返回'.$all_money.'YKB,订单编号：'.$order_coin_info['orderNo'],
                'time'      =>  time(),
            ]);
            if (!$res) {
                throw new Exception("生成订单交易日志失败");
            }
            if($type == 1){
                $res = Db::name('member_balance_log')->insertGetId([
                    'u_id'       => $u_id,
                    'tel'       =>  $user,
                    'o_id'      =>  $order_coin_info['id'],
                    'former_money' =>  $member_info['balance'],
                    'change_money' =>  $all_money,
                    'after_money'  =>  $member_info['balance']+$all_money,
                    'type'      =>  101,
                    'message'   =>  '退回冻结出售订单'.$all_money.'YKB,订单编号：'.$order_coin_info['orderNo'],
                    'bo_time'   =>  time(),
                    'status'    =>  236
                ]);
                if (!$res) {
                    throw new Exception("生成冻结出售金额日志失败");
                }
            }
            Db::commit();
            Cache::set('unpublish'.$user_id,0,1);
            return json(['code' => 1,'msg' => '取消交易成功']);
        } catch (Exception $exception) {
            Db::rollback();
            Cache::set('unpublish'.$user_id,0,1);
            return json(['code' => 2,'msg' => '取消交易失败'.$exception->getMessage()]);
        }
        
        
        
    }
    
    
}

