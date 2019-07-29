<?php

namespace app\chuangke\controller;

use app\common\logic\CartLogic;
use app\common\logic\OrderLogic;
use app\common\logic\UsersLogic;
use app\common\model\Users;
use think\Db;
use app\mobile\controller\MobileBase;

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

        //获取用户等级信息
        $user = Users::get($this->user_id);

        //是否存在用户
        if(!$user) $this->ajaxReturn(['status'=>0,'msg'=>'用户不存在']);
        //if($user['level'] == 1) $this->ajaxReturn(['status'=>0,'msg'=>'普通用户没有提交申请的权利']);

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
        $next_level = Db::name('user_level')->where('level_id',$user['level'] + 1)->field('level_id,level_name,need_num,recom_condition')->find();
        if(empty($next_level)) $this->ajaxReturn(['status'=>0,'msg'=>'没有下一个等级信息']);

        //是否已有等级在审核中
        $count = Db::name('ck_apply')->where(['user_id'=>$user['user_id'],'level'=>$next_level['level_id'],'apply_status'=>0])->count();
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

        //满足审核条件的用户
        $check_id = 0;
        foreach ($leader as $k=>$v){
            if($k < ($next_level['level_id']-1)) continue;//升级N星则从N层开始找，N-1层直接跳过

            $level = Db::name('users')->where('user_id',$v)->value('level');
            if($level >= $next_level['level_id']){
                $check_id = $v;
                break;
            }
            break;//第N层找不到直接退出循环
        }

        //特殊情况 第N层找不到满足条件的用户，直接分配管理员
        if(empty($check_id)){
            //关系链最近的N星管理员
            $check_user = Db::name('users')->where(['user_id'=>['In',implode(',',$leader)],'level'=>$next_level['level_id'],'user_type'=>1])->value('user_id');
            if($check_user){
                $check_id = $check_user;
            }else{
                //关系链上没有N星管理员则选择平台的N星管理员
                $check_user = Db::name('users')->where(['level'=>$next_level['level_id'],'user_type'=>1])->value('user_id');
                if($check_user){
                    $check_id = $check_user;
                }else{
                    $this->ajaxReturn(['status'=>0,'msg'=>'平台没有'.($next_level['level_id']-1).'星管理员']);
                }
            }
        }

        //如果是四星升级到五星 需要增加一个九星星身份的审核
        if($next_level['level_id'] == 2 || $next_level['level_id'] == 6){
            //满足审核条件的用户
            $check_id_2 = 0;
            foreach ($leader as $k=>$v){
                if($k < 10) continue;//从第十层开始找
                //如果第九层有九星以上身份则选择该身份，否则直接退出循环
                $level = Db::name('users')->where('user_id',$v)->value('level');
                if($level >= 11){
                    $check_id_2 = $v;
                    break;
                }
                break;
            }
            //特殊情况 第九层没有九星以上身份 则分配管理员
            if(empty($check_id_2)){
                //关系链最近的九星管理员
                $check_user = Db::name('users')->where(['user_id'=>['In',implode(',',$leader)],'level'=>10,'user_type'=>1])->value('user_id');
                if($check_user){
                    $check_id_2 = $check_user;
                }else{
                    //关系链上没有九星管理员则选择平台的九星管理员
                    $check_user = Db::name('users')->where(['level'=>10,'user_type'=>1])->value('user_id');
                    if($check_user){
                        $check_id_2 = $check_user;
                    }else{
                        $this->ajaxReturn(['status'=>0,'msg'=>'平台没有九星管理员']);
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

        $res = Db::name('ck_apply')->add($data);
        if($res){
            $this->ajaxReturn(['status'=>1,'msg'=>'添加审核成功','data'=>$res]);
        }else{
            $this->ajaxReturn(['status'=>0,'msg'=>'添加审核失败']);
        }



    }

	public function addOrder(){
		if($this->request->isPost()){
			$data = input('post.');
			$user = Users::get($this->user_id);

			//判断是否前往购买商品升级 是否满足条件
			// var_dump($user['distribut_money']);die;
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
			// var_dump($validate1);die;
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
            //第一层领导审核
            $updata = array();
            if($info['check_leader_1'] == $this->user_id){
                if(empty($info['check_leader_2']))  $updata['apply_status'] = $status;
                if(!empty($info['check_status_2'])) $updata['apply_status'] = $status;
                $updata['check_status_1'] = $status;
                $updata['check_time_1']   = time();
                $res = Db::name('ck_apply')->where('id',$id)->save($updata);
                if($res){
                    if($updata['apply_status'] == 1){
                        //审核通过
                        $res1 = Db::name('users')->where('user_id',$info['user_id'])->setField('level',$info['level']);
                    }
                    $this->ajaxReturn(['status'=>1,'msg'=>'审核成功']);
                }
                $this->ajaxReturn(['status'=>0,'msg'=>'审核失败']);
            }

            //第二层领导审核
            $updata = array();
            if($info['check_leader_2'] == $this->user_id){
                if(!empty($info['check_status_1'])) $updata['apply_status'] = $status;
                $updata['check_status_2'] = $status;
                $updata['check_time_2']   = time();
                $res = Db::name('ck_apply')->where('id',$id)->save($updata);
                if($res){
                    if($updata['apply_status'] == 1){
                        //审核通过
                        $res1 = Db::name('users')->where('user_id',$info['user_id'])->setField('level',$info['level']);
                    }
                    $this->ajaxReturn(['status'=>1,'msg'=>'审核成功']);
                }
                $this->ajaxReturn(['status'=>0,'msg'=>'审核失败']);
            }

        }

	    $check_user = Db::name('ck_apply')->alias('A')
            -> field('A.*,B.nickname,B.mobile,B.wx_number,C.level_name')
            -> join('users B','A.user_id = B.user_id','left')
            -> join('user_level C','A.level = C.level_id','left')
            -> where('check_leader_1 = '.$this->user_id.' and A.check_status_1 = 0 or A.check_leader_2 = '.$this->user_id.' and A.check_status_2 = 0')
            -> select();

	    $content = Db::name('article')->where('cat_id = 3')->value('content');
        
        return $this->fetch('user/check_level',[
            'check_user' => $check_user,
            'content'    => htmlspecialchars_decode($content),
        ]);
    }

    /**
     * 历史审核记录
     */
    public function old_check(){

        $check_user = Db::name('ck_apply')->alias('A')
            -> field('A.*,B.nickname,B.mobile,B.wx_number')
            -> join('users B','A.user_id = B.user_id','left')
            -> where('A.check_leader_1 = '.$this->user_id.' and A.check_status_1 != 0 or A.check_leader_2 = '.$this->user_id.' and A.check_status_2 != 0')
            -> order('A.id desc')
            -> select();

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
            -> field('A.*,B.nickname as nickname_1,B.mobile as mobile_1,B.wx_number as wx_1,C.nickname as nickname_2,C.mobile as mobile_2,C.wx_number as wx_2')
            -> join('users B','A.check_leader_1 = B.user_id','left')
            -> join('users C','A.check_leader_2 = C.user_id','left')
            -> where('A.id',$id)
            -> find();
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
    


}