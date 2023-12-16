<?php

namespace app\admin\model;


use think\Exception;
use think\facade\Cache;
use think\Model;
use think\Db;
use think\facade\Session;

class SystemUser extends Model
{
    // 获取请求登录得管理员信息
    public function getLoginUser($username, $password)
    {
        return $this->where('username', $username)->where('password', $password)->find();
    }

    // 执行登录操作
    public function doLogin($username, $password, $ip)
    {
        $err_num = 10; // 锁账户密码错误次数
        $err_time = 3600; // 锁账户时间
        $user = $this->where('username', $username)->whereOr('phone', $username)->find();
        if($user){
            $frequency = 0;
            $systemLoginLogModel = new SystemLoginLog();
            if(Cache::get('system_user_login_num_'.$user['id'])){
                $frequency = Cache::get('system_user_login_num_'.$user['id']); // 此管理员已尝试登录次数
                Cache::set('system_user_login_num_'.$user['id'], ($frequency + 1), $err_time); // 添加此管理员尝试登录次数
                if($frequency >= 100){
                    return json(['code' => 2, 'msg' => '此用户密码连续错误'.$err_num.'次已禁用，请1小时后再试']);
                }
            }else{
                Cache::set('system_user_login_num_'.$user['id'], 1, $err_time); // 添加尝试登录次数（有效期1小时）
            }
            if($password != $user['password']){
                $systemLoginLogModel->addLog($user['id'], '密码错误', $ip, 0); // 写入尝试登录日志
                if($frequency == ($err_num - 1)){
                    return json(['code' => 2, 'msg' => '密码错误, 账户已被禁用1小时']);
                }else{
                    return json(['code' => 2, 'msg' => '密码错误, 您还有'.($err_num - ($frequency + 1)).'次机会']);
                }
            }
            if($user['status'] == 1){
                if($this->where('id', $user['id'])->update([
                    'login_time'    =>  time(),
                    'login_ip'      =>  $ip
                ])){
                    // 读取权限
                    $systemUserRoleModel = new SystemUserRole();
                    $role_ids = $systemUserRoleModel->getUserRoles($user['id']);
                    $systemRoleModel = new SystemRole();
                    if($systemRoleModel->readPower($role_ids)){
                        Session::set('id', $user['id']);
                        Session::set('user', $user);
                        Cache::set('system_user_login_'.$user['id'], session_id(), 24 * 3600); // 进行单点登录操作
                        $systemLoginLogModel->addLog($user['id'], '登录成功', $ip, 1); // 写入尝试登录日志
                        return json(['code' => 1, 'msg' => '登录成功']);
                    }else{
                        $systemLoginLogModel->addLog($user['id'], '登录失败', $ip, 0); // 写入尝试登录日志
                        return json(['code' => 1, 'msg' => '角色被禁用，您没有权限']);
                    }
                }else{
                    $systemLoginLogModel->addLog($user['id'], '登录失败', $ip, 0); // 写入尝试登录日志
                    return json(['code' => 2, 'msg' => '登录失败，请稍后再试']);
                }
            }else{
                if($user['status'] == 2){
                    return json(['code' => 2, 'msg' => '账户被禁用，请联系您的账户提提供者']);
                }elseif($user['status'] == 3){
                    return json(['code' => 2, 'msg' => '账户不存在']);
                }else{
                    return json(['code' => 2, 'msg' => '账户存在风险，被禁止登录']);
                }
            }
        }else{
            return json(['code' => 2, 'msg' => '账户不存在']);
        }
    }

    // 获取指定ids的管理员昵称
    public function getRoleUsersName($ids)
    {
        $users = $this->whereIn('id', $ids)->field('id, username')->select()->toArray();
        return $users;
    }

    // 获取管理员列表
    public function getLists($where, $pageSize, $allParams)
    {
        $users =  $this->where($where)
            ->whereIn('status', [1, 2])
            ->order('id desc')
            ->paginate($pageSize, false, $allParams);
        if(!empty($users)){
            $systemUserRoleModel = new SystemUserRole();
            foreach($users as $key => $user){
                $users[$key]['role'] = $systemUserRoleModel->getUserRole($user['id']);
            }
        }
        return $users;
    }

