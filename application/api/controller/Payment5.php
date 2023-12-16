<?php
namespace app\api\controller;

use think\Db;
use think\Request;
use think\Exception;

class Payment5 extends Base
{
    
    public function PaymentMoney($order,$bank,$channel){
        $data = [
            'amount' =>floor($order['num']*100),
            'publicKey'=>$channel['channel_merchant'],
            'beneficaryName'=>$bank['name'], //用户姓名
            'clientRemark'=>$bank['tel'], //手机号码
            'beneficaryAccount'=>$bank['bank_num'], //银行卡账号
            'ifsCode'=>$bank['ifsc'], //银行卡账号
            'tradeNo'=>$order['order_id'],
            'notifyUrl'=>'https://'.$_SERVER['HTTP_HOST']. '/api/'.$channel['bingfile'].'/withdraw_notify/channel_id/'.$channel['id']
        ];
        $header = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data)),
            'sign: '.$this->xdPaySign($data,$channel['channel_md5key'])
        );
        $ch = curl_init();    
        curl_setopt($ch,CURLOPT_URL,"https://api.threeservices.top/api/withdraw"); //支付请求地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response=curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response,true);
        if($data['code']=="200"){
            $data['msg']="success";
        }else{
            $data['msg']="error";
        }
        return json_encode($data);
    }
    
    public function withdraw_notify(Request $request)
    {
        $headers = $this->request->header();
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
        
        if(empty($headers['sign'])){
            echo 'error:代付回调参数中没有sign';
            exit();
        }
        $sign = $this->xdPaySign($data,$channel['channel_md5key']);
        if($headers['sign'] == $sign){
            if($data['status'] == 1){
               
                $result = $this->withdrawcallbacksuccess($data['tradeNo']);
                 $result = json_decode($result,true);
                //  if($result['code']==1){
                //      echo 'success';exit;
                //  }else{
                //      echo $result['data'];exit;
                //  }
                 echo 'success';exit;
            }elseif($data['status'] == 2){
                $result = $this->withdrawallbackfail($data['tradeNo']);
                echo 'success';exit;
            }
        }else{
            echo 'error:签名校验失败';
            exit();
        }
    }
    
    public function recharge($orderId,$num,$channel){
        $data = [
            'publicKey' => $channel['channel_merchant'],
            'tradeNo' => $orderId,
            'amount' => $num,
            'notifyUrl' => 'https://'.$_SERVER['HTTP_HOST']. '/api/'.$channel['bingfile'].'/notify/channel_id/'.$channel['id'],
            'version' => 'V2'
        ];
        $header = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data)),
            'K-token: '.$this->gettoken1($channel)
        );
        $ch = curl_init();    
        curl_setopt($ch,CURLOPT_URL,$channel['channel_url']); //支付请求地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response=curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response,true);
        //var_dump($response);
        return empty($data['data']['ezjUrl']) ? false : $data['data']['ezjUrl'];
    }
    
    public function notify(){
        
        $headers = $this->request->header();
        $data = $this->request->param();
        $this->paymentLog(__CLASS__.json_encode($headers));
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
        if(empty($data['tradeNo'])){
            echo 'error:回调参数中没有tradeNo';
            exit();
        }
        if(empty($headers['sign'])){
            echo 'error:回调参数中没有sign';
            exit();
        }
        $sign = $this->xdPaySign($data,$channel['channel_md5key']);
        if($headers['sign'] == $sign){
             $amount = $data['rechargeAmount']/100;
            if($data['status'] == 2){
                $result = $this->paycallbacksuccess($data["tradeNo"],$amount);
                 $result = json_decode($result,true);
                //  if($result['code']==1){
                //      echo 'success';exit;
                //  }else{
                //      echo $result['data'];exit;
                //  }
                echo 'success';exit;
            }else{
                $result = $this->paycallbackfail($data["tradeNo"]);
                echo 'success';exit;
            }
        }else{
            echo 'error:签名校验失败';
            exit();
        }
    }
    
    private function gettoken1($channel){
        $data = [
            'publicKey' => $channel['channel_merchant'],
            'sign' => md5($channel['channel_merchant'].$channel['channel_md5key']),
        ];
        $ch = curl_init();    
        curl_setopt($ch,CURLOPT_URL,"https://api.threeservices.top/api/oauth/token"); //获取token地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data)))
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response=curl_exec($ch);
        $data = json_decode($response,true);
        return empty($data['data']) ? false : $data['data'];
    }
    
    protected function xdPaySign($data,$key)
    {
        ksort($data);
        $str = '';
        foreach ($data as $k => $v)
        {
            if($k != 'sign' && !empty($v)){
                if ($str != '') {
                    $str .= '&';
                }
                $str .= $k.'='.$v;
            }
        }
        $str .= "{$key}";
        return strtolower(md5($str));
    }
}

