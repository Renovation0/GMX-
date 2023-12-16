<?php
namespace app\admin\controller;


use think\Request;
use app\admin\model\MMutualAid;
use app\admin\model\MMutualAidOrder;
use app\admin\model\MMember;
use app\admin\model\MMemberMutual;
use think\Exception;
use think\Db;
use app\admin\model\MMutualAidLog;
use app\api\model\MConfig;
use app\admin\model\MMutualAidExamine;

class Mutualaid extends Check
{   
    private function makeRand($num = 9)
    {
        mt_srand((double)microtime() * 1000000);//用 seed 来给随机数发生器播种。
        $strand = str_pad(mt_rand(1, 99999), $num, "0", STR_PAD_LEFT);
        if (Db::name('member_mutualaid')->where('orderNo', 'MT' . date('Ymd') . $strand)->count() == 0) {
            return 'MT' . date('Ymd') . $strand;
        }
        $this->makeRand();
    }
    
    private function makeRandCW()
    {   
        $list = Db::name('member_mutualaid')->where('purchase_no != ""')->order('purchase_no desc')->select();
        if (empty($list)) {
            return 'CW' . date('Ymd') . '00001';
        }else{
            $num = intval(substr($list[0]['purchase_no'],2))+1;
            $num = intval(substr_replace($num, date('md',time()), 4, 4));
             return 'CW' .$num;
            //return 'CW' . date('Ymd') . '00002';
        }
        $this->makeRandCW();
    }
    
    //互助列表
    public function mutualAidList(Request $request){
        $status = intval($request->param('status', 0)); // 状态
        $level = intval($request->param('level', 0)); // 等级
        $serach = $request->param('serach', ''); // 关键字搜索  名称/龙珠/收益天数
        $add_time_s = $request->param('add_time_s', '');
        $add_time_e = $request->param('add_time_e', ''); 
        $allParams = ['query' => $request->param()];
        $this->assign('param_status', $status);
        $this->assign('param_serach', $serach);
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        $this->assign('param_level', $level);
        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if($status != 0){
            $where .= ' and a.`status` = '.$status;
        }
        if($level != 0){
            $where .= ' and a.`level` = '.$level;
        }
        if($serach != ''){
            $where .= ' and a.`name` like \'%'.$serach.'%\' OR a.`subscribe_energy` like \'%'.$serach.'%\' OR a.`energy` like \'%'.$serach.'%\' OR a.`rate` like \'%'.$serach.'%\'';
        }

        $MutualAid = new MMutualAid();
        $list = $MutualAid->getlists($where, 'sort desc', $pageSize, $allParams);
        $this->assign('list',$list);
        
/*         $MMutualAid = new MMutualAid();
        $info = $MMutualAid->getList('','id,name','id desc');
        $this->assign('info',$info); */
        return view();
    }
    
    //新增互助页面
    public function mutualaidAdd(){
        $MutualAid = new MMutualAid();
        $aid_list = $MutualAid->getList();
        $this->assign('aid_list',$aid_list);
        return view();
    }

