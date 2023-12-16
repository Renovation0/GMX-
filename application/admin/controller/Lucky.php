<?php
namespace app\admin\controller;

use think\Db;
use think\Request;

class Lucky extends Check
{
    
    public function luckyList(Request $request){
        $allParams = ['query' => $request->param()];
        $pageSize = 15; // 分页大小
        $where = '1 = 1'; // 初始查询条件

        $Lists = Db::name('lucky')->where($where)->order('id desc')->paginate($pageSize, false, $allParams);
        $this->assign('list', $Lists);
        return view();
    }
    
    //商品状态修改
    public function luckyEditStatus(Request $request){
        $id = $request->param('id', '');//请输入互助id
        $status = $request->param('status', '');
        if ($id == '' || $status == ''){
            return json(['code' =>2,'msg' => '参数错误']);
        }
        
        $res = Db::name('lucky')->where(['id'=>$id])->update(['status'=>$status]);
        if($res){
            return json(['code' => 1, 'msg' => '操作成功']);
        } else {
            return json(['code' => 2, 'msg' => '操作失败']);
        }
    }
    
    
    
    //新增
    public function luckyAdd(Request $request){

        if ($request->isAjax()) {
            $res = $request->param();
/*             if(!empty($res['lunbo_logo'])){
                $lunbo_logo = implode(',', $res['lunbo_logo']);
            } */

            $type =  $res['type'];//intval($request->param('type', 0));奖品名称 1主币 2辅币 3推荐收益 4团队收益 5其他
            $num = $res['num'];// $request->param('title', '');
            $weight = $res['weight'];// $request->param('img', '');
            $msg = $res['msg'];// $request->param('content', '');
            $status = $res['status'];// $request->param('status', 1);
            //$img = $this->updatexg($img);
            
            if (Db::name('lucky')->insert([
                'type' => $type,
                //'logo' => $img,
                'num' => $num,
                'weight' => $weight,
                'msg' => $msg,
                'status' => $status
            ])) {
                return json(['code' => 1, 'msg' => '新增成功']);
            } else {
                return json(['code' => 2, 'msg' => '新增失败']);
            }
        }
        return view();
    }
    
    //编辑
    public function luckyEdit(Request $request){

        $id = intval($request->param('id', 0));
        if($id == 0){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        $info = Db::name('lucky')->where(['id'=>$id])->find();
/*         if(!empty($info['lunbo_logo'])){
            $info['lunbo_logo'] = explode(',', $info['lunbo_logo']);
        } */
        $this->assign('user', $info);
        
        if ($request->isAjax()) {
            $res = $request->param();
            /* if(!empty($res['lunbo_logo'])){
                $lunbo_logo = implode(',', $res['lunbo_logo']);
            } */
            
            $lucky_id = $res['id'];//intval($request->param('goods_id', 0));
            $type =  $res['type'];//intval($request->param('type', 0));奖品名称 1主币 2辅币 3推荐收益 4团队收益 5其他
            $num = $res['num'];// $request->param('title', '');
            $weight = $res['weight'];// $request->param('img', '');
            $msg = $res['msg'];// $request->param('content', '');
            $status = $res['status'];// $request->param('status', 1);
            
            //$img = $this->updatexg($img);
            
            if (Db::name('lucky')->where(['id'=>$lucky_id])->update([
                'type' => $type,
                //'logo' => $img,
                'num' => $num,
                'weight' => $weight,
                'msg' => $msg,
                'status' => $status
            ])) {
                return json(['code' => 1, 'msg' => '编辑成功']);
            } else {
                return json(['code' => 2, 'msg' => '编辑失败']);
            }
        }
        return view();
    }
    
    //删除
    public function luckydelete(Request $request){
        $id = intval($request->param('id', 0));
        if($id == 0 || empty($id)){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        
        $res = Db::name('lucky')->where(['id'=>$id])->delete();
        if($res){
            return json(['code' => 1, 'msg' => '删除成功']);
        } else {
            return json(['code' => 2, 'msg' => '删除失败']);
        }
    }
    
    //订单列表
    public function luckyMemberLog(Request $request){
        $type = trim($request->param('type', ''));
        $user_tel = trim($request->param('tel', ''));
        $add_time_s = $request->param('add_time_s', ''); // 开启时间开始
        $add_time_e = $request->param('add_time_e', ''); // 开启时间结束
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        $this->assign('user_tel', $user_tel);
        $this->assign('type', $type);
        $allParams = ['query' => $request->param()];
        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if($type != 0){
            $where .= " and `type_id` = " . $type;
        }
        if($user_tel != ''){
            $where .= " and `tel` = " . $user_tel;
        }
        if ($add_time_s != '') {
            $where .= " and `time` >= " . strtotime($add_time_s);
        }
        if ($add_time_e != '') {
            $where .= " and `time` <= " . strtotime($add_time_e);
        }
        
        $Lists = Db::name('lucky_log')->where($where)->order('id desc')->paginate($pageSize, false, $allParams);
        $this->assign('list', $Lists);
        return view();
    }
    
    
    
}

