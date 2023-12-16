<?php
namespace app\api\controller;
use think\Db;
use app\api\model\MConfig;

class Share extends Common
{
    /** 分享页面
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
/*     public function shareIndex()
    {
        $data['notice'] = Db::name('cms')->whereIn('id', [4, 5])->order('id desc')->field('content')->select();
        $data['user']['zt_yx_num'] = $this->auth->zt_yx_num;
        $data['user']['zt_num'] = $this->auth->zt_num - $data['user']['zt_yx_num'];
        $data['user']['wx_num'] = $data['user']['zt_num'];
        $data['user']['team'] = $this->auth->yx_team;
        $data['user']['guid'] = $this->auth->guid;
        $this->success('ok', $data);
    }
    
    public function levelIndex()
    {
        $level = Db::name('level')->field('id,logo,zt_num,team_num,assets')->order('id desc')->select();
        foreach ($level as $k => $v) {
            $level[$k]['logo'] = 'http://' . $_SERVER['HTTP_HOST'] . $v['logo'];
            $level[$k]['status'] = $this->auth->level >= $v['id'] ? 1 : 0;
        }
        $this->success('ok', $level);
    } */
    
    /**
     *  图片渲染
     */
    public function shareImg()
    {   
        $user_id = $this->userinfo['user_id'];
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig('shareUrl');
        $guid = Db::name('member_list')->where('id',$user_id)->field('guid,status')->find();
        //$guid = $this->userinfo['guid'];
        if($guid['status'] != 2){
            return json(['code' => 2,'msg' => '请先激活账号']);
        }
        $data['guid'] = $guid['guid'];
        $data['img_url'] = $config_val .'?code='. $guid['guid'];
        
        return json(['code' => 1,'msg' => 'success', 'data'=>$data]);
    }
    
    //升级
    public function versionUp()
    {
        $MConfig = new MConfig();
        $config_val = $MConfig->readConfig('versionCheck,uploadUrl');
        $data['app_v'] = $config_val[0];
        $data['app_wgt_url'] = $config_val[1];

        return json(['code' => 1,'msg' => 'success', 'data'=>$data]);
    }
    
}

