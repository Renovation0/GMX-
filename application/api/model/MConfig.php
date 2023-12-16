<?php
namespace app\api\model;

use think\Db;

class MConfig extends MCommon
{
    public $table= "zm_system_config";
    
    
    
    //获取参数，单个参数type=1 多个参数$key si array type!=1
    public function readConfig($keys, $type = 1)
    {

        if ($type == 1 && is_string($keys)) {  
            
            return  Db::name('system_config')->where('key', $keys)->where('is_hide = 0')->value('value');
            
        }elseif(is_array($keys)){
            $values = [];
            foreach ($keys as $key) {
                    $val = Db::name('system_config')->where('key',$key)->where('is_hide = 0')->value('value');
                    $values[$key][] =$val;
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
    
    
    
}

