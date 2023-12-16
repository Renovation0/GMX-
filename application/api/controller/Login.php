<?php
namespace app\api\controller;
use think\Request;
use think\Db;
use app\api\validate\LoginVal;
use app\api\model\MMember;
use think\facade\Cache;
use app\api\validate\TelVal;
use app\api\model\MConfig;
use think\captcha\Captcha;

header('Content-Type: text/html;charset=utf-8');
header('Access-Control-Allow-Origin:*'); // *代表允许任何网址请求
header('Access-Control-Allow-Methods:POST,GET,OPTIONS,DELETE'); // 允许请求的类型
header('Access-Control-Allow-Credentials: true'); // 设置是否允许发送 cookies
header('Access-Control-Allow-Headers: Content-Type,Content-Length,Accept-Encoding,X-Requested-with, Origin');

class Login extends Base
{   
       
    //注册
    public function register(Request $request){
        $name = $request->param('name'); // 姓名
        $phone = $request->param('phone',''); // 手机号码
        $pass = $request->param('password',''); // 登录密码
        $pay_pass = $request->param('pay_pass',''); // 支付密码
        $code = $request->param('code'); // 验证码
        $guid = $request->param('guid');//邀请码
        $langer = $request->param('langer','EN');
        $abc = '91';
        
        
        if (!$code) {
            if($langer == 'ZH'){
                return json(['code' => 2, 'msg' => '手机验证码输入错误']);
            }elseif($langer == 'EN'){
                return json(['code' => 2, 'msg' => 'Mobile phone verification code input error']);
            }else{
                return json(['code' => 2, 'msg' => 'प्रमाणीकरण कोड इनपुट त्रुटि']);//密码不能为空
            }
        }
        
        if (!$phone) {// || !$code
            if($langer == 'EN'){
                return json(['code' => 2, 'msg' => getErrorInfo(NULL_PHONE)]);
            }else{
                return json(['code' => 2, 'msg' => getErrorInfo(NULL_PHONE_IN)])->send();//手机号码不能为空
            }
        }
        if (!$pass) {// || !$pay_pass
            if($langer == 'EN'){
                return json(['code' => 2, 'msg' => getErrorInfo(NULL_PASSWORD)]);
            }else{
                return json(['code' => 2, 'msg' => getErrorInfo(NULL_PASSWORD_IN)])->send();//密码不能为空
            }
        }
                
        $open = 0;
        $MConfig = new MConfig();
        $MConfig_val = $MConfig->readConfig(['SMS_OPEN','SMS_SELF_HELP'],2);
        if($MConfig_val[0] == 1 && $MConfig_val[1] != ''){
            $open = 1;
        }

        if($open == 1){
            if (Cache::get('tel_'.$abc.$phone) != $code && $code != $MConfig_val[1]) {//
                //return json(['code' => 2, 'msg' => '手机验证码输入错误']);
                if($langer == 'ZH'){
                    return json(['code' => 2, 'msg' => '手机验证码输入错误']);
                }elseif($langer == 'EN'){
                    return json(['code' => 2, 'msg' => 'Mobile phone verification code input error']);
                }else{
                    return json(['code' => 2, 'msg' => 'प्रमाणीकरण कोड इनपुट त्रुटि']);//密码不能为空
                }
            }
        }else{
            if (Cache::get('tel_'.$abc.$phone) != $code) {//
                if($langer == 'ZH'){
                    return json(['code' => 2, 'msg' => '手机验证码输入错误']);
                }elseif($langer == 'EN'){
                    return json(['code' => 2, 'msg' => 'Mobile phone verification code input error']);
                }else{
                    return json(['code' => 2, 'msg' => 'प्रमाणीकरण कोड इनपुट त्रुटि']);//密码不能为空
                }
            }
        } 


        if (!$guid) {// || !$pay_pass
            return json(['code' => 2, 'msg' => getErrorInfo_new("NO_REGISTER_PHONE",$langer)]);
            // if($langer == 'EN'){
                
            //     return json(['code' => 2, 'msg' => getErrorInfo(NO_REGISTER_PHONE)]);
            // }else{
            //     return json(['code' => 2, 'msg' => getErrorInfo(NO_REGISTER_PHONE_IN)])->send();//邀请码不能为空
            // }
        }
        
        if ($pass && $phone && $guid){
            $ip = $this->getIp();
            $memberListModel = new MMember();
            return $memberListModel->register($phone, $pass, $pay_pass, $guid, $ip, $name, $langer);
        }else{
            return json(['code' => 2, 'msg' => getErrorInfo_new("MISS_FAIL",$langer)]);
            // if($langer == 'EN'){
            //     return json(['code' => 2, 'msg' => getErrorInfo_new("MISS_FAIL",$langer)]);
            // }else{
            //     return json(['code' => 2, 'msg' => getErrorInfo_new("MISS_FAIL",$langer)])->send();//缺少必要参数
            // }
        }
    }
    
