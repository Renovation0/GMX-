<?php


namespace app\admin\controller;


use app\admin\model\MCommon;
use app\admin\model\OrderCoinList;
use app\admin\model\OrderList;
use app\admin\model\SystemModule;
use think\Db;
use think\Request;
use app\admin\model\OrderCoinLog;
use app\admin\model\OrderCoin;

class Order extends Check
{
    //订单列表0挂单中1交易中3已付款5交易成功7已取消9申诉成功11申诉中13申诉失败15强制取消17超时自动取消
    public function orderList(Request $request)
    {
        $my_active_module = intval($request->param('my_active_module', 2)); //
        $this->assign('my_active_module', $my_active_module);
        $sort = $request->param('sort', '');
        if ($sort != '') {
            $orders = $sort;
        } else {
            $orders = 'id desc';
        }
        $allParams = ['query' => $request->param()];
        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        $user_tel = trim($request->param('user_tel', ''));
        $type = intval($request->param('type', 0));
        $add_time_s = $request->param('add_time_s', ''); // 开启时间开始
        $add_time_e = $request->param('add_time_e', ''); // 开启时间结束
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        $this->assign('user_tel', $user_tel);
        $this->assign('param_type', $type);
        $this->assign('sort', $sort);
        if ($user_tel != '') {
            $id = new MCommon();
            $condition = "`tel` = '" . $user_tel . "'";
            $user = $id->getValue('member_list', $condition, 'id');
            if ($user) {
                $where .= ' and (`buy_id` = ' . $user . ' or `sell_id` = ' . $user . ')';
            } else {
                $where .= '';
            }
        }
        if ($add_time_s != '') {
            $where .= " and `s_time` >= " . strtotime($add_time_s);
        }
        if ($add_time_e != '') {
            $where .= " and `s_time` <= " . strtotime($add_time_e);
        }
        if ($type == 1) {//挂单中
            $where .= ' and `status` = 0';
        } elseif ($type == 2) {//交易中
            $where .= ' and `status` in (1,3)';
        } elseif ($type == 3) {//申诉中
            $where .= ' and `status` = 11';
        } elseif ($type == 4) {//已取消
            $where .= ' and `status` in (7,9,15,17)';
        } elseif ($type == 5) {//已完成
            $where .= ' and `status` in (5,13)';
        }
        switch ($my_active_module) {
            case 1:
                $where .= ' and type = 1';
                $orderList = $this->balance_order($where, $pageSize, $allParams, $orders);
                break;
            case 2:
                $OrderCoin = new OrderCoin();
                $orderList = $OrderCoin->getLists($where, $pageSize, $allParams, $orders);
                //$orderList = $this->coin_order($where, $pageSize, $allParams, $orders);
                break;
            default:
                return json(['code' => 2, 'msg' => '订单状态不正确']);
                break;
        }
        $this->assign('orderList', $orderList);
        return view();
    }

    //余额订单列表
    public function balance_order($where, $pageSize, $allParams, $orders)
    {
        // 获取订单列表
        $OrderList = new OrderList();
        return $OrderList->getLists($where, $pageSize, $allParams, $orders);
    }

    //子币订单列表
    public function coin_order($where, $pageSize, $allParams, $orders)
    {
        // 获取订单列表
        $OrderList = new OrderCoinList();
        return $OrderList->getLists($where, $pageSize, $allParams, $orders);
    }

    //订单记录
    public function orderLog(Request $request)
    {
        $my_active_module = intval($request->param('my_active_module', 2)); //
        $this->assign('my_active_module', $my_active_module);
        $sort = $request->param('sort', '');
        if ($sort != '') {
            $orders = $sort;
        } else {
            $orders = 'id desc';
        }
        $user_tel = trim($request->param('user_tel', ''));
        $add_time_s = $request->param('add_time_s', ''); // 开启时间开始
        $add_time_e = $request->param('add_time_e', ''); // 开启时间结束
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        $this->assign('user_tel', $user_tel);
        $this->assign('sort', $sort);
        $allParams = ['query' => $request->param()];
        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if ($user_tel != '') {
            $id = new MCommon();
            $condition = "`tel` = '" . $user_tel . "'";
            $user = $id->getValue('member_list', $condition, 'id');
            if ($user) {
                $where .= ' and `uid` = ' . $user;
            } else {
                $where .= '';
            }
        }
        if ($add_time_s != '') {
            $where .= " and `time` >= " . strtotime($add_time_s);
        }
        if ($add_time_e != '') {
            $where .= " and `time` <= " . strtotime($add_time_e);
        }
        switch ($my_active_module) {
            case 1:
                $OrderList = new OrderList();
                $orderList = $OrderList->balance_order_log($where, $pageSize, $allParams, $orders);
                break;
            case 2:
                $OrderCoinLog = new OrderCoinLog();
                $orderList = $OrderCoinLog->getLists($where, $pageSize, $allParams, $orders);                
/*                 $OrderCoinList = new OrderCoinList();
                $orderList = $OrderCoinList->coin_order_log($where, $pageSize, $allParams, $orders); */
                break;
            default:
                return json(['code' => 2, 'msg' => '订单状态不正确']);
                break;
        }
        $this->assign('list', $orderList);
        return view();
    }

