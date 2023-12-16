<?php

namespace app\admin\controller;


use app\admin\model\Lunbo;
use app\admin\model\NoticeType;
use think\Request;
use app\admin\model\MNoticeSchool;

class Notice extends Check
{
    // 分类列表
    public function catLists(Request $request)
    {
        // 获取订单列表
        $title = $request->param('title', '');
        $add_time_s = $request->param('add_time_s', ''); // 开启时间开始
        $add_time_e = $request->param('add_time_e', ''); // 开启时间结束
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        $this->assign('param_title', $title);
        $allParams = ['query' => $request->param()];
        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if ($title != '') {
            $where .= " and `lx_title` like '%".$title."%'";
        }
        if ($add_time_s != '') {
            $where .= " and `time` >= " . strtotime($add_time_s);
        }
        if ($add_time_e != '') {
            $where .= " and `time` <= " . strtotime($add_time_e);
        }
        $noticeTypeModel = new NoticeType();
        $Lists = $noticeTypeModel->where($where)->order('time desc')->paginate($pageSize, false, $allParams);
        $this->assign('list', $Lists);
        return view();
    }

    // 分类添加
    public function catAdd(Request $request)
    {
        if(!$request->isAjax()){
            return view();
        }else{
            $lx_title = $request->param('xl_title', '');
            $img = $request->param('img', '');
            if($lx_title == ''){
                return json(['code' => 2, 'msg' => '请输入分类名称']);
            }
            if($img == ''){
                return json(['code' => 2, 'msg' => '请上传分类图标']);
            }
            $noticeTypeModel = new NoticeType();
            if($noticeTypeModel->insert([
                'lx_title'      =>  $lx_title,
                'img'           =>  $img,
                'time'          =>  time(),
            ])){
                return json(['code' => 1, 'msg' => '添加成功']);
            }else{
                return json(['code' => 2, 'msg' => '添加失败']);
            }
        }
    }

    // 分类编辑
    public function catEdit(Request $request)
    {
        $lx_id = intval($request->param('lx_id', 0));
        $noticeTypeModel = new NoticeType();
        if(!$request->isAjax()){
            if($lx_id == 0){
                $this->error('参数错误');die();
            }
            $cat_info = $noticeTypeModel->where('lx_id', $lx_id)->find();
            if($cat_info){
                $this->assign('catInfo', $cat_info->toArray());
            }else{
                $this->error('无指定分类');die();
            }
            return view();
        }else{
            if($lx_id == 0){
                return json(['code' => 2, 'msg' => '参数错误']);
            }
            $lx_title = $request->param('lx_title', '');
            $img = $request->param('img', '');
            if($lx_title == ''){
                return json(['code' => 2, 'msg' => '请输入分类名称']);
            }
            if($img == ''){
                return json(['code' => 2, 'msg' => '请上传分类图标']);
            }
            if($noticeTypeModel->where('lx_id', $lx_id)->update([
                'lx_title'      =>  $lx_title,
                'img'           =>  $img,
                'up_time'       =>  time(),
            ])){
                return json(['code' => 1, 'msg' => '编辑成功']);
            }else{
                return json(['code' => 2, 'msg' => '编辑失败']);
            }
        }
    }

