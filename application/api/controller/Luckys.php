<?php
namespace app\api\controller;

use think\Db;
use think\Request;
use app\api\model\MConfig;
use app\api\model\MMember;
use think\Exception;
use think\facade\Cache;

class Luckys extends Common
{
    //奖品列表
    public function turntable(){
        $MMember = new MMember();
        $u_id = $this->userinfo['user_id'];
        
        $membr_info = $MMember->getInfo(['id'=>$u_id],'luck_num');
        
        $list = Db::name('lucky')->field('id,msg')->select();
        foreach ($list as $k=>$v){
            $list[$k]['name'] = $v['msg'];
            unset($list[$k]['msg']);
        }
        
        return json(['code'=>1,'msg'=>'success','data'=>$list,'draw_num'=>$membr_info['luck_num']]);
    }
    
    
    /** 中奖纪录
     * @param Request $request
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function turntableLog(Request $request)
    {
        $MConfig = new MConfig();
        $u_id = $this->userinfo['user_id'];
        
        $MConfig_val = $MConfig->readConfig(['mainCurrency','auxiliaryCurrency'],2);
        
        $page = $request->param('page', 1);
        if ($page <= 0)    return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'参数错误']);
        $count = Db::name('lucky_log')->where('uid', $u_id)->count();
        $page_size = 10;
        $pages = ceil($count / $page_size);
        $offset = ($page - 1) * $page_size;
        $list = Db::name('lucky_log')
        ->where('uid', $u_id)
        ->order('time desc')
        ->limit($offset, $page_size)
        ->select();
        foreach ($list as $k => $v) {
            $list[$k]['time'] = date('Y-m-d H:i:s', $v['time']);
            $list[$k]['message'] = $v['msg'];
            unset($list[$k]['msg']);
            switch ($v['type_id']){
                case 1:$list[$k]['title'] = $MConfig_val[0];break;
                case 2:$list[$k]['title'] = $MConfig_val[1];break;
                case 3:$list[$k]['title'] = '推荐收益';break;
                case 4:$list[$k]['title'] = '团队收益';break;
                case 5:$list[$k]['title'] = '其他奖品';break;
            }
            unset($list[$k]['type']);
        }
        $data = [
            'count' => $count,
            'pages' => $pages,
            'list' => $list
        ];
        
        return json(['code' => 1, 'msg' => 'success', 'data'=>$data]);
    }
    
    
    /** 抽奖
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function luckDraw()
    {
        $MMember = new MMember();
        $u_id = $this->userinfo['user_id'];
        
        $member_info = $MMember->getInfo(['id'=>$u_id],'luck_num,id,tel,balance,coin,profit_recom,profit_team');
        
        $list = Db::name('lucky')->where(['status'=>1])->select();
        
        if($member_info['luck_num'] <= 0){
            return json(['code' => 2, 'msg' => '开启资格不足']);
        }
        
        if (Cache::get('luckDraw'.$u_id) == 2){
            return json(['code' => 2, 'msg' => '抽奖执行中', 'data'=>[]]);
        }
        Cache::set('luckDraw'.$u_id,2,5);
        
        $MConfig = new MConfig();
        $MConfig_val = $MConfig->readConfig(['mainCurrency','auxiliaryCurrency'],2);

        // if ($turntableCount >= 2 || ($appointCount <6 && $turntableCount > 0) || ($appointCount < 3))
        //     $this->error('开启资格不足');
        $arr_weight = array_column($list, 'weight');  
        $weight = array_sum($arr_weight);
        $round = mt_rand(1, $weight);//随机取出值
        $verify = 0;
        $reward_id = 0;
        for ($i = 0; $i < count($list); $i++) {
            if ($round > $verify && $round <= $verify + $list[$i]['weight']) {
                $reward_id = $list[$i]['id'];
                break;
            } else {
                $verify += $list[$i]['weight'];
            }
        }
        
        $status = 0;
        if($reward_id == 7){
            $status = 1;
        }

        try {
            Db::startTrans();
            $MMember->where('id',$u_id)->setDec('luck_num');
            $data = [];
            if ($reward_id > 0) {
                $turnInfo = Db::name('lucky')->where('id', $reward_id)->find();
                if (!$turnInfo) return json(['code' => 2, 'msg' => '奖品不存在']);
                if ($turnInfo['type'] == 1) { //主币
                    if ($turnInfo['num'] > 0) {
                        $MMember->where('id', $u_id)->setInc('balance', $turnInfo['num']);
                        Db::name('member_balance_log')->insert([
                            'u_id' => $member_info['id'],
                            'tel' => $member_info['tel'],
                            'o_id' => 0,
                            'former_money' => $member_info['balance'],
                            'change_money' => $turnInfo['num'],
                            'after_money' => $member_info['balance']+$turnInfo['num'],
                            'type' => 2,
                            'message' => '转盘获得'.$turnInfo['num'].$MConfig_val[0],
                            'bo_time' => time(),
                            'status' => 101,
                        ]);
                    }
                } elseif ($turnInfo['type'] == 2) {//辅币
                    if ($turnInfo['num'] > 0) {
                        $MMember->where('id', $u_id)->setInc('coin', $turnInfo['num']);
                        Db::name('member_balance_log')->insert([
                            'u_id' => $member_info['id'],
                            'tel' => $member_info['tel'],
                            'o_id' => 0,
                            'former_money' => $member_info['coin'],
                            'change_money' => $turnInfo['num'],
                            'after_money' => $member_info['coin']+$turnInfo['num'],
                            'type' => 11,
                            'message' => '转盘获得'.$turnInfo['num'].$MConfig_val[1],
                            'bo_time' => time(),
                            'status' => 205,
                        ]);
                    }
                } elseif ($turnInfo['type'] == 3) {//推荐收益
                    if ($turnInfo['num'] > 0) {
                        $MMember->where('id', $u_id)->setInc('profit_recom', $turnInfo['num']);
                        Db::name('member_balance_log')->insert([
                            'u_id' => $member_info['id'],
                            'tel' => $member_info['tel'],
                            'o_id' => 0,
                            'former_money' => $member_info['profit_recom'],
                            'change_money' => $turnInfo['num'],
                            'after_money' => $member_info['profit_recom']+$turnInfo['num'],
                            'type' => 5,
                            'message' => '转盘获得'.$turnInfo['num'].'推荐收益',
                            'bo_time' => time(),
                            'status' => 14,
                        ]);
                    }
                } elseif ($turnInfo['type'] == 4) {//团队收益
                    if ($turnInfo['num'] > 0) {
                        $MMember->where('id', $u_id)->setInc('profit_team', $turnInfo['num']);
                        Db::name('member_balance_log')->insert([
                            'u_id' => $member_info['id'],
                            'tel' => $member_info['tel'],
                            'o_id' => 0,
                            'former_money' => $member_info['profit_team'],
                            'change_money' => $turnInfo['num'],
                            'after_money' => $member_info['profit_team']+$turnInfo['num'],
                            'type' => 6,
                            'message' => '转盘获得'.$turnInfo['num'].'团队收益',
                            'bo_time' => time(),
                            'status' => 15,
                        ]);
                    }
                }
                Db::name('lucky_log')->insert([
                    'uid' => $member_info['id'],
                    'tel' => $member_info['tel'],
                    'lucky_id' => $turnInfo['id'],
                    'type_id' => $turnInfo['type'],
                    'msg' => $turnInfo['msg'],
                    'time' => time()
                ]);
                $data = ['id' => $turnInfo['id'],'type' => $turnInfo['type'],'num' => floatval($turnInfo['num']),'msg' => $turnInfo['msg'],'status'=>$status];
            }
            Db::commit();
            return json(['code' => 1,'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            Db::rollback();
            return json(['code' => 2,'msg' => 'error'. $exception->getMessage()]);
        }
        
    }
    
    
    
    
}

