<?php
namespace app\api\validate;

use think\Validate;

class TelVal extends Validate
{
    
    protected $rule =   [
/*         'pwd'   => 'requireWith:pwd|min:6|max:20',
        'withdraw_pwd'   => 'requireWith:withdraw_pwd|min:6|max:20', */
        'tel'     => 'requireWith:tel|mobile',
        'card_id'=>'idCard',
/*         'email'   => 'requireWith:email|email' */
    ];
    
    protected $message  =   [
/*         'pwd.min'               => '密码最小6个字符',
        'pwd.max'               => '密码最大20个字符',
        'withdraw_pwd.min'      => '密码最小6个字符',
        'withdraw_pwd.max'      => '密码最大20个字符',
        'real_name.min'         => '姓名最小2个字符',
        'sex'                   => '性别不能为空', */
        'tel'                   => '电话号码格式错误',
        'card_id.idCard'        => '身份证格式错误',
/*         'email'                 => '邮箱格式错误',
        'status'                => '用户状态错误',
        'province'              => '省不能为空',
        'city'                  => '市不能为空',
        'county'                => '区/县不能为空',
        'address'               => '地址不能为空',
        'card_id'               => '身份证号码错误',
        'card_photo'            => '身份证照片不能为空', */
    ];
    
/*     protected $rule =   [
        'real_name'=>'chs|max:50',
        'sex'=>'in:0,1,2',
        'tel'=>'mobile',
        'school'=>'max:50',
        'grade'=>'max:50',
        'class'=>'max:50',
        'province'=>'number',
        'city'=>'number',
        'county'=>'number',
        'address'=>'max:255',
        'cost_status'=>'in:1,2',
        'card_id'=>'idCard',
        'card_photo'=>'max:255',
        'member_status'=>'in:1,2',
        'device_id'=>'number'
    ];
    
    protected $message=[
        'real_name.chs'=>'姓名只能为汉字',
        'real_name.max'=>'超出姓名最大长度',
        'tel.mobile'=>'电话号码格式错误',
        'school.max'=>'超出学校最大长度',
        'grade.max'=>'超出年级最大长度',
        'class.max'=>'超出班级最大长度',
        'province.number'=>'省格式错误',
        'city.number'=>'市格式错误',
        'county.number'=>'区县格式错误',
        'address.max'=>'超出地址最大长度',
        'cost_status.in'=>'无效的计费状态',
        'card_id.idCard'=>'身份证格式错误',
        'card_photo.max'=>'照片超出最大长度',
        'member_status.in'=>'无效的会员状态',
        'device_id.number'=>'设备ID错误'
    ]; */
    
    
}

