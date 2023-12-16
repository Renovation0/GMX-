<?php
namespace app\admin\controller;


use app\admin\model\MCommon;
use app\admin\model\OrderCoinList;
use app\admin\model\OrderList;
use app\admin\model\SystemModule;
use think\Db;
use think\Request;
use app\admin\model\OrderCoinLog;
use app\admin\model\OrderCoin;
use app\admin\model\MGoodsCate;
use app\admin\model\MGoods;

class Goods extends Check
{
    //列表
    public function categoryList(Request $request)
    {
        $MGoodsCate = new MGoodsCate();
        $list = $MGoodsCate->getCategory();
        
        $this->assign('list', $list);
        return view();
    }
    
    //新增
    public function categoryadd(Request $request){
        
        $MGoodsCate = new MGoodsCate();
        $list = $MGoodsCate->getFMenus();
        $this->assign('list', $list);
        if ($request->isAjax()) {
            $pid = intval($request->param('pid', 0));
            $name = $request->param('name', '');
            $sort = $request->param('sort', '');
            $status = $request->param('status', '');
            
            if ($MGoodsCate->insert([
                'pid' => $pid,
                'name' => $name,
                'sort' => $sort,
                'status' => $status,
            ])) {
                return json(['code' => 1, 'msg' => '新增成功']);
            } else {
                return json(['code' => 2, 'msg' => '新增失败']);
            }
        }
        return view();
    } 
    
    //编辑
    public function categoryedit(Request $request){
        
        $MGoodsCate = new MGoodsCate();
        $list = $MGoodsCate->getFMenus();
        $this->assign('list', $list);
        
        $id = intval($request->param('id', 0));
        $info = $MGoodsCate->getInfo(['id'=>$id]);
        
        $this->assign('user', $info);
        
        if ($request->isAjax()) {
            $id= intval($request->param('id', 0));
            $pid = intval($request->param('pid', 0));
            $name = $request->param('name', '');
            $sort = $request->param('sort', '');
            $status = $request->param('status', '');
            
            if ($MGoodsCate->where(['id'=>$id])->update([
                'pid' => $pid,
                'name' => $name,
                'sort' => $sort,
                'status' => $status,
            ])) {
                return json(['code' => 1, 'msg' => '编辑成功']);
            } else {
                return json(['code' => 2, 'msg' => '编辑失败']);
            }
        }
        return view();
    } 
    
