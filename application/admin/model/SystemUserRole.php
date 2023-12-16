<?php

namespace app\admin\model;


use think\facade\Cache;
use think\Model;

class SystemUserRole extends Model
{
    // 获取管理员的角色
    public function getUserRoles($u_id)
    {
        return $this->where('u_id', $u_id)->column('role_id'); // 已启用的角色
    }


    // 获取角色授权的用户
    public function getRoleUsers($role_id)
    {
        $u_ids = $this->where('role_id', $role_id)->column('u_id'); // 角色授权的用户id
        if(!empty($u_ids)) {
            $systemUserModel = new SystemUser();
            return $systemUserModel->getRoleUsersName($u_ids);
        }else {
            return [];
        }
    }

    // 获取用户授权的角色
    public function getUserRole($user_id)
    {
        $role_ids = $this->where('u_id', $user_id)->column('role_id'); // 用户授权的角色id
        if(!empty($role_ids)) {
            $systemRoleModel = new SystemRole();
            return $systemRoleModel->getUserRolesName($role_ids);
        }else {
            return [];
        }
    }

    // 为用户分配角色
    public function addUserRole($u_id, $role_ids, $delete_old = false)
    {
        if($delete_old){
            $this->where('u_id', $u_id)->delete();
        }
        if(!empty($role_ids)){
            foreach ($role_ids as $role_id){
                $this->insert([
                    'u_id'      =>  $u_id,
                    'role_id'   =>  $role_id,
                ]);
            }
        }
    }

    // 将用户下线(根据角色id)
    public function userOfflineByRoleId($role_id)
    {
        // 查询属于此角色的管理员
        $u_ids = $this->where('role_id', $role_id)->column('u_id');
        // 对受影响的已登录的用户下线
        if(!empty($u_ids)){
            foreach ($u_ids as $u_id){
                if(Cache::get('system_user_login_'.$u_id)){
                    Cache::rm('system_user_login_'.$u_id);
                }
            }
        }
    }

    // 将用户下线(根据角色id)
    public function userOfflineByUid($u_id)
    {
        if(Cache::get('system_user_login_'.$u_id)){
            Cache::rm('system_user_login_'.$u_id);
        }
    }

    // 清除角色数据
    public function clearRole($role_id)
    {
        return $this->where('role_id', $role_id)->delete();
    }

    // 清除管理员数据
    public function clearUser($u_id)
    {
        return $this->where('u_id', $u_id)->delete();
    }

}