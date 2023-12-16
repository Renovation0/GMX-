<?php
namespace app\api\controller;

use think\Db;
use think\Request;
use think\Exception;

class Payment extends Base
{
    private  $income_key = 'X5eOvG7oCjCVxTjeLSSg';
    private  $payment_key = 'O12NqGSSCoecdiqZYIU5';
    private  $host = 'https://quartet.quartet.hxpayment.xyz';
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

        $key = $type == 1 ? $this->income_key : $this->payment_key;
        $string = $this->generateSignString($data,$key);
        $sign = md5($string);
        //$string .= '&sign='.md5($string);
        $data['key'] = $key;
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
    public function RechargeMoney($orderCode,$amount,$name,$email,$phone,$remark){
        
        $data = [
            'merchantLogin'=>$this->merchantLogin,
            'orderCode'=>$orderCode,
            'amount'=>$amount,
            'name'=>$name,
            'email'=>$email,
            'phone'=>$phone,
            'remark'=>$remark
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
    
    
    
    //充值回调
    public function RechangeCallBack(Request $request)
    {
        $data = $this->request->param();
        //Db::name('pay_info')->insert(['text'=>$data['sign'],'time'=>date('Y-m-d H:i:s',time())]);
        $datas = $data;
        unset($data['sign']);
        //$data = json_decode($json,TRUE);
        $result_arr = $this->RechargeMoneyCallBack($data);

        if($datas['sign'] != $result_arr['sign']){
            //Db::name('pay_info')->insert(['text'=>$datas['sign'].'--'.$result_arr['sign'],'time'=>date('Y-m-d H:i:s',time())]);
            return json(['code'=>2,'data' =>[],'msg'=> 'Signature error']);//签名错误
        }
        
        if($data['status'] == 'PENDING'){
            //Db::name('pay_info')->insert(['text'=>$data['status'],'time'=>date('Y-m-d H:i:s',time())]);
            exit();
        }
        
        $res = Db::name('member_bm_recharge')->where('hash',$data['orderCode'])->find();
        if(!$res){
            //Db::name('pay_info')->insert(['text'=>$data['orderCode'],'time'=>date('Y-m-d H:i:s',time())]);
            return json(['code'=>2,'data' =>[],'msg'=> 'No information detected']);//未找到记录
        }
        
        $member_info = Db::name('member_list')->where('id',$res['uid'])->field('id,tel,rechange_limit')->find();

        
        //预约开始
        try {
            Db::startTrans();
            if($data['status'] == 'SUCCESS'){
                Db::name('member_bm_recharge')->where('hash',$data['orderCode'])->update([
                    'num'=>$data['paidAmount'],
                    'update_time'=>getIndaiTime(time()),
                    'status'=>1
                ]);
                
                $data6 = [
                    'u_id' => $member_info['id'],
                    'tel' => $member_info['tel'],
                    'o_id' => 0,
                    'former_money' => $member_info['rechange_limit'],
                    'change_money' => $data['paidAmount'],
                    'after_money' => $member_info['rechange_limit']+$data['paidAmount'],
                    'type' => 1,
                    'message' => '成功充值'.$data['paidAmount'],
                    'message_e' => 'Successfully recharge '.$data['paidAmount'],
                    'bo_time' => getIndaiTime(time()),
                    'status' => 90,
                ];
                Db::name('member_balance_log')->insert($data6);
                
                Db::name('member_list')->where('id', $member_info['id'])->update([
                    'rechange_limit' => Db::raw('rechange_limit +'.$data['paidAmount']),
                    'rechange_limit_total' => Db::raw('rechange_limit_total +'.$data['paidAmount'])
                ]);
                
            }elseif($data['status'] == 'FAILED'){
                Db::name('member_bm_recharge')->where('hash',$data['orderCode'])->update([
                    'pass_reason'=>'FAILED',
                    'update_time'=>getIndaiTime(time()),
                    'status'=>2
                ]);
            }
            
            Db::commit();
            return json(['code' => 1,'msg' => getErrorInfo(SUCCESS)]);
        } catch (Exception $exception) {
            Db::rollback();
            Db::name('pay_info')->insert(['text'=>$exception->getMessage(),'time'=>date('Y-m-d H:i:s',time())]);
            return json(['code' => 2,'msg' => getErrorInfo(ADD_FAIL).$exception->getMessage()]);
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
    {
        // $json = $this->request->param();
        
        // $data = json_decode($json,TRUE);
        
        // $result_str = $this->PaymentMoneyCallBack($data);
        
        // $result_sign = substr($result_str,strripos($result_str,"=")+1);
        // if($data['sign'] != $result_sign){
        //     return json(['code'=>2,'data' =>[],'msg'=> 'Signature error']);//签名错误
        //     exit();
        // }
        
        // if($data['status'] == 'PENDING'){
        //     exit();
        // }
        // $withdraw_info = Db::name('member_bm_withdraw')->where('hash',$data['orderCode'])->find();
        // if($withdraw_info['status'] != 0){
        //     return json(['code'=>2,'data' =>[],'msg'=> 'The record is closed']);//签名错误
        //     exit();
        // }
        
        
        
        $data = $this->request->param();
        $data_insert = json_encode($data);
        Db::name('pay_info')->insert(['text'=>'提现回调1：'.$data_insert,'time'=>date('Y-m-d H:i:s',time())]);//exit();
        $datas = $data;
        unset($data['sign']);
        //$data = json_decode($json,TRUE);
        //$result_arr = $this->PaymentMoneyCallBack($data);

        // if($datas['sign'] != $result_arr['sign']){
        //     //Db::name('pay_info')->insert(['text'=>$datas['sign'].'--'.$result_arr['sign'],'time'=>date('Y-m-d H:i:s',time())]);
        //     Db::name('pay_info')->insert(['text'=>$data['orderCode'].'的订单验证签名错误','time'=>date('Y-m-d H:i:s',time())]);
        //     return json(['code'=>2,'data' =>[],'msg'=> 'Signature error']);//签名错误
        // }
        
        if($data['status'] == 'PENDING'){
            //Db::name('pay_info')->insert(['text'=>$data['status'],'time'=>date('Y-m-d H:i:s',time())]);
            Db::name('pay_info')->insert(['text'=>$data['orderCode'].'的订单为'.$data['status'],'time'=>date('Y-m-d H:i:s',time())]);
            exit();
        }

        $res = Db::name('member_bm_withdraw')->where('hash',$data['orderCode'])->find();
        //var_dump(Db::name('member_bm_withdraw')->getlastsql());exit();
        $res2 = Db::name('member_bm_withdraw')->where('order_id',$data['merchantCode'])->find();
        if(!$res && !$res2){
            Db::name('pay_info')->insert(['text'=>'未找到'.$data['orderCode'].'的记录','time'=>date('Y-m-d H:i:s',time())]);
            //Db::name('pay_info')->insert(['text'=>$data['orderCode'],'time'=>date('Y-m-d H:i:s',time())]);
            return json(['code'=>2,'data' =>[],'msg'=> 'No information detected']);//未找到记录
        }
        
        $member_info = Db::name('member_list')->where('id',$res['uid'])->field('id,tel,balance')->find();
        
         if($res['status'] != 3){
            Db::name('pay_info')->insert(['text'=>$data['orderCode'].'的记录已完结','time'=>date('Y-m-d H:i:s',time())]);
            //Db::name('pay_info')->insert(['text'=>$data['orderCode'],'time'=>date('Y-m-d H:i:s',time())]);
            return json(['code'=>2,'data' =>[],'msg'=> 'Record closed']);//未找到记录
        }
        
        //预约开始                                    'order_id',$data['merchantCode']
        try {
            Db::startTrans();
            if($data['status'] == 'SUCCESS'){
                Db::name('member_bm_withdraw')->where('hash',$data['orderCode'])->update([
                    'update_time'=>getIndaiTime(time()),
                    'status'=>1
                ]);
                
                Db::name('member_list')->where('id', $res['uid'])->update([
                    'balance_total' => Db::raw('balance_total +'.$res['num'])
                ]);
                
                Db::name('member_balance_log')->insert([
                    'u_id' => $res['uid'],
                    'tel' => $member_info['tel'],
                    'former_money' => 0,
                    'change_money' => $res['num'],
                    'after_money' => 0,
                    'message' => '提现成功'.$res['num'],
                    'message_e' => 'Withdrawal Successful'.$res['num'],
                    'type' => 2,
                    'bo_time' => getIndaiTime(time()),
                    'status' => 92
                ]);
                
            }else{//if($data['status'] == 'FAILED')  'hash',$data['orderCode']
                Db::name('member_bm_withdraw')->where('hash',$data['orderCode'])->update([
                    'update_time'=>getIndaiTime(time()),
                    'status'=>2
                ]);
                
                Db::name('member_balance_log')->insert([
                    'u_id' => $res['uid'],
                    'tel' => $member_info['tel'],
                    'former_money' => $member_info['balance'],
                    'change_money' => $res['num'],
                    'after_money' => $member_info['balance'] + $res['num'],
                    'message' => '提现失败退回'.$res['num'],
                    'message_e' => 'Withdrawal failed and returned '.$res['num'],
                    'type' => 2,
                    'bo_time' => getIndaiTime(time()),
                    'status' => 93
                ]);
                
                Db::name('member_list')->where('id', $res['uid'])->update([
                    'balance' => Db::raw('balance +'.$res['num']),
                    //'balance_total' => Db::raw('balance_total -'.$num)
                ]);
            }
            
            Db::commit();
            return json(['code' => 1,'msg' => getErrorInfo(SUCCESS)]);
        } catch (Exception $exception) {
            Db::name('pay_info')->insert(['text'=>$exception->getMessage(),'time'=>date('Y-m-d H:i:s',time())]);
            Db::rollback();
            return json(['code' => 2,'msg' => getErrorInfo(ADD_FAIL).$exception->getMessage()]);
        }
        
    }
    
}

