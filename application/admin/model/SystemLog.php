<?php


namespace app\admin\model;


use think\facade\Session;
use think\Model;

class SystemLog extends Model
{
    // 添加日志
    public function addLog($log)
    {
        return $this->insert([
            'u_id'  =>   Session::get('user.id'),
            'log'   =>   $log,
            'time'  =>   time(),
        ]);
    }

    // 查看日志
    public function getLogs($where, $pageSize, $allParams)
    {
        $logs =  $this->where($where)->order('id desc')->paginate($pageSize, false, $allParams);
        if(!empty($logs)){
            $u_ids = [];
            foreach($logs as $key => $log){
                $u_ids[] = $log['u_id'];
            }
            $systemUserModel = new SystemUser();
            $users = $systemUserModel->getUsers($u_ids);
            foreach($logs as $key => $log){
                $logs[$key]['username'] = $users[$log['u_id']];
            }
        }
        return $logs;
    }

    // 清除管理员数据
    public function clearUser($u_id)
    {
        return $this->where('u_id', $u_id)->delete();
    }

    // 删除日志（批量）
    public function deleteLog($ids)
    {
        if(empty($ids)){
            return json(['code' => 2, 'msg' => '没有数据']);
        }
        if($this->whereIn('id', $ids)->delete()){
            return json(['code' => 1, 'msg' => '删除成功']);
        }else{
            return json(['code' => 2, 'msg' => '删除失败']);
        }
    }
}