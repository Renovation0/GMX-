<?php

namespace app\admin\controller;

use app\admin\model\MMachineOrder;
use app\admin\model\SystemConfig;
use think\Db;
use think\Exception;
use think\Request;
use module\Redis;
use app\admin\model\MRainList;
use app\admin\model\MMemberLevel;
use app\admin\model\MCommon;

class Rain extends Check
{
    // 三级直通车说明
    public function explain(Request $request)
    {
        if (request()->isAjax()) {
            $content = $request->param('content', '');
            if ($content == '') {
                return json(['code' => 2, 'msg' => '请输入内容']);
            }
            $res = $this->redis->set('explain', $content);
            if ($res) {
                return json(['code' => 1, 'msg' => '提交成功']);
            } else {
                return json(['code' => 2, 'msg' => '提交失败']);
            }
        }
        $explain = $this->redis->get('explain');
        $this->assign('info', $explain);
        return view();
    }


    // 申请列表
    public function rainApply(Request $request)
    {
        $status = intval($request->param('status', -1));
        $user_tel = trim($request->param('user_tel', ''));
        $this->assign('param_status', $status);
        $this->assign('param_user_tel', $user_tel);
        $allParams = ['query' => $request->param()];
        $pageSize = 10; // 分页大小
        $where = "1 = 1"; // 初始查询条件
        if ($status != -1) {
            $where .= " and a.status = " . $status;
        }
        if ($user_tel != '') {
            $where .= " and m.`tel` like '%" . $user_tel . "%'";
        }
        // 获取列表
        $list = Db::name('rain_apply')->alias('a')->leftJoin('member_list m', 'm.id = a.u_id')->order('a.stime desc')->where($where)->field('a.*, m.tel')->paginate($pageSize, false, $allParams);
        $this->assign('list', $list);
        return view();
    }

