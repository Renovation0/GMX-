<?php
namespace app\api\controller;

use think\Request;
use think\Db;

class Notice extends Common
{   
    public function index(Request $request){
        
        $info = Db::name('notice')->where('status = 1')->order('n_id desc')->field('n_id,n_title,description,time')->find();
        
        return json(['code' => 1, 'msg' => 'success', 'data'=>$info]);
    }
    
    //文章列表
    public function notice(Request $request)
    {
        $page = intval($request->post('page', 1));// 列表页码
        if ($page <= 0) {        
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        $count = Db::name('notice')->where('status', 1)->count();
        $page_size = 10;
        $pages = ceil($count / $page_size);
        $offset = ($page - 1) * $page_size;
        $list = Db::name('notice')
        ->field('n_id,n_title as title,img,description,time as create_time')
        ->where('status', 1)->where('type',12)
        ->order('sort desc')
        ->limit($offset, $page_size)
        ->select();
        foreach ($list as $k => $v) {
            $list[$k]['logo'] = 'http://' . $_SERVER['HTTP_HOST'] . $v['img'];
            $list[$k]['create_time'] = date('Y年m月d日', $v['create_time']);
        }
        $data = [
            'count' => $count,
            'pages' => $pages,
            'list' => $list,
        ];
        return json(['code' => 1, 'msg' => 'success', 'data'=>$data]);
    }
    
    //文章详情 
    public function noticeInfo(Request $request)
    {
        $id = intval($request->post('id','2'));// 文章id
        if (!$id || $id <= 0) {
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        $info = Db::name('notice')->where('n_id', $id)->field('n_title as title,n_text as content,time as create_time')->find();
        // $info['content'] = str_replace("/upload",'http://' . $_SERVER['HTTP_HOST'] . "/upload",$info['content']);
        $info['create_time'] = date('Y-m-d', $info['create_time']);
        
        return json(['code' => 1, 'msg' => 'success', 'data'=> $info]);
    }
    
    // 发布工单 
    public function workOrderPost(Request $request)
    {   
        $content = trim($request->post('content'));// 工单内容
        $img = $request->post('img/a');
        if (!$content && !is_array($img))
            return json(['code' => 2, 'msg' => '请填写内容在提交']);
        if(!empty($img)){
            $img = implode(',', $img);
        }
        $img = $this->updatexg($img);
        Db::name('work_order')->insert([
            'u_id' => $this->userinfo['user_id'],
            'tel' => $this->userinfo['tel'],
            'msg' => $content,
            'img' => $img,
            'add_time' => time()
        ]);

        return json(['code' => 1, 'msg' => '提交成功']);
    }
    
    
    //在线客服
    public function onlineCustomer(Request $request)
    {
        $info = Db::name('notice')->where(['n_id'=>5,'status'=>1])->field('n_title as title,n_text as content,time as create_time,img,description')->find();
        
        $info['img'] = 'http://' . $_SERVER['HTTP_HOST'] . $info['img'];
        
        return json(['code' => 1, 'msg' => 'success', 'data'=> $info]);
    }
    
    
}

