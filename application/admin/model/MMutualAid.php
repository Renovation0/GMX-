<?php
namespace app\admin\model;

class MMutualAid extends MCommon
{
    public $table = "zm_mutualaid_list";
    
    
    //获取订单列表
    public function getlists($where,$order,$pageSize,$allParams){
        $list = $this->alias('a')->field('a.*,b.name as level_name')->leftJoin('zm_member_level b','a.level=b.id')
        //->leftJoin('zm_member_list c','a.buy_uid=c.id')
        ->where($where)
        ->order($order)
        ->paginate($pageSize, false, $allParams);
        return $list;
    }
    
    
    //添加
    public function addMutualAid($data){
        $res = $this->insert($data);
        if ($res){
            return true;
        }else{
            return false;
        }
    }    
    
    // 修改互助信息
    public function editMutualaid($id, $old_date, $data)
    {   
        $updateData = [];
        
        if ($data['name'] && $old_date['name'] != $data['name']) {
            $updateData['name'] = $data['name'];
        }
        if ($data['logo'] && $old_date['logo'] != $data['logo']) {
            $updateData['logo'] = $data['logo'];
        }
        if ($data['price'] && $old_date['price'] != $data['price']) {
            $updateData['price'] = $data['price'];
        }
        if ($data['rate'] && $old_date['rate'] != $data['rate']) {
            $updateData['rate'] = $data['rate'];
        }
        if ($data['days'] && $old_date['days'] != $data['days']) {
            $updateData['days'] =$data['days'];
        }
        if ($data['sort'] && $old_date['sort'] != $data['sort']) {
            $updateData['sort'] = $data['sort'];
        }
        if ($data['purchaseNum'] && $old_date['purchaseNum'] != $data['purchaseNum']) {
            $updateData['purchaseNum'] = $data['purchaseNum'];
        }
        if ($data['status'] && $old_date['status'] != $data['status']) {
            $updateData['status'] = $data['status'];
        }
        if ($data['level'] && $old_date['level'] != $data['level']) {
            $updateData['level'] = $data['level'];
        }
        if ($data['introduce'] && $old_date['introduce'] != $data['introduce']) {
            $updateData['introduce'] = $data['introduce'];
        }
        if ($data['zpurchaseNum'] && $old_date['zpurchaseNum'] != $data['zpurchaseNum']) {
            $updateData['zpurchaseNum'] = $data['zpurchaseNum'];
        }
        if ($data['issellpurchaseNum'] && $old_date['issellpurchaseNum'] != $data['issellpurchaseNum']) {
            $updateData['issellpurchaseNum'] = $data['issellpurchaseNum'];
        }
        
        if(empty($updateData)){
            return json(['code' => 1, 'msg' => '操作成功']);
        }

        // 执行修改
        if ($this->where('id', $id)->update($updateData)) {
            return json(['code' => 1, 'msg' => '修改成功']);
        } else {
            return json(['code' => 3, 'msg' => '修改失败', 'info' => $this->getlastsql()]);
        }
    }
    
    public function editStatus($id,$status){
        // 执行修改
        if ($this->where('id', $id)->update(['status'=>$status])) {
            return json(['code' => 1, 'msg' => '操作成功']);
        } else {
            return json(['code' => 3, 'msg' => '操作失败', 'info' => $this->getlastsql()]);
        }
    }
    
}

