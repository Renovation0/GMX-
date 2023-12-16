<?php
namespace app\admin\controller;


use app\admin\model\SystemLog;
use think\Controller;
use think\Request;
use OSS\OssClient;
use think\Config;
use think\Image;

class Base extends Controller
{
    // 获取ip
    protected function getIp()
    {
        if(getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
            $ip = getenv("HTTP_CLIENT_IP");
        elseif (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else
            if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
                $ip = getenv("REMOTE_ADDR");
            else
                if (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
                    $ip = $_SERVER['REMOTE_ADDR'];
                else
                    $ip = "unknown";
        return ($ip);
    }

    // 添加系统日志
    protected function addLog($log)
    {
        $systemLogModel = new SystemLog();
        return $systemLogModel->addLog($log);
    }

    // 全局图片上传
    public function uploadImage(Request $request)
    {
        $image = $request->file('image'); // 图片

        $type = $request->param('type');

        $extension = strtolower(pathinfo($image->getInfo('name'), PATHINFO_EXTENSION));

        if($extension != 'png' && $extension != 'jpg' && $extension != 'jpeg' && $extension != 'gif'){
            return json(['code' => 2, 'msg' => '请上传正确格式的图片'.$extension]);
        }
        
        $module = $request->param('module', ''); // 模块
        $folder = $request->param('folder', ''); // 文件夹
        $basePath = $_SERVER['DOCUMENT_ROOT']."/upload/"; // 文件存储路径初始化
        //$basePath = $_SERVER['DOCUMENT_ROOT']."/../../wbo_img/";
        $addPath = '';
        if($module != ''){
            $addPath .= $module;
            if($folder !=''){
                $addPath .= $folder;
            }else{
                $addPath .= '/common';
            }
        }else{
            $addPath .= 'common';
        }
        $path = $basePath.$addPath;
        $info = $image->move($path);
        
/*         if($type == 1){
            $string = 'img/';
        }else if($type == 2){
            $string = 'realname/';
        }else{
            $string = 'order/'.date('Ymd',time()).'/';
        }

        $config = config('alioss.ali_oss');//Config::pull('alioss'); //获取Oss的配置
        //实例化对象 将配置传入
        $ossClient = new OssClient($config['accessKeyId'], $config['accessKeySecret'], $config['endpoint']);

        //加密 生成文件名 之后连接上后缀
        $fileName = $string.sha1(date('YmdHis', time()) . uniqid()) . '.' . $extension;

        //执行阿里云上传
        $result = $ossClient->uploadFile($config['bucket'], $fileName, $image->getInfo()['tmp_name']); */

        if($info){
            //return json(['code' => 1, 'msg' => 'succeed', 'url' => $result['info']['url']]);
            $url = '/upload/'.$addPath.'/'.$info->getSaveName();
            return json(['code' => 1, 'msg' => 'succeed', 'url' => $url]);
        }else{
            return json(['code' => 2, 'msg' => $image->getError()]);
        }
    }
    
    //修改反斜杠
    public function updatexg($str){
        return str_replace("\\","/",$str);
    }
    
}