    // 分类删除
    public function catDelete(Request $request)
    {
        $lx_id = intval($request->param('lx_id', 0));
        if($lx_id == 0){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        $noticeTypeModel = new NoticeType();
        if($noticeTypeModel->where('lx_id', $lx_id)->delete()){
            return json(['code' => 1, 'msg' => '删除成功']);
        }else{
            return json(['code' => 2, 'msg' => '删除失败']);
        }
    }

    // 资讯列表
    public function lists(Request $request)
    {
        $n_title = $request->param('n_title', '');
        $add_time_s = $request->param('add_time_s', ''); // 开启时间开始
        $add_time_e = $request->param('add_time_e', ''); // 开启时间结束
        $type = intval($request->param('type', 0));
        $status = intval($request->param('status', 0));
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        $this->assign('param_n_title', $n_title);
        $this->assign('param_type', $type);
        $allParams = ['query' => $request->param()];
        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if ($n_title != '') {
            $where .= " and n.`n_title` like '%".$n_title."%'";
        }
        if ($add_time_s != '') {
            $where .= " and n.`time` >= " . strtotime($add_time_s);
        }
        if ($add_time_e != '') {
            $where .= " and n.`time` <= " . strtotime($add_time_e);
        }
        if ($type != 0) {
            $where .= " and n.`type` = ".$type;
        }
        if ($status != 0) {
            $where .= " and n.`status` = ".$status;
        }
        $noticeModel = new \app\admin\model\Notice();
        $Lists = $noticeModel->alias('n')->leftJoin('notice_type t', 'n.type = t.lx_id')->where($where)->field('n.*, t.lx_title')->order('time desc')->paginate($pageSize, false, $allParams);
        $this->assign('list', $Lists);
        return view();
    }

    // 添加资讯
    public function add(Request $request)
    {
        if(!$request->isAjax()){
            $noticeTypeModel = new NoticeType();
            $cat_list = $noticeTypeModel->select();
            if($cat_list){
                $this->assign('catList', $cat_list->toArray());
            }else{
                $this->assign('catList', []);
            }
            return view();
        }else{
            $type = intval($request->param('type', 0));
            $img = $request->param('img', '');
            $n_title = $request->param('n_title', '');
            $n_text = $request->param('n_text', '');
            $description = $request->param('description', '');
            if($type == 0){
                return json(['code' => 2, 'msg' => '请选择分类']);
            }
            if($img == ''){
                return json(['code' => 2, 'msg' => '请上传缩略图']);
            }
            if($n_title == ''){
                return json(['code' => 2, 'msg' => '请输入标题']);
            }
            if($n_text == ''){
                return json(['code' => 2, 'msg' => '请输入内容']);
            }
            if($description == ''){
                return json(['code' => 2, 'msg' => '请输入描述']);
            }
            $noticeModel = new \app\admin\model\Notice();
            if($noticeModel->insert([
                'type'      =>  $type,
                'img'       =>  $img,
                'n_title'   =>  $n_title,
                'n_text'    =>  $n_text,
                'description'    =>  $description,
                'time'      =>  time(),
                'status'    =>  2
            ])){
                return json(['code' => 1, 'msg' => '添加成功']);
            }else{
                return json(['code' => 2, 'msg' => '添加失败']);
            }
        }
    }

    // 编辑资讯
    public function edit(Request $request)
    {
        $n_id = intval($request->param('n_id', 0));
        $noticeModel = new \app\admin\model\Notice();
        if(!$request->isAjax()){
            if($n_id == 0){
                $this->error('参数错误');die();
            }
            $noticeTypeModel = new NoticeType();
            $cat_list = $noticeTypeModel->select();
            if($cat_list){
                $this->assign('catList', $cat_list->toArray());
            }else{
                $this->assign('catList', []);
            }
            $noticeInfo = $noticeModel->where('n_id', $n_id)->find();
            if($noticeInfo){
                
                $noticeInfo['n_text'] = str_replace("﹤","<",$noticeInfo['n_text']);
                $noticeInfo['n_text'] = str_replace("﹥",">",$noticeInfo['n_text']);
                $noticeInfo['n_text'] = str_replace("﹠","&",$noticeInfo['n_text']);
                $noticeInfo['n_text'] = str_replace("﹔",";",$noticeInfo['n_text']);
                
                $this->assign('noticeInfo', $noticeInfo->toArray());
            }else{
                $this->error('无指定资讯');die();
            }
            return view();
        }else{
            if($n_id == 0){
                return json(['code' => 2, 'msg' => '参数错误']);
            }
            $type = intval($request->param('type', 0));
            $img = $request->param('img', '');
            $n_title = $request->param('n_title', '');
            $n_text = $request->param('n_text', '');
            $description = $request->param('description', '');
            if($type == 0){
                return json(['code' => 2, 'msg' => '请选择分类']);
            }
            if($img == ''){
                return json(['code' => 2, 'msg' => '请上传缩略图']);
            }
            if($n_title == ''){
                return json(['code' => 2, 'msg' => '请输入标题']);
            }
            if($description == ''){
                return json(['code' => 2, 'msg' => '请输入描述']);
            }
            if($n_text == ''){
                return json(['code' => 2, 'msg' => '请输入内容']);
            }

            if($noticeModel->where('n_id', $n_id)->update([
                'type'      =>  $type,
                'img'       =>  $img,
                'n_title'   =>  $n_title,
                'description'    =>  $description,
                'n_text'    =>  $n_text,
                'time'      =>  time()
            ])){
                return json(['code' => 1, 'msg' => '编辑成功']);
            }else{
                return json(['code' => 2, 'msg' => '编辑失败']);
            }
        }
    }

    // 编辑状态
    public function status(Request $request)
    {
        $n_id = intval($request->param('n_id', 0));
        $status = intval($request->param('status', 0));
        if($n_id == 0 || ($status != 1 && $status != 2) ){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        $noticeModel = new \app\admin\model\Notice();
        if($noticeModel->where('n_id', $n_id)->setField('status', $status)){
            if($status == 1){
                return json(['code' => 1, 'msg' => '上架成功']);
            }else{
                return json(['code' => 1, 'msg' => '下架成功']);
            }
        }else{
            if($status == 2){
                return json(['code' => 2, 'msg' => '上架失败']);
            }else{
                return json(['code' => 2, 'msg' => '下架失败']);
            }
        }
    }

    // 删除
    public function delete(Request $request)
    {
        $n_id = intval($request->param('n_id', 0));
        if($n_id == 0){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        $noticeModel = new \app\admin\model\Notice();
        if($noticeModel->where('n_id', $n_id)->delete()){
            return json(['code' => 1, 'msg' => '删除成功']);
        }else{
            return json(['code' => 2, 'msg' => '删除失败']);
        }
    }

    
    
    /**
     * 云商学院
     */
    //列表
    public function schoolList(Request $request){

        $add_time_s = $request->param('add_time_s', ''); // 开启时间开始
        $add_time_e = $request->param('add_time_e', ''); // 开启时间结束
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        $allParams = ['query' => $request->param()];
        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if ($add_time_s != '') {
            $where .= " and `addtime` >= " . strtotime($add_time_s);
        }
        if ($add_time_e != '') {
            $where .= " and `addtime` <= " . strtotime($add_time_e);
        }
        $MNoticeSchool = new MNoticeSchool();
        $Lists = $MNoticeSchool->where($where)->order('addtime desc')->paginate($pageSize, false, $allParams);
        $this->assign('list', $Lists);
        return view();
    }
    
    //添加
    public function schoolAdd(Request $request){
        if($request->isAjax()){
            $name = $request->param('name', '');
            $subtitle = $request->param('subtitle', '');
            $img = $request->param('img', '');
            $video = $request->param('video', '');
            $content = $request->param('content', '');
            if($name == ''){
                return json(['code' => 2, 'msg' => '请输入标题名称']);
            }
            if($img == ''){
                return json(['code' => 2, 'msg' => '请上传缩略图']);
            }
            if($content == ''){
                return json(['code' => 2, 'msg' => '请输入内容']);
            }
            $MNoticeSchool = new MNoticeSchool();
            $data = [
                'name'          =>  $name,
                'subtitle'      =>  $subtitle,
                'image'           =>  $img,
                'video'         =>  $video,
                'content'       =>  $content,
                'addtime'          =>  time()
            ];
            $res = $MNoticeSchool->insert($data);
            if($res){
                return json(['code' => 1, 'msg' => '添加成功']);
            }else{
                return json(['code' => 2, 'msg' => '添加失败']);
            }
        }
        return view();
    }
    
    //编辑
    public function schoolEdit(Request $request){
        $MNoticeSchool = new MNoticeSchool();
        if($request->isAjax()){
            $ids = $request->param('id', '');
            $name = $request->param('name', '');
            $subtitle = $request->param('subtitle', '');
            $img = $request->param('img', '');
            $video = $request->param('video', '');
            $content = $request->param('content', '');
            if($ids == ''){
                return json(['code' => 2, 'msg' => '参数错误']);
            }
            if($name == ''){
                return json(['code' => 2, 'msg' => '请输入标题名称']);
            }
            if($content == ''){
                return json(['code' => 2, 'msg' => '请输入内容']);
            }

            $data = [
                'name'          =>  $name,
                'subtitle'      =>  $subtitle,
                'image'           =>  $img,
                'video'         =>  $video,
                'content'       =>  $content
            ];
            $res = $MNoticeSchool->where(['id'=>$ids])->update($data);
            if($res){
                return json(['code' => 1, 'msg' => '修改成功']);
            }else{
                return json(['code' => 2, 'msg' => '修改失败']);
            }
        }
        $id = intval($request->param('id', 0));
        if($id == 0) return json(['code' => 2, 'msg' => '参数错误']);
        $info = $MNoticeSchool->getInfo(['id'=>$id]);
        $this->assign('info',$info);
        return view();
    }
    
    //删除云商学院
    public function schoolDel(Request $request){
        $n_id = intval($request->param('n_id', 0));
        if($n_id == 0){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        $MNoticeSchool = new MNoticeSchool();
        if($MNoticeSchool->where('id', $n_id)->delete()){
            return json(['code' => 1, 'msg' => '删除成功']);
        }else{
            return json(['code' => 2, 'msg' => '删除失败']);
        }
    }
    
}