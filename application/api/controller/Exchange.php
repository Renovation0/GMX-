<?php
namespace app\api\controller;

use think\Request;
use think\Db;
use think\facade\Cache;
use app\api\model\MExchange;
use app\api\model\MConfig;
use think\Exception;
use app\api\model\MMember;

class Exchange extends Common
{
    
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////                         交易大厅                                                    //////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    
    // 法币交易k线图
    public function kLine()
    {
        $list = Db::name('k')->order('time desc')->limit(7)->field('value,time')->select();
        $data['value'] = [];
        $data['time'] = [];

/*         foreach ($list as $k => $v){
            //$list[$k]['time'] = date('m-d', $v['time']);
            $data['value'][] = floatval($v['value']);
            $data['time'][] = date('m-d', $v['time']);
        } */
        for($i = count($list)-1;$i >= 0 ;$i--){
            $data['value'][] = floatval($list[$i]['value']);
            $data['time'][] = date('m-d', $list[$i]['time']);
        }
      
        return json(['code'=>1,'data'=>$data]);
    }
    
    
    // 大厅列表
    public function coinList(Request $request)
    {
        $u_id = $this->userinfo['user_id'];
        $page = intval($request->post('page', 1));// 列表页码
        $type = intval($request->post('type', 1));//1买单 2卖单
        
        if (empty($page) || empty($type))
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'参数错误']);

        if($type == 1){
            $where = 'a.buy_uid';
            $condition = 'a.buy_uid'; 
        }elseif($type == 2){
            $where = 'a.sell_uid';
            $condition = 'a.sell_uid'; 
        }else{
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'参数错误']);
        }  
        $where .= ' !=0 AND '.$where.'!='.$u_id.' AND a.status = 0 AND b.status = 2';

        $count = Db::name('order_coin')->alias('a')
        ->join('zm_member_list b', $condition.'=b.id', 'left')
        ->where($where)
        //->where('a.status', 0)
        //->where('b.status != 3')
        ->count();

        $page_size = 5;
        $pages = ceil($count / $page_size);
        $offset = ($page - 1) * $page_size;
        $list = Db::name('order_coin')->alias('a')
        ->join('zm_member_list b', $condition.'=b.id', 'left')
        ->field('a.id,a.num,a.price,a.total_price,b.id as user_id,b.user,b.u_img,b.level')
        //->where('a.status', 0)
        ->where($where)
        //->where('b.status != 3')
        ->order('a.start_time desc')
        ->limit($offset, $page_size)
        ->select();

        //        if (!empty($list)){
        //        }
        //        $list = shuffle($list);
        //        echo '<pre>';
        //        var_dump($list);die;
        if(!empty($list)){
            foreach ($list as $k => $v) {
                $list[$k]['price'] = floatval($v['price']);
                $list[$k]['total_price'] = floatval($v['total_price']);
                /*             $list[$k]['pay_type'] = explode(',', $v['pay_type']); */
                $list[$k]['avatar'] = 'http://' . $_SERVER['HTTP_HOST'] . $v['u_img'];
                $list[$k]['level_logo'] = 'http://' . $_SERVER['HTTP_HOST'] . Db::name('member_level')->where('id', $v['level'])->value('level_logo');
                $member_pay_list = Db::name('paymant_binding')->where('u_id = '.$v['user_id'].' AND status in (1,2,3,4)')->field('status')->select();
                
                $paymant = array_column($member_pay_list, 'status');
                
                $list[$k]['paymant'] = $paymant;
                unset($list[$k]['level']);
                unset($list[$k]['u_img']);
            }
            $list = $this->retain_key_shuffle($list);
        }
        
        $list = array_values($list);
        
        $data = [
            'count' => $count,
            'pages' => $pages,
            'list' => $list,
        ];

        return json(['code'=>1,'data'=>$data]);
    }
    
    //打散数组
    function retain_key_shuffle($list)
    {
        if (!is_array($list)) {
            return $list;
        }
        $keys = array_keys($list);
        shuffle($keys);
        $random = array();
        foreach ($keys as $key) {
            $random[$key] = $list[$key];
        }
        return $random;
    }
    
    
    
    //卖币
    public function sellOrderDetails(Request $request)
    {    
        $user_id = $this->userinfo['user_id'];
        $id = $request->post('id');         
        $num = getValue(abs(intval($request->post('num', 0))));
        $password = trim($request->post('password', ''));
        
        if (empty($num))
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'请输入卖出数量']);   
        if (empty($password) || $password == '')
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'请输入交易密码']); 
            
        if (!$id && (is_int($num) != true))
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'参数错误']);  

        if (Cache::get('sellOrderDetails'.$user_id) == 2){
            return json(['code' => 2, 'msg' => '请勿频繁操作', 'data'=>[]]);
        }
        Cache::set('sellOrderDetails'.$user_id,2,10);            
        
        $MExchange = new MExchange();
        return $MExchange->sellTo($id, $user_id, $num, $password);            
    }    
            

    //买币
    public function buyOrderDetails(Request $request)
    {
        $user_id = $this->userinfo['user_id'];
        $id = $request->post('id');
        $num = getValue(abs(intval($request->post('num', 0))));
        $password = trim($request->post('password', ''));
        
        if (empty($num))
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'请输入买入数量']);
        if (empty($password) || $password == '')
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'请输入交易密码']);
            
        if (!$id && (is_int($num) != true))
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'参数错误']);
            
        if (Cache::get('buyOrderDetails'.$user_id) == 2){
            return json(['code' => 2, 'msg' => '请勿频繁操作', 'data'=>[]]);
        }
        Cache::set('buyOrderDetails'.$user_id,2,10);
        
        $MExchange = new MExchange();
        return $MExchange->buyTo($id, $user_id, $num, $password);
    } 
    
    
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////                         发布交易                                                    //////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    //发布渲染
    public function releaseindex(){
        $MConfig = new MConfig();
        $Mconfig_val = $MConfig->readConfig(['JY_PRICE_RANGE','JY_MAX_NUM','JY_MIN_NUM'],2);
        
        $money = Db::name('k')->where('1=1')->order('id desc')->value('value');
        
        $info['max_price'] = $money+$Mconfig_val[0];
        $info['min_price'] = $money-$Mconfig_val[0];
        $info['max_num'] = $Mconfig_val[1];
        $info['min_num'] = $Mconfig_val[2];
        
        return json(['code'=>1,'data' =>$info,'msg'=>'success']);
    }
    
    
    //发布
    public function release(Request $request){
        $user_id = $this->userinfo['user_id'];
        $price = getValue(abs(trim($request->post('price'))),'float');
        $num = getValue(abs(intval($request->post('num', 0))),'int');
        $password = trim($request->post('password', ''));
        $type = intval($request->post('type', 1));//1出售 2购买

        if(empty($price) || $price == 0){
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'请输入价格']);
        }
        if(empty($num) || $num == 0){
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'请输入数量']);
        }
        if (empty($password) || $password == ''){
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'请输入交易密码']);
        }                
        
        if (Cache::get('release'.$user_id) == 2){
            return json(['code' => 2, 'msg' => '请勿频繁操作', 'data'=>[]]);
        }
        Cache::set('release'.$user_id,2,10);
        
        $mexchange = new MExchange();
        return $mexchange->release_ykb($user_id, $type, $price, $num, $password);        
    }
    
    
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////                         我的发布                                                    //////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    //我的发布
    public function myrelease(Request $request){
        $u_id = $this->userinfo['user_id'];
        $page = intval($request->post('page', 1));
        $type = intval($request->post('type', 1));//1出售 2购买
        $status = intval($request->post('status', 1));
        
        if (empty($page) || empty($type) || empty($status))
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'参数错误']);
            
        if($type == 1){
            $where_now = 'a.sell_uid';
            switch ($status) {
                case 1:
                    $where = 'a.status in (0,1,2,3,4,5,6,7) and a.sell_uid=' . $u_id;//0挂单中3已完成 5申诉成功6申诉失败7取消
                    break;
                case 2:
                    $where = 'a.status in (1,2) and a.sell_uid=' . $u_id;//1已匹配2已上传
                    break;
                case 3:
                    $where = 'a.status in (3,4,5,6,7) and a.sell_uid=' . $u_id;//3已完成4申诉中5申诉成功6申诉失败7取消
                    break;
            }
        }elseif($type == 2){
            $where_now = 'a.buy_uid';
            switch ($status) {
                case 1:
                    $where = 'a.status in (0,1,2,3,4,5,6,7) and a.buy_uid=' . $u_id;//0挂单中3已完成 5申诉成功6申诉失败7取消
                    break;
                case 2:
                    $where = 'a.status in (1,2) and a.buy_uid=' . $u_id;//1已匹配2已上传
                    break;
                case 3:
                    $where = 'a.status in (3,4,5,6,7) and a.buy_uid=' . $u_id;//3已完成4申诉中5申诉成功6申诉失败7取消
                    break;
            }
        }else{
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'参数错误']);
        }
        if(!isset($where) || empty($where)){
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'参数错误']);
        }              

        $count = Db::name('order_coin')->alias('a')
        ->join('zm_member_list b', $where_now.'=b.id', 'left')
        ->where($where)
