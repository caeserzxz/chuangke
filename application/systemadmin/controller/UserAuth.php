<?php

namespace app\systemadmin\controller;
use app\systemadmin\logic\OrderLogic;
use think\AjaxPage;
use think\Page;
use think\Verify;
use think\Db;
use app\systemadmin\logic\UsersLogic;
use app\common\logic\MemberLogic;
use think\Loader;

class UserAuth extends Base {
    public function index(){
        return $this->fetch();
    }

    public function auth_list(){
        $user_id = I('user_id');
        $status = intval(I('status'));

        $condition = [];
        if($user_id){
            $condition['user_id'] = $user_id;
        }
        if($status){
            $condition['status'] = $status-1;
        }
        $model = M('user_authentication');
        $count = $model->where($condition)->count();
        $Page  = new Page($count,10);
        $authList = $model->where($condition)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();

        $show = $Page->show();

        $this->assign('user_id',$user_id);
        $this->assign('status',$status);
        $this->assign('authList',$authList);
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('pager',$Page);
        return $this->fetch();
    }

    public function auth_detail(){
        $id = I('id');
        $auth_info = M('user_authentication')->where(array('id'=>$id))->find();

        $this->assign('auth_info',$auth_info);
        return $this->fetch();
    }

    //修改实名认证状态
    public function save_status(){
        $status = I('status');
        $id = I('id');

        $auth = M('user_authentication')->where(array('id'=>$id))->find();
        if($status==$auth['status']){
            return  array('status'=>-1,'msg'=>'状态未修改');
        }

        M('users')->startTrans();
        M('user_authentication')->startTrans();
        try {
            $data['status'] = $status;
            $data['save_time'] = time();
            $res = M('user_authentication')->where(array('id'=>$id))->update($data);
            if($status==1){
                $model = new MemberLogic();
                $config = tpCache('shop_info');

                $user = M('users')->where(array('user_id'=>$auth['user_id']))->find();

                //自己获得保证金
                 $model->earnestMoney($user['user_id'],$config['earnest_money']);
                add_message($user['user_id'],'实名认证成功,获得'.$config['earnest_money'].'保证金');
                //推荐人获得保证金
                if(!empty($user['first_leader'])){
                    $model->earnestMoney($user['first_leader'],$config['safe_money']);
                    add_message($user['first_leader'],'下级用户'.$user['mobile'].'实名认证成功,获得'.$config['earnest_money'].'保证金');
                }
            }
        }catch (Exception $e){
            M('users')->rollback();
            M('user_authentication')->rollback();
            return  array('status'=>-1,'msg'=>'操作失败');
            exit;
        }
        M('users')->commit();
        M('user_authentication')->commit();
        return  array('status'=>1,'msg'=>'操作成功');

    }

}