<?php


namespace app\admin\model;


use think\Model;
use think\facade\Session;

class MGoodsCate extends MCommon
{   
    public $table="zm_goods_class";
    // 获取所有菜单
    public function getAllMenus()
    {
        return $this->where('module_id = 1 AND type < 4')->field('id, action,p_id,name,type,status')->select()->toArray();
    }
    
    // 获取所有菜单
    public function getCategory()
    {
        $p_list = $this->where('pid = 0')->order('sort desc')->select()->toArray();
        foreach ($p_list as $k => $v){
            $p_list[$k]['c_list'] = $this->where('pid ='.$v['id'])->order('sort desc')->select()->toArray();
        }
        
        return $p_list;
    }
    
    // 获取父级菜单
    public function getFMenus()
    {
        return $this->where('pid = 0')->select()->toArray();
    }

    // 获取所有菜单id
    public function getMenuIds()
    {
        return $this->where('status', 1)->column('id');
    }

    // 读取有效权限
    public function readPower($posers, $type)
    {
        if($type == 'id'){
            return $this->whereIn('id', $posers)->where('status', 1)->column('id');
        }elseif($type == 'action'){
            return $this->whereIn('id', $posers)->where('status', 1)->column('action');
        }else{
            return [];
        }
    }

    // 读取菜单
    public function readMenus()
    {
        // 读取模块
        $systemModuleModel = new SystemModule();
        $modules = $systemModuleModel->readModule();
        if(!empty($modules)){
            $module_ids = []; // 模块id
            $menus = []; // 正确的菜单
            foreach ($modules as $module){
                $menus[$module['id']]['module'] = $module; // 菜单模块模块名
                $module_ids[] = $module['id'];
            }
            // 所有菜单
            $all_menus = $this->whereIn('module_id', $module_ids)->select()->toArray();

            if(!empty($all_menus)){
                $menusMain = []; // 主菜单
                foreach ($all_menus as $all_menu){ // 子父菜单整理
                    if($all_menu['type'] == 1 || $all_menu['p_id'] == 0){
                        $menusMain[$all_menu['id']]['menu'] = $all_menu;
                        $menusMain[$all_menu['id']]['module_id'] = $all_menu['module_id'];
                    }else{
                        $menusMain[$all_menu['p_id']]['menus'][] = $all_menu;
                    }
                }

                foreach ($menusMain as $menusMainItem){
                    if(empty($menusMainItem['module_id'])){
                        unset($menusMainItem);
                        continue;
                    }
                    $menus[$menusMainItem['module_id']]['menus'][] = $menusMainItem; // 菜单模块菜单
                }
                
                return $menus;
            }else{ // 没有菜单
                return false;
            }
        }else{ // 没有模块
            return false;
        }
    }
    
    // 修改状态
    public function statusMenu($id, $status)
    {
        if ($this->where('id', $id)->count() > 0) {

            if ($this->where('id', $id)->value('status') == $status) {
                if ($status == 1) {
                    return json(['code' => 2, 'msg' => '此菜单已经启用，操作失败']);
                } else {
                    return json(['code' => 2, 'msg' => '此菜单已经停用，操作失败']);
                }
            }
            if ($this->where('id', $id)->setField('status', $status)) {
                if ($status == 1) {
                    return json(['code' => 1, 'msg' => '启用成功']);
                } else {
                    return json(['code' => 1, 'msg' => '停用成功']);
                }
            } else {
                if ($status == 1) {
                    return json(['code' => 3, 'msg' => '启用失败，请联系服务提供商']);
                } else {
                    return json(['code' => 3, 'msg' => '停用失败，请联系服务提供商']);
                }
            }
            
        } else {
            return json(['code' => 2, 'msg' => '无指定菜单，修改失败']);
        }
    }
}