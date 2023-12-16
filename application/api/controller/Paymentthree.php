<?php
namespace app\api\controller;

use think\Db;
use think\Request;
use think\Exception;

class Paymentthree extends Base
{
    private  $key = '5b1cyo88fgbb5zhvg6fjp8yj78vzsgz9';
    private  $url = 'http://pay.yingshanghui.xyz';
    private  $pay_memberid = '12300';
    
    private  $income_host_callback = '/api/paymentthree/RechangeCallBack'; //充值回调
    private  $payment_host_callback = '/api/paymentthree/PaymentCallBack'; //提现回调
    
    
    /**
     * 生成签名
     *
     * @param 参数map
     * @param key       商户密钥
     * @return 添加签名后的参数map
     */
    public function Map($data,$type) {
        ksort($data);
        
        $key = $this->key;
        $string = $this->generateSignString($data,$key);
        $sign = md5($string);
        //$string .= '&sign='.md5($string);
        //$data['key'] = $key;
        $data['pay_md5sign'] = $sign;
        $data['return_type'] = 'json';
        
        $json = json_encode($data);
        Db::name('pay_info')->insert(['text'=>'发起充值3：'.$json,'time'=>date('Y-m-d H:i:s',time())]);
        
        
/*        Db::name('member_bm_recharge')->insert([
                'order_id'=>$data['pay_orderid'],
                'uid'=>1,
                'user'=>'13800138000',
                'tel'=>'13800138000',
                'num'=>'222',
                'create_time'=>time(),
                'status'=>0
            ]);*/
            
 /*       
        $url = "http://pay.yingshanghui.xyz/Pay-payment.aspx";
        //;charset=UTF-8
        $headers = [
            //'Content-Type:application/json'
            'Content-Type:application/x-www-form-urlencoded'
        ];
        
        $curl = curl_init();
        $param[CURLOPT_URL] = $url;
        $param[CURLOPT_HTTPHEADER] = $headers;
        $param[CURLOPT_RETURNTRANSFER] = true;
        $param[CURLOPT_FOLLOWLOCATION] = true;
        $param[CURLOPT_POST] = true;
        $param[CURLOPT_POSTFIELDS] = $data;
        $param[CURLOPT_SSL_VERIFYPEER] = false;
        $param[CURLOPT_SSL_VERIFYHOST] = false;
        curl_setopt_array($curl,$param); //传参数
        $data = curl_exec($curl);       //执行命令
        curl_close($curl);
        
        echo $data;exit();*/
            //var_dump($data); echo '<br/>';
        

        $resp=$this->send_post($this->url.'/Pay-payment.aspx',$data);
        
        return $resp;
    }
    
    /**
     * 将Map中的key按Ascii码进行升序排序，拼接成 key1=val1&key2=val2&key3=val3....&key=密钥 格式
     *
     * @param sourceMap
     * @param key       密钥
     * @return
     */
    public function generateSignString($data,$key) {
        if (!empty($data)) {
            $string = '';
            foreach($data as $k => $v){
                $string .= $k.'='.$v.'&';
            }
        }
        $string .= 'key='.$key;
        return $string;
    }
    
    
    function send_post($url, $post_data) {
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }
    
    
    //收银台收款 --用户充值
    /**
     *
     * @param unknown $orderCode
     * @param unknown $amount
     * @param unknown $name
     * @param unknown $email
     * @param unknown $phone
     * @param unknown $remark
     */
    public function RechargeMoney($orderCode,$amount){
        
        $data = [
            'pay_memberid'=>$this->pay_memberid,
            'pay_orderid'=>$orderCode,
            'pay_applydate'=>date('Y-m-d H:i:s',time()),
            'pay_bankcode'=>'ydsep',
            'pay_notifyurl'=>'http://'.$_SERVER['HTTP_HOST'].$this->income_host_callback,
            'pay_callbackurl'=>'http://'.$_SERVER['HTTP_HOST'].$this->income_host_callback,
            'pay_amount'=>$amount
        ];
        
        //var_dump($data);exit();
        return $this->Map($data, 1);
    }
    
