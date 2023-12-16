<?php
namespace app\admin\model;

use think\Db;
use think\facade\Cache;
use think\Exception;

class OrderCoin extends MCommon
{
    public $table = "zm_order_coin";
    
    public function getLists($where, $pageSize, $allParams,$orders){
        $list = $this->alias('order')
        ->where($where)
        ->order($orders)
        ->paginate($pageSize, false, $allParams);
        $buy_id = array_column($list->items(), 'buy_uid');
        $sell_id = array_column($list->items(), 'sell_uid');
        //合并
        $user_list = array_merge($buy_id, $sell_id);
        //去空
        $user_list = array_filter($user_list);
        //去重
        $user_list = array_unique($user_list);
        $user_ids = implode(",", $user_list);
        $members = array();
        if ($user_ids) {
            $user = DB::name('member_list')
            ->field('id,tel')
            ->whereIn('id', $user_ids)
            ->select();
            foreach ($user as $key => $val) {
                $members[$val['id']] = $val;
            }
            
        }
        $resut = array(
            'list' => $list,
            'member' => $members,
        );
        return $resut;
    }
    
    
    
    //YKB交易所同意申诉
    public function coin_agreeAppeal($order_id)
    {
        if (Cache::get('coin_agreeAppeal'.$order_id) == 2){
            return json(['code' => 2, 'msg' => '请勿频繁操作', 'data'=>[]]);
        }
        Cache::set('coin_agreeAppeal'.$order_id,2,10);      
        
        if ($order_id) {
            $MCommon = new MCommon();
            $condition = 'id=' . $order_id;
            $order = $MCommon->getField('order_coin', $condition);

            if ($order && $order['status'] == 4) {
                //改变订单状态
                try {
                    Db::startTrans();
                    $data = ['status' => 1, 'recevice_time' => time()];
                    //更改订单状态
                    $res1 = $MCommon->getUpdata('order_coin', $condition, $data);
                    if (!$res1) {
                        throw new Exception("更改订单状态失败");
                    }

                     //增加订单记录
                    $data4 = [
                        'orderNo' => $order['orderNo'],
                        'order_id' => $order['id'],
                        'uid' => $order['sell_uid'],
                        'phone' => $order['sell_user'],
                        'message' => '卖家申诉成功:订单重新退回匹配中,请等待买家重新上传支付凭证,订单编号' . $order['orderNo'],
                        'time' => time()
                    ];
                    $data5 = [
                        'orderNo' => $order['orderNo'],
                        'order_id' => $order['id'],
                        'uid' => $order['buy_uid'],
                        'phone' => $order['buy_user'],
                        'message' => '卖家申诉成功:订单重新退回匹配中,请重新上传支付凭证，订单编号' . $order['orderNo'],
                        'time' => time()
                    ];
                    $res4 = Db::name('order_coin_log')->insert($data4);
                    $res5 = Db::name('order_coin_log')->insert($data5);

                    if (!$res4) {
                        throw new Exception("卖家订单日志添加失败");
                    }
                    if (!$res5) {
                        throw new Exception("买家订单日志添加失败");
                    }
                    Db::commit();
                    Cache::set('coin_agreeAppeal'.$order_id,0,1); 
                    return ['code' => 1, 'msg' => '同意申诉成功,订单返回匹配中，等待买家重新上传支付凭证'];
                } catch (\Exception $e) {
                    Db::rollback();
                    Cache::set('coin_agreeAppeal'.$order_id,0,1); 
                    return ['code' => 2, 'msg' => '同意申诉失败'];
                }
            } else {
                return ['code' => 2, 'msg' => '参数错误'];
            }
        } else {
            return ['code' => 2, 'msg' => '参数错误'];
        }
    }
    
