<?php
namespace app\api\controller;

use think\Controller;
use think\Db;
use app\api\model\MMember;
use app\api\model\MConfig;
use think\Request;

header('Content-Type: text/html;charset=utf-8');
header('Access-Control-Allow-Origin:*'); // *代表允许任何网址请求
header('Access-Control-Allow-Methods:POST,GET,OPTIONS,DELETE'); // 允许请求的类型
header('Access-Control-Allow-Credentials: true'); // 设置是否允许发送 cookies
header('Access-Control-Allow-Headers: Content-Type,Content-Length,Accept-Encoding,X-Requested-with, Origin');

class Base extends Controller
{
    protected function withdrawcallbacksuccess($orderid){
        $withdraw_info = Db::name('member_bm_withdraw')->where("order_id = '".$orderid."'")->find();
        if(empty($withdraw_info)){
            $this->paymentLog(__CLASS__.'代付回调异步：未找到该订单');
            return json_encode(['code'=>2,'data' =>'未找到该订单','msg'=>'error']);
        }
        if($withdraw_info['status'] != 0 && $withdraw_info['status'] != 3){
            $this->paymentLog(__CLASS__.'代付回调异步：该订单已处理');
            return json_encode(['code'=>2,'data' =>'该订单已处理','msg'=>'error']);
        }
        $member_info = Db::name('member_list')->where('id',$withdraw_info['uid'])->field('id,tel,balance')->find();
        //预约开始
        try {
            Db::startTrans();
            $res = Db::name('member_bm_withdraw')->where('order_id',$orderid)->update([
                'update_time'=>getIndaiTime(time()),
                // 'hash'=>$data['tradeNo'],
                'status'=>1
            ]);
            
            Db::name('member_list')->where('id', $withdraw_info['uid'])->update([
                'withdrawfail'=>0,
                'balance_total' => Db::raw('balance_total +'.$withdraw_info['num'])
            ]);
            
            Db::name('member_balance_log')->insert([
                'u_id' => $withdraw_info['uid'],
                'tel' => $member_info['tel'],
                'former_money' => $member_info['balance'],
                'change_money' => $withdraw_info['num'],
                'after_money' => $member_info['balance'] ,
                'message' => '提现成功'.$withdraw_info['num'],
                'message_e' => 'Withdrawal Successful'.$withdraw_info['num'],
                'type' => 2,
                'bo_time' => getIndaiTime(time()),
                'status' => 92
            ]);
            
            Db::commit();
            $this->paymentLog(__CLASS__.'代付回调异步：'.$orderid.'完成');
            return json_encode(['code'=>1,'data' =>'成功处理成功','msg'=>'success']);
           
        } catch (Exception $exception) {
            Db::rollback();
            $this->paymentLog(__CLASS__.'代付回调异步：'.$orderid.$exception->getMessage());
            return json_encode(['code'=>2,'data' =>$exception->getMessage(),'msg'=>'error']);
        }
        
        
    }
    protected function withdrawallbackfail($orderid){
        $withdraw_info = Db::name('member_bm_withdraw')->where("order_id = '".$orderid."'")->find();
        if(empty($withdraw_info)){
            $this->paymentLog(__CLASS__.'代付回调异步：未找到该订单');
            return json_encode(['code'=>2,'data' =>'未找到该订单','msg'=>'error']);
        }
        if($withdraw_info['status'] != 0 && $withdraw_info['status'] != 3){
            $this->paymentLog(__CLASS__.'代付回调异步：该订单已处理');
            return json_encode(['code'=>2,'data' =>'该订单已处理','msg'=>'error']);
        }
        $member_info = Db::name('member_list')->where('id',$withdraw_info['uid'])->field('id,tel,balance')->find();
        //预约开始
        try {
            Db::startTrans();
            Db::name('member_bm_withdraw')->where('order_id',$orderid)->update([
                'update_time'=>getIndaiTime(time()),
                'status'=>2
            ]);
            
            Db::name('member_balance_log')->insert([
                'u_id' => $withdraw_info['uid'],
                'tel' => $member_info['tel'],
                'former_money' => $member_info['balance'],
                'change_money' => $withdraw_info['num'],
                'after_money' => $member_info['balance'] + $withdraw_info['num'],
                'message' => '提现失败退回'.$withdraw_info['num'],
                'message_e' => 'Withdrawal failed and returned '.$withdraw_info['num'],
                'type' => 2,
                'bo_time' => getIndaiTime(time()),
                'status' => 93
            ]);
            
            Db::name('member_list')->where('id', $withdraw_info['uid'])->update([
                'withdrawfail'=>Db::raw('withdrawfail +1'),
                'balance' => Db::raw('balance +'.$withdraw_info['num'])
            ]);
            Db::commit();
            $this->paymentLog(__CLASS__.'代付回调异步：'.$orderid.'完成');
            return json_encode(['code'=>1,'data' =>'成功处理成功','msg'=>'success']);
           
        } catch (Exception $exception) {
            Db::rollback();
            $this->paymentLog(__CLASS__.'代付回调异步：'.$orderid.$exception->getMessage());
            return json_encode(['code'=>2,'data' =>$exception->getMessage(),'msg'=>'error']);
        }
    }
    
