<?php
namespace app\api\validate;

use think\Validate;

class PassVal extends Validate
{
    
    
    protected $rule =   [
        'pwd'   => 'requireWith:pwd|min:6|max:20',
        'withdraw_pwd'   => 'requireWith:withdraw_pwd|min:6|max:20'
    ];
    
    protected $message  =   [
     'pwd.min'               => '密码最小6个字符',
     'pwd.max'               => '密码最大20个字符',
     'withdraw_pwd.min'      => '支付密码最小6个字符',
     'withdraw_pwd.max'      => '支付密码最大20个字符'
    ];
    
}

