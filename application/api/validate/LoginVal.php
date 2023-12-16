<?php
namespace app\api\validate;

use think\Validate;

class LoginVal extends Validate
{
    
    protected $rule =   [
        'phone'      => 'require|min:4|max:20',
        'password'   => 'require|min:6|max:20',
    ];
    
    protected $message  =   [
        'username.require'          => '登录名不能为空',
        'password.require'          => '密码不能为空',
        'password.min'              => '登录密码不能低于6个字符',
        'password.max'              => '登录密码不能超过20个字符',
    ];  
    
    
}

