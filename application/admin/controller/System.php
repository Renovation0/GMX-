<?php

namespace app\admin\controller;


use app\admin\model\K;
use app\admin\model\KCoin;
use app\admin\model\SystemConfig;
use app\admin\model\SystemLog;
use app\admin\model\SystemLoginLog;
use app\admin\model\SystemRole;
use app\admin\model\SystemSmsLog;
use app\admin\model\SystemUser;
use module\Redis;
use think\Request;
use app\admin\model\MExchange;
use think\Db;

class System extends Check
{
    // 系统参数配置页渲染
    public function config(Request $request)
    {
        $my_active_module = intval($request->param('my_active_module', 1));
        $this->assign('my_active_module', $my_active_module);
        $systemConfigModel = new SystemConfig();
        $configs = $systemConfigModel->getBlock($my_active_module);
        // var_dump($configs);die();
        $this->assign('configs', $configs);
        return view();
    }

    // 系统参数编辑提交
    public function configEdit(Request $request)
    {
        $params = $request->param();
/*         $redis = new Redis();
        $redis = $redis->redis(); */
        unset($params['/admin/system/configEdit']);
        $systemConfigModel = new SystemConfig();
        if(!empty($params)){
            foreach ($params as $key => $param) {
                $systemConfigModel->where('key', $key)->setField('value', $param);
                //$redis->hSet('config', $key, $param);
            }
            $this->success('修改成功');
        }else{
            $this->error('没有数据');
        }
    }

    // 系统日志页渲染
    public function log(Request $request)
    {
        $u_id = intval($request->param('u_id', 0)); // 管理员id
        $allParams = ['query' => $request->param()];
        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if ($u_id != 0) {
            $this->assign('param_u_id', $u_id);
            $where .= ' and `u_id` = ' . $u_id;
        } else {
            $this->assign('param_u_id', '');
        }
        $systemLogModel = new SystemLog();
        $logList = $systemLogModel->getLogs($where, $pageSize, $allParams);
        /* foreach ($logList as $k => $v){
            $logList[$k]['time'] = getIndaiTime($v['time']);
        } */
        $this->assign('list', $logList);
        return view();
    }

    // 删除日志
    public function logDelete(Request $request)
    {
        $ids = $request->param('id/a', []);
        if (empty($ids)) {
            return json(['code' => 2, 'msg' => '请选择要删除的日志']);
        }
        $systemLogModel = new SystemLog();
        return $systemLogModel->deleteLog($ids);
    }

    // 管理员登录日志页渲染
    public function loginLog(Request $request)
    {
        $u_id = intval($request->param('u_id', 0)); // 管理员id
        $allParams = ['query' => $request->param()];
        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if ($u_id != 0) {
            $this->assign('param_u_id', $u_id);
            $where .= ' and `u_id` = ' . $u_id;
        } else {
            $this->assign('param_u_id', '');
        }
        $systemLoginLogModel = new SystemLoginLog();
        $logList = $systemLoginLogModel->getLogs($where, $pageSize, $allParams);
        $this->assign('list', $logList);
        return view();
    }

    // 删除管理员登录日志
    public function loginLogDelete(Request $request)
    {
        $ids = $request->param('id/a', []);
        if (empty($ids)) {
            return json(['code' => 2, 'msg' => '请选择要删除的日志']);
        }
        $systemLoginLogModel = new SystemLoginLog();
        return $systemLoginLogModel->deleteLog($ids);
    }

    // 回收站渲染
    public function recycle(Request $request)
    {
        $type = intval($request->param('type', 1)); // 类型 默认为角色
        $allParams = ['query' => $request->param()];
        $this->assign('param_type', $type);
        $pageSize = 20; // 分页大小
        if ($type == 1) {
            $systemRoleModel = new SystemRole();
            $list = $systemRoleModel->getRecycle($pageSize, $allParams);
        } elseif ($type == 2) {
            $systemUserModel = new SystemUser();
            $list = $systemUserModel->getRecycle($pageSize, $allParams);
        } else {
            $list = [];
            $this->error('类型有误');
        }
        $this->assign('list', $list);
        return view();
    }

    // 回收站恢复单项数据
    public function recycleBack(Request $request)
    {
        $type = intval($request->param('type', 0)); // 类型
        $id = intval($request->param('id', 0)); // 数据id
        if ($type == 0) {
            return json(['code' => 2, 'msg' => '请选择要恢复数据的分类']);
        }
        if ($id == 0) {
            return json(['code' => 2, 'msg' => '请选择要恢复的数据']);
        }
        switch ($type) {
            case 1: // 角色
                $systemRoleModel = new SystemRole();
                return $systemRoleModel->recycleBack($id);
                break;
            case 2: // 管理员
                $systemUserModel = new SystemUser();
                return $systemUserModel->recycleBack($id);
                break;
            default:
                return json(['code' => 2, 'msg' => '类型错误']);
        }
    }

