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
        $user = M('users')->where(array('user_id'=>$auth['user_id']))->find();
        if(empty($user)){
            return  array('status'=>-1,'msg'=>'用户不存在');
        }
        M('users')->startTrans();
        M('record')->startTrans();
        M('user_authentication')->startTrans();
        try {
            $data['status'] = $status;
            $data['save_time'] = time();
            //更新实名认证状态
            $res = M('user_authentication')->where(array('id'=>$id))->update($data);
            $model = new MemberLogic();
            $config = tpCache('shop_info');
            if($status==1){  //通过
                //更新用户的昵称
                M('users')->where(array('user_id'=>$auth['user_id']))->update(array('nickname'=>$auth['user_name']));
                $model->earnestSend($auth['user_id'],1);
            }elseif($status==2){//不通过
                $model->earnestSend($auth['user_id'],2);
            }
        }catch (Exception $e){
            M('users')->rollback();
            M('record')->rollback();
            M('user_authentication')->rollback();
            return  array('status'=>-1,'msg'=>'操作失败');
            exit;
        }
        M('users')->commit();
        M('record')->commit();
        M('user_authentication')->commit();
        return  array('status'=>1,'msg'=>'操作成功');

    }

    public function del_auth(){
        $id= I('id');
        $res = M('user_authentication')->where(array('id'=>$id))->delete();
        if($res){
            return  array('status'=>1,'msg'=>'删除成功');

        }else{
            return  array('status'=>-1,'msg'=>'删除失败');
        }
    }
}