<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// [ 应用入口文件 ]
namespace think;
// 加载基础文件
// $hthost = $_SERVER["HTTP_HOST"];
// $pipei_hthost = "/(zrfgadmin.xcqfjr.com|zrfgadmin.xcqfjr.com)/is"; 
// if(!preg_match($pipei_hthost, $hthost)){
// header('location: /404.html');die;
// }
require __DIR__ . '/thinkphp/base.php';
// 支持事先使用静态方法设置Request对象和Config对象

// 执行应用并响应
//Container::get('app')->run()->send();

//define('BIND_MODULE','admin');//绑定后台模块

Container::get('app')->bind('admin')->run()->send();
