<?php
namespace app\admin\model;

class MMachineManage extends MCommon
{
    public $table="zm_machine_manage";
    
    
    //添加引擎
    public function addMachine($thumbnail,$name,$price,$power,$hour_output,$cycle){
        $data = [
            'image' => $thumbnail,
            'name' => $name,
            'price' => $price,
            'power' => $power,
            'all_output' => $hour_output*$cycle,
            'rarning_rate' => $hour_output*$cycle*100/$price,
            'hour_output' => $hour_output,
            'cycle' => $cycle,
        ];
        $res = $this->insert($data);
        if ($res){
            return true;
        }else{
            return false;
        }
    }
    
    
    //修改矿机数据
    public function editMachine($condition,$thumbnail,$name,$price,$power,$hour_output,$cycle){
        $data = [
            'image' => $thumbnail,
            'name' => $name,
            'price' => $price,
            'power' => $power,
            'all_output' => $hour_output*$cycle,
            'rarning_rate' => $hour_output*$cycle*100/$price,
            'hour_output' => $hour_output,
            'cycle' => $cycle,
        ];
        $res = $this->where($condition)->update($data);
        if ($res){
            return true;
        }else{
            return false;
        }
    }
    
    
    //修改矿机状态
    public function renewalMachine($condition,$data){
        return $this->where($condition)->update($data);
    }
    
    
    
}

