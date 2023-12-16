<?php
namespace app\admin\model;
use think\Db;

class MMutualAidLog extends MCommon
{
    public $table = "zm_mutualaid_log";
    
    
    //获取订单列表
    public function getlists($where,$pageSize,$allParams){
        $list = $this->alias('a')->field('a.*,b.name,c.real_name as real_name_log')
        ->leftJoin('zm_mutualaid_list b','a.p_id=b.id')
        ->leftJoin('zm_real_name_log c','a.uid=c.u_id')
        //->leftJoin('zm_member_list c','a.buy_uid=c.id')
        ->where($where)
        ->order('a.id desc')
        ->paginate($pageSize, false, $allParams);
        return $list;
    }
    
    //获取订单列表
    public function getlistMembers($where,$pageSize,$allParams){
        $list = $this->alias('a')->field('a.*,b.fail_num,b.last_ip,c.name,b.f_tel,d.real_name,e.real_name as f_real_name')
        ->leftJoin('zm_member_list b','a.uid=b.id')
        ->leftJoin('zm_mutualaid_list c','a.p_id=c.id')
        ->leftJoin('zm_real_name_log d','a.uid=d.u_id')
        ->leftJoin('zm_real_name_log e','b.f_uid=e.u_id')
        ->where($where)
        ->whereTime('a.time','today')
        ->order('a.id desc')
        ->paginate($pageSize, false, $allParams);
        
        foreach ($list as $k => $v){
            $total_asset = Db::name('member_mutualaid')->where('uid', $v['uid'])->where('compose_status in (0,2) and status in (1,2,3) and is_exist = 1')->sum('new_price');
            $list[$k]['total_asset'] = $total_asset;
            $list[$k]['total_count'] = Db::name('mutualaid_log')->where('status = 2 AND uid = '.$v['uid'].' AND p_id = '.$v['p_id'])->count();
        }
        
        return $list;
    }
    
    
}

