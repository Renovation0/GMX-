<?php
namespace app\api\controller;

use think\Db;
use think\Request;
use think\Exception;

class Payment2 extends Base
{
    
    public function PaymentMoney($order,$bank,$channel){
        
        $apply_date=date("Y-m-d H:i:s",time());
        $bank_code=$bank["bank_code"]; //收款银行代码   
        $mch_id=$channel['channel_merchant'];  
        $mch_transferId=$order["order_id"];
        $receive_account=$bank["bank_num"];
        $receive_name=$bank["name"];
        $transfer_amount=intval($order["num"]);
        $back_url = 'https://'.$_SERVER['HTTP_HOST']. '/api/'.$channel['bingfile'].'/withdraw_notify/channel_id/'.$channel['id'];
        $remark = $bank["ifsc"];
        $sign_type="MD5";
        $signStr = "";
        $signStr = $signStr."apply_date=".$apply_date."&";
        if($back_url != ""){
            $signStr = $signStr . "back_url=" . $back_url . "&";
        }
        if($bank_code != ""){
            $signStr = $signStr . "bank_code=" . $bank_code . "&";
        }
        
        $signStr = $signStr."mch_id=".$mch_id."&";    
        $signStr = $signStr."mch_transferId=".$mch_transferId."&";    
        $signStr = $signStr."receive_account=".$receive_account."&";
        $signStr = $signStr."receive_name=".$receive_name."&";   
        $signStr = $signStr."remark=".$remark."&";    
        $signStr = $signStr."transfer_amount=".$transfer_amount;
        
        
        $sign = sign($signStr, $channel['df_md5key']);
        
        
        $postdata=array('apply_date'=>$apply_date,
            'bank_code'=>$bank_code,
            'mch_id'=>$mch_id,
            'mch_transferId'=>$mch_transferId,
            'receive_account'=>$receive_account,
            'receive_name'=>$receive_name,
            'transfer_amount'=>$transfer_amount,
            'back_url'=>$back_url,
            'remark'    =>$remark,
            'sign_type'=>$sign_type,
            'sign'=>$sign);
        if(!$channel['df_url']){
            return json(['code'=>2,'data' =>'channel not setting url','msg'=>'error']);
        }
        $response = curlpostform($channel['df_url'],$postdata);
        $data = json_decode($response,true);
        if($data['respCode']=="SUCCESS"){
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
        $channel = $this->getChannel($channelid);
        if(!$channel){
            $this->paymentLog(__CLASS__.'代付回调异步：通道查询为空');
            exit();
        }
        if(empty($data)){
            echo 'error:收到请求，但未发现任何数据';
            exit();
        }
        if(empty($data['merTransferId'])){
            echo 'error:回调参数中没有merTransferId';
            exit();
        }
        if(empty($data['sign'])){
            echo 'error:代付回调参数中没有sign';
            exit();
        }
        
        $tradeResult = $data["tradeResult"];    
        $merTransferId = $data["merTransferId"];
        $merNo = $data["merNo"];
        $tradeNo = $data["tradeNo"];
        $transferAmount = $data["transferAmount"];
        $applyDate = $data["applyDate"];    
        $version = $data["version"];
        $respCode = $data["respCode"];
        $signType = $data["signType"];
        $sign = $data["sign"];
        
        $signStr = "";
        
        $signStr = $signStr."applyDate=".$applyDate."&";   
        $signStr = $signStr."merNo=".$merNo."&";   
        $signStr = $signStr."merTransferId=".$merTransferId."&";    
        $signStr = $signStr."respCode=".$respCode."&";
        $signStr = $signStr."tradeNo=".$tradeNo."&";
        $signStr = $signStr."tradeResult=".$tradeResult."&";
        $signStr = $signStr."transferAmount=".$transferAmount."&";    
        $signStr = $signStr."version=".$version;   
        
        $sign = sign($signStr,$channel['df_md5key']);
        if($data['sign'] == $sign){
            if($data['tradeResult'] == 1){
                 $result = $this->withdrawcallbacksuccess($merTransferId);
                 $result = json_decode($result,true);
                 if($result['code']==1){
                     echo 'success';exit;
                 }else{
                     echo $result['data'];exit;
                 }
            }else{
               $result = $this->withdrawallbackfail($merTransferId);
            }
        }else{
            echo 'error:签名校验失败';
            exit();
        }
    }
    
    public function recharge($orderId,$num,$channel){
        $version = "1.0";
        $mch_id = $channel['channel_merchant'];
        $notify_url = 'https://'.$_SERVER['HTTP_HOST']. '/api/'.$channel['bingfile'].'/notify/channel_id/'.$channel['id'];
        $page_url = "https://gem-xtra.pro/#/mFundingDetails?t=YteRUHQCH%2B6K%2BMlZFG3fZA%3D%3D";
        $mch_order_no = $orderId;
        $pay_type =$channel['channel_type'];
        $trade_amount = $num;
        $order_date = date("Y-m-d H:i:s",time());
        $goods_name = "xstrata";
        $sign_type = "MD5";
    
        $signStr = "";
    
        $signStr = $signStr."goods_name=".$goods_name."&";
        $signStr = $signStr."mch_id=".$mch_id."&";    
        $signStr = $signStr."mch_order_no=".$mch_order_no."&";
        $signStr = $signStr."notify_url=".$notify_url."&";    
        $signStr = $signStr."order_date=".$order_date."&";
        if($page_url != ""){
            $signStr = $signStr."page_url=".$page_url."&";
        }
        $signStr = $signStr."pay_type=".$pay_type."&";
        $signStr = $signStr."trade_amount=".$trade_amount."&";
        $signStr = $signStr."version=".$version;
        $sign =  sign($signStr,$channel['channel_md5key']);
    
        $postdata=array(
        'goods_name'=>$goods_name,
        'mch_id'=>$mch_id,
        'mch_order_no'=>$mch_order_no,
        'notify_url'=>$notify_url,
        'order_date'=>$order_date,
        'pay_type'=>$pay_type,
        'trade_amount'=>$trade_amount,
        'version'=>$version,
        'page_url'=>$page_url,
        'sign_type'=>$sign_type,
        'sign'=>$sign);
        
        if(!$channel['channel_url']){
            return json(['code'=>2,'data' =>'channel not setting url','msg'=>'error']);
        }
        $response = curlpostform($channel['channel_url'],$postdata);
        $data = json_decode($response,true);
        return empty($data['payInfo']) ? false : $data['payInfo'];
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
        
        $amount = $data["amount"];    
        $mchId = $data["mchId"];
        $mchOrderNo = $data["mchOrderNo"];
        $orderDate = $data["orderDate"];
        $orderNo = $data["orderNo"];    
        $oriAmount = $data["oriAmount"];
        $tradeResult = $data["tradeResult"];
        $signType = $data["signType"];
        $sign = $data["sign"];
        
        $signStr = "";
        $signStr = $signStr."amount=".$amount."&";
        $signStr = $signStr."mchId=".$mchId."&";
        $signStr = $signStr."mchOrderNo=".$mchOrderNo."&";
        //$signStr = $signStr."merRetMsg=".$merRetMsg."&";
        $signStr = $signStr."orderDate=".$orderDate."&";
        $signStr = $signStr."orderNo=".$orderNo."&";
        $signStr = $signStr."oriAmount=".$oriAmount."&";
        $signStr = $signStr."tradeResult=".$tradeResult;
        
        $flag = $this->validateSignByKey($signStr,$channel['channel_md5key'],$sign);
        if($flag){
            if($tradeResult == 1){
                 $result = $this->paycallbacksuccess($mchOrderNo,$amount);
                 $result = json_decode($result,true);
                 if($result['code']==1){
                     echo 'success';exit;
                 }else{
                     echo $result['data'];exit;
                 }
            }else{
                $result = $this->paycallbackfail($mchOrderNo);
               
            }
        }
    }
    
    
    public function validateSignByKey($signSource, $key, $retsign) {
        if (!empty($key)) {
             $signSource = $signSource."&key=".$key;
        }
        $signkey = md5($signSource);
        if($signkey == $retsign){
            return true;
        }
        return false;
    }
}