    // 添加管理员
    public function addUser($data)
    {
        // 验证昵称是否存在
        $status = $this->where('username', $data['username'])->value('status');
        if($status == 1 || $status == 2){
            return json(['code' => 2, 'msg' => '昵称'.$data['username'].'已存在']);
        }elseif($status == 3){
            return json(['code' => 2, 'msg' => '昵称为'.$data['username'].'的管理员账户在回收站中有记录，请先清除此账户或直接从回收站恢复']);
        }
        // 验证手机号是否存在
        $status = $this->where('phone', $data['phone'])->value('status');
        if($status == 1 || $status == 2){
            return json(['code' => 2, 'msg' => '手机号'.$data['phone'].'已被使用']);
        }elseif($status == 3){
            return json(['code' => 2, 'msg' => '手机号为'.$data['phone'].'的管理员账户在回收站中有记录，请先清除此账户或直接从回收站恢复']);
        }
        // 验证邮箱是否存在
        if($data['email'] != ''){
            $status = $this->where('email', $data['email'])->value('status');
            if($status == 1 || $status == 2){
                return json(['code' => 2, 'msg' => '邮箱'.$data['email'].'已被使用']);
            }elseif($status == 3){
                return json(['code' => 2, 'msg' => '邮箱为'.$data['email'].'的管理员账户在回收站中有记录，请先清除此账户或直接从回收站恢复']);
            }
        }
        // 验证角色是否存在
        if(!empty($data['role'])){
            $systemRoleModel = new SystemRole();
            $role_res = $systemRoleModel->haveId($data['role']);
            if($role_res !== true){
                return json(['code' => 2, 'msg' => '指定角色[id='.$role_res.']不存在']);
            }
        }
        // 执行添加
        $insertData = [
            'username'  =>  $data['username'],
            'phone'     =>  $data['phone'],
            'email'     =>  $data['email'],
            'password'  =>  md5($data['password']),
            'add_time'  =>  time(),
            'status'    =>  1,
            'member_id' =>  $data['member_id']
        ];
        $this->startTrans();
        try{
            $u_id = $this->insertGetId($insertData);
            if(!empty($data['role'])){
                $systemUserRoleMode = new SystemUserRole();
                $systemUserRoleMode->addUserRole($u_id, $data['role'], false);
            }
            $this->commit();
            $systemLogModel = new SystemLog();
            $systemLogModel->addLog('添加管理员[id: '.$u_id.']');
            return json(['code' => 1, 'msg' => '添加成功']);
        }catch (Exception $e){
            $this->rollback();
            return json(['code' => 3, 'msg' => '添加失败'.$e->getMessage()]);
        }
    }

    // 获取管理员信息
    public function getUserInfo($id)
    {
        $userInfo = $this->where('id', $id)->find()->toArray();
        if($userInfo){
            if(empty($userInfo['member_id'])){
                $userInfo['member_name'] = '';
            }else{
                $member_info = Db::name('member_list')->where(['id'=>$userInfo['member_id']])->field('tel')->find();
                $userInfo['member_name'] = $member_info['tel'];
            }
            $systemUserRoleModel = new SystemUserRole();
            $userInfo['role'] = $systemUserRoleModel->getUserRoles($id);
            return $userInfo;
        }else{
            return false;
        }
    }

