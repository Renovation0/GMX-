<?php
namespace app\admin\controller;

use app\admin\model\MCommon;
use app\admin\model\MMemberLevel;
use app\admin\model\MMachineManage;
use app\admin\model\OrderList;
use app\admin\model\SystemConfig;
use think\Exception;
use think\Request;
use app\admin\model\MMember;
use think\Db;
use think\facade\Session;
use think\facade\Validate;
use app\admin\model\MMachineOrder;
use app\admin\model\MMemberSellLimitLog;
use app\admin\model\MMemberMacAssetsLog;
use app\admin\model\MMemberMacWalletLog;
use app\admin\model\MMemberBalanceLog;
use app\admin\model\MMemberCoinLog;
use app\admin\model\RealName;
use app\admin\model\PayBind;
use app\admin\model\MMemberMutual;
use think\facade\Cache;
use app\admin\model\JXB;
use app\api\model\MConfig;
use PHPExcel_IOFactory;
use PHPExcel;
require_once 'vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
require_once 'vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php';

class Member extends Check
{   
    // 会员列表
    public function memberList(Request $request)
    {

        $status = intval($request->param('status', -1)); // 使用状态
        $real_status = intval($request->param('real_status', -1)); // 实名状态
        $purchase_status = intval($request->param('purchase_status', -1)); // 抢购状态
        $tel = trim($request->param('tel', '')); // 电话号码
        $ip_address = $request->param('ip_address', ''); // ip
        $id = $request->param('uid', ''); // ip
        $referrer = $request->param('referrer', ''); // ip
        $level = intval($request->param('level', 0)); // 等级

        $sort = $request->param('sort', '');
        
        $order_by = $request->param('order_by', ''); //排序
        $allParams = ['query' => $request->param()];
        $this->assign('ip_address', $ip_address);
        $this->assign('uid', $id);
        $this->assign('param_status', $status);
        $this->assign('real_status', $real_status);
        $this->assign('purchase_status', $purchase_status);
        $this->assign('level', $level);
        $this->assign('param_name', $tel);
        $this->assign('sort', $sort);
        $this->assign('referrer', $referrer);
        
        $MMember = new MMember();
        $f_uid = $MMember->getInfo(['tel'=>$referrer],'id');
       
        $MMemberLevel = new MMemberLevel();
        $levellist = $MMemberLevel->getList();
        $this->assign('levellist', $levellist);
        $pageSize = intval($request->param('limit', 10));; // 分页大小
        $this->assign('pageSize', $pageSize);
        $condition = [];
        //$condition = '1=1';
        if(!empty($tel)){
            $condition[] = ['a.tel','like','%'.$tel.'%'];
            //$condition['a.tel'] = ['like','%' . $tel . '%'];
            //$condition .= ' AND a.tel ';['like','%' . $tel . '%'];
        }
        if(!empty($f_uid)){
            $condition['a.f_uid'] = $f_uid['id'];
        }
        if($status != -1){
            $condition['a.status'] = $status;
        }
        if($real_status != -1){
            $condition['a.real_name_status'] = $real_status;
        }
        if($purchase_status != -1){
            $condition['a.purchase_status'] = $purchase_status;
        }
        if($ip_address != ''){
            $condition['a.last_ip'] = $ip_address;
        }
        if($id != ''){
            $condition['a.id'] = $id;
        }
        if($level != 0){
            $condition['b.id'] = $level;
        }
       
        if($sort != ''){
            $orders = urldecode($sort);
        }else{
            $orders = 'a.id desc';
        }

        $field = 'a.id,a.tel,a.user,a.f_tel,a.last_ip,a.balance,a.profit_deposit,a.pass,a.profit_recom,a.profit_team,a.coin,a.real_name_status,a.control_sell,a.privilege,a.status,a.time,a.first_blood,a.fail_num,a.purchase_status,b.name,r.real_name as real_name_log,a.last_time,a.rechange_limit,a.agent_name';
        $memberLists = Db::name('member_list')->alias('a')
        ->join('zm_member_level b','a.level=b.id', 'LEFT')
        ->join('zm_real_name_log r','a.id=r.u_id', 'LEFT')
        ->where($condition)
        //->whereIn('a.status', [1, 2])
        ->order($orders)->field($field)
        ->paginate($pageSize, false, $allParams);
        // 获取用户列表
        //$memberLists = $MMember->getLists($condition, $pageSize, $allParams, $orders, 'a.id,a.tel,a.user,a.f_tel,a.last_ip,a.balance,a.profit_deposit,a.profit_recom,a.profit_team,a.coin,a.real_name_status,a.control_sell,a.privilege,a.status,a.time,a.first_blood,a.fail_num,a.purchase_status,b.name');
        $this->assign('list', $memberLists);
        // 总记录数
        $total = $memberLists->total();
        // 分页个数
        $pages = ceil($total / 10);
        // 渲染模板
        return view('',['pages'=>$pages, 'total'=>$total,'currentPage'=>$memberLists->currentPage()]);
        // return view();
    }
    // 修改会员状态
    public function memberStatus(Request $request)
    {
        $id = intval($request->param('id', 0)); // 角色id
        $status = intval($request->param('status', 0)); // 状态
        if ($id == 0) {
            return json(['code' => 2, 'msg' => '未指定角色']);
        }
        if ($status != 1 && $status != 2 && $status != 3) {
            return json(['code' => 2, 'msg' => '错误的指定状态']);
        }
        $MMember = new MMember();
        return $MMember->statusMember($id, $status);
        
    }
    // 修改会员卖出状态
    public function memberControl(Request $request)
    {
        $id = intval($request->param('id', 0)); // 角色id
        $status = intval($request->param('status', 0)); // 状态
        if ($id == 0) {
            return json(['code' => 2, 'msg' => '未指定角色']);
        }
        
        if ($status != 1 && $status != 2) {
            return json(['code' => 2, 'msg' => '错误的指定状态']);
        }
        $MMember = new MMember();
        return $MMember->controlMember($id, $status);
    }
    // 修改中奖状态
    public function memberPrivilege(Request $request)
    {
        $id = intval($request->param('id', 0)); // 角色id
        $status = intval($request->param('status', 0)); // 状态
        if ($id == 0) {
            return json(['code' => 2, 'msg' => '未指定角色']);
        }
        
        if ($status != 1 && $status != 2 && $status != 3) {
            return json(['code' => 2, 'msg' => '错误的指定状态']);
        }
        $MMember = new MMember();
        return $MMember->Privilege($id, $status);
        
    }

