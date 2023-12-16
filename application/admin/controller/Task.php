<?php
namespace app\admin\controller;

use think\Db;
use think\Request;
use app\admin\model\MTask;
use app\admin\model\MMutualAidExamine;

class Task extends Check
{   
    
    //任务列表
    public function taskList(Request $request){
        $MTask = new MTask();
        $list = $MTask->getList();
        //$list = $MutualAid->getlists($where, 'sort desc', $pageSize, $allParams);
        $this->assign('list',$list);

        return view();
    }
    
    //新增互助页面
    public function taskAdd(Request $request){
        $MTask = new MTask();
        if ($request->isAjax()) {
            
            $task_name = $request->param('task_name', '');//请输入名称
            $yq_num = $request->param('yq_num', '');//请输入邀请人数
            $jl_num = $request->param('jl_num', '');//请输入奖励获得
            $status = $request->param('status', 0);//请输入状态
            $info = $request->param('task_info', '');//请输入任务介绍
            
            if($yq_num == '' || $jl_num == ''){
                return json(['code' => 2, 'msg' => '请输入邀请人数和奖励获得数']);
            }
            
            if ($MTask->insert([
                'task_name' => $task_name,
                'yq_num' => $yq_num,
                'jl_num' => $jl_num,
                'status' => $status,
                'task_info' => $info
            ])) {
                return json(['code' => 1, 'msg' => '新增成功']);
            } else {
                return json(['code' => 2, 'msg' => '新增失败']);
            }
        }
        return view();
    }

    
    //任务状态修改
    public function taskEditStatus(Request $request){
        $id = $request->param('id', '');//请输入互助id
        $status = $request->param('status', '');
        if ($id == '' || $status == ''){
            return json(['code' =>2,'msg' => '参数错误']);
        }
        $MTask = new MTask();
        if ($MTask->where('id', $id)->update(['status'=>$status])) {
            return json(['code' =>1,'msg' => '操作成功']);
        }else{
            return json(['code' =>2,'msg' => '操作失败']);
        }
    }
    
    
    //任务编辑页面
    public function taskEdit(Request $request){
        $id = $request->param('id', '');//请输入互助id
        if ($id == ''){
            return json(['code' =>2,'msg' => '参数错误']);
        }
        
        $MTask = new MTask();
        $mutualaid_info = $MTask->getInfo(['id'=>$id]);
        $this->assign('mutualaid_info', $mutualaid_info);
        return view();
    }
    //互助编辑页面提交
    public function taskEditPost(Request $request){
        $task_id = $request->param('task_id', 0);
        $task_name = $request->param('task_name', '');//请输入名称
        $yq_num = $request->param('yq_num', '');//请输入胜利或得
        $jl_num = $request->param('jl_num', '');//请输入失败或得
        $info = $request->param('task_info', '');//请输入任务介绍
        $status = $request->param('status', 0);//请输入状态
        
        if($task_id == 0){
            return json(['code' => 2, 'msg' => '数据错误']);
        }
        
        $MTask = new MTask();
        if ($MTask->where(['id'=>$task_id])->update([
            'task_name' => $task_name,
            'yq_num' => $yq_num,
            'jl_num' => $jl_num,
            'status' => $status,
            'task_info' => $info
        ])) {
            return json(['code' => 1, 'msg' => '编辑成功']);
        } else {
            return json(['code' => 2, 'msg' => '编辑失败']);
        }
    }
    
    //删除任务
    public function taskDelete(Request $request){
        $mu_id = intval($request->param('mu_id', 0)); // 任务id
        if($mu_id == 0){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        $MTask = new MTask();
        if($MTask->where('id', $mu_id)->delete()){
            return json(['code' => 1, 'msg' => '删除成功']);
        }else{
            return json(['code' => 2, 'msg' => '删除失败']);
        }
    }
    
      
    
    
    public function taskMemberList(Request $request){
        //$serach = $request->param('serach', ''); // 关键字搜索 账号
        $name = $request->param('name', ''); //宠物
        $add_time_s = $request->param('add_time_s', '');
        $add_time_e = $request->param('add_time_e', '');
        $allParams = ['query' => $request->param()];
        //$this->assign('param_serach', $serach);
        $this->assign('param_name', $name);
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        
        $MTask = new MTask();
        
        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        
        /*if($serach != ''){
         $where .= ' and a.`uid` like \'%'.$serach.'%\' OR a.`num` like \'%'.$serach.'%\'  OR a.`purchase_no` like \'%'.$serach.'%\'';
         }*/
        if($name != ''){
            $where .= ' and a.`p_id` = '.$name;
        }
        if ($add_time_s != '') {
            $where .= " and a.`sta_time` >= " . strtotime($add_time_s);
        }
        if ($add_time_e != '') {
            $where .= " and a.`sta_time` <= " . strtotime($add_time_e);
        }
        
        $MMutualAidExamine = new MMutualAidExamine();
        $list = $MMutualAidExamine->getlists($where, $pageSize, $allParams);
        $this->assign('list',$list);
        
        $info = $MTask->getList('','id,task_name as name','id desc');
        $this->assign('info',$info);
        return view();
    }
    
    
    
    
    // 审核 通过/拒绝
    public function taskagree(Request $request)
    {
        $id = intval($request->param('id', 0)); //id
        $status = intval($request->param('status', 0)); //id
        if ($id == 0 || $status == 0) {
            return json(['code' => 2, 'msg' => '未指定信息']);
        }
        
        $MMutualAidExamine = new MMutualAidExamine();
        $info = $MMutualAidExamine->getinfo(['id'=>$id]);
        if(empty($info)){
            return json(['code' => 2, 'msg' => '数据错误']);
        }
        $member_info = Db::name('member_list')->where(['id'=>$info['uid']])->field('tel,balance')->find();
        //$mo_info = Db::name('mutualaid_order')->where(['id'=>$info['order_id']])->field('p_id')->find();
        
        $MTask = new MTask();
        $ml_info = $MTask->getinfo(['id'=>$info['p_id']],'jl_num');
        
        if($status == 2){
            
            Db::name('member_list')->where(['id'=>$info['uid']])->update([
                'balance'   => Db::raw('balance +'.$ml_info['jl_num']),
            ]);
            
            $data6 = [
                'u_id' => $info['uid'],
                'tel' => $member_info['tel'],
                'o_id' => 0,
                'former_money' => $member_info['balance'],
                'change_money' => $ml_info['jl_num'],
                'after_money' => $member_info['balance']+$ml_info['jl_num'],
                'type' => 2,
                'message' => '完成任务活动'.$ml_info['jl_num'],
                'message_e' => 'Complete the task and obtain '.$ml_info['jl_num'],
                'bo_time' => getIndaiTime(time()),
                'status' => 106
            ];
            Db::name('member_balance_log')->insert($data6);
            
            $result = $MMutualAidExamine->where(['id'=>$id])->update(['end_time'=>time(),'status'=>2]);
        }else{
            $result = $MMutualAidExamine->where(['id'=>$id])->update(['end_time'=>time(),'status'=>3]);
        }
        
        if($result){
            return json(['code' => 1, 'msg' => '审核成功']);
        }else{
            return json(['code' => 2, 'msg' => '审核失败']);
        }
        
    }
    
    

    
}