    // 编辑角色
    public function editUser($id, $data)
    {
        // 验证昵称是否存在
        $status = $this->where('id', '<>', $id)->where('username', $data['username'])->value('status');
        if($status == 1 || $status == 2){
            return json(['code' => 2, 'msg' => '昵称'.$data['username'].'已存在']);
        }elseif($status == 3){
            return json(['code' => 2, 'msg' => '昵称为'.$data['username'].'的管理员账户在回收站中有记录，请先清除此账户或直接从回收站恢复']);
        }
        // 验证手机号是否存在
        $status = $this->where('id', '<>', $id)->where('phone', $data['phone'])->value('status');
        if($status == 1 || $status == 2){
            return json(['code' => 2, 'msg' => '手机号'.$data['phone'].'已被使用']);
        }elseif($status == 3){
            return json(['code' => 2, 'msg' => '手机号为'.$data['phone'].'的管理员账户在回收站中有记录，请先清除此账户或直接从回收站恢复']);
        }
        // 验证邮箱是否存在
        if($data['email'] != ''){
            $status = $this->where('id', '<>', $id)->where('email', $data['email'])->value('status');
            if($status == 1 || $status == 2){
                return json(['code' => 2, 'msg' => '邮箱'.$data['phone'].'已被使用']);
            }elseif($status == 3){
                return json(['code' => 2, 'msg' => '邮箱为'.$data['phone'].'的管理员账户在回收站中有记录，请先清除此账户或直接从回收站恢复']);
            }
        }
        // 验证角色是否存在
        if(isset($data['role']) && !empty($data['role'])){
            $systemRoleModel = new SystemRole();
            $role_res = $systemRoleModel->haveId($data['role']);
            if($role_res !== true){
                return json(['code' => 2, 'msg' => '指定角色[id='.$role_res.']不存在']);
            }
        }
        if(empty($data['member_id'])){
            $data['member_id'] = '';
        }
        $insertData = $data['password'] == '' ?  [
            'username'  =>  $data['username'],
            'phone'     =>  $data['phone'],
            'email'     =>  $data['email'],
            'member_id' =>  $data['member_id']
        ] : [
            'username'  =>  $data['username'],
            'phone'     =>  $data['phone'],
            'email'     =>  $data['email'],
            'member_id' =>  $data['member_id'],
            'password'  =>  md5($data['password']),
        ];
        // 执行修改
        $this->startTrans();
        try{
            $this->where('id', $id)->update($insertData);
            if(isset($data['role']) && !empty($data['role'])){
                $systemUserRoleMode = new SystemUserRole();
                $systemUserRoleMode->addUserRole($id, $data['role'], true);
            }
            $this->commit();
            // 将受影响的管理员下线
            $systemUserRoleModel = new SystemUserRole();
            $systemUserRoleModel->userOfflineByUid($id);
            $systemLogModel = new SystemLog();
            $systemLogModel->addLog('编辑管理员[id: '.$id.']');
            return json(['code' => 1, 'msg' => '编辑成功']);
        }catch (Exception $e){
            $this->rollback();
            return json(['code' => 3, 'msg' => '编辑失败', 'info' => $e->getMessage()]);
        }
    }

    // 修改管理员状态
    public function statusUser($id, $status)
    {
        if($this->where('id', $id)->count() > 0){
            if($this->where('id', $id)->value('status') == 3){
                return json(['code' => 2, 'msg' => '管理员已经被删除，操作失败']);
            }
            if($this->where('id', $id)->value('status') == $status){
                if($status == 1){
                    return json(['code' => 2, 'msg' => '管理员已经启用，操作失败']);
                }else{
                    return json(['code' => 2, 'msg' => '管理员已经停用，操作失败']);
                }
            }
            $this->startTrans();
            try{
                // 1. 修改管理员状态
                $this->where('id', $id)->setField('status', $status);
                // 将受影响的管理员下线
                $systemUserRoleModel = new SystemUserRole();
                $systemUserRoleModel->userOfflineByUid($id);
                $this->commit();
                if($status == 1){
                    $systemLogModel = new SystemLog();
                    $systemLogModel->addLog('启用管理员[id: '.$id.']');
                    return json(['code' => 1, 'msg' => '启用成功']);
                }else{
                    $systemLogModel = new SystemLog();
                    $systemLogModel->addLog('停用管理员[id: '.$id.']');
                    return json(['code' => 1, 'msg' => '停用成功']);
                }
            }catch(Exception $e){
                $this->rollback();
                if($status == 1){
                    return json(['code' => 3, 'msg' => '启用失败，请联系服务提供商']);
                }else{
                    return json(['code' => 3, 'msg' => '停用失败，请联系服务提供商']);
                }
            }
        }else{
            return json(['code' => 2, 'msg' => '无指定管理员，修改失败']);
        }
    }

