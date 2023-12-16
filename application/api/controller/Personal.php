<?php
namespace app\api\controller;
use think\Db;
use app\api\model\MMember;
use app\api\model\MConfig;
use think\Request;
use app\api\model\BanlanceLog;
use app\api\validate\TelVal;
use think\facade\Cache;
use think\Exception;
use app\admin\model\MMemberBalanceLog;
use app\api\model\MMemberMutualaid;

header('Content-Type: text/html;charset=utf-8');
header('Access-Control-Allow-Origin:*'); // *代表允许任何网址请求
header('Access-Control-Allow-Methods:POST,GET,OPTIONS,DELETE'); // 允许请求的类型
header('Access-Control-Allow-Credentials: true'); // 设置是否允许发送 cookies
header('Access-Control-Allow-Headers: Content-Type,Content-Length,Accept-Encoding,X-Requested-with, Origin');

class Personal extends Common
{
    public function querybankinfo(){
        $bankinfo = Db::name('bankinfo')->order('id desc')->select();
        for($i=0;$i<count($bankinfo);$i++){
            $bankinfo[$i]['name'] = $bankinfo[$i]['bankname'];
        }
        return json(['code' => 1, 'msg' => 'success', 'data'=>$bankinfo]);
    }
    /**
     * 我的
     */
    public function myIndex()
    {   
        $MMember = new MMember();
        $MConfig = new MConfig();
        $u_id = $this->userinfo['user_id'];
        if(empty($u_id)){
            return json(['code'=>2,'data' =>[],'msg'=> getErrorInfo(USER_NBUND)]);
        }

        $info = $MMember->getInfo(['id'=>$u_id]);
        $purchase_info = $MMember->getPurchaseInfo(['uid'=>$u_id]);

        date_default_timezone_set('Asia/Calcutta');
        foreach($purchase_info as $purchase=> $v){
            
           if($v['is_overtime'] == 0 && (time()>($v['sta_time'] + ($v['days'] * 86400)))){
            Db::name('zm_member_mutualaid')->where('id', $v['id'])->update(['is_overtime' => 1]);
            $new_balance = $info['balance'] + $v['get_price'];
            $MMember->updateBalance(['id'=>$u_id],$new_balance);
           }
            
        }

        $info = $MMember->getInfo(['id'=>$u_id]);
        $config_val = $MConfig->readConfig(['DEFAULT_LEVEL_IMG','IS_OPEN_CARD','DEFAULT_IMG'],2);
        $MMemberBalanceLog = new BanlanceLog();
        $data['user']['id'] = $u_id;
        $data['user']['guid'] = $info['guid'];
        $data['user']['username'] = $info['user'];//$this->userinfo['user'];
        $data['user']['mobile'] =  substr_replace($info['tel'],'****',3,5);;//$this->userinfo['tel'];
        $data['user']['status'] =  $info['status'];//$this->userinfo['status'];
        $data['user']['u_img'] = $info['u_img']==''?'http://' . $_SERVER['HTTP_HOST'] . $config_val[2]:'http://' . $_SERVER['HTTP_HOST'] . $info['u_img'];//$this->userinfo['u_img'];
        //$data['user']['all_assets'] = Db::name('member_mutualaid')->where('uid', $u_id)->where('compose_status in (0,2) and status in (1,2,3) and is_exist = 1')->sum('new_price');
        //$data['user']['all_reward'] = round($info['census_profit_deposit'] + $info['census_profit_recom'] + $info['census_profit_team'], 2);
        $zzReward = $MMemberBalanceLog->where('u_id', $u_id)->where('type = 2 and status=502')->where('message','like','%收益,--%')->sum('change_money');
        $data['user']['all_reward'] = round($info['profit_deposit'] + $info['profit_recom'] + $info['profit_team'] + $zzReward, 2);

        $me_mu_list = Db::name('member_mutualaid')->where('uid', $u_id)->where('status = 1 and is_exist = 1')->select();
        $total_reward = 0;
        foreach($me_mu_list as $k=> $v){
            $rate = 0;
            //$days = $v['days']-$v['up_time'];
            $days = $v['up_time'] == 0 ? 0 : $v['up_time'];
            $rate = round($v['get_price']*$v['rate']/100*$days,2);
            $total_reward += $rate;
        }
        //获取推广领取的总金额
        $data['user']['zzlingqu'] = Db::name('mutualaid_examine')->where('status = 2 and uid = '.$u_id)->sum('money');
        $data['user']['zzReward'] = $zzReward;//团队收益
        $data['user']['balance'] = $info['balance'];
        $data['user']['rechange_limit'] = $info['rechange_limit'];
        $data['user']['balance_total'] = $info['balance_total'];
        $data['user']['reward_census'] = round($total_reward,2);//$info['reward_census'];
        $data['user']['levels'] = $info['level'];
        //'http://' . $_SERVER['HTTP_HOST'] . $config_val[0]
        $data['user']['level'] = $info['level'] == 0 ? 'http://' . $_SERVER['HTTP_HOST'] . $config_val[0] : 'http://' . $_SERVER['HTTP_HOST'] . Db::name('member_level')->where('id', $info['level'])->value('level_logo');
//         $data['user']['is_open_card'] = $config_val[1];

        return json(['code' => 1, 'msg' => 'success', 'data'=>$data]);
    }
    
 
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////                         收款方式                                                     //////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //收款方式列表
    public function paymentList(Request $request){
        $user_id = $this->userinfo['user_id'];
        
        $lists = Db::name('paymant_binding')->where('u_id=' . $user_id)->field('id,name,tel,account_num,bank_num,ifsc')->select();
 
        return json(['code' => 1,'msg' => 'success','data'=>$lists]);
    }
    