    // 处理代理申请
    public function rainDeal(Request $request)
    {
        $id = intval($request->param('id', 0));
        $status = intval($request->param('status', 0));
        $reply = $request->param('reply', '');
        if ($id <= 0 || ($status != 1 && $status !== 2)) {
            return json(['code' => 2, 'msg' => '非法访问']);
        }
        if ($status == 2 && $reply == '') {
            return json(['code' => 2, 'msg' => '拒绝时请输入原因']);
        }
        $info = Db::name('rain_apply')->where('id', $id)->find();
        if ($info) {
            if ($info['status'] != 0) {
                return json(['code' => 2, 'msg' => '申请已处理过']);
            }
        } else {
            return json(['code' => 2, 'msg' => '申请不存在']);
        }
        if ($status == 1) { // 通过
            // return json(['code' => 2, 'msg' => '开发中。。。']);
            $u_info = Db::name('member_list')->where('id', $info['u_id'])->field('id,status, partner,agent, coin, real_name_status,mac_wallet, balance_num, sell_limit, mac_assets, level, f_uid, level_last,yx_zt_num')->find();
            if ($u_info) {
                if ($u_info['status'] != 1) {
                    return json(['code' => 1, 'msg' => '用户处于封号状态']);
                }
                if ($u_info['real_name_status'] != 1) {
                    return json(['code' => 1, 'msg' => '用户未实名']);
                }
                // 判断是否有已经同意过的申请
                if (Db::name('rain_apply')->where('u_id', $info['u_id'])->where('status', 1)->count() > 0) {
                    return json(['code' => 1, 'msg' => '用户已经开通过三级直通车']);
                }
                $configModel = new SystemConfig();
                // 获取开通后赠送的内容
                $c_3_sum_num = intval($configModel->getConfigByKey('3_sum_num')); //赠送累计买币量
                $c_3_sell_limit = intval($configModel->getConfigByKey('3_sell_limit')); //赠送可售额度
                $c_3_sell_limit_wait = intval($configModel->getConfigByKey('3_sell_limit_wait')); //延长赠送可售额度
                $c_3_t_1 = intval($configModel->getConfigByKey('3_t_1')); //延长赠送可售额度直推1级量
                $c_3_mac_assets = intval($configModel->getConfigByKey('3_mac_assets')); //赠送矿池资产
                $c_3_s = intval($configModel->getConfigByKey('3_s')); //赠送小型矿机
                $c_3_m = intval($configModel->getConfigByKey('3_m')); //赠送中型矿机
                $c_3_l = intval($configModel->getConfigByKey('3_l')); //赠送大型矿机
                $c_3_wgt = intval($configModel->getConfigByKey('3_wgt')); //赠送子币
                $partner_zt = intval($configModel->getConfigByKey('PARTNER_ZT')); //成为合伙人
                $now = time();
                try {
                    Db::startTrans();
                    // 修改申请状态
                    Db::name('rain_apply')->where('id', $id)->update(['reply' => '', 'status' => 1, 'etime' => $now]);
                    // 添加直通车记录
                    $log_id = Db::name('rain_log')->insertGetId([
                        'u_id' => $info['u_id'],
                        'sun_num' => $c_3_sum_num,
                        'sell_limit' => $c_3_sell_limit,
                        'sell_limit_wait' => $c_3_sell_limit_wait,
                        't_1' => $c_3_t_1,
                        'mac_assets' => $c_3_mac_assets,
                        's' => $c_3_s,
                        'm' => $c_3_m,
                        'l' => $c_3_l,
                        'wgt' => $c_3_wgt,
                        'add_time' => $now,
                        'end_time' => $now + 3600 * 30,
                        't' => 0,
                    ]);
                    // 赠送的累计买量、可售额度、矿池资产、子币发放 并添加记录
                    if ($c_3_sum_num > 0) {
                        Db::name('member_list')->where('id', $info['u_id'])->setInc('balance_num', $c_3_sum_num);
                        Db::name('member_list')->where('id', $info['u_id'])->setInc('mac_wallet', $c_3_sum_num);
                        Db::name('member_mac_wallet_log')->insert([
                            'u_id' => $info['u_id'],
                            'o_id' => $log_id,
                            'bo_money' => $c_3_sum_num,
                            'former_money' => $u_info['mac_wallet'],
                            'type' => 314,
                            'message' => '认筹三级直通车赠送矿池钱包',
                            'bo_time' => $now,
                            'status' => 1,
                            'is_look' => 0,
                        ]);
                        // 添加假的买笔订单记录
                        $orderNo = 'NO' . date('Ymd') . rand(9999, 99999);
                        Db::name('order_list')->insert([
                            'orderNo'   => $orderNo,
                            'buy_id'    => $info['u_id'],
                            'num'       => $c_3_sum_num,
                            'price'     => 0,
                            'rechange'  => 0,
                            'bo_money'  => 0,
                            'status'    => 5, // 标记为成功
                            's_time'    => $now,
                            'u_time'    => 0, //排队时间
                            'e_time'    => $now, // 结束时间
                            'type'      => 2 // 标记为三级直通车插入的加数据
                        ]);
                        // 查出进行中的买币活动，金额不大于赠送金额的
                        $activity_ids = Db::name('activity_buycoins')->where('buy_num', '<=', $c_3_sum_num)->where('begin_time', '<=', $now)->where('end_time', '>=', $now)->where('status', 1)->column('id');
                        if(!empty($activity_ids)){
                            foreach ($activity_ids as $activity_id){
                                Db::name('activity_buycoins_log')->insert([
                                    'u_id'      =>  $info['u_id'],
                                    'a_id'      =>  $activity_id,
                                    'message'   =>  '认筹三级直通车触发买币奖励',
                                    'time'      =>  $now,
                                    'status'    =>  1,
                                    'by_uid'    =>  $u_info['f_uid'],
                                ]);
                            }
                        }
                    }
                    if ($c_3_sell_limit > 0) {
                        Db::name('member_list')->where('id', $info['u_id'])->setInc('sell_limit', $c_3_sell_limit);
                        Db::name('member_sell_limit_log')->insert([
                            'u_id' => $info['u_id'],
                            'o_id' => $log_id,
                            'bo_money' => $c_3_sell_limit,
                            'former_money' => $u_info['sell_limit'],
                            'type' => 409,
                            'message' => '认筹三级直通车赠送',
                            'bo_time' => $now,
                            'status' => 1,
                            'is_look' => 0,
                        ]);
                    }
                    if ($c_3_sell_limit > 0) {
                        Db::name('member_list')->where('id', $info['u_id'])->setInc('mac_assets', $c_3_mac_assets);
                        Db::name('member_mac_assets_log')->insert([
                            'u_id' => $info['u_id'],
                            'o_id' => $log_id,
                            'bo_money' => $c_3_mac_assets,
                            'former_money' => $u_info['mac_assets'],
                            'type' => 209,
                            'message' => '认筹三级直通车赠送',
                            'bo_time' => $now,
                            'status' => 1,
                            'is_look' => 0,
                        ]);
                    }
                    if ($c_3_sell_limit > 0) {
                        Db::name('member_list')->where('id', $info['u_id'])->setInc('coin', $c_3_wgt);
                        Db::name('member_coin_log')->insert([
                            'u_id' => $info['u_id'],
                            'o_id' => $log_id,
                            'bo_money' => $c_3_wgt,
                            'former_money' => $u_info['coin'],
                            'type' => 101,
                            'message' => '认筹三级直通车赠送',
                            'bo_time' => $now,
                            'status' => 1,
                            'is_look' => 0,
                        ]);
                    }
                    // 赠送矿机
                    $MMachineOrder = new MMachineOrder();//MachineOrder();
                    if ($c_3_s > 0) {
                        $machine_name = Db::name('machine_manage')->where('id', 1)->value('name');
                        $MMachineOrder->sendMarchine($info['u_id'], 1, $c_3_s);
                        Db::name('machine_log')->insert([
                            'mac_id' => 1,
                            'u_id' => $info['u_id'],
                            'price' => 0,
                            'num' => $c_3_s,
                            'message' => '认筹三级直通车赠送' . $machine_name . $c_3_s . '台',
                            'time' => $now,
                            'status' => 1
                        ]);
                    }
                    if ($c_3_m > 0) {
                        $machine_name = Db::name('machine_manage')->where('id', 2)->value('name');
                        $MMachineOrder->sendMarchine($info['u_id'], 2, $c_3_m);
                        Db::name('machine_log')->insert([
                            'mac_id' => 2,
                            'u_id' => $info['u_id'],
                            'price' => 0,
                            'num' => $c_3_m,
                            'message' => '认筹三级直通车赠送' . $machine_name . $c_3_m . '台',
                            'time' => $now,
                            'status' => 1
                        ]);
                    }
                    if ($c_3_l > 0) {
                        $machine_name = Db::name('machine_manage')->where('id', 3)->value('name');
                        $MMachineOrder->sendMarchine($info['u_id'], 3, $c_3_l);
                        Db::name('machine_log')->insert([
                            'mac_id' => 3,
                            'u_id' => $info['u_id'],
                            'price' => 0,
                            'num' => $c_3_l,
                            'message' => '认筹三级直通车赠送' . $machine_name . $c_3_l . '台',
                            'time' => $now,
                            'status' => 1
                        ]);
                    }
                    //查询等级表
                    //$level = Db::name('member_level')->where('id in (1,2,3)')->field('id,level_machine')->select();//三级
                    //$mac_id_arr = ['1' => $c_3_s,'2' => $c_3_m,'3' =>$c_3_l];
//                    var_dump($level[2]['level_machine']);
//                    var_dump($mac_id_arr[$level[2]['level_machine']]);die;$mac_id_arr[$level[2]['level_machine']] > 0
                    // 升3级 （用户等级小于3 且 赠送对应等级矿机 且 累计买币量达到要求）
                    if ($u_info['level'] < 3 && $c_3_l > 0 && ($u_info['balance_num'] + $c_3_sum_num) >= intval(Db::name('member_level')->where('id', 3)->value('buy_coin'))) {
                        Db::name('member_list')->where('id', $u_info['id'])->setField('level', 3); // 升级到3级
                        //判断是否能够成为合伙人
                        if ($u_info['yx_zt_num'] >= $partner_zt && $u_info['partner'] != 1) {//如果升级到3 检查自己是否能够成为合伙人
                            //检查我的有效直推
                            Db::name('member_list')->where('id', $u_info['id'])->update(['partner'=> 1,'partner_time' => time()]);
                        }
                        // 维护历史最高等级
                        if ($u_info['level_last'] < 3) {
                            Db::name('member_list')->where('id', $u_info['id'])->setField('level_last', 3);
                            if ($u_info['level_last'] < 1 && $u_info['f_uid'] != 0) {
                                //有效升级--直推得奖励
                                $f_info = Db::name('member_list')->where('id', $u_info['f_uid'])->field('level, mac_assets, sell_limit')->find();
                                //$direct_assets = Db::name('member_level')->where('id', $f_info['level'])->value('direct_assets');
                                $Com = new MCommon();
                                $direct_assets = $Com->readLevel($u_info['f_uid'],'direct_assets');
                                $direct_assets = $direct_assets['direct_assets'];
                                Db::name('member_list')->where('id', $u_info['f_uid'])->setInc('mac_assets', $direct_assets);
                                Db::name('member_mac_assets_log')->insert([
                                    'u_id' => $u_info['f_uid'],
                                    'bo_money' => $direct_assets,
                                    'former_money' => $f_info['mac_assets'],
                                    'type' => 204,
                                    'message' => '下级升级为一级奖励',
                                    'bo_time' => $now,
                                    'status' => 1
                                ]);
                                // 获取上级购买直通车记录(未到延迟赠送时间的，且未延迟赠送过的)
                                $rain_log = Db::name('rain_log')->where('u_id', $u_info['f_uid'])->where('end_time', '>=', $now)->where('status', 0)->field('*')->find();
                                if($rain_log){
                                    // 增加人数
                                    Db::name('rain_log')->where('id', $rain_log['id'])->setInc('t');
                                    // 判断到达条件否
                                    if(($rain_log['t'] + 1) >= $rain_log['t_1']){
                                        // 修改为已经赠送
                                        Db::name('rain_log')->where('id', $rain_log['id'])->setField('status', 1);
                                        // 发放赠送
                                        if($rain_log['sell_limit_wait'] > 0){
                                            Db::name('member_list')->where('id', $u_info['f_uid'])->setInc('sell_limit', $rain_log['sell_limit_wait']);
                                            Db::name('member_sell_limit_log')->insert([
                                                'u_id' => $u_info['f_uid'],
                                                'o_id' => $rain_log['id'],
                                                'bo_money' => $rain_log['sell_limit_wait'],
                                                'former_money' => $f_info['sell_limit'],
                                                'type' => 409,
                                                'message' => '认筹三级直通车延长赠送',
                                                'bo_time' => $now,
                                                'status' => 1,
                                                'is_look' => 0,
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        // 升2级 （用户等级小于2 且 赠送了对应等级矿机 且 累计买币量达到要求）$mac_id_arr[$level[1]['level_machine']] > 0
                        if ($u_info['level'] < 2 && $c_3_m > 0 && ($u_info['balance_num'] + $c_3_sum_num) >= intval(Db::name('member_level')->where('id', 2)->value('buy_coin'))) {
                            Db::name('member_list')->where('id', $u_info['id'])->setField('level', 2);
                            // 维护历史最高等级
                            if ($u_info['level_last'] < 2){
                                Db::name('member_list')->where('id', $u_info['id'])->setField('level_last', 2); // 升级到2级
                                if ($u_info['level_last'] < 1  && $u_info['f_uid'] != 0) {
                                    //有效升级--直推得奖励
                                    $f_info = Db::name('member_list')->where('id', $u_info['f_uid'])->field('level, mac_assets, sell_limit')->find();
                                    //$direct_assets = Db::name('member_level')->where('id', $f_info['level'])->value('direct_assets');
                                    $Com = new MCommon();
                                    $direct_assets = $Com->readLevel($u_info['f_uid'],'direct_assets');
                                    $direct_assets = $direct_assets['direct_assets'];
                                    Db::name('member_list')->where('id', $u_info['f_uid'])->setInc('mac_assets', $direct_assets);
                                    Db::name('member_mac_assets_log')->insert([
                                        'u_id' => $u_info['f_uid'],
                                        'bo_money' => $direct_assets,
                                        'former_money' => $f_info['mac_assets'],
                                        'type' => 204,
                                        'message' => '下级升级为一级奖励',
                                        'bo_time' => $now,
                                        'status' => 1
                                    ]);
                                    // 获取上级购买直通车记录(未到延迟赠送时间的，且未延迟赠送过的)
                                    $rain_log = Db::name('rain_log')->where('u_id', $u_info['f_uid'])->where('end_time', '>=', $now)->where('status', 0)->field('*')->find();
                                    if($rain_log){
                                        // 增加人数
                                        Db::name('rain_log')->where('id', $rain_log['id'])->setInc('t');
                                        // 判断到达条件否
                                        if(($rain_log['t'] + 1) >= $rain_log['t_1']){
                                            // 修改为已经赠送
                                            Db::name('rain_log')->where('id', $rain_log['id'])->setField('status', 1);
                                            // 发放赠送
                                            if($rain_log['sell_limit_wait'] > 0){
                                                Db::name('member_list')->where('id', $u_info['f_uid'])->setInc('sell_limit', $rain_log['sell_limit_wait']);
                                                Db::name('member_sell_limit_log')->insert([
                                                    'u_id' => $u_info['f_uid'],
                                                    'o_id' => $rain_log['id'],
                                                    'bo_money' => $rain_log['sell_limit_wait'],
                                                    'former_money' => $f_info['sell_limit'],
                                                    'type' => 409,
                                                    'message' => '认筹三级直通车延长赠送',
                                                    'bo_time' => $now,
                                                    'status' => 1,
                                                    'is_look' => 0,
                                                ]);
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            // 升1级 （用户等级小于1 且 赠送了对应等级矿机 且 累计买币量达到要求）$mac_id_arr[$level[0]['level_machine']] > 0
                            if ($u_info['level'] < 1 && $c_3_s > 0 && ($u_info['balance_num'] + $c_3_sum_num) >= intval(Db::name('member_level')->where('id', 1)->value('buy_coin'))) {
                                Db::name('member_list')->where('id', $u_info['id'])->setField('level', 1);
                                // 维护历史最高等级
                                if ($u_info['level_last'] < 1  && $u_info['f_uid'] != 0){
                                    //有效升级--直推得奖励
                                    $f_info = Db::name('member_list')->where('id', $u_info['f_uid'])->field('level, mac_assets, sell_limit')->find();
                                    //$direct_assets = Db::name('member_level')->where('id', $f_info['level'])->value('direct_assets');
                                    $Com = new MCommon();
                                    $direct_assets = $Com->readLevel($u_info['f_uid'],'direct_assets');
                                    $direct_assets = $direct_assets['direct_assets'];
                                    Db::name('member_list')->where('id', $u_info['f_uid'])->setInc('mac_assets', $direct_assets);
                                    Db::name('member_mac_assets_log')->insert([
                                        'u_id' => $u_info['f_uid'],
                                        'bo_money' => $direct_assets,
                                        'former_money' => $f_info['mac_assets'],
                                        'type' => 204,
                                        'message' => '下级升级为一级奖励',
                                        'bo_time' => $now,
                                        'status' => 1
                                    ]);
                                    // 获取上级购买直通车记录(未到延迟赠送时间的，且未延迟赠送过的)
                                    $rain_log = Db::name('rain_log')->where('u_id', $u_info['f_uid'])->where('end_time', '>=', $now)->where('status', 0)->field('*')->find();
                                    if($rain_log){
                                        // 增加人数
                                        Db::name('rain_log')->where('id', $rain_log['id'])->setInc('t');
                                        // 判断到达条件否
                                        if(($rain_log['t'] + 1) >= $rain_log['t_1']){
                                            // 修改为已经赠送
                                            Db::name('rain_log')->where('id', $rain_log['id'])->setField('status', 1);
                                            // 发放赠送
                                            if($rain_log['sell_limit_wait'] > 0){
                                                Db::name('member_list')->where('id', $u_info['f_uid'])->setInc('sell_limit', $rain_log['sell_limit_wait']);
                                                Db::name('member_sell_limit_log')->insert([
                                                    'u_id' => $u_info['f_uid'],
                                                    'o_id' => $rain_log['id'],
                                                    'bo_money' => $rain_log['sell_limit_wait'],
                                                    'former_money' => $f_info['sell_limit'],
                                                    'type' => 409,
                                                    'message' => '认筹三级直通车延长赠送',
                                                    'bo_time' => $now,
                                                    'status' => 1,
                                                    'is_look' => 0,
                                                ]);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    Db::commit();
                    return json(['code' => 1, 'msg' => '通过成功']);
                } catch (Exception $e) {
                    Db::rollback();
                    return json(['code' => 2, 'msg' => '通过失败'.$e->getMessage()]);
                }
            } else {
                return json(['code' => 1, 'msg' => '用户数据丢失']);
            }
        } else { // 拒绝
            if (Db::name('rain_apply')->where('id', $id)->update(['reply' => $reply, 'status' => 2, 'etime' => time()])) {
                return json(['code' => 1, 'msg' => '拒绝成功']);
            } else {
                return json(['code' => 2, 'msg' => '拒绝失败']);
            }
        }
    }

    // 开通记录
    public function rainLists(Request $request)
    {
        $user_tel = trim($request->param('user_tel', ''));
        $this->assign('param_user_tel', $user_tel);
        $allParams = ['query' => $request->param()];
        $pageSize = 10; // 分页大小
        $where = "1 = 1"; // 初始查询条件
        if ($user_tel != '') {
            $where .= " and m.`tel` like '%" . $user_tel . "%'";
        }
        // 获取列表
        $list = Db::name('rain_log')->alias('l')->leftJoin('member_list m', 'm.id = l.u_id')->where($where)->field('l.*, m.tel')->paginate($pageSize, false, $allParams);
        $this->assign('list', $list);
        return view();
    }
}