    //异步回调
    protected function getChannel($id){
        $channel = Db::name('channel')->where('id',$id)->find();
        return $channel;
    }
    
    //异步回调
    protected function paycallbacksuccess($orderid,$money){
        $order = Db::name('member_bm_recharge')->where('order_id',$orderid)->find();
        if(!$order){
            $this->paymentLog(__CLASS__.'充值回调异步：未找到该订单');
            return json_encode(['code'=>2,'data' =>'未找到该订单','msg'=>'error']);
        }
        if($order['status'] != 0){
            $this->paymentLog(__CLASS__.'充值回调异步：该订单已处理');
            return json_encode(['code'=>2,'data' =>'该订单已处理','msg'=>'error']);
        }
        //TODO 签名正确
        $member_info = Db::name('member_list')->where('id',$order['uid'])->field('id,tel,rechange_limit')->find();
        //预约开始
        try {
            Db::startTrans();
            $isfirstrecharge = 0;
            //需要增加是否首次冲至
            $member_bm_recharge = Db::name('member_bm_recharge')->where(['uid'=>$order['uid'],'status'=>1])->whereTime('update_time', 'today')->find();
            if(!$member_bm_recharge){
                $isfirstrecharge = 1;
            }
            Db::name('member_bm_recharge')->where('order_id',$orderid)->update([
                'num'=>$money,
                'update_time'=>getIndaiTime(time()),
                'isfirstrecharge'=>$isfirstrecharge,
                'status'=>1
            ]);
            
            $dataLog = [
                'u_id' => $member_info['id'],
                'tel' => $member_info['tel'],
                'o_id' => 0,
                'former_money' => $member_info['rechange_limit'],
                'change_money' => $money,
                'after_money' => $member_info['rechange_limit']+$money,
                'type' => 1,
                'message' => '成功充值'.$money,
                'message_e' => 'Successfully recharge '.$money,
                'bo_time' => getIndaiTime(time()),
                'status' => 90,
            ];
            Db::name('member_balance_log')->insert($dataLog);
            
            Db::name('member_list')->where('id', $member_info['id'])->update([
                'rechange_limit' => Db::raw('rechange_limit +'.$money),
                'rechange_limit_total' => Db::raw('rechange_limit_total +'.$money)
            ]);
            Db::commit();
            $this->paymentLog(__CLASS__.'充值回调异步：'.$orderid.'完成。');
            return json_encode(['code'=>1,'data' =>'成功处理成功','msg'=>'success']);
        } catch (Exception $exception) {
            Db::rollback();
            $this->paymentLog(__CLASS__.'充值回调异步：'.$orderid.$exception->getMessage());
            return json_encode(['code'=>2,'data' =>$exception->getMessage(),'msg'=>'error']);
        }
    }
    
