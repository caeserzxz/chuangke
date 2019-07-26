<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/9
 * Time: 9:35
 */
namespace app\common\behavior;

class Init
{
    public function run(&$params)
    {
        //若未绑定，需要进行绑定操作
        /*$is_bind_account = tpCache('basic.is_bind_account');
        if(strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger') && $is_bind_account){
            header("location:" . U('Mobile/User/bind_guide'));//微信浏览器, 调到绑定账号引导页面
        }*/
    }
}