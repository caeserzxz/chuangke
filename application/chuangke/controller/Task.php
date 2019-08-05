<?php

namespace app\chuangke\controller;
use think\Controller;
use think\Db;
use think\Config;

class Task extends Controller {
    public function __construct()
    {
        parent::__construct();
        $userId   = Session('user_id');
        if(empty($userId)){
            // $this->redirect('chuangke/Login/index');
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

        $list = M('user_debt')->where(['status' => 1,'createtime' => ['LT',$time]])->select();
        foreach ($list as $key => $value) {
            if ($value['moneys'] > $shop_info['artificial_min'.$value['type']]) continue;
            // 更新会员等级
            $res = M('user_debt')->where(['id' => $value['id']])->update(['status' => 2,'update_time' =>time()]);
            $mobile = M('users')->where(['user_id' => $value['user_id']])->value('mobile');

            if ($res) {
                // 发送短信
                $msg = jh_message($mobile,Config::get('message.type_examine'),'');
            }
        }
        echo "执行成功";
        // $data = M('user_debt')->where($where)->select();
        // M('user_debt')->where($where)->update(['status' => 2,'update_time' =>time()]);
    }
}