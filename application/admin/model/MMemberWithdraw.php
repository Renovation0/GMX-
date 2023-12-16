<?php
namespace app\admin\model;

use think\Db;

class MMemberWithdraw extends MCommon
{
    public $table="zm_member_bm_withdraw";
    
    // 获取会员列表
    public function getLists($where, $pageSize, $allParams, $order, $field='*')
    {
        $list = $this//->alias('a')
        //->join('zm_member_level b','a.level=b.id', 'LEFT')
        ->where($where)
        //->whereIn('a.status', [1, 2])
        ->order($order)->field($field)
        ->paginate($pageSize, false, $allParams);
        return $list;
    }
    
    /**
     * 生成随机字符串
     * @param integer $length 随机字符串长度
     * @param bool $numeric 是否生成数字串
     * @return string          返回的字符串
     */
    private function getRand($length = 8, $numeric = false)
    {
        $str = "0 1 2 3 4 5 6 7 8 9 q w e r t y u i o p a s d f g h j k l z x c v b n m Q W E R T Y U I O P A S D F G H J K L Z X C V B N M";
        if ($numeric) {
            $str = "0 1 2 3 4 5 6 7 8 9";
        }
        $arr = explode(' ', $str);
        shuffle($arr);
        $str = implode('', $arr);
        $res = substr($str, 0, $length);
        if ($this->where('guid', $res)->count() == 0) {
            return $res;
        } else {
            return $this->getRand($length, $numeric);
        }
    }
    
    
    //添加会员
    public function add_member($name, $tel, $p_tel, $pass, $pay_pass)
    {
        $u_id = $this->max('id') + 1;
        $guid = $this->getRand();
        $Com = new SystemModule();
        $img = $Com->readConfig('DEFAULT_IMG');
        if ($p_tel != '') {//有推荐人
            $p_info = $this->where("`tel` = '". $p_tel."'")->field('id,f_uid_all,deep')->find();  
            if(empty($p_info)){
                return false;
            }
            $f_uid_all = $p_info['f_uid_all'] != '' && !is_null($p_info['f_uid_all']) ? $p_info['f_uid_all'] .','. $p_info['id'] : $p_info['id'];
            $deep = $p_info['deep'] + 1;
            
            $insertId = $this->insert([
                'id' => $u_id,
                'guid' => $guid,
                'user' => $name,
                'tel' => $tel,
                'u_img' => $img,
                'f_uid' => $p_info['id'],
                'f_uid_all' => $f_uid_all,
                'f_tel' => $p_tel,
                'deep' => $deep,
                'pass' => md5(md5($pass).'passwd'),//md5($pass)
                'pay_pass' => md5(md5($pay_pass).'pay_passwd'),
                'time' => time()
            ]);
            
        } else {//无推荐人
            $insertId = $this->insert([
                'id' => $u_id,
                'guid' => $guid,
                'user' => $name,
                'tel' => $tel,
                'u_img' => $img,
                'f_tel' => 0,
                'f_uid_all' => '',
                'pass' => md5(md5($pass).'passwd'),
                'pay_pass' => md5(md5($pay_pass).'pay_passwd'),
                'time' => time()
            ]);
        }
        
        if ($insertId) {
            if ($p_tel != ''){
                //父级直推+1
                $res = $this->where("`tel` = '". $p_tel."'")->setInc('zt_num', 1);
                if(!$res){
                    return false;
                }
                
                // 团队人数 + 1
                $res1 = $this->where('id', 'in', $f_uid_all)->setInc('team');
                if(!$res){
                    return false;
                }
            }
            return true;
        }else{
            return false;
        }

    }
    
