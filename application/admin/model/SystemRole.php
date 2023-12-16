<?php


namespace app\admin\model;


use think\Exception;
use think\Model;

class SystemRole extends Model
{
    // 读取权限
    public function readPower($role_ids)
    {
        $active_roles = $this->whereIn('id', $role_ids)->where('status', 1)->column('id');
        if(!empty($active_roles)){
            // 执行读取权限
            $systemRolePowerModel = new SystemRolePower();
            return $systemRolePowerModel->readPower($active_roles);
        }
        return false;
    }

    // 获取角色列表
    public function getLists($where, $pageSize, $allParams)
    {
        $roles =  $this->where($where)
            ->whereIn('status', [1, 2])
            ->order('id desc')
            ->paginate($pageSize, false, $allParams);
        $systemUserRoleModel = new SystemUserRole();
        foreach($roles as $key => $role){
            $roles[$key]['users'] = $systemUserRoleModel->getRoleUsers($role['id']);
        }
        return $roles;
    }

    // 添加角色
    public function addRole($name, $message)
    {
        if($this->where('name', $name)->whereIn('status', [1, 2])->count() > 0){
            return json(['code' => 2, 'msg' => '已存在相同的角色名“'.$name.'”，添加失败']);
        }else{
            $id = $this->where('name', $name)->where('status', 3)->value('id'); // 已删除的此角色名的id
            if($id){ // 如果有被删除的记录
                return json(['code' => 2, 'msg' => '角色名“'.$name.'”在回收站记录中，修改失败']);
            }else{
                $res = $this->insertGetId(['name' => $name, 'message' => $message]);
                $id = $res;
            }
            if($res){
                $systemLogModel = new SystemLog();
                $systemLogModel->addLog('添加角色[id: '.$id.']');
                return json(['code' => 1, 'msg' => '添加成功']);
            }else{
                return json(['code' => 2, 'msg' => '添加失败']);
            }
        }
    }

    // 获取角色信息
    public function getRoleInfo($id)
    {
        return $this->where('id', $id)->find()->toArray();
    }

    // 编辑角色
    public function editRole($id, $name, $message)
    {
        if($this->where('id', $id)->count() > 0){
            if($this->where('id', $id)->value('status') == 3){
                return json(['code' => 2, 'msg' => '角色已被删除，禁止修改，请刷新页面']);
            }else{
                if($this->where('name', $name)->where('id', '<>', $id)->whereIn('status', [1, 2])->count() > 0){
                    return json(['code' => 2, 'msg' => '已存在相同的角色名“'.$name.'”，修改失败']);
                }else{
                    if($this->where('name', $name)->where('id', '<>', $id)->where('status', 3)->count() > 0){
                        return json(['code' => 2, 'msg' => '角色名“'.$name.'”在回收站记录中，修改失败']);
                    }else{
                        $res = $this->where('id', $id)->update(['name' => $name, 'message' => $message]); // 执行修改
                        if($res){
                            $systemLogModel = new SystemLog();
                            $systemLogModel->addLog('编辑角色[id: '.$id.']');
                            return json(['code' => 1, 'msg' => '修改成功']);
                        }else{
                            return json(['code' => 2, 'msg' => '修改失败，可能是数据未发生改变']);
                        }
                    }
                }
            }
        }else{
            return json(['code' => 2, 'msg' => '无指定角色，修改失败']);
        }
    }

