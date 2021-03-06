<?php

namespace app\chuangke\controller;
use think\Controller;
use think\Db;
use think\Config;
use app\common\logic\MemberLogic;

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
     * 系统维护中
     */
    public function index(){
        if (tpCache('shop_info.system_switch') != 1) {
            $this->redirect('chuangke/Index/index');
        }
        $config = tpCache('shop_info');
        $this->assign('config',$config);

        return $this->fetch('plan/system_maintain');
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
                $shop_info = tpCache('shop_info');
                if($shop_info['debt_mess']==1){
                     $msg = jh_message($mobile,Config::get('database.type_examine'),'');
                }
            }
        }
        $this->AutomaticAudit();
        echo "执行成功";
        // $data = M('user_debt')->where($where)->select();
        // M('user_debt')->where($where)->update(['status' => 2,'update_time' =>time()]);
    }

    public function AutomaticAudit(){
        $config = tpCache('shop_info');

        //获取10分钟之前未通过的实名认证
        $model = new MemberLogic();
        $ahtu_time = time()-($config['auth_time']*60);
        $auth_list = $model->getAuthList($ahtu_time);

        Db::startTrans();
        try {
            foreach($auth_list as $k=>$v){
                //更改状态
                M('user_authentication')->where(array('id'=>$v['id']))->update(array('status'=>1));
                //更改昵称
                M('users')->where(array('user_id'=>$v['user_id']))->update(array('nickname'=>$v['user_name']));
                //分佣保证金
                $model->earnestSend($v['user_id'],1,'');
            }
        }catch (\Exception $e) {  //如书写为（Exception $e）将无效
            Db::rollback();
            dump($e->getMessage());
            exit;
        }
        Db::commit();// 提交事务
        dump('执行成功');
    }
}