    // 修改会员信息
    public function editMember($id, $data)
    {
        $updateData['user'] = $data['user'];
        $updateData['u_img'] = $data['u_img'];
        $updateData['cardImg1'] = $data['cardImg1'];
        $updateData['cardImg2'] = $data['cardImg2'];
        $updateData['real_name_status'] = $data['real_name_status'];
        $updateData['purchase_status'] = $data['purchase_status'];
        $updateData['status'] = $data['status'];
        $updateData['level'] = $data['level'];
        if ($data['user']) {
            $updateData['user'] = $data['user'];
        }
        if ($data['u_img']) {
            $updateData['u_img'] = $data['u_img'];
        }
        if ($data['cardImg1']) {
            $updateData['cardImg1'] = $data['cardImg1'];
        }
        if ($data['cardImg2']) {
            $updateData['cardImg2'] = $data['cardImg2'];
        }
        if ($data['real_name_status']) {
            $updateData['real_name_status'] = $data['real_name_status'];
        }
        if ($data['purchase_status']) {
            $updateData['purchase_status'] = $data['purchase_status'];
        }
        if ($data['status']) {
            $updateData['status'] = $data['status'];
        }
        if ($data['level']) {
            $updateData['level'] = $data['level'];
        }
        if ($data['pass']) {
            $updateData['pass'] = md5(md5($data['pass']).'passwd');//md5($data['pass'])
        }
        if ($data['pay_pass']) {
            $updateData['pay_pass'] = md5(md5($data['pay_pass']).'pay_passwd');//md5(md5($data['pay_pass']).'pay_pass')
        }

        // 执行修改
        if ($this->where('id', $id)->update($updateData)) {
            return json(['code' => 1, 'msg' => '修改成功']);
        } else {
            return json(['code' => 3, 'msg' => '修改失败', 'info' => $this->getlastsql()]);
        }
    }
    
    
   
    // 修改会员状态
    public function statusMember($id, $status)
    {
        if ($this->where('id', $id)->count() > 0) {
            if ($this->where('id', $id)->value('status') == 4) {
                return json(['code' => 2, 'msg' => '此会员已经被永久封号，操作失败']);
            }
            if ($this->where('id', $id)->value('status') == $status) {
                if ($status == 2) {
                    return json(['code' => 2, 'msg' => '此会员已经启用，操作失败']);
                } else if ($status == 3) {
                    return json(['code' => 2, 'msg' => '此会员已经冻结，操作失败']);
                } else {
                    return json(['code' => 1, 'msg' => '此会员已是未激活，操作失败']);
                }
            }
            if ($this->where('id', $id)->setField('status', $status)) {
                if ($status == 2) {
                    $this->where('id', $id)->update(['first_blood' => $status,'partner_time' => time()]);
                    return json(['code' => 1, 'msg' => '启用成功']);
                } else if ($status == 3){
                    return json(['code' => 1, 'msg' => '冻结成功']);
                } else{
                    return json(['code' => 1, 'msg' => '恢复未激活成功']);
                }
            } else {
                if ($status == 2) {
                    return json(['code' => 1, 'msg' => '启用失败，请联系服务提供商']);
                } else if ($status == 3){
                    return json(['code' => 1, 'msg' => '冻结失败，请联系服务提供商']);
                } else{
                    return json(['code' => 1, 'msg' => '恢复未激活失败，请联系服务提供商']);
                }
            }
            
        } else {
            return json(['code' => 2, 'msg' => '无指定角色，修改失败']);
        }
    }
    
    // 修改会员状态
    public function controlMember($id, $status)
    {
        if ($this->where('id', $id)->count() > 0) {
            if ($this->where('id', $id)->value('status') == 4) {
                return json(['code' => 2, 'msg' => '此会员已经被永久封号，操作失败']);
            }
            if ($this->where('id', $id)->value('control_sell') == $status) {
                if ($status == 1) {
                    return json(['code' => 2, 'msg' => '此会员已被控制，操作失败']);
                } else {
                    return json(['code' => 2, 'msg' => '此会员已被启用，操作失败']);
                }
            }
            if ($this->where('id', $id)->setField('control_sell', $status)) {
                if ($status == 1) {
                    return json(['code' => 1, 'msg' => '控制成功']);
                } else {
                    return json(['code' => 1, 'msg' => '解控成功']);
                }
            } else {
                if ($status == 1) {
                    return json(['code' => 3, 'msg' => '控制失败，请联系服务提供商']);
                } else {
                    return json(['code' => 3, 'msg' => '解控失败，请联系服务提供商']);
                }
            }
            
        } else {
            return json(['code' => 2, 'msg' => '无指定角色，修改失败']);
        }
    }
    