    //新增互助提交
    public function mutualaidAddPost(Request $request){
        $name = $request->param('name', '');//请输入名称
        $english_name = $request->param('english_name', '');//请输入英文名称
        $logo = $request->param('log', '');//请选择logo
        $name_log = $request->param('name_log', '');//请选择名称logo
        $shop_logo = $request->param('shop_logo', '');//请选择产品logo
        $min_price = $request->param('min_price', 0);//请输入最小价格
        $max_price = $request->param('max_price', 0);//请输入最大价格
        $up_appoint = intval($request->param('up_appoint', '0'));//请输入指定升级
        $give_balance = $request->param('give_balance', 0);//请输入赠送积分
        $sale_expend = $request->param('sale_expend', 0);//请输入出售价格
        $energy= $request->param('energy', 0);//请输入预约龙珠
        $subscribe_energy = $request->param('subscribe_energy', 0);//请输入即抢龙珠
        $rate = $request->param('rate', 0);//请输入收益比例
        $days = $request->param('days', 0);//请输入升级天数
        $sort = $request->param('sort', 0);//请输入排序
        $purchaseNum = $request->param('purchaseNum', 0);//请输入预约人数
        $sta_time = $request->param('sta_time', '');//请输入筑梦开始时间
        $end_time = $request->param('end_time', '');//请输入筑梦结束时间
        $status = $request->param('status', 0);//请输入状态
        $level = $request->param('level', 0);//请输入等级
        $award= $request->param('award', 0);//请输入预约人返还
        $f_award= $request->param('f_award', 0);//请输入推荐人返还
        $single_field= $request->param('single_field', '');//压单
        
        $logo = $this->updatexg($logo);
        $name_log = $this->updatexg($name_log);
        $shop_logo = $this->updatexg($shop_logo);
        
        if ($name == ''){
            return json(['code' =>2,'msg' => '请输入名称']);
        }
/*         if ($english_name == ''){
            return json(['code' =>2,'msg' => '请输入英文名称']);
        } */
        if ($logo == ''){// || $name_log == ''
            return json(['code' =>2,'msg' => '请选择logo']);
        }
        if (($min_price == 0 || !is_numeric($min_price)) || ($max_price == 0 || !is_numeric($max_price))){
            return json(['code' =>2,'msg' => '请输入最小价格与最大价格']);
        }
        if($min_price >= $max_price){
            return json(['code' =>2,'msg' => '最小价格不能大于或等于最大价格']);
        }
/*         if ($sale_expend == 0 || !is_numeric($sale_expend)){
            return json(['code' =>2,'msg' => '请输入出售价格']);
        }
        if ($award == 0 || !is_numeric($award)){
            return json(['code' =>2,'msg' => '请输入预约人返还']);
        }
        if ($f_award == 0 || !is_numeric($f_award)){
            return json(['code' =>2,'msg' => '请输入推荐人返还']);
        }
        if ($up_appoint == 0 || !is_numeric($up_appoint)){
            return json(['code' =>2,'msg' => '请输入指定升级']);
        }*/
        if ($give_balance == 0 || !is_numeric($give_balance)){
            return json(['code' =>2,'msg' => '请输入赠送HTT']);
        }
        if ($energy == 0 || !is_numeric($energy)){
            return json(['code' =>2,'msg' => '请输入预约龙珠']);
        }
        if ($subscribe_energy == 0 || !is_numeric($subscribe_energy)){
            return json(['code' =>2,'msg' => '请输入即抢龙珠']);
        }
        if ($rate == 0 || !is_numeric($rate)){
            return json(['code' =>2,'msg' => '请输入收益比例']);
        }
        if ($days == 0 || !is_numeric($days)){
            return json(['code' =>2,'msg' => '请输入升级天数']);
        }
        /* if ($purchaseNum == 0 || !is_numeric($purchaseNum)){
            return json(['code' =>2,'msg' => '请输入预约人数']);
        } */
        if ($sta_time == '' || $end_time == '' ){
            return json(['code' =>2,'msg' => '请输入筑梦开始时间与结束时间']);
        }
        if(strtotime($sta_time) >= strtotime($end_time)){
            return json(['code' =>2,'msg' => '筑梦开始时间不能大于或等于结束时间']);
        }
        if ($status == 0 || !is_numeric($status)){
            return json(['code' =>2,'msg' => '请输入状态']);
        }
        /* if ($level == 0 || !is_numeric($level)){
            return json(['code' =>2,'msg' => '请输入等级']);
        } */  
        if ($logo && $name && $min_price && $max_price && $energy && $subscribe_energy && $rate && $sta_time && $end_time && $status){
            
            $data = [
                'name'=>$name,
                'english_name'=>$english_name,
                'logo'=>$logo,
                'name_log'=>$name_log,
                'shop_logo'=>$shop_logo,
                'min_price'=>$min_price,
                'max_price'=>$max_price,
                'sale_expend'=>$sale_expend,
                'award'=>$award,
                'f_award'=>$f_award,
                'up_appoint'=>$up_appoint,
                'give_balance'=>$give_balance,
                'energy'=>$energy,
                'subscribe_energy'=>$subscribe_energy,
                'rate'=>$rate,
                'days'=>$days,
                'sort'=>$sort,
                'purchaseNum'=>$purchaseNum,
                'sta_time'=>strtotime($sta_time),
                'end_time'=>strtotime($end_time),
                'status'=>$status,
                'level'=>$level,
                'single_field'=>$single_field
            ];
            
            //添加互助
            $MutualAid = new MMutualAid();
            $add = $MutualAid->addMutualAid($data);
            if ($add){
                return json(['code' =>1,'msg' => '添加成功']);
            }else{
                return json(['code' =>2,'msg' => '添加失败']);
            }
        }else{
            return json(['code' =>2,'msg' => '参数错误']);
        }
    }
    
