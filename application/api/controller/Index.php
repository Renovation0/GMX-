<?php
namespace app\api\controller;

use app\api\model\MMember;
use think\Db;
use app\api\model\MMutualAid;
use app\api\model\MMutualAidLog;
use app\api\model\MMemberMutualaid;
use think\Request;
use think\Exception;
use app\api\model\MConfig;
use app\admin\model\MMemberLevel;
use think\facade\Cache;

class Index extends Common
{   
    //首页公告弹框
    public function indexNotice(){
        $info = Db::name('notice')->where('status = 1')->order('sort desc')->limit(1)->select();
        if(empty($info)){
            $data = [];
        }else{
            $text = $info[0]['n_text'];
            $text = str_replace("﹤","<",$text);
            $text = str_replace("﹥",">",$text);
            $text = str_replace("﹠","&",$text);
            $text = str_replace("﹔",";",$text);
            
            $data = [
                'title'=>$info[0]['n_title'],
                'description'=>$text
            ];
        }
        return json(['code' => 1, 'data'=>$data]);
    }
    //获取领取推广列表
    public function indexNoticeListnew(Request $request){       
        $list = Db::query("SELECT * FROM zm_mutualaid_examine where status=2  ORDER BY RAND() LIMIT 10");
        //$list = Db::name('mutualaid_examine')->where('status = 2')->order('id desc')->limit(10)->select();
        $MMember = new MMember();
        $u_id = $this->userinfo['user_id'];
        $info = $MMember->getInfo(['id'=>$u_id]);
       
        foreach ($list as $k => $v){
            $list[$k]['sta_time'] = date('Y-m-d H:i:s',$v['sta_time']);
            $user = Db::name('member_list')->where('id='.$list[$k]['uid'])->find();
            $list[$k]['name'] = $user['user'];
            $rand = rand(0,9);
            $randarr = ['63','77','83','84','85','86','93','95','98','99'];
            $user['tel'] = $user['tel']==''?$randarr[$rand].rand(10000000,99999999):$user['tel'];
            $list[$k]['tel'] = substr_replace($user['tel'],'****',3,5);
        }        
        shuffle($list);
        return json(['code' => 1, 'msg' => 'success', 'data'=>$list]);
    }
    
