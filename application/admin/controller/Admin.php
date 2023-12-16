<?php

namespace app\admin\controller;


use app\admin\model\SystemMenu;
use app\admin\model\SystemRole;
use app\admin\model\SystemRolePower;
use app\admin\model\SystemUser;
use think\facade\Validate;
use think\Request;
use app\admin\model\MMember;

class Admin extends Check
{
    // 角色列表页面渲染
    public function roleList(Request $request)
    {
        $status = intval($request->param('status', -1)); // 状态
        $name = $request->param('name', ''); // 角色名
        $allParams = ['query' => $request->param()];
        $this->assign('param_status', $status);
        $this->assign('param_name', $name);
        $pageSize = 20; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if($status != -1){
            $where .= ' and `status` = '.$status;
        }
        if($name != ''){
            $where .= ' and `name` like \'%'.$name.'%\'';
        }
        // 获取列表
        $systemRoleModel = new SystemRole();
        $roleLists = $systemRoleModel->getLists($where, $pageSize, $allParams);
        $this->assign('list', $roleLists);
        return view();
    }

    // 添加角色页面渲染
    public function roleAdd()
    {
        return view();
    }

    // 添加角色提交
    public function roleAddPost(Request $request)
    {
        $name = $request->param('name', ''); // 角色名
        $message = $request->param('message', ''); // 角色名
        if($name == ''){
            return json(['code' => 2, 'msg' => '角色名不能为空']);
        }
        $systemRoleModel = new SystemRole();
        return $systemRoleModel->addRole($name, $message);
    }

    // 角色权限分配页渲染
    public function rolePower(Request $request)
    {
        $id = intval($request->param('id', 0)); // 角色id
        if($id == 1){
            $this->error('超级管理员的权限不能编辑');
        }
        $systemMenuModel = new SystemMenu();
        $nodes = $systemMenuModel->getNodes();
        if(!$nodes){
            $this->error('未读取到任何系统权限节点');die();
        }
        $systemRolePowerModel = new SystemRolePower();
        $power_ids = $systemRolePowerModel->getPowerIds($id);
        
        // 自己拼接json
        $str = "[";
        if(!empty($power_ids)){
            foreach ($power_ids as $k => $power_id){
                $str .= intval($power_id).",";
            }
            $str = substr($str,0,strlen($str)-1);
        }
        $str .= "]";
        $this->assign('id', $id);
        $this->assign('nodes', $nodes);
        $this->assign('power_ids', $str);
        return view();
    }

    // 角色权限提交
    public function rolePowerPost(Request $request)
    {
        $id = intval($request->param('id', 0)); // 角色id
        $poser = $request->param('power/a', 0);
        if($id == 1){
            return json(['code' => 2, 'msg' => '超级管理员的权限不能编辑']);
        }
        $systemRolePowerModel = new SystemRolePower();
        return $systemRolePowerModel->editRolePower($id, $poser);
    }

    // 编辑角色页面渲染
    public function roleEdit(Request $request)
    {
        $id = intval($request->param('id', 0)); // 角色id
        if($id == 1){
            $this->error('不能编辑超级管理员');
        }
        $systemRoleModel = new SystemRole();
        $role_info = $systemRoleModel->getRoleInfo($id);
        $this->assign('role_info', $role_info);
        return view();
    }

    // 编辑角色提交
    public function roleEditPost(Request $request)
    {
        $id = intval($request->param('id', '')); // 角色id
        $name = $request->param('name', ''); // 角色名
        $message = $request->param('message', ''); // 角色名
        if($id == 1){
            return json(['code' => 2, 'msg' => '不能编辑超级管理员']);
        }
        if($name == ''){
            return json(['code' => 2, 'msg' => '角色名不能为空']);
        }
        $systemRoleModel = new SystemRole();
        return $systemRoleModel->editRole($id, $name, $message);
    }

    // 修改角色状态
    public function roleStatus(Request $request)
    {
        $id = intval($request->param('id', 0)); // 角色id
        $status = intval($request->param('status', 0)); // 状态
        if($id == 0){
            return json(['code' => 2, 'msg' => '未指定角色']);
        }
        if($id == 1){
            return json(['code' => 2, 'msg' => '不能修改超级管理员状态']);
        }
        if($status != 1 && $status != 2){
            return json(['code' => 2, 'msg' => '错误的指定状态']);
        }
        $systemRoleModel = new SystemRole();
        return $systemRoleModel->statusRole($id, $status);

    }