    // 回收站恢复单项数据
    public function recycleClear(Request $request)
    {
        $type = intval($request->param('type', 0)); // 类型
        $id = intval($request->param('id', 0)); // 数据id
        if ($type == 0) {
            return json(['code' => 2, 'msg' => '请选择要清除数据的分类']);
        }
        if ($id == 0) {
            return json(['code' => 2, 'msg' => '请选择要清除的数据']);
        }
        switch ($type) {
            case 1: // 角色
                $systemRoleModel = new SystemRole();
                return $systemRoleModel->recycleClear($id);
                break;
            case 2: // 管理员
                $systemUserModel = new SystemUser();
                return $systemUserModel->recycleClear($id);
                break;
            default:
                return json(['code' => 2, 'msg' => '类型错误']);
        }
    }

    //短信记录
    public function smsLog(Request $request)
    {
        $tel = $request->param('tel', 0); // 数据id
        $allParams = ['query' => $request->param()];
        $add_time_s = $request->param('add_time_s', ''); // 开启时间开始
        $add_time_e = $request->param('add_time_e', ''); // 开启时间结束
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        $pageSize = 20; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if ($tel != 0) {
            $this->assign('tel', $tel);
            $where .= ' and `tel` = ' . $tel;
        } else {
            $this->assign('tel', '');
        }
        if ($add_time_s != '') {
            $where .= " and `time` >= " . strtotime($add_time_s);
        }
        if ($add_time_e != '') {
            $where .= " and `time` <= " . strtotime($add_time_e);
        }
        $sms = new SystemSmsLog();
        $list = $sms->getlists($where, $pageSize, $allParams);
        /* foreach ($list as $k => $v){
            $list[$k]['time'] = getIndaiTime($v['time']);
        } */
        $this->assign('list', $list);
        return view();
    }
    
    //注册协议
    public function gvrp(Request $request){
        $M_MExchange = new MExchange();
        if($request->isAjax()){
            $id = $request->param('id', '');
            $content = $request->param('content', '');
            if($id == ''){
                return json(['code' => 2, 'msg' => '参数错误']);
            }
            if($content == ''){
                return json(['code' => 2, 'msg' => '请输入内容']);
            }
            $data = [
                'content'          =>  $content
            ];
            $res = Db::name('gvrp')->where(['id'=>$id])->update($data);
            if($res){
                return json(['code' => 1, 'msg' => '修改成功']);
            }else{
                return json(['code' => 2, 'msg' => '修改失败']);
            }
        }        
        $info = Db::name('gvrp')->where('id = 1')->find();  
        
         $text = $info['content'];
        
            $text = str_replace("﹤","<",$text);
            $text = str_replace("﹥",">",$text);
            $text = str_replace("﹠","&",$text);
            $text = str_replace("﹔",";",$text);
            
       $info['content'] =  $text;
            
        $this->assign('info',$info);
        return view();
    }
    

    // K线图
    public function kLine()
    {
        $kModel = new K();
        $k_line_datas = $kModel->order('time desc')->limit(30)->select();
        if ($k_line_datas) {
            $k_line_datas = $k_line_datas->toArray();
            $k_line_datas = array_reverse($k_line_datas);
        } else {
            $k_line_datas = [];
        }
        $this->assign('k_line_datas', $k_line_datas);
        return view();
    }

    // 修改K线数据
    public function kLineSet(Request $request)
    {
        $time = $request->param('time', '');
        $value = floatval($request->param('value', 0.000));
        if ($time == '') {
            return json(['code' => 2, 'msg' => '非法操作']);
        }
        $kModel = new K();
        if ($kModel->where('time', $time)->setField('value', $value)) {
            // 修改数据后删除缓存
            $this->redis->del('linear_graph_balance');
            return json(['code' => 1, 'msg' => '修改成功']);
        } else {
            return json(['code' => 2, 'msg' => '修改失败']);
        }
    }

    // Kcoin线图
    public function kLinecoin()
    {
        $kModel = new KCoin();
        $k_line_datas = $kModel->order('time desc')->limit(30)->select();
        if ($k_line_datas) {
            $k_line_datas = $k_line_datas->toArray();
            $k_line_datas = array_reverse($k_line_datas);
        } else {
            $k_line_datas = [];
        }
        $this->assign('k_line_datas', $k_line_datas);
        return view();
    }

