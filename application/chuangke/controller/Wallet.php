<?php

namespace app\chuangke\controller;
use think\Controller;
use think\Db;

class Wallet extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $userId   = Session('user_id');
        if(empty($userId)){
            $this->redirect('chuangke/Login/index');
        }else{
            $userInfo =Db::name('users')
                ->where('user_id',$userId)
                ->find();
            $this->userInfo = $userInfo;
        }
    }

    /**
     * 钱包
     */
    public function wallet(){

        return $this->fetch();
    }

    /**
     * 钱包明细
     */
    public function walletRecord(){

        return $this->fetch();
    }
}