/*         ->where($where.'=' . $u_id)
        ->whereIn('a.status', '0,3')
        ->where('b.status', 2) */
        ->count();
        $page_size = 5;
        $pages = ceil($count / $page_size);
        $offset = ($page - 1) * $page_size;
        $list = Db::name('order_coin')->alias('a')
        ->join('zm_member_list b', $where_now.'=b.id', 'left')
        ->field('a.id,a.initial_num,a.num,a.price,a.total_price,a.start_time,a.status,a.recevice_time,a.voucher_time,a.end_time,b.id as user_id,b.user,b.u_img,b.level')
        ->where($where)
/*         ->whereIn('a.status', '0,3')
        ->where($where.'=' . $u_id)
        ->where('b.status', 2) */
        ->order('a.start_time desc')
        ->limit($offset, $page_size)
        ->select();


        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig(['JY_UPVOUCHER','JY_SUB','JY_END'],2);
        
        foreach ($list as $k => $v) {
            $list[$k]['price'] = floatval($v['price']);
            $list[$k]['total_price'] = floatval($v['total_price']);
            $list[$k]['avatar'] = 'http://' . $_SERVER['HTTP_HOST'] . $v['u_img'];
            $list[$k]['level_logo'] = 'http://' . $_SERVER['HTTP_HOST'] . Db::name('member_level')->where('id', $v['level'])->value('level_logo');
            $member_pay_list = Db::name('paymant_binding')->where('u_id = '.$v['user_id'].' AND status in (1,2,3,4)')->field('status')->select();
            $paymant = array_column($member_pay_list, 'status');
            $list[$k]['paymant'] = $paymant;
            $list[$k]['start_time'] = date('Y-m-d',$v['start_time']);
            unset($list[$k]['level']);
            unset($list[$k]['u_img']);
            switch ($v['status']) {
                case 0:
                    $list[$k]['status_type'] = '匹配中';
                    break;
                case 1:
                    $recevice_time_end = $config_val[0]*60;
                    $list[$k]['status_type'] = '已匹配';
                    $list[$k]['recevice_time'] = date('Y-m-d H:i:s',$v['recevice_time']); 
                    //$list[$k]['recevice_time_end'] = date('Y-m-d H:i:s',$v['recevice_time']+$recevice_time_end);
                    $list[$k]['time'] = ($v['recevice_time']+$recevice_time_end)-time() > 0 ? ($v['recevice_time']+$recevice_time_end)-time() : 0; 
                    unset($list[$k]['voucher_time']);
                    unset($list[$k]['end_time']);
                    break;
                case 2:
                    $voucher_time_end = $config_val[1]*60;
                    $list[$k]['status_type'] = '已上传';
                    $list[$k]['voucher_time'] = date('Y-m-d H:i:s',$v['recevice_time']); 
                    //$list[$k]['voucher_time_end'] = date('Y-m-d H:i:s',$v['recevice_time']+$voucher_time_end);
                    $list[$k]['time'] = ($v['voucher_time']+$voucher_time_end)-time() > 0 ? ($v['voucher_time']+$voucher_time_end)-time() : 0; 
                    unset($list[$k]['recevice_time']);
                    unset($list[$k]['end_time']);
                    break;
                case 3:
                    $end_time_end = $config_val[2]*60;
                    $list[$k]['status_type'] = '已完成';
                    $list[$k]['end_time'] = date('Y-m-d H:i:s',$v['end_time']);
                    //$list[$k]['end_time_end'] = date('Y-m-d H:i:s',$v['end_time']+$end_time_end);
                    $list[$k]['time'] = ($v['voucher_time']+$end_time_end)-time() > 0 ? ($v['voucher_time']+$end_time_end)-time() : 0; 
                    unset($list[$k]['recevice_time']);
                    unset($list[$k]['voucher_time']);
                    break;
                case 4:
                    $list[$k]['status_type'] = '申诉中';
                    break;
                case 5:
                    $list[$k]['status_type'] = '申诉成功';
                    break;
                case 6:
                    $list[$k]['status_type'] = '申诉失败';
                    break;
                case 7:
                    $list[$k]['status_type'] = '已取消';
                    break;
            }
        }
        
        $data=[
            'count' => $count,
            'pages' => $pages,
            'list' => $list
        ];

        return json(['code' => 1,'msg' => 'success', 'data' => $data]);
    }
    

    //取消发布
    public function unpublish(Request $request){
        $u_id = $this->userinfo['user_id'];
        $id = intval($request->post('id', 0));
        $type = intval($request->post('type', 1));//1出售 2购买
        
        if (!$id)
            return json(['code' => 1,'msg' => '参数错误']);
        
        if (Cache::get('unpublish'.$u_id) == 2){
            return json(['code' => 2, 'msg' => '取消发布执行中', 'data'=>[]]);
        }
        Cache::set('unpublish'.$u_id,2,10);            
        
        $MExchange = new MExchange();
        
        return $MExchange->unpublish($id, $u_id, $type);
        
    }
    
    //订单详情
    //上传凭证渲染\查看凭证  
    public function uploadVoucherykb(Request $request)
    {
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig(['JY_UPVOUCHER','JY_SUB','JY_END'],2);
        
        $u_id = $this->userinfo['user_id'];
        $id = intval($request->post('id', 0));//指定ID
               
        $order = Db::name('order_coin')->where('id', $id)->find();
        if (!$order)
            return json(['code' => 2, 'msg' => '参数错误', 'data'=>[]]);
        //查询对方信息
        if ($order['buy_uid'] == $u_id) {//自己是买家
            $user_where = $order['sell_uid'];
        } else if ($order['sell_uid'] == $u_id) {
            $user_where = $order['buy_uid'];
        } else {
            return json(['code' => 2, 'msg' => '网络繁忙', 'data'=>[]]);
        }
        if(empty($user_where)){
            return json(['code' => 2, 'msg' => '网络繁忙', 'data'=>[]]);
        }

        //会员信息
        if ($order['status'] == 0) {//挂单中 显示自己的
            $user = Db::name('member_list')->where('id', $order['buy_uid'])->field('user,u_img,level,tel,urgent_mobile')->find();
        } else {
            $user = Db::name('member_list')->where('id', $user_where)->field('user,u_img,level,tel,urgent_mobile')->find();
        }
        //        $user = Db::name('user')->where('id', $user_where)->field('avatar,username,mobile,level,urgent_mobile')->find();
        $user['u_img'] = 'http://' . $_SERVER['HTTP_HOST'] . $user['u_img'];
        $user['level'] = 'http://' . $_SERVER['HTTP_HOST'] . Db::name('member_level')->where('id', $user['level'])->value('level_logo');
        //订单信息
        $coin['orderInfo'] = $order['buy_uid'] == $u_id ? 'buy' : 'sell';
        $coin['price'] = floatval($order['price']);
        $coin['num'] = floatval($order['num']);
        $coin['voucher'] = $order['voucher'] != '' ? 'http://' . $_SERVER['HTTP_HOST'] . $order['voucher'] : $order['voucher'];
        $coin['total_price'] = floatval($order['total_price']);
        $coin['pay_type'] = explode(',', $order['pay_type']);
        $coin['status'] = $order['status'];
        $coin['pay_way'] = [];
        $coin['less_time'] = 0;
        $coin['voucher_button'] = $order['status'] == 1 && $u_id == $order['buy_uid'] ? 1 : 0;
        $coin['confirm_button'] = $order['status'] == 2 && $u_id == $order['sell_uid'] ? 1 : 0;
        $coin['reply_button'] = $order['status'] == 2 ? 1 : 0;
        if ($order['status'] == 1 && $order['buy_uid'] == $u_id) {//匹配订单 自己是买家 可以上传凭证 展示对方的付款方式
            $coin['msg'] = '上传凭证';                                          //->whereIn('status', $coin['pay_type'])
            $pay_way = Db::name('paymant_binding')->where('u_id', $order['sell_uid'])->field('name,account_num,bank_num,receive_qrcode,status')->select();
            foreach ($pay_way as $k => $v) {
                if($v['receive_qrcode'] != ''){
                    $pay_way[$k]['receive_qrcode'] = 'http://' . $_SERVER['HTTP_HOST'] .$v['receive_qrcode'];
                }
            }
/*             $new_pay_way = array();
            foreach ($pay_way as $k => $v) {
                $new_pay_way[$v['status']] = ['name' => $v['name'], 'account_num' => $v['account_num'], 'bank_num' => $v['bank_num'], 'receive_qrcode' => 'http://' . $_SERVER['HTTP_HOST'] . $v['receive_qrcode']];
            } */
            $coin['pay_way'] = $pay_way;
            $coin['less_time'] = $config_val[0] * 60 - (time() - $order['recevice_time']) > 0 ? $config_val[0] * 60 - (time() - $order['recevice_time']) : 0;
        } elseif ($order['status'] == 1 && $order['sell_uid'] == $u_id) {// 自己是卖家 等待对方上传凭证
            $coin['msg'] = '等待对方上传凭证';
            $coin['less_time'] = $config_val[0] * 60 - (time() - $order['recevice_time']) > 0 ? $config_val[0] * 60 - (time() - $order['recevice_time']) : 0;
        } elseif ($order['status'] == 2 && $order['buy_uid'] == $u_id) {//已上传凭证 自己是买家 等待对方确认
            $coin['msg'] = '等待对方确认订单';
            $coin['less_time'] = $config_val[1] * 60 - (time() - $order['voucher_time']) > 0 ? $config_val[1] * 60 - (time() - $order['voucher_time']) : 0;
        } elseif ($order['status'] == 2 && $order['sell_uid'] == $u_id) {//已上传凭证 自己是卖家 确认
            $coin['msg'] = '确认订单';
            $coin['less_time'] = $config_val[1] * 60 - (time() - $order['voucher_time']) > 0 ? $config_val[1] * 60 - (time() - $order['voucher_time']) : 0;
        } elseif ($order['status'] == 3) {
            $coin['msg'] = '';
            $coin['less_time'] = 0;
        } elseif ($order['status'] == 4) {
            $coin['msg'] = '申诉中';
            $coin['less_time'] = 0;
        } else {
            return json(['code' => 2, 'msg' => '参数错误', 'data'=>[]]);
        }
        $data['order'] = $user;
        $data['coin'] = $coin;
        //$data['pay_way'] = json_decode(json_encode($new_pay_way),true);
        
        return json(['code' => 1,'msg' => '','data'=>$data]);
    }
    
    // 上传凭证
    public function uploadVoucherykbPost(Request $request)
    {
        $u_id = $this->userinfo['user_id'];
        //$type = intval($request->post('type'));
        $img = $request->post('voucher');
        $orderId = $request->post('id');
        if (!$img || !$orderId)
            return json(['code' => 2, 'msg' => '参数错误']);
        
        $member_info = Db::name('member_list')->where('id',$u_id)->field('balance,status')->find();
        if($member_info['status'] == 1){
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'请先激活账号']);
        }
        if($member_info['status'] == 3){
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'账号已被冻结，无法发布']);
        }
            
            
        $order = Db::name('order_coin')->where('id =' . $orderId)->find();
        
        if (!$order || $order['buy_uid'] != $u_id)
            return json(['code' => 2, 'msg' => '该订单不存在', 'data'=>[]]);
            
        if($order['status'] != 1){
            return json(['code' => 2, 'msg' => '该订单信息错误', 'data'=>[]]);
        }
        //消除反斜杠
        $img = $this->updatexg($img);
        
        $data['voucher'] = $img;
        //$data['pay_type'] = $type;
        $data['status'] = 2;
        $data['voucher_time'] = time();
        try {
            Db::startTrans();
            Db::name('order_coin')->where('id', $orderId)->update($data);
            Db::name('order_coin_log')->insert([
                'orderNo' => $order['orderNo'],
                'order_id' => $orderId,
                'uid' => $order['buy_uid'],
                'phone' => $order['buy_user'],
                'message' => '上传凭证',
                'time' => time(),
            ]);
            Db::commit();
            // $sms = new Sms();
            // $sms->sendPostNew($order['sell_user'],3);
            return json(['code' => 1,'msg' => '上传成功']);
        } catch (Exception $exception) {
            Db::rollback();
            return json(['code' => 2,'msg' => '上传失败'.$exception->getMessage()]);
        }
        
    }
    
    
    
    
    // 订单申诉

    public function applyOrderykb(Request $request)
    {
        $u_id = $this->userinfo['user_id'];
        
        $imgs = $request->post('apply/a');
        $orderId = $request->post('id');
        $content = getValue($request->post('content'));
        if (!$orderId)
            return json(['code' => 2, 'msg' => '参数错误', 'data'=>[]]);
        if (!$imgs || !$content)
            return json(['code' => 2, 'msg' => '请填写完整的信息', 'data'=>[]]);           
            
        $member_info = Db::name('member_list')->where('id',$u_id)->field('balance,status')->find();
        if($member_info['status'] == 1){
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'请先激活账号']);
        }
        if($member_info['status'] == 3){
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'账号已被冻结，无法发布']);
        }
            
        $order = Db::name('order_coin')->where('id =' . $orderId)->find();
        if (!$order || $order['status'] != 2 || $order['sell_uid'] != $u_id)
            return json(['code' => 2, 'msg' => '该订单不存在', 'data'=>[]]);

        $data['reply_img'] = implode(',', $imgs);
        $data['reply_content'] = $imgs;
        $data['status'] = 4;
        $data['reply_time'] = time();
        try {
            Db::startTrans();
            Db::name('order_coin')->where('id', $orderId)->update($data);
            Db::name('order_coin_log')->insert([
                'orderNo' => $order['orderNo'],
                'order_id' => $orderId,
                'uid' => $u_id,
                'phone' => $order['sell_user'],
                'message' => '订单申诉',
                'time' => time(),
            ]);
            Db::commit();
            return json(['code' => 1,'msg' => '申诉成功',]);
        } catch (Exception $exception) {
            Db::rollback();
            return json(['code' => 2,'msg' => '申诉失败'.$exception->getMessage()]);
        }
    }
    
    
    
    
    
    /** 确认订单渲染
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
 /*    public function confirmYkbOrderIndex(Request $request)
    {
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig(['JY_SUB','JY_END'],2);
        
        $u_id = 2;//$this->userinfo['user_id'];
        
        $id = $request->post('id');
        if (!$id)
            return json(['code' => 2, 'msg' => '参数错误', 'data'=>[]]);
            
            $order = Db::name('order_coin')->where('id =' . $id . ' and is_exist = 1')->find();
            if (!$order)
                return json(['code' => 2, 'msg' => '该订单不存在', 'data'=>[]]);
                
                $purchaseList = Db::name('mutualaid_list')->field('id,logo,name,min_price,max_price,days,type,rate')->select();
                //查询买家会员信息
                if ($order['buy_uid'] == $u_id) {//自己是买家 查看卖家的信息 需要上传凭证
                    $userId = $order['sell_uid'];
                    $data['status'] = 'buy';
                } elseif ($order['sell_uid'] == $u_id) {
                    $userId = $order['buy_uid'];
                    $data['status'] = 'sell';
                } else {
                    return json(['code' => 2, 'msg' => '参数错误', 'data'=>[]]);
                }
                $userInfo = Db::name('member_list')->where('id', $userId)->field('user,u_img,level,tel,urgent_mobile')->find();
                foreach ($purchaseList as $k => $v) {
                    if ($order['purchase_id'] == $v['id']) {
                        $data['purchase']['name'] = $v['name'];
                        $data['purchase']['order_id'] = $order['id'];
                        $data['purchase']['days'] = $v['days'];
                        $data['purchase']['status'] = $order['status'];
                        $data['purchase']['create_time'] = date('Y-m-d H:i:s',$order['pay_time']);
                        $data['purchase']['reward'] = round($v['days'] * $v['rate'] * $order['price'] / 100, 2);
                        if ($v['type'] == 1) {//普通2合约
                            $data['purchase']['rate'] = $v['days'] . '天/' . $v['days'] * $v['rate'] . '%';
                        } else {
                            $data['purchase']['rate'] = $v['days'] . '天/每天' . $v['rate'] . '%';
                        }
                        $data['purchase']['type'] = $v['type'];//1普通合约 2合约宠物
                        $data['purchase']['price'] = $order['price'];
                        $data['purchase']['logo'] = 'http://' . $_SERVER['HTTP_HOST'] . $v['logo'];
                        $data['userInfo']['deal_time'] = $config_val * 60 - (time() - $order['pay_time']) > 0 ? $config_val * 60 - (time() - $order['pay_time']) : 0;
                        break;                          //Config::get('site.huzhuConfirm')
                    }
                }
                $data['voucher'] = 'http://' . $_SERVER['HTTP_HOST'] . $order['voucher'];
                $data['userInfo']['urgent_tel'] = $userInfo['urgent_mobile'];
                $data['userInfo']['username'] = $userInfo['user'];
                $data['userInfo']['tel'] = $userInfo['tel'];
                $data['userInfo']['order_id'] = $order['id'];
                $data['userInfo']['avatar'] = 'http://' . $_SERVER['HTTP_HOST'] . $userInfo['u_img'];
                
                return json(['code' => 1,'msg' => '','data'=>$data]);
    }
 */    

    
    /** 确认订单
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function confirmYkbOrder(Request $request)
    {
        $MMember = new MMember();//MMember();
        $u_id = $this->userinfo['user_id'];
        $orderId = $request->post('id');
        if (!$orderId)
            return json(['code' => 2, 'msg' => '参数错误', 'data'=>[]]);
            
        $Mconfig = new MConfig();
        
        //$config_val = $Mconfig->readConfig(['huzhuVoucher','huzhuConfirm'],2); var_dump($config_val);exit();
        $config_val = $Mconfig->readConfig(['JY_END','JY_commission'],2);
        //$cv0 = $config_val[0] == ''?$config_val[3]:$config_val[0];
        
        $member_info = $MMember->getinfo(['id'=>$u_id]);
        if($member_info['status'] == 1){
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'请先激活账号']);
        }
        if($member_info['status'] == 3){
            return json(['code'=>SIGN_ERROR,'data' =>[],'msg'=>'账号已被冻结，无法发布']);
        }
        
        
        if (Cache::get('confirmYkbOrder'.$u_id) == 2){
            return json(['code' => 2, 'msg' => '请勿频繁操作', 'data'=>[]]);
        }
        Cache::set('confirmYkbOrder'.$u_id,2,10);
        
        $order = Db::name('order_coin')->where('id =' . $orderId )->find();
        if (!$order || $order['status'] != 2 || $order['sell_uid'] != $u_id)
            return json(['code' => 2, 'msg' => '该订单不存在', 'data'=>[]]);
            
        $user_info = $MMember->getinfo(['id'=>$order['buy_uid']]);

        $data['status'] = 3;
        $data['end_time'] = time();
        try {
            Db::startTrans();
            //订单处理
            Db::name('order_coin')->where('id', $orderId)->update($data);
            Db::name('order_coin_log')->insert([
                'orderNo' => $order['orderNo'],
                'uid' => $u_id,
                'phone' => $member_info['tel'],
                'order_id' => $order['id'],
                'message' => '确认订单,订单编号'.$order['orderNo'],
                'time' => time()
            ]);
            $res4 = Db::name('member_list')->where('id='.$order['sell_uid'])->update([
                'frozen_dot' => Db::raw('frozen_dot -' . ($order['num']+$order['recharge']))
            ]);
            if (!$res4) {
                throw new \Exception("卖家扣除冻结YKB失败");
            }
            
            $condition_buy = 'id=' . $order['buy_uid'];
            $res4 = Db::name('member_list')->where($condition_buy)->update([
                'balance' => Db::raw('balance +' . $order['num']),
                'coin_num' => Db::raw('coin_num +' . $order['num'])
            ]);
            if (!$res4) {
                throw new \Exception("买家YKB增加失败");
            }
            
            //增加YKB记录
            $data6 = [
                'u_id' => $order['buy_uid'],
                'tel' => $order['buy_user'],
                'o_id' => $order['id'],
                'former_money' => $user_info['balance'],
                'change_money' => $order['num'],
                'after_money' => $user_info['balance']+$order['num'],
                'type' => 102,
                'message' => '成功购买'.$order['num'].'YKB,订单编号：'.$order['orderNo'],
                'bo_time' => time(),
                'status' => 234,
            ];
            $res6 = Db::name('member_balance_log')->insert($data6);
            if (!$res6) {
                throw new \Exception("买家YKB记录添加失败");
            }
            //增加YKB记录
            $data7 = [
                'u_id' => $order['sell_uid'],
                'tel' => $order['sell_user'],
                'o_id' => $order['id'],
                'former_money' => 0,
                'change_money' => $order['num']+$order['recharge'],
                'after_money' => 0,
                'type' => 101,
                'message' => '成功出售'.$order['num'].'YKB,手续费'.$order['recharge'].'YKB,订单编号：'.$order['orderNo'],
                'bo_time' => time(),
                'status' => 231,
            ];
            
            $res7 = Db::name('member_balance_log')->insert($data7);
            if (!$res7) {
                throw new \Exception("卖家YKB记录添加失败");
            }
            
            Db::commit();
            Cache::set('confirmYkbOrder'.$u_id,0,1);
            return json(['code' => 1,'msg' => '确认成功']);
        } catch (Exception $exception) {
            Db::rollback();
            Cache::set('confirmYkbOrder'.$u_id,0,1);
            return json(['code' => 2,'msg' => '确认失败'.$exception->getMessage()]);
        }
                
    }
    
    
    
}

