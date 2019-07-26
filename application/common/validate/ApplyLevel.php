<?php

namespace app\common\validate;

use think\Db;
use think\Validate;

/**
 * Description of Article
 *
 * @author Administrator
 */
class ApplyLevel extends Validate
{
    //验证规则
    protected $rule = [
        'level'=>'',
    ];

    //错误消息
    protected $message = [
    ];

    //验证场景
    protected $scene = [
        'apply_before'  => ['level' =>  'require|isTopLevel'],
        'apply_submit'  => [
            'level' =>  'require|isTopLevel',
        ]
    ];

    public function isTopLevel($value){
        $level_id = Db::name('user_level')->order('level_id desc')->value('level_id');
        if($value == $level_id){
            return '您已经达到最高级,无法继续申请';
        }
        return true;
    }


 






}