    // 删除管理员
    public function deleteUser($id)
    {
        if($this->where('id', $id)->count() > 0){
            if($this->where('id', $id)->value('status') == 3){
                return json(['code' => 2, 'msg' => '管理员已经被删除，操作失败']);
            }
            $this->startTrans();
            try{
                // 1. 删除角色
                $this->where('id', $id)->setField('status', 3); // 状态标记为删除
                $this->commit();
                // 将受影响的管理员下线
                $systemUserRoleModel = new SystemUserRole();
                $systemUserRoleModel->userOfflineByUid($id);
                $systemLogModel = new SystemLog();
                $systemLogModel->addLog('删除管理员[id: '.$id.']');
                return json(['code' => 1, 'msg' => '删除成功']);
            }catch(Exception $e){
                $this->rollback();
                return json(['code' => 3, 'msg' => '删除失败，请联系服务提供商']);
            }
        }else{
            return json(['code' => 2, 'msg' => '无指定管理员，删除失败']);
        }
    }

    // 获取回收站中的管理员
    public function getRecycle($pageSize, $allParams)
    {
        return $this->where('status', 3)->field('id, username as name')->paginate($pageSize, false, $allParams);
    }

    // 从回收站恢复单项管理员
    public function recycleBack($id)
    {
        if($this->where('id', $id)->count() > 0){
            if($this->where('id', $id)->value('status') != 3){
                return json(['code' => 2, 'msg' => '管理员未被删除，操作失败']);
            }
            if($this->where('id', $id)->setField('status', 1)) {
                $systemLogModel = new SystemLog();
                $systemLogModel->addLog('从回收站恢复管理员[id: '.$id.']');
                return json(['code' => 1, 'msg' => '管理员[id: '.$id.']恢复成功']);
            }else{
                return json(['code' => 2, 'msg' => '管理员[id: '.$id.']恢复失败']);
            }
        }else{
            return json(['code' => 2, 'msg' => '无指定管理员，恢复失败']);
        }
    }

    // 从回收站清除单项管理员
    public function recycleClear($id)
    {
        if($this->where('id', $id)->count() > 0){
            if($this->where('id', $id)->value('status') != 3){
                return json(['code' => 2, 'msg' => '管理员未被删除，清除失败']);
            }
            $this->startTrans();
            try{
                // 1. 清除管理员表数据
                $this->where('id', $id)->delete();
                // 2. 清除管理员日志表数据
                $systemLogModel = new SystemLog();
                $systemLogModel->clearUser($id);
                // 2. 清除管理员登录日志表数据
                $systemLoginLogModel = new SystemLoginLog();
                $systemLoginLogModel->clearUser($id);
                // 3. 清除用户角色分配表数据
                $systemUserRoleModel = new SystemUserRole();
                $systemUserRoleModel->clearUser($id);
                $this->commit();
                $systemLogModel = new SystemLog();
                $systemLogModel->addLog('从回收站清除管理员[id: '.$id.']');
                return json(['code' => 1, 'msg' => '管理员[id: '.$id.']清除成功']);
            }catch(Exception $e){
                $this->rollback();
                return json(['code' => 3, 'msg' => '管理员[id: '.$id.']清除失败，请联系服务提供商', 'info' => $e->getMessage()]);
            }
        }else{
            return json(['code' => 2, 'msg' => '无指定管理员，操作失败']);
        }
    }

    // 获取指定用户(将id作为key)
    public function getUsers($ids)
    {
        $users =  $this->whereIn('id', $ids)->field('id, username')->select()->toArray();
        $res = [];
        if(!empty($users)){
            foreach ($users as $user){
                $res[$user['id']] = $user['username'];
            }
        }
        return $res;
    }
}