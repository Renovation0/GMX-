<?php
namespace app\api\controller;
use think\Db;
use think\App;
use think\facade\Cache;
use think\facade\Session;
use think\facade\Request;
use app\api\model\MMember;
use app\api\model\MConfig;

class Common extends Base
{
    //管理员ID
    protected $user;
    //管理员基本信息
    protected $userinfo;
    // 当前模型
    protected $module = null;
    // 当前控制器
    protected $controller = null;
    // 当前方法
    protected $action = null;
    
    
    public function __construct(App $app = null)
    {
        header("Content-Type: text/html;charset=utf-8");
        parent::__construct($app);
        
        $this->module = \think\facade\Request::module();
        $this->controller = \think\facade\Request::controller();
        $this->action = \think\facade\Request::action();
        
        $this->init();
    }
    
    
    public function init()
    {
        $data = $this->request->param();
        //array_shift($data);
        
        $langer = $this->request->param('langer','EN');
        
        if (empty($data['sign']) || empty($data['appid'])){
            if($langer == 'EN'){
                json(['code' => 2,'data' =>[], 'msg' => getErrorInfo(MISS_FAIL)])->send();//'缺少必要参数'
                exit;
            }else{
                json(['code' => 2,'data' =>[], 'msg' => getErrorInfo(MISS_FAIL_IN)])->send();//'缺少必要参数'
                exit;
            }
        }
        
         $appinfo = Db::name('app_secret')->where('app_id',$data['appid'])->find();
        
         if(empty($appinfo)){
             if($langer == 'EN'){
                json(['code' => 2,'data' =>[], 'msg' => getErrorInfo(PARAMETER_ERROR)])->send();
                exit;
             }else{
                 json(['code' => 2,'data' =>[], 'msg' => getErrorInfo(PARAMETER_ERROR_IN)])->send();//'参数错误'
                 exit;
             }
        }
        
        $sign_str = $this->getSign($appinfo['secret'], $data);
        
        if($data['sign'] !== $sign_str){
            if($langer == 'EN'){
                json(['code' => 2,'data' =>[], 'msg' => getErrorInfo(PARAMETER_ERROR)])->send();
                exit;
            }else{
                json(['code' => 2,'data' =>[], 'msg' => getErrorInfo(PARAMETER_ERROR_IN)])->send();//'参数错误'
                exit;
            }
        }
        
        // 登陆检测
        if(empty($data['token'])){
            if($langer == 'EN'){
                json(['code' => 2,'data' =>[], 'msg' => getErrorInfo(MISS_FAIL)])->send();//'缺少必要参数'
                exit;
            }else{
                json(['code' => 2,'data' =>[], 'msg' => getErrorInfo(MISS_FAIL_IN)])->send();//'缺少必要参数'
                exit;
            }
        }
        
//         $a = $this->msectime();
//         $jk_time = sprintf('%.0f', $a/1000);
//         $time = time();
//         if($time - $jk_time > 1 || $jk_time - $time > 1){
//             json(['code' => SIGN_ERROR,'data' =>[], 'msg' => '请求超时'])->send();
//             exit;
//         }
        
        $login_info=Db::name('login_token')->field('exceed_time,use_id')->where(array('token'=>$data['token']))->find();
        if(!$login_info || $login_info['exceed_time']<time()){
            if($langer == 'EN'){
                json(['code' => 401,'data' =>[], 'msg' => getErrorInfo(LOGIN_AGAIN)])->send();
                exit;
            }else{
                json(['code' => 2,'data' =>[], 'msg' => getErrorInfo(LOGIN_AGAIN_IN)])->send();//'凭据已过期，请重新登录'
                exit;
            }
        }
        
        /* $login_list = Db::name('login_token')->field('token')->order('login_token_id desc')->where(array('use_id'=>$login_info['use_id']))->select();
        if($login_list[0]['token'] != $data['token']){
            json(['code' => 401,'data' =>[], 'msg' => '该账号已在其他设备登录，请重新登录或者修改密码'])->send();
            exit;
        } */
        
        $user_info=Db::name('member_list')->field('id as user_id,u_img,user,tel,status,real_name_status,balance,is_effective,first_blood,partner_time')->where(array('id'=>$login_info['use_id']))->find();        
        if(!$user_info){
            if($langer == 'EN'){
                json(['code' => 401,'data' =>[], 'msg' => getErrorInfo(LOGIN_AGAIN)])->send();
                exit;
            }else{
                json(['code' => 401,'data' =>[], 'msg' => getErrorInfo(LOGIN_AGAIN_IN)])->send();//'凭据已过期，请重新登录'
                exit;
            }
        }

        
//         $ACTIVATE_MIN = Db::name('system_config')->where(['key'=>'ACTIVATE_MIN'])->value('value');
//         if($user_info['balance'] >= $ACTIVATE_MIN && $user_info['status'] == 1){
//             Db::name('member_list')->where(['id'=>$user_info['user_id']])->update(['status'=>2]);            
//             if ($user_info['is_effective'] == 0) {
//                 $this->upEffective($user_info['user_id']);
//             }
//         }
        
//         $NUM_DAY = Db::name('system_config')->where(['key'=>'NUM_DAY'])->value('value');
        
//         if($NUM_DAY != 0){
//             $now_time = time();
//             $days = strtotime("-".$NUM_DAY." day");
            
//             if($user_info['first_blood'] == 2){
//                 $blood = intval(($now_time - $user_info['partner_time'])/86400);
//                 if($blood >= $days){
//                     $res_log = Db::name('mutualaid_log')->where('time >= '.$days.' AND time <= '.$now_time .' AND uid = '.$user_info['user_id'])->count();
//                     if(empty($res_log) || $res_log == 0){
//                         Db::name('member_list')->where(['id'=>$user_info['user_id']])->update(['status'=>3]);
//                         json(['code' => SIGN_ERROR, 'msg' => getErrorInfo(USER_FIAL),'data' =>[]])->send();
//                         exit();
//                     }
//                 }
                
//             }else{
//                 $res_log = Db::name('mutualaid_log')->where('time >= '.$days.' AND time <= '.$now_time .' AND uid = '.$user_info['user_id'])->count();
//                 if(empty($res_log) || $res_log == 0){
//                     Db::name('member_list')->where(['id'=>$user_info['user_id']])->update(['status'=>3]);
//                     json(['code' => SIGN_ERROR, 'msg' => getErrorInfo(USER_FIAL),'data' =>[]])->send();
//                     exit();
//                 }
//             }
//         }
        
        
        if($user_info['status'] == 3){
            if($langer == 'EN'){
                json(['code' => SIGN_ERROR, 'msg' => getErrorInfo(USER_FIAL),'data' =>[]])->send();
                exit();
            }else{
                json(['code' => SIGN_ERROR,'data' =>[], 'msg' => getErrorInfo(USER_FIAL_IN)])->send();//'用户名被禁用''
                exit;
            }
        }
        
/*      
        if (empty($data['user_id'])||$user_info['user_id']!=$data['user_id']){
            json(['code' => LOGIN_AGAIN,'data' =>[], 'msg' => '请求异常'])->send();
            exit;
        } */
        $this->userinfo = $user_info;
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
        $UpLevel = new MMember();//UpLevel();
        //升级有效用户Db::name('user')
        $user = $UpLevel->where('id', $uid)->field('is_effective,f_uid,f_uid_all,pets_assets')->find();
        if ($user['is_effective'] == 0) {//第一次升级有效会员
//             $assets = Db::name('member_mutualaid')->where('uid =' . $uid . ' and status in (1,2,3)')->sum('new_price');
//             if ($assets >= $site_asstes) {//$user['pets_assets']
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
//             }
        }
    }
    
    
}

