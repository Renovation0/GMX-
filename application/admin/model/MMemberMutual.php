<?php
namespace app\admin\model;

class MMemberMutual extends MCommon
{
    public $table = "zm_member_mutualaid";

    //获取订单列表
    public function getlists($where,$pageSize,$allParams){
        $list = $this->alias('a')->field('a.*,b.name,c.real_name as real_name_log')
        ->leftJoin('zm_mutualaid_list b','a.purchase_id=b.id')
        ->leftJoin('zm_real_name_log c','a.uid=c.u_id')
        //->leftJoin('zm_member_list c','a.buy_uid=c.id')
        ->where($where)
        ->order('a.id desc')
        ->paginate($pageSize, false, $allParams);
        return $list;
    }
    
    
    // 修改互助订单
    public function editMutualaidOrder($id, $data){
        // 执行修改
        if ($this->where('id', $id)->update($data)) {
            return json(['code' => 1, 'msg' => '修改成功']);
        } else {
            return json(['code' => 3, 'msg' => '修改失败', 'info' => $this->getlastsql()]);
        }
    }
    
}

