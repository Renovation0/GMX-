<?php
namespace app\admin\controller;


use think\Request;
use app\admin\model\MLanguage;

class Language extends Check
{
    public function index()
    {
        $fileName = $_SERVER['DOCUMENT_ROOT'].'/../config/language.php';
        $handle = fopen($fileName, "r");
        $texts = "";
        while (!feof($handle)){
            $text = fgets($handle);
            $texts .= $text;
        }
        $this->assign('text', $texts);
        return view();
    }

    public function edit(Request $request)
    {
        $code = $request->param('code', '');
//        var_dump($code);die();
        $handle = fopen($_SERVER['DOCUMENT_ROOT'].'/../config/language.php', "w");
        $res = fwrite($handle, $code);
        fclose($handle);
        if($res == FALSE){
            return json(['code' => 2, 'msg' => '保存失败']);
        }else{
            return json(['code' => 1, 'msg' => '保存成功']);
        }
    }
    
    
    
    //语言包列表
    public function l_list(Request $request){
        $my_active_module = intval($request->param('my_active_module', 1));
        $this->assign('my_active_module', $my_active_module);
        $M_MLanguage = new MLanguage();
        $configs = $M_MLanguage->getBlock($my_active_module);
        $this->assign('configs', $configs);
        return view();
    }
    
    // 语言包列表参数编辑提交
    public function configEdit(Request $request)
    {
        $params = $request->param();
        unset($params['/index/language/configEdit']);
        $M_MLanguage = new MLanguage();
        if(!empty($params)){
            foreach ($params as $key => $param) {
                $M_MLanguage->where('key', $key)->setField('zh_cn', $param[0]);
                $M_MLanguage->where('key', $key)->setField('zh_cn_tw', $param[1]);
                $M_MLanguage->where('key', $key)->setField('en_us', $param[2]);
            }
            $this->success('修改成功');
        }else{
            $this->error('没有数据');
        }
    }
    
    
    // 语言包列表参数提交
    public function configAdd(Request $request)
    {
        if(!$request->isAjax()){
            return view();
        }else{
            $key = $request->param('key', '');
            $name = $request->param('name', '');
            $zh_cn = $request->param('zh_cn', '');
            $zh_cn_tw = $request->param('zh_cn_tw', '');
            $en_us = $request->param('en_us', '');
            
            if($key == ''){
                return json(['code' => 2, 'msg' => '请输入参数索引']);
            }
            if($name == ''){
                return json(['code' => 2, 'msg' => '请输入参数名称']);
            }
            if($zh_cn == ''){
                return json(['code' => 2, 'msg' => '请输入参数值']);
            }
            
            $M_MLanguage = new MLanguage();
            
            $info = $M_MLanguage->getInfo(['key'=>$key]);
            if(!empty($info)){
                return json(['code' => 2, 'msg' => '参数索引不能重复']);
            }
            
            if($M_MLanguage->insert([
                'key'      =>  $key,
                'name'     =>  $name,
                'zh_cn'    =>  $zh_cn,
                'zh_cn_tw'     =>  $zh_cn_tw,
                'en_us'     =>  $en_us
            ])){
                return json(['code' => 1, 'msg' => '添加成功']);
            }else{
                return json(['code' => 2, 'msg' => '添加失败']);
            }
        }
    }
    
    
}