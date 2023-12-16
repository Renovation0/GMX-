<?php
namespace app\admin\controller;

use think\Exception;
use think\Request;
use think\Db;

class Channel extends Check
{   
    // 会员列表
    public function channellist(Request $request)
    {
        $allParams = ['query' => $request->param()];
        $pageSize = intval($request->param('limit', 10));; // 分页大小
        $channelList = Db::name('channel')->paginate($pageSize, false, $allParams);
        $this->assign('list', $channelList);
        // 总记录数
        $total = $channelList->total();
        // 分页个数
        $pages = ceil($total / 10);
        // 渲染模板
        return view('',['pages'=>$pages, 'total'=>$total,'currentPage'=>$channelList->currentPage()]);
    }
    
    // 添加角色
    public function add()
    {
        return view();
    }
    
    
    // 添加角色提交
    public function addPost(Request $request)
    {
        $data = $request->param();
        $channelList = Db::name('channel')->insert($data);
        return json(['code' => 1, 'msg' => '添加成功']);
    }
    
    // 编辑会员页面渲染
    public function edit(Request $request)
    {
        $id = intval($request->param('id', 0));
        $channel = Db::name('channel')->where('id',$id)->find();
        $this->assign('channel', $channel);
        return view();
    }
    
    // 编辑会员修改
    public function channelEditPost(Request $request)
    {   
        $data = $request->param();
        $channelList = Db::name('channel')->update($data);
        return json(['code' => 1, 'msg' => '编辑成功']);
        
    }
}

