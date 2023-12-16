<?php
namespace app\api\model;

class MMemberLoginLog extends MCommon
{
    protected $table = 'zm_member_login_logs';
    
    // 添加尝试登陆记录
    public function addLog($id, $name, $res, $ip, $status)
    {
        return $this->insert([
            'user_id'            =>  $id,
            'user_name'          =>  $name,
            'login_res'           =>  $res,
            'login_ip'            =>  $ip,
            'login_time'          =>  time(),
            'login_status'        =>  $status
        ]);
    }
    
}

