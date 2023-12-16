<?php
namespace app\admin\model;

use app\admin\model\MCommon;

class MMemberLevel extends MCommon
{
    public $table = 'zm_member_level';
    
    // 获取会员列表
    public function getLists($condition='', $field="*", $order="")
    {
        $list = $this->alias('a')
        ->join('zm_member_level b','a.level=b.id', 'LEFT')
        ->where($condition)
        ->order($order)->field($field)
        ->select();
        //->paginate($pageSize, false, $allParams);
        return $list;
    }
    
    //修改等级下的订单显示、隐藏
    public function order_hide($id,$sta){
        if($this->where('id', $id)->value('order_hide') == $sta){
            return json(['code' => 2, 'msg' => '操作失败,该状态已变更！']);
        }
        $result = $this->where('id', $id)->update(['order_hide'=>$sta]);
        if($result){
            return json(['code' => 1, 'msg' => '操作成功']);
        }else{
            return json(['code' => 2, 'msg' => '操作失败']);
        }
    }
    /*tree 0927*/
    //获取详情
    public function levelInfo($where,$field = '*'){
        return $this->where($where)->field($field)->find();
    }
    /*end*/
}