    //公告列表
    public function indexNoticeList(Request $request){    
        $type = $request->post('type');
        $list = Db::name('notice')->where('status = 1 and type='.$type)->order('sort desc')->field('n_id as id,n_title as title,n_text as text,description,time')->limit(5)->select();
        foreach ($list as $k => $v){
            $list[$k]['text'] = str_replace("﹤","<",$list[$k]['text']);
            $list[$k]['text'] = str_replace("﹥",">",$list[$k]['text']);
            $list[$k]['text'] = str_replace("﹠","&",$list[$k]['text']);
            $list[$k]['text'] = str_replace("﹔",";",$list[$k]['text']);
            $list[$k]['time'] = date('Y-m-d H:i:s',$v['time']);
        }        
        return json(['code' => 1, 'msg' => 'success', 'data'=>$list]);
    }
    //公告详情
    public function noticeinfo(Request $request){
        $id = $request->post('id');
        if(empty($id)){
            return json(['code' => 2, 'data'=>[], 'msg'=>'参数错误']);
        }
        
        $info = Db::name('notice')->where('n_id',$id)->field('n_title as title,n_text as content,time')->find();
        if(!empty($info)){
            $info['content'] = str_replace("﹤","<",$info['content']);
            $info['content'] = str_replace("﹥",">",$info['content']);
            $info['content'] = str_replace("﹠","&",$info['content']);
            $info['content'] = str_replace("﹔",";",$info['content']);
            
            $info['time'] = date('Y-m-d H:i:s',$info['time']);
        }
        return json(['code' => 1, 'msg' => 'success', 'data'=>$info]);
    }
    
    
    /*
     * 首页
     */
    public function index(){
        $MConfig = new MConfig();
        $MMember = new MMember();
        $u_id = $this->userinfo['user_id'];
        if(empty($u_id)){
            return json(['code'=>2,'data' =>[],'msg'=> getErrorInfo(USER_NOMO)]);
        }
        $info = $MMember->getInfo(['id'=>$u_id]);
        $config_val = $MConfig->readConfig(['DEFAULT_LEVEL_IMG','LUNBO_1','LUNBO_2','LUNBO_3','SubscribeTime'],2);
        
        $MMutualAid = new MMutualAid();
        $mu_list = $MMutualAid->getList('(status = 1 OR status = 3) AND level = 0','','sort desc');
        
        $MMemberLevel = new MMemberLevel();
        $level_list = $MMemberLevel->getList('1=1','id,level_logo');
        
        $HTTP_HOST = 'http://' . $_SERVER['HTTP_HOST'];
        
        foreach ($mu_list as $k => $v){
            $mu_list[$k]['logo'] = $HTTP_HOST.$v['logo'];
            $mu_list[$k]['reward'] = round($v['days'] * $v['price'] * $v['rate'] / 100, 2); //利润
            $mu_list[$k]['daily_ratio'] = round($v['price'] * $v['rate'] / 100, 2); //天利润
            $mu_list[$k]['issell'] = $mu_list[$k]['issellpurchaseNum']>=$mu_list[$k]['zpurchaseNum']?1:0; //是否售罄
        }
        
        $vip_mu_list = $MMutualAid->getList('(status = 1 OR status = 3) and level > 0','','sort desc');
        foreach ($vip_mu_list as $kk => $vv){
            $vip_mu_list[$kk]['logo'] = $HTTP_HOST.$vv['logo'];
            foreach ($level_list as $key => $val){
                if($vv['level'] == $val['id']){
                    $vip_mu_list[$kk]['level_logo'] = $HTTP_HOST.$val['level_logo'];
                    $vip_mu_list[$kk]['reward'] = round($vv['days'] * $vv['price'] * $vv['rate'] / 100, 2); //利润
                    $vip_mu_list[$kk]['daily_ratio'] = round($vv['price'] * $vv['rate'] / 100, 2); //天利润
                    $vip_mu_list[$kk]['issell'] = $vip_mu_list[$kk]['issellpurchaseNum']>=$vip_mu_list[$kk]['zpurchaseNum']?1:0; //是否售罄
                }
            }
        }
        
        $event_mu_list = $MMutualAid->getList('(status = 1 OR status = 3) and level = -1','','sort desc');
        foreach ($event_mu_list as $kkk => $vvv){
            $event_mu_list[$kkk]['logo'] = $HTTP_HOST.$vvv['logo'];
            $event_mu_list[$kkk]['reward'] = round($vvv['days'] * $vvv['price'] * $vvv['rate'] / 100, 2); //利润
            $event_mu_list[$kkk]['daily_ratio'] = round($vvv['price'] * $vvv['rate'] / 100, 2); //天利润
            $event_mu_list[$kkk]['issell'] = $event_mu_list[$kkk]['issellpurchaseNum']>=$event_mu_list[$kkk]['zpurchaseNum']?1:0; //是否售罄
        }
        
        $gvrp_info = Db::name('gvrp')->where('1 = 1')->find();
        
        $text = $gvrp_info['content'];
        $text = str_replace("﹤","<",$text);
        $text = str_replace("﹥",">",$text);
        $text = str_replace("﹠","&",$text);
        $text = str_replace("﹔",";",$text);
        $text = str_replace("/upload/ueditor",$HTTP_HOST."/upload/ueditor",$text);
        $gvrp_info['content'] = $text;
        
        $kf_list = Db::name('exchange')->where('1 = 1')->select();
        foreach ($kf_list as $kkk => $vvv){
            $kf_list[$kkk]['img'] = $HTTP_HOST.$vvv['img'];
        }
        
        $data = [
            'pt_product'=>$mu_list,
            'vip_product'=>$vip_mu_list,
            'event_mu_list'=>$event_mu_list,
            'kf'=>$kf_list,
            'time'=>time(),
            'company'=>$gvrp_info,
            'balance'=>$info['balance'],
            'rechange'=>$info['rechange_limit']
        ];
        
        return json(['code' => 1, 'msg' => 'success', 'data'=>$data]);
    }    
    
