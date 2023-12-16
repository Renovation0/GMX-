<?php

namespace app\admin\controller;

use think\Controller;
use app\admin\model\SystemUser;
use think\facade\Cache;
use think\Request;
use think\facade\Session;
use module\Redis;
use app\admin\model\SystemConfig;

class Login extends Base
{
    // 登录页面渲染
    public function login()
    {
        if(Session::has('id')){
            if(Cache::get('system_user_login_'.Session::get('id'))){
                //$this->success('您已经登录', 'admin/index/index');
                $this->redirect(url('Index/index'));
            }
        }

        $M_SystemConfig = new SystemConfig();
        $title = $M_SystemConfig->where(['key'=>'website'])->field('value')->find();
        $this->assign('log_title',$title['value']);
        return view();
    }

    // 执行登录（ajax）
    public function do_login(Request $request)
    {
        Session::clear(); // 清除之前记录
        $username = $request->param('username');
        $password = $request->param('password');
        $passwordMd5 = md5($password);
        if($username || $password){
            $ip = $this->getIp();
            $systemUserModel = new SystemUser();
            return $systemUserModel->doLogin($username, $passwordMd5, $ip);
        }else{
            return json(['code' => 2, 'msg' => '非法操作！']);
        }
    }
}