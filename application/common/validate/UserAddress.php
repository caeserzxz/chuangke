<?php

namespace app\common\validate;

use think\Db;
use think\Validate;

/**
 * Description of Article
 *
 * @author Administrator
 */
class UserAddress extends Validate
{
    //验证规则
    protected $rule = [
        'consignee' =>  '',
        'mobile'    =>  '',
        'address'   =>  '',
        'area_id'   =>  '',

    ];

    //错误消息
    protected $message = [
        'consignee.require'=>'收货人不能为空值',
        'mobile.require'=>  '手机不能为空值',

        'address.require'=>'收货地址不能为空',
        'area_id.require'=>'请选择省市区',
    ];

    //验证场景
    protected $scene = [
        'apply_submit'  => [
            'consignee' =>  'require',
            'mobile'    =>  'require',
            'address'   => 'require',
            'area_id'   =>  'require|isAbleArea',
        ]
    ];

    //是否是有效的区域
    public function isAbleArea($value){
        $lists = explode(',',$value);
        $count = Db::name('region')->whereIn('id',$lists)->count();
        if($count != count($lists)){
            return '填写省市区有误!';
        }
        return true;
    }










}
