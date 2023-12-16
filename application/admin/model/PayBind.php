<?php
namespace app\admin\model;

class PayBind extends MCommon
{
    public $table="zm_paymant_binding";
    
    // 获取会员列表
    public function getLists($where, $pageSize, $allParams)
    {
        $list = $this->where($where)
        ->order('id desc')
        ->paginate($pageSize, false, $allParams);
        
        $user_id = array_column($list->items(), 'u_id');
        $user_ids=implode(",", $user_id);
        $members=array();
        if($user_ids){
            $MMember = new MMember();
            $user = $MMember
            ->field('id,tel,user')
            ->whereIn('id', $user_ids)
            ->select();
            foreach($user as $key=>$val){
                $members[$val['id']]=$val;
                $members[$val['user']]=$val['user'];
            }
            
        }
        $resut	=array(
            'list'=>$list,
            'member'=>$members,
        );
        return $resut;
    }
    
    
    
    
    
}