    //YKB交易所拒绝申诉
    public function coin_refuseAppeal($order_id)
    {   
        if (Cache::get('coin_refuseAppeal'.$order_id) == 2){
            return json(['code' => 2, 'msg' => '请勿频繁操作', 'data'=>[]]);
        }
        Cache::set('coin_refuseAppeal'.$order_id,2,10);
               
        $MCommon = new MCommon();
        $order = $MCommon->getField('order_coin', 'id=' . $order_id);
        $member_list = Db::name('member_list')->where('id',$order['buy_uid'])->find();

        if ($order && $order['status'] == 4) {
            try {
                Db::startTrans();
                 $data1 = ['status' => 6, 'end_time' => time()];
                //更改订单状态
                $res1 = $MCommon->getUpdata('order_coin', 'id=' . $order_id, $data1);
                if (!$res1) {
                    throw new \Exception("更改订单状态失败");
                }
/*                 //冻结账户
                $data8 = ['status' => 2];
                $condition_sell = 'id=' . $order['sell_id'];
                $buy_user = $MCommon->getValue('member_list', $condition_sell, 'status');
                if ($buy_user == 1) {
                    $res8 = $MCommon->getUpdata('member_list', $condition_sell, $data8);
                    if (!$res8) {
                        throw new \Exception("冻结卖家账户失败");
                    }
                } */                
                //增加订单记录
                $data2 = [
                    'orderNo' => $order['orderNo'],
                    'order_id' => $order['id'],
                    'uid' => $order['sell_uid'],
                    'phone' => $order['sell_user'],
                    'message' => '卖家申诉失败:订单完结，编号' . $order['orderNo'],
                    'time' => time()
                ];
                $data3 = [
                    'orderNo' => $order['orderNo'],
                    'order_id' => $order['id'],
                    'uid' => $order['buy_uid'],
                    'phone' => $order['buy_user'],
                    'message' => '卖家申诉失败:订单完结,编号' . $order['orderNo'],
                    'time' => time()
                ];
                $res2 = $MCommon->getInsert('order_coin_log', $data2);
                $res3 = $MCommon->getInsert('order_coin_log', $data3);
                if (!$res2) {
                    throw new \Exception("卖家订单日志添加失败");
                }
                if (!$res3) {
                    throw new \Exception("买家订单日志添加失败");
                }
                $condition_buy = 'id=' . $order['buy_uid'];
                //$user = $MCommon->getField('member_list', $condition_buy, 'balance,level');
                $res4 = $MCommon->getIncrease('member_list', $condition_buy, 'balance', $order['num']);//买家加钱
                if (!$res4) {
                    throw new \Exception("买家YKB增加失败");
                }
                $res5 = $MCommon->getIncrease('member_list', $condition_buy, 'coin_num', $order['num']);//买家累计购买量
                if (!$res5) {
                    throw new \Exception("买家增加累计购买量失败");
                }
                $res51 = $MCommon->getReduce('member_list', 'id ='.$order['sell_uid'], 'frozen_dot', $order['num']);//卖家扣除冻结金额
                if (!$res5) {
                    throw new \Exception("卖家扣除冻结金额失败");
                }

                 //增加YKB记录
                $data6 = [
                    'u_id' => $order['buy_uid'],
                    'tel' => $order['buy_user'],
                    'o_id' => $order['id'],
                    'former_money' => $member_list['balance'],
                    'change_money' => $order['num'],
                    'after_money' => $member_list['balance']+$order['num'],
                    'type' => 102,
                    'message' => '成功购买'.$order['num'].'YKB,订单编号：'.$order['orderNo'],
                    'bo_time' => time(),
                    'type' => 234,
                ];
                $res6 = $MCommon->getInsert('member_balance_log', $data6);//YKB变动记录
                if (!$res6) {
                    throw new \Exception("买家YKB记录添加失败");
                }
                //增加YKB记录
                $data7 = [
                    'u_id' => $order['sell_uid'],
                    'tel' => $order['sell_user'],
                    'o_id' => $order['id'],
                    'former_money' => 0,
                    'change_money' => 0,
                    'after_money' => 0,
                    'type' => 101,
                    'message' => '成功出售'.$order['num'].'YKB,手续费'.$order['recharge'].'YKB,订单编号：'.$order['orderNo'],
                    'bo_time' => time(),
                    'type' => 231,
                ];
                $res7 = $MCommon->getInsert('member_balance_log', $data7);//YKB变动记录
                if (!$res7) {
                    throw new \Exception("卖家YKB记录添加失败");
                }
                
                Db::commit();
                Cache::set('coin_refuseAppeal'.$order_id,0,1);
                return ['code' => 1, 'msg' => '拒绝申诉成功,交易完成'];
            } catch (\Exception $e) {
                Db::rollback();
                Cache::set('coin_refuseAppeal'.$order_id,0,1);
                return ['code' => 2, 'msg' => '拒绝申诉失败'];
            } 
        } else {
            return ['code' => 2, 'msg' => '订单不存在'];
        }
    }
    
    
    
}

