<?php

namespace app\chuangke\controller;

use think\Controller;
use think\Session;
use think\Db;

class Login extends Controller
{

    /**
     * 登录
     */
    public function index(){

        return $this->fetch();
    }

    /**
     * 注册
     */
    public function register(){

        return $this->fetch();
    }

    /**
     * 忘记密码
     */
    public function forgotPassword(){

        return $this->fetch();
    }

    /**
     * 退出登录
     */
    public function layOut(){

        return $this->fetch();
    }
}