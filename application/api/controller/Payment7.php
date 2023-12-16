<?php
namespace app\api\controller;

use think\Db;
use think\Request;
use think\Exception;

class Payment7 extends Base
{
    
    public function PaymentMoney($order,$bank,$channel){
       $data = array(
                "mchid" => $channel['channel_merchant'],
                "channl_id"=>$channel['df_type'],
                "out_trade_no"=>$order["order_id"],
                "money" => $order['num'],
                "bankname" => $bank['account_num'],
                //"subbranch" => $cash['area'],
                "accountname" => $bank['name'],
                "cardnumber" => $bank['bank_num'],
                "mobile" => $bank['tel'],
                "backcode" => $bank['ifsc'],
                "notifyurl" => 'https://'.$_SERVER['HTTP_HOST']. '/api/'.$channel['bingfile'].'/withdraw_notify/channel_id/'.$channel['id']
            
            );
           
            // var_dump($bank['bank_code']);die();
        // $extends = array("account_type"=>2,"cert_id"=>"cpf","mode"=>1);
        // $data['extends'] = base64_encode(json_encode($extends));
        $tjurl = $channel['df_url'];   //提交地址
        ksort($data);
        $md5str = "";
        foreach ($data as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $channel['df_md5key']));
        $data["pay_md5sign"] = $sign;
        $response = curlpostform($tjurl,$data);
        $data = json_decode($response,true);
        $data['msg1']=$data['msg'];
        if($data['status']=="success"){
            $data['msg']="success";
        }else{
            $data['msg']="error";
        }
        return json_encode($data);
    }
    
    public function withdraw_notify(Request $request)
    {
        $data = $this->request->param();
        $this->paymentLog(__CLASS__.'代付回调异步：'.json_encode($data));
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
        if(empty($data['out_trade_no'])){
            echo 'error:回调参数中没有out_trade_no';
            exit();
        }
        if(empty($data['sign'])){
            echo 'error:代付回调参数中没有sign';
            exit();
        }
        $sign = $data['sign'];
        unset($data['sign']);
        
        ksort($data);
        //var_dump($_POST);die;
        $md5str = "";
        foreach ($data as $key => $val) {
            if($val){
                $md5str = $md5str . $key . "=" . $val . "&";
            }
        }
        $sign1 = strtoupper(md5($md5str . "key=" . $channel['df_md5key']));
        if($sign == $sign1){
            if($data['refCode'] == 1){
                 $result = $this->withdrawcallbacksuccess($data['out_trade_no']);
                //  $result = json_decode($result,true);
                //  if($result['code']==1){
                //      echo 'ok';exit;
                //  }else{
                //      echo $result['data'];exit;
                //  }
            }elseif($data['refCode'] == 2||$data['refCode'] == 5||$data['refCode'] == 7||$data['refCode'] == 8){
                $result = $this->withdrawallbackfail($data['out_trade_no']);
            }
            echo 'ok';exit;
        }else{
            echo 'error:签名校验失败';
            exit(); 
        }
    }
    
    public function recharge($orderId,$num,$channel){
        $pay_memberid = $channel['channel_merchant'];   //商户后台API管理获取
        $pay_orderid = $orderId;    //订单号
        $pay_amount = $num;    //交易金额
        $pay_applydate = date("Y-m-d H:i:s");  //订单时间
        $pay_notifyurl = 'https://'.$_SERVER['HTTP_HOST']. '/api/'.$channel['bingfile'].'/notify/channel_id/'.$channel['id'];
        $pay_callbackurl = "https://xstrataplc.net/#/mFundingDetails?t=YteRUHQCH%2B6K%2BMlZFG3fZA%3D%3D";
        $tjurl = $channel['channel_url'];   //提交地址
        $pay_bankcode = $channel['channel_type']; //支付宝扫码  //商户后台通道费率页 获取银行编码
        $native = array(
            "pay_memberid" => $pay_memberid,
            "pay_orderid" => $pay_orderid,
            "pay_applydate" => $pay_applydate,
            "pay_bankcode" => $pay_bankcode,
            "pay_amount" => $pay_amount,
            "pay_notifyurl" => $pay_notifyurl,
            "pay_callbackurl" => $pay_callbackurl,
            "pay_phone"=>8812345677,
           "pay_email"=>"Xstrata@gmail.com",
           "pay_realname"=>"Xstrata"
        );
        ksort($native);
        $md5str = "";
        foreach ($native as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $channel['channel_md5key']));
        $native["pay_md5sign"] = $sign;
        $response = curlpostform($tjurl,$native);
        // var_dump($response);
        $data = json_decode($response,true);
        return empty($data['data']['pay_url']) ? false : $data['data']['pay_url'];
    }
    
    public function notify(){
        
        
        $data = $this->request->param();
        $this->paymentLog(__CLASS__.json_encode($data));
        $channelid = $data['channel_id'];
        $channel = $this->getChannel($channelid);
        if(!$channel){
            $this->paymentLog(__CLASS__.'充值回调异步：通道查询为空');
            exit();
        }
         $returnArray = array( // 返回字段
            "memberid" => $data["memberid"], // 商户ID
            "orderid" =>  $data["orderid"], // 订单号
            "amount" =>  $data["amount"], // 交易金额
            "datetime" =>  $data["datetime"], // 交易时间
            "transaction_id" =>  $data["transaction_id"], // 支付流水号
            "returncode" => $data["returncode"],
        );
        ksort($returnArray);
        reset($returnArray);
        $md5str = "";
        foreach ($returnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $channel['channel_md5key']));
        if ($sign == $data["sign"]) {
            
            if($data["returncode"] == "00"){
                $result = $this->paycallbacksuccess($data["orderid"],$data["amount"]);
                 $result = json_decode($result,true);
                 if($result['code']==1){
                     echo 'ok';exit;
                 }else{
                     echo $result['data'];exit;
                 }
            }else{
                $result = $this->paycallbackfail($data["orderid"]);
            }
        }else{
            
            echo "Signature error";  exit;
        }
    }
}