    /**
     *
     * @param unknown $orderCode
     * @param unknown $amount
     * @param unknown $name
     * @param unknown $email
     * @param unknown $phone
     * @param unknown $remark
     */
    public function RechargeMoneyCallBack($data){
        
        $data = $this->request->param();
        
        //var_dump($data);exit();
        // if(empty($data)){
        //     $json = '收到请求，但未发现任何数据';
        // }else{
        $json = json_encode($data);
        // }
        Db::name('pay_info')->insert(['text'=>'充值异步3：'.$json,'time'=>date('Y-m-d H:i:s',time())]);

    }
    
    
    
    //充值回调
    public function RechangeCallBack(Request $request)
    {   
        //echo 'OK';
        $data = $this->request->param();
        if(empty($data)){
            $json = '收到请求，但未发现任何数据';
        }else{
            $json = json_encode($data);
        }
        Db::name('pay_info')->insert(['text'=>'充值回调异步3：'.$json,'time'=>date('Y-m-d H:i:s',time())]);
        
        // if($data['returncode'] == 'PENDING'){
        //     exit();
        // }
        
        $res = Db::name('member_bm_recharge')->where('order_id',$data['orderid'])->find();
        if(!$res){
            Db::name('pay_info')->insert(['text'=>'充值回调异步3：未找到该订单！','time'=>date('Y-m-d H:i:s',time())]); 
            //echo 'OK';
            exit();
        }
                
        if($res['status'] != 0){
            Db::name('pay_info')->insert(['text'=>'充值回调异步3：该订单已处理','time'=>date('Y-m-d H:i:s',time())]); 
            //echo 'OK';
            exit();
        }
        
        $member_info = Db::name('member_list')->where('id',$res['uid'])->field('id,tel,rechange_limit')->find();


        //预约开始
        try {
            Db::startTrans();
            if($data['returncode'] == 00){
                Db::name('member_bm_recharge')->where('order_id',$data['orderid'])->update([
                    'num'=>$data['amount'],
                    'update_time'=>getIndaiTime(time()),
                    'hash'=>$data['transaction_id'],
                    'status'=>1
                ]);
                
                $data6 = [
                    'u_id' => $member_info['id'],
                    'tel' => $member_info['tel'],
                    'o_id' => 0,
                    'former_money' => $member_info['rechange_limit'],
                    'change_money' => $data['amount'],
                    'after_money' => $member_info['rechange_limit']+$data['amount'],
                    'type' => 1,
                    'message' => '成功充值'.$data['amount'],
                    'message_e' => 'Successfully recharge '.$data['amount'],
                    'bo_time' => getIndaiTime(time()),
                    'status' => 90,
                ];
                Db::name('member_balance_log')->insert($data6);
                
                Db::name('member_list')->where('id', $member_info['id'])->update([
                    'rechange_limit' => Db::raw('rechange_limit +'.$data['amount']),
                    'rechange_limit_total' => Db::raw('rechange_limit_total +'.$data['amount'])
                ]);
                
            }else{
                Db::name('member_bm_recharge')->where('order_id',$data['orderid'])->update([
                    'pass_reason'=>'FAILED',
                    'update_time'=>getIndaiTime(time()),
                    'status'=>2
                ]);
            }
            
            Db::commit();
            
            Db::name('pay_info')->insert(['text'=>'充值回调异步3：'.$data['orderid'].'完成。','time'=>date('Y-m-d H:i:s',time())]); 
            echo 'OK';
            //return"OK";
            //exit();
            //return json(['code' => 1,'msg' => getErrorInfo(SUCCESS)]);
        } catch (Exception $exception) {
            Db::rollback();
            Db::name('pay_info')->insert(['text'=>'充值回调异步3：'.$data['orderid'].$exception->getMessage(),'time'=>date('Y-m-d H:i:s',time())]); 
            echo 'OK';
            //return 'OK';
            //return"OK";
            //exit();
            //return json(['code' => 2,'msg' => getErrorInfo(ADD_FAIL).$exception->getMessage()]);
        }
    }
    
    
    