    //收款方式列表
    public function paymentDetail(Request $request){
        $id = $request->post('id');//名称
        $user_id = $this->userinfo['user_id'];
        
        $detail = Db::name('paymant_binding')->where([
            	'id'	=>	$id
            ])->field('id,name,tel,account_num,bank_num,ifsc,bank_code')->find();
 
        return json(['code' => 1,'msg' => 'success','data'=>$detail]);
    }
    
    
    //收款方式提交
    public function paymentMethod(Request $request){
        $user_id = $this->userinfo['user_id'];
        $tel = $this->userinfo['tel'];
        if(empty($user_id) || empty($tel)){
            return json(['code'=>2,'data' =>[],'msg'=> getErrorInfo(USER_NBUND)]);
        }
//         $status = intval($request->post('type', 0));//1支付宝  2微信 3银行卡
//         if (!in_array($status, [1, 2, 3])) {
//             return json(['code' => 2, 'data' => [], 'msg' => '出错了,请正确提交收款方式']);
//         }
        //$receive_qrcode = getValue(trim($request->post('receive_qrcode')));//二维码
        $id = getValue(trim($request->post('id')));//名称
        $name = getValue(trim($request->post('name')));//名称
        $tel = getValue(trim($request->post('tel')));//电话
        $account_num = getValue(trim($request->post('account_num')));//卡号 账户
        $bank_num = getValue(trim($request->post('bank_num')));//银行卡号
        $ifsc = (trim($request->post('ifsc')));//ifsc
        $bank_code = getValue(trim($request->post('sexchoicecode')));//银行编码
        //$code = trim($request->post('code'));//验证码
        //$adress = trim($request->post('adress'));//USDT地址
        //$password = trim($request->post('password'));//
        $langer = $request->param('langer','EN');

        $MMember = new MMember();
        $member_info = $MMember->getInfo(['id'=>$user_id]);
        $langer = $member_info['langer'];
        //消除反斜杠
        //$receive_qrcode = $this->updatexg($receive_qrcode);        
        
        //$MConfig = new MConfig();
        //$config_val = $MConfig->readConfig(['repeatTimeZFB','repeatTimeYhName'],2);
        
        if(!$name || !$tel || !$account_num || !$bank_num || !$ifsc){
            if($langer == 'EN'){
                return json(['code'=>2,'data' =>[],'msg'=> getErrorInfo(MISS_FAIL)]);
            }else{
                return json(['code' => 2, 'msg' => getErrorInfo(MISS_FAIL_IN)]);//缺少必要参数
            }
        }
        
        if (Cache::get('paymentMethod'.$user_id) == 2){
            if($langer == 'EN'){
                return json(['code' => 2, 'msg' => getErrorInfo(NOT_OPERATE), 'data'=>[]]);
            }else{
                return json(['code' => 2, 'msg' => getErrorInfo(NOT_OPERATE_IN)]);//请勿频繁操作
            }
        }
        Cache::set('rechargePost'.$user_id,2,10);
        
//         if(empty($status) || $status == 0 || $status > 4){
//             Cache::set('rechargePost'.$user_id,0,1);
//             return json(['code' => 2,'msg' => '请选择上传类型']);
//         }
        
/*         if(Cache::get('tel_'.$tel) != $code && $code != 123456){
            Cache::set('rechargePost'.$user_id,0,1);
            return json(['code' => 2, 'msg' => '验证码不正确']);
        } */
        
       
        $pay_pass = $MMember->getValue('id = '.$user_id, 'pay_pass');
        
//         if($pay_pass != md5($password.'pay_passwd')){
//             return json(['code' => 2,'msg' => '交易密码错误']);
//         }
        
        $receive_arr['name'] = $name;
        $receive_arr['tel'] = $tel;
        $receive_arr['account_num'] =  $account_num;
        $receive_arr['bank_num'] = $bank_num;
        $receive_arr['ifsc'] = $ifsc;
        $receive_arr['bank_code'] = $bank_code;
        //$receive_arr['receive_qrcode'] = $receive_qrcode;
        $receive_arr['status'] = 3;
        //$list = Db::name('paymant_binding')->where('status =' . $status . ' and u_id=' . $user_id)->find();
        if($id){
            $list = Db::name('paymant_binding')->where('id =' . $id)->find();
        }
        // var_dump($list);die();
        $oper = '';

        try {
            Db::startTrans();
            if (!empty($list)) {
                // if ($num > $this->auth->balance) $this->error('U不足');
                $oper = '编辑';
                $receive_arr['modify_time'] = time();
                //throw new Exception('编辑信息请联系客服');
                Db::name('paymant_binding')->where('id', $list['id'])->update($receive_arr);
            } else {
                $oper = '添加';
                $receive_arr['u_id'] = $user_id;
                $receive_arr['create_time'] = time();
                //检查是否已经有此类型的收款方式，防止重复添加同种收款方式
//                 $res_check = Db::name('paymant_binding')->where(['u_id' => $user_id, 'status' => $status])->value('status');
//                 if ($res_check) {
//                     Cache::set('rechargePost'.$user_id,0);
//                     throw new Exception('已经提交成功，请不要重复提交');
//                 }
                Db::name('paymant_binding')->insert($receive_arr);
            }

            Db::commit();
            Cache::set('rechargePost'.$user_id,0,1);
            return json(['code' => 1, 'msg' => getErrorInfo_new("SUCCESS",$langer)]);//成功
        } catch (Exception $exception) {
            Db::rollback();
            Cache::set('rechargePost'.$user_id,0,1);
            return json(['code' => 2,'msg' => getErrorInfo_new("ADD_FAIL",$langer).$exception->getMessage()]);
            // if($langer == 'EN'){
            //     return json(['code' => 2,'msg' => getErrorInfo(ADD_FAIL).$exception->getMessage()]);
            // }else{
            //     return json(['code' => 2, 'msg' => getErrorInfo(ADD_FAIL_IN)]);
            // }
        }

    }
    
    
    //收款方式删除
    public function paymentDel(Request $request){
        $user_id = $this->userinfo['user_id'];
        $id = $request->param('id');
        
        $MMember = new MMember();
        $member_info = $MMember->getInfo(['id'=>$user_id]);
        $langer = $member_info['langer'];
        
        $result = Db::name('paymant_binding')->where('id = '.$id.' AND u_id = '.$user_id)->find();
        if(empty($result)){
            if($langer == 'EN'){
                return json(['code' => 2, 'msg' => getErrorInfo(PARAMETER_ERROR)]);//成功
            }else{
                return json(['code' => 2, 'msg' => getErrorInfo(PARAMETER_ERROR_IN)]);
            }
        }
        
        $res = Db::name('paymant_binding')->where('id = '.$id.' AND u_id = '.$user_id)->delete();
        if($res){
            if($langer == 'EN'){
                return json(['code' => 1, 'msg' => getErrorInfo(SUCCESS)]);//成功
            }else{
                return json(['code' => 1, 'msg' => getErrorInfo(SUCCESS_IN)]);
            }
        }else{
            if($langer == 'EN'){
                return json(['code' => 2,'msg' => getErrorInfo(DELETE_FAIL)]);
            }else{
                return json(['code' => 2, 'msg' => getErrorInfo(DELETE_FAIL_IN)]);
            }
        }
    }
    
    //充值渲染
    public function rechangeRender(Request $request)
    {   
        $MMember = new MMember();
        $user_id = $this->userinfo['user_id'];
        $member_info = $MMember->getInfo(['id'=>$user_id]);
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig(['PAY_ONE','PAY_TWO','PAY_ONE_NAME','PAY_TWO_NAME','PAY_THREE','PAY_THREE_NAME','PAY_FOUR','PAY_FOUR_NAME','RECHAGE_MONEY'],2);
        $channelList = Db::name('channel')->field('id,name,recharge_min,recharge_max')->where('recharge_status=1')->order('recharge_order desc')->select();
        
        return json(['code' => 1,'msg' => 'success','data'=>$channelList, 'rechange'=>$member_info['rechange_limit'],'limit'=>$config_val['8']]);
    }
    
    
    //充值订单编号
    private function makeRandr($num = 6)
    {
        mt_srand((double)microtime() * 1000000);//用 seed 来给随机数发生器播种。
        $strand = str_pad(mt_rand(1, 999999), $num, "0", STR_PAD_LEFT);
        if (Db::name('member_bm_recharge')->where('order_id', 'C' . date('Ymd') . $strand)->count() == 0) {
            return 'C' . date('Ymd') . $strand;
        }
        $this->makeRandr();
    }
    //提现订单编号
    private function makeRandt($num = 6)
    {
        mt_srand((double)microtime() * 1000000);//用 seed 来给随机数发生器播种。
        $strand = str_pad(mt_rand(1, 999999), $num, "0", STR_PAD_LEFT);
        if (Db::name('member_bm_withdraw')->where('order_id', 'T' . date('Ymd') . $strand)->count() == 0) {
            return 'T' . date('Ymd') . $strand;
        }
        $this->makeRandt();
    }


