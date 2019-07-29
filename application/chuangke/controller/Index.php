<?php

namespace app\chuangke\controller;
use think\Controller;
use think\Db;

class Index extends Controller
{
    /**
     * 首页
     */
    public function index(){

        return $this->fetch();
    }

    /**
     * 0费率还款技巧
     */
    public function paymentSkills(){

        return $this->fetch();
    }

    /**
     * 担保金如何获取
     */
    public function obtainGuarantee(){

        return $this->fetch();
    }

    /**
     * 如何帮他人还款赚利息
     */
    public function earningInterest(){

        return $this->fetch();
    }

    /**
     * 借款人不还钱怎么办
     */
    public function noPayment(){

        return $this->fetch();
    }

}