    // 修改
    public function Privilege($id, $status)
    {
        if ($this->where('id', $id)->count() > 0) {
            if ($this->where('id', $id)->value('purchase_status') == $status) {
                if ($status == 2) {
                    return json(['code' => 2, 'msg' => '已是随机，操作失败']);
                } else if ($status == 3) {
                    return json(['code' => 2, 'msg' => '已是必不中，操作失败']);
                } else {
                    return json(['code' => 1, 'msg' => '已是必中，操作失败']);
                }
            }
            if ($this->where('id', $id)->setField('purchase_status', $status)) {
                if ($status == 2) {
                    return json(['code' => 1, 'msg' => '成功']);
                } else if ($status == 3){
                    return json(['code' => 1, 'msg' => '成功']);
                } else{
                    return json(['code' => 1, 'msg' => '成功']);
                }
            } else {
                if ($status == 2) {
                    return json(['code' => 1, 'msg' => '切换失败，请联系服务提供商']);
                } else if ($status == 3){
                    return json(['code' => 1, 'msg' => '切换失败，请联系服务提供商']);
                } else{
                    return json(['code' => 1, 'msg' => '切换失败，请联系服务提供商']);
                }
            } 
            
        } else {
            return json(['code' => 2, 'msg' => '无指定角色，修改失败']);
        }
    }
    
    // 删除会员
    public function deleteMember($id)
    {
        if ($this->where('id', $id)->count() > 0) {
            if ($this->where('id', $id)->value('status') == 4) {
                return json(['code' => 2, 'msg' => '角色已经被删除，操作失败']);
            }
            if ($this->where('id', $id)->setField('status', 4)) {
                return json(['code' => 1, 'msg' => '删除成功']);
            } else {
                return json(['code' => 3, 'msg' => '删除失败，请联系服务提供商']);
            }
        } else {
            return json(['code' => 2, 'msg' => '无指定角色，删除失败']);
        }
    }
    
    
    // 获取会员详情
    public function getDetail($where){
        $MMemberLevel = new MMemberLevel();
        
        $list = $this->alias('l')
        ->leftJoin('member_census c', 'l.id = c.u_id')
        ->where($where)
        ->order('l.id desc')
        ->select();
        
        
        if ($list) {
            $level = $MMemberLevel->getList('','id,name');
            foreach ($level as $key => $val) {
                if ($val['id'] == $list[0]['level']) {
                    $list[0]['level_name'] = $val['name'];
                }
            }
            if(!isset($list[0]['level_name'])){
                $list[0]['level_name'] = "未知";
            }
            
            return $list[0];
        }
        return $list;
    }
    
    
    //首次实名赠送
    public function realName($id){
        $MActivityRealname = new MActivityRealname();
        $MActivityRealnameLog = new MActivityRealnameLog();
        $MMachineLog = new MMachineLog();
        $MMachineOrder = new MMachineOrder();
        $MMachineManage = new MMachineManage();
        $time = time();
        
        $MMember = new MMember();
        $info = $MMember->getInfo(['id'=>$id],'f_uid');
        
        $real_info = $MActivityRealname->getInfo();
        if($real_info['status'] == 1){
            if ($time > $real_info['begin_time'] && $time < $real_info['end_time']){
                for ($i = 1; $i <= $real_info['mac_num']; $i++) {
                    $mach_info = $MMachineManage->getInfo(['id'=>$real_info['mac_id']]);                    
                    $mach_data = [
                        'mac_id'=>$mach_info['id'],
                        'u_id'=>$id,
                        'num'=>0,
                        's_cycle'=>$mach_info['cycle'],
                        'cycle'=>$mach_info['cycle'],
                        'hour_output'=>$mach_info['hour_output'],
                        'r_output'=>$mach_info['all_output'],
                        'time'=>time(),
                        //'e_time' => time() + $mach_info['cycle'] * 3600,
                        'e_time' => time() + $mach_info['cycle'] * 86400,
                        'status'=>1,
                        'is_giving'=>1
                    ];
                    $MMachineOrder->addActivity($mach_data);
                    
                    $mach_log_data=[
                        'mac_id'=>$mach_info['id'],
                        'u_id'=>$id,
                        'price'=>0,
                        'message'=>'实名赠送'.$mach_info['name'],
                        'time'=>time(),
                        'status'=>1,
                        'num'=>1
                    ];
                    $MMachineLog->addActivity($mach_log_data);
                    
                    //添加实名活动日志
                    $data=[
                        'u_id'=>$id,
                        'a_id'=>$real_info['id'],
                        'message'=>'实名奖励',
                        'time'=>time(),
                        'status'=> 1,
                        'by_uid'=> $info['f_uid']
                    ];
                    $MActivityRealnameLog->addActivity($data);
                    
                }
                
                //上级开启同样矿机奖励
                if($real_info['superior'] == 1){
                    for ($i = 1; $i <= $real_info['mac_num']; $i++) {
                        $mach_info = $MMachineManage->getInfo(['id'=>$real_info['mac_id']]);
                        $mach_data = [
                            'mac_id'=>$mach_info['id'],
                            'u_id'=>$info['f_uid'],
                            'num'=>0,
                            's_cycle'=>$mach_info['cycle'],
                            'cycle'=>$mach_info['cycle'],
                            'hour_output'=>$mach_info['hour_output'],
                            'r_output'=>$mach_info['all_output'],
                            'time'=>time(),
                            'e_time' => time() + $mach_info['cycle'] * 86400,
                            'status'=>1,
                            'is_giving'=>1
                        ];
                        $MMachineOrder->addActivity($mach_data);
                        
                        $mach_log_data=[
                            'mac_id'=>$mach_info['id'],
                            'u_id'=>$info['f_uid'],
                            'price'=>0,
                            'message'=>'下级实名赠送'.$mach_info['name'],
                            'time'=>time(),
                            'status'=>1,
                            'num'=>1
                        ];
                        $MMachineLog->addActivity($mach_log_data);
                        
                        //添加实名活动日志
                        $data=[
                            'u_id'=>$info['f_uid'],
                            'a_id'=>$real_info['id'],
                            'message'=>'下级实名奖励',
                            'time'=>time(),
                            'status'=> 2,
                            'by_uid'=> $info['f_uid']
                        ];
                        $MActivityRealnameLog->addActivity($data);
                        
                        
                    }
                }
            }

            
            $user = $this->getinfo(['id'=>$id],'mac_assets,eff_machine');
            $f_user = $this->getinfo(['id'=>$info['f_uid']],'eff_machine');
            $user_data = [
                'mac_assets'=>$user['mac_assets']+$real_info['assets'],
                'eff_machine'=>$user['eff_machine']+$real_info['mac_num'],
                'real_name_status'=>1,
                'real_name_time'=>time()
            ];
            $this->where(['id'=>$id])->update($user_data);
            $this->where(['id'=>$info['f_uid']])->update(['eff_machine'=>$f_user['eff_machine']+$real_info['mac_num']]);
            $MMemberMacAssetsLog = new MMemberMacAssetsLog();
            $data=[
                'u_id'=>$id,
                'bo_money'=>$real_info['assets'],
                'former_money'=>$user['mac_assets'],
                'message'=>'实名奖励',
                'bo_time'=>time(),
                'type'=>207,
                'status'=> 1
            ];
            $MMemberMacAssetsLog->addActivity($data);
        }else{
            $user_data = [
                'real_name_status'=>1,
                'real_name_time'=>time()
            ];
            $this->where(['id'=>$id])->update($user_data);
        }
        return true;
    }
    
    

