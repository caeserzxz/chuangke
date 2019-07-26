<?php

namespace app\common\model;

use think\Db;
use think\Model;
class Users extends Model
{
    //自定义初始化
    protected static function init()
    {
        //TODO:自定义的初始化
    }

    //获取相应等级名
    public function getLevelNameAttr($value,$data)
    {
        $level_name =Db::name('user_level')->where('level_id',$data['level'])->value('level_name');
        return $level_name;
    }

    //获取默认收货地址
    public function getDefaultAddressAttr($value,$data){
        $address = UserAddress::where('user_id',$data['user_id'])->where('is_default',1)->find();
        return $address;
    }
}
