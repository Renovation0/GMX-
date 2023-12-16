<?php
namespace app\api\controller;

use think\Db;
use think\Request;
use think\Exception;
use think\facade\Cache;

class Paymenttwo extends Base
{
    private  $income_key = '1659604480553';//'1645869831193'; //商户ID
    //private  $key = 'P0ppElfQMjVwQOveatzhkUWBAf9Yb9gyiLQcY3QOSqEQcD1FJpy85CXLY7rS7CHhG5J8n5oJ6HqIIuWdF16UNfmLzEzqtMBQDmxowi6gtnoEPewmQ7GIOPnDsYF8il3K';
    private  $key = 'Q37GPUIS7MRK7IFJ';
    
    private  $income_host = 'http://api.letspayfast.com/apipay'; //充值
    private  $income_host_query = 'http://api.letspayfast.com/qpayorder'; //充值查询
    private  $income_host_callback = '/api/paymenttwo/RechangeCallBack'; //充值回调
    
    private  $payment_host = 'http://api.letspayfast.com/apitrans'; //提现
    private  $payment_host_query = 'http://api.letspayfast.com/qtransorder'; //提现查询
    private  $payment_host_callback = '/api/paymenttwo/PaymentCallBack'; //提现回调
    
    private  $merchantLogin = 'HX251';
    
    
    /**
     * 生成签名
     *
     * @param 参数map
     * @param key       商户密钥
     * @return 添加签名后的参数map
     */
    public function Map($data,$type) {
        //$data = $this->request->param();
        //echo'<pre/>';
        //         $data = [
        //             'merchantLogin'=>'username',
        //             'orderCode'=>'code123456',
        //             'amount'=>'100.00',
        //             'name'=>'Jone Connor',
        //             'email'=>'aaa@aaa.com',
        //             'phone'=>'911234567890',
        //             'remark'=>'remark'
        //         ];
        
        ksort($data);
        var_dump($data); echo '<br/>';echo '<br/>';
        //$key = $this->income_key;
        $string = $this->generateSignString($data);
        var_dump($string); echo '<br/>'; echo '<br/>';
        //$string = 'amount=1000&bankcode=all&goods=email:kakaka@google.com/name:tom/phone:91931340330&mchId=1645869831193&notifyUrl=http://hw.cn/api/paymenttwo/RechangeCallBack&orderNo=C20220302512680&product=indiaupi&returnUrl=http://hw.cn/api/paymenttwo/RechangeCallBackTb&key=P0ppElfQMjVwQOveatzhkUWBAf9Yb9gyiLQcY3QOSqEQcD1FJpy85CXLY7rS7CHhG5J8n5oJ6HqIIuWdF16UNfmLzEzqtMBQDmxowi6gtnoEPewmQ7GIOPnDsYF8il3K';
        //$sign = strtoupper(md5($string));
        $sign = md5($string);
        var_dump($sign); echo '<br/>';echo '<br/>';
        //$string .= '&sign='.$sign;
        //$data['key'] = $this->key;
        $data['sign'] = $sign;
        return $data;
    }
    
    /**
     * 将Map中的key按Ascii码进行升序排序，拼接成 key1=val1&key2=val2&key3=val3....&key=密钥 格式
     *
     * @param sourceMap
     * @param key       密钥
     * @return
     */
    public function generateSignString($data) {
        if (!empty($data)) {
            $string = '';
            foreach($data as $k => $v){
                $string .= $k.'='.$v.'&';
            }
        }
        $string .= 'key='.$this->key;
        return $string;
    }
    
