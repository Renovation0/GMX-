<?php
namespace app\admin\controller;

use think\Request;
use app\admin\model\MMember;
use think\Db;
use app\admin\model\MMemberMutual;

class Term extends Check
{     
    public function memberTeamLog(Request $request){
        $tel = $request->param('tel', 0);
        $this->assign('phone', $tel);
        return view();
    }
  
    public function memberteam(Request $request){
        $MMember = new MMember();
        $MMemberMutual = new MMemberMutual();
        //Db::name('member_list')
        //Db::name('member_mutualaid')
        $id = $request->param('id', '');
        $tel = $request->param('tel', '');
//         var_dump($id);
//         var_dump($tel);
        if(empty($id)){
            $id = 0;
        }
        if($tel != ''){
            $member_list = Db::name('member_list')->cache(300)->where(['tel'=>$tel])->field('id,user,tel,yx_team,f_uid')->select();
        }else{
            $member_list = Db::name('member_list')->cache(300)->where('f_uid = '.$id)->order('id')->field('id,user,tel,yx_team,f_uid')->select();
        }
        //$member_list = Db::name('member_list')->where('f_uid = '.$id)->order('id')->field('id,user,tel,yx_team,f_uid')->select();
        
        foreach ($member_list as $k => $v){
            //个人资产
            $personAssets = $MMemberMutual->cache(300)->where('uid ='.$v['id'].' and status in (1,2,3) and is_exist = 1')->sum('new_price');
            
            $arr_user = $MMember->cache(300)->where('FIND_IN_SET(:id,f_uid_all)',['id' => $v['id']])->column('id');
            //团队资产
            $teamAssets = $MMemberMutual->cache(300)->whereIn('uid',$arr_user)->where('status in (1,2,3) and is_exist = 1')->sum('new_price');
            
            //今日团队资产
            $teamAssetsToday = $MMemberMutual->cache(300)->whereTime('sta_time','today')->whereIn('uid',$arr_user)->where('status in (1,2,3) and is_exist = 1')->sum('new_price');
            
            //个人总收益
            $allReward = Db::name('member_balance_log')->cache(300)->where('type in (4,5,6,8) and u_id=' . $v['id'])->sum('change_money');
            
            //团队总收益
            $teamallReward = Db::name('member_balance_log')->cache(300)->where('type in (4,5,6,8)')->whereIn('u_id',$arr_user)->sum('change_money');
            //$member_list[$k]['text']=trim('Id:'.$v['id'].'-名称:'.$v['user'].'(手机号：'.$v['tel'].'; 有效团队：'.$v['yx_team'].'; 个人资产：'.$personAssets.'; 团队资产：'.$teamAssets.'; 今日团队资产：'.$teamAssetsToday.'');
            //$member_list[$k]['parent']=!empty($v['f_uid'])?$v['f_uid']:'#';
            //判断是否是根
            /* if(empty($v['f_uid'])){
                $member_list[$k]['type']='root';
            } */
            //判断是否有子
            $children=Db::name('member_list')->cache(300)->where('f_uid',$v['id'])->field('id,user,f_uid')->select();
            if(!empty($children)){
                $member_list[$k]['children']=true;
            }
            
            $member_list[$k]['personAssets']=$personAssets;
            $member_list[$k]['teamAssets']=$teamAssets;
            $member_list[$k]['teamAssetsToday']=$teamAssetsToday;
            $member_list[$k]['allReward']=$allReward;
            $member_list[$k]['teamallReward']=$teamallReward;
        }
        return $member_list;

    }
    
    
    