    //删除
    public function categorydelete(Request $request){
        $MGoodsCate = new MGoodsCate();
        $id = intval($request->param('id', 0));
        if($id == 0 || empty($id)){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        
        $res = $MGoodsCate->where(['id'=>$id])->delete();
        if($res){
            return json(['code' => 1, 'msg' => '删除成功']);
        } else {
            return json(['code' => 2, 'msg' => '删除失败']);
        }
    }
    

    //商品列表
    public function goodsList(Request $request)
    {
/*         $user_tel = trim($request->param('user_tel', ''));
        $add_time_s = $request->param('add_time_s', ''); // 开启时间开始
        $add_time_e = $request->param('add_time_e', ''); // 开启时间结束
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        $this->assign('user_tel', $user_tel); */
        $allParams = ['query' => $request->param()];
        $pageSize = 15; // 分页大小
        $where = '1 = 1'; // 初始查询条件
/*         if ($add_time_s != '') {
            $where .= " and `time` >= " . strtotime($add_time_s);
        }
        if ($add_time_e != '') {
            $where .= " and `time` <= " . strtotime($add_time_e);
        } */
        $MGoods = new MGoods();
        $Lists = $MGoods->where($where)->order('id desc')->paginate($pageSize, false, $allParams);
        $this->assign('list', $Lists);
        return view();
    }
    
    //商品状态修改
    public function goodsEditStatus(Request $request){
        $id = $request->param('id', '');//请输入互助id
        $status = $request->param('status', '');
        if ($id == '' || $status == ''){
            return json(['code' =>2,'msg' => '参数错误']);
        }
        
        $MGoods = new MGoods();
        $res = $MGoods->where(['id'=>$id])->update(['status'=>$status]);
        if($res){
            return json(['code' => 1, 'msg' => '操作成功']);
        } else {
            return json(['code' => 2, 'msg' => '操作失败']);
        }
    }
    
    
    
    //新增
    public function goodsAdd(Request $request){
        
        $MGoodsCate = new MGoodsCate();
        $list = $MGoodsCate->getList('pid != 0');
        $this->assign('list', $list);
        $MGoods = new MGoods();
        if ($request->isAjax()) {
            
            $res = $request->param();
            //var_dump(json_decode($res,true));
            if(!empty($res['lunbo_logo'])){
                $lunbo_logo = implode(',', $res['lunbo_logo']);
            }
           
            $pid =  $res['type'];//intval($request->param('type', 0));
            $title = $res['title'];// $request->param('title', '');
            $img = $res['thumbnail'];// $request->param('img', '');
            $price = $res['price'];// $request->param('price', '');
            $stock = $res['stock'];// $request->param('stock', '');
            $sort = $res['sort'];// $request->param('sort', '');
            $pay_user = $res['pay_user'];// $request->param('pay_user', '');
            $content = $res['content'];// $request->param('content', '');
            $status = 1;// $request->param('status', 1);
            $specification = $res['specification'];// $request->param('specification', '');
            $label = $res['label'];// $request->param('label', 0);
            $postage_money = $res['postage_money'];
            $img = $this->updatexg($img);
/*             
            $pid = intval($request->param('type', 0));
            $title = $request->param('title', '');
            $img = $request->param('img', '');
            $price = $request->param('price', '');
            $stock = $request->param('stock', '');
            $sort = $request->param('sort', '');
            $pay_user = $request->param('pay_user', '');
            $content = $request->param('content', '');
            $status = $request->param('status', 1);
            $specification = $request->param('specification', '');
            $label = $request->param('label', 0);
            $postage_money = $request->param('postage_money', 0);
            
            $img = $this->updatexg($img); */
            
            if ($MGoods->insert([
                'class_id' => $pid,
                'logo' => $img,
                'lunbo_logo' => $lunbo_logo,
                'goods_name' => $title,
                'price' => $price,
                'stock' => $stock,
                'content' => $content,
                'sort' => $sort,
                'createtime' => time(),
                'status' => $status,
                'pay_user' => $pay_user,
                'specification' => $specification,
                'label' => $label,
                'postage_money' => $postage_money
            ])) {
                return json(['code' => 1, 'msg' => '新增成功']);
            } else {
                return json(['code' => 2, 'msg' => '新增失败']);
            }
        }
        return view();
    }
    
    //编辑
    public function goodsEdit(Request $request){
        $MGoodsCate = new MGoodsCate();
        $list = $MGoodsCate->getList('pid != 0');
        $this->assign('list', $list);
        $MGoods = new MGoods();
        
        $id = intval($request->param('id', 0));
        if($id == 0){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        $info = $MGoods->getInfo(['id'=>$id]);
        if(!empty($info['lunbo_logo'])){
            $info['lunbo_logo'] = explode(',', $info['lunbo_logo']);
        }
        $this->assign('user', $info);
        
        if ($request->isAjax()) {
            
            $res = $request->param();
            //var_dump(json_decode($res,true));
            if(!empty($res['lunbo_logo'])){
                $lunbo_logo = implode(',', $res['lunbo_logo']);
            }
            
            $goods_id = $res['id'];//intval($request->param('goods_id', 0));
            $pid =  $res['type'];//intval($request->param('type', 0));
            $title = $res['title'];// $request->param('title', '');
            $img = $res['thumbnail'];// $request->param('img', '');
            $price = $res['price'];// $request->param('price', '');
            $stock = $res['stock'];// $request->param('stock', '');
            $sort = $res['sort'];// $request->param('sort', '');
            $pay_user = $res['pay_user'];// $request->param('pay_user', '');
            $content = $res['content'];// $request->param('content', '');
            $status = $res['status'];// $request->param('status', 1);
            $specification = $res['specification'];// $request->param('specification', '');
            $label = $res['label'];// $request->param('label', 0);
            $postage_money = $res['postage_money'];
            
            $img = $this->updatexg($img);
            
            if ($MGoods->where(['id'=>$goods_id])->update([
                'class_id' => $pid,
                'logo' => $img,
                'lunbo_logo' => $lunbo_logo,
                'goods_name' => $title,
                'price' => $price,
                'stock' => $stock,
                'content' => $content,
                'sort' => $sort,
                'updatetime' => time(),
                'status' => $status,
                'pay_user' => $pay_user,
                'specification' => $specification,
                'label' => $label,
                'postage_money' => $postage_money
            ])) {
                return json(['code' => 1, 'msg' => '编辑成功']);
            } else {
                return json(['code' => 2, 'msg' => '编辑失败']);
            }
        }
        return view();
    }
    
    //删除
    public function goodsdelete(Request $request){
        $MGoods = new MGoods();
        $id = intval($request->param('id', 0));
        if($id == 0 || empty($id)){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        
        $res = $MGoods->where(['id'=>$id])->delete();
        if($res){
            return json(['code' => 1, 'msg' => '删除成功']);
        } else {
            return json(['code' => 2, 'msg' => '删除失败']);
        }
    }
    
    //订单列表
    public function orderList(Request $request){
        $user_tel = trim($request->param('user_tel', ''));
        $add_time_s = $request->param('add_time_s', ''); // 开启时间开始
        $add_time_e = $request->param('add_time_e', ''); // 开启时间结束
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        $this->assign('user_tel', $user_tel);
        $allParams = ['query' => $request->param()];
        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if ($add_time_s != '') {
            $where .= " and `create_time` >= " . strtotime($add_time_s);
        }
        if ($add_time_e != '') {
            $where .= " and `create_time` <= " . strtotime($add_time_e);
        }

        $Lists = Db::name('goods_order')->where($where)->order('id desc')->paginate($pageSize, false, $allParams);
        $this->assign('list', $Lists);
        return view();
    }
    
    
    //订单编辑--发货
    public function orderEdit(Request $request){

        $id = intval($request->param('id', 0));
        if($id == 0){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        $info = Db::name('goods_order')->where(['id'=>$id])->find();
        $this->assign('user', $info);
        
        if ($request->isAjax()) {
            $order_id = intval($request->param('order_id', 0));
            $track_number = getValue(intval($request->param('track_number', '')));
            //$order_status = $request->param('order_status', '');
            
            if (Db::name('goods_order')->where(['id'=>$order_id])->update([
                'track_number' => $track_number,
                'order_status' => 2,
                'shipping_status' => 1,
                'consign_time' => time()
            ])) {
                return json(['code' => 1, 'msg' => '编辑成功']);
            } else {
                return json(['code' => 2, 'msg' => '编辑失败']);
            }
        }
        return view();
    }
    
    
    //删除
    public function orderdelete(Request $request){
        $id = intval($request->param('id', 0));
        if($id == 0 || empty($id)){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        
        $res = Db::name('goods_order')->where(['id'=>$id])->delete();
        if($res){
            return json(['code' => 1, 'msg' => '删除成功']);
        } else {
            return json(['code' => 2, 'msg' => '删除失败']);
        }
    }
    
    
}