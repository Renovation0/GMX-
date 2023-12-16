<?php
namespace app\api\model;

use think\Db;
use think\Exception;
use think\Config;

class MMember extends MCommon
{
    protected $table = 'zm_member_list'; 
    protected $table1 = 'zm_member_mutualaid';
    
    public function login_result($username, $password, $ip, $appid, $langer)
    {
        $member_info = $this->getInfo(['tel'=>$username],'id,tel,u_img,user,pass,status,sid');
        
        if($member_info){
            
            $MMemberLoginLog = new MMemberLoginLog();
            if($password != $member_info['pass']){
                 return json(['code' => 2, 'data' => [], 'msg' => getErrorInfo_new('PASSWORD_FAIL',$langer)]);//'密码错误'
            }
            if($member_info['status'] != 3){
                
                $token = $this->setToken();

                try {
                    Db::startTrans();

                    //添加登录login_toke
                    $map=[
                        'app_id'=>$appid,
                        'token'=>$token,
                        'exceed_time'=>time()+72000,
                        'use_id'=>$member_info['id']
                    ];
                    Db::name('login_token')->insert($map);
                    
                    $this->where(['id'=>$member_info['id']])->update(['last_ip'=>$ip,'last_time'=>getIndaiTime(time())]);
                    
                    $MMemberLoginLog->addLog($member_info['id'], $member_info['tel'], '登录成功', $ip, 1);//写入登录日志
                    
                    unset($member_info['pass']);
                    $member_info['member_id'] = $member_info['id'];
                    unset($member_info['id']);
                    $member_info['token'] = $token;
                    
                    Db::commit();
                    // if($langer == 'EN'){
                    //     return json(['code' => 1, 'msg' => getErrorInfo(SUCCESS), 'data' => $member_info]);//'登录成功'
                    // }else{
                    //     return json(['code' => 1, 'msg' => getErrorInfo(SUCCESS_IN), 'data' => $member_info]);
                    // }
                     return json(['code' => 1, 'msg' => getErrorInfo('SUCCESS',$langer), 'data' => $member_info]);
                } catch (Exception $e) {
                    Db::rollback();
                    return json(['code' => 2, 'data' => [], 'msg' => getErrorInfo('LOGIN_FAIL') . $e->getMessage()]);//'登录失败'
                   
                }
                
            }else{
                $MMemberLoginLog->addLog($member_info['id'], $member_info['tel'], '登录失败', $ip, 2);//写入登录日志
                // if($langer == 'EN'){
                //     return json(['code' => 2, 'data' => [], 'msg' => getErrorInfo(USER_LOCK)]);//'该账户已被锁定'
                // }else{
                //     return json(['code' => 2, 'msg' => getErrorInfo(USER_LOCK_IN)]);
                // }
                 return json(['code' => 2, 'data' => [], 'msg' => getErrorInfo_new("USER_LOCK",$langer)]);
            }
        }else{
            return json(['code' => 2, 'data' => [], 'msg' => getErrorInfo_new("USER_NBUND",$langer)]);
            // if($langer == 'EN'){
            //     return json(['code' => 2, 'data' => [], 'msg' => getErrorInfo(USER_NBUND)]);//'该用户不存在'
            // }else{
            //     return json(['code' => 2, 'msg' => getErrorInfo(USER_NBUND_IN)]);
            // }
        }
        
    }
    
    
    
    
    /**
     * 执行注册
     * @param unknown $password
     * @param unknown $pay_pass
     * @param unknown $mobile
     * @param unknown $guid
     * @param array $extend
     * @return boolean
     */
    public function register($mobile, $password, $pay_pass, $guid, $extend = [], $name, $langer)
    {
        $MMember = new MMember();
        $member_info = $MMember->getInfo(['tel'=>$mobile]);
        
        $MConfig = new MConfig();
        $config = $MConfig->readConfig(['DEFAULT_IMG', 'giveEnergy', 'mainCurrency','CLOSE','DEFAULT_USER','ipLimit'], 2);
/*         $DEFAULT_IMG_VAL = $MConfig->getValue(['key'=>'DEFAULT_IMG'], 'value');
        $giveEnergy_val = $MConfig->getValue(['key'=>'giveEnergy'], 'value');
        $auxiliary_val = $MConfig->getValue(['key'=>'auxiliaryCurrency'], 'value'); */
        if($config[3] != 1){
            return json(['code' => 2, 'msg' => getErrorInfo_new("NO_COUPON",$langer)]);
            // if($langer == 'EN'){
            //     return json(['code' => 2, 'msg' => getErrorInfo(NO_COUPON)]);//'请等待开放注册'
            // }else{
            //     return json(['code' => 2, 'msg' => getErrorInfo(NO_COUPON_IN)]);
            // }
        }
        
        // 检测用户名或邮箱、手机号是否存在  User::getByMobile($mobile)
        if ($mobile && $member_info['tel']) {
            return json(['code' => 2, 'msg' => getErrorInfo_new("USER_MOBILE_REPEAT",$langer)]);
            // if($langer == 'EN'){
            //     return json(['code' => 2, 'msg' => getErrorInfo(USER_MOBILE_REPEAT)]);//手机号码重复
            // }else{
            //     return json(['code' => 2, 'msg' => getErrorInfo(USER_MOBILE_REPEAT_IN)]);
            // }

        }
        //检查是否存在该推荐人
        $f_user = $MMember->where('guid', $guid)->field('id,tel,f_uid_all,deep')->find();
        if (is_null($f_user)) {
            return json(['code' => 2, 'msg' => getErrorInfo_new("NO_RECOMMENDER",$langer)]);//'推荐人不存在'
            // if($langer == 'EN'){
            //     return json(['code' => 2, 'msg' => getErrorInfo_new(NO_RECOMMENDER)]);//'推荐人不存在'
            // }else{
            //     return json(['code' => 2, 'msg' => getErrorInfo(NO_RECOMMENDER_IN)]);
            // }
        }
        $ip = request()->ip();
        $ip_count = $MMember->where('register_ip',$ip)->count();
        if ($ip_count >= $config[5]) {
            return json(['code' => 2, 'msg' => getErrorInfo_new("IP_LIMIT",$langer)]);
            // if($langer == 'EN'){
            //     return json(['code' => 2, 'msg' => getErrorInfo(IP_LIMIT)]);//'该IP注册用户已超过限制'
            // }else{
            //     return json(['code' => 2, 'msg' => getErrorInfo(IP_LIMIT_IN)]);
            // }
        }
        
        $time = time();
        $f_uid_all = $f_user['f_uid_all'] != '' && !is_null($f_user['f_uid_all']) ? $f_user['f_uid_all'] .','. $f_user['id'] : $f_user['id'];
        $deep = $f_user['deep'] + 1;
        
        //获取团队顶级ID
        $dids = explode(",",$f_user['f_uid_all']);
        $did = $dids[0];
        $sid = Db::name('system_user')->where('member_id',$did)->find();
        
        $ids = $MMember->where('1=1')->limit(1)->field('sid')->order('sid desc')->select();
        $str = rand(3,5);
        
        $data = [
            'sid' => $ids[0]['sid']+$str,
            'guid' => $this->getRand(6,true),
            'user' => $name,//$config[4],
            'tel' => $mobile,
            'level' => 0,
            'pass' => md5($password.'passwd'),
            'pay_pass' => md5($pay_pass.'pay_passwd'),
            'f_uid' => $f_user['id'],
            'f_tel' => $f_user['tel'],
            'f_uid_all' => $f_uid_all,
            'deep' => $deep,
            'time' => getIndaiTime(time()),
            'status' => 2,
            'u_img' => $config[0],
            'status' => 2,//$config_val
            'register_ip'=>$ip,
            'langer'=>'EN',
            'agent_name'=>$sid['username']
        ];
        //$member_id = Db::name('member_list')->insertGetId($data);
       
        //账号注册时需要开启事务,避免出现垃圾数据
        try {
            Db::startTrans();
            //团队人数
            $MMember->where('id', $f_user['id'])->update([
                'team' =>  Db::raw('team + 1')
            ]);//->setInc('zt_num');
            $MMember->where('id in (' . $f_uid_all . ')')->update([
                'team' =>  Db::raw('team + 1')
            ]);//->setInc('team');
            
            $member_id = $MMember->insertGetId($data);
            
            //注册赠送
            $giveEnergy = $config[1];//$giveEnergy_val;//赠送积分

            $data = [];
            if ($giveEnergy > 0) {
                Db::name('member_list')->where('id',$member_id)->update([
                    'balance' =>  Db::raw('balance + '.$giveEnergy)
                ]);//->setInc('balance',$giveEnergy);
                $data[] = [
                    'u_id' => $member_id,
                    'tel' => $mobile,
                    'change_money' => $giveEnergy,
                    'after_money' => $giveEnergy,
                    'message' => '注册赠送'.$config[2],//$auxiliary_val,
                    'message_e' => 'Register for gifts'.$config[2],//$auxiliary_val,
                    'type' => 2,
                    'bo_time' => getIndaiTime(time()),
                    'status' => 206
                ];
            }
            if (count($data) > 0) Db::name('member_balance_log')->insertAll($data);
            
            Db::commit();
            // if($langer == 'EN'){
            //     return json(['code' => 1, 'msg' => getErrorInfo(SUCCESS)]);
            // }else{
            //     return json(['code' => 1, 'msg' => getErrorInfo(SUCCESS_IN)]);
            // }
            return json(['code' => 1, 'msg' => getErrorInfo_new("SUCCESS",$langer)]);
        } catch (Exception $e) {
            Db::rollback();
            return json(['code' => 2, 'msg' => getErrorInfo_new("ADD_FAIL",$langer).$e->getMessage()]);
            // if($langer == 'EN'){
            //     return json(['code' => 2, 'msg' => getErrorInfo(ADD_FAIL).$e->getMessage()]);
            // }else{
            //     return json(['code' => 2, 'msg' => getErrorInfo(ADD_FAIL_IN)]);
            // }
        }
        return true;
    }

    

    
    /** 升级
     * @param $level /等级数组 id 从大到小
     * @param $uid
     * @param $userlevel
     * @param $pets_assets_history
     * @param $zt_user
     * @param $team_user
     * @return bool
     */
    public function uplevel($level, $uid, $userlevel, $pets_assets_history, $zt_user, $team_user)
    {
        foreach ($level as $k => $v) {
            if ($userlevel < $v['id']) {
                if (($pets_assets_history >= $v['pet_assets'])) {  // || ($zt_user > $v['direct_push'] && $team_user > $v['team_push'])
                    $this->where('id', $uid)->setField('level', $v['id']);
                    break;
                }
            }
        }
        return true;
    }

    
}