    //修改实名状态 通过/拒绝
    public function memberAuth(Request $request){
        $MMember = new MMember();
        $member_id = $request->param('id', '');
        if ($member_id) {
            $info = $MMember->getInfo(['id'=>$member_id],'id,real_name_status,real_name,urgent_mobile,idcard,payment_code_img1,payment_code_img2,real_contact,real_name_time,cardImg1,cardImg2');
            $this->assign('info',$info);
        } else {
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        return view('member_real_name');
    }
    // 实名提交
    public function memberRealNamePost(Request $request)
    {  
        $sta = $request->param('sta', ''); 
        $id = $request->param('id', '');
        $refusal_reason = $request->param('refusal_reason', '');
        if($this->redis->get('realname'.$id)){
            return json(['code' => 2, 'msg' => '提交失败，请稍后']);
        }
        $this->redis->set('realname'.$id, '1',10);       
        $MMember = new MMember();
        $real_name_time = $MMember->getValue('member_list', ['id'=>$id], 'real_name_time');

        if(empty($sta)){
            $res = $MMember->updataActivity(['id'=>$id], ['real_name_status'=>3,'refusal_reason'=>$refusal_reason]);
            if($res){
                $this->redis->del('realname'.$id);
                return json(['code' => 1, 'msg' => '提交成功，已拒绝！']);
            }else{
                $this->redis->del('realname'.$id);
                return json(['code' => 2, 'msg' => '提交失败']);
            }
        }
        if($real_name_time > 0){
           $data = [
                'real_name_status'=>1,
                'real_name_time'=>time(),
                'refusal_reason'=> ''
            ];
            $res = $MMember->updataActivity(['id'=>$id], $data);
            
            if($res){
                $this->redis->del('realname'.$id);
                return json(['code' => 1, 'msg' => '提交成功，已通过！']);
            }else{
                $this->redis->del('realname'.$id);
                return json(['code' => 2, 'msg' => '提交失败']);
            }
        }else{
            $res = $MMember->realName($id);
            if($res){
                $this->redis->del('realname'.$id);
                return json(['code' => 1, 'msg' => '提交成功，已通过！']);
            }else{
                $this->redis->del('realname'.$id);
                return json(['code' => 2, 'msg' => '提交失败']);
            }
        }
    }   
    
    
    
    // 添加角色
    public function roleAdd()
    {
        return view('member_add');
    }
    // 添加角色提交
    public function roleAddPost(Request $request)
    {
        $name = $request->param('name', ''); // 角色名
        $tel = $request->param('tel', ''); // 手机号
        $parent_tel = $request->param('parent_tel', '');
        $pass = $request->param('pass', '');
        $pay_pass = $request->param('pay_pass', '');
        $MMember = new MMember();
        if ($name == '') {
            return json(['code' => 2, 'msg' => '角色名不能为空']);
        }
        if (preg_match("/[\',.:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/", $name)) { //不允许特殊字符
            return json(['code' => 2, 'msg' => '昵称不能包含特殊字符']);
        }
        if (preg_match("/[\',.:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/", $name)) { //不允许特殊字符
            return json(['code' => 2, 'msg' => '昵称不能包含特殊字符']);
        }
        if (mb_strlen($name,'utf8') > 11 || mb_strlen($name,'utf8') < 2){
            return json(['code' => 2, 'msg' => '昵称长度2~11字符']);
        }
        if ($tel == '') {
            return json(['code' => 2, 'msg' => '手机号不能为空']);
        }
        if (strlen($tel) != 11) {
            return json(['code' => 2, 'msg' => '手机号码长度为11位']);
        }
        if ($parent_tel == '') {
            $p_tel = 0;
        } else {
            $p_tel = $MMember->getInfo(['tel'=>$parent_tel]);
            if (empty($p_tel)) {
                return json(['code' => 2, 'msg' => '推荐人不存在']);
            }
        }
        if ($pass == '') {
            return json(['code' => 2, 'msg' => '密码不能为空']);
        }
        if ($pay_pass == '') {
            return json(['code' => 2, 'msg' => '支付密码不能为空']);
        }
        if (!is_numeric($pay_pass)) {
            return json(['code' => 2, 'msg' => '支付密码只能为数字']);
        }
        if (strlen($pay_pass) != 6) {
            return json(['code' => 2, 'msg' => '支付密码只能为6位数字']);
        }
        $user_tel = $MMember->getCount(['tel'=>$tel]);
        if ($user_tel != 0) {
            return json(['code' => 2, 'msg' => '该手机号已被注册']);
        }
        try {
            Db::startTrans();
            $add_member = $MMember->add_member($name, $tel, $p_tel['tel'], $pass, $pay_pass);
            if ($add_member) {
                Db::commit();
                return json(['code' => 1, 'msg' => '添加成功']);
            } else {
                Db::rollback();
                return json(['code' => 2, 'msg' => '添加失败']);
            }
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 2, 'msg' => '失败' . $e->getMessage()]);
            
        }
    }
    
    
    // 编辑会员页面渲染
    public function memberEdit(Request $request)
    {
        $id = intval($request->param('id', 0)); // 角色id
        $MMember = new MMember();
        $member = $MMember->getInfo(['id'=>$id],'id,user,tel,last_ip,last_time,cardImg1,cardImg2,addressUsdt,real_name,idcard,u_img,real_name_status,purchase_status,status,level');
        $this->assign('member', $member);
        $MMemberLevel = new MMemberLevel();
        $level = $MMemberLevel->getList();
        $this->assign('level', $level);
        return view();
    }
    // 编辑会员修改
    public function userEditPost(Request $request)
    {   
        $id = intval($request->param('id', 0)); // id
        $user = $request->param('yhuser', ''); // 昵称
        $u_img = $request->param('u_img', ''); //
        $cardImg1 = $request->param('cardImg1', '');
        $cardImg2 = $request->param('cardImg2', ''); 
        $real_name_status = $request->param('real_name_status', ''); 
        $purchase_status = $request->param('purchase_status', '');
        $status = $request->param('status', '');
        $pass = $request->param('dlpassword', '');
        $pay_pass = $request->param('rpassword', '');
        $level = $request->param('level', '');
        
        $u_img = $this->updatexg($u_img);
        $cardImg1 = $this->updatexg($cardImg1);
        $cardImg2 = $this->updatexg($cardImg2);
       
        if ($id == 0) {
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        $rule = [
            'user' => 'min:2|max:25',
        ];
        $msg = [
            'username.require' => '昵称为必填项',
            'user.min' => '昵称不能少于2个字符',
            'user.max' => '昵称不能超过25个字符',
        ];
        $data = [
            'user' => $user,
            'u_img' => $u_img,
            'cardImg1' => $cardImg1,
            'cardImg2' => $cardImg2,
            'status' => $status,
            'real_name_status' => $real_name_status,
            'purchase_status' => $purchase_status,
            'pass' => $pass,
            'pay_pass' => $pay_pass,
            'level' => $level,
        ];
        $validate = Validate::make($rule, $msg);
        $result = $validate->check($data);
        if (!$result) {
            return json(['code' => 2, 'msg' => $validate->getError()]);
        } else {
            $MMember = new MMember();
            return $MMember->editMember($id, $data);
        }
    }
    
    
    
    
    // 会员详情
    public function memberDetail(Request $request)
    {
        $my_active_module = intval($request->param('my_active_module', 1)); //
        $this->assign('my_active_module', $my_active_module);
        $id = intval($request->param('id', 0)); //
        $this->assign('id', $id);
        $where = 'l.id = ' . $id; // 初始查询条件
        $MMember = new MMember();
        $memberDetail = $MMember->getDetail($where);
        $payment_list = Db::name('paymant_binding')->where(['u_id'=>$id])->select();
        $data_receive = [];
        foreach ($payment_list as $k => $v){
            $data_receive[$v['status']] = ['name' => $v['name'], 'account_num' => $v['account_num'], 'bank_num' => $v['bank_num'], 'receive_qrcode' => 'http://' . $_SERVER['HTTP_HOST'] . $v['receive_qrcode']];            
        }
        if(empty($data_receive[1])){
            $data_receive[1] = ['receive_qrcode' => ''];
        }
        if(empty($data_receive[2])){
            $data_receive[2] = ['receive_qrcode' => ''];
        }
        if(empty($data_receive[4])){
            $data_receive[4] = ['receive_qrcode' => ''];
        }
        if(empty($data_receive[3])){
            $data_receive[3] = ['name' => '', 'account_num' => '', 'bank_num' => ''];
        }

        $this->assign('payment', $data_receive);

        
        //实名信息
        $user = $MMember->getInfo(['id'=>$id]);
        $user['level_name'] = $user['level']==0?'精灵':$memberDetail['level_name'];
        $this->assign('user', $user);
        
        $allParams = ['query' => $request->param()];
        $pageSize = 10; // 分页大小
        //$list = Db::name('member_mutualaid')->alias('a')->leftJoin('member_list m', 'm.id = a.u_id')->order('a.stime desc')->where($where)->field('a.*, m.tel')->paginate($pageSize, false, $allParams);
        $list = Db::name('member_mutualaid')->alias('a')
        ->leftJoin('mutualaid_list m', 'm.id = a.purchase_id')
        ->where('a.uid = '.$user['id'].' AND a.status = 1 AND a.deal_type = 1')->field('a.tel,a.new_price,a.rate,a.orderNo,a.purchase_no,a.status,a.days,m.name')
        ->order('a.id desc')->paginate($pageSize, false, $allParams);
        
        $this->assign('mutualaid', $list);
        
        return view();
    }

    //获取资产
    public function get_money(Request $request)
    {
        $uid = intval($request->param('u_id', 0)); // 角色id
        $type = intval($request->param('type', 0)); // 角色id
        if ($uid && $type) {//1可售额度2流量资产3糖果钱包4可售余额5NAD6余额
            $MemberList = new MCommon();
            switch ($type) {
                case 1:
                    $money = $MemberList->getValue('member_list','id ='.$uid,'sell_limit');
                    break;
                case 2:
                    $money = $MemberList->getValue('member_list','id ='.$uid,'mac_assets');
                    break;
                case 3:
                    $money = $MemberList->getValue('member_list','id ='.$uid,'mac_wallet');
                    break;
                case 4:
                    $money = $MemberList->getValue('member_list','id ='.$uid,'balance');
                    break;
                case 5:
                    $money = $MemberList->getValue('member_list','id ='.$uid,'coin');
                    break;
                default:
                    return json(['code' => 2, 'msg' => '参数错误']);
                    break;
            }
            return json(['code' => 1, 'msg' => 'ok', 'money' => $money]);
        } else {
            return json(['code' => 2, 'msg' => '参数错误']);
        }
    }
    
    //发送资产页面渲染
    public function memberMoney(Request $request)
    {      
        $username = Session::get('user.username');
        $id = intval($request->param('id', 0)); // 角色id
        $MConfig = new MConfig();
        $MConfig_val = $MConfig->readConfig(['mainCurrency','auxiliaryCurrency'],2);
        if ($request->isAjax()) {
            $balance = floatval($request->param('balance', 0)); //
            $coin = floatval($request->param('coin', 0)); //
            $profit_recom = floatval($request->param('profit_recom', 0)); //
            $profit_team = intval($request->param('profit_team', 0));
            $rechange_limit = intval($request->param('rechange_limit', 0));
            
            if (Cache::get('memberMoney'.$id) == 2){
                return json(['code' => 2, 'msg' => '请勿频繁操作', 'data'=>[]]);
            }
            Cache::set('memberMoney'.$id,2,10);   
            
            if ($balance == 0 && $coin == 0 && $profit_recom == 0 && $profit_team == 0 && $rechange_limit == 0) {
                return json(['code' => 1, 'msg' => '无修改']);
            } else {
                $MMember = new MMember();//MemberList();
                $MMemberBalanceLog = new MMemberBalanceLog();
                try {
                    Db::startTrans();
                    if(!empty($balance)){
                        $money = $MMember->where('id', $id)->value('balance');
                        if (($money + $balance) < 0) {
                            Cache::set('memberMoney'.$id,1,1);   
                            return json(['code' => 2, 'msg' => $MConfig_val[0].'不能小于零']);
                        } else {
                            $MMember->where('id', $id)->setInc('balance', $balance);
                            $balance_data = [
                                'u_id' => $id,
                                'o_id' => 0,
                                'change_money' => $balance,
                                'former_money' => $money,
                                'after_money' => $money+$balance,
                                'type' => 2,
                                'message' => '管理员-'.$username.':发送'.$MConfig_val[0],
                                'message_e' => ' System adjustment funds ',
                                'bo_time' => time(),
                                'status' => 100
                            ];
                            $MMemberBalanceLog->addActivity($balance_data);                           
                        }
                    }
                    if(!empty($coin)){
                        $coin_money = $MMember->where('id', $id)->value('coin');
                        if (($coin_money + $coin) < 0) {
                            return json(['code' => 2, 'msg' => $MConfig_val[1].'不能小于零']);
                        } else {
                            $res1 = $MMember->where('id', $id)->setInc('coin', $coin);
                            $coin_data = [
                                'u_id' => $id,
                                'o_id' => 0,
                                'change_money' => $coin,
                                'former_money' => $coin_money,
                                'after_money' => $coin_money+$coin,
                                'type' => 11,
                                'message' => '管理员-'.$username.':发送'.$MConfig_val[1],
                                'bo_time' => time(),
                                'status' => 100
                            ];
                            $MMemberBalanceLog->addActivity($coin_data);
                        }
                    }
                    if(!empty($profit_recom)){
                        $profit_recom_money = $MMember->where('id', $id)->value('profit_recom');
                        if (($profit_recom_money + $profit_recom) < 0) {
                            return json(['code' => 2, 'msg' => '推荐收益不能小于零']);
                        } else {
                            $MMember->where('id', $id)->setInc('profit_recom', $profit_recom);
                            $recom_data = [
                                'u_id' => $id,
                                'o_id' => 0,
                                'change_money' => $profit_recom,
                                'former_money' => $profit_recom_money,
                                'after_money' => $profit_recom_money+$profit_recom,
                                'type' => 5,
                                'message' => '管理员-'.$username.':发送推荐收益',
                                'bo_time' => time(),
                                'status' => 100
                            ];
                            $MMemberBalanceLog->addActivity($recom_data);
                        }
                    }
                    if(!empty($profit_team)){
                        $profit_team_money = $MMember->where('id', $id)->value('profit_team');
                        if (($profit_team_money + $profit_team) < 0) {
                            return json(['code' => 2, 'msg' => '团队收益不能小于零']);
                        } else {
                            $MMember->where('id', $id)->setInc('profit_team', $profit_team);
                            $team_data = [
                                'u_id' => $id,
                                'o_id' => 0,
                                'change_money' => $profit_team,
                                'former_money' => $profit_team_money,
                                'after_money' => $profit_team_money+$profit_team,
                                'type' => 6,
                                'message' => '管理员-'.$username.':发送团队收益',
                                'bo_time' => time(),
                                'status' => 100
                            ];
                            $MMemberBalanceLog->addActivity($team_data);
                        }
                    }
                    if(!empty($rechange_limit)){
                        $profit_rechange_limit = $MMember->where('id', $id)->value('rechange_limit');
                        if (($profit_rechange_limit + $rechange_limit) < 0) {
                            return json(['code' => 2, 'msg' => '充值余额不能小于零']);
                        } else {
                            $MMember->where('id', $id)->setInc('rechange_limit', $rechange_limit);
                            $rechange_data = [
                                'u_id' => $id,
                                'o_id' => 0,
                                'change_money' => $rechange_limit,
                                'former_money' => $profit_rechange_limit,
                                'after_money' => $profit_rechange_limit+$rechange_limit,
                                'type' => 1,
                                'message' => '管理员-'.$username.':发送充值金额',
                                'message_e' => ' System adjustment funds ',
                                'bo_time' => time(),
                                'status' => 100
                            ];
                            $MMemberBalanceLog->addActivity($rechange_data);
                        }
                    }  
                    Db::commit();
                    Cache::set('memberMoney'.$id,1,1); 
                    return json(['code' => 1, 'msg' => '发送成功']);
                } catch (\Exception $e) {
                    Db::rollback();
                    Cache::set('memberMoney'.$id,1,1); 
                    return json(['code' => 2, 'msg' => '发送失败']);
                }
            }
        } else {
            if ($id) {
                $MMember = new MMember();
                $member = $MMember->getInfo(['id'=>$id]);
                if ($member) {
                    $this->assign('member', $member);
                    return view();
                } else {
                    $this->error('用户不存在');die();
                }
            } else {
                $this->error('参数错误');die();
            }
        }
        
    }
    
    // 删除会员
    public function memberDelete(Request $request)
    {
        $id = intval($request->param('id', 0)); // 角色id
        if ($id == 0) {
            return json(['code' => 2, 'msg' => '未指定角色']);
        }
        if ($id == 1) {
            return json(['code' => 2, 'msg' => '不能删除超级管理员']);
        }
        $MMember = new MMember();
        return $MMember->deleteMember($id);
    }

    /**
     * 等级设置
     */
    public function level(){
        $MMemberLevel = new MMemberLevel();
        $levellist = $MMemberLevel->getList();
/*         foreach ($levellist as $k => $v){
            $levellist[$k]['machine_name'] = $MMemberLevel->getValue('machine_manage', ['id'=>$v['level_machine']], 'name');
        } */
        
        $this->assign('levels', $levellist);
        return view();
    }

    //添加等级
    public function levelAdd(Request $request){
        $memberLevelModel = new MMemberLevel();
        if ($request->isAjax()) {
            $name = $request->param('name', '');
            $thumbnail = $request->param('thumbnail', '');
            $sell_rate = intval($request->param('sell_rate', 0));
            $one_era = intval($request->param('one_era', 0));
            $two_era = intval($request->param('two_era', 0));
            $three_era = intval($request->param('three_era', 0));
            $team_income_ratio = intval($request->param('team_income_ratio', 0));
            $pet_assets = intval($request->param('pet_assets', 0));
            $team_push = intval($request->param('team_push', 0));
            $direct_push = intval($request->param('direct_push', 0));

            $thumbnail = $this->updatexg($thumbnail);
            
            if ($memberLevelModel->insert([
                'name' => $name,
                'level_logo' => $thumbnail,
                'sell_rate' => $sell_rate,
                'team_income_ratio' => $team_income_ratio,
                'pet_assets' => $pet_assets,
                'team_push' => $team_push,
                'direct_push' => $direct_push,
                'one_era' => $one_era,
                'two_era' => $two_era,
                'three_era' => $three_era,
            ])) {
                return json(['code' => 1, 'msg' => '新增成功']);
            } else {
                return json(['code' => 2, 'msg' => '新增失败']);
            }

        }
/*         $MMachineManage = new MMachineManage();
        $mach_list = $MMachineManage->getList(['status'=>1]);
        $this->assign('machlist', $mach_list); */
        return view();
    }
    //等级修改
    public function levelEdit(Request $request){
        $memberLevelModel = new MMemberLevel();
        if ($request->isAjax()) {
            $id = $request->param('id');
            if (empty($id)) return json(['code' => 2, 'msg' => '参数错误']);
            
            $name = $request->param('name');
            $thumbnail = $request->param('thumbnail', '');
            $sell_rate = intval($request->param('sell_rate', 0));
            $one_era = intval($request->param('one_era', 0));
            $two_era = intval($request->param('two_era', 0));
            $three_era = intval($request->param('three_era', 0));
            $team_income_ratio = intval($request->param('team_income_ratio', 0));
            $pet_assets = intval($request->param('pet_assets', 0));
            $team_push = intval($request->param('team_push', 0));
            $direct_push = intval($request->param('direct_push', 0));
            
            $thumbnail = $this->updatexg($thumbnail);
            
            if ($memberLevelModel->where(['id'=>$id])->update([
                'name' => $name,
                'level_logo' => $thumbnail,
                'sell_rate' => $sell_rate,
                'team_income_ratio' => $team_income_ratio,
                'pet_assets' => $pet_assets,
                'team_push' => $team_push,
                'direct_push' => $direct_push,
                'one_era' => $one_era,
                'two_era' => $two_era,
                'three_era' => $three_era,
            ])) {
                return json(['code' => 1, 'msg' => '修改成功']);
            } else {
                return json(['code' => 2, 'msg' => '修改失败']);
            }
            
        }
        $id = intval($request->param('id', 0));
        $levelinfo = $memberLevelModel->getInfo(['id'=>$id]);
        $this->assign('level',$levelinfo);
        
/*         $MMachineManage = new MMachineManage();
        $mach_list = $MMachineManage->getList(['status'=>1]);
        $this->assign('machlist', $mach_list); */
        return view();
    }
    //修改等级下的订单显示隐藏状态
    public function level_hide(Request $request){
        $id = intval($request->param('id', 0)); // 角色id
        $status = intval($request->param('status', 0)); // 状态
        if ($id == 0) {
            return json(['code' => 2, 'msg' => '未指定角色']);
        }
        if ($status != 1 && $status != 2) {
            return json(['code' => 2, 'msg' => '错误的指定状态']);
        }
        $MMemberLevel = new MMemberLevel();
        return $MMemberLevel->order_hide($id, $status);
    }
    
    
    /**
     *  实名记录
     */
    public function realnameLog(Request $request)
    {
        $user_tel = trim($request->param('tel', ''));
        $search = trim($request->param('search', ''));
        $type = $request->param('type', 3);
        $allParams = ['query' => $request->param()];
        $add_time_s = $request->param('add_time_s', ''); // 开启时间开始
        $add_time_e = $request->param('add_time_e', ''); // 开启时间结束
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        $this->assign('type', $type);
        $this->assign('search', $search);
        $this->assign('tel', $user_tel);

        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if ($user_tel != '') {
            $MMember = new MMember();//MemberList();
            $condition = 'tel like "%' . $user_tel . '%"';//['tel'=>$user_tel]

            $user = $MMember->getInfo($condition,'id');
            if ($user) {
                $where .= ' and `u_id` = ' . $user['id'];
            } else {
                $where .= ' and `u_id` = 0';
            }
        }
        if ($type != '' && $type < 3) {
            $where .= ' and `status` = ' . $type;
        }
        if ($add_time_s != '') {
            $where .= " and `time` >= " . strtotime($add_time_s);
        }
        if ($add_time_e != '') {
            $where .= " and `time` <= " . strtotime($add_time_e);
        }
        if ($search != '') {
            $where .= ' and `real_name` like "%' . $search . '%" OR id_card like "%' . $search . '%"';
        }

        // 获取列表
        $RealName = new RealName();
        $list = $RealName->getLists($where, $pageSize, $allParams);
        $this->assign('list', $list);
        return view();
    }
    
    // 验证实名 通过/拒绝
    public function agree(Request $request)
    {
        $id = intval($request->param('id', 0)); //id
        $status = intval($request->param('status', 0)); //id
        if ($id == 0 || $status == 0) {
            return json(['code' => 2, 'msg' => '未指定信息']);
        }
        $RealName = new RealName();
        return $RealName->updateStatus($id, $status);
    }  
    
    //实名编辑页面渲染
    public function realnameEdit(Request $request){
        $id = $request->param('id', '');
        $RealName = new RealName();
        $info = $RealName->getInfo(['id'=>$id]);
        $this->assign('info', $info);
        return view();
    }
    //实名编辑页面修改
    public function realnameEditPost(Request $request){
        $RealName = new RealName();
        $MMember = new MMember();
        if ($request->isAjax()) {
            $id = $request->param('id');
            if (empty($id)) return json(['code' => 2, 'msg' => '参数错误']);
            $info = $RealName->getInfo(['id'=>$id],'id,u_id');
            $name = $request->param('name', '');
            $cardImg1 = $request->param('cardImg1', '');
            $cardImg2 = $request->param('cardImg2', '');
            $cardImg1 = $this->updatexg($cardImg1);
            $cardImg2 = $this->updatexg($cardImg2);
            if ($RealName->where(['id'=>$id])->update([
                'cardImg1' => $cardImg1,
                'cardImg2' => $cardImg2,
                'real_name' => $name
            ])) {
                
                $res = $MMember->where(['id'=>$info['u_id']])->update(['cardImg1'=>$cardImg1,'cardImg2' => $cardImg1,'real_name'=>$name]);
                if($res){
                    return json(['code' => 1, 'msg' => '修改成功']);
                }else{
                    return json(['code' => 2, 'msg' => '修改失败2']);
                }
            } else {
                return json(['code' => 2, 'msg' => '修改失败']);
            }
        }
    }
    
    
    /**
     *  支付绑定记录
     */
    public function memberPayBindLog(Request $request)
    {
        $user_tel = trim($request->param('tel', ''));
        $search = trim($request->param('search', ''));
        $type = $request->param('type', 0);
        $allParams = ['query' => $request->param()];
        $add_time_s = $request->param('add_time_s', ''); // 开启时间开始
        $add_time_e = $request->param('add_time_e', ''); // 开启时间结束
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        $this->assign('type', $type);
        $this->assign('search', $search);
        $this->assign('tel', $user_tel);
        
        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if ($user_tel != '') {
            $MMember = new MMember();//MemberList();
            $condition = 'tel like "%' . $user_tel . '%"';//['tel'=>$user_tel]
            
            $user = $MMember->getInfo($condition,'id');
            if ($user) {
                $where .= ' and `u_id` = ' . $user['id'];
            } else {
                $where .= ' and `u_id` = 0';
            }
        }
        if ($type != 0) {
            $where .= ' and `status` = ' . $type;
        }
        if ($add_time_s != '') {
            $where .= " and `create_time` >= " . strtotime($add_time_s);
        }
        if ($add_time_e != '') {
            $where .= " and `create_time` <= " . strtotime($add_time_e);
        }
        if ($search != '') {
            $where .= ' and `name` like "%' . $search . '%" OR account_num like "%' . $search . '%" OR bank_num like "%' . $search . '%"';
        }
        
        // 获取列表
        $PayBind = new PayBind();
        $list = $PayBind->getLists($where, $pageSize, $allParams);
        $this->assign('list', $list);
        return view();
    }
    
    //支付绑定编辑页面渲染
    public function memberPayBindEdit(Request $request){
        $id = $request->param('id', '');
        $PayBind = new PayBind();
        $info = $PayBind->getInfo(['id'=>$id]);
        $this->assign('info', $info);
        return view();
    }
    //支付绑定编辑页面修改
    public function memberPayBindEditPost(Request $request){
        $PayBind = new PayBind();
        if ($request->isAjax()) {
            $id = $request->param('id');
            if (empty($id)) return json(['code' => 2, 'msg' => '参数错误']);
            $name = $request->param('name', '');
            $account_num = $request->param('account_num', '');
            $bank_num = $request->param('bank_num', '');
            $receive_qrcode = $request->param('receive_qrcode', '');
            $status = $request->param('status', '');
            
            $receive_qrcode = $this->updatexg($receive_qrcode);
            if ($PayBind->where(['id'=>$id])->update([
                'name' => $name,
                'account_num' => $account_num,
                'bank_num' => $bank_num,
                'receive_qrcode' => $receive_qrcode,
                'modify_time'=>time(),
                'status'=>$status
            ])) {
                return json(['code' => 1, 'msg' => '修改成功']);
            } else {
                return json(['code' => 2, 'msg' => '修改失败']);
            }
        }
    }
    
    //支付绑定删除
    public function memberPayBindDelete(Request $request){
        $id = $request->param('id', 0);
        $PayBind = new PayBind();
        $info = $PayBind->where('id',$id)->delete();
        if($info){
            return json(['code' => 1, 'msg' => '删除成功']);
        } else {
            return json(['code' => 2, 'msg' => '删除失败']);
        }
    }
    
    
    /**
     *  可售额度资金记录
     */
    public function memberSellLimitLog(Request $request)
    {
        $user_tel = trim($request->param('user_tel', ''));
        $type = $request->param('type', 0);
        $allParams = ['query' => $request->param()];
        $add_time_s = $request->param('add_time_s', ''); // 开启时间开始
        $add_time_e = $request->param('add_time_e', ''); // 开启时间结束
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        $this->assign('param_type', $type);
        $this->assign('user_tel', $user_tel);
        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if ($user_tel != '') {
            $MMember = new MMember();//MemberList();
            $user = $MMember->getInfo(['tel'=>$user_tel],'id');
            if ($user) {
                $where .= ' and `u_id` = ' . $user['id'];
            } else {
                $where .= ' and `u_id` = 0';
            }
        }
        if ($type != 0) {
            $where .= ' and `type` = ' . $type;
        }
        if ($add_time_s != '') {
            $where .= " and `bo_time` >= " . strtotime($add_time_s);
        }
        if ($add_time_e != '') {
            $where .= " and `bo_time` <= " . strtotime($add_time_e);
        }
        // 获取管理员列表
        $MMemberSellLimitLog = new MMemberSellLimitLog();//MemberSellLimitLog();
        $memberLists = $MMemberSellLimitLog->getLists($where, $pageSize, $allParams);
        $this->assign('list', $memberLists);
        return view();
    }
    
    // 流量资产资金记录
    public function memberMacAssetsLog(Request $request)
    {
        $user_tel = trim($request->param('user_tel', ''));
        $type = $request->param('type', 0);
        $allParams = ['query' => $request->param()];
        $add_time_s = $request->param('add_time_s', ''); // 开启时间开始
        $add_time_e = $request->param('add_time_e', ''); // 开启时间结束
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        $this->assign('param_type', $type);
        $this->assign('user_tel', $user_tel);
        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if ($user_tel != '') {
            $MMember = new MMember();//MemberList();
            $user = $MMember->getInfo(['tel'=>$user_tel],'id');
            if ($user) {
                $where .= ' and `u_id` = ' . $user['id'];
            } else {
                $where .= ' and `u_id` = 0';
            }
        }
        if ($type != 0) {
            $where .= ' and `type` = ' . $type;
        }
        if ($add_time_s != '') {
            $where .= " and `bo_time` >= " . strtotime($add_time_s);
        }
        if ($add_time_e != '') {
            $where .= " and `bo_time` <= " . strtotime($add_time_e);
        }
        // 获取管理员列表
        $MMemberMacAssetsLog = new MMemberMacAssetsLog();//MemberMacAssetsLog();
        $memberLists = $MMemberMacAssetsLog->getLists($where, $pageSize, $allParams);
        $this->assign('list', $memberLists);
        return view();
    }
    
    // 矿池资钱包金记录
    public function memberMacWalletLog(Request $request)
    {
        $user_tel = trim($request->param('user_tel', ''));
        $type = $request->param('type', 0);
        $allParams = ['query' => $request->param()];
        $add_time_s = $request->param('add_time_s', ''); // 开启时间开始
        $add_time_e = $request->param('add_time_e', ''); // 开启时间结束
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        $this->assign('param_type', $type);
        $this->assign('user_tel', $user_tel);
        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if ($user_tel != '') {
            $MMember = new MMember();//MemberList();
            $user = $MMember->getInfo(['tel'=>$user_tel],'id');
            if ($user) {
                $where .= ' and `u_id` = ' . $user['id'];
            } else {
                $where .= ' and `u_id` = 0';
            }
        }
        if ($type != 0) {
            if($type == 330){
                $where .= ' and `type` in(330,331,332,333,334,335,336,337,338,339)';
            }else{
                $where .= ' and `type` = ' . $type;
            }
        }
        if ($add_time_s != '') {
            $where .= " and `bo_time` >= " . strtotime($add_time_s);
        }
        if ($add_time_e != '') {
            $where .= " and `bo_time` <= " . strtotime($add_time_e);
        }

        // 获取管理员列表
        $MMemberMacWalletLog = new MMemberMacWalletLog();//MemberMacWalletLog();
        $memberLists = $MMemberMacWalletLog->getLists($where, $pageSize, $allParams);
        $this->assign('list', $memberLists);
        return view();
    }
    
    // 流水记录
    public function memberBalanceLog(Request $request)
    {
        $user_tel = trim($request->param('user_tel', ''));
        $type = $request->param('type', 0);
        $allParams = ['query' => $request->param()];
        $add_time_s = $request->param('add_time_s', ''); // 开启时间开始
        $add_time_e = $request->param('add_time_e', ''); // 开启时间结束
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        $this->assign('param_type', $type);
        $this->assign('user_tel', $user_tel);
        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if ($user_tel != '') {
            $MMember = new MMember();//MemberList();
            $user = $MMember->getInfo(['tel'=>$user_tel],'id');
            if ($user) {
                $where .= ' and `u_id` = ' . $user['id'];
            } else {
                $where .= ' and `u_id` = 0';
            }
        }
        if ($type != 0) {
            $where .= ' and `type` = ' . $type;
        }
        if ($add_time_s != '') {
            $where .= " and `bo_time` >= " . strtotime($add_time_s);
        }
        if ($add_time_e != '') {
            $where .= " and `bo_time` <= " . strtotime($add_time_e);
        }
        // 获取管理员列表
        $MMemberBalanceLog = new MMemberBalanceLog();
        $memberLists = $MMemberBalanceLog->getLists($where, $pageSize, $allParams);
        
        if(!empty($memberLists)){
            foreach ($memberLists['list'] as $k => $v){
                $memberLists['list'][$k]['bo_time'] = $v['bo_time'];//-9000;
                //$user = Db::name('member_list')->where('u_id='.$memberLists['list'][$k]['u_id'])->find();
                if($memberLists['list'][$k]['tel']==0 ){
                    $user = Db::name('member_list')->where('id='.$memberLists['list'][$k]['u_id'])->find();
                    $memberLists['list'][$k]['tel'] = $user['tel'];
                }
            }
        }
        $this->assign('list', $memberLists);
        return view();
    }
    
    
    //流水记录删除
    public function memberBalanceLogDel(Request $request){
        $id = $request->param('id', 0);
        $MMemberBalanceLog = new MMemberBalanceLog();
        $info = $MMemberBalanceLog->where('id',$id)->delete();
        if($info){
            return json(['code' => 1, 'msg' => '删除成功']);
        } else {
            return json(['code' => 2, 'msg' => '删除失败']);
        }
    }
    
    // coin金记录
    public function memberCoinLog(Request $request)
    {
        $user_tel = trim($request->param('user_tel', ''));
        $type = $request->param('type', 0);
        $allParams = ['query' => $request->param()];
        $add_time_s = $request->param('add_time_s', ''); // 开启时间开始
        $add_time_e = $request->param('add_time_e', ''); // 开启时间结束
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        $this->assign('param_type', $type);
        $this->assign('user_tel', $user_tel);
        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if ($user_tel != '') {
            $MMember = new MMember();//MemberList();
            $user = $MMember->getInfo(['tel'=>$user_tel],'id');
            if ($user) {
                $where .= ' and `u_id` = ' . $user['id'];
            } else {
                $where .= ' and `u_id` = 0';
            }
        }
        if ($type != 0) {
            $where .= ' and `type` = ' . $type;
        }
        if ($add_time_s != '') {
            $where .= " and `bo_time` >= " . strtotime($add_time_s);
        }
        if ($add_time_e != '') {
            $where .= " and `bo_time` <= " . strtotime($add_time_e);
        }
        // 获取管理员列表
        $MMemberCoinLog = new MMemberCoinLog();//MemberCoinLog();
        $memberLists = $MMemberCoinLog->getLists($where, $pageSize, $allParams);
        $this->assign('list', $memberLists);
        return view();
    }

  

    
    //团队关系
    public function memberTeamLog(){
        $MMember = new MMember();
        $MMemberMutual = new MMemberMutual();
        //Db::name('member_list')
        //Db::name('member_mutualaid')
        $member_list = Db::name('member_list')->where('f_uid = 0')->order('id')->field('id,user,tel,yx_team,f_uid')->select();       
        
        foreach ($member_list as $k => $v){
            //个人资产
            $personAssets = $MMemberMutual->where('uid ='.$v['id'].' and status in (1,2,3) and is_exist = 1')->sum('new_price');
            
            $arr_user = $MMember->where('FIND_IN_SET(:id,f_uid_all)',['id' => $v['id']])->column('id');
            //团队资产
            $teamAssets = $MMemberMutual->whereIn('uid',$arr_user)->where('status in (1,2,3) and is_exist = 1')->sum('new_price');
            //今日团队资产
            $teamAssetsToday = $MMemberMutual->whereTime('sta_time','today')->whereIn('uid',$arr_user)->where('status in (1,2,3) and is_exist = 1')->sum('new_price');
            
            $member_list[$k]['text']=trim('Id:'.$v['id'].'-名称:'.$v['user'].'(手机号：<div style = "color:blue;display:inline-block;">'.$v['tel'].'</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;有效团队：<div style = "color:red;display:inline-block;">'.$v['yx_team'].'</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;个人资产：<div style = "color:violet;display:inline-block;">'.$personAssets.'</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;团队资产：'.$teamAssets.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 今日团队资产：<div style = "color:green;display:inline-block;">'.$teamAssetsToday.'</div>)');
            $member_list[$k]['parent']=!empty($v['f_uid'])?$v['f_uid']:'#';
             //判断是否是根
            if(empty($v['f_uid'])){
                $member_list[$k]['type']='root';
            }
            //判断是否有子
            $children=$MMember->where('f_uid',$v['id'])->field('id,user,f_uid')->select();
            if(!empty($children)){
                $member_list[$k]['children']=true;
            }
        }
//          echo '<pre/>';
//         var_dump($member_list);
        $this->assign('list',$member_list);
        return view();
    }
    
    
    /**
     *  充值记录
     */
    public function memberusdtLog(Request $request)
    {
        $user_tel = trim($request->param('tel', ''));
        $search = trim($request->param('search', ''));
        $allParams = ['query' => $request->param()];
        $add_time_s = $request->param('add_time_s', ''); // 开启时间开始
        $add_time_e = $request->param('add_time_e', ''); // 开启时间结束
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        $this->assign('search', $search);
        $this->assign('tel', $user_tel);
        
        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件

        if ($add_time_s != '') {
            $where .= " and `time` >= " . strtotime($add_time_s);
        }
        if ($add_time_e != '') {
            $where .= " and `time` <= " . strtotime($add_time_e);
        }
        if ($search != '') {
            $where .= ' and `tel` like "%' . $search . '%" OR num like "%' . $search . '%"';
        }
        
        $list = Db::name('jxb_log')->where($where)->order('id desc')->paginate($pageSize, false, $allParams);
        $this->assign('list', $list);
        return view();
    }
    
    // 通过/拒绝
    public function usdtagree(Request $request)
    {
        $id = intval($request->param('id', 0)); //id
        $status = intval($request->param('status', 0)); //id
        if ($id == 0 || $status == 0) {
            return json(['code' => 2, 'msg' => '未指定信息']);
        }              
        $MMember = new MMember();        
        $info = Db::name('jxb_log')->where(['id'=>$id])->find();   
        
        if($info['is_look'] != 0){
            return json(['code' => 2, 'msg' => '状态错误']);
        }
        $member_info = $MMember->getInfo(['id'=>$info['u_id']],'coin,tel');        
        try {
            Db::startTrans();            
            Db::name('jxb_log')->where(['id'=>$id])->update(['is_look'=>$status]);
            
            if($status == 1){
                Db::name('member_list')->where('id', $info['u_id'])->setInc('coin', $info['num']);
                Db::name('member_balance_log')->insert([
                    'u_id' => $info['u_id'],
                    'tel' => $member_info['tel'],
                    'former_money' => $member_info['coin'],
                    'change_money' => +$info['num'],
                    'after_money' => $member_info['coin'] + $info['num'],
                    'message' => '充值激活码  审核成功',
                    'type' => 11,
                    'bo_time' => time(),
                    'status' => 110
                ]);
            }else{
                Db::name('member_balance_log')->insert([
                    'u_id' => $info['u_id'],
                    'tel' => $member_info['tel'],
                    'former_money' => 0,
                    'change_money' => -$info['num'],
                    'after_money' => 0,
                    'message' => '充值激活码 审核失败',
                    'type' => 11,
                    'bo_time' => time(),
                    'status' => 110
                ]);
            }
            Db::commit();
            return json(['code' => 1, 'msg' => '添加成功']);
            
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => 2, 'msg' => '失败' . $e->getMessage()]);
            
        }

    }  
   
    
    

    public function memberTeam(Request $request){
        $MMember = new MMember();
        $MMemberMutual = new MMemberMutual();
        //Db::name('member_list')
        //Db::name('member_mutualaid')
        $id = $request->param('id', '');
        
        if(empty($id)){
            $id = 0;
        }
        $member_list = Db::name('member_list')->where('f_uid = '.$id)->order('id')->field('id,user,tel,yx_team,f_uid')->select();
        
        foreach ($member_list as $k => $v){
            //个人资产
            $personAssets = $MMemberMutual->where('uid ='.$v['id'].' and status in (1,2,3) and is_exist = 1')->sum('new_price');
            
            $arr_user = $MMember->where('FIND_IN_SET(:id,f_uid_all)',['id' => $v['id']])->column('id');
            //团队资产
            $teamAssets = $MMemberMutual->whereIn('uid',$arr_user)->where('status in (1,2,3) and is_exist = 1')->sum('new_price');
            
            //今日团队资产
            $teamAssetsToday = $MMemberMutual->whereTime('sta_time','today')->whereIn('uid',$arr_user)->where('status in (1,2,3) and is_exist = 1')->sum('new_price');
            
            //$member_list[$k]['text']=trim('Id:'.$v['id'].'-名称:'.$v['user'].'(手机号：'.$v['tel'].'; 有效团队：'.$v['yx_team'].'; 个人资产：'.$personAssets.'; 团队资产：'.$teamAssets.'; 今日团队资产：'.$teamAssetsToday.'');
            //$member_list[$k]['parent']=!empty($v['f_uid'])?$v['f_uid']:'#';
            //判断是否是根
            /* if(empty($v['f_uid'])){
                $member_list[$k]['type']='root';
            } */
            //判断是否有子
            $children=Db::name('member_list')->where('f_uid',$v['id'])->field('id,user,f_uid')->select();
            if(!empty($children)){
                $member_list[$k]['children']=true;
            }
            
            $member_list[$k]['personAssets']=$personAssets;
            $member_list[$k]['teamAssets']=$teamAssets;
            $member_list[$k]['teamAssetsToday']=$teamAssetsToday;
        }
        return $member_list;

    }
    
    
    
    //查询
    public function term_tree(Request $request){
        $MMember = new MMember();
        $MMemberMutual = new MMemberMutual();
        //Db::name('member_list')
        //Db::name('member_mutualaid')
        
        //'f_uid = 0'
        
/*         $member_list = Db::name('member_list')->where('1=1')->order('id')->field('id,user,tel,yx_team,f_uid')->select();
        
          foreach ($member_list as $k => $v){
              $member_list[$k]['pid']=$v['f_uid'];
          } */
            //个人资产
            //$personAssets = $MMemberMutual->where('uid ='.$v['id'].' and status in (1,2,3) and is_exist = 1')->sum('new_price');
            //$member_list[$k]['text']=trim('Id:'.$v['id'].'-名称:'.$v['user'].'(手机号：<div style = "color:blue;display:inline-block;">'.$v['tel'].'</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;有效团队：<div style = "color:red;display:inline-block;">'.$v['yx_team'].'</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;个人资产：<div style = "color:violet;display:inline-block;">0</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;团队资产：0 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 今日团队资产：<div style = "color:green;display:inline-block;">0</div>)');
            //$arr_user = $MMember->where('FIND_IN_SET(:id,f_uid_all)',['id' => $v['id']])->column('id');
            //团队资产
            //$teamAssets = $MMemberMutual->whereIn('uid',$arr_user)->where('status in (1,2,3) and is_exist = 1')->sum('new_price');
            //今日团队资产
            //$teamAssetsToday = $MMemberMutual->whereTime('sta_time','today')->whereIn('uid',$arr_user)->where('status in (1,2,3) and is_exist = 1')->sum('new_price');
            
//            $member_list[$k]['text']=trim('Id:'.$v['id'].'-名称:'.$v['user'].'(手机号：<div style = "color:blue;display:inline-block;">'.$v['tel'].'</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;有效团队：<div style = "color:red;display:inline-block;">'.$v['yx_team'].'</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;个人资产：<div style = "color:violet;display:inline-block;">'.$personAssets.'</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;团队资产：'.$teamAssets.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 今日团队资产：<div style = "color:green;display:inline-block;">'.$teamAssetsToday.'</div>)');
            //$member_list[$k]['parent']=!empty($v['f_uid'])?$v['f_uid']:'#';
            
            /* //判断是否是根
            if(empty($v['f_uid'])){
                $member_list[$k]['type']='root';
            }*/
            //判断是否有子
/*             $children=$MMember->where('f_uid',$v['id'])->field('id,user,f_uid')->select();
            if(!empty($children)){
                $member_list[$k]['children']=true;
            }
        } */
        
        
        
/*         return $member_list;  */
        

        $id = $request->param('id', '');

        $member_list = Db::name('member_list')->where('f_uid = '.$id)->order('id')->field('id,user,tel,yx_team,f_uid')->select();
        
/*         $member_list = Db::name('member_list')
        ->where('f_uid',$id)
        ->field('id,user,f_uid,tel,yx_team')
        // ->order($sort, $order)
        ->order('id')
        ->select(); */
         
/*         $arr = [
            'id'=>3965,
            'user'=>123123,
            'f_uid'=>0,
            'tel'=>13800138000,
            'yx_team'=>123,
            'text'=>'123123',
            'isParent'=>false
        ];
        $arr1 = [
            'id'=>3999,
            'user'=>123123,
            'f_uid'=>3970,
            'tel'=>13800138000,
            'yx_team'=>123,
            'text'=>'11111111111111111111'
        ];
        $arr2 = [
            'id'=>3997,
            'user'=>123123,
            'f_uid'=>3970,
            'tel'=>13800138000,
            'yx_team'=>123,
            'text'=>'22222222222'
        ];
        $arr3 = [
            'id'=>3998,
            'user'=>123123,
            'f_uid'=>0,
            'tel'=>13800138000,
            'yx_team'=>123,
            'text'=>'333333333333',
            'isParent'=>true,
        ];
 */
        foreach ($member_list as $k => $v){
            //个人资产
            $personAssets = $MMemberMutual->where('uid ='.$v['id'].' and status in (1,2,3) and is_exist = 1')->sum('new_price');
            
            $arr_user = $MMember->where('FIND_IN_SET(:id,f_uid_all)',['id' => $v['id']])->column('id');
            //团队资产
            $teamAssets = $MMemberMutual->whereIn('uid',$arr_user)->where('status in (1,2,3) and is_exist = 1')->sum('new_price');
            //今日团队资产
            $teamAssetsToday = $MMemberMutual->whereTime('sta_time','today')->whereIn('uid',$arr_user)->where('status in (1,2,3) and is_exist = 1')->sum('new_price');
            
            //$member_list[$k]['text']=trim('Id:'.$v['id'].'-名称:'.$v['user'].'(手机号：<div style = "color:blue;display:inline-block;">'.$v['tel'].'</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;有效团队：<div style = "color:red;display:inline-block;">'.$v['yx_team'].'</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;个人资产：<div style = "color:violet;display:inline-block;">'.$personAssets.'</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;团队资产：'.$teamAssets.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 今日团队资产：<div style = "color:green;display:inline-block;">'.$teamAssetsToday.'</div>)');
            $member_list[$k]['text']=trim('Id:'.$v['id'].'-名称:'.$v['user'].'(手机号：'.$v['tel'].' ;有效团队：'.$v['yx_team'].';个人资产：'.$personAssets.';团队资产：'.$teamAssets.'; 今日团队资产：'.$teamAssetsToday.')');
            $member_list[$k]['parent']=!empty($v['f_uid'])?$v['f_uid']:'#';
            //$member_list[$k]['pid']=$v['f_uid'];
            //判断是否是根
            if(empty($v['f_uid'])){
                $member_list[$k]['type']='root';
            }
            //判断是否有子
            $children=$MMember->where('f_uid',$v['id'])->field('id,user,f_uid')->select();
            if(!empty($children)){
                $member_list[$k]['isParent']=true;
            }
        }
/*         $member_list[]=$arr;
        $member_list[]=$arr1;
        $member_list[]=$arr2;
        $member_list[]=$arr3;  */

        return $member_list;    
    }
    
    
    public function pub_excels()
    {
        // 读取数据表信息
        $MMember = new MMember();
        $list = $MMember->getlist('1=1','id,user,tel');
        
        $xlsName = "用户表"; // 表名称
        $xlsCell = [
            ['id', '序号'],
            ['user', '名称'],
            ['tel', '电话']
        ];// 表头信息
        $this->downloadExcel($xlsName, $xlsCell, $list);// 传递参数
    }
    
    protected function downloadExcel($expTitle, $expCellName, $expTableData)
    {
        $xlsTitle    = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName    = $expTitle;
        $cellNum     = count($expCellName);// 单元格长度
        $dataNum     = count($expTableData);
        $objPHPExcel = new \PHPExcel();// 引入库
        $cellName = [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N',
            'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
        ];
        $objPHPExcel->getActiveSheet(0)
        ->mergeCells('A1:' . $cellName[$cellNum - 1] . '1');//合并单元格为表头
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle);// 设置表头单元格
        for ($i = 0; $i < $cellNum; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($cellName[$i] . '2', $expCellName[$i][1]);
            
            // 设置列
        }
        // Miscellaneous glyphs, UTF-8  循环写入数据
        for ($i = 0; $i < $dataNum; $i++) {
            for ($j = 0; $j < $cellNum; $j++) {
                $objPHPExcel->getActiveSheet(0)
                ->setCellValue($cellName[$j] . ($i + 3), $expTableData[$i][$expCellName[$j][0]]);
            }
        }
        ob_end_clean();//这一步非常关键，用来清除缓冲区防止导出的excel乱码
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//"xls"参考下一条备注
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        //"Excel2007"生成2007版本的xlsx，"Excel5"生成2003版本的xls 调用工厂类
        return $objWriter->save('php://output');
    }


    
    public function pub_excel()
    {
        $MMember = new MMember();
        $list = $MMember->getlist('1=1','id,tel');
        $xlsName = "用户表"; // 文件名
        $xlsCell = [        // 列名
            ['id', '序号'],
            ['tel', '标题']
        ];// 表头信息
        $this->downloadExcels($xlsName, $xlsCell, $list);// 传递参数       
    }
    
    protected function downloadExcels($Title, $CellNameList, $TableData)
    {
        $xlsTitle    = iconv('utf-8', 'gb2312', $Title);  // excel标题
        $fileName    = $Title;                  // 文件名称
        $cellNum     = count($CellNameList);    // 单元格名 个数
        $dataNum     = count($TableData);       // 数据 条数
        
        $obj = new PHPExcel();
        $originCell = [                           // 所有原生名
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N',
            'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
        ];
        
        //getActiveSheet(0) 获取第一张表
        $obj->getActiveSheet(0)
        ->mergeCells('A1:' . $originCell[$cellNum - 1] . '1');       //合并单元格A1-F1 变成新的A1
        
        $obj->getActiveSheet(0)->setCellValue('A1', $fileName);      // 设置第一张表中 A1的内容
        
        for ($i = 0; $i < $cellNum; $i++) {                                     // 设置第二行 ,值为字段名
            $obj->getActiveSheet(0)
            ->setCellValue($originCell[$i] . '2', $CellNameList[$i][1]);      //设置 A2-F2 的值
        }
        
        // Miscellaneous glyphs, UTF-8  循环写入数据
        for ($i = 0; $i < $dataNum; $i++) {
            for ($j = 0; $j < $cellNum; $j++) {                         // 设置第三行 ,每一行为 数据库一条数据
                $obj->getActiveSheet(0)                                 // 设 A3 值, 值为$TableData[0]['id']
                ->setCellValue($originCell[$j] . ($i + 3), $TableData[$i][$CellNameList[$j][0]]);
            }
        }
        //居中
        //$obj->getActiveSheet(0)->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        ob_end_clean();//这一步非常关键，用来清除缓冲区防止导出的excel乱码
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xlsx"');
        header("Content-Disposition:attachment;filename=$fileName.xlsx");//"xls"参考下一条备注
        $objWriter = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
        
        //"Excel2007"生成2007版本的xlsx，"Excel5"生成2003版本的xls 调用工厂类
        return $objWriter->save('php://output');
    }

}