    //异步回调
    protected function paycallbackfail($orderid){
         Db::name('member_bm_recharge')->where('order_id',$orderid)->update([
            'pass_reason'=>'FAILED',
            'update_time'=>getIndaiTime(time()),
            'status'=>2
        ]);
       return json_encode(['code'=>1,'data' =>'失败处理成功','msg'=>'success']);
    }
    // 获取ip
    protected function getIp()
    {
        if(getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
            $ip = getenv("HTTP_CLIENT_IP");
            elseif (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
            else
                if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
                    $ip = getenv("REMOTE_ADDR");
                    else
                        if (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
                            $ip = $_SERVER['REMOTE_ADDR'];
                            else
                                $ip = "unknown";
                                return ($ip);
    }
    
    
    protected function getSign($secret,$data){

        if (isset($data['sign'])){
            unset($data['sign']);
        }
        ksort($data);
        $str = '';
        
        if($data['appid'] != ''){
            $str .= $data['appid'];
        }
        if($data['times'] != ''){
            $str .= $data['times'];
        }        
        if($data['ranstr'] != ''){
            $str .= $data['ranstr'];
        }

        return md5(md5($str).$secret);
    }
    
    //修改反斜杠
    public function updatexg($str){
        return str_replace("\\","/",$str);
    }
    
    // public function msectime(){
    //     list($msec, $sec) = explode(' ', microtime());
    //     $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    //     return $msectime;
    // }
    
        //充值订单编号
    private function makeRandr($num = 6)
    {
        mt_srand((double)microtime() * 1000000);//用 seed 来给随机数发生器播种。
        $strand = str_pad(mt_rand(1, 999999), $num, "0", STR_PAD_LEFT);
        if (Db::name('member_bm_recharge')->where('order_id', 'C' . date('Ymd') . $strand)->count() == 0) {
            return 'C' . date('Ymd') . $strand;
        }
        $this->makeRandr();
    }
    
    public function paymentLog($data){
        Db::name('pay_info')->insert(['text'=>$data,'time'=>date('Y-m-d H:i:s',time())]);
    }
    
    public function rechange(Request $request)
    {   
        $MMember = new MMember();
        $user_id = 1;//$this->userinfo['user_id'];
        $member_info = $MMember->getInfo(['id'=>$user_id]);
        $pay_id = intval($request->post('pay_id',222));
        $num = abs(getValue($request->post('num',800)));
        if(empty($num)){
            return json(['code'=>2,'data' =>[],'msg'=> getErrorInfo(MISS_FAIL)]);
        }
        
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig(['PAY_ONE','PAY_TWO','PAY_ONE_NAME','PAY_TWO_NAME'],2);
        
        $orderCode = $this->makeRandr();//订单编号
        
    
        $PaymentTwo = new Paymentthree();                          //$member_info['user']                                   //$member_info['tel']
        $result_str = $PaymentTwo->RechargeMoney($orderCode,$num,$member_info['user'],'showWin@google.com','91931340331');
        var_dump($result_str);//exit();
        
        $data = json_decode($result_str,true);
        
        if(!empty($data['platOrder']) && $data['retCode'] == 'SUCCESS'){
            Db::name('member_bm_recharge')->insert([
                'order_id'=>$orderCode,
                'uid'=>$user_id,
                'user'=>$member_info['user'],
                'tel'=>$member_info['tel'],
                'num'=>$num,
                'create_time'=>time(),
                'status'=>0
            ]);
            
            return json(['code'=>1,'data' =>$data]);
        }else{
            return json(['code'=>2,'data' =>$data,'msg'=>'error']);
        }

        
    }
    
    
    public function abc(Request $request){
        $data = $this->request->param();

        var_dump($data['msg']['transaction_id']);
        
    }
    
}