    //互助状态修改
    public function mutualAidEditStatus(Request $request){
        $id = $request->param('id', '');//请输入互助id
        $status = $request->param('status', '');
        if ($id == '' || $status == ''){
            return json(['code' =>2,'msg' => '参数错误']);
        }
        
        $MutualAid = new MMutualAid();
        return $MutualAid->editStatus($id, $status);
    }
    
    
    //互助编辑页面
    public function mutualaidEdit(Request $request){
        $id = $request->param('id', '');//请输入互助id
        if ($id == ''){
            return json(['code' =>2,'msg' => '参数错误']);
        }
        
        $MutualAid = new MMutualAid();
        $mutualaid_info = $MutualAid->getInfo(['id'=>$id]);
        $this->assign('mutualaid_info', $mutualaid_info);
        
        $aid_list = $MutualAid->getList();
        $this->assign('aid_list',$aid_list);
        
        return view();
    }
    //互助编辑页面提交
    public function mutualaidEditPost(Request $request){
        $name = $request->param('name', '');//请输入名称
        $english_name = $request->param('english_name', '');//请输入名称
        $logo = $request->param('log', '');//请选择logo
        $name_log = $request->param('name_log', '');//请选择名称logo
        $shop_logo = $request->param('shop_logo', '');//请选择产品logo
        $min_price = abs($request->param('min_price', 0));//请输入最小价格
        $max_price = abs($request->param('max_price', 0));//请输入最大价格
        $up_appoint = intval($request->param('up_appoint', '0'));//请输入指定升级
        $give_balance = $request->param('give_balance', 0);//请输入赠送积分
        $energy= $request->param('energy', 0);//请输入预约龙珠
        $subscribe_energy = $request->param('subscribe_energy', 0);//请输入即抢龙珠
        $rate = $request->param('rate', 0);//请输入收益比例
        $days = $request->param('days', 0);//请输入升级天数
        $sort = $request->param('sort', 0);//请输入排序
        $purchaseNum = $request->param('purchaseNum', 0);//请输入预约人数
        $sta_time = $request->param('sta_time', '');//请输入筑梦开始时间
        $end_time = $request->param('end_time', '');//请输入筑梦结束时间
        $status = $request->param('status', 0);//请输入状态
        $level = $request->param('level', 0);//请输入等级
        $mu_id = $request->param('mu_id', '');
        $sale_expend = $request->param('sale_expend', 0);//请输入出售价格
        $award= $request->param('award', 0);//请输入预约人返还
        $f_award= $request->param('f_award', 0);//请输入推荐人返还
        $single_field= $request->param('single_field', '');//是否压单 1否 2是
        $introduce= $request->param('introduce', '');//简介
        
        $logo = $this->updatexg($logo);
        $name_log = $this->updatexg($name_log);
        $shop_logo = $this->updatexg($shop_logo);
        
        if ($mu_id == ''){
            return json(['code' =>2,'msg' => '参数错误']);
        }
        if ($name == ''){
            return json(['code' =>2,'msg' => '请输入名称']);
        }
        if ($english_name == ''){
            return json(['code' =>2,'msg' => '请输入英文名称']);
        }
        if ($logo == ''){// || $name_log == ''
            return json(['code' =>2,'msg' => '请选择logo']);
        }
        if (($min_price == 0 || !is_numeric($min_price)) || ($max_price == 0 || !is_numeric($max_price))){
            return json(['code' =>2,'msg' => '请输入最小价格与最大价格']);
        }
        if($min_price >= $max_price){
            return json(['code' =>2,'msg' => '最小价格不能大于或等于最大价格']);
        }
/*         if ($sale_expend == 0 || !is_numeric($sale_expend)){
            return json(['code' =>2,'msg' => '请输入出售价格']);
        }
        if ($award == 0 || !is_numeric($award)){
            return json(['code' =>2,'msg' => '请输入预约人返还']);
        }
        if ($f_award == 0 || !is_numeric($f_award)){
            return json(['code' =>2,'msg' => '请输入推荐人返还']);
        }
        if ($up_appoint == 0 || !is_numeric($up_appoint)){
            return json(['code' =>2,'msg' => '请输入指定升级']);
        }*/
        if ($give_balance == 0 || !is_numeric($give_balance)){
            return json(['code' =>2,'msg' => '请输入赠送辅币']);
        }
        if ($energy == 0 || !is_numeric($energy)){
            return json(['code' =>2,'msg' => '请输入预约主币']);
        }
        if ($subscribe_energy == 0 || !is_numeric($subscribe_energy)){
            return json(['code' =>2,'msg' => '请输入即抢主币']);
        }
        if ($rate == 0 || !is_numeric($rate)){
            return json(['code' =>2,'msg' => '请输入收益比例']);
        }
        if ($days == 0 || !is_numeric($days)){
            return json(['code' =>2,'msg' => '请输入升级天数']);
        }
/*         if ($purchaseNum == 0 || !is_numeric($purchaseNum)){
            return json(['code' =>2,'msg' => '请输入预约人数']);
        } */
        if ($sta_time == '' || $end_time == '' ){
            return json(['code' =>2,'msg' => '请输入筑梦开始时间与结束时间']);
        }
        if(strtotime($sta_time) >= strtotime($end_time)){
            return json(['code' =>2,'msg' => '筑梦开始时间不能大于或等于结束时间']);
        }
        if ($status == 0 || !is_numeric($status)){
            return json(['code' =>2,'msg' => '请输入状态']);
        }
        /* if ($level == 0 || !is_numeric($level)){
            return json(['code' =>2,'msg' => '请输入等级']);
        } */
        
/*         $sta_time_len = strlen($sta_time);
        if($sta_time_len > 5){
            $sta_time = substr($sta_time, 0, -3);
            var_dump($sta_time);
        }
        $end_time_len = strlen($end_time);
        if($end_time_len > 5){
            $end_time = substr($end_time, 0, -3);
            var_dump($end_time);
        } */
        $data = [
            'name'=>$name,
            'english_name'=>$english_name,
            'logo'=>$logo,
            'name_log'=>$name_log,
            'shop_logo'=>$shop_logo,
            'min_price'=>$min_price,
            'max_price'=>$max_price,
            'sale_expend'=>$sale_expend,
            'award'=>$award,
            'f_award'=>$f_award,
            'up_appoint'=>$up_appoint,
            'give_balance'=>$give_balance,
            'energy'=>$energy,
            'subscribe_energy'=>$subscribe_energy,
            'rate'=>$rate,
            'days'=>$days,
            'sort'=>$sort,
            'purchaseNum'=>$purchaseNum,
            'sta_time'=>$sta_time,
            'end_time'=>$end_time,
            'status'=>$status,
            'level'=>$level,
            'single_field'=>$single_field,
            'introduce'=>$introduce
            
        ];
        
        $MutualAid = new MMutualAid();
        $mutual_info = $MutualAid->getInfo(['id'=>$mu_id]);
        if (!$mutual_info){
            return json(['code' =>2,'msg' => '该互助不存在']);
        }else{
            return $MutualAid->editMutualaid($mu_id, $mutual_info, $data);
        }
    }
    
