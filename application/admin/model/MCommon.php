<?php
namespace app\admin\model;

use think\Model;
use think\Db;

class MCommon extends Model
{
    /**
     * 获取单条记录的基本信息
     *
     * @param unknown $condition
     * @param string $field
     */
    public function getInfo($condition = '', $field = '*')
    {
        $info = Db::table($this->table)->where($condition)
        ->field($field)
        ->find();
        return $info;
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
     * 获取求和记录值
     *
     * @param unknown $condition
     */
    public function getSum($condition = '', $field="*")
    {
        $sum = Db::table($this->table)->where($condition)->sum($field);
        return $sum;
    }
    /**
     * 获取统计记录值
     *
     * @param unknown $condition
     */
    public function getCount($condition = '', $field="*")
    {
        $count = Db::table($this->table)->where($condition)->field($field)->count();
        return $count;
    }
    
    /**
     * 获取单表分页记录
     * 
     * @param unknown $where
     * @param unknown $pageSize
     * @param unknown $allParams
     * @return \think\Paginator
     */
    public function getListPage($condition, $order="", $pageSize, $allParams)
    {
        $list = Db::table($this->table)->where($condition)->order($order)->paginate($pageSize, false, $allParams);        
        return $list;
    }
    /**
     * 新增内容
     *
     * @param unknown $data
     * @return number|string
     */
    public function addActivity($data){
        return Db::table($this->table)->insert($data);
    }
    
    /**
     * 修改内容
     * 
     * @param unknown $condition
     * @param unknown $data
     * @return number|string
     */
    public function updataActivity($condition,$data){
        return Db::table($this->table)->where($condition)->update($data);
    }
    
    /**
     * 删除内容
     * 
     * @param unknown $condition
     * @return number
     */
    public function deleteActivity($condition){
        return Db::table($this->table)->where($condition)->delete();
    }

    public function readLevel($u_id,$field = '*'){
        $user = Db::name('member_list')->where('id ='.$u_id)->field('level,real_name_time,first_blood')->find();
        if ($user['level'] == 0 && $user['real_name_time'] == 0){//注册会员
            $user['level'] = 97;
        }
        if ($user['level'] == 0 && $user['real_name_time'] != 0){//认证会员
            $user['level'] = 98;
        }
        if ($user['level'] == 0 && $user['first_blood'] == 2){//有效会员
            $user['level'] = 99;
        }
        return Db::name('member_level')->where('id ='.$user['level'])->field($field)->find();
    }

    /*tree 0729*/
    //查询多个数据
    public function getSelect($table,$where){
        return Db::name($table)->where($where)->select();
    }

    //查询单个值
    public function getValue($table,$where,$value){
        return Db::name($table)->where($where)->value($value);
    }

    //查询多个值
    public function getField($table,$where,$field = '*'){
        return Db::name($table)->where($where)->field($field)->find();
    }

    //增加数据
    public function getInsert($table,$data){
        return Db::name($table)->insert($data);
    }

    //统计数据
    public function getCounts($table,$where){
        return Db::name($table)->where($where)->count();
    }

    //增加数据返回id
    public function getInsertId($table,$data){
        return Db::name($table)->insertGetId($data);
    }

    //更新数据
    public function getUpdata($table,$where,$data){
        return Db::name($table)->where($where)->update($data);
    }

    //字段数值增加
    public function getIncrease($table,$where,$filed,$num){
        return Db::name($table)->where($where)->setInc($filed,$num);
    }

    //字段数值减少
    public function getReduce($table,$where,$filed,$num){
        return Db::name($table)->where($where)->setDec($filed,$num);
    }
    /*end*/
}

