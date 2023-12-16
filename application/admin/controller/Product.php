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
use app\admin\model\MMemberLevel;

class Product extends Check
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
    
    //产品列表
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
            $where .= ' and a.`name` like \'%'.$serach.'%\' OR a.`price` like \'%'.$serach.'%\' OR a.`rate` like \'%'.$serach.'%\'';
        }

        $MutualAid = new MMutualAid();
        $list = $MutualAid->getlists($where, 'sort desc', $pageSize, $allParams);
        $this->assign('list',$list);

        return view();
    }
    
    //新增产品页面
    public function mutualaidAdd(){
        $MMemberLevel = new MMemberLevel();
        $aid_list = $MMemberLevel->getList();
        $this->assign('aid_list',$aid_list);
        return view();
    }

    //新增产品提交
    public function mutualaidAddPost(Request $request){
        $name = $request->param('name', '');//请输入名称
        $logo = $request->param('log', '');//请选择logo
        $price = $request->param('price', 0);//请输入价格
        $rate = $request->param('rate', 0);//请输入收益比例
        $days = $request->param('days', 0);//请输入升级天数
        $sort = $request->param('sort', 0);//请输入排序
        $purchaseNum = $request->param('purchaseNum', 0);//请输入预约人数
        $status = $request->param('status', 0);//请输入状态
        $level = $request->param('level', 0);//请输入等级
        $introduce = $request->param('introduce', '');//请输入简介
        
        $logo = $this->updatexg($logo);
        
        if ($name == ''){
            return json(['code' =>2,'msg' => '请输入名称']);
        }
        if ($logo == ''){// || $name_log == ''
            return json(['code' =>2,'msg' => '请选择logo']);
        }
        if (($price == 0 || !is_numeric($price))){
            return json(['code' =>2,'msg' => '请输入价格']);
        }
        if ($rate == 0 || !is_numeric($rate)){
            return json(['code' =>2,'msg' => '请输入收益比例']);
        }
        if ($days == 0 || !is_numeric($days)){
            return json(['code' =>2,'msg' => '请输入收益天数']);
        }
        if ($status == 0 || !is_numeric($status)){
            return json(['code' =>2,'msg' => '请输入状态']);
        }
        /* if ($level == 0 || !is_numeric($level)){
            return json(['code' =>2,'msg' => '请输入等级']);
        } */  
        if ($logo && $name && $price && $rate && $status){
            
            $data = [
                'name'=>$name,
                'logo'=>$logo,
                'price'=>$price,
                'rate'=>$rate,
                'days'=>$days,
                'sort'=>$sort,
                'purchaseNum'=>$purchaseNum,
                'status'=>$status,
                'level'=>$level,
                'introduce'=>$introduce
            ];
            
            //添加产品
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
    
    //产品状态修改
    public function mutualAidEditStatus(Request $request){
        $id = $request->param('id', '');//请输入产品id
        $status = $request->param('status', '');
        if ($id == '' || $status == ''){
            return json(['code' =>2,'msg' => '参数错误']);
        }
        
        $MutualAid = new MMutualAid();
        return $MutualAid->editStatus($id, $status);
    }
    
    
    //产品编辑页面
    public function mutualaidEdit(Request $request){
        $id = $request->param('id', '');//请输入产品id
        if ($id == ''){
            return json(['code' =>2,'msg' => '参数错误']);
        }
        
        $MutualAid = new MMutualAid();
        $mutualaid_info = $MutualAid->getInfo(['id'=>$id]);
        $this->assign('mutualaid_info', $mutualaid_info);

        $MMemberLevel = new MMemberLevel();
        $aid_list = $MMemberLevel->getList();
        $this->assign('aid_list',$aid_list);
        
        return view();
    }
    //产品编辑页面提交
    public function mutualaidEditPost(Request $request){
        $mu_id = $request->param('mu_id', '');
        $name = $request->param('name', '');//请输入名称
        $logo = $request->param('log', '');//请选择logo
        $price = $request->param('price', 0);//请输入价格
        $rate = $request->param('rate', 0);//请输入收益比例
        $days = $request->param('days', 0);//请输入升级天数
        $sort = $request->param('sort', 0);//请输入排序
        $purchaseNum = $request->param('purchaseNum', 0);//请输入预约人数
        $status = $request->param('status', 0);//请输入状态
        $level = $request->param('level', 0);//请输入等级
        $introduce = $request->param('introduce', '');//请输入简介
        $zpurchaseNum = $request->param('zpurchaseNum', 0);//总份数
        $issellpurchaseNum = $request->param('issellpurchaseNum', 0);//总份数
        
        $logo = $this->updatexg($logo);
        if ($mu_id == ''){
            return json(['code' =>2,'msg' => '参数错误']);
        }
        if ($name == ''){
            return json(['code' =>2,'msg' => '请输入名称']);
        }
        if ($logo == ''){// || $name_log == ''
            return json(['code' =>2,'msg' => '请选择logo']);
        }
        if (($price == 0 || !is_numeric($price))){
            return json(['code' =>2,'msg' => '请输入价格']);
        }
        if ($rate == 0 || !is_numeric($rate)){
            return json(['code' =>2,'msg' => '请输入收益比例']);
        }
        if ($days == 0 || !is_numeric($days)){
            return json(['code' =>2,'msg' => '请输入收益天数']);
        }
        if ($status == 0 || !is_numeric($status)){
            return json(['code' =>2,'msg' => '请输入状态']);
        }
        
        $data = [
            'name'=>$name,
            'logo'=>$logo,
            'price'=>$price,
            'rate'=>$rate,
            'days'=>$days,
            'sort'=>$sort,
            'purchaseNum'=>$purchaseNum,
            'status'=>$status,
            'level'=>$level,
            'introduce'=>$introduce,
            'zpurchaseNum'=>$zpurchaseNum,
            'issellpurchaseNum'=>$issellpurchaseNum
        ];
        
        $MutualAid = new MMutualAid();
        $mutual_info = $MutualAid->getInfo(['id'=>$mu_id]);
        if (!$mutual_info){
            return json(['code' =>2,'msg' => '该产品不存在']);
        }else{
            return $MutualAid->editMutualaid($mu_id, $mutual_info, $data);
        }
    }
    
    //删除产品
    public function mutualAidDelete(Request $request){
        $mu_id = intval($request->param('mu_id', 0)); // 产品id
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
    ////会员产品
    
    //会员产品列表
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
    
    //会员产品增加
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
            if($mutual_info['price'] != $price){
                return json(['code' => 2, 'msg' => '价格不匹配']);
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
    //会员产品编辑渲染
    function mutualAidMemberEdit(Request $request){
        $id = $request->param('id', '');//请输入产品id
        if(empty($id)){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        $MMemberMutual = new MMemberMutual();
        $MMemberMutual_info = $MMemberMutual->getInfo(['id'=>$id]);
        $this->assign('info',$MMemberMutual_info);
        
        return view();
    }
    
    //会员产品编辑
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
    
    //会员产品删除
    public function mutualaidmemberdelete(Request $request){
        $mu_id = intval($request->param('mu_id', 0)); // 订单id
        if($mu_id == 0){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        $MMemberMutual = new MMemberMutual();
        
        
        try {
            Db::startTrans();
            
            $MMemberMutual->where('id', $mu_id)->update(['is_exist'=>0]);
            
            Db::name('mutualaid_order')->where('p_id',$mu_id)->update(['is_exist'=>0]);

            Db::commit();
            return json(['code' => 1, 'msg' => '操作成功']);
        } catch (Exception $e) {
            Db::rollback();
            return json(['code' => 2, 'msg' => '操作失败'.$e->getMessage()]);
        }
    }
    
    //宠物拆分
    public function mutualaidMemberSplit(Request $request){
        $id = intval($request->param('id', 0)); // 订单id
        if($id == 0){
            return json(['code' => 2, 'msg' => '参数错误']);
        }
        $MMemberMutual = new MMemberMutual();
        $info = $MMemberMutual->getInfo(['id'=>$id]);
        $this->assign('row',$info);
        $MMutualAid = new MMutualAid();
        $list = $MMutualAid->getList('','id,name,min_price,max_price,sta_time,end_time');
        $now_day = strtotime(date('Y-m-d', time()));
        foreach ($list as $k => $v) {
            $list[$k]['time'] = date('H:i', ($v['sta_time'] + $now_day)) . ' - ' . date('H:i', ($v['end_time'] + $now_day));
        }
        $this->assign('list',$list);
        $this->assign('id',$id);
        return view();
    }
    //宠物拆分提交
    public function mutualaidMemberSplitPost(Request $request){
        $data = $request->param();
        
        if ($data['numA'] == '' && $data['numB'] == '' && $data['numC'] == '') {
            return json(['code' => 2, 'msg' => '无拆分']);
        }
        $id = $data['id'];
        $MMemberMutual = new MMemberMutual();
        $info = $MMemberMutual->getInfo(['id'=>$id]);
        $this->assign('row',$info);
        
        $MMutualAid = new MMutualAid();
        $list = $MMutualAid->getList('','id,name,min_price,max_price,sta_time,end_time');
        
        if ($info['status'] != 2) return json(['code' => 2, 'msg' => '只能够拆分待转让宠物']);
        
        $listArr = array_column($list, 'id');
        $allPrice = 0;
        try {
            Db::startTrans();
            Db::name('member_mutualaid')->where('id',$id)->update(['status' => 5,'is_exist' => 0]);
            Db::name('mutualaid_order')->where('orderNo',$info['orderNo'])->update(['is_exist' => 0]);
            if (intval($data['numA']) > 0) {//如果
                if (!in_array($data['purchaseListA'], $listArr))  return json(['code' => 2, 'msg' => '请选择拆分A宠物种类']);
                if (floatval($data['priceA']) <= 0)  return json(['code' => 2, 'msg' => '请输入拆分A宠物价格']);
                $allPrice += $data['priceA'] * $data['numA'];
                $this->splitDeal($id,$data['purchaseListA'],$data['numA'],$data['priceA']);
            }
            if (intval($data['numB']) > 0) {//如果
                if (!in_array($data['purchaseListB'], $listArr))  return json(['code' => 2, 'msg' => '请选择拆分B宠物种类']);
                if (floatval($data['priceB']) <= 0)  return json(['code' => 2, 'msg' => '请输入拆分B宠物价格']);
                $allPrice += $data['priceB'] * $data['numB'];
                $this->splitDeal($id,$data['purchaseListB'],$data['numB'],$data['priceB']);
            }
            if (intval($data['numC']) > 0) {//如果
                if (!in_array($data['purchaseListC'], $listArr))  return json(['code' => 2, 'msg' => '请选择拆分C宠物种类']);
                if (floatval($data['priceC']) <= 0)  return json(['code' => 2, 'msg' => '请输入拆分C宠物价格']);
                $allPrice += $data['priceC'] * $data['numC'];
                $this->splitDeal($id,$data['purchaseListC'],$data['numC'],$data['priceC']);
            }
            if (strval($allPrice) != strval($info['new_price']))  return json(['code' => 2, 'msg' => '拆分后价格不等于原价格']);
            Db::commit();
            return json(['code' => 1, 'msg' => '拆分成功']);
        } catch (Exception $e) {
            Db::rollback();
            return json(['code' => 2, 'msg' => '拆分失败'.$e->getMessage()]);
        }
        
    }
    
    
    public function splitDeal($id, $purchase_id, $num, $price)
    {
        $info = Db::name('member_mutualaid')->where('id', $id)->find();//需要拆的数据
        $purchase = Db::name('mutualaid_list')->where('id', $purchase_id)->find();//拆成的信息
        for ($i = 0; $i < $num; $i++){
            $orderNo = $this->makeRand();
            $purchase_no = $this->makeRandCW();
            $insertId = Db::name('member_mutualaid')->insertGetId([
                'orderNo' => $orderNo,
                'uid' => $info['uid'],
                'tel' => $info['tel'],
                'purchase_id' => $purchase_id,
                'purchase_no'=> $purchase_no,
                'get_price' => $price,
                'new_price' => $price,
                'rate' => $purchase['rate'],
                'deal_type' => 7,
                'status' => 2,
                'sta_time' => time(),
                'days' => $purchase['days']
            ]);
            Db::name('mutualaid_order')->insert([
                'p_id' => $insertId,
                'purchase_id' => $purchase_id,
                'purchase_no'=> $purchase_no,
                'orderNo' => $orderNo,
                'sell_uid' => $info['uid'],
                'sell_user' => $info['tel'],
                'price' => $price,
                'status' => 0,
                'create_time' => time()
            ]);
        }
    }
    
    
    
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////会员产品
    
    public function mutualAidBookedList(Request $request){
        $status = $request->param('status', -1); // 匹配状态
        $pur_status = $request->param('pur_status', -1); // 抢购状态
        $order_status = $request->param('order_status', -1); // 预约状态
        $serach = $request->param('serach', ''); // 关键字搜索 账号 预约金额
        $name = $request->param('name', ''); //宠物
        $add_time_s = $request->param('add_time_s', '');
        $add_time_e = $request->param('add_time_e', '');
        $allParams = ['query' => $request->param()];
        $this->assign('param_status', $status);
        $this->assign('param_purstatus', $pur_status);
        $this->assign('param_orderstatus', $order_status);
        $this->assign('param_serach', $serach);
        $this->assign('param_name', $name);
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
        
        $pageSize = 10; // 分页大小
        $where = '1 = 1'; // 初始查询条件
        if($status != -1){
            $where .= ' and a.`status` = '.$status;
        }
        if($pur_status != -1){
            $where .= ' and a.`purchase_status` = '.$pur_status;
        }
        if($order_status != -1){
            $where .= ' and a.`order_status` = '.$order_status;
        }
        if($serach != ''){
            $where .= ' and a.`tel` like \'%'.$serach.'%\' OR a.`num` like \'%'.$serach.'%\'  OR a.`purchase_no` like \'%'.$serach.'%\'';
        }
        if($name != ''){
            $where .= ' and a.`p_id` = '.$name;
        }
        if ($add_time_s != '') {
            $where .= " and a.`time` >= " . strtotime($add_time_s);
        }
        if ($add_time_e != '') {
            $where .= " and a.`time` <= " . strtotime($add_time_e);
        }
        
        $MMutualAidLog = new MMutualAidLog();
        $list = $MMutualAidLog->getlists($where, $pageSize, $allParams);
        $this->assign('list',$list);
        
        $MMutualAid = new MMutualAid();
        $info = $MMutualAid->getList('','id,name','id desc');
        $this->assign('info',$info);
        return view();
    }
    
    
    
    /**有效会员升级
     * @throws Exception
     */
    public function upEffective($uid)
    {
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig('effectiveUserAssets');
        $site_asstes = $config_val;//Config::get('site.effectiveUserAssets');
        $level = Db::name('member_level')->order('id desc')->select();
        $UpLevel = new \app\api\model\MMember();
        //升级有效用户Db::name('user')
        $user = $UpLevel->where('id', $uid)->field('is_effective,f_uid,f_uid_all,pets_assets')->find();
        if ($user['is_effective'] == 0) {//第一次升级有效会员
            $assets = Db::name('member_mutualaid')->where('uid =' . $uid . ' and status in (1,2,3)')->sum('new_price');
            if ($assets >= $site_asstes) {//$user['pets_assets']
                $UpLevel->where('id', $uid)->setField('is_effective', 1);
                if ($user['f_uid'] != 0) {
                    //团队 及直推
                    $UpLevel->where('id', $user['f_uid'])->setInc('zt_yx_num');
                    $UpLevel->where('id in (' . $user['f_uid_all'] . ')')->setInc('yx_team');
                    $team_list = $UpLevel->where('id in ('.$user['f_uid_all'].')')
                    ->field('id,level,pets_assets_history,zt_yx_num,yx_team,level')->select();
                    foreach ($team_list as $k => $v) {//判断
                        $UpLevel->upLevel($level, $v['id'], $v['level'], $v['pets_assets_history'], $v['zt_yx_num'], $v['yx_team']);
                    }
                }
            }
        }
    }
    
    
        
    
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////产品续期
    
    public function mutualAidExamine(Request $request){
        //$serach = $request->param('serach', ''); // 关键字搜索 账号
        $name = $request->param('name', ''); //宠物
        $add_time_s = $request->param('add_time_s', '');
        $add_time_e = $request->param('add_time_e', '');
        $allParams = ['query' => $request->param()];
        //$this->assign('param_serach', $serach);
        $this->assign('param_name', $name);
        $this->assign('param_add_time_s', $add_time_s);
        $this->assign('param_add_time_e', $add_time_e);
                
        $MMutualAid = new MMutualAid();
        
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

        $info = $MMutualAid->getList('','id,name','id desc');
        $this->assign('info',$info);
        return view();
    }
    
    
    
        
    // 续约 通过/拒绝
    public function mutualAidExamineAgree(Request $request)
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

        $mo_info = Db::name('mutualaid_order')->where(['id'=>$info['order_id']])->field('p_id')->find();
        
        $MMutualAid = new MMutualAid();
        $ml_info = $MMutualAid->getinfo(['id'=>$info['p_id']],'days');
        
        if($status == 2){
            
            Db::name('mutualaid_order')->where(['id'=>$info['order_id']])->update(['status'=>3,'is_exist'=>1]);
            
            Db::name('member_mutualaid')->where(['id'=>$mo_info['p_id']])->update([
                'days'   => Db::raw('days +'.$ml_info['days']),
                'deal_type' => 1,
                'status' => 1
            ]);
            
            
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