    //充值
    public function rechange(Request $request)
    {   
        $MMember = new MMember();
        $user_id = $this->userinfo['user_id'];
        $member_info = $MMember->getInfo(['id'=>$user_id]);
        $langer = $member_info['langer'];
        
        $num = abs(getValue($request->post('num',0)));
        if(empty($num)){
            return json(['code'=>2,'data' =>[],'msg'=> getErrorInfo_new("MISS_FAIL",$langer)]);
        }
        
        $pay_id = getValue($request->post('id',0));
         $channel = Db::name('channel')->where(['id'=>$pay_id,'recharge_status'=>1])->find();
        //$channel = Db::name('channel')->where(['id'=>$pay_id])->find();
        if(!$channel){
            if($langer == 'EN'){
                return json(['code'=>2,'data' =>[],'msg'=> 'Recharging channel is closed']);
            }else{
                return json(['code'=>2, 'msg' => 'चैनल फिर चार्ज कर रहा है बन्द है']);
            }
        }
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig(['RECHAGE_MONEY','PAY_TWO','PAY_ONE_NAME','PAY_TWO_NAME'],2);
        
        if($num < $config_val[0]){
            if($langer == 'EN'){
                return json(['code'=>2,'data' =>[],'msg'=> 'Minimum recharge amount '.$config_val[0]]);
            }else{
                return json(['code'=>2, 'msg' => 'पुनरार्ज मात्रा इससे कम नहीं हो सकता '.$config_val[0]]);//
            }
        }
        
        if($channel['recharge_min']!=0&&$num<$channel['recharge_min']){
            if($langer == 'EN'){
                return json(['code'=>2,'data' =>[],'msg'=> 'Minimum recharge amount '.$channel['recharge_min']]);
            }else{
                return json(['code'=>2, 'msg' => 'पुनरार्ज मात्रा इससे कम नहीं हो सकता '.$channel['recharge_min']]);//
            }
        }
        $orderCode = $this->makeRandr();//订单编号
        
        $res = Db::name('member_bm_recharge')->insert([
                    'order_id'=>$orderCode,
                    'uid'=>$user_id,
                    'tel'=>$member_info['tel'],
                    'hash'=>$orderCode,
                    'num'=>$num,
                    'create_time'=>getIndaiTime(time()),
                    'status'=>0,
                    'type'=>$pay_id
                ]);
        $c=controller($channel['bingfile']);
        $url = $c->recharge($orderCode,$num,$channel);
        if($url){
            return json(['code'=>1,'data' =>['paymentUrl'=>$url]]);   
        }else{
            return json(['code'=>2,'data' =>'error','msg'=>'error']);
        }
        
        
        
        
        
        $data = array(
            'paymenturl' => $url, 
        );
        
        return json(['code'=>1,'data' =>$datas]);
        if($pay_id == 1){
            
            $Paymentfive = new Paymentfive();   
            $result_str = $Paymentfive->RechargeMoney($orderCode,$num);
            
            Db::name('pay_info')->insert(['text'=>'发起充值1返回：'.json_encode($result_str),'time'=>date('Y-m-d H:i:s',time())]);
            
            //$data = json_decode($result_str,true);
            
            $url = 'https://api.victory-pay.com/payweb/recharge';
            $headers = [
                'Content-Type:application/json'
            ];
            
            $curl = curl_init();
            $param[CURLOPT_URL] = $url;
            $param[CURLOPT_HTTPHEADER] = $headers;
            $param[CURLOPT_RETURNTRANSFER] = true;
            $param[CURLOPT_FOLLOWLOCATION] = true;
            $param[CURLOPT_POST] = true;
            $param[CURLOPT_POSTFIELDS] = json_encode($result_str);
            $param[CURLOPT_SSL_VERIFYPEER] = false;
            $param[CURLOPT_SSL_VERIFYHOST] = false;
            curl_setopt_array($curl,$param); //传参数
            $data = curl_exec($curl);       //执行命令
            curl_close($curl);
            
            //var_dump($data); echo '<br/>';
            $data = json_decode($data,true);
            //var_dump($data); exit();
            
            if($data['code'] == 200){
                Db::name('member_bm_recharge')->insert([
                    'order_id'=>$orderCode,
                    'uid'=>$user_id,
                    'user'=>$member_info['user'],
                    'tel'=>$member_info['tel'],
                    'hash'=>$orderCode,
                    'num'=>$num,
                    'create_time'=>getIndaiTime(time()),
                    'status'=>0,
                    'type'=>1
                ]);
                
                
                $datas = [
                    'paymentUrl'=>$data['data']['pay_url']
                ];
                
                return json(['code'=>1,'data' =>$datas]);
            }else{
                return json(['code'=>2,'data' =>$data,'msg'=>'error']);
            }
            
            /*
            
            $Payment = new Payment();
            $result_str = $Payment->RechargeMoney($orderCode,$num,$member_info['user'],'email@.com',$member_info['tel'],'remark');
            //var_dump($result_str);exit();
    
            // $result_url = 'https://quartet.quartet.hxpayment.xyz/payment/collection/'.$result_str;
            
            // var_dump($result_url);exit();    
            // $arrContextOptions=array(
            //     "ssl"=>array(
            //         "verify_peer"=>false,
            //         "verify_peer_name"=>false
            //     )
            // );
            // var_dump($result_url);exit();
            
            // $result = file_get_contents($result_url);//, false, stream_context_create($arrContextOptions)
            // //$result = file_get_contents($result_url);
            // var_dump($result);exit();
            /*
            amount=800&email=email@.com&merchantLogin=HX251&name=13800138000&orderCode=C20220217360467&phone=13800138000&remark=remark&key=X5eOvG7oCjCVxTjeLSSg&sign=e53efa9c839e7759da2a35263d5aa033*/
            /*$url = "https://quartet.quartet.hxpayment.xyz/payment/collection";
            
            // $data = [
            //     'amount' => '800',
            //     'email' => 'email@.com',
            //     'merchantLogin' => 'HX251',
            //     'name' => '13800138000',
            //     'orderCode' => 'C20220217360467',
            //     'phone' => '13800138000',
            //     'remark' => 'remark',
            //     'key' => 'X5eOvG7oCjCVxTjeLSSg',
            //     'sign' => 'e53efa9c839e7759da2a35263d5aa033'
            // ];
        //;charset=UTF-8
            $headers = [
                'Content-Type:application/json'
            ];
            
            $curl = curl_init();
            $param[CURLOPT_URL] = $url;
            $param[CURLOPT_HTTPHEADER] = $headers;
            $param[CURLOPT_RETURNTRANSFER] = true;
            $param[CURLOPT_FOLLOWLOCATION] = true;
            $param[CURLOPT_POST] = true;
            $param[CURLOPT_POSTFIELDS] = json_encode($result_str);
            $param[CURLOPT_SSL_VERIFYPEER] = false;
            $param[CURLOPT_SSL_VERIFYHOST] = false;
            curl_setopt_array($curl,$param); //传参数
            $data = curl_exec($curl);       //执行命令
            curl_close($curl);
    
            //var_dump($data); echo '<br/>';
            $data = json_decode($data,true);
            //var_dump($data); echo '<br/>';
    
            if(!empty($data['platformOrderCode'])){
                Db::name('member_bm_recharge')->insert([
                    'order_id'=>$orderCode,
                    'uid'=>$user_id,
                    'user'=>$member_info['user'],
                    'tel'=>$member_info['tel'],
                    'hash'=>$data['platformOrderCode'],
                    'num'=>$num,
                    'create_time'=>getIndaiTime(time()),
                    'status'=>0,
                    'type'=>1
                ]);
    
                return json(['code'=>1,'data' =>$data]);
            }else{
                return json(['code'=>2,'data' =>$data,'msg'=>'error']);
            }
            */
        }elseif($pay_id == 2){
           
            Db::name('member_bm_recharge')->insert([
                    'order_id'=>$orderCode,
                'uid'=>$user_id,
                // 'user'=>$member_info['user'],
                'tel'=>$member_info['tel'],
                'hash'=>$orderCode,
                'num'=>$num,
                'create_time'=>getIndaiTime(time()),
                'status'=>0,
                'type'=>2
            ]);
            $datas = [
                'paymentUrl'=>'baidu.com'
            ];
            
            return json(['code'=>1,'data' =>$datas]);
            $PaymentTwo = new Paymentserven();                          //$member_info['user']                                   //$member_info['tel']
            $result_str = $PaymentTwo->RechargeMoney($orderCode,$num,$member_info['user'],$member_info['tel'].'@gmail.com',$member_info['tel']);
            $data = json_decode($result_str,true);
            //var_dump($data);exit();
            if($data['retCode'] == 'SUCCESS'){
                Db::name('member_bm_recharge')->insert([
                    'order_id'=>$orderCode,
                    'uid'=>$user_id,
                    'user'=>$member_info['user'],
                    'tel'=>$member_info['tel'],
                    'hash'=>$data['platorder'],
                    'num'=>$num,
                    'create_time'=>getIndaiTime(time()),
                    'status'=>0,
                    'type'=>2
                ]);
                $datas = [
                    'paymentUrl'=>$data['payUrl']
                ];
                
                return json(['code'=>1,'data' =>$datas]);
                
            }else{
                return json(['code'=>2,'data' =>$data,'msg'=>'error']);
            }
            
        }elseif($pay_id == 3){
            
            /*$PaymentTwo = new Paymentthree();    
            //$member_info['user']                                   //$member_info['tel']
            $result_str = $PaymentTwo->RechargeMoney($orderCode,$num);
            
            Db::name('pay_info')->insert(['text'=>'发起充值3返回：'.$result_str,'time'=>date('Y-m-d H:i:s',time())]);

            $data = json_decode($result_str,true);
            
            //$json = json_encode($data);
            
            //var_dump($data);
            if($data['returncode'] == 200){
                Db::name('member_bm_recharge')->insert([
                    'order_id'=>$orderCode,
                    'uid'=>$user_id,
                    'user'=>$member_info['user'],
                    'tel'=>$member_info['tel'],
                    'hash'=>$orderCode,
                    'num'=>$num,
                    'create_time'=>getIndaiTime(time()),
                    'status'=>0,
                    'type'=>3
                ]);
                $datas = [
                    'paymentUrl'=>$data['payurl']
                ];
                
                return json(['code'=>1,'data' =>$datas]);
            }else{
                return json(['code'=>2,'data' =>$data,'msg'=>'error']);
            }*/
            
            
            $Paymentfour = new Paymentfour();
            $result_str = $Paymentfour->RechargeMoney($orderCode,$num);

            Db::name('pay_info')->insert(['text'=>'发起充值3返回：'.json_encode($result_str),'time'=>date('Y-m-d H:i:s',time())]);
            
            //$data = json_decode($result_str,true);
            
            $url = 'https://api.victory-pay.com/payweb/recharge';
            $headers = [
                'Content-Type:application/json'
            ];
            
            $curl = curl_init();
            $param[CURLOPT_URL] = $url;
            $param[CURLOPT_HTTPHEADER] = $headers;
            $param[CURLOPT_RETURNTRANSFER] = true;
            $param[CURLOPT_FOLLOWLOCATION] = true;
            $param[CURLOPT_POST] = true;
            $param[CURLOPT_POSTFIELDS] = json_encode($result_str);
            $param[CURLOPT_SSL_VERIFYPEER] = false;
            $param[CURLOPT_SSL_VERIFYHOST] = false;
            curl_setopt_array($curl,$param); //传参数
            $data = curl_exec($curl);       //执行命令
            curl_close($curl);
            
            //var_dump($data); echo '<br/>';
            $data = json_decode($data,true);
            //var_dump($data); exit();
            
            if($data['code'] == 200){
                Db::name('member_bm_recharge')->insert([
                    'order_id'=>$orderCode,
                    'uid'=>$user_id,
                    'user'=>$member_info['user'],
                    'tel'=>$member_info['tel'],
                    'hash'=>$orderCode,
                    'num'=>$num,
                    'create_time'=>getIndaiTime(time()),
                    'status'=>0,
                    'type'=>3
                ]);
                
                
                $datas = [
                    'paymentUrl'=>$data['data']['pay_url']
                ];
                
                return json(['code'=>1,'data' =>$datas]);
            }else{
                return json(['code'=>2,'data' =>$data,'msg'=>'error']);
            }
            
        }elseif($pay_id == 4){
            
            $Paymentsix = new Paymentsix();
            $result_str = $Paymentsix->RechargeMoney($orderCode,$num);

            Db::name('pay_info')->insert(['text'=>'发起充值4返回：'.json_encode($result_str),'time'=>date('Y-m-d H:i:s',time())]);

            $ch = curl_init();    
            curl_setopt($ch,CURLOPT_URL,"https://payment.weglobalpayment.com/pay/web"); //支付请求地址
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($result_str));  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
            $response=curl_exec($ch);
            //$res=simplexml_load_string($response);
            curl_close($ch);
            //var_dump($response);// exit();
            $data = json_decode($response,true);
            //var_dump($data); exit();

            
            if($data['respCode'] == 'SUCCESS'){
                Db::name('member_bm_recharge')->insert([
                    'order_id'=>$orderCode,
                    'uid'=>$user_id,
                    'user'=>$member_info['user'],
                    'tel'=>$member_info['tel'],
                    'hash'=>$data['orderNo'],
                    'num'=>$num,
                    'create_time'=>getIndaiTime(time()),
                    'status'=>0,
                    'type'=>4
                ]);
                
                
                $datas = [
                    'paymentUrl'=>$data['payInfo']
                ];
                
                return json(['code'=>1,'data' =>$datas]);
            }else{
                return json(['code'=>2,'data' =>$data,'msg'=>'error']);
            }
            
        }else{
            if($langer == 'EN'){
                return json(['code'=>2,'data' =>[],'msg'=> '请选择冲至通道']);
            }elseif($langer == 'EN'){
                return json(['code'=>2,'data' =>[],'msg'=> 'Please select recharge channel']);
            }else{
                return json(['code'=>2, 'msg' => 'कृपया फिर चैनल चुनें']);
            }
        }
        
        
    }

        
    //提现渲染
    public function withdrawalRender(Request $request)
    {   
        $MMember = new MMember();
        $user_id = $this->userinfo['user_id'];
        $member_info = $MMember->getInfo(['id'=>$user_id]);
        
        $lists = Db::name('paymant_binding')->where('u_id=' . $user_id)->field('id,name,tel,account_num,bank_num,ifsc')->select();

        return json(['code' => 1,'msg' => 'success', 'bank_list'=>$lists, 'balance'=>$member_info['balance']]);
    }
    
    //提现
    public function Withdrawal(Request $request)
    {   
        //exit();
        $MMember = new MMember();
        $user_id = $this->userinfo['user_id'];
        $member_info = $MMember->getInfo(['id'=>$user_id]);
        
        $id = intval($request->post('id',0));
        $num = abs(getValue($request->post('num',0)));
        $langer = $member_info['langer'];//$request->param('langer','EN');
        
        if(empty($num) || empty($id)){
            if($langer == 'ZH'){
                return json(['code'=>2,'data' =>[],'msg'=> '请填写银行卡号']);
            }elseif($langer == 'EN'){
                return json(['code'=>2,'data' =>[],'msg'=> 'Please add your bank card']);
            }else{
                return json(['code'=>2, 'msg' => 'कृपया अपना बैंक कार्ड जोड़ें']);//
            }
        }
        
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig(['Withdrawal_min'],2);
        
        if($num < $config_val[0]){
            if($langer == 'ZH'){
                return json(['code'=>2,'data' =>[],'msg'=> '最低提现金额 '.$config_val[0]]);
            }elseif($langer == 'EN'){
                return json(['code'=>2,'data' =>[],'msg'=> 'Minimum withdrawal amount '.$config_val[0]]);
            }else{
                return json(['code'=>2, 'msg' => 'न्यूनतम घटाने के मात्रा'.$config_val[0]]);//
            }
        }
        
        $bank_info = Db::name('paymant_binding')->where('id',$id)->find();
        if(empty($bank_info) || $bank_info['u_id'] != $user_id){
            return json(['code'=>2,'data' =>[],'msg'=> getErrorInfo_new("MISS_FAIL",$langer)]);
        }

        if($member_info['balance'] < $num){
            return json(['code' => 2,'msg' => getErrorInfo_new("USER_BLANCE_NO",$langer)]);
        }
        
        $version =$member_info['version'];
        
        $orderCode = $this->makeRandt();//订单编号
            try {
                Db::startTrans();
                
                $falg = Db::execute('UPDATE zm_member_list SET balance=balance-'.$num.',version=version+1 WHERE version='.$version.' and id='.$user_id);
                if($falg){
                     Db::name('member_bm_withdraw')->insert([
                        'order_id'=>$orderCode,
                        'uid'=>$user_id,
                        // 'user'=>$member_info['user'],
                        'tel'=>$member_info['tel'],
                        'hash'=>'',//$data['platformOrderCode']
                        'num'=>$num,
                        'create_time'=>getIndaiTime(time()),
                        'status'=>0,
                        'address'=>$bank_info['name'].'--'.$bank_info['bank_num'],
                        'bank_id'=>$id
                    ]);
                    
                    // Db::name('member_list')->where('id', $user_id)->update([
                    //     'balance' => Db::raw('balance -' .$num),
                    //     //'balance_total' => Db::raw('balance_total +' .$num)
                    // ]);
                    
                    $data = [
                        'u_id' => $member_info['id'],
                        'tel' => $member_info['tel'],
                        'former_money' => $member_info['balance'],
                        'change_money' => -$num,
                        'after_money' => $member_info['balance'] - $num,
                        'message' => '提现'.$num.'至 '.$bank_info['name'].'--'.$bank_info['bank_num'],
                        'message_e' => 'Withdrawal and deduction'.$num,
                        'message_type' => $bank_info['name'].'--'.$bank_info['bank_num'],
                        'type' => 2,
                        'bo_time' => getIndaiTime(time()),
                        'status' => 91
                    ];
                    Db::name('member_balance_log')->insert($data);
                }
            
                Db::commit();
                if($langer == 'ZH'){
                    return json(['code' => 1,'msg' => 'Success, Please wait for approval']);//成功，请等待批准
                }elseif($langer == 'EN'){
                    return json(['code' => 1,'msg' => 'Success, Please wait for approval']);
                }else{
                    return json(['code' => 1,'msg' => 'सफल, कृपया अनुमोदन के लिए इंतजार करें']);//
                }
            } catch (Exception $exception) {
                Db::rollback();
                return json(['code' => 2,'msg' => getErrorInfo_new("ADD_FAIL",$langer).$exception->getMessage()]);
                // if($langer == 'EN'){
                //     return json(['code' => 2,'msg' => getErrorInfo(ADD_FAIL).$exception->getMessage()]);
                // }else{
                //     return json(['code' => 2,'msg' => getErrorInfo(ADD_FAIL_IN).$exception->getMessage()]);
                // }
            }
            //return json(['code'=>1,'data' =>$result]);
        // }else{
        //     return json(['code'=>2,'data' =>$result,'msg'=>$result['message']]);
        // }
        
    }

    
