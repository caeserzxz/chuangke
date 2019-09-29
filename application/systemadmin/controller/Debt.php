<?php

namespace app\systemadmin\controller;
use app\systemadmin\logic\OrderLogic;
use think\AjaxPage;
use think\Config;
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
        $data =  M('user_debt')->where(['id' => $id])->find();
        if (!$data) $this->error('数据不存在');

        $res = M('user_debt')->where(['id' => $id])->save(['status' => $status,'update_time' => time()]);
        if (!$res) $this->error('操作失败');
        if ($status == 2) {
            $mobile = M('users')->where(['user_id' => $data['user_id']])->value('mobile');
            // 发送短信
            $shop_info = tpCache('shop_info');
            if($shop_info['debt_mess']==1){
                $msg = jh_message($mobile,Config::get('database.type_examine'),'');
                if ($msg['error_code'] > 0) {
                    $this->error($msg['reason']);
                }
            }
        }
        $this->success('操作成功');
    }
    /**
     * 删除债务
     */
    public function del_debt(){
        $id = I('get.id');
        $res = M('user_debt')->where(['id' => $id])->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
    /**
     * 还款情况
     */
    public function repayment_list(){
        I('user_id') ? $where['u.user_id'] = I('user_id') : false;

        $count = Db::name('users')->alias('u')->join('user_debt d', 'u.user_id = d.user_id', 'RIGHT')->where($where)->count();
        $Page  = new Page($count,20);
        $list = Db::name('users')->alias('u')->field('u.user_id,u.mobile,u.nickname')
            ->join('user_debt d', 'u.user_id = d.user_id', 'RIGHT')
            ->where($where)->order("u.user_id desc")
            ->limit($Page->firstRow.','.$Page->listRows)->group('u.user_id')->select();
        foreach ($list as $key => $value) {
            // 众筹总额
            $uid = $value['user_id'];
            if (!$uid) continue;
            $debt_money = M('user_debt')->where(['user_id' => $uid,'status' => 2])->sum('moneys');
            // 已收款
            $where = 'check_leader_1='.$uid.' and check_status_1=1 or check_leader_2='.$uid.' and check_status_2=1';
            $enter_money = M('ck_apply')->where($where)->sum('make_money');

            // 已付款
            $out_money = M('ck_apply')->where('user_id='.$uid.' and apply_status=1')->sum('make_money');
            // 审核次数
            $check_num = M('ck_apply')->where($where)->count();

            $list[$key]['debt_money'] = $debt_money;
            $list[$key]['enter_money'] = $enter_money;
            $list[$key]['out_money'] = $out_money;
            $list[$key]['check_num'] = $check_num;
        }
        $show  = $Page->show();
        $this->assign('show',$show);
        $this->assign('list',$list);
        $this->assign('pager',$Page);

        return $this->fetch();
    }

    /**
     * 还款方式
     * @date 2019/08/10
     */
    public function receivables_list(){
        I('user_id') ? $where['u.user_id'] = I('user_id') : false;

        $count = Db::name('receipt_information')->alias('w')->join('__USERS__ u', 'u.user_id = w.user_id', 'INNER')->where($where)->count();
        $Page  = new Page($count,20);
        $list = Db::name('receipt_information')->alias('w')->field('w.*,u.nickname')->join('__USERS__ u', 'u.user_id = w.user_id', 'INNER')->where($where)->order("w.id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
        $show  = $Page->show();

        $this->assign('show',$show);
        $this->assign('list',$list);
        $this->assign('pager',$Page);


        return $this->fetch();
    }
}