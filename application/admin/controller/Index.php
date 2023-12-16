<?php
namespace app\admin\controller;

use app\admin\model\SystemMenu;
use think\facade\Session;
use app\admin\model\SystemConfig;
use app\admin\model\MMember;
use think\Db;
use think\Request;
use app\admin\model\MMutualAid;
use app\admin\model\MMutualAidOrder;
use app\admin\model\MMutualAidLog;
use app\admin\model\MMemberMutual;
use app\api\model\MConfig;

class Index extends Check
{
    // 后台框架渲染
    public function index()
    {
        // 读取菜单
//        if(Session::has('menus') && Session::get('menus') != false){
//            $menus = Session::get('menus');
//        }else{
            $systemMenuModel = new SystemMenu();
            $menus = $systemMenuModel->readMenus();
            //Session::set('menus', $menus);
//        }

        $M_SystemConfig = new SystemConfig();
        $title = $M_SystemConfig->where(['key'=>'website'])->field('value')->find();
        $this->assign('log_title',$title['value']);
            
        $username = Session::get('user.username');
        $this->assign('menus', $menus);
        $this->assign('username', $username);
        return view('indexs');
    }

    // 后台欢迎页渲染
    public function welcome(Request $request)
    {
        $username = Session::get('user.username');
        date_default_timezone_set('Asia/Calcutta');
        $now_time = date('Y-m-d H:i:s', (time()));
        $this->assign('username', $username);
        $this->assign('now_time', $now_time);
        $my_active_module = intval($request->param('my_active_module', 1)); //
        $this->assign('my_active_module', $my_active_module);
                
        $MMember = new MMember();
        $member_count = $MMember->memberCensus();
        
        $beginToday = getIndaiTime(mktime(0,0,0,date('m'),date('d'),date('Y')));
        $endToday = getIndaiTime(mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1);
        
        $pets_assets      = Db::name('member_mutualaid')->where('is_exist = 1 and status in (1,2,3)')->sum('new_price');//用户宠物资产
        $alluser_purchase   = Db::name('member_mutualaid')->count();//宠物订单总数
        $shialluser_purchase      = Db::name('member_mutualaid')->where('is_exist',0)->count();//失效宠物订单总数
        $totalpurchase       = Db::name('member_mutualaid')->where('is_exist = 1 and status = 1 and sta_time >= '.$beginToday.' AND sta_time <= '.$endToday)->count();//今日新增宠物数
        $hadtotalpurchase      = Db::name('member_mutualaid')->where('is_exist = 1 and status = 4')->count();//转让后总宠物数
        $totalorder =  Db::name('member_mutualaid')->where('is_exist = 1 and status = 1')->count();//总待交易订单
        //$totalordersuccess =  Db::name('mutualaid_order')->where('status = 3')->count();//交易成功订单
        $totalorderprice   = Db::name('member_mutualaid')->where('is_exist = 1 and status in (1,2,3)')->sum('new_price');//宠物资产和
        $jytotalorder   = Db::name('order_coin')->where('status != 7')->count('num');//交易所单量
        $jytotalnum   = Db::name('order_coin')->where('status = 3')->sum('num');//交易所单量
        $jhbtotalnum   = Db::name('jxb_log')->where('is_look = 1')->sum('num');//积分总充值
        $syschangenum   = Db::name('member_balance_log')->where('status = 90 and bo_time >= '.$beginToday.' AND bo_time <= '.$endToday)->sum('change_money');//充值金额
        //$syswithdrawnum   = Db::name('member_balance_log')->where('status = 91 and bo_time >= '.$beginToday.' AND bo_time <= '.$endToday)->sum('change_money');//提现金额
        $syswithdrawnum   = Db::name('member_bm_withdraw')->where('status = 1 and update_time >= '.$beginToday.' AND update_time <= '.$endToday)->sum('num');//提现金额

        $syschangecount   = Db::name('member_balance_log')->where('type = 2 AND status = 100')->count('change_money');//后台充值狗粮次数
        $syschangecountsy   = Db::name('member_balance_log')->where('type in(5,6) AND status = 100')->count('change_money');//后台充值收益数量
        $syschangenumsy   = Db::name('member_balance_log')->where('type in(5,6) AND status = 100')->sum('change_money');//后台充值收益次数
        
        $firstrechargenum   = Db::name('member_bm_recharge')->whereTime('update_time', 'today')->where('status = 1 and isfirstrecharge=1')->count('num');//后台当天首次冲至
        // var_dump(Db::getlastsql());die();
        $member_counts = [
            'pets_assets'=>$pets_assets,
            'alluser_purchase'=>$alluser_purchase,
            'shialluser_purchase'=>$shialluser_purchase,
            'totalpurchase'=>$totalpurchase,
            'hadtotalpurchase'=>$hadtotalpurchase,
            'totalorder'=>$totalorder,
            'totalorderprice'=>$totalorderprice,
            'jytotalorder'=>$jytotalorder,
            //'jytotalnum'=>$jytotalnum,
            'jhbtotalnum'=>$jhbtotalnum,
            'syschangenum'=>$syschangenum,
            'syswithdrawnum'=>$syswithdrawnum,
            'syschangecount'=>$syschangecount,
            'syschangecountsy'=>$syschangecountsy,
            'syschangenumsy'=>$syschangenumsy,
            'firstrechargenum'=>$firstrechargenum
        ];
        
        $member_count = array_merge($member_count,$member_counts);

        $this->assign('member',$member_count); 
        
        $MConfig = new MConfig();
        $MConfig_val = $MConfig->readConfig(['mainCurrency','auxiliaryCurrency'],2);
        $this->assign('config_val',$MConfig_val);
        
        $mutu_list = Db::name('mutualaid_list')->where('status=1')->select();
        $MMutualAidOrder = new MMutualAidOrder();
        $MMutualAidLog = new MMutualAidLog();
        $MMemberMutual = new MMemberMutual();
        
        foreach ($mutu_list as $k => $v){
            //升值中  status=1
            $mutu_list[$k]['revalue_in'] = $MMemberMutual->getCount(['status'=>1,'purchase_id'=>$v['id'],'is_exist'=>1]);
            //待转让  status=2
            $mutu_list[$k]['holdon_transfer'] = $MMemberMutual->getCount(['status'=>2,'purchase_id'=>$v['id'],'is_exist'=>1]);
            //已失效
            $mutu_list[$k]['invalid'] = $MMemberMutual->getCount(['is_exist'=>0,'purchase_id'=>$v['id']]);
            //今日预约
            $mutu_list[$k]['today_reserve'] = Db::name('mutualaid_log')->whereTime('time', 'today')->where('order_status = 1 AND p_id='.$v['id'])->count();
            //今日抢购
            $mutu_list[$k]['today_snapup'] = Db::name('mutualaid_log')->whereTime('time', 'today')->where('purchase_status = 1 AND p_id='.$v['id'])->count();
            //今日转让成功
            $mutu_list[$k]['transfer_CG'] = Db::name('mutualaid_order')->whereTime('end_time', 'today')->where('status = 3 AND purchase_id ='.$v['id'])->count();
            //今日新增
            $mutu_list[$k]['newly_added'] = Db::name('mutualaid_order')->whereTime('create_time', 'today')->where('status = 3 AND purchase_id ='.$v['id'])->count();
            //今日待上架统计                                                                                                               ->whereTime('create_time', 'today')
            $mutu_list[$k]['holdon_shelves'] = Db::name('mutualaid_order')->where('status = 9 AND purchase_id ='.$v['id'])->count();
            //今日指定转让成功
            //$mutu_list[$k]['appoint_CG'] = Db::name('designated_transfer')->whereTime('time', 'today')->where('status = 3 AND is_appoint = 1 AND purchase_id ='.$v['id'])->count();            
            $mutu_list[$k]['appoint_CG'] = Db::name('designated_transfer')->alias('a')
            ->join('mutualaid_order b','a.p_id = b.id','left')
            ->whereTime('a.time', 'today')
            ->where('a.status = 1 AND b.status = 3 AND b.purchase_id ='.$v['id'])
            ->count();
            
            //总价值
            $mutu_list[$k]['total_value'] = Db::name('member_mutualaid')->where('is_exist = 1 AND purchase_id ='.$v['id'])->sum('new_price');
                        
        }
        
        $this->assign('mutu_list',$mutu_list); 
        return view();
    }
}