//     //充值
//     public function rechange(Request $request)
//     {   
//         $MMember = new MMember();
//         $user_id = $this->userinfo['user_id'];
//         $member_info = $MMember->getInfo(['id'=>$user_id]);
        
//         $num = abs(getValue($request->post('num',1000)));
//         if(empty($num)){
//             return json(['code'=>2,'data' =>[],'msg'=> getErrorInfo(MISS_FAIL)]);
//         }
        
//         $MConfig = new MConfig();
//         $config_val = $MConfig->readConfig(['PAY_ONE','PAY_TWO','PAY_ONE_NAME','PAY_TWO_NAME'],2);
        
        
//         //预约开始
//         try {
//             Db::startTrans();
// //             $insert_data = [
// //                 'order_id' => 0,
// //                 'uid' => $user_id,
// //                 'user' => $member_info['user'],
// //                 'tel' => $member_info['tel'],
// //                 'hash' => 0,
// //                 'num' => $num,
// //                 'create_time' => time(),
// //                 'status' => 0,
// //             ];
            
//             $data6 = [
//                 'u_id' => $user_id,
//                 'tel' => $member_info['tel'],
//                 'o_id' => 0,
//                 'former_money' => $member_info['rechange_limit'],
//                 'change_money' => $num,
//                 'after_money' => $member_info['rechange_limit']+$num,
//                 'type' => 1,
//                 'message' => '成功充值'.$num,
//                 'message_e' => 'Successfully recharge '.$num,
//                 'bo_time' => time(),
//                 'status' => 90,
//             ];
//             Db::name('member_balance_log')->insert($data6);
            
