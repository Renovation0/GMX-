<?php
namespace app\admin\model;

use think\Db;

class MGameDataBTC extends MCommon
{
    public $table="zm_game_data_btc";
    
    public function getLists($where, $pageSize, $allParams){
        $list = $this->where($where)->order('id desc')
        ->paginate($pageSize, false, $allParams);
        return $list;
    }

    public function getMemList($where, $pageSize, $allParams){
        $list = Db::name('game_btc')->alias('a')
            ->field('a.*,b.tel')
            ->join('zm_member_list b','a.u_id=b.id','left')
            ->where($where)->order('id desc')
            ->paginate($pageSize, false, $allParams);
        return $list;
    }
    
}

