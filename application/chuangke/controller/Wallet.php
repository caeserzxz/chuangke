<?php

namespace app\chuangke\controller;
use think\Controller;
use think\Db;

class Wallet extends Controller
{
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