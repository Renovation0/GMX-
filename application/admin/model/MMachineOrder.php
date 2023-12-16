<?php
namespace app\admin\model;

use module\Redis;

class MMachineOrder extends MCommon
{
    
    public $table = "zm_machine_order";
    
    // 获取矿机购买列表
    public function getBuyList($where, $pageSize, $allParams)
    {
        $list = $this->alias('order')
        ->field('order.*,main.name,main.price,main.hour_output')
        ->leftJoin('machine_manage main','order.mac_id = main.id')
        //->field('main.name,main.hour_output,main.cycle,order.cycle,c.id')
        ->where($where)
        ->order('order.id desc')
        ->paginate($pageSize, false, $allParams);
        
        return $list;
    }
    
    // 获取会员引擎列表
    public function getLists($where, $pageSize, $allParams, $orders)
    {   
        $MMember = new MMember();
        $MMachineManage = new MMachineManage();
        
        $list = $this->where($where)
        ->order($orders)
        ->paginate($pageSize, false, $allParams);
        //用户手机号
        $user_id = array_column($list->items(), 'u_id');
        $mac_id = array_column($list->items(), 'mac_id');
        $mac_ids = implode(",", $mac_id);
        $user_ids = implode(",", $user_id);
        $members = array();
        $macs = array();
        if ($user_ids) {
            //$user = DB::name('member_list')
            $user = $MMember->field('id,tel')->whereIn('id', $user_ids)->select();
            foreach ($user as $key => $val) {
                $members[$val['id']] = $val;
            }
        }
        
        if ($mac_ids){
            //$mac = DB::name('machine_manage')
            $mac = $MMachineManage->field('id,name')->whereIn('id', $mac_ids)->select();
            foreach ($mac as $key => $val) {
                $macs[$val['id']] = $val;
            }
        }
        $resut = array(
            'list' => $list,
            'member' => $members,
            'mac' => $macs
        );
        return $resut;
    }
    
        
    //赠送引擎
    public function sendMarchine($uid,$id,$num){
        $MMachineManage = new MMachineManage();
        $machine = $MMachineManage->getInfo(['id'=>$id]);
        $jishi = 0;
        $data = [
            'mac_id' => $machine['id'],
            'u_id' => $uid,
            'num' => 0,
            'hours' => 0,
            's_cycle' => $machine['cycle'],
            'cycle' => $machine['cycle'],
            'hour_output' => $machine['hour_output'],
            'output' => 0,
            'r_output' => $machine['hour_output']*$machine['cycle'],
            'time' => time(),
            'e_time' => time() + $machine['cycle'] * 86400,
            'status' => 1
        ];
        for ($i=0;$i<$num;$i++){
            $this->insert($data);
            $jishi ++;
        }
        if ($jishi == $num){
            return true;
        }else{
            return false;
        }
    }

    //矿机统计
    public function macCensus(){
        $Redis = new Redis();
        $redis = $Redis->redis();
        //矿机总数
        $mac_num = $this->count();
        $redis->hSet('census','mac_num',$mac_num);
        //已过期总数
        $mac_pass_num = $this->where('status = 2')->count();
        $redis->hSet('census','mac_pass_num',$mac_pass_num);
        //赠送矿机总数
        $mac_give_num = $this->where('is_giving = 1')->count();
        $redis->hSet('census','mac_give_num',$mac_give_num);
        //矿机总产量
        //$mac_yield_num = $this->sum('output');
        $r_output = $this->where('status = 1')->sum('r_output');
        $output = $this->where('status = 1')->sum('output');
        $mac_yield_num = $r_output+$output;
        $redis->hSet('census','mac_yield_num',$mac_yield_num);
        
        $beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $beginYesterday = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
        $endYesterday = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
        //昨日产量
        $mac_yesterday_num = $this->where("last_time > ".$beginYesterday." AND last_time < ".$endYesterday)->sum('hour_output');
        $redis->hSet('census','mac_yesterday_num',$mac_yesterday_num);
        //今日产量
        $mac_today_num = $this->where("last_time > ".$beginToday." AND last_time < ".$endToday)->sum('hour_output');
        $redis->hSet('census','mac_today_num',$mac_today_num);

        $redis->hSet('census','mac_refresh_time',time());
        return true;
    }
    
}

