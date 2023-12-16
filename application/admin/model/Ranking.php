<?php


namespace app\admin\model;


use think\Model;
use think\Db;
class Ranking extends Model
{
    public function getLists(){
        $star_time = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 - 7, date('Y'));
        $end_time = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 , date('Y'));
        
        $lists=Db::name('ranking')
        ->where('time', '>', $star_time)
        ->where('time', '<', $end_time)
        ->select();
        if(!$lists){
            $this->getListInCensus();
            
            $this->getListInCensusBuy();
        }
        $lists=Db::name('ranking')
         ->where('time', '>', $star_time)
         ->where('time', '<', $end_time)
         ->select();
        //print_r($lists);exit;
        return $lists;
/*         return $this->alias('r')
        ->join('member_list m','r.u_id = m.id', 'left')
        ->join('member_list m_show','r.u_id_show = m_show.id', 'left')
        ->field('r.*, m.tel, m_show.tel as tel_show')
        ->where('r.time', '>', $star_time)
        ->order('r.sort desc')->select(); */
    }
    
    //检测是否已发放后仍修改用户
    public function is_send_u_id($where_is_send){
        return $this->where($where_is_send)->value('u_id_show');
    }
    //数据验证通过修改排行榜
    public function edit_pass($where_pass,$data){
        return $this->where($where_pass)
        ->update($data);
    }
    public function getListInCensus(){
        $star_time = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 - 7, date('Y'));
        $end_time = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 , date('Y'));
        //查询完成首单[认证有效用户]的排行基础数据
        $sql="SELECT count(1) as counts ,f_uid,time  FROM `zm_blood_log` WHERE `time` > '".$star_time."' and  `time` < '".$end_time."' group by f_uid  ORDER BY counts desc limit 10;";
        $lists=Db::query($sql);
        if($lists){
            //抽取id
            $uid_arr=array_column($lists, 'f_uid');
            //转为字符串 方便sql拼接
            $main_id = implode(",", $uid_arr);
            $main_id = $main_id ? $main_id : 0;
            $sql_user = "SELECT id,tel FROM  zm_member_list WHERE id in (" . $main_id . ")";
            $list_user=Db::query($sql_user);
            $users=array();
            //改变数据结构为 id为key tel为value
            foreach($list_user as $uk=>$uv){
                $users[$uv['id']]=$uv['tel'];
            }
            //重组数据
            $maxkey=count($lists) -1;
            $insert_string='';
            foreach($lists as $key=>$val){
                $lists[$key]['tel']=$users[$val['f_uid']];
                $val['tel']=$users[$val['f_uid']];
                $insert_string.="(".$val['f_uid'].",'".$val['tel']."',".$val['f_uid'].",'".$val['tel']."',".$val['counts'].",".$val['counts'].",".($key+1).",1,'".$val['time']."')";
                if($key==$maxkey){
                    if($maxkey < 9){
                        $insert_string.=",";
                        for($i=$maxkey+1;$i<=9;$i++){
                            $insert_string.="(0,0,0,0,0,0,".($i+1).",1,'".$val['time']."')";
                            if($i <= 8){
                                $insert_string.=",";
                            }
                        }
                    }
                    $insert_string.=';';
                }else{
                    $insert_string.=",";
                }
            }
            
            $sql_del="DELETE FROM zm_ranking ;";
            Db::execute($sql_del);
            $inser_sql="INSERT INTO zm_ranking (u_id,u_tel,u_id_show,u_tel_show,num,num_show,sort,type,time) VALUES ".$insert_string;
            Db::execute($inser_sql);
            //echo $inser_sql;exit;
        }
        return $lists;
    }
    
    
    public function getListInCensusBuy(){
        $star_time = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 - 7, date('Y'));
        $end_time = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 , date('Y'));
        //查询完成首单[认证有效用户]的排行基础数据
        $sql="SELECT sum(num) as counts ,buy_id,e_time  FROM `zm_order_list` WHERE `type` = 1 and  (status = 5 OR status = 13) and `e_time` > '".$star_time."' and  `e_time` < '".$end_time."' group by buy_id  ORDER BY counts desc limit 10;";
        $lists=Db::query($sql);
        if($lists){
            //抽取id
            $uid_arr=array_column($lists, 'buy_id');
            //转为字符串 方便sql拼接
            $main_id = implode(",", $uid_arr);
            $main_id = $main_id ? $main_id : 0;
            $sql_user = "SELECT id,tel FROM  zm_member_list WHERE id in (" . $main_id . ")";
            $list_user=Db::query($sql_user);
            $users=array();
            //改变数据结构为 id为key tel为value
            foreach($list_user as $uk=>$uv){
                $users[$uv['id']]=$uv['tel'];
            }
            //重组数据
            $maxkey=count($lists) -1;
            $insert_string='';
            foreach($lists as $key=>$val){
                $lists[$key]['tel']=$users[$val['buy_id']];
                $val['tel']=$users[$val['buy_id']];
                $insert_string.="(".$val['buy_id'].",'".$val['tel']."',".$val['buy_id'].",'".$val['tel']."',".$val['counts'].",".$val['counts'].",".($key+1).",2,'".$val['e_time']."')";
                if($key==$maxkey){
                    if($maxkey < 9){
                        $insert_string.=",";
                        for($i=$maxkey+1;$i<=9;$i++){
                            $insert_string.="(0,0,0,0,0,0,".($i+1).",2,'".$val['e_time']."')";
                            if($i <= 8){
                                $insert_string.=",";
                            }
                        }
                    }
                    $insert_string.=';';
                }else{
                    $insert_string.=",";
                }
            }
//             var_dump($insert_string);exit();
//             $sql_del="DELETE FROM zm_ranking ;";
//             Db::execute($sql_del);
            $inser_sql="INSERT INTO zm_ranking (u_id,u_tel,u_id_show,u_tel_show,num,num_show,sort,type,time) VALUES ".$insert_string;
            Db::execute($inser_sql);
            //echo $inser_sql;exit;
        }
        return $lists;
    }
    
    
}