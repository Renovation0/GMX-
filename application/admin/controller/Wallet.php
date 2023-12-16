<?php
namespace app\admin\controller;


use think\Request;
use app\admin\model\MMutualAid;
use app\admin\model\MMutualAidOrder;
use app\admin\model\MMember;
use app\admin\model\MMemberMutual;
use think\Exception;
use think\Db;
use app\admin\model\MMutualAidLog;
use app\api\model\MConfig;
use app\admin\model\MMutualAidExamine;
use app\admin\model\MMemberLevel;
use app\admin\model\MMemberRechage;
use app\admin\model\MMemberWithdraw;

class Wallet extends Check
{   
    public function changeall(Request $request)
    {
        $MMemberWithdraw = new MMemberWithdraw();
        $ids = $request->param('ids'); //id
        if(empty($ids)){
            return json(['code' => 2,'msg' => '参数错误','data'=>[]]);
        }
        $type = intval($request->param('type', 0)); //id
        $ids = implode(",",$ids);
        $res = $MMemberWithdraw->where('id','in',$ids)->update(['type'=>$type]);
        if($res){
            return json(['code' => 1, 'msg' => '成功']);
        }
    }
    
     public function seebank(Request $request){
        $id = intval($request->param('id', 0));
        $channel = Db::name('paymant_binding')->where('id',$id)->find();
        $this->assign('bind', $channel);
        $banklist = Db::name('bankinfo')->order('id asc')->select();
        $this->assign('banklist', $banklist);
        return view();
    }
    
    public function rechargeAgree(Request $request){
        $id = intval($request->param('id', 0)); //id
        $status = intval($request->param('status', 0)); //id
        if ($id == 0 || $status == 0) {
            return json(['code' => 2, 'msg' => '未指定信息']);
        }
        if($status==2){//拒绝
            Db::name('member_bm_recharge')->where('id', $id)->update([
                'update_time'=>getIndaiTime(time()),
                'status' => $status
            ]);
            return json(['code' => 1, 'msg' => '拒绝成功']);
        }else{
            //预约开始
            try {
                Db::startTrans();
                    $res = Db::name('member_bm_recharge')->where('id',$id)->find();
                    //需要增加是否首次冲至
                    $isfirstrecharge = 0;
                    $member_info = Db::name('member_bm_recharge')->where(['uid'=>$res['uid'],'status'=>1])->find();
                    if(!$member_info){
                        $isfirstrecharge = 1;
                    }
                    Db::name('member_bm_recharge')->where('id',$id)->update([
                        'update_time'=>getIndaiTime(time()),
                        'status'=>1,
                        'isfirstrecharge'=>$isfirstrecharge
                    ]);

                    $member_info = Db::name('member_list')->where('id',$res['uid'])->field('id,tel,rechange_limit')->find();
                    $data6 = [
                        'u_id' => $member_info['id'],
                        'tel' => $member_info['tel'],
                        'o_id' => 0,
                        'former_money' => $member_info['rechange_limit'],
                        'change_money' => $res['num'],
                        'after_money' => $member_info['rechange_limit']+$res['num'],
                        'type' => 1,
                        'message' => '手动充值成功'.$res['num'],
                        'message_e' => 'Successfully recharge '.$res['num'],
                        'bo_time' => getIndaiTime(time()),
                        'status' => 90,
                    ];
                    Db::name('member_balance_log')->insert($data6);
                    
                    Db::name('member_list')->where('id', $member_info['id'])->update([
                        'rechange_limit' => Db::raw('rechange_limit +'.$res['num']),
                        'rechange_limit_total' => Db::raw('rechange_limit_total +'.$res['num'])
                    ]);
                
                Db::commit();
                return json(['code' => 1, 'msg' => '手动充值成功']);
            } catch (Exception $exception) {
                Db::rollback();
                 return json(['code' => 2, 'msg' => '异常']);
            }
        }
    }
    
