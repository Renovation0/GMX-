<?php
namespace app\api\model;

use think\Db;

class DesignatedTransfer extends MCommon
{
    public $table= "zm_designated_transfer";
    
    //获取活动列表
    public function getPage($page_index=1, $page_size=10, $condition='',$order='', $field='*'){
        $offset         = ($page_index-1)*$page_size;
        $count          = Db::table($this->table)->where($condition)->count();
        $list = $this->alias('a')->field($field)->leftJoin('zm_mutualaid_list b','a.p_id=b.id')->where($condition)
        ->withAttr('time',function ($value,$data) {
            return $value?date("Y-m-d H:i:s",$value):'';
        })
        ->limit($offset,$page_size)->order($order)->select();

        $page_count     = ceil($count/$page_size);
        return array(
            'page'     => $page_index,
            'pages'     => $page_count,
            'count'     => $count,
            'list'      => $list,
        );

    }

    
    
    
}

