<?php
namespace app\api\controller;

use think\Request;
use think\Exception;
use think\Db;
use app\api\model\MConfig;
use app\api\model\MMember;

class Shop extends Common
{   
    private function makeRand($num = 9)
    {
        mt_srand((double)microtime() * 1000000);//用 seed 来给随机数发生器播种。
        $strand = str_pad(mt_rand(1, 99999), $num, "0", STR_PAD_LEFT);
        if (Db::name('goods_order')->where('orderNo', 'TM' . date('Ymd') . $strand)->count() == 0) {
            return 'TM' . date('Ymd') . $strand;
        }
        $this->makeRand();
    }
    
    //商城首页
    public function goodsList(Request $request){
        $c_id = intval($request->param('c_id', 0));
        
       //$cate_list = Db::name('goods_class')->where('pid > 0')->select();
       if($c_id == 0){
           $list = Db::name('goods')->where('status = 1')->select();
       }else{
           $list = Db::name('goods')->where('status = 1 and class_id ='.$c_id)->select();
       }
       //$list = Db::name('goods')->where('status = 1')->select();
       
       $HTTP_HOST = $_SERVER['HTTP_HOST'];
       $goods = [];
       
       foreach ($list as $k => $v){
           $goods[$k]['id'] = $v['id'];
           $goods[$k]['logo'] = 'http://' . $HTTP_HOST . $v['logo']; 
           $goods[$k]['goods_name'] = $v['goods_name'];
           $goods[$k]['price'] = $v['price'];
           $goods[$k]['pay_user'] = $v['pay_user'];
           $goods[$k]['label'] = $v['label'];
       }
       
       return json(['code' => 1, 'msg' => 'success', 'data'=>$goods]);
    }
        
    //商城首页分类
    public function cateList(Request $request){
        $cate_list = Db::name('goods_class')->where('pid > 0')->select();
        return json(['code' => 1, 'msg' => 'success', 'data'=>$cate_list]);
    }
    
