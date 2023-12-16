<?php


namespace app\admin\model;


use think\Model;

class SystemSmsLog extends Model
{
    public function getlists($where,$pageSize,$allParams)
    {
        $list = $this->where($where)
            ->whereIn('status', [1, 2])
            ->order('id desc')
            ->paginate($pageSize, false, $allParams);

        return $list;
    }
}