    //购买产品
    public function buy_now(Request $request){
        $MMember = new MMember();
        $u_id = $this->userinfo['user_id'];
        $member_info = $MMember->getInfo(['id'=>$u_id]);
        $id = $request->post('id');
        $num = abs($request->post('number'));
        $langer = $member_info['langer'];//$request->param('langer','EN');

        if(empty($num)){
            return json(['code' => 2,'data' =>[], 'msg' => getErrorInfo_new("PARAMETER_ERROR",$langer)]);
            // if($langer == 'EN'){
            //     return json(['code' => 2,'data' =>[], 'msg' => getErrorInfo(PARAMETER_ERROR)]);
            // }else{
            //     return json(['code' => 2, 'msg' => getErrorInfo(PARAMETER_ERROR_IN)]);//缺少必要参数
            // }
        }
            
        // if (Cache::get('buy_now'.$u_id) == 2){
        //     return json(['code' => 2, 'msg' => 'Order generation in progress...', 'data'=>[]]);
        // }
        // Cache::set('buy_now'.$u_id,2,30);    
        
        $MMutualAid = new MMutualAid();
        $mu_info = $MMutualAid->getInfo(['id'=>$id]);

        if(!empty($mu_info['level'])){
            if($member_info['level'] < $mu_info['level']){
                return json(['code' => 2,'data' =>[], 'msg' => getErrorInfo_new("USER_VIP_ISUSE",$langer)]);
                // if($langer == 'EN'){
                //     return json(['code' => 2,'data' =>[], 'msg' => getErrorInfo(USER_VIP_ISUSE)]);
                // }else{
                //     return json(['code' => 2, 'msg' => getErrorInfo(USER_VIP_ISUSE_IN)]);//您还未到当前VIP等级
                // }
            }
        }
        
        $count = Db::name('member_mutualaid')->where('uid = '.$u_id.' and purchase_id = '.$id)->count();
        if($num > $mu_info['purchaseNum'] || ($count+$num) > $mu_info['purchaseNum']){
            return json(['code' => 2,'data' =>[], 'msg' => getErrorInfo_new("USER_HEAD_GET",$langer)]);
            // if($langer == 'EN'){
            //     return json(['code' => 2,'data' =>[], 'msg' => getErrorInfo(USER_HEAD_GET)]);
            // }else{
            //     return json(['code' => 2, 'msg' => getErrorInfo(USER_HEAD_GET_IN)]);//已超过最大购买次数
            // }
        }
        //购买次数超过最大次数
        if(($mu_info['issellpurchaseNum']+$num)>$mu_info['zpurchaseNum']){
            return json(['code' => 2,'data' =>[], 'msg' => getErrorInfo_new("USER_HEAD_GET",$langer)]);
        }
        $total_price = $mu_info['price']*$num;
        
        $balance =$member_info['rechange_limit'];
        $status = 1;
        if($member_info['rechange_limit'] < $total_price){
            if($member_info['rechange_limit']+$member_info['balance'] < $total_price){
                return json(['code' => 2,'data' =>[], 'msg' => getErrorInfo_new("USER_BLANCE_NO",$langer)]);//余额不足
                // if($langer == 'EN'){
                //     return json(['code' => 2,'data' =>[], 'msg' => getErrorInfo(USER_BLANCE_NO)]);//余额不足
                // }else{
                //     return json(['code' => 2, 'msg' => getErrorInfo(USER_BLANCE_NO_IN)]);
                // }
            }else{
                $status = 2;
                $balance = $member_info['rechange_limit']+$member_info['balance'];
            }
        }
        $level = Db::name('member_level')->order('id desc')->select();
        
        $coin_money = $member_info['coin_money']+$total_price;
        
        //预约开始
        try {
            Db::startTrans();
 
            for ($i = 0; $i < $num; $i++){
                Db::name('member_mutualaid')->insert([
                    'uid' => $u_id,
                    'purchase_id' => $id,
                    'tel' => $member_info['tel'],
                    'get_price' => $mu_info['price'],
                    'new_price' => $mu_info['price'],
                    'days' => $mu_info['days'],
                    'rate' => $mu_info['rate'],
                    'deal_type' => 1,
                    'status' => 1,
                    'sta_time' => getIndaiTime(time())
                ]);
            }
            //修改已售份数
            Db::name('mutualaid_list')->where('id', $id)->update(['issellpurchaseNum'=>Db::raw('issellpurchaseNum +'.$num)]);
            
            
            if ($member_info['is_effective'] == 0) {
                $this->upEffective($member_info['id']);
            }
            
            foreach($level as $kk=> $vv){
                if($coin_money >= $vv['pet_assets'] && $vv['id'] > $member_info['level']){
                    Db::name('member_list')->where('id', $u_id)->update(['level'=>$vv['id']]);
                }
            }

            if($status == 1){
                Db::name('member_list')->where('id', $u_id)->update([
                    'rechange_limit'=>Db::raw('rechange_limit -'.$total_price),
                    'coin_money'=>Db::raw('coin_money +'.$total_price)
                ]);
                
                Db::name('member_balance_log')->insert([
                    'u_id' => $u_id,
                    'tel' => $member_info['tel'],
                    'former_money' => $member_info['rechange_limit'],
                    'change_money' => -$total_price,
                    'after_money' => $member_info['rechange_limit'] - $total_price,
                    'message' => '购买'.$mu_info['name'].','.$num.'个扣除'.$total_price,
                    'message_e' => 'Successful purchase '.$mu_info['name'].'*'.$num,
                    'type' => 1,
                    'bo_time' => time(),
                    'status' => 207
                ]);
            }else{
                $limit_price = $total_price-$member_info['rechange_limit'];
                
                Db::name('member_list')->where('id', $u_id)->update([
                    'balance'=>Db::raw('balance -'.$limit_price),
                    'rechange_limit'=>0,
                    'coin_money'=>Db::raw('coin_money +'.$total_price)
                ]);
                if($member_info['rechange_limit'] > 0){
                    Db::name('member_balance_log')->insert([
                        'u_id' => $u_id,
                        'tel' => $member_info['tel'],
                        'former_money' => $member_info['rechange_limit'],
                        'change_money' => -$member_info['rechange_limit'],
                        'after_money' => $member_info['rechange_limit'] - $member_info['rechange_limit'],
                        'message' => '购买'.$mu_info['name'].','.$num.'个扣除'.$member_info['rechange_limit'],
                        'message_e' => 'Successful purchase '.$mu_info['name'].'*'.$num,
                        'type' => 1,
                        'bo_time' => time(),
                        'status' => 207
                    ]);
                }
                
                Db::name('member_balance_log')->insert([
                    'u_id' => $u_id,
                    'tel' => $member_info['tel'],
                    'former_money' => $member_info['balance'],
                    'change_money' => -$limit_price,
                    'after_money' => $member_info['balance'] - $limit_price,
                    'message' => '购买'.$mu_info['name'].','.$num.'个扣除'.$limit_price,
                    'message_e' => 'Successful purchase '.$mu_info['name'].'*'.$num,
                    'type' => 2,
                    'bo_time' => time(),
                    'status' => 207
                ]);
            }
            
            $reward = $total_price;// * $mu_info['rate'] / 100 * $mu_info['days'];

            $data = [];
            if($mu_info['level'] > 0){
                $data = $this->VIPztReward($reward,$u_id,$data);
            }elseif($mu_info['level'] == 0){
                $data = $this->ztReward($reward,$u_id,$data);
            }else{
                $data = $this->hdReward($reward,$u_id,$data);
            }
            /*}else{
                $data = $this->ztReward($reward,$u_id,$data);
            }*/
            
            if (count($data) > 0)  $res = Db::name('member_balance_log')->insertAll($data);
            
            //setDec('energy', $purchase['energy']);
            Db::commit();
            return json(['code' => 1,'msg' => getErrorInfo_new("SUCCESS",$langer)]);
            // if($langer == 'EN'){
            //     return json(['code' => 1,'msg' => getErrorInfo(SUCCESS)]);
            // }else{
            //     return json(['code' => 1, 'msg' => getErrorInfo(SUCCESS_IN)]);
            // }
        } catch (Exception $exception) {
            Db::rollback();
             return json(['code' => 2,'msg' => getErrorInfo_new("ADD_FAIL",$langer).$exception->getMessage()]);
            // if($langer == 'EN'){
            //     return json(['code' => 2,'msg' => getErrorInfo(ADD_FAIL).$exception->getMessage()]);
            // }else{
            //     return json(['code' => 2, 'msg' => getErrorInfo(ADD_FAIL_IN)]);
            // }
        }
    }
    
        
    /**有效会员升级
     * @throws Exception
     */
    public function upEffective($uid)
    {
        $MMember = new MMember();
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig('effectiveUserAssets',1);
        $site_asstes = $config_val;//Config::get('site.effectiveUserAssets');
        $level = Db::name('member_level')->order('id desc')->select();
        $user = $MMember->where('id', $uid)->field('is_effective,f_uid,f_uid_all')->find();
        if ($user['is_effective'] == 0) {//第一次升级有效会员
            $MMember->where('id', $uid)->setField('is_effective', 1);
            if ($user['f_uid'] != 0) {
                //团队 及直推
                $MMember->where('id', $user['f_uid'])->setInc('zt_yx_num');
                $MMember->where('id in (' . $user['f_uid_all'] . ')')->setInc('yx_team');
                $team_list = $MMember->where('id in ('.$user['f_uid_all'].')')
                ->field('id,level,pets_assets_history,zt_yx_num,yx_team,level')->select();
                foreach ($team_list as $k => $v) {//判断
                    $MMember->upLevel($level, $v['id'], $v['level'], $v['pets_assets_history'], $v['zt_yx_num'], $v['yx_team']);
                }
            }
        }
    }
    
    
    //VIP直推奖励 三级收益
    public function VIPztReward($all_profit, $uid, $data)
    {   
        $MConfig = new MConfig();
        $config_vals = $MConfig->readConfig(['ztOneRateVIP','ztTwoRateVIP','ztThreeRateVIP','effectiveUserAssets'],2);
        $configs['ztOneRate'] = $config_vals[0];//Config::get('site.ztOneRate');
        $configs['ztTwoRate'] = $config_vals[1];//Config::get('site.ztTwoRate');
        $configs['ztThreeRate'] = $config_vals[2];//Config::get('site.ztThreeRate');

        //上级奖励
        $f_uid_all = Db::name('member_list')->where('id', $uid)->value('f_uid_all');
        $user_tel = Db::name('member_list')->where('id', $uid)->value('tel');
        if ($f_uid_all != '') {
            $f_user = Db::name('member_list')->where('id in (' . $f_uid_all . ')')->field('id,tel,balance,level')->order('id desc')->limit(3)->select();
            for ($i = 0; $i < count($f_user); $i++) {
                if ($i == 0) {//diy第一代
                    // if($f_user[$i]['level'] != 0){
                    //     $one_era = Db::name('member_level')->where(['id'=>$f_user[$i]['level']])->value('one_era');
                    //     $rate = $one_era;//$config['ztOneRate'];
                    // }else{
                        $rate = $configs['ztOneRate'];
                    //}
                    $str = '直推';
                    $str_e = 'Get Level 1 reward ';
                } elseif ($i == 1) {
                    // if($f_user[$i]['level'] != 0){
                    //     $two_era = Db::name('member_level')->where(['id'=>$f_user[$i]['level']])->value('two_era');
                    //     $rate = $two_era;//$config['ztTwoRate'];
                    // }else{
                        $rate = $configs['ztTwoRate'];
                    //}
                    $str = '间推';
                    $str_e = 'Get Level 2 reward ';
                } else {
                    // if($f_user[$i]['level'] != 0){
                    //     $three_era = Db::name('member_level')->where(['id'=>$f_user[$i]['level']])->value('three_era');
                    //     $rate = $three_era;//$config['ztThreeRate'];
                    // }else{
                        $rate = $configs['ztThreeRate'];
                    //}
                    $str = '三级';
                    $str_e = 'Get Level 3 reward ';
                }

                $user = Db::name('member_list')->where('id', $f_user[$i]['id'])->field('id,tel,balance')->find();

                    $reward = $all_profit * $rate / 100;
                    if ($reward > 0) {
                        Db::name('member_list')->where('id', $user['id'])->update([
                            'balance' => Db::raw('balance +' .$reward)
                            ]);
                        $data[] = [
                            'u_id' => $user['id'],
                            'tel' => $user['tel'],
                            'former_money' => $user['balance'],
                            'change_money' => $reward,
                            'after_money' => $user['balance'] + $reward,
                            'message' => $str.'收益,--'.substr_replace($user_tel, '****', 3, 4),
                            'message_e' => $str_e.' profit',
                            'type' => 2,
                            'bo_time' => time(),
                            'status' => 502
                        ];
                    }
                //}
            }
        }
        return $data;
    }
    
