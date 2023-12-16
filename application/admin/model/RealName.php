<?php
namespace app\admin\model;

use think\Model;
use think\Db;

class RealName extends MCommon
{
    public $table="zm_real_name_log";
    
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
    
    
    // 修改实名申请状态
    public function updateStatus($id, $status)
    {
        if ($this->where('id', $id)->count() > 0) {
            $row = $this->getInfo(['id'=>$id]);

            if ($row['status'] == 1 ) {
                return json(['code' => 2, 'msg' => '此会员已通过实名，操作失败']);
            } elseif ($row['status'] == 3) {
                return json(['code' => 2, 'msg' => '此会员已被拒绝，操作失败']);
            }
            
            $where['real_name_status'] = $status;
            if($status == 1){
                $where['real_name_time'] = time();
                $where['real_name'] = $row['real_name'];
                $where['idcard'] = $row['id_card'];
                $where['cardImg1'] = $row['cardImg1'];
                $where['cardImg2'] = $row['cardImg2'];
                $where['urgent_mobile'] = $row['urgent_man'];
            }
            
            if ($this->where('id', $id)->update(['status' => $status,'deal_time' => time()])) {
                $res = Db::name('member_list')->where('id', $row['u_id'])->update($where);
                if($res){
                    return json(['code' => 1, 'msg' => '操作成功']);
                }else{
                    return json(['code' => 2, 'msg' => '操作失败']);
                }
            } else {
                return json(['code' => 2, 'msg' => '操作失败2']);
            }
        } else {
            return json(['code' => 2, 'msg' => '无指定信息，修改失败']);
        }
    }
    
}