    // 修改角色状态
    public function statusRole($id, $status)
    {
        if($this->where('id', $id)->count() > 0){
            if($this->where('id', $id)->value('status') == 3){
                return json(['code' => 2, 'msg' => '角色已经被删除，操作失败']);
            }
            if($this->where('id', $id)->value('status') == $status){
                if($status == 1){
                    return json(['code' => 2, 'msg' => '角色已经启用，操作失败']);
                }else{
                    return json(['code' => 2, 'msg' => '角色已经停用，操作失败']);
                }
            }
            $this->startTrans();
            try{
                // 1. 修改角色状态
                $this->where('id', $id)->setField('status', $status);
                // 将受影响的管理员下线
                $systemUserRoleModel = new SystemUserRole();
                $systemUserRoleModel->userOfflineByRoleId($id);
                $this->commit();
                if($status == 1){
                    $systemLogModel = new SystemLog();
                    $systemLogModel->addLog('启用角色[id: '.$id.']');
                    return json(['code' => 1, 'msg' => '启用成功']);
                }else{
                    $systemLogModel = new SystemLog();
                    $systemLogModel->addLog('停用角色[id: '.$id.']');
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
            return json(['code' => 2, 'msg' => '无指定角色，修改失败']);
        }
    }

    // 删除角色
    public function deleteRole($id)
    {
        if($this->where('id', $id)->count() > 0){
            if($this->where('id', $id)->value('status') == 3){
                return json(['code' => 2, 'msg' => '角色已经被删除，操作失败']);
            }
            $this->startTrans();
            try{
                // 1. 删除角色
                $this->where('id', $id)->setField('status', 3); // 状态标记为删除
                $this->commit();
                // 将受影响的管理员下线
                $systemUserRoleModel = new SystemUserRole();
                $systemUserRoleModel->userOfflineByRoleId($id);
                $systemLogModel = new SystemLog();
                $systemLogModel->addLog('删除角色[id: '.$id.']');
                return json(['code' => 1, 'msg' => '删除成功']);
            }catch(Exception $e){
                $this->rollback();
                return json(['code' => 3, 'msg' => '删除失败，请联系服务提供商']);
            }
        }else{
            return json(['code' => 2, 'msg' => '无指定角色，删除失败']);
        }
    }

    // 获取指定ids的角色名(未被删除的)
    public function getUserRolesName($ids)
    {
        return $roles = $this->whereIn('id', $ids)->whereIn('status', [1, 2])->field('id, name')->select()->toArray();
    }

    // 获取可用角色列表
    public function getRoles()
    {
        return $this->where('status', 1)->field('id, name')->select()->toArray();
    }

    // 判断指定ids的角色是否存在
    public function haveId($ids)
    {
        if(!empty($ids)){
            foreach ($ids as $id){
                if($this->where('id', $id)->whereIn('status', [1, 2])->count() == 0){
                    return $id;
                }
            }
            return true;
        }else{
            return true;
        }
    }

    // 获取回收站中的角色
    public function getRecycle($pageSize, $allParams)
    {
        return $this->where('status', 3)->field('id, name')->paginate($pageSize, false, $allParams);
    }

    // 从回收站恢复单项角色
    public function recycleBack($id)
    {
        if($this->where('id', $id)->count() > 0){
            if($this->where('id', $id)->value('status') != 3){
                return json(['code' => 2, 'msg' => '角色未被删除，恢复失败']);
            }
            if($this->where('id', $id)->setField('status', 1)) {
                $systemLogModel = new SystemLog();
                $systemLogModel->addLog('从回收站恢复角色[id: '.$id.']');
                return json(['code' => 1, 'msg' => '角色[id: '.$id.']恢复成功']);
            }else{
                return json(['code' => 2, 'msg' => '角色[id: '.$id.']恢复失败']);
            }
        }else{
            return json(['code' => 2, 'msg' => '无指定角色，操作失败']);
        }
    }

    // 从回收站清除单项角色
    public function recycleClear($id)
    {
        if($this->where('id', $id)->count() > 0){
            if($this->where('id', $id)->value('status') != 3){
                return json(['code' => 2, 'msg' => '角色未被删除，清除失败']);
            }
            $this->startTrans();
            try{
                // 1. 清除角色表数据
                $this->where('id', $id)->delete();
                // 2. 清除角色权限表数据
                $systemRolePowerModel = new SystemRolePower();
                $systemRolePowerModel->clearRole($id);
                // 3. 清除用户角色分配表数据
                $systemUserRoleModel = new SystemUserRole();
                $systemUserRoleModel->clearRole($id);
                $this->commit();
                $systemLogModel = new SystemLog();
                $systemLogModel->addLog('从回收站清除角色[id: '.$id.']');
                return json(['code' => 1, 'msg' => '角色[id: '.$id.']清除成功']);
            }catch(Exception $e){
                $this->rollback();
                return json(['code' => 3, 'msg' => '角色[id: '.$id.']清除失败，请联系服务提供商']);
            }
        }else{
            return json(['code' => 2, 'msg' => '无指定角色，操作失败']);
        }
    }
}