    //查询
    public function term_tree(Request $request){
        $MMember = new MMember();
        $MMemberMutual = new MMemberMutual();
        //Db::name('member_list')
        //Db::name('member_mutualaid')
        
        //'f_uid = 0'
        
/*         $member_list = Db::name('member_list')->where('1=1')->order('id')->field('id,user,tel,yx_team,f_uid')->select();
        
          foreach ($member_list as $k => $v){
              $member_list[$k]['pid']=$v['f_uid'];
          } */
            //个人资产
            //$personAssets = $MMemberMutual->where('uid ='.$v['id'].' and status in (1,2,3) and is_exist = 1')->sum('new_price');
            //$member_list[$k]['text']=trim('Id:'.$v['id'].'-名称:'.$v['user'].'(手机号：<div style = "color:blue;display:inline-block;">'.$v['tel'].'</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;有效团队：<div style = "color:red;display:inline-block;">'.$v['yx_team'].'</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;个人资产：<div style = "color:violet;display:inline-block;">0</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;团队资产：0 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 今日团队资产：<div style = "color:green;display:inline-block;">0</div>)');
            //$arr_user = $MMember->where('FIND_IN_SET(:id,f_uid_all)',['id' => $v['id']])->column('id');
            //团队资产
            //$teamAssets = $MMemberMutual->whereIn('uid',$arr_user)->where('status in (1,2,3) and is_exist = 1')->sum('new_price');
            //今日团队资产
            //$teamAssetsToday = $MMemberMutual->whereTime('sta_time','today')->whereIn('uid',$arr_user)->where('status in (1,2,3) and is_exist = 1')->sum('new_price');
            
//            $member_list[$k]['text']=trim('Id:'.$v['id'].'-名称:'.$v['user'].'(手机号：<div style = "color:blue;display:inline-block;">'.$v['tel'].'</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;有效团队：<div style = "color:red;display:inline-block;">'.$v['yx_team'].'</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;个人资产：<div style = "color:violet;display:inline-block;">'.$personAssets.'</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;团队资产：'.$teamAssets.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 今日团队资产：<div style = "color:green;display:inline-block;">'.$teamAssetsToday.'</div>)');
            //$member_list[$k]['parent']=!empty($v['f_uid'])?$v['f_uid']:'#';
            
            /* //判断是否是根
            if(empty($v['f_uid'])){
                $member_list[$k]['type']='root';
            }*/
            //判断是否有子
/*             $children=$MMember->where('f_uid',$v['id'])->field('id,user,f_uid')->select();
            if(!empty($children)){
                $member_list[$k]['children']=true;
            }
        } */
        
        
        
/*         return $member_list;  */
        

        $id = $request->param('id', '');

        $member_list = Db::name('member_list')->where('f_uid = '.$id)->order('id')->field('id,user,tel,yx_team,f_uid')->select();
        
/*         $member_list = Db::name('member_list')
        ->where('f_uid',$id)
        ->field('id,user,f_uid,tel,yx_team')
        // ->order($sort, $order)
        ->order('id')
        ->select(); */
         
/*         $arr = [
            'id'=>3965,
            'user'=>123123,
            'f_uid'=>0,
            'tel'=>13800138000,
            'yx_team'=>123,
            'text'=>'123123',
            'isParent'=>false
        ];
        $arr1 = [
            'id'=>3999,
            'user'=>123123,
            'f_uid'=>3970,
            'tel'=>13800138000,
            'yx_team'=>123,
            'text'=>'11111111111111111111'
        ];
        $arr2 = [
            'id'=>3997,
            'user'=>123123,
            'f_uid'=>3970,
            'tel'=>13800138000,
            'yx_team'=>123,
            'text'=>'22222222222'
        ];
        $arr3 = [
            'id'=>3998,
            'user'=>123123,
            'f_uid'=>0,
            'tel'=>13800138000,
            'yx_team'=>123,
            'text'=>'333333333333',
            'isParent'=>true,
        ];
 */
        foreach ($member_list as $k => $v){
            //个人资产
            $personAssets = $MMemberMutual->where('uid ='.$v['id'].' and status in (1,2,3) and is_exist = 1')->sum('new_price');
            
            $arr_user = $MMember->where('FIND_IN_SET(:id,f_uid_all)',['id' => $v['id']])->column('id');
            //团队资产
            $teamAssets = $MMemberMutual->whereIn('uid',$arr_user)->where('status in (1,2,3) and is_exist = 1')->sum('new_price');
            //今日团队资产
            $teamAssetsToday = $MMemberMutual->whereTime('sta_time','today')->whereIn('uid',$arr_user)->where('status in (1,2,3) and is_exist = 1')->sum('new_price');
            
            //$member_list[$k]['text']=trim('Id:'.$v['id'].'-名称:'.$v['user'].'(手机号：<div style = "color:blue;display:inline-block;">'.$v['tel'].'</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;有效团队：<div style = "color:red;display:inline-block;">'.$v['yx_team'].'</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;个人资产：<div style = "color:violet;display:inline-block;">'.$personAssets.'</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;团队资产：'.$teamAssets.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 今日团队资产：<div style = "color:green;display:inline-block;">'.$teamAssetsToday.'</div>)');
            $member_list[$k]['text']=trim('Id:'.$v['id'].'-名称:'.$v['user'].'(手机号：'.$v['tel'].' ;有效团队：'.$v['yx_team'].';个人资产：'.$personAssets.';团队资产：'.$teamAssets.'; 今日团队资产：'.$teamAssetsToday.')');
            $member_list[$k]['parent']=!empty($v['f_uid'])?$v['f_uid']:'#';
            //$member_list[$k]['pid']=$v['f_uid'];
            //判断是否是根
            if(empty($v['f_uid'])){
                $member_list[$k]['type']='root';
            }
            //判断是否有子
            $children=$MMember->where('f_uid',$v['id'])->field('id,user,f_uid')->select();
            if(!empty($children)){
                $member_list[$k]['isParent']=true;
            }
        }
/*         $member_list[]=$arr;
        $member_list[]=$arr1;
        $member_list[]=$arr2;
        $member_list[]=$arr3;  */

        return $member_list;    
    }


}