    // 删除角色
    public function roleDelete(Request $request)
    {
        $id = intval($request->param('id', 0)); // 角色id
        if($id == 0){
            return json(['code' => 2, 'msg' => '未指定角色']);
        }
        if($id == 1){
            return json(['code' => 2, 'msg' => '不能删除超级管理员']);
        }
        $systemRoleModel = new SystemRole();
        return $systemRoleModel->deleteRole($id);
    }

    // 管理员列表页面渲染
    public function userList(Request $request)
    {
        $status = intval($request->param('status', 0)); // 状态
        $username = $request->param('username', ''); // 管理员名
        $allParams = ['query' => $request->param()];
        $this->assign('param_status', $status);
        $this->assign('param_username', $username);
        $pageSize = 20; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if($status != 0){
            $where .= ' and `status` = '.$status;
        }
        if($username != ''){
            $where .= ' and `username` like \'%'.$username.'%\'';
        }
        // 获取管理员列表
        $systemUserModel = new SystemUser();
        $userLists = $systemUserModel->getLists($where, $pageSize, $allParams);
        $this->assign('list', $userLists);
        return view();
    }

    // 添加管理员
    public function userAdd()
    {
        // 获取可用的角色列表
        $systemRoleModel = new SystemRole();
        $roleList = $systemRoleModel->getRoles();
        $this->assign('roles', $roleList);
        return view();
    }

    // 添加管理员提交
    public function userAddPost(Request $request)
    {
        $username = $request->param('username', ''); // 管理员昵称
        $phone = $request->param('phone', ''); // 手机号
        $email = $request->param('email', ''); // 邮箱
        $role = $request->param('role/a'); // 角色分配
        $password = $request->param('password', ''); // 密码
        $rpassword = $request->param('rpassword', ''); // 确认密码
        $member_name = $request->param('member_name', ''); //绑定ID
        if(in_array(1, $role)){
            return json(['code' => 2, 'msg' => '不能指定角色为超级管理员']);
        }
        $rule =   [
            'username'  =>  'require|min:2|max:25',
            'phone'     =>  'require|mobile',
            'email'     =>  'email',
            'role'      =>  'array',
            'password'  =>  'require|min:6|max:16',
            'rpassword' => 'require',
        ];
        $msg = [
            'username.require'  =>  '昵称为必填项',
            'username.min'      =>  '昵称不能少于2个字符',
            'username.max'      =>  '昵称不能超过25个字符',
            'phone.require'     =>  '手机号为必填项',
            'phone.mobile'      =>  '手机号格式不正确',
            'email.email'       =>  '邮箱格式不正确',
            'role.array'        =>  '权限选择不正确',
            'password.require'  =>  '密码为必填',
            'password.min'      =>  '密码长度不少于6位',
            'password.max'      =>  '密码长度不能超过于16位',
            'rpassword.require' =>  '确认密码为必填',
        ];
        
        if(!empty($member_name)){
            $MMember = new MMember();
            $member_info = $MMember->getInfo(['tel'=>$member_name],'id');
            $member_name = $member_info['id'];
        }
        
        $data = [
            'username'  =>  $username,
            'phone'     =>  $phone,
            'email'     =>  $email,
            'role'      =>  $role,
            'password'  =>  $password,
            'rpassword' =>  $rpassword,
            'member_id' =>  $member_name
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
            return $systemUserModel->addUser($data);
        }
    }

    // 管理员编辑页渲染
    public function userEdit(Request $request)
    {
        $u_id = intval($request->param('id', 0)); // 管理员id
        if($u_id == 0){
            $this->error('参数错误');
        }
        $this->assign('id', $u_id);
        $systemUserModel = new SystemUser();
        $user = $systemUserModel->getUserInfo($u_id);
        $this->assign('user', $user);
        $systemRoleModel = new SystemRole();
        $roleList = $systemRoleModel->getRoles();
        $this->assign('roles', $roleList);
        return view();
    }

