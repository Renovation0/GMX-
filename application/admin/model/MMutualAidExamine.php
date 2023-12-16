<?php
namespace app\admin\model;

class MMutualAidExamine extends MCommon
{
    public $table = "zm_mutualaid_examine";
    
    
    //获取订单列表
    public function getlists($where,$pageSize,$allParams){
        $list = $this->alias('a')
        //->leftJoin('zm_mutualaid_order c','a.order_id=c.id')
        ->leftJoin('zm_member_list d','a.uid=d.id')
        ->leftJoin('zm_task b','a.p_id=b.id')
        ->where($where)
        ->order('a.id desc')
        ->field('a.*,b.task_name,b.jl_num as price,d.tel,d.yx_team,d.zt_yx_num')
        ->paginate($pageSize, false, $allParams);
        return $list;
    }

    
    
}