    // 获取用户的信息
    public function getMemberValue($where, $filed = '*')
    {
        return $this->where($where)->field($filed)->find();
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
                if ($pets_assets_history >= $v['pet_assets'] && $zt_user > $v['direct_push'] && $team_user > $v['team_push']) {
                    $this->where('id', $uid)->setField('level', $v['id']);
                    break;
                }
            }
        }
        return true;
    }

    //会员统计
    public function memberCensus(){
/*         $beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $beginYesterday = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
        $endYesterday = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
        
         //昨日新增会员
         $member_yesterday = $this->where("time > ".$beginYesterday." AND time < ".$endYesterday)->count();
         $redis->hSet('census','member_yesterday',$member_yesterday);
         //今日新增会员
         $member_today = $this->where("time > ".$beginToday." AND time < ".$endToday)->count();*/
        
        //会员总数
        $member_num = $this->where('')->count();
        
        //已实名人数
        $member_ral_name_num = $this->where('real_name_status = 1')->count();
        
        //已激活会员
        $member_activation_num = $this->where('status = 2')->count();
        
        //已禁用会员
        $member_disabled_num = $this->where('status = 3')->count();
        
        //YKB总额
        $member_ykb = $this->sum('balance');
        
        //激活币总额
        $member_jhb = $this->sum('coin');
        
        //收益总额
        $member_reward_census = $this->sum('reward_census');
        
        $data = [
            'member_num' =>  $member_num,
            'member_ral_name_num' =>  $member_ral_name_num,
            'member_activation_num' =>  $member_activation_num,
            'member_disabled_num' =>  $member_disabled_num,
            'member_ykb' =>  $member_ykb,
            'reward_census' =>  $member_reward_census,
            'member_jhb' =>  $member_jhb,
        ];
        
        return $data;
    }

}