    // 修改Kcoin线数据
    public function kLineCoinset(Request $request)
    {
        $time = $request->param('time', '');
        $value = floatval($request->param('value', 0.000));
        if ($time == '') {
            return json(['code' => 2, 'msg' => '非法操作']);
        }
        $kModel = new KCoin();
        if ($kModel->where('time', $time)->setField('value', $value)) {
            // 修改数据后删除缓存
            $this->redis->del('linear_graph_coin');
            return json(['code' => 1, 'msg' => '修改成功']);
        } else {
            return json(['code' => 2, 'msg' => '修改失败']);
        }
    }
    
    
    /**
     * 首页交易所
     */
    //列表
    public function exchangeList(Request $request){
        $M_MExchange = new MExchange();
        $allParams = ['query' => $request->param()];
        $pageSize = 10; // 分页大小
        $Lists = $M_MExchange->order('id desc')->paginate($pageSize, false, $allParams);
        $this->assign('list', $Lists);

        return view();
    }
    
    //增加
    public function exchangeAdd(Request $request){
        $M_MExchange = new MExchange();
        if($request->isAjax()){
            $c_name = $request->param('c_name', '');
            $e_name = $request->param('e_name', '');
            $img = $request->param('img', '');
            $url = $request->param('url', '');
            if($e_name == ''){//$c_name == '' || 
                return json(['code' => 2, 'msg' => '请输入标题名称']);
            }
            if($img == ''){
                return json(['code' => 2, 'msg' => '请上传缩略图']);
            }
            if($url == ''){
                return json(['code' => 2, 'msg' => '请输入内容']);
            }
            $data = [
                'c_name'          =>  $c_name,
                'e_name'      =>  $e_name,
                'img'           =>  $img,
                'url'         =>  $url
            ];
            $res = $M_MExchange->insert($data);
            if($res){
                return json(['code' => 1, 'msg' => '添加成功']);
            }else{
                return json(['code' => 2, 'msg' => '添加失败']);
            }
        }
        return view();
    }
    
    //编辑
    public function exchangeEdit(Request $request){
        $M_MExchange = new MExchange();
        if($request->isAjax()){
            $ids = $request->param('id', '');
            $c_name = $request->param('c_name', '');
            $e_name = $request->param('e_name', '');
            $img = $request->param('img', '');
            $url = $request->param('url', '');
            if($ids == ''){
                return json(['code' => 2, 'msg' => '参数错误']);
            }
            if($e_name == ''){
                return json(['code' => 2, 'msg' => '请输入标题名称']);
            }
            if($img == ''){
                return json(['code' => 2, 'msg' => '请上传缩略图']);
            }
            if($url == ''){
                return json(['code' => 2, 'msg' => '请输入内容']);
            }
            $M_MExchange = new MExchange();
            $data = [
                'c_name'          =>  $c_name,
                'e_name'      =>  $e_name,
                'img'           =>  $img,
                'url'         =>  $url
            ];
            $res = $M_MExchange->where(['id'=>$ids])->update($data);
            if($res){
                return json(['code' => 1, 'msg' => '修改成功']);
            }else{
                return json(['code' => 2, 'msg' => '修改失败']);
            }
        }
        $id = intval($request->param('id', 0));
        if($id == 0) return json(['code' => 2, 'msg' => '参数错误']);
        $info = $M_MExchange->getInfo(['id'=>$id]);
        $this->assign('info',$info);
        return view();
    }
    
    
    //删除
    public function exchangeDel(Request $request){
        $n_id = intval($request->param('n_id', 0));
        if($n_id == 0){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        $M_MExchange = new MExchange();
        if($M_MExchange->where('id', $n_id)->delete()){
            return json(['code' => 1, 'msg' => '删除成功']);
        }else{
            return json(['code' => 2, 'msg' => '删除失败']);
        }
    }
    
    
    //工单
    public function workorderList(Request $request){
        $allParams = ['query' => $request->param()];
        $pageSize = 10; // 分页大小
        $Lists = Db::name('work_order')->order('id desc')->paginate($pageSize, false, $allParams);
        
        $img_list = array();
        foreach ($Lists as $k => $v){
            if(empty($v['img'])){
                $img_list[$v['id']]['img1'] = '';
                $img_list[$v['id']]['img2'] = '';
                $img_list[$v['id']]['img3'] = '';
            }else{
                $img = explode(',', $v['img']);
                $img_list[$v['id']]['img1'] = $img[0] == ''?'':$img[0];
                $img_list[$v['id']]['img2'] = isset($img[1])?$img[1]:'';
                $img_list[$v['id']]['img3'] = isset($img[2])?$img[2]:'';
            }
        }
        
        $List = array(
            'list'=>$Lists,
            'img_list'=>$img_list,
        );
        
        $this->assign('list', $List);
        
        return view('work_list');
    }

}