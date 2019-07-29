<?php

namespace app\chuangke\controller;
use think\Controller;
use think\Db;

class Member extends Controller
{
    /**
     * 首页
     */
    public function index(){

        return $this->fetch();
    }

    /**
     * 实名认证
     */
    public function realNameAuthentication(){

        return $this->fetch();
    }

    /**
     * 实名认证结果
     */
    public function authenticationResult(){

        return $this->fetch();
    }

    /**
     * 收款方式
     */
    public function paymentMethod(){

        return $this->fetch();
    }

    /**
     * 我的好友
     */
    public function myGoodFriend(){

        return $this->fetch();
    }

    /**
     * 好友列表
     */
    public function goodFriendList(){

        return $this->fetch();
    }

    /**
     * 我的团队
     */
    public function myTeam(){

        return $this->fetch();
    }

    /**
     * 申请代理
     */
    public function applicationAgency(){

        return $this->fetch();
    }

    /**
     * 联系我们
     */
    public function callMeBaby(){

        return $this->fetch();
    }
}