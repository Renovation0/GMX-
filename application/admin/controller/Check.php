<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/4/5
 * Time: 17:37
 */

namespace app\admin\controller;


use module\Redis;
use think\App;
use think\facade\Cache;
use think\facade\Session;
use think\facade\Request;

class Check extends Base
{
    protected $redis = NULL;
    public function __construct(App $app = null)
    {
        parent::__construct($app);
        // 判断登录
        if(!Session::has('id') || Cache::get('system_user_login_'.Session::get('id')) == false || Cache::get('system_user_login_'.Session::get('id')) != session_id()){
            Session::clear();
            if (Request::instance()->isAjax()){ // ajax请求
                json(['code' => 2, 'msg' => '请先登录！'])->send();exit();
            }else{ // 普通请求
                //$this->redirect('/admin/login/login');exit();
                $this->error('请先登录', '/witkey2022.php/login/login');exit();
            }
        }
        // 权限验证
        if(Session::get('user.id') != 1){ // 超级管理员不检查权限
            $module = Request::instance()->module();
            $controller = Request::instance()->controller();
            $action = Request::instance()->action();
            //$userController = "/".strtolower($module)."/".strtolower($controller);
            $userController = "/".strtolower($controller)."/".strtolower($action);
            if($userController != '/index/resource' && $userController != '/index/index' && $userController != '/index/welcome' && $userController != '/user/info' && $userController != '/user/logout' && $userController != '/wallet/tmtypecheck'){ // && $userController != '/user' 请求资源文件不从基本验证中验证权限resource                
            //if($userController != '/index/resource' && $userController != '/admin/index' && $userController != '/admin/user'){ // 请求资源文件不从基本验证中验证权限resource
                //$action = Request::instance()->action();
                //$userAction = $userController."/".strtolower($action);
                if(!in_array($userController, (array)session('power_action'))){
                    if (Request::instance()->isAjax()){ // ajax请求
                        json(['code' => 2, 'msg' => '无权限！'])->send();exit();
                    }else{ // 普通请求
                        $this->error('无权限');exit();
                    }
                }
            }
        }
        /* $Redis = new Redis();
        $redis = $Redis->redis();
        $this->redis = $redis; */
        // 更新缓存时间
        Cache::set('system_user_login_'.Session::get('id'), session_id(), 24 * 3600);
    }
    
    
}