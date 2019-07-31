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

        $list = M('message_board')->where(array('user_id'=>$userInfo['user_id']))->order('create_time desc')->select();

        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * 修改消息为已读
     */
    public function save_news(){
        $id = I('id');
        $info = M('message_board')->where(array('id'=> $id ))->find();
        if($info['status']==0){
            $map['status'] = 1;
            M('message_board')->where(array('id'=> $id ))->update($map);
        }else{

        }
    }
}