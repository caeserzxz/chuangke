<?php

namespace app\chuangke\controller;
use think\Controller;
use think\Db;
use app\common\logic\MemberLogic;
use app\mobile\controller\MobileBase;

class Wallet extends MobileBase
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
        $userInfo = $this->userInfo;

        $this->assign('userInfo',$userInfo);
        if (tpCache('shop_info.template3') == 2) {
            return $this->fetch('plan/template3');
        }else{
            return $this->fetch();
        }
    }

    /**
     * 钱包明细
     */
    public function walletRecord(){
        $userInfo = $this->userInfo;
        $model = new MemberLogic();
        $p = I('p')?I('p'):1;
        if(IS_POST){
            $record_list = $model->getRecord($userInfo['user_id'],$p);
            return $record_list;
        }else{
            $record_list = $model->getRecord($userInfo['user_id'],$p);
            $this->assign('record_list',$record_list);
            return $this->fetch();
        }

    }

    /**
     * 佣金明细
     */
    public function commission_list(){
        return $this->fetch();
    }

}