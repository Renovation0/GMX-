<?php
namespace app\admin\controller;

use think\Exception;
use think\Request;
use think\Db;

class Bing extends Check
{   
    // 会员列表
    public function bingding(Request $request)
    {
        $tel = trim($request->param('tel', '')); // 电话号码
        $this->assign('tel', $tel);
        $sort = $request->param('sort', '');
        $this->assign('sort', $sort);
        $allParams = ['query' => $request->param()];
        $pageSize = intval($request->param('limit', 10));; // 分页大小
        $this->assign('pageSize', $pageSize);
        // $channelList = Db::name('paymant_binding')->paginate($pageSize, false, $allParams);
       
        
         if($sort != ''){
            $orders = urldecode($sort);
        }else{
            $orders = 'a.id desc';
        }
        $orders = 'a.id desc';
        $condition = [];
        if(!empty($tel)){
            // $condition[] = ['b.tel','like','%'.$tel.'%'];
            $condition['b.tel'] = $tel;
        }
        
        $field = 'a.*,b.tel';
        $memberLists = Db::name('paymant_binding')->alias('a')
        ->join('zm_member_list b','a.u_id=b.id', 'LEFT')
         ->where($condition)
        ->order($orders)
        ->field($field)
        ->paginate($pageSize, false, $allParams);
       
        
        $this->assign('list', $memberLists);
        // 总记录数
        $total = $memberLists->total();
        // 分页个数
        $pages = ceil($total / 10);
        // 渲染模板
        return view('',['pages'=>$pages, 'total'=>$total,'currentPage'=>$memberLists->currentPage()]);
    }
    
    // 添加角色
    public function add()
    {
        return view();
    }
    
    
    // 添加角色提交
    // public function addPost(Request $request)
    // {
    //     $data = $request->param();
    //     $channelList = Db::name('channel')->insert($data);
    //     return json(['code' => 1, 'msg' => '添加成功']);
    // }
    
    // 编辑会员页面渲染
    public function edit(Request $request)
    {
        $id = intval($request->param('id', 0));
        $channel = Db::name('paymant_binding')->where('id',$id)->find();
        $this->assign('bind', $channel);
        $banklist = Db::name('bankinfo')->order('id asc')->select();
        $this->assign('banklist', $banklist);
        return view();
    }
    
    // 编辑会员修改
    public function channelEditPost(Request $request)
    {   
        $data = $request->param();
        $bank = Db::name('bankinfo')->where('id',$data['bankid'])->find();
        unset($data['bankid']);
        $data['account_num'] = $bank['bankname'];
        $data['bank_code'] = $bank['code'];
        $channelList = Db::name('paymant_binding')->update($data);
        return json(['code' => 1, 'msg' => '编辑成功']);
        
    }
    
    //删除
    public function bdelete(Request $request){
        $id = $request->param('id', 0);
        $info = Db::name('paymant_binding')->where('id',$id)->delete();
        if($info){
            return json(['code' => 1, 'msg' => '删除成功']);
        } else {
            return json(['code' => 2, 'msg' => '删除失败']);
        }
    }
}

