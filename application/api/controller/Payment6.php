<?php
namespace app\api\controller;

use think\Db;
use think\Request;
use think\Exception;

class Payment6 extends Base
{
    
    public function PaymentMoney($order,$bank,$channel){
        $email = Db::query('SELECT * FROM zm_payh_email WHERE id >= (SELECT floor(RAND() * (SELECT MAX(id) FROM zm_payh_email))) ORDER BY id LIMIT 1');
// 		var_dump($email[0]['email']);
		$address = Db::query('SELECT * FROM zm_payh_address WHERE id >= (SELECT floor(RAND() * (SELECT MAX(id) FROM zm_payh_address))) ORDER BY id LIMIT 1');
// 		var_dump($address[0]['address']);
       $data = array(
           "merchant_order_id"=>$order["order_id"],
        //   "order_currency"=>"INR",
           "amount"=>$order['num']*100,
        //   "transfer_mode"=>$channel['df_type'],
           "bene_name"=>$bank['name'],
           "bene_phone"=>$bank['tel'],
           "bene_email"=>$email[0]['email'],
           "bene_bank_acct"=>$bank['bank_num'],
           "bene_ifsc"=>$bank['ifsc'],
           "bene_card_no"=>$bank['bank_num'],
           "bene_address"=>$address[0]['address'],
           "notify_url"=>'https://'.$_SERVER['HTTP_HOST']. '/api/'.$channel['bingfile'].'/withdraw_notify/channel_id/'.$channel['id'],
        "remarks"=>"relx"
        );
        $tjurl = $channel['df_url'];   //提交地址
        ksort($data);
        $md5str = "";
        foreach ($data as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
       
        $sign = strtoupper(md5($md5str . "key=" . $channel['df_md5key']));
        // $data["pay_md5sign"] = $sign;
        
        $headers = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data)),
            'mid:'.$channel['channel_merchant'],
            'signature:'.$sign
        );
        
        $log_content = date('H:i:s') . ":\n" . json_encode($tjurl) . "\n";
        $log_content .= json_encode($headers) . "\n";
        $log_content .= json_encode($md5str . "key=" . $channel['df_md5key']) . "\n";
        $log_content .= json_encode($data) . "\n";
        file_put_contents('payment6.log', $log_content, FILE_APPEND);
        
        $response = curlpostjsonheader($tjurl,$data,$headers);
        $data = json_decode($response,true);
        if($data['code']==0){
            $data['msg']="success";
        }else{
            $data['msg']="error";
        }
        return json_encode($data);
    }
    
    public function withdraw_notify(Request $request)
    {
        $headers = $this->request->header();
        $this->paymentLog(__CLASS__.json_encode($headers));
        
        $data = $this->request->param();
        $this->paymentLog(__CLASS__.'代付回调异步：'.json_encode($data));
        if(empty($headers['signature'])){
            echo 'error:回调参数中没有signature';
            exit();
        }
        $channelid = $data['channel_id'];
        unset($data['channel_id']);
        $channel = $this->getChannel($channelid);
        if(!$channel){
            $this->paymentLog(__CLASS__.'代付回调异步：通道查询为空');
            exit();
        }
        if(empty($data)){
            echo 'error:收到请求，但未发现任何数据';
            exit();
        }
        if(empty($data['merchant_order_id'])){
            echo 'error:回调参数中没有merchant_order_id';
            exit();
        }
       
        ksort($data);
      
        $md5str = "";
        foreach ($data as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign1 = strtoupper(md5($md5str . "key=" . $channel['df_md5key']));
        if($headers['signature'] == $sign1){
            if($data['order_status'] == 0){
                 $result = $this->withdrawcallbacksuccess($data['merchant_order_id']);
                 $result = json_decode($result,true);
                //  if($result['code']==1){
                //      echo 'ok';exit;
                //  }else{
                //      echo $result['data'];exit;
                //  }
                 echo 'OK';exit;
            }elseif($data['order_status'] == 1||$data['order_status'] == 2){
                $result = $this->withdrawallbackfail($data['merchant_order_id']);
            }
        }else{
            echo 'error:签名校验失败';
            exit(); 
        }
    }
    
    public function recharge($orderId,$num,$channel){
        $pay_notifyurl = 'https://'.$_SERVER['HTTP_HOST']. '/api/'.$channel['bingfile'].'/notify/channel_id/'.$channel['id'];
        $pay_callbackurl = "https://xstrataplc.net/#/mFundingDetails?t=YteRUHQCH%2B6K%2BMlZFG3fZA%3D%3D";
        $tjurl = $channel['channel_url'];   //提交地址
        $native = array(
            "merchant_order_id" => $orderId,
            "amount" => $num*100,
            "customer_name" => 'Xstrata',
            "customer_phone"=>"9998887771",
            "customer_email"=>"Xstrata@gamail.com",
            'order_note'=>'Xstrata',
            'return_url'=>$pay_callbackurl,
            'notify_url'=>$pay_notifyurl
        );
        ksort($native);
        $md5str = "";
        foreach ($native as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $channel['channel_md5key']));
        $headers = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($native)),
            'mid:'.$channel['channel_merchant'],
            'signature:'.$sign
        );
        // $native['return_url'] = $pay_callbackurl;
        // $native['notify_url'] = $pay_notifyurl;
        $response = curlpostjsonheader($tjurl,$native,$headers);
        $data = json_decode($response,true);
        return empty($data['payment_link']) ? false : $data['payment_link'];
    }
    
    public function notify(){
        
        $headers = $this->request->header();
        $this->paymentLog(__CLASS__.json_encode($headers));
        $data = $this->request->param();
        $this->paymentLog(__CLASS__.json_encode($data));
        $channelid = $data['channel_id'];
        unset($data['channel_id']);
        $channel = $this->getChannel($channelid);
        if(!$channel){
            $this->paymentLog(__CLASS__.'充值回调异步：通道查询为空');
            exit();
        }
        if(empty($headers['signature'])){
            echo 'error:回调参数中没有signature';
            exit();
        }
        ksort($data);
        // reset($returnArray);
        $md5str = "";
        foreach ($data as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $channel['channel_md5key']));
        if ($sign == $headers['signature']) {
            
            if($data["order_status"] == "0"){
                $result = $this->paycallbacksuccess($data["merchant_order_id"],$data["amount"]/100);
                 $result = json_decode($result,true);
                 echo 'OK';exit;
            }elseif($data["order_status"] == "1"){
                $result = $this->paycallbackfail($data["merchant_order_id"]);
            }
        }else{
            
            echo "Signature error";  exit;
        }
    }
}

