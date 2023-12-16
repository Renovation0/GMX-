<?php


namespace app\admin\model;


use module\Redis;
use think\Db;
use think\Model;

class SystemModule extends Model
{
    //获取参数，单个参数type=1 多个参数$key si array type!=1
    public function readConfig($keys, $type = 1)
    {
/*         $Redis = new Redis();
        $redis = $Redis->redis(); */
        if ($type == 1 && is_string($keys)) {
/*             if ($redis->hExists('config', $keys)) {
                $value = $redis->hGet('config', $keys);
            }else{ */
                $value = Db::name('system_config')->where('key', $keys)->value('value');
           /*      $redis->hSet('config', $keys, $value);
            } */
            return $value;
        }elseif(is_array($keys)){
            $values = [];
            foreach ($keys as $key) {
               /*  if ($redis->hExists('config', $key)) {
                    $values[$key][] = $redis->hGet('config', $key);
                }else{ */
                    $val = Db::name('system_config')->where('key',$key)->value('value');
                    $values[$key][] =$val;
               /*      $redis->hSet('config', $key, $val);
                } */
            }
            $value = [];
            foreach ($values as $k=>$v){
                foreach($v as $key=>$val){
                    $value[]=$val;
                }
            }
        }else{
            $value = false;
        }
        return $value;
    }


    // 读取启用的模块
    public function readModule()
    {
        return $this->where('status', 1)->select()->toArray();
    }

    // 读取未删除的模块
    public function readExistModule()
    {
        return $this->whereIn('status', [1, 2])->select()->toArray();
    }
}