    public function test(Request $request)
    {
        $member_info = Db::name('member_bm_recharge')->where(['uid'=>12,'status'=>1])->whereTime('update_time', 'today')->find();
        var_dump($member_info);
        //var_dump(RSA_openssl("4aacf2e03fa1ac3ba2e7b92ece4dbd5b","encode"));die();
        $str="xfa3dPGWyz9KBbyniFzTlZFUSW9wmlr%2FSJzSJU98ErBI7Y%2FpMD9jQRCsiyqMSQalzy3G1QF7aOWv%2B5X9YPYxahQnDL0wq%2F88UrseiIab0BSgwxwGqJMf3I%2FmZr5Ax0BTqKDO5xzpwhVyII7ljCEMwxjGW4Jv8Gs6I8U1kAd7fak%3D";
        
         var_dump(RSA_openssl(urldecode($str),"decode"));die();
        
    }
    
    //登录
    public function sign_in(Request $request)
    {
        $data = $request->param();
        //array_shift($data);
        $langer = $request->param('langer','EN');
        // var_dump($langer);die();
        if(!isset($data['appid']) || empty($data['appid'])){
            return json(['code' => 2, 'data' => [], 'msg' => getErrorInfo_new('MISS_FAIL',$langer)]);//'密码错误'
            exit;
        }
        
        $appinfo = Db::name('app_secret')->where('app_id',$data['appid'])->field('secret')->find();
        if(empty($appinfo)){
            // if($langer == 'EN'){
            //     return json(['code' => 2,'data' =>[], 'msg' => getErrorInfo(PARAMETER_ERROR)])->send();
            // }else{
            //     return json(['code' => 2, 'msg' => getErrorInfo(PARAMETER_ERROR_IN)])->send();//参数错误
            // }
            return json(['code' => 2,'data' =>[], 'msg' => getErrorInfo_new("PARAMETER_ERROR",$langer)])->send();
            exit;
        }
        $sign_str = $this->getSign($appinfo['secret'], $data);
        if($data['sign'] !== $sign_str){
            // if($langer == 'EN'){
            //     return json(['code' => 2,'data' =>[], 'msg' => getErrorInfo(PARAMETER_ERROR)])->send();
            // }else{
            //     return json(['code' => 2, 'msg' => getErrorInfo(PARAMETER_ERROR_IN)])->send();//参数错误
            // }
            return json(['code' => 2,'data' =>[], 'msg' => getErrorInfo_new("PARAMETER_ERROR",$langer)])->send();
            exit;
        }
        $username = $data['phone'];
        $password = $data['password'];
/*         
        $validate = new LoginVal();
        if (!$validate->check($data)) {
            return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => $validate->getError()]);
        } */
        
        $passwordMd5 = md5($password.'passwd');
                
        $ip = $this->getIp();
        $MMember = new MMember();
        return $MMember->login_result($username, $passwordMd5, $ip, $data['appid'], $langer);
    }
    
    
    //登录
    public function sign_in_new(Request $request)
    {
        // $str="lOSmqkW1UBrwQsgluXa0kYeAM+xE2dLqNTfnuilpYUE2YJr5NcZxyO//6tik0zrLm3MX58+1eEOaRUxRwYx0fEpdzX4ZmidYgl/4Fqu5j51baR6fFRb30XdiWDTQnvjgRHvLCVntxGWSIGfulbEPJIdqiDGzOiLXGOdNQniifXg=";
        // var_dump(RSA_openssl($str,"decode"));die();
        // var_dump(RSA_openssl("dfbd72d978105eb7b4b51ce1c97f7140","encode"));die();
        $data = $request->param();
        $langer = $request->param('langer','EN');
     
        if(!isset($data['appid']) || empty($data['appid'])){
            return json(['code' => 2, 'data' => [], 'msg' => getErrorInfo_new('MISS_FAIL',$langer)]);//'密码错误'
            exit;
        }
        
        $appinfo = Db::name('app_secret')->where('app_id',$data['appid'])->field('secret')->find();
        if(empty($appinfo)){
            return json(['code' => 2,'data' =>[], 'msg' => getErrorInfo_new("PARAMETER_ERROR",$langer)])->send();
            exit;
        }
        $sign_str = $this->getSign($appinfo['secret'], $data);
        if($data['sign'] !== $sign_str){
            return json(['code' => 2,'data' =>[], 'msg' => getErrorInfo_new("PARAMETER_ERROR",$langer)])->send();
            exit;
        }
        $username = $data['phone'];
        $password = decrypt($data['password']);
        $ip = $this->getIp();
        $MMember = new MMember();
        return $MMember->login_result($username, $password, $ip, $data['appid'], $langer);
    }

    
    //忘记密码
    public function forget_pass(Request $request){

        $phone = $request->param('phone'); // 手机号
        // $phone = substr($phone,0,2)=="91"?$phone:'91'.$phone;
            
        $pass = $request->param('password'); // 密码
        $code = $request->param('code'); // 短信验证
        $langer = $request->param('langer','EN');
        if (!$phone || $phone == '') {
             if($langer == 'ZH'){
                return json(['code' => 2, 'msg' => '电话号码不能为空']);
            }elseif($langer == 'EN'){
                return json(['code' => 2, 'msg' => 'Please enter your mobile number']);
            }else{
                return json(['code' => 2, 'msg' => 'कृपया अपना मोबाइल संख्या भरें'])->send();//电话号码不能为空
            }
        }
        if (!$code || $code == '') {
            if($langer == 'ZH'){
                return json(['code' => 2, 'msg' => '短信验证码不能为空']);
            }elseif($langer == 'EN'){
                return json(['code' => 2, 'msg' => 'Please enter SMS verification code']);
            }else{
                return json(['code' => 2, 'msg' => 'कृपया SMS सत्यापन कोड भरें'])->send();//短信验证码不能为空
            }
        }
        if (!$pass || $pass == '') {
            if($langer == 'ZH'){
                return json(['code' => 2, 'msg' => '密码不能为空']);
            }elseif($langer == 'EN'){
                return json(['code' => 2, 'msg' => 'Please input a password']);
            }else{
                return json(['code' => 2, 'msg' => 'कृपया पासवर्ड प्रविष्ट करें'])->send();//密码不能为空
            }
        }
        $open = 0;
        $MConfig = new MConfig();
        $MConfig_val = $MConfig->readConfig(['SMS_OPEN','SMS_SELF_HELP'],2);
        if($MConfig_val[0] == 1 && $MConfig_val[1] != ''){
            $open = 1;
        }
        if($open == 1){
            if (Cache::get('tel_'.$phone) != $code && $code != $MConfig_val[1]) {//
                if($langer == 'ZH'){
                    return json(['code' => 2, 'msg' => '验证码错误']);
                }elseif($langer == 'EN'){
                    return json(['code' => 2, 'msg' => 'Mobile phone verification code error']);
                }else{
                    return json(['code' => 2, 'msg' => 'सत्यापन कोड त्रुटि'])->send();//验证码错误
                }
            }
        }else{
            if (Cache::get('tel_'.$phone) != $code) {//
                if($langer == 'ZH'){
                    return json(['code' => 2, 'msg' => '验证码错误1']);
                }elseif($langer == 'EN'){
                    return json(['code' => 2, 'msg' => 'Mobile phone verification code error']);
                }else{
                    return json(['code' => 2, 'msg' => 'सत्यापन कोड त्रुटि'])->send();//验证码错误
                }
            }
        }
        /* if (Cache::get('tel_'.$phone) != $code && $code != 123456) {// 
            return json(['code' => 2, 'msg' => '手机验证码输入错误']);
        }else{ */
        $MMember = new MMember();   

        $res = $MMember->getValue(['tel'=>$phone], 'id');
        if ($MMember->getValue(['tel'=>$phone], 'id') == true ){
                                                                 
            if ($MMember->where(['tel'=>$phone])->update(['pass'=>md5($pass.'passwd')])){//md5(md5($pass,'passwd'))
                if($langer == 'ZH'){
                    return json(['code' => 1, 'msg' => '修改成功']);
                }elseif($langer == 'EN'){
                    return json(['code' => 1, 'msg' => 'Password modified successfully']);
                }else{
                    return json(['code' => 1, 'msg' => 'पासवर्ड सफलतापूर्वक परिवर्धित'])->send();//修改成功
                }
            }else{
                if($langer == 'ZH'){
                    return json(['code' => 2, 'msg' => '密码修改失败']);
                }elseif($langer == 'EN'){
                    return json(['code' => 2, 'msg' => 'Failed to modify password']);
                }else{
                    return json(['code' => 2, 'msg' => 'पासवर्ड परिवर्तन असफल'])->send();//密码修改失败
                }
            }
        }else{
             if($langer == 'ZH'){
                 return json(['code' => 2, 'msg' => '用户不存在']);
            }elseif($langer == 'EN'){
                return json(['code' => 2, 'msg' => 'User does not exist']);
            }else{
                return json(['code' => 2, 'msg' => 'प्रयोक्ता मौजूद नहीं है'])->send();//用户不存在
            }
        }           
        //}
    }
    
    
    //退出
    public function login_out(Request $request){
        $token = $request->param('token');
        //删除登录token
        Db::name('login_token')->where(array('token'=>$token))->delete();
        return json(['code' => 1,'msg' => getErrorInfo(SUCCESS)]);       
    }
    
