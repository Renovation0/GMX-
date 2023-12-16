<?php
namespace app\admin\controller;


use app\admin\model\SystemUser;
use think\Request;
use think\facade\Session;
use think\facade\Validate;

class User extends Check
{
    // 个人信息页渲染
    public function info()
    {
        $u_id =  Session::get('user.id'); // 管理员id
        if(!$u_id){
            $this->error('参数错误');
        }
        $systemUserModel = new SystemUser();
        $user = $systemUserModel->getUserInfo($u_id);
        $this->assign('user', $user);
        return view();
    }

    // 个人信息编辑提交
    public function infoEditPost(Request $request)
    {
        $id =  Session::get('user.id'); // 管理员id
        $username = $request->param('username', ''); // 管理员昵称
        $phone = $request->param('phone', ''); // 手机号
        $email = $request->param('email', ''); // 邮箱
        $password = $request->param('password', ''); // 密码
        $rpassword = $request->param('rpassword', ''); // 确认密码
        if(!$id){
            return json(['code' => 2, 'msg' => '参数错误']);

        }
        $rule =   [
            'username'  =>  'require|min:2|max:25',
            'phone'     =>  'require|mobile',
            'email'     =>  'email',
            'password'  =>  'min:6|max:16',
        ];
        $msg = [
            'username.require'  =>  '昵称为必填项',
            'username.min'      =>  '昵称不能少于2个字符',
            'username.max'      =>  '昵称不能超过25个字符',
            'phone.require'     =>  '手机号为必填项',
            'phone.mobile'      =>  '手机号格式不正确',
            'email.email'       =>  '邮箱格式不正确',
            'password.min'      =>  '密码长度不少于6位',
            'password.max'      =>  '密码长度不能超过于16位',
        ];
        $data = [
            'username'  =>  $username,
            'phone'     =>  $phone,
            'email'     =>  $email,
            'password'  =>  $password,
            'rpassword' =>  $rpassword,
        ];
        $validate   = Validate::make($rule, $msg);
        $result = $validate->check($data);
        if(!$result){
            return json(['code' => 2, 'msg' => $validate->getError()]);
        }else{
            if($rpassword != $password){
                return json(['code' => 2, 'msg' => '两次密码不一致']);
            }
            $systemUserModel = new SystemUser();
            return $systemUserModel->editUser($id, $data);
        }
    }

    // 退出登录
    public function logout()
    {
        Session::clear();
        //$this->redirect('/login/login');
        $this->success('退出成功', 'Login/login', '', 0);
    }
}