    //直推奖励 三级收益
    public function ztReward($all_profit, $uid, $data)
    {
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig(['ztOneRate','ztTwoRate','ztThreeRate','effectiveUserAssets'],2);
        $config['ztOneRate'] = $config_val[0];//Config::get('site.ztOneRate');
        $config['ztTwoRate'] = $config_val[1];//Config::get('site.ztTwoRate');
        $config['ztThreeRate'] = $config_val[2];//Config::get('site.ztThreeRate');

        //上级奖励
        $f_uid_all = Db::name('member_list')->where('id', $uid)->value('f_uid_all');
        $user_tel = Db::name('member_list')->where('id', $uid)->value('tel');
        if ($f_uid_all != '') {
            $f_user = Db::name('member_list')->where('id in (' . $f_uid_all . ')')->field('id,tel,balance,level')->order('id desc')->limit(3)->select();
            for ($i = 0; $i < count($f_user); $i++) {
                if ($i == 0) {//diy第一代
                    // if($f_user[$i]['level'] != 0){
                    //     $one_era = Db::name('member_level')->where(['id'=>$f_user[$i]['level']])->value('one_era');
                    //     $rate = $one_era;//$config['ztOneRate'];
                    // }else{
                        $rate = $config['ztOneRate'];
                    //}
                    $str = '直推';
                    $str_e = 'Get Level 1 reward ';
                } elseif ($i == 1) {
                    // if($f_user[$i]['level'] != 0){
                    //     $two_era = Db::name('member_level')->where(['id'=>$f_user[$i]['level']])->value('two_era');
                    //     $rate = $two_era;//$config['ztTwoRate'];
                    // }else{
                        $rate = $config['ztTwoRate'];
                    //}
                    $str = '间推';
                    $str_e = 'Get Level 2 reward ';
                } else {
                    // if($f_user[$i]['level'] != 0){
                    //     $three_era = Db::name('member_level')->where(['id'=>$f_user[$i]['level']])->value('three_era');
                    //     $rate = $three_era;//$config['ztThreeRate'];
                    // }else{
                        $rate = $config['ztThreeRate'];
                    //}
                    $str = '三级';
                    $str_e = 'Get Level 3 reward ';
                }

                $user = Db::name('member_list')->where('id', $f_user[$i]['id'])->field('id,tel,balance')->find();
                $reward = $all_profit * $rate / 100;
 
                if ($reward > 0) {
                    Db::name('member_list')->where('id', $user['id'])->update([
                        'balance' => Db::raw('balance +' .$reward)
                    ]);
                    $data[] = [
                        'u_id' => $user['id'],
                        'tel' => $user['tel'],
                        'former_money' => $user['balance'],
                        'change_money' => $reward,
                        'after_money' => $user['balance'] + $reward,
                        'message' => $str.'收益,--'.substr_replace($user_tel, '****', 3, 4),
                        'message_e' => $str_e.' profit',
                        'type' => 2,
                        'bo_time' => time(),
                        'status' => 502
                    ];
                }
            }
        }
        return $data;
    }
    
