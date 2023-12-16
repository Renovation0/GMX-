<?php
namespace app\api\controller;

use think\Db;
use think\Request;
use think\Exception;

class Paymentserven extends Base
{
    private  $key = 'SBQH4E6VPAHKEXUJZ1FOH5TFMJJS41Z4';//
    private  $url = '';//'https://api.victory-pay.com/payweb/recharge';
    private  $pay_memberid = '4508540';
    
    public $method = 'AES-128-CBC'; //AES加密定义不要更改
    public $password = 'ga5AmS88P1UXraN2'; //AES密钥
    public $authorizationKey = 'conR9ITH0Z';  //请求头中的商户Key
    
    
    private  $income_host_callback = '/api/Paymentserven/RechangeCallBack'; //充值回调
    private  $payment_host_callback = '/api/Paymentserven/PaymentCallBack'; //提现回调
    
    
    
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
        //$sign = md5($string);
        $sign = md5($string);//strtoupper()
        //$string .= '&sign='.md5($string);
        //$data['key'] = $key;
        //$data['sign_type'] = 'MD5';
        //$data['return_type'] = 'json';
        $data['sign_type'] = 'MD5';
        $data['sign'] = $sign;
        
        $json = json_encode($data);
        Db::name('pay_info')->insert(['text'=>'发起充值B：'.$json,'time'=>date('Y-m-d H:i:s',time())]);

        
        return $data;
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
    public function RechargeMoney($orderCode,$amount,$name,$email,$phone){
        
        $url='http://crash.ludocrash.org/payin';
        $mchId=$this->pay_memberid;
        $orderNo= $orderCode;
        $amount=$amount;
        $bankcode="all";
        $notifyUrl='http://'.$_SERVER['HTTP_HOST'].$this->income_host_callback;
        $returnUrl='http://'.$_SERVER['HTTP_HOST'].$this->income_host_callback.'Tb';
        $remark = '{"phone":"'.$phone.'","name":"'.$name.'","email":"'.$email.'"}';
        $key=$this->key;
        $type = 'indiapaytm';
        
        $sign_str = '';
    	$sign_str  = $sign_str . 'amount=' . $amount;
    	$sign_str  = $sign_str . '&bankcode=' . $bankcode;
    	$sign_str  = $sign_str . '&mchid=' . $mchId;
    	$sign_str  = $sign_str . '&notifyurl=' . $notifyUrl;
    	$sign_str  = $sign_str . '&orderno=' . $orderNo;
    	$sign_str  = $sign_str . '&remark=' . $remark;
    	$sign_str  = $sign_str . '&returnurl=' . $returnUrl;
    	$sign_str  = $sign_str . '&type=' . $type;
    	$sign_str  = $sign_str . '&key=' . $key;
    	
    	$sign = strtoupper(md5($sign_str));
    	
        //var_dump($sign_str);
        //Db::name('pay_info')->insert(['text'=>'发起充值2：'.$sign_str,'time'=>date('Y-m-d H:i:s',time())]);
        $sign = strtoupper(md5($sign_str));
        //Db::name('pay_info')->insert(['text'=>'发起充值2：'.$sign,'time'=>date('Y-m-d H:i:s',time())]);
        //格式化
        $data=sprintf("amount=%s&bankcode=%s&remark=%s&mchid=%s&notifyurl=%s&orderno=%s&product=%s&returnurl=%s&sign=%s",
              					$amount,
              					$bankcode,
              					$remark,
              					$mchId,
              					$notifyUrl,
              					$orderNo,
              					$type,
             					$returnUrl,
              					$sign);
        						
        
        $post_data = array(
           'amount' => $amount,
           'bankcode' => $bankcode,
           'remark' => $remark,
           'mchid' => $mchId,
           'notifyurl' => $notifyUrl,
           'orderno' => $orderNo,
           'type' => $type,
           'returnurl' => $returnUrl,
           'sign' => $sign
        );
        //var_dump($post_data);exit();
        $resp=$this->send_post($url,$post_data);
        //print_r($resp);exit();
        
        return $resp;
    }
    
