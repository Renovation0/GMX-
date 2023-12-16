<?php
namespace app\api\controller;

use think\Db;
use think\Request;
use think\Exception;

class Payment1 extends Base
{
    
    public function PaymentMoney($order,$bank,$channel){
        
        $data = [
            'merchant_id' => $channel['channel_merchant'],//商户id
            'notify_url' => 'https://'.$_SERVER['HTTP_HOST']. '/api/'.$channel['bingfile'].'/withdraw_notify/channel_id/'.$channel['id'],
            'mer_order_num' => $order['order_id'],
            'price' => $order['num'],
            'account_name' => $bank['name'],//收款人银行卡姓名
            'account_num' => $bank['bank_num'],//收款银行账号
            'account_bank' => $bank['account_num'],//收款人银行名称
            'remark' => $bank['ifsc'],//ifsc码
            'timestamp' => time(),
        ];
        $sign = strtoupper(md5key(asc_sort($data),$channel['channel_md5key']));
        $data['sign'] = $sign;
        $data['sign_type'] = 'MD5'; 
        $response = curlpostjson($channel['df_url'],$data);
        return $response;
    }
    
    public function withdraw_notify(Request $request)
    {
        $res = $this->request->param();
        $this->paymentLog(__CLASS__.'代付回调异步：'.json_encode($res));
        $channelid = $res['channel_id'];
        $data = file_get_contents("php://input");
        if(empty($data)){
             $this->paymentLog(__CLASS__.'代付回调异步：返回数据为空');
            //TODO 参数为空返回处理
            echo 'error:收到请求，但未发现任何数据';exit;
            
        }
        $return_data_new = json_decode($data, true);
        $return_data = $return_data_new['data'];
        
        $channel = $this->getChannel($channelid);
        if(!$channel){
            $this->paymentLog(__CLASS__.'代付回调异步：通道查询为空');
            exit();
        }
        
        //签名校验
        $sign = $return_data['sign'];
        unset($return_data['sign']);
        unset($return_data['sign_type']);
        if($sign == strtoupper(md5key(asc_sort($return_data),$channel['channel_md5key']))){
            if($return_data_new['code'] == 200){
                 $result = $this->withdrawcallbacksuccess($return_data['mer_order_num']);
                 $result = json_decode($result,true);
                 if($result['code']==1){
                     echo 'success';exit;
                 }else{
                     echo $result['data'];exit;
                 }
            }else{
                $result = $this->withdrawallbackfail($return_data['mer_order_num']);
               
            }
        }else{
            $this->paymentLog(__CLASS__.'代付回调异步：签名不正确');
            echo 'error';exit;
        }
        
    }
    
    public function recharge($orderId,$num,$channel){
        
        $data = [
            'merchant_id' => $channel['channel_merchant'],
            'mer_order_num' => $orderId,
            'price' => $num,
            'pay_code' => $channel['channel_type'],
            'attach' => 'xstarta',
            'notify_url' => 'https://'.$_SERVER['HTTP_HOST']. '/api/'.$channel['bingfile'].'/notify/channel_id/'.$channel['id'],
            'page_url' => 'https://xstrataplc.net/#/mFundingDetails?t=YteRUHQCH%2B6K%2BMlZFG3fZA%3D%3D',
            'order_date' => date("Y-m-d H:i:s",time()),
            'timestamp' => time(),
        ];
        if(!$channel['channel_url']){
            return json(['code'=>2,'data' =>'channel not setting url','msg'=>'error']);
        }
        $sign = strtoupper(md5key(asc_sort($data),$channel['channel_md5key']));
        $data['sign'] = $sign;
        $data['sign_type'] = "MD5";
        $response = curlpostjson($channel['channel_url'],$data);
        $data = json_decode($response,true);
        return empty($data['data']['pay_url']) ? false : $data['data']['pay_url'];
    }
    
    public function notify(){
        $res = $this->request->param();
        $channelid = $res['channel_id'];
        $data = file_get_contents("php://input");
        if(empty($data)){
            //TODO 参数为空返回处理
            $this->paymentLog(__CLASS__.'充值回调异步：返回数据为空');
            echo 'fail';exit;
        }
        $return_data_new = json_decode($data, true);
        $return_data = $return_data_new['data']; 
      
        if(empty($return_data['mer_order_num'])){
            echo 'error:回调参数中没有mer_order_num';
            exit();
        }
        $this->paymentLog(__CLASS__.$data);
        $channel = $this->getChannel($channelid);
        if(!$channel){
            $this->paymentLog(__CLASS__.'充值回调异步：通道查询为空');
            exit();
        }
        
        //签名校验
        $sign = $return_data['sign'];
        unset($return_data['sign']);
        unset($return_data['sign_type']);
        if($sign == strtoupper(md5key(asc_sort($return_data),$channel['channel_md5key']))){
            if($return_data_new['code'] == 200){
                 $result = $this->paycallbacksuccess($return_data['mer_order_num'],$return_data['price']);
                 $result = json_decode($result,true);
                 if($result['code']==1){
                     echo 'success';exit;
                 }else{
                     echo $result['data'];exit;
                 }
            }else{
                $result = $this->paycallbackfail($return_data['mer_order_num']);
               
            }
        }else{
            $this->paymentLog(__CLASS__.'充值回调异步：签名不正确');
            echo 'error';exit;
        }
    }
}