    public function rechargeCancle(Request $request){
        $id = intval($request->param('id', 0)); // 通道
        $status = intval($request->param('status', 0)); // 通道
        Db::name('member_bm_recharge')->where('id', $id)->update([
                'status' => $status
            ]);
    }
    //充值记录
    public function tmNotificationList(Request $request){
        $type = intval($request->param('type', 0)); // 通道
        $status = intval($request->param('status', 0)); // 状态
        $serach = $request->param('serach', ''); // 关键字搜索  名称/龙珠/收益天数
        $add_time_s = $request->param('add_time_s', '');
        $add_time_e = $request->param('add_time_e', ''); 
        $allParams = ['query' => $request->param()];
        $this->assign('param_type', $type);
        $this->assign('param_status', $status);
        $this->assign('param_serach', $serach);
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if($status != 0){
            $where .= ' and `status` = '.$status;
        }
        if($type != 0){
            $where .= ' and `type` = '.$type;
        }
        if($serach != ''){
            $where .= ' and `hash` like \'%'.$serach.'%\' OR `user` like \'%'.$serach.'%\' OR `tel` like \'%'.$serach.'%\' OR `num` like \'%'.$serach.'%\'';
        }
        if ($add_time_s != '') {
            $where .= " and `create_time` >= " . strtotime($add_time_s);
        }
        if ($add_time_e != '') {
            $where .= " and `create_time` <= " . strtotime($add_time_e);
        }

        $MMemberRechage = new MMemberRechage();
        $list = $MMemberRechage->getlists($where, $pageSize, $allParams, 'id desc');
        foreach ($list as $k => $v){
            $list[$k]['create_time'] = $v['create_time'];//-9000;
            if($v['update_time'] > 0){
                $list[$k]['update_time'] = $v['update_time'];//-9000;
            }
            $name = Db::name('channel')->field('name')->where('id='.$list[$k]['type'])->find();
            $list[$k]['channel'] = $name['name']==null?'无':$name['name'];
        }
        $this->assign('orderList',$list);   
        
        $channelList = Db::name('channel')->field('id,name,bname')->order('recharge_order desc')->select();
        $this->assign('channelList', $channelList);

        return view();
    }
    
    
    //提现记录
    public function tmWithdrawList(Request $request){
        $type = intval($request->param('type', 0)); // 通道
        $status = intval($request->param('status', -1)); // 状态
        $serach = $request->param('serach', ''); // 关键字搜索  名称/龙珠/收益天数
        $add_time_s = $request->param('add_time_s', '');
        $add_time_e = $request->param('add_time_e', '');
        $allParams = ['query' => $request->param()];
        $this->assign('param_type', $type);
        $this->assign('param_status', $status);
        $this->assign('param_serach', $serach);
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if($status != -1){
            $where .= ' and `status` = '.$status;
        }
        if($type != 0){
            $where .= ' and `type` = '.$type;
        }
        if($serach != ''){
            //$where .= ' and `name` like \'%'.$serach.'%\' OR `price` like \'%'.$serach.'%\' OR `rate` like \'%'.$serach.'%\'';
            $where .= ' and `user` like \'%'.$serach.'%\' OR `num` like \'%'.$serach.'%\' OR `tel` like \'%'.$serach.'%\' OR `order_id` like \'%'.$serach.'%\' OR `hash` like \'%'.$serach.'%\'';
        }
        if ($add_time_s != '') {
            $where .= " and `create_time` >= " . strtotime($add_time_s);
        }
        if ($add_time_e != '') {
            $where .= " and `create_time` <= " . strtotime($add_time_e);
        }
        
        $MMemberWithdraw = new MMemberWithdraw();
        $list = $MMemberWithdraw->getlists($where, $pageSize, $allParams, 'id desc');
        foreach ($list as $k => $v){
            $list[$k]['create_time'] = $v['create_time'];//-9000;
            if($v['update_time'] > 0){
                $list[$k]['update_time'] = $v['update_time'];//-9000;
            }
            $name = Db::name('channel')->field('name')->where('id='.$list[$k]['type'])->find();
            $list[$k]['channel'] = $name['name']==null?'无':$name['name'];
            $member = Db::name('member_list')->field('withdrawfail')->where('id='.$list[$k]['uid'])->find();
            
            $list[$k]['withdrawfail'] = $member['withdrawfail'];
        }
        $this->assign('orderList',$list);
        $channelList = Db::name('channel')->field('id,name,bname')->order('withdraw_order desc')->select();
        $this->assign('channelList', $channelList);
        return view();
    }
    
    
    // 验证 通过/拒绝
    public function tmagree(Request $request)
    {
        $id = intval($request->param('id', 0)); //id
        $status = intval($request->param('status', 0)); //id
        if ($id == 0 || $status == 0) {
            return json(['code' => 2, 'msg' => '未指定信息']);
        }
        
        $MMemberWithdraw = new MMemberWithdraw();
        
        $info = $MMemberWithdraw->getInfo(['id'=>$id]);
        
        if($info['type'] == 0){
            return json(['code' => 2, 'msg' => '请选择通道']);
        }
        
        $MMember = new MMember();
        
        $member_info = $MMember->getInfo(['id'=>$info['uid']],'balance');
        
        $ban_info = Db::name('paymant_binding')->where('id',$info['bank_id'])->find();
        
        if($status == 3){
            
            $channel = Db::name('channel')->where('id',$info['type'])->find();
            $c=controller('api/'.$channel['bingfile']);
            $result1 = $c->PaymentMoney($info,$ban_info,$channel);
            $result1 = json_decode($result1,true);
            if($result1['msg']=="success"){
                 Db::name('member_bm_withdraw')->where('id',$id)->update([
                    // 'hash'=>$orderno,//$data['orderno'],
                    'status'=>3
                ]);
                return json(['code' => 1, 'msg' => '成功，请等待处理']);
            }else{
                return json(['code' => 2, 'msg' => json_encode($result1)]);
            }
            
        }else{
            try {
                if($info['status'] != 0 && $info['status'] != 3){
                    return json(['code' => 2,'msg' => '该订单已处理']);
                }
                Db::startTrans();
                
                Db::name('member_bm_withdraw')->where('id',$id)->update([
                    'update_time'=>time(),
                    'status'=>2
                ]);
                    
                Db::name('member_balance_log')->insert([
                    'u_id' => $info['uid'],
                    'tel' => $info['tel'],
                    'former_money' => $member_info['balance'],
                    'change_money' => $info['num'],
                    'after_money' => $member_info['balance'] + $info['num'],
                    'message' => '提现失败退回'.$info['num'],
                    'message_e' => 'Withdrawal failed and returned '.$info['num'],
                    'type' => 2,
                    'bo_time' => time(),
                    'status' => 91
                ]);
                
                Db::name('member_list')->where('id', $info['uid'])->update([
                    'balance' => Db::raw('balance +'.$info['num'])
                ]);
                            
                Db::commit();
                return json(['code' => 1,'msg' => '操作成功']);
            } catch (Exception $exception) {
                Db::rollback();
                return json(['code' => 2,'msg' => '操作失败 '.$exception->getMessage()]);
            }
        }
        

    }
    