    /**加密
     * @param array $data
     * @return string
     */
    public function encryptionAes(array $data)
    {
        //修改
        $jsonData = json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE );
        $aesSecret = bin2hex(openssl_encrypt($jsonData, $this->method,$this->password,  OPENSSL_RAW_DATA, $this->password));
        return $aesSecret;
    }
    
    public static function sign($signSource,$key) {
        if (!empty($key)) {
              $signSource = $signSource."&key=".$key;
        }
        return  md5($signSource);
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
    public function RechangeCallBackTb($data){
        
        $data = $this->request->param();
        
        //var_dump($data);exit();
        // if(empty($data)){
        //     $json = '收到请求，但未发现任何数据';
        // }else{
        $json = json_encode($data);
        // }
        Db::name('pay_info')->insert(['text'=>'充值同步2：'.$json,'time'=>date('Y-m-d H:i:s',time())]);
        
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
        Db::name('pay_info')->insert(['text'=>'充值回调异步2：'.$json,'time'=>date('Y-m-d H:i:s',time())]);
        
        // if($data['returncode'] == 'PENDING'){
        //     exit();
        // }
        
        $res = Db::name('member_bm_recharge')->where('order_id',$data['orderno'])->find();
        
        if(!$res){
            Db::name('pay_info')->insert(['text'=>'充值回调异步2：未找到该订单！','time'=>date('Y-m-d H:i:s',time())]);
            echo 'success';
            exit();
        }
        
        if($res['status'] != 0){
            Db::name('pay_info')->insert(['text'=>'充值回调异步2：该订单已处理','time'=>date('Y-m-d H:i:s',time())]);
            echo 'success';
            exit();
        }
        
        $member_info = Db::name('member_list')->where('id',$res['uid'])->field('id,tel,rechange_limit')->find();
        
        
        //预约开始
        try {
            Db::startTrans();
            if($data['status'] == 2){
                Db::name('member_bm_recharge')->where('order_id',$data['orderno'])->update([
                    'num'=>$data['amount'],
                    'update_time'=>getIndaiTime(time()),
                    //'hash'=>$data['orderNo'],
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
                Db::name('member_bm_recharge')->where('order_id',$data['orderno'])->update([
                    'pass_reason'=>'FAILED',
                    'update_time'=>getIndaiTime(time()),
                    'status'=>2
                ]);
            }
            
            Db::commit();
            
            Db::name('pay_info')->insert(['text'=>'充值回调异步2：'.$data['orderno'].'完成。','time'=>date('Y-m-d H:i:s',time())]);
            echo 'success';
            //return"OK";
            //exit();
            //return json(['code' => 1,'msg' => getErrorInfo(SUCCESS)]);
        } catch (Exception $exception) {
            Db::rollback();
            Db::name('pay_info')->insert(['text'=>'充值回调异步2：'.$data['orderno'].$exception->getMessage(),'time'=>date('Y-m-d H:i:s',time())]);
            echo 'success';
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
        Db::name('pay_info')->insert(['text'=>'提现回调异步4：'.$json,'time'=>date('Y-m-d H:i:s',time())]);
        //exit();
        
        $withdraw_info = Db::name('member_bm_withdraw')->where("order_id = '".$data['merTransferId']."'")->find();
        if(empty($withdraw_info)){
            Db::name('pay_info')->insert(['text'=>'提现回调异步4：未找到该订单！','time'=>date('Y-m-d H:i:s',time())]);
            echo 'success';
            exit();
        }
        
        if($withdraw_info['status'] != 0 && $withdraw_info['status'] != 3){
            Db::name('pay_info')->insert(['text'=>'提现回调异步4：该订单已处理','time'=>date('Y-m-d H:i:s',time())]);
            echo 'success';
            exit();
        }
        
        $member_info = Db::name('member_list')->where('id',$withdraw_info['uid'])->field('id,tel,balance')->find();
        
        //merchant_id":"8000310","mer_order_num":"T20220623301506","price":"300.00","finish_time":"2022-06-23 18:24:14","order_num":"2022062317360611064","sign":"563DB5AF66FF2DA9271641F05C4D57CB","sign_type":"MD5"
        
        //{"code":201,"msg":"Error\uff1a\u4ee3\u4ed8\u63d0\u4ea4\u5931\u8d25\uff1aInvalid IFSC Code in Bank Account","data":{"merchant_id":"8000311","mer_order_num":"T20220623585874","price":"300.00","finish_time":"2022-06-23 20:29:00","order_num":"2022062317564678503","sign":"CFD62CA9A685435771BEC4A8F1A616C5","sign_type":"MD5"}}
        
/*        {"tradeResult":"2","merTransferId":"T20220801904944","merNo":"100668003","tradeNo":"281253313150","transferAmount":"100.00","sign":"2c1b27f80cd809c23c8cb5dcf8116bbf","signType":"MD5","applyDate":"2022-08-01 19:25:36","version":"1.0","respCode":"SUCCESS"}*/
        
        //预约开始
        try {
            Db::startTrans();
            if($data['tradeResult'] == 1){
                $res = Db::name('member_bm_withdraw')->where('order_id',$data['merTransferId'])->update([
                    'update_time'=>getIndaiTime(time()),
                    'hash'=>$data['tradeNo'],
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
                Db::name('member_bm_withdraw')->where('order_id',$data['merTransferId'])->update([
                    'update_time'=>getIndaiTime(time()),
                    'status'=>2
                ]);
                
                Db::name('member_balance_log')->insert([
                    'u_id' => $withdraw_info['uid'],
                    'tel' => $member_info['tel'],
                    'former_money' => $member_info['balance'],
                    'change_money' => $withdraw_info['num'],
                    'after_money' => $member_info['balance'] + $withdraw_info['num'],
                    'message' => '提现失败退回'.$data['data']['price'],
                    'message_e' => 'Withdrawal failed and returned '.$withdraw_info['num'],
                    'type' => 2,
                    'bo_time' => getIndaiTime(time()),
                    'status' => 93
                ]);
                
                Db::name('member_list')->where('id', $withdraw_info['uid'])->update([
                    'balance' => Db::raw('balance +'.$withdraw_info['num'])
                ]);
            }
            
            Db::commit();
            
            Db::name('pay_info')->insert(['text'=>'提现回调异步4：'.$data['merTransferId'].'完成。','time'=>date('Y-m-d H:i:s',time())]);
            echo 'success';
            //return"OK";
            //return json(['code' => 1,'msg' => getErrorInfo(SUCCESS)]);
        } catch (Exception $exception) {
            Db::rollback();
            Db::name('pay_info')->insert(['text'=>'提现回调异步4：'.$data['merTransferId'].$exception->getMessage(),'time'=>date('Y-m-d H:i:s',time())]);
            echo 'success';
            //return"OK";
            //return json(['code' => 2,'msg' => getErrorInfo(ADD_FAIL).$exception->getMessage()]);
        }
        
    }
    
    
}

