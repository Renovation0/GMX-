<?php
namespace app\admin\model;

class MMachineLog extends MCommon
{
    public $table="zm_machine_log";
    
    
    // 获取引擎记录列表
    public function getLists($where, $pageSize, $allParams)
    {
        $list = $this->where($where)
        ->order('id desc')
        ->paginate($pageSize, false, $allParams);
        
        $user_id = array_column($list->items(), 'u_id');
        $user_ids=implode(",", $user_id);
        $mac_id = array_column($list->items(), 'mac_id');
        $mac_ids = implode(",", $mac_id);
        $members=array();
        $macs = array();
        if($user_ids){
            $MMember = new MMember();
            //$user = DB::name('member_list')
            $user = $MMember->field('id,tel')->whereIn('id', $user_ids)->select();
            foreach($user as $key=>$val){
                $members[$val['id']]=$val;
            }
        }
        if ($mac_ids){
            $MMachineManage = new MMachineManage();
            //$mac = DB::name('machine_manage')
            $mac = $MMachineManage->field('id,name')->whereIn('id', $mac_ids)->select();
            foreach ($mac as $key => $val) {
                $macs[$val['id']] = $val;
            }
        }
        $resut	=array(
            'list'=>$list,
            'member'=>$members,
            'mac' => $macs
        );
        return $resut;
    }
    
    //添加记录
    public function addLog($data){
        return $this->insert($data);
    }
    
}

