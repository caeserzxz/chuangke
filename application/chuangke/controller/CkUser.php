<?php

namespace app\chuangke\controller;

use app\common\logic\CartLogic;
use app\common\logic\OrderLogic;
use app\common\logic\UsersLogic;
use app\common\model\Users;
use think\Db;
use app\mobile\controller\MobileBase;
use think\Page;

class CkUser extends MobileBase
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
	//申请升级页面
	public function applyLevel(){
		//获取用户等级信息
		$user = Users::get($this->user_id);
        //用户当前等级
        $now_level  = Db::name('user_level')->where('level_id',$user['level'])->field('level_id,level_name')->find();
		//获取下一个等级信息
		$next_level = Db::name('user_level')->where('level_id',$user['level'] + 1)->field('level_id,level_name')->find();
		//团队
        $all_arr = all_leader_arr($user['user_id']);
        $total   = array_sum($all_arr);

		$this->assign('user',$user);
		$this->assign('total',$total);
        $this->assign('now_level',$now_level);
		$this->assign('next_level',$next_level);
		return $this->fetch('user/apply_level');
	}

	//申请升级处理
    public function apply_handle(){
        $shopping   = input('shopping');//快递方式 1快递 2自提
        $text = tpCache('shop_info.store_name');

        //获取用户等级信息
        $user = Users::get($this->user_id);
        //是否存在用户
        if(!$user) $this->ajaxReturn(['status'=>0,'msg'=>'用户不存在']);
        //是否有默认地址
        if(($shopping == 1)){
            $address_info = Db::name('user_address')->where(['user_id'=>$user['user_id'],'is_default'=>1])->find();
            if(!$address_info) $this->ajaxReturn(['status'=>0,'msg'=>'用户没有默认地址，请先填写收货地址']);
            $con_people = $address_info['consignee'];
            $con_mobile = $address_info['mobile'];
            $province   = $address_info['province'];
            $city       = $address_info['city'];
            $district   = $address_info['district'];
            $address    = $address_info['address'];
        }

        //获取下一个等级信息
        $next_level = Db::name('user_level')->where('level_id',$user['level'] + 1)->field('level_id,level_name,need_num,recom_condition,make_money')->find();
        if(empty($next_level)) $this->ajaxReturn(['status'=>0,'msg'=>'您已是最高等级']);

        // 是否实名认证
        $is_authent = M('user_authentication')->where(['user_id' => $this->user_id,'status' => ['IN',[0,1]]])->count();
        if (!$is_authent) $this->ajaxReturn(['status'=>0,'msg'=>$text.'：请先在个人中心实名认证！','url' => U('chuangke/Member/realNameAuthentication')]);
        // 是否绑定收款方式
        $is_receivables = M('receipt_information')->where(['user_id' => $this->user_id])->count();
        if (!$is_receivables) $this->ajaxReturn(['status'=>0,'msg'=>$text.'：请先在个人中心绑定收款方式！','url' => U('chuangke/Member/paymentMethod')]);
        // 是否有众筹计划
        $is_plan = M('user_debt')->where(['user_id' => $this->user_id,'status' => 2])->count();
        if (!$is_plan) $this->ajaxReturn(['status'=>0,'msg'=>$text.'：请先添加众筹计划！']);

        //是否已有等级在审核中
        $count = Db::name('ck_apply')->where(['user_id'=>$user['user_id'],'apply_status'=>0])->count();
        if($count) $this->ajaxReturn(['status'=>0,'msg'=>'已有等级正在审核中，请稍后再试']);

        // 验证推荐条件是否满足
        $check_recom_condition = $this->check_recom_condition($this->user_id,$next_level);
        if ($check_recom_condition['code'] != 1) {
            $this->ajaxReturn(['status'=>0,'msg'=>'您的推荐人数不足']);
        }
        //我的N层下级一星以上用户数量
       /* $user_id_arr = sub_user1([$user['user_id']],$user['level']-1);
        $num = count($user_id_arr) - 1;
        if($num < $next_level['need_num']) $this->ajaxReturn(['status'=>0,'msg'=>'你的团队的一星或以上人数不足'.$next_level['need_num'].'人' ]);*/
        //如果没有关系链
        // if(!$user['leader_all']) $this->ajaxReturn(['status'=>0,'msg'=>'没有关系链']);

        //获取拥有审核权的用户
        $leader_arr = explode('_',$user['leader_all']);        
        krsort($leader_arr);
        $leader = array_values($leader_arr);
        $team_data = [];
        //满足审核条件的用户
        $check_id = 0;
        if ($next_level['level_id'] == 10 && tpCache('shop_info.nine_stars_rule') == 1) {
            // 升级九星若后台设置的是规则二不找对应层级直接匹配给管理员
            // 其他等级不受影响
        }else{
            foreach ($leader as $k=>$v){
                if($k  < ($next_level['level_id']-1)) continue;//升级N星则从N层开始找，N-1层直接跳过

                $now_user = Db::name('users')->field('level,user_type,is_lock')->where('user_id',$v)->find();
                // 对应层级用户是否满足
                if ($k == $next_level['level_id']-1) {
                    if($now_user['level'] >= $next_level['level_id'] && $now_user['is_lock'] != 1){
                        $check_id = $v;
                        // 添加订单数
                        $team_data[$v]['team_order_'.$k] = 1;
                        break;
                    }else{
                        // 添加漏单记录
                        $team_data[$v]['is_out'] = 1;
                        $team_data[$v]['team_order_out_'.$k] = 1;
                    }
                }
                // 对应层级不满足找最近的链上管理员 不分等级
                if ($now_user['user_type'] == 1 && !$now_user['is_lock']) {
                    $check_id = $v;
                    break;
                }
            }
        }
        //特殊情况 第N层找不到满足条件的用户，直接分配管理员
        if(empty($check_id)){
            //关系链最近的N星管理员
            /*$check_user = Db::name('users')->where(['user_id'=>['In',implode(',',$leader)],'level'=>$next_level['level_id'],'user_type'=>1])->value('user_id');
            if($check_user){
                $check_id = $check_user;
            }else{*/
                //关系链上没有N星管理员则选择平台管理员 不分等级 取比自己ID大的
                $check_user = Db::name('users')->where(['user_type'=>1,'user_id' => ['GT',$this->user_id],'is_lock' => 0])->value('user_id');
                if (!$check_user) {
                    // 没有比自己大的取最近的
                    $check_user = Db::name('users')->where(['user_type'=>1,'user_id' => ['NEQ',$this->user_id],'is_lock' => 0])->value('user_id');
                }
                if($check_user){
                    $check_id = $check_user;
                }else{
                    $this->ajaxReturn(['status'=>0,'msg'=>'平台暂无符合的管理员']);
                }
            // }
        }

        //升级一星 需要增加一个九星星身份的审核
        if($next_level['level_id'] == 2){
            //满足审核条件的用户
            $check_id_2 = 0;
            foreach ($leader as $k=>$v){
                // if($k < 9) continue;//从第九层开始找
                $now_user = Db::name('users')->field('level,user_type,is_lock')->where('user_id',$v)->find();
                // if ($k == 9) {
                    //找最近的九星
                if($now_user['level'] >= 10 ){
                    if ($now_user['is_lock'] != 1) {
                        $check_id_2 = $v;
                        // 添加订单记录
                        $team_data[$v]['team_order_'.$k] = 1;
                        break;
                    }else{
                        // 添加漏单记录
                        $team_data[$v]['is_out'] = 1;
                        $team_data[$v]['team_order_out_'.$k] = 1;
                    }
                }
                // }
                // 对应层级不满足找最近的链上管理员 不分等级
                /*if (($now_user['user_type'] == 1) && ($v != $check_id) && ($now_user['is_lock'] != 1)) {
                    $check_id_2 = $v;
                    break;
                }*/
            }
            //特殊情况 关系链没有九星以上身份 则分配管理员
            if(empty($check_id_2)){
                //关系链最近的管理员
                // $check_user = Db::name('users')->where(['user_id'=>['In',implode(',',$leader)],'level'=>10,'user_type'=>1])->value('user_id');
                $leaders = implode(',',$leader);
                $where_admin = "user_id in ($leaders) and user_type=1 and is_lock=0 and user_id <> ".$check_id." and user_id <>".$this->user_id;
                $check_user = Db::name('users')->where($where_admin)->value('user_id');

                if($check_user){
                    $check_id_2 = $check_user;
                }else{
                    $where_id2 = 'user_type = 1 and user_id <> ' . $check_id . ' and user_id > ' . $this->user_id . ' and is_lock = 0';
                    //关系链上没有管理员则选择平台管理员
                    $check_user = Db::name('users')->where($where_id2)->value('user_id');
                    if (!$check_user) {
                        $where_id2 = 'user_type = 1 and user_id <> ' . $check_id . ' and user_id <> ' . $this->user_id . ' and is_lock = 0';
                        $check_user = Db::name('users')->where($where_id2)->value('user_id');
                    }
                    if($check_user){
                        $check_id_2 = $check_user;
                    }else{
                        $this->ajaxReturn(['status'=>0,'msg'=>'平台暂无符合的管理员']);
                    }
                }
            }
        }

        //满足条件 添加到审核表
        $data = array();
        $data['user_id']        = $user['user_id'];
        $data['level']          = $next_level['level_id'];
        $data['apply_time']     = time();
        $data['check_leader_1'] = $check_id;
        $data['check_leader_2'] = isset($check_id_2) ? $check_id_2 : '';
        $data['shopping_type']  = $shopping;
        $data['con_people']     = $con_people;
        $data['con_mobile']     = $con_mobile;
        $data['province']       = $province;
        $data['city']           = $city;
        $data['district']       = $district;
        $data['address']        = $address;
        $data['make_money']     = $next_level['make_money'];

        Db::startTrans();
        try {
            $resID = Db::name('ck_apply')->add($data);
            if (!$resID) {
                Db::rollback();
                $this->ajaxReturn(['status'=>0,'msg'=>'添加审核失败']);
            }
            if (!$team_data) {
                Db::commit();
                $this->ajaxReturn(['status'=>1,'msg'=>'添加审核成功','data'=>$resID]);
            }
            # 添加团队统计记录
            foreach ($team_data as $key => $value) {
                $users_team = M('users_team')->where(['user_id' => $key])->find();

                if ($value['is_out'] == 1) {
                    end($value);
                    // 添加漏单消息记录
                    $res = add_message($key,'亲爱的'.$text.'用户:你错过了审核'.substr_replace($user['mobile'],'****',3,4).'用户升级的订单');
                    // if (!$res) break;
                }
                // 升一星时9星用户没有层级限制,可能会有team_order_10情况
                $res = M('users_team')->where(['id' => $users_team['id']])->setInc(key($value),1);
                // if (!$res) break;
            }
            /*if (!$res) {
                Db::rollback();
                $this->ajaxReturn(['status'=>0,'msg'=>'添加审核失败']);
            }*/
            Db::commit();
            $this->ajaxReturn(['status'=>1,'msg'=>'添加审核成功','data'=>$resID]);
        } catch (\Exception $e){
            $this->ajaxReturn(['status'=>0,'msg'=>'操作失败']);
        }
    }

	public function addOrder(){
		if($this->request->isPost()){
			$data = input('post.');
			$user = Users::get($this->user_id);

			//判断是否前往购买商品升级 是否满足条件
			$user_level_money = Db::name('user_level')->where('level_id',$user['level']+1)->value('conditions_1');

			if($user['level'] == 1 && $user['first_leader'] == 0){
				return $this->error("上级会员不存在",U('User/index'));
			}

			if($user['level'] > 1){

				if($user_level_money == 0 || $user['distribut_money'] == 0){
					return $this->error("收入金额不足，前往升级失败",U('User/index'));
				}
				if(floatval($user['distribut_money']) < floatval($user_level_money)){
					return $this->error("收入金额不足，前往升级失败",U('User/index'));
				}

				if($user['first_leader'] == 0){
					return $this->error("上级会员不存在",U('User/index'));
				}
			}


			$data['level'] = $user['level'];
			$validate = $this->validate($data,'ApplyLevel.apply_submit');
			$validate1 = $this->validate($data,'UserAddress.apply_submit');	//验证地址

			if($validate !== true)
				exit(json_encode(['status'=>0,'msg'=>$validate]));
			if($validate1 !== true)
				exit(json_encode(['status'=>0,'msg'=>$validate1]));

			if(!$data['goods_ids'])
				exit(json_encode(['status'=>0,'msg'=>'请选择商品']));

			//更新或插入一条新的用户地址
			$user_logic = new UsersLogic();
			$tmp_address_id = !$user['default_address']['address_id'] ? 0 : $user['default_address']['address_id'] ;
			$pcd = explode(',',$data['area_id']);

			list($data['province'],$data['city'],$data['district']) = $pcd;
			$edit_address = $user_logic->add_address($this->user_id,$tmp_address_id,$data);
			if($edit_address['status'] != 1){
				exit(json_encode($edit_address));
			}
			//更新了的用户默认地址需要重新更新获取
			$address_id = $edit_address['result'];
			// dump($address_id);exit;
			$goods_ids = explode(',',$data['goods_ids']);
			$user_note = $data['user_note'];
			$user_money = $data['user_money'];

			//构造购物车模式
			$cartLogic = new CartLogic();
			$cartLogic->setUserId($this->user_id);
			$cartLogic->clean();	//首先全部初始购物车
			//商品id需要循环-*
			foreach($goods_ids as $key =>$value){
				$cartLogic->setGoodsModel($value);
				$cartLogic->setGoodsBuyNum(1);
				$result_cart = $cartLogic->addGoodsToCart();
				if($result_cart['status'] !== 1){
					exit(json_encode($result_cart));
				}
			}

			$userCartList = $cartLogic->getCartList(1);
			$order_goods = collection($userCartList)->toArray();

			$orderLogic = new OrderLogic();
			$orderLogic->setAction('submit_order');
			$orderLogic->setCartList($order_goods);

			$payables =	$goodsFee = 0 ;
			//计算金额
			foreach($userCartList as $k => $v){
				$payables += $v['member_goods_price'] * $v['goods_num'];
				$goodsFee += $v['member_goods_price'] * $v['goods_num'];
			}

			//判断金额是否合法
			if($user_money < 0 || $user['user_money'] < $user_money)
				exit(json_encode(['status'=>0,'msg'=>'使用金额不合法']));

			if($user['user_money'] >= $user_money && $user_money <= $goodsFee){
				$payables -= $user_money;
			}

			$car_price = array(
				'postFee'      =>0, // 物流费
				'couponFee'    =>0, // 优惠券
				'balance'      => $user_money, // 使用用户余额
				'pointsFee'    => 0, // 积分支付
				'payables'     => $payables, // 应付金额
				'goodsFee'     => $goodsFee,// 商品价格
				'order_prom_id' => 0, // 订单优惠活动id
				'order_prom_amount' => 0, // 订单优惠活动优惠了多少钱
			);

			$result = $orderLogic->ckAddOrder($this->user_id,$address_id,$car_price,$user_note,'alipay'); // 添加订单
			exit(json_encode($result));

		}
	}


	/**
     * 升级审核
     */
	public function check_level(){
	    if($this->request->isAjax()){
            $status = input('status');
            $id     = input('id');
            if(empty($id) || empty($status)) $this->ajaxReturn(['status'=>0,'msg'=>'数据有误']);
            $info = Db::name('ck_apply')->where('id',$id)->find();
            if(empty($info)) $this->ajaxReturn(['status'=>0,'msg'=>'查无信息']);

            // 账号是否被冻结
            $is_lock = M('users')->where(['user_id' => $this->user_id])->value('is_lock');
            if ($is_lock == 1) $this->ajaxReturn(['status'=>0,'msg'=>'账号已被冻结,请联系管理员']);

            //第一层领导审核
            $updata = array();
            if ($info['check_leader_1'] == $info['check_leader_2']) {
                if ($info['check_status_1'] < 1) {
                    $updata['check_status_1'] = $status;
                    $updata['check_time_1']   = time();
                }else{
                    if($status == 1 && $info['check_status_1'] == 1) $updata['apply_status'] = $status;
                    $updata['check_status_2'] = $status;
                    $updata['check_time_2']   = time();
                }
            }else{
                if($info['check_leader_1'] == $this->user_id){
                    if(empty($info['check_leader_2']))  $updata['apply_status'] = $status;
                    // if(!empty($info['check_status_2'])) $updata['apply_status'] = $status;
                    if($info['check_status_2'] == 1) $updata['apply_status'] = $status;
                    $updata['check_status_1'] = $status;
                    $updata['check_time_1']   = time();
                }elseif($info['check_leader_2'] == $this->user_id){
                    //第二层领导审核
                    if(!empty($info['check_status_1']) && $status == 1) $updata['apply_status'] = $status;
                    $updata['check_status_2'] = $status;
                    $updata['check_time_2']   = time();
                }else{
                    $this->ajaxReturn(['status'=>0,'msg'=>'数据错误']);
                }
            }
            if (!$updata) $this->ajaxReturn(['status'=>0,'msg'=>'数据错误']);

            Db::startTrans();
            try {
                $res = Db::name('ck_apply')->where('id',$id)->save($updata);
                if (!$res) {
                    Db::rollback();
                    $this->ajaxReturn(['status'=>0,'msg'=>'数据更新失败']);
                }
                if($updata['apply_status'] == 1){
                    //审核通过 更新用户等级
                    $res1 = Db::name('users')->where('user_id',$info['user_id'])->setField('level',$info['level']);
                    if (!$res1) {
                        Db::rollback();
                        $this->ajaxReturn(['status'=>0,'msg'=>'等级更新失败']);
                    }
                    # 升一星时添加对应层级激活人数
                    if ($info['level'] == 2) {
                        $user = M('users')->field('leader_all')->where(['user_id' => $info['user_id']])->find();
                        $leader_arr = explode('_',$user['leader_all']);        
                        krsort($leader_arr);
                        $leader = array_values($leader_arr);
                        if (count($leader) > 1) {
                            foreach ($leader as $key => $value) {
                                if ($key >= 10 || $key < 1) continue;
                                $res2 = M('users_team')->where(['user_id' => $value])->setInc('team_'.$key,1);
                                if (!$res2) break;
                            }
                            if (!$res2) {
                                Db::rollback();
                                $this->ajaxReturn(['status'=>0,'msg'=>'激活人数更新失败']);
                            }
                        }                        
                    }
                }
                # 验证是否已还款完成 冻结账号
                if ($is_lock != 1) {
                    $all_debt = M('user_debt')->where(['user_id' => $this->user_id,'status' => 2])->sum('moneys'); // 所有负债
                    // 已收款金额
                    $all_rece = M('ck_apply')
                        ->where(['check_leader_1' => $this->user_id,'apply_status' => 1])
                        ->whereOR('check_leader_2='.$this->user_id.' and apply_status=1')
                        ->sum('make_money');

                    if ($all_rece >= $all_debt) {
                        $res3 = M('users')->where(['user_id' => $this->user_id])->save(['is_lock' => 1]);
                        if (!$res3) {
                            Db::rollback();
                            $this->ajaxReturn(['status'=>0,'msg'=>'账号冻结失败']);
                        }
                    }
                }
                Db::commit();
                $this->ajaxReturn(['status'=>1,'msg'=>'操作成功']);
            } catch (\Exception $e){
                $this->ajaxReturn(['status'=>0,'msg'=>'操作失败']);
            }   
        }
    }
    public function check_level_list(){
        $count = Db::name('ck_apply')->alias('A')
            -> where('check_leader_1 = '.$this->user_id.' and A.check_status_1 < 1 or A.check_leader_2 = '.$this->user_id.' and A.check_status_2 < 1')
            -> count();
        $Page = new Page($count, 10);

        $check_user = Db::name('ck_apply')->alias('A')
            -> field('A.*,B.nickname,B.mobile,B.wx_number,C.level_name,D.user_name,D.id_card')
            -> join('users B','A.user_id = B.user_id','left')
            -> join('user_level C','A.level = C.level_id','left')
            -> join('user_authentication D','A.user_id = D.user_id','left')
            -> where('check_leader_1 = '.$this->user_id.' and A.check_status_1 < 1 and  A.apply_status = 0 or A.check_leader_2 = '.$this->user_id.' and A.check_status_2 < 1 and  A.apply_status = 0 ')
            // -> where('check_leader_1 = '.$this->user_id.' or A.check_leader_2 = '.$this->user_id)
            ->order('A.apply_time DESC')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            -> select();
        foreach ($check_user as $key => $value) {
            $check_user[$key]['user_name']  = $this->substr_cut($value['user_name']);
            $check_user[$key]['id_card']  = substr_replace($value['id_card'],'**********',4,10);

            if ($value['check_leader_1'] == $value['check_leader_2']) {
                // 审核人1和审核人2是同一个人 type取决于哪一个没有审核
                if (!$value['check_status_1']) {
                    $check_user[$key]['type'] = 1;
                }else{
                    $check_user[$key]['type'] = 2;
                }
            }else{
                if ($value['check_leader_1'] == $this->user_id) {
                    $check_user[$key]['type'] = 1;
                }else{
                    $check_user[$key]['type'] = 2;
                }
            }
            
            // 当前用户是否审核
            if ($value['apply_status'] != 0) {
                $check_user[$key]['is_check'] = 1;
                continue;
            }
            if ($value['check_leader_1'] == $value['check_leader_2']) {
                // 审核人1和审核人2是同一个人 状态为未审核
                $check_user[$key]['is_check'] = 0;
            }else{
                if ($value['check_leader_1'] == $this->user_id && $value['check_status_1'] == 1) {
                    $check_user[$key]['is_check'] = 1;
                }elseif ($value['check_leader_2'] == $this->user_id && $value['check_status_2'] == 1) {
                    $check_user[$key]['is_check'] = 1;
                }else{
                    $check_user[$key]['is_check'] = 0;
                }
            }
        }
        $content = Db::name('article')->where('cat_id = 3')->value('content');

        if (IS_AJAX) {
            return $this->fetch('user/ajax_check_list',[
                'check_user' => $check_user,
                'user_id'    => $this->user_id,
            ]);
        }
        return $this->fetch('user/check_level',[
            'check_user' => $check_user,
            'content'    => htmlspecialchars_decode($content),
        ]);
        
    }
    

    /**
     * 历史审核记录
     */
    public function old_check(){

        $count = Db::name('ck_apply')->alias('A')
                -> where('check_leader_1 = '.$this->user_id.' and A.check_status_1 != 0 or A.check_leader_2 = '.$this->user_id.' and A.check_status_2 != 0')
                -> count();
        $Page = new Page($count, 10);

        $check_user = Db::name('ck_apply')->alias('A')
                -> field('A.*,B.nickname,B.mobile,B.wx_number,C.level_name,D.user_name,D.id_card')
                -> join('users B','A.user_id = B.user_id','left')
                -> join('user_level C','A.level = C.level_id','left')
                -> join('user_authentication D','A.user_id = D.user_id','left')
                -> where('check_leader_1 = '.$this->user_id.' and A.check_status_1 != 0 or A.check_leader_2 = '.$this->user_id.' and A.check_status_2 != 0')
                ->order('A.apply_time DESC')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();

        foreach ($check_user as $key => $value) {
            $check_user[$key]['user_name']  = $this->substr_cut($value['user_name']);
            $check_user[$key]['id_card']  = substr_replace($value['id_card'],'**********',4,10);
            $check_user[$key]['is_check'] = 1;
        }
        if (IS_AJAX) {
            return $this->fetch('user/ajax_check_list',[
                'check_user' => $check_user,
                'user_id'    => $this->user_id,
            ]);
        }
        return $this->fetch('user/old_check',[
            'check_user' => $check_user,
            'user_id'    => $this->user_id,
        ]);
    }

    /**
     * 用户默认地址
     */
    public function user_address(){
        $address_info = Db::name('user_address')->where(['user_id'=>$this->user_id,'is_default'=>1])->find();
        if($address_info){
            $province = Db::name('region')->where('id',$address_info['province'])->value('name');
            $city     = Db::name('region')->where('id',$address_info['city'])->value('name');
            $district = Db::name('region')->where('id',$address_info['district'])->value('name');
            $address_info['p_c_d'] = $province.' '.$city.' '.$district;
            $this->ajaxReturn(['status'=>1,'msg'=>'获取成功','data'=>$address_info]);
        }else{
            $this->ajaxReturn(['status'=>0,'msg'=>'获取失败','data'=>'']);
        }

    }

    /**
     * 正在审核中
     */
    public function applying(){
        $id = input('id');
        if(empty($id)) $this->ajaxReturn(['status'=>0,'msg'=>'请传入参数','data'=>'']);
        $apply = Db::name('ck_apply')->alias('A')
            -> field('A.*,B.nickname as nickname_1,B.mobile as mobile_1,B.wx_number as wx_1,C.nickname as nickname_2,C.mobile as mobile_2,C.wx_number as wx_2,D.id_card as id_card1,E.id_card as id_card2,D.user_name as user_name1,E.user_name as user_name2')
            -> join('users B','A.check_leader_1 = B.user_id','left')
            -> join('users C','A.check_leader_2 = C.user_id','left')
            -> join('user_authentication D','A.check_leader_1 = D.user_id','left')
            -> join('user_authentication E','A.check_leader_2 = E.user_id','left')
            -> where('A.id',$id)
            -> find();

        $apply['user_name1']  = $this->substr_cut($apply['user_name1']);
        $apply['user_name2']  = $this->substr_cut($apply['user_name2']);

        $apply['id_card1']  = substr_replace($apply['id_card1'],'**********',4,10);
        $apply['id_card2']  = substr_replace($apply['id_card2'],'**********',4,10);

        $usersModel = Db('users');
        foreach ($apply as $key => $val) {

            $check_leader_level = $usersModel->alias('t1')->field('t2.level_name')->join('user_level t2','t1.level = t2.level_id')->where('t1.user_id',$apply['check_leader_1'])->find();
            $apply['check_leader_level']  = $check_leader_level['level_name'];

            if(!empty($apply['check_leader_2'])){
                $check_leader2_level = $usersModel->alias('t1')->field('t2.level_name')->join('user_level t2','t1.level = t2.level_id')->where('t1.user_id',$apply['check_leader_2'])->find();
                $apply['check_leader2_level']  = $check_leader2_level['level_name'];
            }
        }
        return $this->fetch('user/applying',[
            'apply' => $apply,
        ]);

    }
    /**
     * 验证升级条件是否满足
     * $user_id 用户ID
     * $level 升级等级
     * time 19-07-29
     */
    public function check_recom_condition($user_id,$level){
        $condition_one = $condition_two = true;
        // 升级条件
        $condition = unserialize($level['recom_condition']);
        if ($condition['direct_num'] > 0) {        
            // 直推达标人数
            $direct_where = [
                'first_leader' => $user_id,
                'level' => ['>=',$condition['direct_level']]
            ];
            $direct_num = M('users')->where($direct_where)->count();
            if ($direct_num < $condition['direct_num']) {
                $condition_one = false;
            }
        }
        if ($condition['team_num'] > 0) {
            // 团队达标人数
            $all_sub = get_team_all_user($user_id,$condition['team_level'],[]);    
            $all_subs = []; // 二维数组合并成一维数组
            array_walk_recursive($all_sub, function($value2) use (&$all_subs) {
                array_push($all_subs, $value2);
            });
            $team_num = count($all_subs);

            if ($team_num < $condition['team_num']) {
                $condition_two = false;
            }
        }
        if ($condition_one && $condition_two) {
            return ['code' => 1];
        }else{
            return ['code' => 0];
        }
    }

    /**
     * 替换中文汉字
     * @author MEI
     */
    public function substr_cut($user_name){
        if (!$user_name) return false;
        $strlen = mb_strlen($user_name, 'utf-8');
        $firstStr = mb_substr($user_name,0,1,'utf-8');
        $lastStr = mb_substr($user_name, -1,1,'utf-8');

        if ($strlen == 2) {
            $text = $firstStr.str_repeat('*', mb_strlen($user_name,'utf-8')-1);
        }elseif ($strlen == 3) {
            $text = $firstStr.str_repeat('*', $strlen-($strlen-1)).$lastStr;
        }else{
            $text = $firstStr.str_repeat('**', $strlen-($strlen-1)).$lastStr;
        }
        return $text;
    }
    /**
     * 付款信息
     * @author MEI
     */
    public function pay_detail($id,$type){
        $apply = M('ck_apply')->where(['id' => $id])->find();

        if ($type == 1) {
            $check_leader = $apply['check_leader_1'];
            $apply['img'] = $apply['voucher_img1'];
        }else{
            $check_leader = $apply['check_leader_2'];
            $apply['img'] = $apply['voucher_img2'];
        }
        $authent = M('user_authentication')->where(['user_id' => $check_leader])->find();
        $receipt = M('receipt_information')->where(['user_id' => $check_leader])->find();

        $apply['user_name']  = $this->substr_cut($authent['user_name']);
        $apply['id_card']  = substr_replace($authent['id_card'],'**********',4,10);
        $apply['account_number']  = $receipt['account_number'];
        $apply['account_code_img']  = $receipt['account_code_img'];
        $apply['receivables_name']  = $receipt['receivables_name'];

        $this->assign('appType',session('appType'));
        $this->assign('apply',$apply);
        $this->assign('type',$type);
        return $this->fetch('plan/pay_detail');
    }
    /**
     * 上传打款凭证
     * @author MEI
     */
    public function pay_voucher(){
        $upload_img = input('post.upload_img');
        $type = input('post.type');
        $id = input('post.id');

        if(empty($upload_img) && empty($_FILES['upload_img']['tmp_name'])){
            $this->error('请上传打款凭证');
        }
        if($upload_img){
            $img_src = $upload_img;
        }
        $MemberLogic  = new \app\common\logic\MemberLogic();
        if($_FILES['upload_img']['tmp_name']){//上传身份证正面
            $upload_img = $MemberLogic->upload_img('upload_img','plan');
            if($upload_img){
                $img_src = '/'.UPLOAD_PATH.'plan/'.$upload_img;
            }
        }
        $apply = M('ck_apply')->where(['id' => $id])->find();
        if ($type == 1) {
            $updata['voucher_img1'] = $img_src;
            $check_leader = $apply['check_leader_1'];
        }else{
            $updata['voucher_img2'] = $img_src;
            $check_leader = $apply['check_leader_2'];
        }
        $res = M('ck_apply')->where(['id' => $id])->update($updata);
        if ($res) {
            if (tpCache('shop_info.voucher_mess') == 1) {
                // 给审核人发送短信 刘雄杰
                $mobile = M('users')->where(['user_id' => $check_leader])->value('mobile');
                jh_message($mobile,Config::get('database.type_voucher'),'');
            }
            $this->success('上传成功',U('chuangke/CkUser/applying',['id' => $id]));
        }else{
            $this->error('上传失败');
        }
    }
    /**
     * 收款信息
     * @author MEI
     */
    public function rece_detail($id){
        $apply = M('ck_apply')->alias('A')
            ->field('A.*,B.user_name,B.id_card,C.account_number,C.receivables_name')
            ->join('user_authentication B','A.user_id = B.user_id')
            ->join('receipt_information C','A.user_id = C.user_id')
            ->where(['A.id' => $id])
            ->find();
        if ($apply['check_leader_1'] == $apply['check_leader_2'] ) {
            if ($apply['check_status_1'] < 1) {
                $apply['img'] = $apply['voucher_img1'];
            }elseif ($apply['check_status_2'] < 1) {
                $apply['img'] = $apply['voucher_img2'];
            }else{
                $this->error('数据错误');
            }
        }else{
            if ($apply['check_leader_1'] == $this->user_id) {
            $apply['img'] = $apply['voucher_img1'];
            }elseif ($apply['check_leader_2'] == $this->user_id) {
                $apply['img'] = $apply['voucher_img2'];
            }else{
                $this->error('数据错误');
            }
        }
        
        $apply['user_name']  = $this->substr_cut($apply['user_name']);
        $apply['id_card']  = substr_replace($apply['id_card'],'**********',4,10);

        $this->assign('apply',$apply);
        return $this->fetch('plan/rece_detail');
    }
    
}