<?php
namespace app\admin\model;
use think\Db;

class MMutualAidOrder extends MCommon
{
    public $table="zm_mutualaid_order";
          
        
    //获取订单列表
    public function getlists($where,$pageSize,$allParams){
        $list = $this->alias('a')->field('a.*,b.name,c.real_name as buy_real_name,d.real_name as sell_real_name')
        ->leftJoin('zm_mutualaid_list b','a.purchase_id=b.id')
        ->leftJoin('zm_real_name_log c','a.buy_uid=c.u_id')
        ->leftJoin('zm_real_name_log d','a.sell_uid=d.u_id')
        //->leftJoin('zm_member_list c','a.buy_uid=c.id')
        ->where($where)
        ->order('a.id desc')
        ->paginate($pageSize, false, $allParams);
        return $list;
    }
    
    
    // 修改互助订单
    public function editMutualaidOrder($id, $data, $p_id){
        // 执行修改
        if ($this->where('id', $id)->update($data)) {
			            
            $status = Db::name('member_mutualaid')->where(['id'=>$p_id])->value('status');
            if($status == 3){
                return json(['code' => 1, 'msg' => '修改成功']);
            }else{
                if(Db::name('member_mutualaid')->where(['id'=>$p_id])->update(['status'=>3])){
                    return json(['code' => 1, 'msg' => '修改成功']);
                }else{
                    return json(['code' => 2, 'msg' => '修改失败了']);
                }
            }
            
        } else {
            return json(['code' => 3, 'msg' => '修改失败', 'info' => $this->getlastsql()]);
        }
    }
    
    
    //获取订单列表
    public function getlistAllId($id){
        $list = $this->whereIn('id', $id)->where('status',0)->field('id,purchase_id,buy_uid,status,sell_uid')->select();
        //$list = $this->where('FIND_IN_SET(:id,id)',['id' => $id])->select();
        return $list;
    }
    
    
}

