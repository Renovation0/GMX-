<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/20
 * Time: 13:30
 */

namespace app\admin\validate;


use think\Validate;

class AdminUser extends  Validate
{
    protected $rule =   [
        'username'  => 'require|min:2|max:25',
        'phone'   => 'require|phone',
        'email' => 'email',
        'password' => 'require|password',
        'rpassword' => 'require|rpassword',
    ];

    protected $message = [
        'username.require'  =>  '昵称为必填项',
        'username.min'  =>  '昵称不能少于2个字符',
        'username.max'  =>  '昵称不能超过25个字符',
        'phone.require'  =>  '手机号为必填项',
        'phone.phone'  =>  '手机号格式不正确',
        'email.email'  =>  '邮箱格式不正确',
    ];

    protected function password($value, $rule, $data = [])
    {

    }
}