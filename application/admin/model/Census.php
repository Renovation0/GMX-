<?php


namespace app\admin\model;


use think\Db;
use think\Model;

class Census extends Model
{
    public function getCensusLists()
    {
        $list = DB::name('census_money')
            ->limit(0,4)
            ->order('id desc')
            ->select();
        return $list;
    }
}