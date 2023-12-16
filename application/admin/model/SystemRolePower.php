<?php


namespace app\admin\model;


use think\Exception;
use think\Model;
use think\facade\Session;

class SystemRolePower extends Model
{
    // 执行读取权限
    public function readPower($role_ids)
    {
        if(in_array(1, $role_ids)){ // 超级管理员拥有所有权限
            $systemMenuModel = new SystemMenu();
            $powers = $systemMenuModel->getMenuIds();
        }else{
            $powers = $this->whereIn('role_id', $role_ids)->column('menu_id');
        }
        if(!empty($powers)){
            // 权限有效性验证
            $systemMenuModel = new SystemMenu();
            $active_powers_id = $systemMenuModel->readPower($powers, 'id');
            $active_powers_action = $systemMenuModel->readPower($powers, 'action');
            if(!empty($active_powers_id) && !empty($active_powers_action)){
                foreach ($active_powers_action as $k => $item){
                    $active_powers_action[$k] = strtolower($item);
                }
                $active_powers_action[] = '/index/index/index'; // 写入后台基础框架权限
                // 加载个人基础权限
                $active_powers_id[] = 1;
                $active_powers_id[] = 2;
                $active_powers_id[] = 3;
                $active_powers_id[] = 4;
                $active_powers_action[] = '/index/user/info';
                $active_powers_action[] = '/index/user/infoEditPost';
                $active_powers_action[] = '/index/user/logout';
                $active_powers_action[] = '/index/index/welcome';
                // 写入权限
                Session::set('power_id', $active_powers_id);
                Session::set('power_action', $active_powers_action);
            }else{ // 未找到有效的权限（对应菜单未关闭）
                return false;
            }
            return true;
        }else{ // 未找到权限
            return false;
        }
    }

    // 读取角色权限对应菜单id
    public function getPowerIds($role_id)
    {
        return $this->where('role_id', $role_id)->column('menu_id');
    }

    // 角色分配权限
    public function editRolePower($role_id, $powers)
    {
        $this->startTrans();
        try{
            $this->where('role_id', $role_id)->delete();
            if(!empty($powers)){
                foreach ($powers as $power){
                    $this->insert(['role_id' => $role_id, 'menu_id' => $power]);
                }
            }
            $this->commit();
            // 将受影响的管理员下线
            $systemUserRoleModel = new SystemUserRole();
            $systemUserRoleModel->userOfflineByRoleId($role_id);
            $systemLogModel = new SystemLog();
            $systemLogModel->addLog('分配角色权限[角色id: '.$role_id.']');
            return json(['code' => 1, 'msg' => '权限分配成功']);
        }catch (Exception $e){
            $this->rollback();
            return json(['code' => 3, 'msg' => '权限分配失败，请联系服务提供商']);
        }
    }

    // 删除角色相关权限
    public function deleteRolePower($role_id)
    {
        return $this->where('role_id', $role_id)->delete();
    }

    // 清除角色数据
    public function clearRole($role_id)
    {
        return $this->deleteRolePower($role_id);
    }
}