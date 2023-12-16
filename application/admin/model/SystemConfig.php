<?php

namespace app\admin\model;


use module\Redis;
use think\facade\Cache;
use think\Model;

class SystemConfig extends Model
{
    // 获取所有参数
    public function getBlock($Block)
    {
        $configs = $this->where('block',$Block)->where('is_hide',0)->select()->toArray();
        return $configs;
    }

    
    // 编辑参数
    public function editConfig($params)
    {
//         $configs = $this->getBlock($params[0]);
//         $redis = new Redis();
//         $redis = $redis->redis();
//         foreach ($configs as $config) {
//             if (isset($params[$config['key']])) {
//                 $this->where('key', $config['key'])->setField('value', $params[$config['key']]);
//                 $redis->hSet('config',$config['key'],$params[$config['key']]);
//             } else {
//                 if ($this->where('key', $config['key'])->value('type') == 4) {
//                     $this->where('key', $config['key'])->setField('value', 0);
//                     $redis->hSet('config',$config['key'],$params[$config['key']]);
//                 } else {
//                     $this->where('key', $config['key'])->setField('value', '');
//                 }
//             }
//         }
//        $all_config = $this->select()->toArray();
//        $con = array();
//        foreach ($all_config as $k=>$v){
//            $con[$v['key']][] = $v['value'];
//        }
//        $redis->set('config', json_encode($con));
        return true;
    }

    // 系统参数编辑提交
    public function configEdit(Request $request)
    {
        $params = $request->param();
        unset($params['/index/system/configEdit']);
        $systemConfigModel = new SystemConfig();
        if(!empty($params)){
            foreach ($params as $key => $param) {
                $systemConfigModel->where('key', $key)->setField('value', $param);
                $this->redis->hSet('system_config', $key, $param);
            }
            $this->success('修改成功');
        }else{
            $this->error('没有数据');
        }
    }

    // 获取单一参数
    public function getConfigByKey($key)
    {
        $redis = new Redis();
        $redis = $redis->redis();
//        $data = $redis->get('config');
//        $configs_arr = (array)json_decode($data);
//        if ($configs_arr && $configs_arr[$key]) {
//            $value = $configs_arr[$key];
//        } else {
//            $all_config = $this->select()->toArray();
//            $configs_arr = array();
//            foreach ($all_config as $k=>$v){
//                $configs_arr[$v['key']][] = $v['value'];
//            }
//            $redis->set('config', json_encode($configs_arr));
//            $value = $configs_arr[$key];
//        }
        if($redis->hExists('config',$key)){
            $value = $redis->hGet('config',$key);
        }else{
            $value = $this->where('key',$key)->value('value');
            if($value !== false){
                $redis->hSet('config',$key,$value);
            }
        }
        /*$value = $redis->hGet('config',$key);
        if (!isset($value)){
            $value = $this->where('key',$key)->value('value');
            $redis->hSet('config',$key,$value);
        }*/
        return $value;
    }
}