    //商品详情
    public function goodsInfo(Request $request){
        $MConfig = new MConfig();
        $MConfig_val = $MConfig->readConfig('auxiliaryCurrency');
        
        $id = intval($request->param('id', 0));
        
        if(empty($id)){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        
        $info = Db::name('goods')->where(['id'=>$id])->find();
        
        $arr = explode(',', $info['lunbo_logo']);
        
        $HTTP_HOST = $_SERVER['HTTP_HOST'];
        $info['logo'] = 'http://' . $HTTP_HOST .$info['logo'];
        
        foreach($arr as $k => $v){
            $info['lunbo'][] = 'http://' . $HTTP_HOST .$v;
        }
        unset($info['lunbo_logo']);
        
        $info['coin'] = $MConfig_val;
        
        return json(['code' => 1, 'msg' => 'success', 'data'=>$info]);
    }
    
    
    //提交订单
    public function submitOrder(Request $request){
        $MConfig = new MConfig();
        $MConfig_val = $MConfig->readConfig('auxiliaryCurrency');
        $u_id = $this->userinfo['user_id'];
        $id = intval($request->param('id', 0));
        $num = intval($request->param('num', 2));
        
        if(empty($id)){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        
        $member_adress = Db::name('goods_member_address')->where(['uid'=>$u_id,'status'=>1])->field('id as address_id,tel,name,address_all,address_detail')->find();
        if(empty($member_adress)){
            $member_adress = [];
        }
        
        $info = Db::name('goods')->where(['id'=>$id])->field('id,logo,goods_name,price,postage_money')->find();
        $HTTP_HOST = $_SERVER['HTTP_HOST'];
        $info['logo'] = 'http://' . $HTTP_HOST . $info['logo'];
        $info['coin'] = $MConfig_val;
        $info['num']  = $num;
        $info['total_price']  = $num*$info['price']+$info['postage_money'];
        
        return json(['code' => 1, 'msg' => 'success', 'data'=>$info, 'adress'=>$member_adress]);
    }
    
    
    
    //确认订单
    public function confirmOrder(Request $request){
        $address_id = intval($request->param('address_id', 0));
        $goods_id = intval($request->param('goods_id', 0));
        $num = intval($request->param('num', 0));
        $u_id = $this->userinfo['user_id'];
        
        if(!$address_id || !$goods_id || !$num){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        
        $MConfig = new MConfig();
        $MConfig_val = $MConfig->readConfig('auxiliaryCurrency');
        
        $MMember = new MMember();
        $member_info = $MMember->getInfo(['id'=>$u_id],'coin,tel');
        
        $member_adress = Db::name('goods_member_address')->where(['uid'=>$u_id,'status'=>1])->field('tel,name,address_all,address_detail')->find();
        $info = Db::name('goods')->where(['id'=>$goods_id])->field('id,logo,goods_name,price,specification,postage_money')->find();

        $total_price = $num*$info['price']+$info['postage_money'];
        if($total_price > $member_info['coin']){
            return json(['code' => 2, 'msg' => $MConfig_val.'余额不足']);
        }
        
        try {
            Db::startTrans();
            
/*             Db::name('member_list')->where(['id'=>$u_id])->update([
                'coin'=>Db::raw('coin -'. $total_price)
            ]);
            
            Db::name('member_balance_log')->insert([
                'u_id' => $u_id,
                'tel' => $member_info['tel'],
                'former_money' => $member_info['coin'],
                'change_money' => $total_price,
                'after_money' => $member_info['coin'] - $total_price,
                'message' => '购买'.$auxiliaryCurrency,
                'type' => 2,
                'bo_time' => time(),
                'status' => 210
            ]); */
            
            $id = Db::name('goods_order')->insertGetId([
                'orderNo'           =>  $this->makeRand(),
                'goods_id'          =>  $info['id'],
                'goods_name'        =>  $info['goods_name'],
                'goods_logo'        =>  $info['logo'],
                'buyer_id'          =>  $u_id,
                'buyer_tel'         =>  $member_info['tel'],
                'receiver_mobile'   =>  $member_adress['tel'],
                'receiver_province' =>  '',
                'receiver_city'     =>  '',
                'receiver_district' =>  '',
                'receiver_address'  =>  $member_adress['address_detail'].' '.$member_adress['address_all'],
                'receiver_name'     =>  $member_adress['name'],
                'goods_price'       =>  $info['price'],
                'goods_num'         =>  $num,
                'order_money'       =>  $total_price,
                'pay_money'         =>  $total_price,
                'order_status'      =>  0,
                'create_time'       =>  time(),
/*                 'dot_goods_price'   =>  $total_price,
 'dot_order_money'   =>  $total_price, */
                'dot_shipping_money'=> $info['postage_money'],
                'specification'     =>  $info['specification']
            ]);
            
            Db::commit();
            return json(['code' => 1,'msg' => '提交成功','data' => $id]);
        } catch (Exception $exception) {
            Db::rollback();
            return json(['code' => 2,'msg' => '提交失败'.$exception->getMessage()]);
        }

    }
    
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////                      地址列表                                                             /////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    //地址列表
    public function addressList(Request $request){
        $u_id = $this->userinfo['user_id'];
        
        $list = Db::name('goods_member_address')->field('id,tel,name,address_all,address_detail,status')->where(['uid'=>$u_id])->select();

        return json(['code' => 1, 'msg' => 'success', 'data'=>$list]);
    }
    
    
    //新增地址
    public function addressAdd(Request $request){
        
        $u_id = $this->userinfo['user_id'];
        $id = getValue($request->param('id', 0));
        $tel = getValue($request->param('tel', ''));
        $name = getValue($request->param('name', ''));
        $status = intval($request->param('status', 0));
        $address_detail = $request->param('address_detail', '');
        $address_all = $request->param('address_all', '');
        
        if(!$tel || !$name || !$address_detail || !$address_all){
            return json(['code' => 2, 'msg' => '请完整填写参数']);
        }
        if($status == 1){
            Db::name('goods_member_address')->where(['uid'=>$u_id])->update(['status'=>0]);
        }
        
        if($id == 0){
            $data = [
                'uid'=>$u_id,
                'tel'=>$tel,
                'name'=>$name,
                'status'=>$status,
                'address_detail'=>$address_detail,
                'address_all'=>$address_all,
                'create_time'=>time()
            ];
            $res = Db::name('goods_member_address')->insert($data);
        }else{
            $data = [
                'uid'=>$u_id,
                'tel'=>$tel,
                'name'=>$name,
                'status'=>$status,
                'address_detail'=>$address_detail,
                'address_all'=>$address_all,
                'update_time'=>time()
            ];
            $res = Db::name('goods_member_address')->where(['id'=>$id])->update($data);
        }
        
        if($res){
            return json(['code' => 1,'msg' => '提交成功']);
        }else{
            return json(['code' => 2,'msg' => '提交失败']);
        }
    }
    
    //编辑地址
    public function addressEdit(Request $request){
        
        $id = intval($request->param('id', 0));
        if(empty($id)){
            return json(['code' => 2,'msg' => '参数错误']);
        }
        $info = Db::name('goods_member_address')->where(['id'=>$id])->field('id,tel,name,address_all,address_detail,status')->find();
        
        return json(['code' => 1, 'msg' => 'success', 'data'=>$info]);
    }
    
    
    //编辑地址提交
    public function addressEditPost(Request $request){
        
        $u_id = $this->userinfo['user_id'];
        $id = getValue($request->param('id', 0));
        $tel = getValue($request->param('tel', ''));
        $name = getValue($request->param('name', ''));
        $status = intval($request->param('status', 0));
        $address_detail = getValue($request->param('address_detail', ''));
        $address_all = getValue($request->param('address_all', ''));
        
        if(empty($id)){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        
        if(!$tel || !$name || !$status || !$address_detail || !$address_all){
            return json(['code' => 2, 'msg' => '请完整填写参数']);
        }
        
        $data = [
            'uid'=>$u_id,
            'tel'=>$tel,
            'name'=>$name,
            'status'=>$status,
            'address_detail'=>$address_detail,
            'address_all'=>$address_all,
            'update_time'=>time(),
        ];
        
        $res = Db::name('goods_member_address')->where(['id'=>$id])->update($data);
        if($res){
            return json(['code' => 1,'msg' => '修改成功']);
        }else{
            return json(['code' => 2,'msg' => '修改失败']);
        }
    }
    
    
    //删除地址
    public function addressDelete(Request $request){
        $id = getValue($request->param('id', 0));
        
        if(empty($id)){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        
        $result = Db::name('goods_member_address')->where(['id'=>$id])->find();
        if(empty($result)){
            return json(['code' => 2,'msg' => '地址信息不存在']);
        }
        
        $res = Db::name('goods_member_address')->where(['id'=>$id])->delete();
        if($res){
            return json(['code' => 1,'msg' => '删除成功']);
        }else{
            return json(['code' => 2,'msg' => '删除失败']);
        }
    }
    
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////                      订单列表                                                             /////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    //订单列表
    public function orderList(Request $request){
        $MConfig = new MConfig();
        $MConfig_val = $MConfig->readConfig('auxiliaryCurrency');
        $u_id = $this->userinfo['user_id'];
        
        //$list = Db::name('goods_order')->field('goods_name,goods_logo,goods_price,goods_num,order_money')->where(['buyer_id'=>$u_id])->select();
        //var_dump($list);
        
        $type = $request->param('type', 9);
        $page = $request->param('page', 1);
        
        if($type == 9){
            $where = ' order_status in (0,1,2,3,4)';
        }else{
            $where = ' order_status = '.$type;
        }
        
        $count = Db::name('goods_order')->where('buyer_id', $u_id)->where($where)->count();
        
        $page_size = 8;
        $pages = ceil($count / $page_size);
        $offset = ($page - 1) * $page_size;
        $list = Db::name('goods_order')
        ->field('id,goods_name,goods_logo,goods_price,goods_num,order_money,order_status,track_number')
        ->where(['buyer_id'=>$u_id])
        ->where($where)
        //->where($status)//'status',
        ->order('id desc')
        ->limit($offset, $page_size)
        ->select();
        
        $HTTP_HOST = $_SERVER['HTTP_HOST'];
        
        foreach ($list as $k => $v) {
            $list[$k]['goods_logo'] = 'http://' . $HTTP_HOST .$v['goods_logo'];
            if($v['order_status'] == 0){
                $list[$k]['type'] = '待付款';
            }elseif($v['order_status'] == 1){
                $list[$k]['type'] = '待发货';
            }elseif($v['order_status'] == 2){
                $list[$k]['type'] = '已发货';
            }elseif($v['order_status'] == 3){
                $list[$k]['type'] = '已完成';
            }elseif($v['order_status'] == 4){
                $list[$k]['type'] = '已取消';
            }
        }
        $data = [
            'count' => $count,
            'pages' => $pages,
            'list' => $list,
            'coin' => $MConfig_val
        ];
        
        return json(['code' => 1, 'msg' => 'success', 'data'=>$data]);
    }
    
    
    //订单详情
    public function orderInfo(Request $request){
        $MConfig = new MConfig();
        $MConfig_val = $MConfig->readConfig('auxiliaryCurrency');
        
        $u_id = $this->userinfo['user_id'];
        $id = $request->param('id', 0);
        if(empty($id)){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        
        $info = Db::name('goods_order')->where(['id'=>$id,'buyer_id'=>$u_id])->field('id,goods_name,goods_logo,goods_price,goods_num,order_money,order_status,receiver_mobile,receiver_name,receiver_province,receiver_city,receiver_district,receiver_address,track_number')->find();        
        if(empty($info)){
            return json(['code' => 2, 'msg' => '订单数据错误']);
        }
        
        $HTTP_HOST = $_SERVER['HTTP_HOST'];
        
        $info['goods_logo'] = 'http://' . $HTTP_HOST .$info['goods_logo'];
        if($info['order_status'] == 0){
            $info['type'] = '待付款';
        }elseif($info['order_status'] == 1){
            $info['type'] = '待发货';
        }elseif($info['order_status'] == 2){
            $info['type'] = '待收货';
        }elseif($info['order_status'] == 3){
            $info['type'] = '已完成';
        }elseif($info['order_status'] == 4){
            $info['type'] = '已取消';
        }
        $info['coin'] = $MConfig_val;
        
        return json(['code' => 1, 'msg' => 'success', 'data'=>$info]);
    }
    
    
    //取消订单
    public function orderCancel(Request $request){
        $MConfig = new MConfig();
        $MConfig_val = $MConfig->readConfig('auxiliaryCurrency');
        
        $u_id = $this->userinfo['user_id'];
        $id = $request->param('id', 0);
        if(empty($id)){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        $MMember = new MMember();
        $member_info = $MMember->getInfo(['id'=>$u_id]);
        
        $order_info = Db::name('goods_order')->where(['id'=>$id,'buyer_id'=>$u_id])->field('id,order_money,order_status')->find();
        if(empty($order_info)){
            return json(['code' => 2, 'msg' => '订单数据错误']);
        }
        
        if($order_info['order_status'] == 1){
            return json(['code' => 2, 'msg' => '订单已支付，无法取消']);
        }
        
        try {
            Db::startTrans();
            
            Db::name('goods_order')->where(['id'=>$order_info['id']])->update([
                'order_status'=> 4,
                'cancel_time' => time()
            ]);
            
            Db::name('member_list')->where(['id'=>$u_id])->update([
                'coin' => Db::raw('coin +'.$order_info['order_money'])
            ]);                
            
            Db::name('member_balance_log')->insert([
                'u_id' => $u_id,
                'tel' => $member_info['tel'],
                'former_money' => $member_info['coin'],
                'change_money' => $order_info['order_money'],
                'after_money' => $member_info['coin'] + $order_info['order_money'],
                'message' => '取消订单退回'.$MConfig_val,
                'type' => 11,
                'bo_time' => time(),
                'status' => 115
            ]);
            
            Db::commit();
            return json(['code' => 1,'msg' => '订单取消成功']);
        } catch (Exception $exception) {
            Db::rollback();
            return json(['code' => 2,'msg' => '订单取消失败'.$exception->getMessage()]);
        }
        
    }
    
    //立即付款
    public function orderPayment(Request $request){
        $MConfig = new MConfig();
        $MConfig_val = $MConfig->readConfig('auxiliaryCurrency');
        
        $u_id = $this->userinfo['user_id'];
        $id = $request->param('id', 0);
        $password = getValue($request->param('password', 0));
        if(empty($id)){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        
        $MMember = new MMember();
        $member_info = $MMember->getInfo(['id'=>$u_id],'coin,pay_pass,tel');
        
        $order_info = Db::name('goods_order')->where(['id'=>$id,'buyer_id'=>$u_id])->field('id,order_money')->find();
        if(empty($order_info)){
            return json(['code' => 2, 'msg' => '订单数据错误']);
        }
        
        if($member_info['pay_pass'] != md5($password.'pay_passwd')){
            return json(['code' => 2, 'msg' => '支付密码错误']);
        }
        
        try {
            Db::startTrans();
            
            Db::name('goods_order')->where(['id'=>$order_info['id']])->update([
                'order_status'=> 1,
                'pay_status'=> 1,
                'dot_goods_price'=> $order_info['order_money'],
                'dot_order_money'=> $order_info['order_money'],
                'pay_time' => time()
            ]);
            
            Db::name('member_list')->where(['id'=>$u_id])->update([
                'coin' => Db::raw('coin -'.$order_info['order_money'])
            ]);
            
            Db::name('member_balance_log')->insert([
                'u_id' => $u_id,
                'tel' => $member_info['tel'],
                'former_money' => $member_info['coin'],
                'change_money' => -$order_info['order_money'],
                'after_money' => $member_info['coin'] - $order_info['order_money'],
                'message' => '订单支付'.$MConfig_val,
                'type' => 11,
                'bo_time' => time(),
                'status' => 116
            ]);
            
            Db::commit();
            return json(['code' => 1,'msg' => '订单支付成功']);
        } catch (Exception $exception) {
            Db::rollback();
            return json(['code' => 2,'msg' => '订单支付失败'.$exception->getMessage()]);
        }
        
    }
    
    //确认收货
    public function orderComplete(Request $request){
        $u_id = $this->userinfo['user_id'];
        $id = $request->param('id', 0);
        if(empty($id)){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        
        $MMember = new MMember();
        $member_info = $MMember->getInfo(['id'=>$u_id]);
        
        $order_info = Db::name('goods_order')->where(['id'=>$id,'buyer_id'=>$u_id])->field('id,order_money')->find();
        if(empty($order_info)){
            return json(['code' => 2, 'msg' => '订单数据错误']);
        }
        
        $res = Db::name('goods_order')->where(['id'=>$order_info['id']])->update([
            'order_status'=> 3,
            'finish_time' => time()
        ]);
        
        if($res){
            return json(['code' => 1,'msg' => '确认收货成功']);
        }else{
            return json(['code' => 2,'msg' => '确认收货失败']);
        }
        
    }
    
    
    
    
    
    
}

