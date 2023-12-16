<?php
namespace app\api\controller;

use think\Db;
use think\Request;
use think\Exception;

class Payment3 extends Base
{
    
    public function PaymentMoney($order,$bank,$channel){
        
        $data = [
            'merchant' => $channel['channel_merchant'],
            'payCode' => $channel['df_type'],
            'amount' => floor($order['num']*100)/100,
            'orderId' => $order['order_id'],
            'notifyUrl' => 'https://'.$_SERVER['HTTP_HOST']. '/api/'.$channel['bingfile'].'/withdraw_notify/channel_id/'.$channel['id'],
            'bankAccount' => $bank['bank_num'],
            'customName' => $bank['name'],
            'remark'    =>$bank['ifsc']
        ];
        $data['sign'] = xdPaySign($data,$channel['channel_md5key']);
        $response = curlpostjson($channel['df_url'],$data);
        return $response;
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
        if(empty($data['orderId'])){
            echo 'error:回调参数中没有orderId';
            exit();
        }
        if(empty($data['sign'])){
            echo 'error:代付回调参数中没有sign';
            exit();
        }
        $sign = xdPaySign($data,$channel['channel_md5key']);
        if($data['sign'] == $sign){
            if($data['status'] == 1){
                 $result = $this->withdrawcallbacksuccess($data['orderId']);
                 $result = json_decode($result,true);
                 if($result['code']==1){
                     echo 'success';exit;
                 }else{
                     echo $result['data'];exit;
                 }
            }else{
               $result = $this->withdrawallbackfail($data['orderId']);
            }
        }else{
            echo 'error:签名校验失败';
            exit();
        }
    }
    
    public function recharge($orderId,$num,$channel){
        
         $data = [
            'merchant' => $channel['channel_merchant'],
            'payCode' => $channel['channel_type'],
            'amount' => $num,
            'orderId' => $orderId,
            'notifyUrl' => 'https://'.$_SERVER['HTTP_HOST']. '/api/'.$channel['bingfile'].'/notify/channel_id/'.$channel['id'],
            'callbackUrl' => 'https://gem-xtra.pro/#/mFundingDetails?t=YteRUHQCH%2B6K%2BMlZFG3fZA%3D%3D',
        ];
        $data['sign'] = xdPaySign($data,$channel['channel_md5key']);
        $response = curlpostjson($channel['channel_url'],$data);
        $data = json_decode($response,true);
        return empty($data['data']['url']) ? false : $data['data']['url'];
    }
    
    public function notify(){
        
        $data = $this->request->param();
        $this->paymentLog(__CLASS__.json_encode($data));
        
        $channelid = $data['channel_id'];
        unset($data['channel_id']);
        $channel = $this->getChannel($channelid);
        if(!$channel){
            $this->paymentLog(__CLASS__.'充值回调异步：通道查询为空');
            exit();
        }
        
        if(empty($data)){
            echo 'error:收到请求，但未发现任何数据';
            exit();
        }
        if(empty($data['orderId'])){
            echo 'error:回调参数中没有orderId';
            exit();
        }
        if(empty($data['sign'])){
            echo 'error:回调参数中没有sign';
            exit();
        }
        $sign = xdPaySign($data,$channel['channel_md5key']);
        if($data['sign'] == $sign){
            if($data['status'] == 1){
                 $result = $this->paycallbacksuccess($data['orderId'],$data['amount']);
                 $result = json_decode($result,true);
                 if($result['code']==1){
                     echo 'success';exit;
                 }else{
                     echo $result['data'];exit;
                 }
            }else{
                $result = $this->paycallbackfail($mchOrderNo);
               
            }
        }else{
            echo 'error:签名校验失败';
            exit();
        }
    }
}

