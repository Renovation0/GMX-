<?php
namespace app\api\controller;

use think\Request;
use think\Db;
use app\api\validate\TelVal;
use think\facade\Cache;

header('Content-Type: text/html;charset=utf-8');
header('Access-Control-Allow-Origin:*'); // *代表允许任何网址请求
header('Access-Control-Allow-Methods:POST,GET,OPTIONS,DELETE'); // 允许请求的类型
header('Access-Control-Allow-Credentials: true'); // 设置是否允许发送 cookies
header('Access-Control-Allow-Headers: Content-Type,Content-Length,Accept-Encoding,X-Requested-with, Origin');

class Sms extends Base
{   
    //
    public function send(Request $request)
    {
        $phone = trim($request->get('phone'));

        $aaa = substr($phone, 0, 3);
        if (in_array($aaa, [170, 171, 165])) {
            return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => '手机格式不支持']);
        }
        
        $data['tel'] = $phone;
        
        $validate = new TelVal();
        if (!$validate->check($data)) {
            return json(['code' => 2, 'data' => [], 'msg' => $validate->getError()]);
        }

        $this->sendPostNew($phone);
    }
    
    //注册
    public function sendRegister(Request $request)
    {   
        $phone = trim($request->post('phone'));
        
        var_dump($phone); exit();
        $aaa = substr($phone, 0, 3);
        if (in_array($aaa, [170, 171, 165])) {
            return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => '手机格式不支持']);
        }
        $validate = new TelVal();
        $data['tel'] = $phone;
        if (!$validate->check($data)) {
            return json(['code' => 2, 'data' => [], 'msg' => $validate->getError()]);
        }
        $this->sendPostNew($phone, 2,'register');
    }
    
    //无需传手机号
/*     public function sendVer(Request $request)
    {
        $phone = $this->auth->mobile;
        if (!$phone) $this->error('参数错误');
        $this->sendPostNew($phone);
    } */
    
    //update by tree 22
    public function sendPostNew($mobile, $type = 2,$event = '')
    {
        if ($type == 2) {
            if (Cache::get('tel_'.$mobile) && $type == 2) {
                return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => '短信发送中']);
            }
            
            $last = Db::name('system_sms_log')->where('tel',$mobile)->order('id desc')->limit(1)->select();
            if ($last && time() - $last['createtime'] < 60 && $type == 2) {
                return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => '发送频繁']);
            }
        }
        Cache::set('tel_'.$mobile,$mobile,100);   
        
        $code = mt_rand(10000, 99999);
        $smsapi = "http://www.smsbao.com/"; //短信网关
        $user = "13798535403"; //短信平台帐号
        $pas = "zz123456";
        $pass = md5($pas); //短信平台密码
        $content = $type == 1 ? '尊敬的会员，您的预约已匹配成功，请及时处理。' : ($type == 3 ? '尊敬的会员，你的订单对方已上传凭证，请及时确认订单。' : "您的验证码是".$code);
        $phone = $mobile;
        $sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".$phone."&c=".urlencode($content);
        $result =file_get_contents($sendurl) ;
        
        $time = time();
        //$ip = request()->ip();
        if ($result == 0) {
            Db::name('system_sms_log')->insert(['tel'=>$mobile,'code'=>$code,'status'=>1,'type'=>$types,'time'=>$time]);
            return json(['code' => SUCCESS, 'data' => [], 'msg' => '发送成功']);

        } else {
            Db::name('system_sms_log')->insert(['tel'=>$mobile,'code'=>$code,'status'=>2,'type'=>$types,'time'=>$time]);
            return json(['code' => SIGN_ERROR, 'data' => [], 'msg' => '发送失败，请检查短信配置是否正确']);

        }
    }
    
    
    
}