    // 编辑管理员提交
    public function userEditPost(Request $request)
    {
        $id = intval($request->param('id', 0)); // 管理员id
        $username = $request->param('username', ''); // 管理员昵称
        $phone = $request->param('phone', ''); // 手机号
        $email = $request->param('email', ''); // 邮箱
        $role = $request->param('role/a'); // 角色分配
        $password = $request->param('password', ''); // 密码
        $rpassword = $request->param('rpassword', ''); // 确认密码
        $member_name = $request->param('member_name', ''); //绑定ID
        if($id == 0){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        if(empty($role)){
            return json(['code' => 2, 'msg' => '角色不能为空']);
        }
        if(in_array(1, $role)){
            return json(['code' => 2, 'msg' => '不能指定角色为超级管理员']);
        }
        $rule =   [
            'username'  =>  'require|min:2|max:25',
            'phone'     =>  'require|mobile',
            'email'     =>  'email',
            'role'      =>  'array',
            'password'  =>  'min:6|max:16',
        ];
        $msg = [
            'username.require'  =>  '昵称为必填项',
            'username.min'      =>  '昵称不能少于2个字符',
            'username.max'      =>  '昵称不能超过25个字符',
            'phone.require'     =>  '手机号为必填项',
            'phone.mobile'      =>  '手机号格式不正确',
            'email.email'       =>  '邮箱格式不正确',
            'role.array'        =>  '权限选择不正确',
            'password.min'      =>  '密码长度不少于6位',
            'password.max'      =>  '密码长度不能超过于16位',
        ];
        
        if(!empty($member_name)){
            $MMember = new MMember();
            $member_info = $MMember->getInfo(['tel'=>$member_name],'id');
            $member_name = $member_info['id'];
        }
        
        $data = [
            'username'  =>  $username,
            'phone'     =>  $phone,
            'email'     =>  $email,
            'role'      =>  $role,
            'password'  =>  $password,
            'rpassword' =>  $rpassword,
            'member_id' =>  $member_name
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

    // 修改管理员状态
    public function userStatus(Request $request)
    {
        $id = intval($request->param('id', 0)); // 管理员id
        $status = intval($request->param('status', 0)); // 状态
        if($id == 0){
            return json(['code' => 2, 'msg' => '未指定管理员']);
        }
        if($id == 1){
            return json(['code' => 2, 'msg' => '不能修改admin状态']);
        }
        if($status != 1 && $status != 2){
            return json(['code' => 2, 'msg' => '错误的指定状态']);
        }
        $systemUserModel = new SystemUser();
        return $systemUserModel->statusUser($id, $status);
    }

    // 删除管理员
    public function userDelete(Request $request)
    {
        $id = intval($request->param('id', 0)); // 管理员id
        if($id == 0){
            return json(['code' => 2, 'msg' => '未指管理员']);
        }
        if($id == 1){
            return json(['code' => 2, 'msg' => '不能删除admin']);
        }
        $systemUserModel = new SystemUser();
        return $systemUserModel->deleteUser($id);
    }
    
    
    // 菜单列表页面渲染
    public function menuList(Request $request)
    {   

        $systemMenuModel = new SystemMenu();
        $menus = $systemMenuModel->getAllMenus();
        $this->assign('list', $menus);
        
        return view();
    }
    // 添加菜单
    public function menuAdd(Request $request)
    {   
        $systemMenuModel = new SystemMenu();
        
        if ($request->isAjax()) {
            $p_id = intval($request->param('p_id', 0));
            $action = $request->param('action', '');
            $name = $request->param('name', '');
            if($p_id == 0){
                $type = 1;
            }else{
                $abc=substr($p_id,-4);
                if($abc == '0000'){
                    $type = 2;
                }else{
                    $type = 3;
                }
            };

            if ($systemMenuModel->insert([
                'p_id' => $p_id,
                'name' => $name,
                'action' => $action,
                'type' => $type,
            ])) {
                return json(['code' => 1, 'msg' => '新增成功']);
            } else {
                return json(['code' => 2, 'msg' => '新增失败']);
            }
        }
        
        $list = $systemMenuModel->getAllMenus();
        foreach ($list as $k => $v){
            if($v['type'] == 2){
                $list[$k]['name'] = '--'.$v['name'];
            }elseif($v['type'] == 3){
                unset($list[$k]);
            }
        }
        $this->assign('list', $list);
        return view();
    }
    
    // 修改菜单状态
    public function menuStatus(Request $request)
    {
        $id = intval($request->param('id', 0)); // id
        $status = intval($request->param('status', 0)); // 状态
        if ($id == 0) {
            return json(['code' => 2, 'msg' => '未指定菜单']);
        }
        if ($status != 1 && $status != 2) {
            return json(['code' => 2, 'msg' => '错误的指定状态']);
        }
        $systemMenuModel = new SystemMenu();
        return $systemMenuModel->statusMenu($id, $status);
        
    }
    
    
    
}