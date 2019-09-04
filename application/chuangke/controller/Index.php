<?php

namespace app\chuangke\controller;
use think\Controller;
use think\Db;
use app\mobile\controller\MobileBase;

class Index extends MobileBase
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

        $image_info = tpCache('image_info');
        $this->assign('image_info',$image_info);
        $this->assign('unread_message',$unread_message);

        // 首页文章
        $article = M('article')->where(['cat_id' => 7])->select();
        $this->assign('article',$article);

        // 最近还款列表
        $loopNotice = M('loopNotice')->where(['is_show' => 1])->select();
        $this->assign('loopNotice',$loopNotice);

        // 首页公告
        $notice = M('article')->where(['cat_id' => 8])->order('article_id DESC')->find();
        $this->assign('notice',htmlspecialchars_decode($notice['content']));

        // 是否弹出公告
        if ($notice && tpCache('shop_info.is_notice') == 1) {
            $is_login = strpos($_SERVER['HTTP_REFERER'],'/chuangke/Login/index.html');
            if (!$_SERVER['HTTP_REFERER'] || $is_login) $is_popup = 1;
        }

        if (tpCache('shop_info.template1') == 2) {
            return $this->fetch('plan/template1');
        }else{
            return $this->fetch();
        }
    }

    /**
     * 0费率还款技巧
     */
    public function paymentSkills(){

        $image_info = tpCache('image_info');
        if(empty($image_info['paymentSkills_title'])){
            $title = '0费率还款技巧';
        }else{
            $title = $image_info['paymentSkills_title'];
        }
        $this->assign('title',$title);
        $this->assign('image_info',$image_info);
        return $this->fetch();
    }

    /**
     * 担保金如何获取
     */
    public function obtainGuarantee(){

        $image_info = tpCache('image_info');
        if(empty($image_info['obtainGuarantee_title'])){
            $title = '0费率还款技巧';
        }else{
            $title = $image_info['obtainGuarantee_title'];
        }
        $this->assign('title',$title);
        $this->assign('image_info',$image_info);
        return $this->fetch();
    }

    /**
     * 如何帮他人还款赚利息
     */
    public function earningInterest(){
        $image_info = tpCache('image_info');
        if(empty($image_info['earningInterest_title'])){
            $title = '如何帮他人还款赚利息';
        }else{
            $title = $image_info['earningInterest_title'];
        }
        $this->assign('title',$title);
        $this->assign('image_info',$image_info);
        return $this->fetch();
    }

    /**
     * 借款人不还钱怎么办
     */
    public function noPayment(){
        $image_info = tpCache('image_info');
        if(empty($image_info['noPayment_title'])){
            $title = '借款人不还钱怎么办';
        }else{
            $title = $image_info['noPayment_title'];
        }
        $this->assign('title',$title);
        $this->assign('image_info',$image_info);
        return $this->fetch();
    }
}