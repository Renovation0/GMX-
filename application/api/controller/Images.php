<?php
namespace app\api\controller;

use think\captcha\Captcha;
use think\facade\Cache;
use think\App;

class Images extends Base
{

    public function generate(){       
        /* $Captcha = new Captcha();
        return $Captcha->entry(); */
        
        //随机码
       // $key = '2132123134523';//randstr();
        
        $key = $this->str_rand(); 
        /* var_dump($key);
        exit(); */
        
        $captcha = new Captcha();
        
        $data['imgkey'] = $key;
        $data['base64img'] = $captcha->entry($key);
        return json(['code'=>1,'msg'=>'success','data'=>$data]);
    }           
    
    function str_rand($length = 32, $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
       if(!is_int($length) || $length < 0) {
            return false;
       }
  
       $string = '';
       for($i = $length; $i > 0; $i--) {
            $string .= $char[mt_rand(0, strlen($char) - 1)];
       }

        return $string;
    }

                
}

