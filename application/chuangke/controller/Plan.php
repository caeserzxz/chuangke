<?php

namespace app\chuangke\controller;

use app\common\logic\CartLogic;
use app\common\logic\OrderLogic;
use app\common\logic\UsersLogic;
use app\common\model\Users;
use think\Db;
use app\mobile\controller\MobileBase;
use app\common\logic\MemberLogic;


class Plan extends MobileBase
{
	protected $user_id;
	public function _initialize()
	{
		parent::_initialize();
		$user = session('user');
		$this->user_id = $user['user_id'];
		$nologin = [];
		if (!$this->user_id && !in_array(ACTION_NAME, $nologin)) {
			header("location:" . U('Mobile/User/login'));
			exit;
		}
	}
    // 计划页面
    public function index(){
        $user = M('users')->where(['user_id' => $this->user_id])->find();
        $debt[] = ['code' => '1','name' => '信用卡'];
        $debt[] = ['code' => '2','name' => '房贷'];
        $debt[] = ['code' => '3','name' => '车贷'];
        $debt[] = ['code' => '4','name' => '其他'];

        foreach ($debt as $key => $value) {
            $money = M('user_debt')->where(['user_id' => $this->user_id,'type' => $value['code'],'status' => 2])->sum('moneys');
            $debt[$key]['money'] = $money ? $money : 0;
            $debt_count = M('user_debt')->where(['user_id' => $this->user_id,'type' => $value['code'],'status' => 1])->count(); // 是否有计划正在审核
            $debt[$key]['debt_count'] = $debt_count;
        }

        # 阶段及百分比计算
        $stage = [];
        $shop_info = tpCache('shop_info');
        // 所有负债
        $all_debt = M('user_debt')->where(['user_id' => $this->user_id,'status' => 2])->sum('moneys');
        // 已收款金额
        $all_rece1 = M('ck_apply')->where(['check_leader_1' => $this->user_id,'check_status_1' => 1])->sum('make_money');
        $all_rece2 = M('ck_apply')->where(['check_leader_2' => $this->user_id,'check_status_2' => 1])->sum('make_money');
        $all_rece = $all_rece1 + $all_rece2;
        /*$all_rece = M('ck_apply')
            ->where(['check_leader_1' => $this->user_id,'check_status_1' => 1])
            ->whereOR('check_leader_2='. $this->user_id.' and check_status_2=1')
            ->sum('make_money');*/
        $all_rece = floor($all_rece);
        $user['all_rece'] = $all_rece;
        $user['all_debt'] = $all_debt;
        
        if (($all_debt > 0) && ($all_rece >= $all_debt) && ($user['is_lock'] != 1)) {
            M('users')->where(['user_id' => $this->user_id])->save(['is_lock' => 1]);
        }
        // 是否有审核订单
        $is_check = M('ck_apply')
            ->where(['check_leader_1' => $this->user_id,'check_status_1' => ['LT',1],'apply_status' => 0])
            ->whereOR('check_leader_2='.$this->user_id.' and check_status_2<1 and apply_status=0')
            ->count();

        $surplus_debt = $all_debt;
        $surplus_rece = $all_rece;

        $user['all_debt'] = $all_debt;
        $user['all_rece'] = $all_rece;

        $check = 0;
        for($i=1;$i<10;$i++){
            $ratio_now = $rece_money = 0;
            if ($surplus_debt <= 0) continue;
            // 第N阶段所需金额
            $need_money = $shop_info['debt_based']*pow(3,$i);
            if ($need_money > $surplus_debt) {
                // 最后一阶段,取剩余所有金额
                $need_money = $surplus_debt;
            }

            if ($surplus_rece >= $need_money) {
                // 剩余还款金额足够,比例100
                $ratio_now = 100;
                $rece_money = $need_money;
            }else{
                if ($is_check) $check ++;

                // 剩余还款金额不够,比例四舍五入
                if ($surplus_rece > 0) {
                    $ratio_now = round($surplus_rece*100 / $need_money);
                    $rece_money = $surplus_rece;
                }
            }
            $stage[] = ['need_money' => $need_money,'rece_money' => $rece_money,'check' => $check];
            $ratio[] = $ratio_now;

            $surplus_debt -= $need_money;
            $surplus_rece -= $need_money;
        }

        $text = ['一','二','三','四','五','六','七','八','九'];
        // 是否有等级正在审核
        $apply = M('ck_apply')->where(['user_id' => $this->user_id,'apply_status' => 0])->find();

        $this->assign('user',$user);                // 用户数据
        $this->assign('debt',$debt);                // 负债类型
        $this->assign('text',$text);                // 阶段文字
        $this->assign('all_debt',$all_debt);        // 负债总额
        $this->assign('stage',$stage);              // 阶段数据
        $this->assign('ratio',json_encode($ratio)); // 各阶段还款百分比
        $this->assign('apply',$apply);              // 审核中等级申请数量

        if (tpCache('shop_info.template2') == 2) {
            return $this->fetch('plan/template2');
        }else{
            return $this->fetch();
        }
    }

