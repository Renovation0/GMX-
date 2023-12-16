<?php
namespace app\admin\model;

use think\Model;

class MLanguage extends MCommon
{   
    public $table = 'zm_system_language';
    // 获取所有参数
    public function getBlock($Block)
    {
        $configs = $this->where('block',$Block)->select()->toArray();
        return $configs;
    }
    
    
}

