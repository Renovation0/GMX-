<?php


namespace app\admin\model;


use module\Redis;
use think\Db;
use think\Exception;
use think\Model;

class MGame extends Model
{
    //游戏数据统计
    public function gameCensus()
    {
        $Redis = new Redis();
        $redis = $Redis->redis();
        //额度下注量
        $sum_num = Db::name('game_btc')->sum('money');
        $redis->hSet('census', 'game_num_btc', $sum_num);
        //平台额度盈利
        $btc_pay_num = Db::name('game_btc')->sum('r_money');
        if ($btc_pay_num > 0) {
            $btc_pay_num = '-' . $btc_pay_num;
        } else {
            $btc_pay_num = abs($btc_pay_num);
        }
        $redis->hSet('census', 'btc_pay_num', $btc_pay_num);

        $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $endToday = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
        //额度今日盈利
        $td_today_num = Db::name('game_btc')->where("time > " . $beginToday . " AND time < " . $endToday)->sum('r_money');
        $redis->hSet('census', 'btc_today_num', $td_today_num);
        //额度今日下注量
        $order = Db::name('game_btc')->where("time > " . $beginToday . " AND time < " . $endToday)->sum('money');
        $redis->hSet('census', 'td_btc_num', $order);

        //子币下注量
        $order = Db::name('game_eth')->sum('money');
        $redis->hSet('census', 'game_num_eth', $order);
        //平台子币盈利
        $order = Db::name('game_eth')->sum('r_money');
        if ($order > 0) {
            $order = '-' . $order;
        } else {
            $order = abs($order);
        }
        $redis->hSet('census', 'eth_pay_num', $order);
        //子币今日盈利
        $td_today_num = Db::name('game_eth')->where("time > " . $beginToday . " AND time < " . $endToday)->sum('r_money');
        $redis->hSet('census', 'eth_today_num', $td_today_num);
        //子币今日下注量
        $td_eth_num = Db::name('game_eth')->where("time > " . $beginToday . " AND time < " . $endToday)->sum('money');
        $redis->hSet('census', 'td_eth_num', $td_eth_num);
        $redis->hSet('census', 'game_refresh_time', time());
        return true;
    }

    public function return_game($type)
    {
        if ($type != 1 && $type != 2) {
            return ['code' => 2, 'msg' => '参数错误'];
        } else {
            try {
                Db::startTrans();
                $message = '';
                if ($type == 1) {//退还非当期未成功的BTC投注
                    $now = time();
                    $per = substr(date('Ymd', time()), 2, 6);//获取年月日
                    date('Ymd', $now);
                    $time = time() - strtotime(date('Y-m-d', time()));//今日凌晨时间戳
                    $per_now = intval($time / 600);
                    $per_num = $per * 1000 + $per_now;//该次期数
                    $per_num = $per_now == 0 ? intval(substr(date('Ymd', time() - 86400), 2, 6) . '144') : $per_num;
                    //查询非当期未开奖奖励
                    $res = Db::name('game_btc')->where('status = 0 and per_num !=' . $per_num)->select();
                    if (!empty($res)) {
                        $ins = array();
                        $up = array();
                        foreach ($res as $k => $v) {
                            //加钱
                            $money = Db::name('member_list')->where('id =' . $v['u_id'])->value('sell_limit');
                            if (!Db::name('member_list')->where('id =' . $v['u_id'])->setInc('sell_limit', $v['money'])) {
                                $message = '可售额度返还失败';
                                throw new \Exception($message);
                            }
                            if (!Db::name('game_btc')->where('id =' . $v['id'])->update(['status' => 2])) {
                                $message = '更新状态失败';
                                throw new \Exception($message);
                            }
                            //$up[] = ['id' => $v['id'], 'status' => 2];
                            $ins[] = [
                                'u_id' => $v['u_id'],
                                'o_id' => 0,
                                'bo_money' => $v['money'],
                                'former_money' => $money,
                                'type' => 413,
                                'message' => $per_num . '期游戏返还',
                                'bo_time' => time()
                            ];
                        }
//                        if (!Db::name('game_btc')->saveAll($up)) {
//                            $message = '更新状态失败';
//                            throw new \Exception($message);
//                        }
                        if (!Db::name('member_sell_limit_log')->insertAll($ins)) {
                            $message = '流水记录增加失败';
                            throw new \Exception($message);
                        }
                        Db::commit();
                        return ['code' => 1,'msg' => '返还成功'];
                    }else{
                        return ['code' => 1,'msg' => '暂无数据'];
                    }
                } elseif ($type == 2) {
                    $now = time();
                    $per = substr(date('Ymd', time()), 2, 6);//获取年月日
                    $time = time() - strtotime(date('Y-m-d', time()));//今日的时间戳
                    $per_now = floor(($time + 301) / 600);
                    $per_num = $per * 1000 + $per_now;//该次期数
                    //查询非当期未开奖奖励
                    $res = Db::name('game_eth')->where('status = 0 and per_num !=' . $per_num)->select();
                    if (!empty($res)) {
                        $ins = array();
                        $up = array();
                        foreach ($res as $k => $v) {
                            //加钱
                            $money = Db::name('member_list')->where('id =' . $v['u_id'])->value('coin');
                            if (!Db::name('member_list')->where('id =' . $v['u_id'])->setInc('coin', $v['money'])) {
                                $message = 'WGT返还失败';
                                throw new \Exception($message);
                            }
                            if (!Db::name('game_eth')->where('id =' . $v['id'])->update(['status' => 2])) {
                                $message = '更新状态失败';
                                throw new \Exception($message);
                            }
                            //$up[] = ['id' => $v['id'], 'status' => 2];
                            $ins[] = [
                                'u_id' => $v['u_id'],
                                'o_id' => 0,
                                'bo_money' => $v['money'],
                                'former_money' => $money,
                                'type' => 525,
                                'message' => $per_num . '期游戏返还',
                                'bo_time' => time()
                            ];
                        }
//                        if (!Db::name('game_eth')->saveAll($up)) {
//                            $message = '更新状态失败';
//                            throw new \Exception($message);
//                        }
                        if (!Db::name('member_coin_log')->insertAll($ins)) {
                            $message = '流水记录增加失败';
                            throw new \Exception($message);
                        }
                        Db::commit();
                        return ['code' => 1,'msg' => '返还成功'];
                    }else{
                        return ['code' => 1,'msg' => '暂无数据'];
                    }
                }
            } catch (\Exception $e) {
                Db::rollback();
                return ['code' => 2,'msg' => $message.$e->getMessage()];
            }
        }
    }
}