    //收款回调
    public function PaymentCallBack(Request $request)
    {
        //echo 'OK';
        $data = $this->request->param();//var_dump($data);exit();
        if(empty($data)){
            $json = '收到请求，但未发现任何数据';
        }else{
            $json = json_encode($data);
        }
        Db::name('pay_info')->insert(['text'=>'提现回调异步3：'.$json,'time'=>date('Y-m-d H:i:s',time())]);
        //exit();
        
        $withdraw_info = Db::name('member_bm_withdraw')->where("order_id = '".$data['orderid']."'")->find();
        if(empty($withdraw_info)){
            Db::name('pay_info')->insert(['text'=>'提现回调异步3：未找到该订单！','time'=>date('Y-m-d H:i:s',time())]); return"OK";
            exit();
        }
                        
        if($withdraw_info['status'] != 0 && $withdraw_info['status'] != 3){
            Db::name('pay_info')->insert(['text'=>'提现回调异步3：该订单已处理','time'=>date('Y-m-d H:i:s',time())]); return"OK";
            exit();
        }
        
        $member_info = Db::name('member_list')->where('id',$withdraw_info['uid'])->field('id,tel,balance')->find();

        //预约开始
        try {
            Db::startTrans();
            if($data['returncode'] == '00'){
                $res = Db::name('member_bm_withdraw')->where('order_id',$data['orderid'])->update([
                    'update_time'=>getIndaiTime(time()),
                    'hash'=>$data['transaction_id'],
                    'status'=>1
                ]);
                
                Db::name('member_list')->where('id', $withdraw_info['uid'])->update([
                    'balance_total' => Db::raw('balance_total +'.$withdraw_info['num'])
                ]);
                
                Db::name('member_balance_log')->insert([
                    'u_id' => $withdraw_info['uid'],
                    'tel' => $member_info['tel'],
                    'former_money' => 0,
                    'change_money' => $withdraw_info['num'],
                    'after_money' => 0,
                    'message' => '提现成功'.$withdraw_info['num'],
                    'message_e' => 'Withdrawal Successful'.$withdraw_info['num'],
                    'type' => 2,
                    'bo_time' => getIndaiTime(time()),
                    'status' => 92
                ]);
                
            }else{
                Db::name('member_bm_withdraw')->where('order_id',$data['orderid'])->update([
                    'update_time'=>getIndaiTime(time()),
                    'status'=>2
                ]);
                
                Db::name('member_balance_log')->insert([
                    'u_id' => $withdraw_info['uid'],
                    'tel' => $member_info['tel'],
                    'former_money' => $member_info['balance'],
                    'change_money' => $data['amount'],
                    'after_money' => $member_info['balance'] + $data['amount'],
                    'message' => '提现失败退回'.$data['amount'],
                    'message_e' => 'Withdrawal failed and returned '.$data['amount'],
                    'type' => 2,
                    'bo_time' => getIndaiTime(time()),
                    'status' => 93
                ]);
                
                Db::name('member_list')->where('id', $withdraw_info['uid'])->update([
                    'balance' => Db::raw('balance +'.$data['amount']),
                    //'balance_total' => Db::raw('balance_total -'.$data['amount'])
                ]);
            }
            
            Db::commit();
            
            Db::name('pay_info')->insert(['text'=>'提现回调异步3：'.$data['orderid'].'完成。','time'=>date('Y-m-d H:i:s',time())]); 
            echo 'OK';
            //return"OK";
            //return json(['code' => 1,'msg' => getErrorInfo(SUCCESS)]);
        } catch (Exception $exception) {
            Db::rollback();
            Db::name('pay_info')->insert(['text'=>'提现回调异步3：'.$data['orderid'].$exception->getMessage(),'time'=>date('Y-m-d H:i:s',time())]); 
            echo 'OK';
            //return"OK";
            //return json(['code' => 2,'msg' => getErrorInfo(ADD_FAIL).$exception->getMessage()]);
        }
        
    }
    
    
    
}