    //收银台收款 --用户充值
    /**
     *
     * @param unknown $orderCode
     * @param unknown $amount
     * @param unknown $name
     * @param unknown $email
     * @param unknown $phone
     */
    public function RechargeMoney($orderCode,$amount,$name,$email,$phone){
        
        $url='http://api.letspayfast.com/apipay';
        $mchId=$this->income_key;
        $product="indiaupi";
        $orderNo=$orderCode;
        $amount=$amount;
        $bankcode="all";
        $notifyUrl='http://'.$_SERVER['HTTP_HOST'].$this->income_host_callback;
        $returnUrl='http://'.$_SERVER['HTTP_HOST'].$this->income_host_callback.'Tb';
        $goods = 'email:'.$email.'/name:'.$name.'/phone:'.$phone;//"email:kakaka@google.com/name:tom/phone:91931340330";        
        //$key="wuwCsDjyA2ayG0QTsfUzmLpHuUcdKHYlBxpXFPvaw6fY7AkODD6rHOeXnPdseTCMIjwnP29NM8LIPqheN9G03awpW5FQwWsIPL5vWZxabbRibhKG5jdHsAzP5Vuu1VzU";
        $key=$this->key;
        
        //签名
        $sign_str = '';
        $sign_str  = $sign_str . 'amount=' . $amount;
        $sign_str  = $sign_str . '&bankcode=' . $bankcode;
        $sign_str  = $sign_str . '&goods=' . $goods;
        $sign_str  = $sign_str . '&mchId=' . $mchId;
        $sign_str  = $sign_str . '&notifyUrl=' . $notifyUrl;
        $sign_str  = $sign_str . '&orderNo=' . $orderNo;
        $sign_str  = $sign_str . '&product=' . $product;
        $sign_str  = $sign_str . '&returnUrl=' . $returnUrl;
        $sign_str = $sign_str . '&key=' . $key;
        
        $sign = strtoupper(md5($sign_str));
        
        Db::name('pay_info')->insert(['text'=>'发起充值2：'.$sign_str.',sign为'.$sign,'time'=>date('Y-m-d H:i:s',time())]);
        
        //var_dump($sign_str);exit();
        //格式化
        $data=sprintf("amount=%s&bankcode=%s&goods=%s&mchId=%s&notifyUrl=%s&orderNo=%s&product=%s&returnUrl=%s&sign=%s",
            $amount,
            $bankcode,
            $goods,
            $mchId,
            $notifyUrl,
            $orderNo,
            $product,
            $returnUrl,
            $sign);
        
        
        $post_data = array(
            'amount' => $amount,
            'bankcode' => $bankcode,
            'goods' => $goods,
            'mchId' => $mchId,
            'notifyUrl' => $notifyUrl,
            'orderNo' => $orderNo,
            'product' => $product,
            'returnUrl' => $returnUrl,
            'sign' => $sign
        );
        
        // get
        //$urldata=$url.'?'.$data;
        //$resp=curl_request($urldata);
        //post
        $resp=$this->send_post($url,$post_data);
        //print_r($resp);
        
        return $resp;
        
        //return $this->Map($data, 1);
    }
    
    
    
    function curl_request($url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
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
        
        $data = [
            'merchantLogin'=>$data['merchantLogin'],
            'orderCode'=>$data['orderCode'],
            'merchantCode'=>$data['merchantCode'],
            'status'=>$data['status'],
            'orderAmount'=>$data['orderAmount'],
            'paidAmount'=>$data['paidAmount'],
            //'remark'=>$remark
        ];
        //var_dump($data);exit();
        return $this->Map($data, 1);
    }
    
    //充值回调同步
    public function RechangeCallBackTb(Request $request)
    {
        $data = $this->request->param();
        if(empty($json)){
            $json = '收到请求，但未发现任何数据';
        }else{
            $json = json_encode($json);
        }
        Db::name('pay_info')->insert(['text'=>'充值同步2：'.$json,'time'=>date('Y-m-d H:i:s',time())]);
    }
    