	// 计划页面
	public function index2(){
        $user = M('users')->where(['user_id' => $this->user_id])->find();
        $debt[] = ['code' => '1','name' => '信用卡'];
        $debt[] = ['code' => '2','name' => '房贷'];
        $debt[] = ['code' => '3','name' => '车贷'];
        $debt[] = ['code' => '4','name' => '其他'];

        foreach ($debt as $key => $value) {
            $money = M('user_debt')->where(['user_id' => $this->user_id,'type' => $value['code'],'status' => 2])->sum('moneys');
            $debt[$key]['money'] = $money ? $money : 0;
            $debt_count = M('user_debt')->where(['user_id' => $this->user_id,'type' => $value['code'],'status' => 1])->count(); // 是否有计划正在审核
            $debt[$key]['debt_count'] = $debt_count;
        }

        # 阶段及百分比计算
        $stage = [];
        $shop_info = tpCache('shop_info');
        // 所有负债
        $all_debt = M('user_debt')->where(['user_id' => $this->user_id,'status' => 2])->sum('moneys');
        // 已收款金额
        $all_rece = M('ck_apply')
            ->where(['check_leader_1' => $this->user_id,'check_status_1' => 1])
            ->whereOR('check_leader_2='. $this->user_id.' and check_status_2=1')
            ->sum('make_money');

        if (($all_debt > 0) && ($all_rece >= $all_debt) && ($user['is_lock'] != 1)) {
            M('users')->where(['user_id' => $this->user_id])->save(['is_lock' => 1]);
        }
        // 是否有审核订单
        $is_check = M('ck_apply')
            ->where(['check_leader_1' => $this->user_id,'check_status_1' => 0])
            ->whereOR('check_leader_2='.$this->user_id.' and check_status_2=0')
            ->count();

        $surplus_debt = $all_debt;
        $surplus_rece = $all_rece;

        $check = 0;
        for($i=1;$i<10;$i++){
            $ratio_now = $rece_money = 0;
            if ($surplus_debt <= 0) continue;
            // 第N阶段所需金额
            $need_money = $shop_info['debt_based']*pow(3,$i);
            if ($need_money > $surplus_debt) {
                // 最后一阶段,取剩余所有金额
                $need_money = $surplus_debt;
            }

            if ($surplus_rece >= $need_money) {
                // 剩余还款金额足够,比例100
                $ratio_now = 100;
                $rece_money = $need_money;
            }else{
                if ($is_check) $check ++;

                // 剩余还款金额不够,比例四舍五入
                if ($surplus_rece > 0) {
                    $ratio_now = round($surplus_rece*100 / $need_money);
                    $rece_money = $surplus_rece;
                }
            }
            $stage[] = ['need_money' => $need_money,'rece_money' => $rece_money,'check' => $check];
            $ratio[] = $ratio_now;

            $surplus_debt -= $need_money;
            $surplus_rece -= $need_money;
        }

        $text = ['一','二','三','四','五','六','七','八','九'];
        // 是否有等级正在审核
        $apply = M('ck_apply')->where(['user_id' => $this->user_id,'apply_status' => 0])->find();

        $this->assign('user',$user);                // 用户数据
        $this->assign('debt',$debt);                // 负债类型
        $this->assign('text',$text);                // 阶段文字
        $this->assign('all_debt',$all_debt);        // 负债总额
        $this->assign('stage',$stage);              // 阶段数据
        $this->assign('ratio',json_encode($ratio)); // 各阶段还款百分比
        $this->assign('apply',$apply);              // 审核中等级申请数量

		return $this->fetch();
	}
    // 添加债务
    public function add_debt(){

        if($this->request->isPost()){
            $user = M('users')->field('is_lock,level')->where(['user_id' => $this->user_id])->find();
            if ($user['is_lock'] == 1) $this->error('账号已被冻结,请联系管理员');
            $shop_info = tpCache('shop_info');
            $text = $shop_info['store_name'];

            // 是否实名认证
            $is_authent = M('user_authentication')->where(['user_id' => $this->user_id,'status' => ['IN',[0,1]]])->count();
            if (!$is_authent) $this->ajaxReturn(['status'=>0,'msg'=>$text.'：请先在个人中心实名认证！','url' => U('chuangke/Member/realNameAuthentication')]);
            // 是否绑定收款方式
            $is_receivables = M('receipt_information')->where(['user_id' => $this->user_id])->count();
            // if (!$is_receivables) $this->ajaxReturn(['status'=>0,'msg'=>$text.'：请先在个人中心绑定收款方式！','url' => U('chuangke/Member/paymentMethod')]);

            $money = input('post.money');
            $type = input('post.type');
            $imgsrc = input('post.imgsrc');

            // 是否有已上传或正在审核的同类型债务
            $status1 = M('user_debt')->where(['user_id' => $this->user_id,'type' => $type,'status' => 1])->count();
            if ($status1) $this->error('已有同类型债务审核中');

            $status2 = M('user_debt')->where(['user_id' => $this->user_id,'type' => $type,'status' => 2])->count();
            if ($status2) $this->error('已有同类型债务众筹中');

            // 是否超过最大额度
            $all_adopt = M('user_debt')->where(['user_id' => $this->user_id,'status' => 2])->sum('moneys');
            if ($all_adopt+$money > $shop_info['max_quota']) {
                $this->error('所有债务总额不得超过'.$shop_info['max_quota']);
            }
            $all_check = M('user_debt')->where(['user_id' => $this->user_id,'status' => 1])->sum('moneys');
            if ($all_check+$all_adopt+$money > $shop_info['max_quota']) {
                $this->error('所有债务总额不得超过'.$shop_info['max_quota']);
            }
            // 一旦激活就不能再添加负债
            if ($user['level'] > 1) $this->error('账号已激活,无法添加负债');

            if(empty($imgsrc) && empty($_FILES['imgsrc']['tmp_name'])){
                $this->error('请上传债务凭证');
            }
            if($imgsrc){
                $img_src =   $imgsrc;
            }
            $MemberLogic  = new MemberLogic();
            if($_FILES['imgsrc']['tmp_name']){//上传身份证正面
                $imgsrc = $MemberLogic->upload_img('imgsrc','plan');
                if($imgsrc){
                    $img_src = '/'.UPLOAD_PATH.'plan/'.$imgsrc;
                }
            }

            if ($money <= 0) $this->error('金额错误');
            // 金额是否是200整数倍
            $debt_based = $shop_info['debt_based'];
            if ($money % $debt_based > 0) {
                $this->error('请输入' . $debt_based . '整数倍');
            }
            $data = [
                'user_id'    => $this->user_id,
                'moneys'     => $money,
                'imgsrc'     => $img_src,
                'type'       => $type,
                'createtime' => time(),
            ];
            // 添加记录
            $res = M('user_debt')->add($data);
            if ($res) {
                $this->success('提交成功，等待审核',U('chuangke/Plan/index'));
            }else{
                $this->error('添加失败');
            }
        }else{
            $type = I('get.type');
            if ($type < 1 || $type > 4) $this->error('债务类型错误');
            $debt_name = ['未知','信用卡','房贷','车贷','其他'];

            $this->assign('config', tpCache('shop_info'));
            $this->assign('appType',session('appType'));
            $this->assign('type',$type);
            $this->assign('debt_name',$debt_name);
            return $this->fetch();
        }
    }
    // 我的团队
    public function users_team(){
        $team = M('users_team')->where(['user_id' =>$this->user_id])->find();
        $this->assign('team',$team);
        return $this->fetch();
    }
}