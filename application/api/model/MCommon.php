<?php
namespace app\api\model;

use think\Model;
use think\Db;

class MCommon extends Model
{
    /*
     * 流水号生成
     * return string
     * */
    public function createserialnum()
    {
        return date('YmdHis') . rand(1111, 9999).rand(1111, 9999);
    }
    
    
    /** 生成随机字符串
     * @param $length
     * @param bool $numeric
     * @return bool|string
     * @throws Exception
     */
    public function getRand($length, $numeric = false)
    {
        $str = "0 1 2 3 4 5 6 7 8 9 q w e r t y u i o p a s d f g h j k l z x c v b n m Q W E R T Y U I O P A S D F G H J K L Z X C V B N M";
        if ($numeric) {
            $str = "0 1 2 3 4 5 6 7 8 9";
        }
        $arr = explode(' ', $str);
        shuffle($arr);
        $str = implode('', $arr);
        $res = substr($str, 0, $length);
        if (Db::name('member_list')->where('guid', $res)->count() == 0) {
            return $res;
        } else {
            return $this->getRand($length, $numeric);
        }
    }
    
    
    //生成guids
    public static function settoken()
    {
        $str = md5(uniqid(md5(microtime(true)), true));  //生成一个不会重复的字符串
        $str = sha1($str);  //加密
        return $str;
    }

    
    
    public function delInfo($condition)
    {
        return Db::table($this->table)->where($condition)->delete();
    }
    
    
    /**
     * 字段数值增加
     */
    public function getIncrease($condition,$filed,$num){
        return Db::name($this->table)->where($condition)->setInc($filed,$num);
    }
    
    /**
     * 字段数值减少
     */
    public function getReduce($condition,$filed,$num){
        return Db::name($this->table)->where($condition)->setDec($filed,$num);
    }
    
    
    /**
     * 获取单条记录的基本信息
     *
     * @param unknown $condition
     * @param string $field
     */
    public function getInfo($condition = '', $field = '*')
    {

        $info = Db::table($this->table)->where($condition)->field($field)->find();
        return $info;
    }
    

    public function getPurchaseInfo($condition = '',$field = '*')
    {

        $purchase_info = Db::table('zm_member_mutualaid')->where($condition)->field($field)->select();
        return $purchase_info;
    }

    public function updateBalance($condition = '',$new_balance = '')
    {
        
        Db::table($this->table)->where($condition)->update(['balance'=>$new_balance]);
    }
    /**
     * 查询单个值
     */
    public function getValue($condition,$value){
        return Db::table($this->table)->where($condition)->value($value);
    }
    
    
    /**
     * 获取多条数据总和
     * @param unknown $condition
     */
    public function getSum($condition,$field){
        return Db::table($this->table)->where($condition)->sum($field);
    }
    
    /**
     * 获取多条数据总条数
     * @param unknown $condition
     */
    public function getListCount($condition='')
    {
        $count = Db::table($this->table)->where($condition)->count();
        return $count;
    }
    
    /**
     * 获取多条数据
     * @param unknown $condition
     * @param unknown $field
     */
    public function getList($condition='', $field="*", $order="")
    {
        $list = Db::table($this->table)->field($field)->where($condition)->order($order)->select();
        
        return $list;
    }
    
    /**
     * 列表查询
     *
     * @param unknown $page_index页码
     * @param number $page_size每页显示记录数
     * @param string $condition查询条件
     * @param string $order排序方式(升序|降序)
     * @param string $field查询字段
     */
    public function getListPage($page_index=1, $page_size=10, $condition='',$order='', $field='*')
    {
        $offset         = ($page_index-1)*$page_size;
        $count          = Db::table($this->table)->where($condition)->count();
        $list           = Db::table($this->table)->where($condition)->limit($offset,$page_size)->order($order)->field($field)->select();
        
        foreach ($list as $k => $v){
            if(!empty($v['create_time'])){
                $list[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
            }
            if(!empty($v['update_time'])){
                $list[$k]['update_time'] = date('Y-m-d H:i:s',$v['update_time']);
            }
            if(!empty($v['bo_time'])){
                $list[$k]['bo_time'] = date('Y-m-d H:i:s',$v['bo_time']);
            }
        }
        
        $page_count     = ceil($count/$page_size);
        return array(
            'page'     => $page_index,
            'pages'     => $page_count,
            'count'     => $count,
            'list'      => $list,
        );
    }
    
    /**
     * 多条件列表查询
     *
     * @param unknown $page_index页码
     * @param number $page_size每页显示记录数
     * @param string $condition查询条件
     * @param string $term  多查询条件
     * @param string $order排序方式(升序|降序)
     * @param string $field查询字段
     */
    public function getListPages($page_index=1, $page_size=10, $condition='',$term='',$order='', $field='*')
    {
        $offset         = ($page_index-1)*$page_size;
        $count          = Db::table($this->table)->where($condition)->where($term)->count();
        $list           = Db::table($this->table)->where($condition)->where($term)->limit($offset,$page_size)->order($order)->field($field)->select();
        $page_count     = ceil($count/$page_size);
        return array(
            'list'      => $list,
            'count'     => $count,
            'pages'     => $page_count
        );
    }
    
}