        //活动产品奖励 三级收益
    public function hdReward($all_profit, $uid, $data)
    {
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig(['activityOne','activityTwo','activityThree','effectiveUserAssets'],2);
        $config['ztOneRate'] = $config_val[0];//Config::get('site.ztOneRate');
        $config['ztTwoRate'] = $config_val[1];//Config::get('site.ztTwoRate');
        $config['ztThreeRate'] = $config_val[2];//Config::get('site.ztThreeRate');
        
        //上级奖励
        $f_uid_all = Db::name('member_list')->where('id', $uid)->value('f_uid_all');
        $user_tel = Db::name('member_list')->where('id', $uid)->value('tel');
        if ($f_uid_all != '') {
            $f_user = Db::name('member_list')->where('id in (' . $f_uid_all . ')')->field('id,tel,balance,level')->order('id desc')->limit(3)->select();
            for ($i = 0; $i < count($f_user); $i++) {
                if ($i == 0) {//diy第一代
                    // if($f_user[$i]['level'] != 0){
                    //     $one_era = Db::name('member_level')->where(['id'=>$f_user[$i]['level']])->value('one_era');
                    //     $rate = $one_era;//$config['ztOneRate'];
                    // }else{
                    $rate = $config['ztOneRate'];
                    //}
                    $str = '直推';
                    $str_e = 'Get Level 1 reward ';
                } elseif ($i == 1) {
                    // if($f_user[$i]['level'] != 0){
                    //     $two_era = Db::name('member_level')->where(['id'=>$f_user[$i]['level']])->value('two_era');
                    //     $rate = $two_era;//$config['ztTwoRate'];
                    // }else{
                    $rate = $config['ztTwoRate'];
                    //}
                    $str = '间推';
                    $str_e = 'Get Level 2 reward ';
                } else {
                    // if($f_user[$i]['level'] != 0){
                    //     $three_era = Db::name('member_level')->where(['id'=>$f_user[$i]['level']])->value('three_era');
                    //     $rate = $three_era;//$config['ztThreeRate'];
                    // }else{
                    $rate = $config['ztThreeRate'];
                    //}
                    $str = '三级';
                    $str_e = 'Get Level 3 reward ';
                }
                
                $user = Db::name('member_list')->where('id', $f_user[$i]['id'])->field('id,tel,balance')->find();
                $reward = $all_profit * $rate / 100;
                
                if ($reward > 0) {
                    Db::name('member_list')->where('id', $user['id'])->update([
                        'balance' => Db::raw('balance +' .$reward)
                    ]);
                    $data[] = [
                        'u_id' => $user['id'],
                        'tel' => $user['tel'],
                        'former_money' => $user['balance'],
                        'change_money' => $reward,
                        'after_money' => $user['balance'] + $reward,
                        'message' => $str.'收益,--'.substr_replace($user_tel, '****', 3, 4),
                        'message_e' => $str_e.' profit',
                        'type' => 2,
                        'bo_time' => time(),
                        'status' => 502
                    ];
                }
            }
        }
        return $data;
    }
    
    
    
    
}