    //充值回调
    public function RechangeCallBack(Request $request)
    {   $return = 'success';
        $data = $this->request->param();
        
        //var_dump($data);exit();
        // if(empty($data)){
        //     $json = '收到请求，但未发现任何数据';
        // }else{
        $json = json_encode($data);
        // }
        Db::name('pay_info')->insert(['text'=>'充值异步2：'.$json,'time'=>date('Y-m-d H:i:s',time())]);
        //$data = json_decode($data,TRUE);
        
        if($data['status'] == 1){
            exit();
        }

        $res = Db::name('member_bm_recharge')->where('order_id',$data['orderNo'])->find();
        if(!$res){
            $this->wlog('充值2异步错误：'.$data['orderNo'].'未找到记录。' . date('Y-m-d H:i:s', time()), 'RechangeCallBack'); 
            return $return;
            echo 'success';
            exit();
            //Db::name('pay_info')->insert(['text'=>'充值异步错误：'.$data['orderNo'].'已完成','time'=>date('Y-m-d H:i:s',time())]);
            //return json(['code'=>2,'data' =>[],'msg'=> 'No information detected']);//未找到记录
        }
        
        if (Cache::get('RCB'.$data['orderNo']) == 2){
            //return json(['code' => 2, 'msg' => '兑换中', 'data'=>[]]);
            $this->wlog('充值2异步错误：'.$data['orderNo'].'重复。' . date('Y-m-d H:i:s', time()), 'RechangeCallBack'); exit();
            Db::name('pay_info')->insert(['text'=>'充值异步2：'.$data['orderNo'].'重复','time'=>date('Y-m-d H:i:s',time())]);
            return $return;
            echo 'success';
            exit();
        }
        Cache::set('RCB'.$data['orderNo'],2,10); 
        
        if($res['status'] == 1 || $res['status'] == 2){
            
            Db::name('pay_info')->insert(['text'=>'充值异步2：'.$data['orderNo'].'已完成','time'=>date('Y-m-d H:i:s',time())]);
            return $return;
            echo 'success';
            exit();
        }
        
        $member_info = Db::name('member_list')->where('id',$res['uid'])->field('id,tel,rechange_limit')->find();

        //预约开始
        try {
            Db::startTrans();
            if($data['status'] == 2){
                Db::name('member_bm_recharge')->where('order_id',$data['orderNo'])->update([
                    'num'=>$data['amount'],
                    'update_time'=>getIndaiTime(time()),
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
                Db::name('member_bm_recharge')->where('order_id',$data['orderNo'])->update([
                    'pass_reason'=>'FAILED',
                    'update_time'=>getIndaiTime(time()),
                    'status'=>2
                ]);
            }
            
            Db::commit();
            return $return;
            echo 'success';
            //return json(['code' => 1,'msg' => getErrorInfo(SUCCESS)]);
        } catch (Exception $exception) {
            Db::rollback();
            return $return;
            echo 'success';
            //return json(['code' => 2,'msg' => getErrorInfo(ADD_FAIL).$exception->getMessage()]);
        }
    }
    
    
    
    //收银台付款 --用户提现
    /**
     *
     * @param unknown $orderCode
     * @param unknown $amount
     * @param unknown $name
     * @param unknown $email
     * @param unknown $phone
     * @param unknown $remark
     */
    public function PaymentMoney($orderCode,$amount,$name,$account,$ifsc,$remark){
        
        $data = [
            'merchantLogin'=>$this->merchantLogin,
            'orderCode'=>$orderCode,
            'amount'=>$amount,
            'name'=>$name,
            'account'=>$account,
            'ifsc'=>$ifsc,
            'remark'=>$remark
        ];
        //var_dump($data);exit();
        return $this->Map($data, 2);
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
    public function PaymentMoneyCallBack($data){
        
        $data = [
            'merchantLogin'=>$data['merchantLogin'],
            'orderCode'=>$data['orderCode'],
            'merchantCode'=>$data['merchantCode'],
            'status'=>$data['status']
        ];
        //var_dump($data);exit();
        return $this->Map($data, 2);
    }
    
    
    //收款回调
    public function PaymentCallBack(Request $request)
    { $return = 'success';
        $data = $this->request->param();
        if(empty($data)){
            $json = '接收到信息，未发现数据';
            Db::name('pay_info')->insert(['text'=>'提现异步2：'.$json,'time'=>date('Y-m-d H:i:s',time())]);
            //echo 'success';
            return $return;
            exit();
        }else{
            $json = json_encode($data);
            //$json = json_decode($data,TRUE);
            Db::name('pay_info')->insert(['text'=>'提现异步2：'.$json,'time'=>date('Y-m-d H:i:s',time())]);
        }
        
        // $result_str = $this->PaymentMoneyCallBack($data);
        
        // $result_sign = substr($result_str,strripos($result_str,"=")+1);
        // if($data['sign'] != $result_sign){
        //     return json(['code'=>2,'data' =>[],'msg'=> 'Signature error']);//签名错误
        //     exit();
        // }

        if($data['status'] == 1){
            //echo 'success';
            return $return;
            exit();
        }
        $withdraw_info = Db::name('member_bm_withdraw')->where('hash',$data['mchTransNo'])->find();
        if($withdraw_info['status'] != 0 && $withdraw_info['status'] != 3){
            Db::name('pay_info')->insert(['text'=>'提现异步2：id:'.$data['mchTransNo'].'订单已完成','time'=>date('Y-m-d H:i:s',time())]);
            //echo 'success';
            return $return;
            //return json(['code'=>2,'data' =>[],'msg'=> 'The record is closed']);//
            exit();
        }
        
        $member_info = Db::name('member_list')->where('id',$withdraw_info['uid'])->field('id,tel,balance')->find();

        //预约开始
        try {
            Db::startTrans();
            if($data['status'] == 2){
                
                Db::name('member_bm_withdraw')->where('hash',$data['mchTransNo'])->update([
                    'update_time'=>getIndaiTime(time()),
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
                
                //echo '11111';
                
            }elseif($data['status'] == 3){
                Db::name('member_bm_withdraw')->where('hash',$data['mchTransNo'])->update([
                    'update_time'=>getIndaiTime(time()),
                    'status'=>2
                ]);
                
                Db::name('member_balance_log')->insert([
                    'u_id' => $user_id,
                    'tel' => $member_info['tel'],
                    'former_money' => $member_info['balance'],
                    'change_money' => $num,
                    'after_money' => $member_info['balance'] + $num,
                    'message' => '提现失败退回'.$num,
                    'message_e' => 'Withdrawal failed and returned '.$num,
                    'type' => 2,
                    'bo_time' => getIndaiTime(time()),
                    'status' => 93
                ]);
                
                Db::name('member_list')->where('id', $user_id)->update([
                    'balance' => Db::raw('balance +'.$num),
                    //'balance_total' => Db::raw('balance_total -'.$num)
                ]);
                //echo '22222';
            }
            
            Db::commit();
            //echo 'success';
            return $return;
            //return json(['code' => 1,'msg' => getErrorInfo(SUCCESS)]);
        } catch (Exception $exception) {
            Db::rollback();
            //echo 'success';
            return $return;
            //return json(['code' => 2,'msg' => getErrorInfo(ADD_FAIL).$exception->getMessage()]);
        }
        
    }
    
    
    
    //日志操作
    public function wlog($content = '', $dir = 'log')
    {
        $dir_path = 'Log/' . $dir . '/';
        $filename = date('Ymd') . '.log';
        $this->sp_dir_createwrfs($dir_path);
        $log_content = date('H:i:s') . ":\n" . $content . "\n\n";
        file_put_contents($dir_path . $filename, $log_content, FILE_APPEND);
    }

    public function sp_dir_createwrfs($path, $mode = 0777)
    {
        if (is_dir($path)) return true;
        $ftp_enable = 0;
        $path = $this->sp_dir_pathwarsa($path);
        $temp = explode('/', $path);
        $cur_dir = '';
        $max = count($temp) - 1;
        for ($i = 0; $i < $max; $i++) {
            $cur_dir .= $temp[$i] . '/';
            if (@is_dir($cur_dir))
                continue;
            @mkdir($cur_dir, 0777, true);
            @chmod($cur_dir, 0777);
        }
        return is_dir($path);
    }

    public function sp_dir_pathwarsa($path)
    {
        $path = str_replace('\\', '/', $path);
        if (substr($path, -1) != '/')
            $path = $path . '/';
        return $path;
    }
    
    
}

