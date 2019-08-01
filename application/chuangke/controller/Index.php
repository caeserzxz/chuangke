<?php

namespace app\chuangke\controller;
use think\Controller;
use think\Db;

class Index extends Controller
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
     * 首页
     */
    public function index(){
        $userInfo = $this->userInfo;
        $unread_message = M('message_board')->where(array('user_id'=>$userInfo['user_id'],'status'=>0))->count();

        $this->assign('unread_message',$unread_message);
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
    /**
     * 自动审核计划任务
     */
    public function autoCheck(){
        $shop_info = tpCache('shop_info');
        $time = time() - $shop_info['autoTime'] * 60;
        $where = "status=1 and createtime<$time and " .
            "(moneys <= ".$shop_info['artificial_min1']." and type=1 or ".
            "moneys <= ". $shop_info['artificial_min2']." and type=2 or ".
            "moneys <= ". $shop_info['artificial_min3']." and type=3 or ".
            "moneys <= ". $shop_info['artificial_min4']." and type=4)";

        // $data = M('user_debt')->where($where)->select();
        M('user_debt')->where($where)->update(['status' => 2,'update_time' =>time()]);
    }

}