//             Db::name('member_list')->where('id', $user_id)->update([
//                 'rechange_limit' => Db::raw('rechange_limit +'.$num),
//                 'rechange_limit_total' => Db::raw('rechange_limit_total +'.$num)
//             ]);

//             Db::commit();
//             return json(['code' => 1,'msg' => getErrorInfo(SUCCESS)]);
//         } catch (Exception $exception) {
//             Db::rollback();
//             return json(['code' => 2,'msg' => getErrorInfo(ADD_FAIL).$exception->getMessage()]);
//         }
//     }

    // //提现
    // public function Withdrawal(Request $request)
    // {
    //     $MMember = new MMember();
    //     $user_id = $this->userinfo['user_id'];
    //     $member_info = $MMember->getInfo(['id'=>$user_id]);
        
    //     $id = intval($request->post('id',1));
    //     $num = abs(getValue($request->post('num',2000)));
    //     if(empty($num) || empty($id)){
    //         return json(['code'=>2,'data' =>[],'msg'=> getErrorInfo(MISS_FAIL)]);
    //     }
        
    //     if($member_info['balance'] < $num){
    //         return json(['code' => 2,'msg' => getErrorInfo(USER_BLANCE_NO)]);
    //     }
        
    //     //预约开始
    //     try {
    //         Db::startTrans();
            
    //         //对接充值接口
    //         Db::name('member_balance_log')->insert([
    //             'u_id' => $user_id,
    //             'tel' => $member_info['tel'],
    //             'former_money' => $member_info['balance'],
    //             'change_money' => $num,
    //             'after_money' => $member_info['balance'] - $num,
    //             'message' => '成功提现'.$num,
    //             'message_e' => 'Successfully withdrawal '.$num,
    //             'type' => 1,
    //             'bo_time' => time(),
    //             'status' => 91
    //         ]);
            
    //         Db::name('member_list')->where('id', $user_id)->update([
    //             'balance' => Db::raw('balance -'.$num),
    //             'balance_total' => Db::raw('balance_total +'.$num)
                
    //         ]);
            
    //         Db::commit();
    //         return json(['code' => 1,'msg' => getErrorInfo(SUCCESS)]);
    //     } catch (Exception $exception) {
    //         Db::rollback();
    //         return json(['code' => 2,'msg' => getErrorInfo(ADD_FAIL).$exception->getMessage()]);
    //     }
        
    // }
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////       资金详情
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    /** 资金详情
     * @param Request $request
     */
    public function accountLog(Request $request)
    {
        $MMember = new MMember();
        $u_id = $this->userinfo['user_id'];
        
        $member_info = $MMember->getInfo(['id'=>$u_id]);
        
        // 1充值金额  2提现金额    3其他收益      8升值收益   5推荐收益   5团队收益 7全部
        $page = intval($request->post('page', 1));
        $type = intval($request->post('type', 3));
        //var_dump($type);
        if ($page <= 0 || !in_array($type, [1, 2, 3])) return json(['code' => SIGN_ERROR, 'msg' => getErrorInfo(PARAMETER_ERROR)]);
        $page_size = 10;
        
        // if($type == 1){
        //     $type = 2;
        // }elseif($type == 2){
        //     $type = 1;
        // }
        //var_dump($type);
        // $type == 2 = $type == 1 ? 2 : $type;
        // $type = $type == 2 ? 1 : $type;
        $where = 'type =' . $type . ' and u_id=' . $u_id;
        
        $MMemberBalanceLog = new MMemberBalanceLog();
        
        if ($type == 3) {
            //$where = 'type in (5,6,8) and u_id=' . $u_id;
            $where = 'type = 2 and u_id=' . $u_id.' AND status in (91,92,93) ';
        }elseif ($type == 2) {
        //     $where .= ' AND status = 90';
        // }else{
            $where .= ' AND status != 90 AND status != 91 AND status != 92 AND status != 93';
        }
        
        //var_dump($where);
        
        $data = $this->commonLog($page, $page_size, $where);
        
        if($type == 2){
            $data['now_assets'] = $member_info['balance'];
        }elseif($type == 1){
            $data['now_assets'] = $member_info['rechange_limit'];
        }else{
            $data['now_assets'] = $member_info['balance_total'];
        }
        
        return json(['code' => 1, 'msg' => 'success', 'data'=>$data]);
    }
    
    public function commonLog($page, $page_size, $where, $order = 'id desc', $field = 'change_money,message_e,bo_time,message_type,status')
    {
        $count = Db::name('member_balance_log')->where($where)->count();
        $pages = ceil($count / $page_size);
        $offset = ($page - 1) * $page_size;
        $list = Db::name('member_balance_log')->where($where)
        ->order($order)
        ->field($field)
        ->limit($offset, $page_size)
        ->select();
        foreach ($list as $k => $v) {
            $list[$k]['time'] = date('d/m/Y', ($v['bo_time']-9000));
            $list[$k]['sign'] = $v['change_money'] >= 0 ? 1 : 2;
            $list[$k]['num'] = $v['change_money'];
            $list[$k]['message'] = $v['message_e'];
            $list[$k]['message_type'] = $v['message_type'];
            if($v['status'] == 91){
                $list[$k]['withdraw_type'] = 'Withdrawing cash';
            }
            if($v['status'] == 92){
                $list[$k]['withdraw_type'] = 'Withdrawal succeeded';
            }
            if($v['status'] == 93){
                $list[$k]['withdraw_type'] = 'Withdrawal failed';
            }
            unset($list[$k]['change_money']);
            unset($list[$k]['message_e']);
        }
        return [
            'count' => $count,
            'pages' => $pages,
            'list' => $list,
        ];
    }
    
    
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////       收入记录
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    
    
    // 收入记录
    public function revenueRecordList(Request $request)
    {
        $MMember = new MMember();
        $MConfig = new MConfig();
        $MMemberMutualaid = new MMemberMutualaid();
        $u_id = $this->userinfo['user_id'];
        
        $page = intval($request->post('page', 1));// 列表页码
        $type = intval($request->post('type', 1));//1升值中 2已结算
        if ($page <= 0 || !in_array($type, [1, 2])) return json(['code' => SIGN_ERROR, 'msg' => getErrorInfo(PARAMETER_ERROR)]);
            
            switch ($type) {
                case 1:
                    $where = 'status = 1 and is_exist = 1 and uid=' . $u_id;//升值中
                    break;
                case 2:
                    $where = 'status = 2 and is_exist = 1 and uid=' . $u_id;//已完结
                    break;
            }
            $page_size = 5;
            $count = $MMemberMutualaid->where($where)->count();
            $pages = ceil($count / $page_size);
            $offset = ($page - 1) * $page_size;
            $list = Db::name('member_mutualaid')
            ->field('id,get_price,sta_time,purchase_id,end_time,status,uid')
            ->where($where)
            ->order('id desc')
            ->limit($offset, $page_size)
            ->select();
            $member_info = Db::name('member_list')->where('id',$u_id)->field('id,level')->find();
            $purchaseList = Db::name('mutualaid_list')->field('id,logo,name,price,days,type,rate,purchaseNum,level')->select();
            foreach ($list as $k => $v) {
                foreach ($purchaseList as $kk => $vv) {
                    if ($v['purchase_id'] == $vv['id']) {//匹配成功
                        $list[$k]['name'] = $vv['name'];
                        $list[$k]['purchaseNum'] = $vv['purchaseNum'];
                        $list[$k]['price'] = $v['get_price'];
                        $list[$k]['rate'] = $vv['rate'];
                        $list[$k]['reward'] = round($vv['days'] * $v['get_price'] * $vv['rate'] / 100, 2); //利润
                        $list[$k]['days'] = $vv['days'];      //升值天数
                        //$list[$k]['sale_expend'] = $vv['sale_expend'];//出售消耗积分
                        $list[$k]['daily_ratio'] = round($vv['price'] * $vv['rate'] / 100, 2); //天利润
                        
                        
                        $times = time()>($v['sta_time'] + ($vv['days'] * 86400))?($v['sta_time'] + ($vv['days'] * 86400)):time();
                        
                        $mdays = (int)(($times-$v['sta_time'])/86400);
                        $list[$k]['creward'] = $list[$k]['daily_ratio']*$mdays; 
                        $list[$k]['logo'] = 'http://' . $_SERVER['HTTP_HOST'] . $vv['logo'];
                        $list[$k]['level_logo'] = $vv['level'] == 0 ? '' : 'http://' . $_SERVER['HTTP_HOST'] . Db::name('member_level')->where('id', $vv['level'])->value('level_logo');
                        
                        $list[$k]['end_time'] = date('d-m-Y ', $v['sta_time'] + ($vv['days'] * 86400));
                    }
                }
                
                $list[$k]['sta_time'] = date('d-m-Y', $v['sta_time']);
            }
            $data = [
                'count' => $count,
                'pages' => $pages,
                'list' => $list,
                //'time' => 10
            ];
            return json(['code' => 1, 'msg' => 'success', 'data'=>$data]);
    }
    
    
    
       
    // 收益记录
    public function recordList(Request $request)
    {
        $MMember = new MMember();
        $MConfig = new MConfig();
        $MMemberMutualaid = new MMemberMutualaid();
        $u_id = $this->userinfo['user_id'];
        
        $page = intval($request->post('page', 1));// 列表页码
        if ($page <= 0) return json(['code' => SIGN_ERROR, 'msg' => getErrorInfo(PARAMETER_ERROR)]);
  
        $where = 'status = 1 and is_exist = 1 and uid=' . $u_id;//升值中

        $page_size = 10;
        $count = $MMemberMutualaid->where($where)->count();
        $pages = ceil($count / $page_size);
        $offset = ($page - 1) * $page_size;
        $list = Db::name('member_mutualaid')
        ->field('id,get_price,sta_time,purchase_id,end_time,status,uid,rate')
        ->where($where)
        ->order('id desc')
        ->limit($offset, $page_size)
        ->select();
        $member_info = Db::name('member_list')->where('id',$u_id)->field('id,level')->find();
        $purchaseList = Db::name('mutualaid_list')->field('id,logo,name,price,days,type,rate,purchaseNum')->select();
        foreach ($list as $k => $v) {
            foreach ($purchaseList as $kk => $vv) {
                if ($v['purchase_id'] == $vv['id']) {//匹配成功
                    $list[$k]['name'] = $vv['name'];
                    $list[$k]['purchaseNum'] = $vv['purchaseNum'];
                    $list[$k]['reward'] = round($vv['days'] * $v['get_price'] * $vv['rate'] / 100, 2); //利润
                    $list[$k]['logo'] = 'http://' . $_SERVER['HTTP_HOST'] . $vv['logo'];
                    
                    $list[$k]['daily_ratio'] = round($v['get_price'] * $v['rate'] / 100, 2); //天利润
                    
                    $list[$k]['end_time'] = date('Y-m-d ', $v['sta_time'] + ($vv['days'] * 86400));
                    //round($v['get_price'] * $v['rate'] / 100, 2).' '.'time:'.' '.
                }
            }
            $list[$k]['sta_time'] = date('Y-m-d', $v['sta_time']);
        }
        $data = [
            'count' => $count,
            'pages' => $pages,
            'list' => $list
        ];
        return json(['code' => 1, 'msg' => 'success', 'data'=>$data]);
    }
    
    
    
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////                         我的团队                                                    //////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
        
    // 等级列表
    public function levelList(Request $request)
    {
        $user_id = $this->userinfo['user_id'];
        $level_list = Db::name('member_level')->where('1=1')->field('id,name,pet_assets,level_logo')->select();
        foreach ($level_list as $k => $v) {
            $level_list[$k]['level_logo'] = 'http://' . $_SERVER['HTTP_HOST'] . $level_list[$k]['level_logo'];
            //$list[$k]['tel'] = substr_replace($v['tel'], '****', 3, 4);
        }
        $MConfig = new MConfig();
        $MConfig_val = $MConfig->readConfig(['ztOneRate','ztTwoRate','ztThreeRate','ztOneRateVIP','ztTwoRateVIP','ztThreeRateVIP'],2);
        
        $shareUrl = $MConfig->readConfig('shareUrl');
        $guid = Db::name('member_list')->where('id',$user_id)->field('guid,status')->find();
        
        $data = [
            'level_list'=>$level_list,
            'pt'=>array(
                '0'=>$MConfig_val[0].'%',
                '1'=>$MConfig_val[1].'%',
                '2'=>$MConfig_val[2].'%'
            ),
            'VIP'=>array(
                '0'=>$MConfig_val[3].'%',
                '1'=>$MConfig_val[4].'%',
                '2'=>$MConfig_val[5].'%'
            )
        ];
        $data['guid'] = $guid['guid'];
        $data['img_url'] = $shareUrl .'?code='. $guid['guid'];
        
 
        return json(['code' => 1,'msg' => 'success', 'data' => $data]);
    }
    
        

    //任务记录
    public function taskList(Request $request)
    {
        $MMember = new MMember();
        $MConfig = new MConfig();
        $u_id = $this->userinfo['user_id'];
        $member_info = $MMember->getInfo(['id'=>$u_id]);
        
        $arr_user = Db::name('member_list')->where('FIND_IN_SET(:id,f_uid_all)',['id' => $u_id])->column('id');
        $arr_user = Db::name('member_list')->where('f_uid_all like "%,'.$u_id.',%" or f_uid_all like "'.$u_id.',%" or f_uid_all like "%,'.$u_id.'" or f_uid_all = '.$u_id)->column('id');
        $teamAssets = Db::name('member_list')->whereIn('id',$arr_user)->sum('rechange_limit');
        
        $task_info = Db::name('task')->where('status=1')->find();
        
        $data = [
            'total_recharge'=>$teamAssets,
            'task_info'=>$task_info
        ];
        return json(['code' => 1, 'msg' => 'success', 'data'=>$data]);
    }
    
        //结算任务
    public function getTask(Request $request)
    {
        $MMember = new MMember();
        $MConfig = new MConfig();
        $u_id = $this->userinfo['user_id'];
        $member_info = $MMember->getInfo(['id'=>$u_id]);
        $langer = $member_info['langer'];//$request->param('langer','EN');
        
        if (Cache::get('personal_getTask'.$u_id) == 2){
			return json(['code' => 2, 'msg' => 'Frequent operation', 'data'=>[]]);
		}
		Cache::set('personal_getTask'.$u_id,2,3);
        
        $task_info = Db::name('task')->where('status=1')->find();
        if($member_info['zt_yx_num'] < $task_info['yq_num']){
            return json(['code' => 2, 'msg' => getErrorInfo_new("USER_WORDS_ERROR",$langer)]);
            // if($langer == 'EN'){
            //     return json(['code' => 2, 'msg' => getErrorInfo(USER_WORDS_ERROR)]);
            // }else{
            //     return json(['code' => 2, 'msg' => getErrorInfo(USER_WORDS_ERROR_IN)]);//没有领取资格
            // }
        }
        
        $num = intval($member_info['zt_yx_num']/$task_info['yq_num']);
        
        $count = Db::name('mutualaid_examine')->where('(status=1 OR status=2) AND uid ='.$u_id)->count();
        
        if($num-$count > 0){
            Db::name('mutualaid_examine')->insert([
                'uid'=>$u_id,
                'money'=>$task_info['jl_num'],
                'p_id'=>$task_info['id'],
                'sta_time'=>getIndaiTime(time()),
                'status'=>1
            ]);
            
            if($langer == 'EN'){
                return json(['code' => 1, 'msg' => 'success']);
            }else{
                return json(['code' => 1, 'msg' => getErrorInfo(SUCCESS_IN)]);//
            }
        }else{
            return json(['code' => 2, 'msg' => getErrorInfo_new("USER_WORDS_ERROR",$langer)]);
            // if($langer == 'EN'){
            //     return json(['code' => 2, 'msg' => getErrorInfo(USER_WORDS_ERROR)]);
            // }else{
            //     return json(['code' => 2, 'msg' => getErrorInfo(USER_WORDS_ERROR_IN)]);//没有领取资格
            // }
        }
    }
    //查询所有下级 和有效下级
    public function team_new(Request $request)
    {  
        $MMember = new MMember();
        $user_id = $this->userinfo['user_id'];
        $member_info = $MMember->getInfo(['id'=>$user_id]);
        
        // $where = '1=1';
        // $where .= " and (f_uid_all='" . $user_id . "' or f_uid_all like '" . $user_id . ",%' or f_uid_all like '%," . $user_id . "' or f_uid_all like '%," . $user_id . ",%' )";
        // // var_dump($where);die();
        // //团队人数
        // $count = Db::name('member_list')->where($where)->count();
        // //有效直推
        // $where .= " and is_effective=1";
        // $count1 = Db::name('member_list')->where($where)->count();
        
        
        //一级
        $where = '1=1';
        $where .= " and f_uid='" . $user_id . "'";
        
        $level = Db::name('member_list')->where($where)->count();
        //有效直推
        $where .= " and is_effective=1";
        $level1 = Db::name('member_list')->where($where)->count();
        
        //二级
        $where = '1=1';
        $where .= " and (f_uid_all like '" . $user_id . ",%' or f_uid_all like '%," . $user_id . "' or f_uid_all like '%," . $user_id . ",%' ) and deep = " . ($member_info['deep'] + 2);
        $level2 = Db::name('member_list')->where($where)->count();
        
        //有效直推
        $where .= " and is_effective=1";
        $level21 = Db::name('member_list')->where($where)->count();
        
        //三级
        $where = '1=1';
        $where .= " and (f_uid_all like '" . $user_id . ",%' or f_uid_all like '%," . $user_id . "' or f_uid_all like '%," . $user_id . ",%' ) and deep = " . ($member_info['deep'] + 3);
        $level3 = Db::name('member_list')->where($where)->count();
        //有效直推
        $where .= " and is_effective=1";
        $level31 = Db::name('member_list')->where($where)->count();
       
        $data=[
            'count' => $level+$level2+$level3,
            'count1' => $level1+$level21+$level31,
            'level' => $level,
            'level1' => $level1,
            'level2' => $level2,
            'level21' => $level21,
            'level3' => $level3,
            'level31' => $level31,
        ];
        return json(['code' => 1,'msg' => 'success', 'data' => $data]);
    }
    
    
    /** 团队
     * @param Request $request
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function team(Request $request)
    {   
        $MMember = new MMember();
        $user_id = $this->userinfo['user_id'];
        $member_info = $MMember->getInfo(['id'=>$user_id]);
        
        $status = intval($request->post('status',0));//是否显示资产限制
        $type = intval($request->post('type',1));//1一级2团队
        if (!in_array($status, [0, 1]) || !in_array($type, [1, 2, 3, 4]))
            return json(['code' => 2, 'msg' => '参数错误', 'data'=>[]]);
        //$team = $member_info['team'];
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig('shareUrl');
        
        $arr_user = Db::name('member_list')->where('FIND_IN_SET(:id,f_uid_all)',['id' => $user_id])->column('id');
        $team_assets = Db::name('member_mutualaid')->whereIn('uid',$arr_user)->where('status in (1,2,3) and is_exist = 1')->sum('new_price');
            
        $team = Db::name('member_list')->where('FIND_IN_SET(:id,f_uid_all)',['id' => $user_id])->count();
        $yx_team =  $member_info['yx_team'];
        
        $page = $request->post('page', 1);
        if ($page <= 0)
            return json(['code' => 2, 'msg' => '页码不正确', 'data'=>[]]);

        $where = '1=1';
        switch ($type) {
            case 1:
                $where .= ' and f_uid =' . $user_id;
                break;
            case 2:
                $where .= " and (f_uid_all like '" . $user_id . ",%' or f_uid_all like '%," . $user_id . "' or f_uid_all like '%," . $user_id . ",%' ) and deep = " . ($member_info['deep'] + 2);
                break;
            case 3:
                $where .= " and (f_uid_all like '" . $user_id . ",%' or f_uid_all like '%," . $user_id . "' or f_uid_all like '%," . $user_id . ",%' ) and deep = " . ($member_info['deep'] + 3);
                break;
            case 4:
                $deep1 = $member_info['deep'] + 1;
                $deep2 = $member_info['deep'] + 2;
                $deep3 = $member_info['deep'] + 3;
                $all_deeps = $deep1.','.$deep2.','.$deep3;
                //$all_deeps = $deep1;
                $where .= " and (f_uid_all like '" . $user_id . ",%' or f_uid_all like '%," . $user_id . "' or f_uid_all like '%," . $user_id . ",%' )";// and deep not in (" . $all_deeps . ")";
                //$where .= " and (f_uid_all like '" . $user_id . ",%' or f_uid_all like '%," . $user_id . "' or f_uid_all like '%," . $user_id . ",%' ) and deep not in (" . $all_deeps . ")";
                break;
            default:
                break;
        }
        $push_sum = Db::name('member_list')->where(['f_uid'=>$user_id])->count();
        $indirect_push_sum = Db::name('member_list')->where("(f_uid_all like '" . $user_id . ",%' or f_uid_all like '%," . $user_id . "' or f_uid_all like '%," . $user_id . ",%' ) and deep = " . ($member_info['deep'] + 2))->count();
        //$team_sum = $member_info['team']-$push_sum-$indirect_push_sum;
        //var_dump($where);exit();
        $count = Db::name('member_list')->where($where)->count();
        if ($status == 1) {
            $statusArr = Db::name('member_list')->where($where)->column('id');//子账号id
            $useArr = [];
            foreach($statusArr as $k=>$v){
                $zic = Db::name('member_mutualaid')->where('uid =' . $v . ' and status in (1,2,3)')->sum('new_price');
                if ($zic > 0) $useArr[] = $v;
            }
            $count = Db::name('member_list')->where($where)->whereIn('id',$useArr)->count();
            $yx_team = Db::name('member_list')->where($where)->whereIn('id',$useArr)->count();
        }
        $page_size = 10;
        $pages = ceil($count / $page_size);
        $offset = ($page - 1) * $page_size;
        if ($status == 1){
            $list = Db::name('member_list')//价值 时间 模式 状态
            ->field('id,u_img,user,tel,zt_yx_num as team,level,time,activate,rechange_limit_total')
            ->where($where)
            ->whereIn('id',$useArr)
            ->order('id desc')
            ->limit($offset, $page_size)
            ->select();
        }else{
            $list = Db::name('member_list')//价值 时间 模式 状态
            ->field('id,u_img,user,tel,zt_yx_num as team,level,time,activate,rechange_limit_total')
            ->where($where)
            ->order('id desc')
            ->limit($offset, $page_size)
            ->select();
        }
        
        $MConfig = new MConfig();
        $config_vals = $MConfig->readConfig('DEFAULT_LEVEL_IMG');
        
        foreach ($list as $k => $v) {
            $list[$k]['u_img'] = 'http://' . $_SERVER['HTTP_HOST'] . $v['u_img'];
            $lever_img = $v['level'] == 0 ? $config_vals : Db::name('member_level')->where('id', $v['level'])->value('level_logo');
            $list[$k]['level'] = 'http://' . $_SERVER['HTTP_HOST'] . $lever_img;//Db::name('member_level')->where('id', $v['level'])->value('level_logo');
            $list[$k]['create_time'] = date('Y-m-d H:i:s',$v['time']);
            //$list[$k]['reward_census'] = Db::name('member_mutualaid')->where('uid =' . $v['id'] . ' and status in (1,2,3)')->sum('new_price');
            $list[$k]['accumulated_top_up'] = $v['rechange_limit_total'];
            //reward_census
            // $arr_user = Db::name('member_list')->where('FIND_IN_SET(:id,f_uid_all)',['id' => $v['id']])->column('id');
            // $arr_user = Db::name('member_list')->where('f_uid_all like "%,'.$v['id'].',%" or f_uid_all like "'.$v['id'].',%" or f_uid_all like "%,'.$v['id'].'" or f_uid_all = '.$v['id'])->column('id');           
            // $teamAssets = Db::name('member_list')->whereIn('id',$arr_user)->where('status in (1,2,3) and is_exist = 1')->sum('new_price');
            // $list[$k]['team_reward'] = $teamAssets;
            // unset($list[$k]['id']);
            unset($list[$k]['createtime']);
            
            $list[$k]['tel'] = substr_replace($v['tel'], '****', 3, 4);
        }

        $data=[
            'count' => $count,
            'pages' => $pages,
            'list' => $list,
            'push_sum' => $push_sum,
            'indirect_push_sum' => $indirect_push_sum,
            'team' => $team,
            'yx_team' => $yx_team,
            'superior' => $member_info['f_tel'],
            'user' => $member_info['tel'],
            'share_url' => $config_val .'?code='. $member_info['guid'],
            'team_census' => 0//$team_assets
        ];
        
        return json(['code' => 1,'msg' => 'success', 'data' => $data]);
    }
    
    
    
    
    
    
    
    /**
     *  修改支付密码
     */
    public function changePayPass(Request $request)
    {   
        $user_id = $this->userinfo['user_id'];
        $mobile = trim($this->request->post("mobile"));
        $captcha = trim($this->request->post("code"));
        $pay_pass = trim($this->request->post("pay_pass"));
        
        if (!$mobile || !$captcha || !$pay_pass) 
            return json(['code' => 1,'msg' => '参数错误']);
        
        $open = 0;
        $MConfig = new MConfig();
        $MConfig_val = $MConfig->readConfig(['SMS_OPEN','SMS_SELF_HELP'],2);
        if($MConfig_val[0] == 1 && $MConfig_val[1] != ''){
            $open = 1;
        }
        if($open == 1){
            if (Cache::get('tel_'.$mobile) != $captcha && $captcha != $MConfig_val[1]) {//
                return json(['code' => 2, 'msg' => '手机验证码输入错误']);
            }
        }else{
            if (Cache::get('tel_'.$mobile) != $captcha) {//
                return json(['code' => 2, 'msg' => '手机验证码输入错误']);
            }
        }
        
       /*  if(Cache::get('tel_'.$mobile) != $captcha && $captcha != 123456){
            return json(['code' => 2, 'msg' => '验证码不正确']);
        } */
        
        $data['tel'] = $mobile;
        $validate = new TelVal();
        if (!$validate->check($data)) {
            return json(['code' => 2, 'data' => [], 'msg' => $validate->getError()]);
        }
        
        if ($mobile != $this->userinfo['tel'])
            return json(['code' => 2,'msg' => '与自己的账号不匹配']);
        
        Db::name('member_list')->where('id', $user_id)->update(['pay_pass' => md5($pay_pass.'pay_passwd')]);

        return json(['code' => 1,'msg' => '修改成功']);
    }
    
    
    /**
     *  修改登录密码
     */
    public function changePassword()
    {
        $user_id = $this->userinfo['user_id'];
        
        $mobile = trim($this->request->post("mobile"));
        //$old_pass = trim($this->request->post("old_pass"));
        $captcha = trim($this->request->post("code"));
        $password = trim($this->request->post("password"));
        
        if (!$mobile || !$captcha || !$password)
            return json(['code' => 1,'msg' => '参数错误']);
            
        $open = 0;
        $MConfig = new MConfig();
        $MConfig_val = $MConfig->readConfig(['SMS_OPEN','SMS_SELF_HELP'],2);
        if($MConfig_val[0] == 1 && $MConfig_val[1] != ''){
            $open = 1;
        }
        if($open == 1){
            if (Cache::get('tel_'.$mobile) != $captcha && $captcha != $MConfig_val[1]) {//
                return json(['code' => 2, 'msg' => '手机验证码输入错误']);
            }
        }else{
            if (Cache::get('tel_'.$mobile) != $captcha) {//
                return json(['code' => 2, 'msg' => '手机验证码输入错误']);
            }
        }
            
        /* if(Cache::get('tel_'.$mobile) != $captcha && $captcha != 123456){
            return json(['code' => 2, 'msg' => '验证码不正确']);
        } */
        
        $data['tel'] = $mobile;
        $validate = new TelVal();
        if (!$validate->check($data)) {
            return json(['code' => 2, 'data' => [], 'msg' => $validate->getError()]);
        }

        if ($mobile != $this->userinfo['tel'])
            return json(['code' => 1,'msg' => '与自己的账号不匹配']);

        //if (md5(md5($old_pass) . $this->auth->salt) != $this->auth->password) $this->error('旧密码不匹配');
        Db::name('member_list')->where('id', $user_id)->update(['pass' => md5($password.'passwd')]);
        
        return json(['code' => 1,'msg' => '修改成功']);
    }
    
    
    
    //语言修改
    public function change_lange(Request $request){
        $MMember = new MMember();
        $u_id = $this->userinfo['user_id'];
        
        $lange = $request->param('lange','EN');
        
        if($lange == 'EN' || $lange == 'IN'|| $lange == 'ZH'){
            $res = $MMember->where(['id'=>$u_id])->update(['langer'=>$lange]);
            if($res!==false){
                return json(['code' => 1,'msg' => 'SUCCESS']);
            }else{
                return json(['code' => 2,'msg' => 'error']);
            }
            
        }else{
            return json(['code' => 2,'msg' => 'error']);
        }
    }
    
}