    //订单详情
    public function orderInformation(Request $request)
    {
        $order_id = intval($request->param('id', ''));
        $type = intval($request->param('type', ''));
        if ($order_id) {
            $MCommon = new MCommon();
            switch ($type) {
                case 1:
                    $order = $MCommon->getField('order_list', 'id=' . $order_id);
                    break;
                case 2:
                    $order = $MCommon->getField('order_coin', 'id=' . $order_id);
                    break;
                default:
                    return json(['code' => 2, 'msg' => '订单状态不正确']);
            }
            if ($order) {
/*                 $condition = 'id=' . $order['buy_uid'];
                $buy_tel = $MCommon->getValue('member_list', $condition, 'tel');
                $order['buy_tel'] = $buy_tel;
                if ($order['sell_id'] != '') {
                    $condition = 'id=' . $order['sell_uid'];
                    $sell_tel = $MCommon->getValue('member_list', $condition, 'tel');
                    $order['sell_tel'] = $sell_tel;
                } else {
                    $order['sell_tel'] = '';
                } */
                switch ($order['status']) {
                    case 0:
                        $order['r_status'] = '挂单中';
                        break;
                    case 1:
                        $order['r_status'] = '已匹配';
                        break;
                    case 2:
                        $order['r_status'] = '已上传';
                        break;
                    case 3:
                        $order['r_status'] = '已完成';
                        break;
                    case 4:
                        $order['r_status'] = '申诉中';
                        break;
                    case 5:
                        $order['r_status'] = '申诉成功';
                        break;
                    case 6:
                        $order['r_status'] = '申诉失败';
                        break;
                    case 7:
                        $order['r_status'] = '已取消';
                        break;
                }
                $order['start_time'] = date('Y-m-d H:i:s', $order['start_time']);
                if ($order['recevice_time'] != 0) {
                    $order['recevice_time'] = date('Y-m-d H:i:s', $order['recevice_time']);
                }
                if ($order['voucher_time'] != 0) {
                    $order['voucher_time'] = date('Y-m-d H:i:s', $order['voucher_time']);
                }
                if ($order['reply_time'] != 0) {
                    $order['reply_time'] = date('Y-m-d H:i:s', $order['reply_time']);
                }
                if ($order['cancel_time'] != 0) {
                    $order['cancel_time'] = date('Y-m-d H:i:s', $order['cancel_time']);
                }
                if ($order['end_time'] != 0) {
                    $order['end_time'] = date('Y-m-d H:i:s', $order['end_time']);
                }
                $this->assign('order', $order);
            } else {
                return json(['code' => 2, 'msg' => '订单不存在']);
            }
        } else {
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        return view();
    }

    //查看凭证
    public function orderVoucher(Request $request)
    {
        $order_id = intval($request->param('id', ''));
        $type = intval($request->param('type', ''));
        if ($order_id) {
            $MCommon = new MCommon();
            switch ($type) {
                case 1:
                    $condition = 'id=' . $order_id;
                    $order = $MCommon->getValue('order_list', $condition, 'pic');
                    break;
                case 2:
                    $condition = 'id=' . $order_id;
                    $order = $MCommon->getValue('order_coin', $condition, 'voucher');
                    break;
                default:
                    return json(['code' => 2, 'msg' => '订单状态不正确']);
            }
            if ($order) {
                $order = 'http://' . $_SERVER['HTTP_HOST'] .'/'.$order;
                $this->assign('order', $order);
            } else {
                //$this->error('凭证不存在');
                return json(['code' => 2, 'msg' => '凭证不存在']);
            }
        } else {
            //$this->error('参数错误');
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        return view();
    }

    //查看申诉
    public function orderAppeal(Request $request)
    {
        $order_id = intval($request->param('id', ''));
        $type = intval($request->param('type', ''));
        if ($order_id) {
            $MCommon = new MCommon();
            switch ($type) {
                case 1:
                    $condition = 'id=' . $order_id;
                    $order = $MCommon->getField('order_list', $condition, 'ss_pic1,ss_pic2,reply');
                    break;
                case 2:
                    $condition = 'id=' . $order_id;
                    $order = $MCommon->getField('order_coin', $condition, 'reply_img,reply_content');
                    break;
                default:
                    return json(['code' => 2, 'msg' => '订单状态不正确']);
            }
            if ($order) {
                $order['reply_img'] = explode(',', $order['reply_img']);
                foreach ($order['reply_img'] as $item){
                    $order['img'][] = 'http://' . $_SERVER['HTTP_HOST'] .$item;
                }
                unset($order['reply_img']);
                $this->assign('order', $order);
            } else {
                return json(['code' => 2, 'msg' => '申诉凭证不存在']);
            }
        } else {
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        return view();
    }

    //强制取消0挂单中1交易中3已付款5交易成功7已取消9申诉成功11申诉中13申诉失败15强制取消
    public function forceCancel(Request $request)
    {
        $order_id = intval($request->param('id', ''));
        $type = intval($request->param('type', ''));
        if (!$order_id) {
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        switch ($type) {
            case 1:
                $OrderList= new OrderList();
                $data = $OrderList->balance_forceCancel($order_id);
                break;
            case 2:
                $OrderCoinList= new OrderCoinList();
                $data = $OrderCoinList->coin_forceCancel($order_id);
                break;
            default:
                return json(['code' => 2, 'msg' => '订单状态不正确']);
        }
        return json(['code' => $data['code'], 'msg' => $data['msg']]);
    }

    //修改订单显示状态0显示1隐藏
    public function forceDisplay(Request $request)
    {
        $order_id = intval($request->param('id', ''));
        $status = intval($request->param('status', ''));
        $type = intval($request->param('type', ''));
        if ($order_id && ($status == 1 || $status == 0) && $type) {
            $MCommon = new MCommon();
            $condition = 'id=' . $order_id;
            $order = $MCommon->getField('order_list', $condition, 'id,display');
            if ($order && ($order['display'] == 0 || $order['display'] == 1)) {
                //改变订单状态
                $data = ['display' => $status];
                switch ($type) {
                    case 1:
                        $condition = 'id=' . $order_id;
                        $orderupdata = $MCommon->getUpdata('order_list', $condition, $data);
                        break;
                    case 2:
                        $condition = 'id=' . $order_id;
                        $orderupdata = $MCommon->getUpdata('order_coin_list', $condition, $data);
                        break;
                    default:
                        return json(['code' => 2, 'msg' => '参数异常']);
                }
                $message1 = '';
                $message2 = '';
                $orderupdata && $status = 0 ? $message1 = '订单显示成功' : $message1 = '订单隐藏成功';
                $orderupdata && $status = 0 ? $message2 = '订单显示失败' : $message2 = '订单隐藏失败';
                if ($orderupdata) {
                    $type == 1 ? $this->addLog('修改WBA'.$message1.'订单id'.$order_id) : $this->addLog('修改WGT'.$message1.'订单id'.$order_id);
                    return json(['code' => 1, 'msg' => $message1]);
                } else {
                    return json(['code' => 2, 'msg' => $message2]);
                }
            } else {
                return json(['code' => 2, 'msg' => '参数错误']);
            }
        } else {
            return json(['code' => 2, 'msg' => '参数错误']);
        }
    }

    //同意申诉 订单存在问题 余额返回冻结账户
    public function agreeAppeal(Request $request)
    {
        $order_id = intval($request->param('id', ''));
        $type = intval($request->param('type', ''));
        if ($order_id && ($type == 1 || $type == 2)) {
            switch ($type) {
                case 1:
                    $OrderList = new OrderList();
                    $res = $OrderList->balance_agreeAppeal($order_id);
                    break;
                case 2:
                    $OrderCoin = new OrderCoin();
                    $res = $OrderCoin->coin_agreeAppeal($order_id);                    
/*                     $OrderCoinList = new OrderCoinList();
                    $res = $OrderCoinList->coin_agreeAppeal($order_id); */
                    break;
                default:
                    return json(['code' => 2, 'msg' => '参数异常']);
            }
            $type == 1 ? $this->addLog('同意申诉WBA订单id'.$order_id) : $this->addLog('同意申诉YKB订单id'.$order_id);
            return json(['code' => $res['code'], 'msg' => $res['msg']]);
        } else {
            return json(['code' => 2, 'msg' => '参数错误']);
        }

    }

    //拒绝申诉 交易完成
    public function refuseAppeal(Request $request)
    {
        $order_id = intval($request->param('id', ''));
        $type = intval($request->param('type', ''));
        if ($order_id && ($type == 1 || $type == 2)) {
            switch ($type) {
                case 1:
                    $OrderList = new OrderList();
                    $res = $OrderList->balance_refuseAppeal($order_id);
                    break;
                case 2:
                    $OrderCoin = new OrderCoin();
                    $res = $OrderCoin->coin_refuseAppeal($order_id);
                    
/*                     $OrderCoinList = new OrderCoinList();
                    $res = $OrderCoinList->coin_refuseAppeal($order_id); */
                    break;
                default:
                    return json(['code' => 2, 'msg' => '参数异常']);
            }
            $type == 1 ? $this->addLog('拒绝申诉WBA订单id'.$order_id) : $this->addLog('拒绝申诉YKB订单id'.$order_id);
            return json(['code' => $res['code'], 'msg' => $res['msg']]);
        } else {
            return json(['code' => 2, 'msg' => '参数错误']);
        }
    }

    //交易大厅
    public function orderHall()
    {
//        $this->redis->del('order_balance');
//            $this->redis->del('order_balance_c');die();
        $lists = $this->redis->get('order_balance'); // 主数据
        if ($lists) {
            $lists = json_decode($lists, true);
            $remainingTime = $this->redis->ttl('order_balance'); // 主数据缓存时间
        } else {
            $OrderCoinList = new OrderCoinList();
            $lists = $OrderCoinList->getHall();
            if ($lists) {
                if (!empty($lists)) {
                    $SystemModule = new SystemModule();
                    $refreshTime = $SystemModule->readConfig('REFRESH_TIME') * 60; // 主数据刷新时间
                    //$refreshTime = 120; // 主数据刷新时间
                    if($refreshTime > 0){
                        $overtime = (time() % $refreshTime); // 主数据超出最后刷新时间
                        $remainingTime = $overtime != 0 ? $overtime : $refreshTime; // 主数据缓存时间
                        $this->redis->set('order_balance', json_encode($lists), $remainingTime); // 主数据缓存数据
                    }
                } else {
                    return json(['code' => 1, 'msg' => 'ok', 'data' => []]);
                }
            } else {
                return json(['code' => 2, 'msg' => '暂无数据！']);
            }
        }
        if (empty($lists)) { // 主数据为空
            return json(['code' => 1, 'msg' => 'ok', 'data' => []]);
        } else {
            $listsC = $this->redis->get('order_balance_c'); // 子数据
            if ($listsC) {
                $listsC = json_decode($listsC, true);
            } else {
                $listsC = []; // 初始化子数据
                $stop_ids = $this->redis->get('order_balance_stop_ids'); // 被停止的订单
                if ($stop_ids && $stop_ids != '') {
                    $stop_ids = explode(',', $stop_ids);
                } else {
                    $stop_ids = [];
                }
                if (empty($stop_ids)) {
                    $listsC = $lists;
                } else {
                    foreach ($lists as $k => $list) {
                        if (in_array($list['id'], $stop_ids)) {
                            $listsC[] = $list; // 被停止的订单排在前面
                            unset($lists[$k]);
                        }
                    }
                    $listsC = array_merge($listsC, $lists); // 最终得到的子数据
                    $refreshTimeC = 120; // 子刷新时间
                    $refreshTimeC = $refreshTimeC <= $remainingTime ? $refreshTimeC : $remainingTime; // 维护子刷新时间
                    $this->redis->set('order_balance_c', json_encode($listsC), $refreshTimeC); // 主数据缓存数据
                }
            }
            // 至此得到正确的数据 把我的数据从子数据删除
            $list_last = [];
            if (!empty($listsC)) {
                foreach ($listsC as $clist) {
                    // $list_last[] = $clist;
                    $list_last[] = Db::name('order_list')->alias('o')
                        ->leftJoin('member_list m', 'm.id = o.buy_id')
                        ->leftJoin('member_list l', 'l.id = o.sell_id')
                        ->where('o.id', $clist['id'])
                        ->order('m.first_blood')->order('o.status')->order('o.num')// 排序
                        ->field('o.*,m.level,m.real_name_time,m.first_blood,m.tel as b_tel,l.tel as s_tel')// 需查的数据
                        ->find();
                }
            }
            if (!empty($list_last)){
                for ($i=0;$i<count($list_last);$i++){
                    $list_last[$i]['new'] = $list_last[$i]['first_blood'] == 1 ? 1 : 0;
                    $list_last[$i]['xl_id'] = $i+1;
                }
            }
            $this->assign('orderList', $list_last);
        }
        return view();
    }
}