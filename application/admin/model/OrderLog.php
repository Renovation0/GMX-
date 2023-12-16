<?php


namespace app\admin\model;


use think\Db;
use think\Model;

class OrderLog extends Model
{
    // 获取订单记录表
    public function getLists($where, $pageSize, $allParams,$orders)
    {
        $list = $this->alias('order')
            ->where($where)
            ->order($orders)
            ->paginate($pageSize, false, $allParams);

        $u_id = array_column($list->items(), 'u_id');
        $user_ids = implode(",", $u_id);
        $order_id = array_column($list->items(), 'order_id');
        $order_ids = implode(",", $order_id);
        $members = array();
        $orderNo = array();
        if ($user_ids) {

            $user = DB::name('member_list')
                ->field('id,tel')
                ->whereIn('id', $user_ids)
                ->select();
            foreach ($user as $key => $val) {
                $members[$val['id']] = $val;
            }

        }
        if ($order_ids) {
            $order = DB::name('order_list')
                ->field('id,orderNo')
                ->whereIn('id', $order_ids)
                ->select();
            foreach ($order as $key => $val) {
                $orderNo[$val['id']] = $val;
            }

        }
        $resut = array(
            'list' => $list,
            'member' => $members,
            'orderno' => $orderNo
        );
        return $resut;
    }
}