    //删除互助
    public function mutualAidDelete(Request $request){
        $mu_id = intval($request->param('mu_id', 0)); // 互助id
        if($mu_id == 0){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        $MutualAid = new MMutualAid();
        if($MutualAid->where('id', $mu_id)->delete()){
            return json(['code' => 1, 'msg' => '删除成功']);
        }else{
            return json(['code' => 2, 'msg' => '删除失败']);
        }
    }
    

    
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////会员互助
    
    //会员互助列表
    public function mutualAidMemberList(Request $request){
        $status = intval($request->param('status', 0)); // 状态
        $type = intval($request->param('type', 0)); // 类型
        $serach = $request->param('serach', ''); // 关键字搜索
        $name = $request->param('name', ''); //宠物
        $add_time_s = $request->param('add_time_s', '');
        $add_time_e = $request->param('add_time_e', '');
        $allParams = ['query' => $request->param()];
        $this->assign('param_status', $status);
        $this->assign('param_type', $type);
        $this->assign('param_serach', $serach);
        $this->assign('param_name', $name);
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        
        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if($status != 0){
            $where .= ' and a.`status` = '.$status;
        }
        if($type != 0){
            $where .= ' and a.`deal_type` = '.$type;
        }
        if($serach != ''){
            $where .= ' and a.`orderNo` like \'%'.$serach.'%\' OR a.`tel` like \'%'.$serach.'%\' OR a.`purchase_no` like \'%'.$serach.'%\'';
        }
        if($name != ''){
            $where .= ' and a.`purchase_id` = '.$name;
        }
        if ($add_time_s != '') {
            $add_time_s_arr = explode('~',$add_time_s);
            $where .= " and a.`sta_time` >= " . strtotime($add_time_s_arr[0])." and a.`sta_time` <= " . strtotime($add_time_s_arr[1]);
        }
        if ($add_time_e != '') {
            $add_time_e_arr = explode('~',$add_time_e);
            $where .= " and a.`end_time` >= " . strtotime($add_time_e_arr[0])." and a.`end_time` <= " . strtotime($add_time_e_arr[1]);
        }

        $MMemberMutual = new MMemberMutual();
        $list = $MMemberMutual->getlists($where, $pageSize, $allParams);
        $this->assign('list',$list);
        
        $MMutualAid = new MMutualAid();
        $info = $MMutualAid->getList('','id,name','id desc');
        $this->assign('info',$info);
        return view();
    }
    
    //会员互助增加
    public function mutualAidMemberAdd(Request $request){
        $MMemberMutual = new MMemberMutual();
        $MMutualAid = new MMutualAid();
        $MMember = new MMember();
        if ($request->isAjax()) {
            $purchase_id = intval($request->param('purchase_id', 0));
            $tel = $request->param('tel', '');
            $price = abs($request->param('price', ''));
            $max_price = abs($request->param('max_price', ''));
            $num = $request->param('num', '');

            if(empty($purchase_id)){
                return json(['code' => 2, 'msg' => '请选择产品']);
            }
            
            $mutual_info = $MMutualAid->getInfo(['id'=>$purchase_id]);
            if(!$mutual_info){
                return json(['code' => 2, 'msg' => '产品信息错误']);
            }
            
            if($tel == ''){
                return json(['code' => 2, 'msg' => '会员账号不能为空']);
            }
            $userInfo = $MMember->getInfo(['tel'=>$tel],'id,tel');
            if(!$userInfo){
                return json(['code' => 2, 'msg' => '该会员账号不存在']);
            }
            if($price == '' || $price == 0){
                return json(['code' => 2, 'msg' => '价格不能为空']);
            }
                

            Db::startTrans();
            try{
                for ($i = 0; $i < $num; $i++) {
                    //$orderNo = $this->makeRand();
                    //$purchase_no = $this->makeRandCW();
                    $p_id = Db::name('member_mutualaid')->insertGetId([
                        'uid' => $userInfo['id'],
                        'purchase_id' => $purchase_id,
                        //'purchase_no' => $purchase_no,
                        'tel' => $userInfo['tel'],
                        //'orderNo' => $orderNo,
                        'get_price' => $price,
                        'new_price' => $price,
                        'days' => $mutual_info['days'],
                        'rate' => $mutual_info['rate'],
                        'deal_type' => 4,
                        'status' => 1,
                        'sta_time' => time()
                    ]);
                }
                Db::commit();
                return json(['code' => 1,'msg' => '发放成功']);
            }catch (Exception $e){
                Db::rollback();
                return json(['code' => 2,'msg' => '发放失败']);
            }
        }
        
        $list = $MMutualAid->getList();
        $this->assign('list',$list);
        return view();
    }
    //会员互助编辑渲染
    function mutualAidMemberEdit(Request $request){
        $id = $request->param('id', '');//请输入互助id
        if(empty($id)){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        $MMemberMutual = new MMemberMutual();
        $MMemberMutual_info = $MMemberMutual->getInfo(['id'=>$id]);
        $this->assign('info',$MMemberMutual_info);
        
        return view();
    }
    
    //会员互助编辑
    function mutualAidMemberEditPost(Request $request){
        $MMember = new MMember();       
        $MMemberMutual = new MMemberMutual();
        
        $order_id = intval($request->param('order_id', 0));//订单ID
        $tel = $request->param('tel', '');//买家账号
        
        if(empty($order_id) || $order_id == 0 || $tel == ''){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        
        $info = $MMember->getInfo(['tel'=>$tel]);
        if(!$info){
            return json(['code' => 2, 'msg' => '该账号不存在']);
        }
        
        $res = $MMemberMutual->where('id',$order_id)->update(['tel'=>$tel,'uid'=>$info['id']]);
        if($res){
            return json(['code' => 1, 'msg' => '修改成功']);
        }else{
            return json(['code' => 1, 'msg' => '修改失败']);
        }
    }
    
    //会员互助删除
    public function mutualaidmemberdelete(Request $request){
        $mu_id = intval($request->param('mu_id', 0)); // 订单id
        if($mu_id == 0){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        $MMemberMutual = new MMemberMutual();
        
        
        try {
            Db::startTrans();
            
            $MMemberMutual->where('id', $mu_id)->update(['is_exist'=>0]);
            
            //Db::name('mutualaid_order')->where('p_id',$mu_id)->update(['is_exist'=>0]);

            Db::commit();
            return json(['code' => 1, 'msg' => '操作成功']);
        } catch (Exception $e) {
            Db::rollback();
            return json(['code' => 2, 'msg' => '操作失败'.$e->getMessage()]);
        }
    }

    
    
}