    public function generateSignString($data,$key) {
        if (!empty($data)) {
            $string = '';
            foreach($data as $k => $v){
                $string .= $k.'='.$v.'&';
            }
        }
        $string .= 'key='.$key;
        return $string;
    }
    
        
    
    function send_post($url, $post_data) {
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }
        
    public function tmtypecheck(Request $request)
    {
        $MMemberWithdraw = new MMemberWithdraw();
        
        if ($request->isAjax()) {
            $id = intval($request->param('id', 0)); //id
            $type = intval($request->param('type', 0)); //id
            
            if(empty($id)){
                return json(['code' => 2,'msg' => '参数错误','data'=>[]]);
            }
            
            $res = $MMemberWithdraw->where('id',$id)->update(['type'=>$type]);
            if($res){
                return json(['code' => 1, 'msg' => '成功']);
            }
        }else{
            $channelList = Db::name('channel')->field('id,name,bname')->where('withdraw_status=1')->order('withdraw_order desc')->select();
            $this->assign('channelList', $channelList);
            $MConfig = new MConfig();
            $config_val = $MConfig->readConfig(['PAY_ONE','PAY_TWO','PAY_ONE_NAME','PAY_TWO_NAME','PAY_THREE','PAY_THREE_NAME','PAY_FOUR','PAY_FOUR_NAME'],2);
            $data = [];
            if($config_val[0] == 1){
                $pay1 = $config_val[2];
            }else{
                $pay1 = $config_val[2].'-已关闭';
            }
            if($config_val[1] == 1){
                $pay2 = $config_val[3];
            }else{
                $pay2 = $config_val[3].'-已关闭';
            }
            if($config_val[4] == 1){
                $pay3 = $config_val[5];
            }else{
                $pay3 = $config_val[5].'-已关闭';
            }
            if($config_val[6] == 1){
                $pay4 = $config_val[7];
            }else{
                $pay4 = $config_val[7].'-已关闭';
            }
            
            $id = intval($request->param('id', 0)); //id
            if ($id == 0) {
                return json(['code' => 2, 'msg' => '未指定信息']);
            }
            
            $info = $MMemberWithdraw->getInfo(['id'=>$id]);
            
            $this->assign('member', $info);
            $this->assign('pay1', $pay1);
            $this->assign('pay2', $pay2);
            $this->assign('pay3', $pay3);
            $this->assign('pay4', $pay4);
            
            return view();
        }
        
    }
    
    
    public function checkordersub(Request $request){
        $MMemberWithdraw = new MMemberWithdraw();
        
        $id = intval($request->param('id', 0)); //id
        $status = intval($request->param('status', 0)); //id
        
        if(empty($id) || empty($status)){
            return json(['code' => 2,'msg' => '参数错误','data'=>[]]);
        }
        if($status != 1){
            return json(['code' => 2,'msg' => '订单状态错误','data'=>[]]);
        }
        // $res = $MMemberWithdraw->where('id',$id)->update(['type'=>$type]);
        // if($res){
        //     return json(['code' => 1, 'msg' => '成功']);
        // }
        
        
        $withdraw_info = Db::name('member_bm_withdraw')->where('id = '.$id)->find();
        if(empty($withdraw_info)){
            Db::name('pay_info')->insert(['text'=>'提现手动执行回调：未找到该订单！','time'=>date('Y-m-d H:i:s',time())]);
            return json(['code' => 2, 'msg' => '未找到该订单']);
            exit();
        }
                        
        if($withdraw_info['status'] != 0 && $withdraw_info['status'] != 3){
            Db::name('pay_info')->insert(['text'=>'提现手动执行回调：该订单已处理','time'=>date('Y-m-d H:i:s',time())]);
            return json(['code' => 2, 'msg' => '该订单已被处理']);
            exit();
        }
        
        $member_info = Db::name('member_list')->where('id',$withdraw_info['uid'])->field('id,tel,balance')->find();

        //预约开始
        try {
            Db::startTrans();

                $res = Db::name('member_bm_withdraw')->where('id',$id)->update([
                    'update_time'=>time(),
                    'status'=>1
                ]);
                
                Db::name('member_list')->where('id', $withdraw_info['uid'])->update([
                    'balance_total' => Db::raw('balance_total +'.$withdraw_info['num'])
                ]);
                
                Db::name('member_balance_log')->insert([
                    'u_id' => $withdraw_info['uid'],
                    'tel' => $member_info['tel'],
                    'former_money' => 0,
                    'change_money' => $withdraw_info['num'],
                    'after_money' => 0,
                    'message' => '提现成功'.$withdraw_info['num'],
                    'message_e' => 'Withdrawal Successful'.$withdraw_info['num'],
                    'type' => 2,
                    'bo_time' => getIndaiTime(time()),
                    'status' => 92
                ]);
            
            Db::commit();
            
            Db::name('pay_info')->insert(['text'=>'提现手动执行回调：'.$id.'完成。','time'=>date('Y-m-d H:i:s',time())]); 
            //echo 'OK';
            //return"OK";
            return json(['code' => 1,'msg' => '成功']);
        } catch (Exception $exception) {
            Db::rollback();
            Db::name('pay_info')->insert(['text'=>'提现手动执行回调：'.$id.'失败，原因：'.$exception->getMessage(),'time'=>date('Y-m-d H:i:s',time())]); 
            //echo 'OK';
            //return"OK";
            return json(['code' => 2,'msg' => '提现手动执行回调：'.$id.'失败，原因：'.$exception->getMessage()]);
        }
    }
    
}