    //注册协议
    public function reg_agreement(){
        $info = Db::name('gvrp')->where('id = 1')->field('content')->find();
        return json(['code' => 1,'data' =>$info]);
    }
    
    
    //版本号
    public function versionUp(){

        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig(['versionCheck','uploadUrl'],2);
        
        $data['app_v'] = $config_val[0];
        $data['app_wgt_url'] = $config_val[1];
        
        return json(['code' => 1, 'msg' => 'ok','data' => $data]);
        
    }
    
    //获取更新地址
    public function getdownload(){
        
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig(['apkDownload','iosDownload'],2);
        
        $data['apkurl'] = $config_val[0];
        $data['iosurl'] = $config_val[1];

        return json(['code' => 1, 'msg' => 'ok','data' => $data]);
        
    }

    //注册
    public function sendRegister(Request $request)
    {
        $phone = $request->post('phone');
        $langer = $request->param('langer','EN');
        
        if($phone == ''){
            if($langer == 'ZH'){
                return json(['code' => 2, 'data' => [], 'msg' => '电话号码不能为空']);
            }elseif($langer == 'EN'){
                return json(['code' => 2, 'data' => [], 'msg' => 'Mobile phone number not obtained']);
            }else{
                return json(['code' => 2, 'msg' => 'कृपया अपना मोबाइल संख्या भरें'])->send();//电话号码不能为空
            }
        }
        if(strlen($phone)!=10){
        	if($langer == 'ZH'){
                return json(['code' => 2, 'data' => [], 'msg' => '电话号码不正确']);
            }elseif($langer == 'EN'){
                return json(['code' => 2, 'data' => [], 'msg' => 'Incorrect phone number']);
            }else{
                return json(['code' => 2, 'msg' => 'गलत फोन संख्या'])->send();//电话号码不能为空
            }
        }
        $phone = '91'.$phone;
        return $this->sendPostNew($phone, 2,'register',$langer);
    }
    

