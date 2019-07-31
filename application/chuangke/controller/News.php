<?php

namespace app\chuangke\controller;
use think\Controller;
use think\Db;

class News extends Controller
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
     * 消息列表
     */
    public function newsList(){
        $userInfo = $this->userInfo;

        $list = M('message_board')->where(array('user_id'=>$userInfo['user_id']))->select();

        $this->assign('list',$list);
        return $this->fetch();
    }
}