<?php
namespace app\api\controller;

use think\Request;

class Upload extends Base
{   
    // 全局图片上传
    public function upload(Request $request)
    {
        //$image = $request->file('image'); // 图片
        $image = $this->request->file('file');
        
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
    
    
    
    /**
     * 上传文件
     * @ApiMethod (POST)
     * @param File $file 文件流
     */
    public function uploads()
    {
        $file = $this->request->file('file');
        if (empty($file)) {
            //$this->error(__('No file upload or server upload limit exceeded'));
            return json(['code' => 2,'msg' => 'No file upload or server upload limit exceeded','data'=>[]]);
        }
        
        //判断是否已经存在附件
        $sha1 = $file->hash();
        $upload=[
            'maxsize'=> '5M',
            'mimetype'=> 'jpg,jpeg,png',
            'savekey'=> 'string'
        ];
        //$upload['maxsize'] =  ;//Config::get('upload');
        
        preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
        $type = strtolower($matches[2]);
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $size = (int)$upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
        $fileInfo = $file->getInfo();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';
        
        $mimetypeArr = explode(',', strtolower($upload['mimetype']));
        $typeArr = explode('/', $fileInfo['type']);
        
        //禁止上传PHP和HTML文件
        if (in_array($fileInfo['type'], ['text/x-php', 'text/html']) || in_array($suffix, ['php', 'html', 'htm'])) {
            //$this->error(__('Uploaded file format is limited'));
            return json(['code' => 2,'msg' => 'Uploaded file format is limited','data'=>[]]);
        }
        //验证文件后缀
        if ($upload['mimetype'] !== '*' &&
            (
                !in_array($suffix, $mimetypeArr)
                || (stripos($typeArr[0] . '/', $upload['mimetype']) !== false && (!in_array($fileInfo['type'], $mimetypeArr) && !in_array($typeArr[0] . '/*', $mimetypeArr)))
                )
            ) {
                //$this->error(__('Uploaded file format is limited'));
                return json(['code' => 2,'msg' => 'Uploaded file format is limited','data'=>[]]);
            }
            //验证是否为图片文件
            $imagewidth = $imageheight = 0;
            if (in_array($fileInfo['type'], ['image/gif', 'image/jpg', 'image/jpeg', 'image/bmp', 'image/png', 'image/webp']) || in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp'])) {
                $imgInfo = getimagesize($fileInfo['tmp_name']);
                if (!$imgInfo || !isset($imgInfo[0]) || !isset($imgInfo[1])) {
                    //$this->error(__('Uploaded file is not a valid image'));
                    return json(['code' => 2,'msg' => 'Uploaded file format is limited','data'=>[]]);
                }
                $imagewidth = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
                $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
            }
            $replaceArr = [
                '{year}'     => date("Y"),
                '{mon}'      => date("m"),
                '{day}'      => date("d"),
                '{hour}'     => date("H"),
                '{min}'      => date("i"),
                '{sec}'      => date("s"),
                '{random}'   => $this->alnum(6),//Random::alnum(16),
                '{random32}' => $this->alnum(32),//Random::alnum(32),
                '{filename}' => $suffix ? substr($fileInfo['name'], 0, strripos($fileInfo['name'], '.')) : $fileInfo['name'],
                '{suffix}'   => $suffix,
                '{.suffix}'  => $suffix ? '.' . $suffix : '',
                '{filemd5}'  => md5_file($fileInfo['tmp_name']),
            ];
            $savekey = $upload['savekey'];
            $savekey = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);
            
            $uploadDir = substr($savekey, 0, strripos($savekey, '/') + 1);
            $fileName = substr($savekey, strripos($savekey, '/') + 1);
            //ROOT_PATH            
            $splInfo = $file->validate(['size' => $size])->move($_SERVER['DOCUMENT_ROOT'] . '/public' . $uploadDir, $fileName);
            if ($splInfo) {
                $params = array(
                    'admin_id'    => 0,
                    'user_id'     => 2,//(int)$this->userinfo['user_id'],//$this->auth->id,
                    'filesize'    => $fileInfo['size'],
                    'imagewidth'  => $imagewidth,
                    'imageheight' => $imageheight,
                    'imagetype'   => $suffix,
                    'imageframes' => 0,
                    'mimetype'    => $fileInfo['type'],
                    'url'         => $uploadDir . $splInfo->getSaveName(),
                    'uploadtime'  => time(),
                    'storage'     => 'local',
                    'sha1'        => $sha1,
                );
/*                 $attachment = model("attachment");
                $attachment->data(array_filter($params));
                $attachment->save();
                \think\Hook::listen("upload_after", $attachment);
 */                
                return json(['code' => 1, 'msg' => 'succeed', 'url' => $uploadDir . $splInfo->getSaveName()]);
                
                /*                 $this->success(__('Upload successful'), [
                 'url' => $uploadDir . $splInfo->getSaveName()
                 ]); */
            } else {
                // 上传失败获取错误信息
                //$this->error($file->getError());
                return json(['code' => 2,'msg' => 'error','data'=>$file->getError()]);
            }
    }
    
    
    /**
     * 生成数字和字母
     *
     * @param int $len 长度
     * @return string
     */
    public static function alnum($len = 6)
    {
        return self::build('alnum', $len);
    }
    
    /**
     * 能用的随机数生成
     * @param string $type 类型 alpha/alnum/numeric/nozero/unique/md5/encrypt/sha1
     * @param int $len 长度
     * @return string
     */
    public static function build($type = 'alnum', $len = 8)
    {
        switch ($type) {
            case 'alpha':
            case 'alnum':
            case 'numeric':
            case 'nozero':
                switch ($type) {
                    case 'alpha':
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'alnum':
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'numeric':
                        $pool = '0123456789';
                        break;
                    case 'nozero':
                        $pool = '123456789';
                        break;
                }
                return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
            case 'unique':
            case 'md5':
                return md5(uniqid(mt_rand()));
            case 'encrypt':
            case 'sha1':
                return sha1(uniqid(mt_rand(), true));
        }
    }
}