    public function sendPostNew($mobile, $type = 2,$event = '',$langer)
    {   
        $MConfig = new MConfig();
        $MConfig_val = $MConfig->readConfig(['SMS_ACCOUNT','SMS_PASSWORD'],2);
        if(empty($MConfig_val[0]) || empty($MConfig_val[1])){
            //return json(['code' => 2, 'data' => [], 'msg' => 'Sending failed, SMS configuration error']);
        }

        if (Cache::get('code_'.$mobile) == $mobile) {
            if($langer == 'ZH'){
                return json(['code' => 2, 'data' => [], 'msg' => '不要频繁发送短信']);
            }elseif($langer == 'EN'){
                return json(['code' => 2, 'data' => [], 'msg' => 'Do not send text messages frequently']);
            }else{
                return json(['code' => 2, 'msg' => 'कृपया अपना मोबाइल संख्या भरें'])->send();
            }
            
        }
        
        $code = mt_rand(100000, 999999);
        //if($type == 2){
            Cache::set('tel_'.$mobile,$code,70);
        //}
        //Cache::set('tel_'.$mobile,$code,70);
        Cache::set('code_'.$mobile,$mobile,60);
        
        $url = "https://api.onbuka.com/v3/sendSms";
        $apiKey = "xUlYpKzX";    //Api认证名（查询位置 客户端：页面账户管理-API管理-认证名）
        $apiSecret = "nTDCgEj4";  //认证密钥（查询位置  客户端：页面账户管理-API管理-认证密钥）
        
        $appId = "RpGKQ5Ls";    //应用id（查询位置 客户端：页面账户管理-应用管理-appId属性）
        $numbers = $mobile;   //短信接收号码，多个号码之间以英文逗号分隔（get最多100个,post最多1000个）
        $content = "Dear Customer, Your Gem-Xtra OTP is ".$code;   //发送内容，get请求内容需要urlEncode
        $senderId = "";    //发送的号码(非必填)
        
        $data = [
            'appId' => $appId,
            'numbers' => $numbers,
            'content' => $content,
            //'senderId'=>$senderId //非必填，使用时可解除注释
        ];
        
        $timeStamp = time();
        $sign = md5($apiKey . $apiSecret . $timeStamp);
        
        $headers = [
            'Content-Type:application/json;charset=UTF-8',
            "Sign:$sign",
            "Timestamp:$timeStamp",
            "Api-Key:$apiKey"
        ];
        
        $curl = curl_init();
        $param[CURLOPT_URL] = $url;
        $param[CURLOPT_HTTPHEADER] = $headers;
        $param[CURLOPT_RETURNTRANSFER] = true;
        $param[CURLOPT_FOLLOWLOCATION] = true;
        $param[CURLOPT_POST] = true;
        $param[CURLOPT_POSTFIELDS] = json_encode($data);
        $param[CURLOPT_SSL_VERIFYPEER] = false;
        $param[CURLOPT_SSL_VERIFYHOST] = false;
        curl_setopt_array($curl,$param); //传参数
        $data = curl_exec($curl);       //执行命令
        curl_close($curl);
        //return $data;
        
        $string = substr($data,0,strpos($data, ','));
        $string = str_replace('"','',$string);
        $result = str_replace('{status:','',$string);

        $time = time();
        if ($result == '0') {
        //if (true) {
            $res = Db::name('system_sms_log')->insert([
                'tel'=>$mobile,
                'code'=>$code,
                'type'=>'验证码',
                'time'=>getIndaiTime($time)
            ]);
            
            if($langer == 'ZH'){
                return json(['code' => 1, 'data' => [], 'msg' => '发送成功']);
            }elseif($langer == 'EN'){
               
                return json(['code' => 1, 'data' => [], 'msg' => 'Sent successfully']);
            }else{
                return json(['code' => 1, 'msg' => 'सफलतापूर्वक भेजा गया'])->send();//发送成功
            }
            
        } else {
            if($langer == 'ZH'){
                return json(['code' => 2, 'data' => [], 'msg' => '发送失败,请联系客服']);
            }elseif($langer == 'EN'){
                return json(['code' => 2, 'data' => [], 'msg' => 'Sending failed. Please check whether the SMS configuration is correct']);
            }else{
                return json(['code' => 1, 'msg' => 'भेजने में असफल'])->send();//发送失败
            }
            
        }
    }
    
}

