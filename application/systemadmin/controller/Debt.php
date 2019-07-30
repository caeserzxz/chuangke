<?php

namespace app\systemadmin\controller;
use app\systemadmin\logic\OrderLogic;
use think\AjaxPage;
use think\Page;
use think\Verify;
use think\Db;
use app\systemadmin\logic\UsersLogic;
use think\Loader;

class Debt extends Base {

    public function index(){
        return $this->fetch();
    }
    /**
     *  审核升级记录
     */
    public function debt_list(){

        I('user_id') ? $where['u.user_id'] = I('user_id') : false; 
        I('status') ? $where['w.status'] = I('status') : false; 
        I('type') ? $where['w.type'] = I('type') : false; 

        $count = Db::name('user_debt')->alias('w')->join('__USERS__ u', 'u.user_id = w.user_id', 'INNER')->where($where)->count();
        $Page  = new Page($count,20);
        $list = Db::name('user_debt')->alias('w')->field('w.*,u.nickname')->join('__USERS__ u', 'u.user_id = w.user_id', 'INNER')->where($where)->order("w.id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
        $show  = $Page->show();

        $type = ['债务类型','信用卡','房贷','车贷','其他'];
        $status = ['当前状态','审核中','审核成功','审核失败','其他'];
        $this->assign('type',$type);
        $this->assign('status',$status);

        $this->assign('show',$show);
        $this->assign('list',$list);
        $this->assign('pager',$Page);

        return $this->fetch();
    }
    /**
     * 审核债务
     */
    public function edit_debt(){
        $id = I('get.id');
        $status = I('get.status');
        $res = M('user_debt')->where(['id' => $id])->save(['status' => $status,'update_time' => time()]);
        if (!$res) {
            $this->error('操作失败');
        }
        $this->success('操作成功');
    }
}