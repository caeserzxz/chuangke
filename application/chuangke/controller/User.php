<?php

namespace app\chuangke\controller;

use app\common\logic\CartLogic;
use app\common\logic\DistributLogic;
use app\common\logic\MessageLogic;
use app\common\logic\UsersLogic;
use app\common\logic\OrderLogic;
use app\common\logic\CouponLogic;
use app\common\model\Order;
use app\common\model\Users;
use app\mobile\controller\MobileBase;
use think\Page;
use think\Request;
use think\Verify;
use think\db;

class User extends MobileBase
{

    public $user_id = 0;
    public $user = array();

    /*
    * 初始化操作
    */
    public function _initialize()
    {
        parent::_initialize();
        if (session('?user')) {
            $user = session('user');
            $user = M('users')->where("user_id", $user['user_id'])->find();
            session('user', $user);  //覆盖session 中的 user
            $this->user = $user;
            $this->user_id = $user['user_id'];
            //初始化账户信息
            DistributLogic::rebateDivide($this->user_id);   //初始获取分佣情况

            $this->assign('user', $user); //存储用户信息
        }
        $nologin = array(
            'login', 'pop_login', 'do_login', 'logout', 'verify', 'set_pwd', 'finished',
            'verifyHandle', 'reg', 'send_sms_reg_code', 'find_pwd', 'check_validate_code',
            'forget_pwd', 'check_captcha', 'check_username', 'send_validate_code', 'express' , 'bind_guide', 'bind_account',
        );
        $is_bind_account = tpCache('basic.is_bind_account');

        if (!$this->user_id && !in_array(ACTION_NAME, $nologin)) {
            if(strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger') && $is_bind_account){
                header("location:" . U('chuangke/User/bind_guide'));//微信浏览器, 调到绑定账号引导页面
            }else{
                header("location:" . U('chuangke/User/login'));
            }
            exit;
        }

        $order_status_coment = array(
            'WAITPAY' => '待付款 ', //订单查询状态 待支付
            'WAITSEND' => '待发货', //订单查询状态 待发货
            'WAITRECEIVE' => '待收货', //订单查询状态 待收货
            'WAITCCOMMENT' => '待评价', //订单查询状态 待评价
        );
        $this->assign('order_status_coment', $order_status_coment);
    }



    /*
     * 用户中心首页
     */
    public function index()
    {
        // Session::clear();
        
        
        
        $user_id =$this->user_id;

        $user_code = Db::name('tuijian_code')->where('user_id',$user_id)->value('code');

        if(empty($user_code)){

            $code = $this->getWelcode();

            Db::name('tuijian_code')->save(['user_id'=>$user_id,'code'=>$code]);

        }

        $kefu_mobile = tpCache('shop_info.qq3'); // 客服联系方式
        $title_name = tpCache('shop_info.store_name'); // 网站名称

        $logic = new UsersLogic();
        $user = $logic->get_info($user_id); //当前登录用户信息
        $comment_count = M('comment')->where("user_id", $user_id)->count();   // 我的评论数
        $level_name = M('user_level')->where("level_id", $this->user['level'])->getField('level_name'); // 等级名称
	    //公告
        $notice = Db::name('article')->where('cat_id = 2')->order('add_time desc')->field('article_id,title')->select();

        //是否提交审核
        $apply = Db::name('ck_apply')->where(['user_id'=>$user_id,'apply_status'=>0])->find();

        //获取用户信息的数量
        $messageLogic = new MessageLogic();
        $user_message_count = $messageLogic->getUserMessageCount();
        $this->assign('user_message_count', $user_message_count);
        $this->assign('level_name', $level_name);
        $this->assign('comment_count', $comment_count);
        $this->assign('user',$user['result']);
        $this->assign('user_code',$user_code);
        $this->assign('title','个人中心');
        $this->assign('kefu_mobile',$kefu_mobile);
        $this->assign('title_name',$title_name);
        $this->assign('notice',$notice);
        $this->assign('apply',$apply);
        //查询购物车商品数量
        $cartLogic=new \app\common\logic\CartLogic;
        $user = session('user');
        $cartLogic->setUserId($user['user_id']);
        $this->assign('cart_goods_num', $cartLogic->getUserCartGoodsNum());
        return $this->fetch();
    }

    //公告列表
    public function notice_list(){
        $notice = Db::name('article')->where('cat_id = 2')->order('add_time desc')->field('article_id,title')->select();
        $this->assign('notice',$notice);
        return $this->fetch();
    }

    /*帮助界面*/
    public function notice_detail(){

        $cat_id = I('id');
        $article = Db::name('article')->where('article_id',$cat_id)->find();
        $help_content = htmlspecialchars_decode($article['content']); // 帮助内容

        $this->assign('help_content',$help_content);
        $this->assign('help_title',$article['title']);
        return $this->fetch();
    }


    //递归推荐码不重复
    public function getWelcode(){
        $code = $this->myRand();
        $isexist = Db::name('tuijian_code')->where('code',$code)->find();
        if(empty($isexist)){
           return $code;
        }else{
           return $this->getWelcode();
        }
    }

    //生成推荐码
    public function myRand(){
        if(PHP_VERSION < '4.2.0'){
            srand();
        }
        $randArr = array();
        for($i = 0; $i < 3; $i++){
            $randArr[$i] = rand(0, 9);
            $randArr[$i + 3] = chr(rand(0, 25) + 97);
        }
        shuffle($randArr);
        return implode('', $randArr);
    }

    /*支付微信支付宝银行卡账户信息*/

    public function user_account(){

        $user_id =$this->user_id;
            
        // $payment_list = M('user_payment')->where('user_id',$user_id)->order('id desc')->select();
        
        // foreach($payment_list as $k=>$v){
        //     switch($v['type']){
        //         case 1: $payment_list[$k]['pay_name']="支付宝"; break;
        //         case 2: $payment_list[$k]['pay_name']="微信"; break;
        //         case 3: $payment_list[$k]['pay_name']="银行卡"; break;
        //         default:$payment_list[$k]['pay_name']=" ";
        //     }
        // }

	
	//支付宝
        $payment_alipay = M('user_payment')->where(['user_id'=>$user_id,'type'=>1])->find();
        //微信
        $payment_weixin = M('user_payment')->where(['user_id'=>$user_id,'type'=>2])->find();
        //银行卡
        $payment_bank = M('user_payment')->where(['user_id'=>$user_id,'type'=>3])->find();

        
        $this->assign('payment_alipay',$payment_alipay);
        $this->assign('payment_weixin',$payment_weixin);
        $this->assign('payment_bank',$payment_bank);
      
        return $this->fetch();
    }

    /*团队*/
    public function team_list(){

        $user_id =$this->user_id;

        $all_arr = all_leader_arr($user_id);

        $team_list = [];
        $total = 0;
        foreach($all_arr as $key => $val){
            $ceng = numToWord($key + 1);

            $team_list[$ceng] = $val;
            $total += $val;
        }
        $total = array_sum($team_list);
        $this->assign('team_list',$team_list);
        $this->assign('total',$total);
        return $this->fetch();
    }

    /*团队信息*/
    public function team_user(){
    	$level = I('level', '0');
        $user_id =$this->user_id;

        $leader_str = all_leader_arr($user_id,[],$level);
        $team_user = [];
        if(!empty($leader_str)){
            $team_user = Db('users')->field('user_id,nickname,mobile,head_pic,level,reg_time,wx_number')->where(['user_id'=>['In',$leader_str]])->select();
            foreach($team_user as $key => $val){
                $start = numToWord($val['level'] - 1);
                if($start == '零'){
                    $team_user[$key]['start'] = '注册会员';
                }else{
                    $team_user[$key]['start'] = $start.'星';
                }
               
                $team_user[$key]['reg_time'] = date('Y-m-d H:i:s',$val['reg_time']);
            }
        }
        $this->assign('team_user',$team_user);
        return $this->fetch();
    }

    /*奖金收入明细*/
    public function income_log(){
        $type = I('type', 'all');
        $usersLogic = new UsersLogic;
        $result = $usersLogic->account($this->user_id, $type);
        $this->assign('type', $type);
        $this->assign('account_log', $result['account_log']);
        return $this->fetch();
    }

     /*协助注册*/
    public function assist_reg(){
        $user_id = $this->user_id;
        if ($this->request->isAjax()) {
            $logic = new UsersLogic();
            //验证码检验
            //$this->verifyHandle('user_reg');
            $data = [];
            $data['nickname']   = I('post.nickname', '');
            $data['mobile']     = I('post.mobile', '');
            $data['wx_number']  = I('post.wx_number', '');
            $password   = I('post.password', '');
            $invite     = I('post.invite','');


            if(empty($data['mobile'])){
                $res = array('status'=>-1,'msg'=>'手机号不能为空'); 
                $this->ajaxReturn($res);
                exit; 
            }
	        if(empty($data['wx_number'])){
                $res = array('status'=>-1,'msg'=>'微信号不能为空'); 
                $this->ajaxReturn($res);
                exit; 
            }
            if(empty($data['nickname'])){
                $res = array('status'=>-1,'msg'=>'用户名不能为空'); 
                $this->ajaxReturn($res);
                exit; 
            }
            if(empty($password)){
                $res = array('status'=>-1,'msg'=>'密码不能为空'); 
                $this->ajaxReturn($res);
                exit; 
            }

            $is_mobile = Db('users')->where('mobile',$data['mobile'])->value('user_id');
            if($is_mobile){
                $res = array('status'=>-1,'msg'=>'手机号已注册'); 
                $this->ajaxReturn($res);
                exit; 
            }

            if(!empty($invite)){
                // $invite = get_user_info($invite,2);//根据手机号查找邀请人
                $data['first_leader'] = M('tuijian_code')->where('code',$invite)->value('user_id');
                if(empty($data['first_leader'])){
                   $res = array('status'=>-1,'msg'=>'推荐码错误，请重新输入','data'=>$invite); 
                   $this->ajaxReturn($res);
                   exit; 
                }
            }else{
                $res = array('status'=>-1,'msg'=>'请输入推荐码'); 
                $this->ajaxReturn($res);
                exit; 
            }
            
            //保存数据库
            $data['reg_time'] = time();
            $data['password'] = encrypt($password);
            $reg_id = M('users')->insertGetId($data);
            if($reg_id === false){
                $res = array('status'=>-1,'msg'=>'推荐码错误，请重新输入'); 
                $this->ajaxReturn($res);
                exit; 
            }

            //更新关系链
            $leader = Db::name('users')->where('user_id',$user_id)->value('leader_all');
            if($leader){
                $leader = $leader.'_'.$reg_id;
            }else{
                $leader = $reg_id;
            }
            Db::name('users')->where('user_id',$reg_id)->setField('leader_all',$leader);

            //获取拥有审核权的用户
            $leader_arr = explode('_',$leader);
            krsort($leader_arr);
            $leader_new = array_values($leader_arr);

            //满足审核条件的用户
            $check_id = 1;
            foreach ($leader_new as $k=>$v){
                if($k < 5) continue;//从第五层开始找
                //如果第五层有五星以为身份则选择该身份，否则直接退出循环
                $level = Db::name('users')->where('user_id',$v)->value('level');
                if($level == 6){
                    $check_id = $v;
                    break;
                }
                break;
            }

            //特殊情况 第五层没有五星以上身份 则分配管理员
            if(empty($check_id)){
                //关系链最近的五星管理员
                $check_user = Db::name('users')->where(['user_id'=>['In',implode(',',$leader_new)],'level'=>6,'user_type'=>1])->value('user_id');
                if($check_user){
                    $check_id = $check_user;
                }else{
                    //关系链上没有五星管理员则选择平台的五星管理员
                    $check_user = Db::name('users')->where(['level'=>6,'user_type'=>1])->value('user_id');
                    if($check_user){
                        $check_id = $check_user;
                    }else{
                        $this->ajaxReturn(['status'=>0,'msg'=>'平台没有五星管理员']);
                    }
                }
            }

            //添加到一星审核表
            /*$data = array();
            $data['user_id']        = $reg_id;
            $data['level']          = 2;
            $data['apply_time']     = time();
            $data['check_leader_1'] = $user_id;
            $data['shopping_type']  = 2;
            $data['check_leader_2'] = $check_id;
            $res = Db::name('ck_apply')->add($data);*/

            $code = getWelcode();
            Db::name('tuijian_code')->save(['user_id'=>$reg_id,'code'=>$code]);

            //添加代注册记录
            $assist_log_data = [
                'reg_id' => $reg_id,
                'assist_id' => $user_id,
                'create_time' => time()
            ];
            $reg_id = M('assist_reg')->insertGetId($assist_log_data);

            $res = array('status'=>1,'msg'=>'注册成功'); 
            $this->ajaxReturn($res);
            exit; 

        }

        //获取用户邀请码
        $invite_code = Db('tuijian_code')->where('user_id',$this->user_id)->value('code');

        $this->assign('invite_code',$invite_code);
        return $this->fetch();
    }

    /*注册记录*/
    public function reg_log()
    {
        $user_id = $this->user_id;
        $list = Db('assist_reg t1')
                    ->field('t1.create_time,t2.nickname,t2.mobile,t2.user_id')
                    ->join('users t2','t1.reg_id = t2.user_id','LEFT')
                    ->where('t1.assist_id',$user_id)->select();

        $this->assign('list',$list);
        return $this->fetch();
    }


    /*设置*/
    public function setsys(){

        $this->assign('level', $this->user['level']);
        return $this->fetch();
    }

    /*邀请*/
    public function share_code(){
        
        //获取用户邀请码
        $invite_code = Db('tuijian_code')->where('user_id',$this->user_id)->value('code');
        //获取用户邀请二维码
        $myqrcode = $this->myqrcode();  

        $this->assign('invite_code', $invite_code);
        $this->assign('myqrcode', $myqrcode);
        return $this->fetch();
    }

    /*反馈*/
    public function feedback(){

        return $this->fetch();
    }

    /*修改密码*/
    public function change_pass()
    {
        if (IS_POST) {
            $logic = new UsersLogic();
            $data = $logic->get_info($this->user_id);
            $user = $data['result'];
            if ($user['mobile'] == '' && $user['email'] == '')
                $this->ajaxReturn(['status' => -1, 'msg' => '请先绑定手机或邮箱', 'url' => U('/Mobile/User/index')]);
            $userLogic = new UsersLogic();
            $data = $userLogic->password($this->user_id, I('post.old_password'), I('post.new_password'), I('post.confirm_password'));
            if ($data['status'] == -1)
                $this->ajaxReturn(['status' => -1, 'msg' => $data['msg']]);
            $this->ajaxReturn(['status' => 1, 'msg' => $data['msg'], 'url' => U('/Mobile/User/index')]);
            exit;
        }
	
	$logic = new UsersLogic();
        $data = $logic->get_info($this->user_id);

        $this->assign('mobile', $data['result']['mobile']);
        return $this->fetch();
    }

    /*我的收入*/
    public function income()
    {
        $user = session('user');
        //获取账户资金记录
        $logic = new UsersLogic();
        $data = $logic->get_account_log($this->user_id, I('get.type'));
        $account_log = $data['result'];

        //支出
        $where = " and user_money < 0 ";
        $pent_price = Db::name('account_log')->field("sum(user_money) as user_money")->where("user_id=" . $this->user_id.$where)->find();

        $this->assign('user', $user);
        $this->assign('account_log', $account_log);
        $this->assign('pent_price', $pent_price);
        $this->assign('page', $data['show']);
        return $this->fetch();
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        setcookie('uname','',time()-3600,'/');
        setcookie('cn','',time()-3600,'/');
        setcookie('user_id','',time()-3600,'/');
        setcookie('PHPSESSID','',time()-3600,'/');
        //$this->success("退出成功",U('Mobile/Index/index'));
        header("Location:" . U('chuangke/User/login'));
        exit();
    }

    /*
     * 账户资金
     */
    public function account()
    {
        $user = session('user');
        //获取账户资金记录
        $logic = new UsersLogic();
        $data = $logic->get_account_log($this->user_id, I('get.type'));
        $account_log = $data['result'];
        $this->assign('user', $user);
        $this->assign('account_log', $account_log);
        $this->assign('page', $data['show']);
        if ($_GET['is_ajax']) {
            return $this->fetch('ajax_account_list');
            exit;
        }
        return $this->fetch();
    }

    public function account_list()
    {
        $type = I('type', 'all');
        $usersLogic = new UsersLogic;
        $result = $usersLogic->account($this->user_id, $type);
        $this->assign('type', $type);
        $this->assign('account_log', $result['account_log']);
        if ($_GET['is_ajax']) {
            return $this->fetch('ajax_account_list');
        }
        return $this->fetch();
    }

    public function account_detail(){
        $log_id = I('log_id/d', 0);
        $detail = Db::name('account_log')->where(['log_id' => $log_id])->find();
        $this->assign('detail', $detail);
        return $this->fetch();
    }



    /**
     *  登录
     */
    public function login()
    {
        if ($this->user_id > 0) header("Location: " . U('chuangke/User/index'));
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U("chuangke/User/index");
        $this->assign('referurl', $referurl);
        $this->assign('historyback', U('chuangke/User/index'));
        return $this->fetch();
    }

    /**
     * 登录
     */
    public function do_login()
    {
        $username = trim(I('post.username'));
        $password = trim(I('post.password'));

        //验证码验证
        if (isset($_POST['verify_code'])) {
            $verify_code = I('post.verify_code');
            $verify = new Verify();
            if (!$verify->check($verify_code, 'user_login')) {
                $res = array('status' => 0, 'msg' => '验证码错误');
                exit(json_encode($res));
            }
        }
        $logic = new UsersLogic();
        $res = $logic->login($username, $password);
        if ($res['status'] == 1) {
            $res['url'] = urldecode(I('post.referurl'));
            session('user', $res['result']);
            setcookie('user_id', $res['result']['user_id'], null, '/');
            setcookie('is_distribut', $res['result']['is_distribut'], null, '/');
            $nickname = empty($res['result']['nickname']) ? $username : $res['result']['nickname'];
            setcookie('uname', urlencode($nickname), null, '/');
            setcookie('cn', 0, time() - 3600, '/');
            $cartLogic = new CartLogic();
            $cartLogic->setUserId($res['result']['user_id']);
            $cartLogic->doUserLoginHandle();// 用户登录后 需要对购物车 一些操作
            $orderLogic = new OrderLogic();
            $orderLogic->setUserId($res['result']['user_id']);//登录后将超时未支付订单给取消掉
            $orderLogic->abolishOrder();
        }
        exit(json_encode($res));
    }

    /**
     *  注册
     */
    public function reg()
    {
        if ($this->request->isAjax()) {
            $logic = new UsersLogic();
            //验证码检验
            //$this->verifyHandle('user_reg');
            $data = [];
            $data['nickname']   = I('post.nickname', '');
            $data['mobile']     = I('post.mobile', '');
            $data['wx_number']  = I('post.wx_number', '');
            $password   = I('post.password', '');
            $invite     = I('post.invite','');

            if(empty($data['nickname'])){
                $res = array('status'=>-1,'msg'=>'用户名不能为空'); 
                $this->ajaxReturn($res);
                exit; 
            }
            if(empty($data['mobile'])){
                $res = array('status'=>-1,'msg'=>'手机号不能为空'); 
                $this->ajaxReturn($res);
                exit; 
            }
            if(empty($password)){
                $res = array('status'=>-1,'msg'=>'密码不能为空'); 
                $this->ajaxReturn($res);
                exit; 
            }

            $is_mobile = Db('users')->where('mobile',$mobile)->value('user_id');

            if(!empty($invite)){
                // $invite = get_user_info($invite,2);//根据手机号查找邀请人
                $data['first_leader'] = M('tuijian_code')->where('code',$invite)->value('user_id');
                if(empty($data['first_leader'])){
                   $res = array('status'=>-1,'msg'=>'推荐码错误，请重新输入','data'=>$invite); 
                   $this->ajaxReturn($res);
                   exit; 
                }
            }else{
                $res = array('status'=>-1,'msg'=>'请输入推荐码'); 
                $this->ajaxReturn($res);
                exit; 
            }
            
            //保存数据库
            $data['reg_time'] = time();
            $data['password'] = encrypt($password);
            $user_id = M('users')->insertGetId($data);
            $code = getWelcode();
            Db::name('tuijian_code')->save(['user_id'=>$user_id,'code'=>$code]);
            if($user_id === false){
                $res = array('status'=>-1,'msg'=>'推荐码错误，请重新输入'); 
                $this->ajaxReturn($res);
                exit; 
            }

            $user = M('users')->where("user_id", $user_id)->find();
           
            session('user', $user);
            setcookie('user_id', $user['user_id'], null, '/');
            setcookie('is_distribut', $user['is_distribut'], null, '/');
            $cartLogic = new CartLogic();
            $cartLogic->setUserId($user['user_id']);
            $cartLogic->doUserLoginHandle();// 用户登录后 需要对购物车 一些操作

            $res = array('status'=>1,'msg'=>'注册成功'); 
            $this->ajaxReturn($res);
            exit; 

        }

        //获取用户邀请码
        $invite_code = input('invite_code','');

        $this->assign('invite_code',$invite_code);
        return $this->fetch();
    }

    public function bind_guide(){
        $data = session('third_oauth');
        $this->assign("nickname", $data['nickname']);
        $this->assign("oauth", $data['oauth']);
        $this->assign("head_pic", $data['head_pic']);
        return $this->fetch();
    }



    /*
     * 用户地址列表
     */
    public function address_list()
    {
        cookie('url_cart2',request()->param());
        $address_lists = get_user_address_list($this->user_id);
        $region_list = get_region_list();
        $this->assign('region_list', $region_list);
        $this->assign('lists', $address_lists);
        return $this->fetch();
    }

    /*
     * 添加地址
     */
    public function add_address()
    {
        if (IS_POST) {
            $source = input('source');
            $post_data = input('post.');

            $pcd = explode(',',$post_data['area_codes']);

            list($post_data['province'],$post_data['city'],$post_data['district']) = $pcd;

            $logic = new UsersLogic();
            $data = $logic->add_address($this->user_id, 0, $post_data);
            $goods_id = input('goods_id/d');
            $item_id = input('item_id/d');
            $goods_num = input('goods_num/d');
            $order_id = input('order_id/d');
            $action = input('action');

            if($data['status'] == 1){

                $this->ajaxReturn(['status' => 1, 'msg' => "保存成功",'url'=>U('User/address_list')]);

            }else{

                $this->ajaxReturn(['status' => -1, 'msg' => "保存失败"]);
            }

            // if ($data['status'] != 1) {
            //     $this->error($data['msg']);
            // } elseif ($source == 'cart2') {
            //     $data['url'] = U('/Mobile/Cart/cart2', array('address_id' => $data['result'], 'goods_id' => $goods_id, 'goods_num' => $goods_num, 'item_id' => $item_id, 'action' => $source));
            //     $this->ajaxReturn($data);
            // } elseif ($_POST['source'] == 'integral') {
            //     $data['url'] = U('/Mobile/Cart/integral', array('address_id' => $data['result'], 'goods_id' => $goods_id, 'goods_num' => $goods_num, 'item_id' => $item_id));
            //     $this->ajaxReturn($data);
            // } elseif ($source == 'pre_sell_cart') {
            //     $data['url'] = U('/Mobile/Cart/pre_sell_cart', array('address_id' => $data['result'], 'act_id' => $post_data['act_id'], 'goods_num' => $post_data['goods_num']));
            //     $this->ajaxReturn($data);
            // } elseif ($source == 'team') {
            //     $data['url'] = U('/Mobile/Team/order', array('address_id' => $data['result'], 'order_id' => $order_id));
            //     $this->ajaxReturn($data);
            // } else {
            //     $data['url'] = U('/Mobile/User/address_list');
            //     $this->success($data['msg'], U('/Mobile/User/address_list'));
            // }
        }

        $p = M('region')->where(array('parent_id' => 0, 'level' => 1))->select();
        $this->assign('province', $p);
        //return $this->fetch('edit_address');
        return $this->fetch();

    }

    /*
     * 地址编辑
     */
    public function edit_address()
    {
        $id = I('id/d');
        $address = M('user_address')->where(array('address_id' => $id, 'user_id' => $this->user_id))->find();
        $address['addres'] = $address['province'].','.$address['city'].','.$address['district'];

        $province = M('region')->where('id',$address['province'])->value('name');
        $city     = M('region')->where('id',$address['city'])->value('name');
        $district = M('region')->where('id',$address['district'])->value('name');


        if (IS_POST) {

            $post_data = input('post.');

            $logic = new UsersLogic();

            $pcd = explode(',',$post_data['area_codes']);

            list($post_data['province'],$post_data['city'],$post_data['district']) = $pcd;

            $data = $logic->add_address($this->user_id,$post_data['id'],$post_data);
            
            if($data['status'] == 1){
                $this->ajaxReturn(['status' => 1, 'msg' => "编辑成功",'url'=>U('User/address_list')]);
            }else{
                $this->ajaxReturn(['status' => -1, 'msg' => "编辑失败"]);
            }
        }
        //获取省份
        $p = M('region')->where(array('parent_id' => 0, 'level' => 1))->select();
        $c = M('region')->where(array('parent_id' => $address['province'], 'level' => 2))->select();
        $d = M('region')->where(array('parent_id' => $address['city']))->select();

        if ($address['twon']) {
            $e = M('region')->where(array('parent_id' => $address['district'], 'level' => 4))->select();
            $this->assign('twon', $e);
        }

        $this->assign('province', $province.' '.$city.' '.$district);
//        $this->assign('city', $city);
//        $this->assign('district', $district);
        $this->assign('address', $address);
        $this->assign('id', $id);
        return $this->fetch();
    }

    /*
     * 设置默认收货地址
     */
    public function set_default()
    {
        $id = I('get.id/d');
        $source = I('get.source');
        M('user_address')->where(array('user_id' => $this->user_id))->save(array('is_default' => 0));
        $row = M('user_address')->where(array('user_id' => $this->user_id, 'address_id' => $id))->save(array('is_default' => 1));
        if ($source == 'cart2') {
            header("Location:" . U('Mobile/Cart/cart2'));
            exit;
        } else {
            header("Location:" . U('Mobile/User/address_list'));
        }
    }

    /*
     * 地址删除
     */
    public function del_address()
    {
        $id = I('get.id/d');

        $address = M('user_address')->where("address_id", $id)->find();
        $row = M('user_address')->where(array('user_id' => $this->user_id, 'address_id' => $id))->delete();
        // 如果删除的是默认收货地址 则要把第一个地址设置为默认收货地址
        if ($address['is_default'] == 1) {
            $address2 = M('user_address')->where("user_id", $this->user_id)->find();
            $address2 && M('user_address')->where("address_id", $address2['address_id'])->save(array('is_default' => 1));
        }
        if (!$row)
            $this->ajaxReturn(['status' => -1, 'msg' => "删除失败"]);
            // $this->error('操作失败', U('User/address_list'));
        else
            $this->ajaxReturn(['status' => 1, 'msg' => "删除成功"]);
            // $this->success("操作成功", U('User/address_list'));
    }

    public function set_default1()
    {
        $id = I('get.id/d');
        M('user_address')->where(array('user_id' => $this->user_id))->save(array('is_default' => 0));
        $row = M('user_address')->where(array('user_id' => $this->user_id, 'address_id' => $id))->save(array('is_default' => 1));
        if (!$row)
            $this->error('设置默认地址失败', U('User/address_list'));
        else
            $this->success("设置默认地址成功", U('User/address_list'));
    }

    /*
     * 个人信息
     */
    public function userinfo()
    {
        $userLogic = new UsersLogic();
        $user_info = $userLogic->get_info($this->user_id); // 获取用户信息
        $user_info = $user_info['result'];
        $level_name = M('user_level')->where("level_id", $user_info['level'])->getField('level_name'); // 等级名称
        $user_code = Db::name('tuijian_code')->where('user_id',$this->user_id)->value('code');
        if (IS_POST) {
                     
             
            $spare_mobile = I('post.spare_mobile');
            $alipay_number = I('post.alipay_number');
            $nickname = I('post.nickname');
            $wx_number = I('post.wx_number');
            

            // if(!empty($spare_mobile)){
            //     $a = M('users')->where(['spare_mobile' => $spare_mobile])->value('spare_mobile');
            //     $b = M('users')->where(['mobile' => $spare_mobile])->value('mobile');
            //     if($a || $b)
            //         $this->ajaxReturn(array('status'=>-1,'msg'=>'手机号码已被使用'));
            // }
            
            // if(!empty($alipay_number)){
            //     $c = M('users')->where(['alipay_number' => $alipay_number])->value('alipay_number');
            //     if($c)
            //         $this->ajaxReturn(array('status'=>-1,'msg'=>'支付账户已被使用'));   
            // }

            // if (!empty($mobile)) {
            //     $c = M('users')->where(['mobile' => input('post.mobile'), 'user_id' => ['<>', $this->user_id]])->count();
            //     $c && $this->error("手机已被使用");
            //     if (!$code) $this->error('请输入验证码');
            //     $check_code = $userLogic->check_validate_code($code, $mobile, 'phone', $this->session_id, $scene);
            //     if ($check_code['status'] != 1) $this->error($check_code['msg']);
            // }
            // if (!$userLogic->update_info($this->user_id, $post)) $this->error("保存失败");
            // setcookie('uname', urlencode($post['nickname']), null, '/');
            M('users')->where('user_id',$this->user_id)->update(array('spare_mobile'=>$spare_mobile,'alipay_number'=>$alipay_number,'nickname'=>$nickname,'wx_number'=>$wx_number));
            $this->ajaxReturn(array('status'=>1,'msg'=>'操作成功'));
            exit;
        }
        //  获取省份
        // $province = M('region')->where(array('parent_id' => 0, 'level' => 1))->select();
        //  获取订单城市
        // $city = M('region')->where(array('parent_id' => $user_info['province'], 'level' => 2))->select();
        //  获取订单地区
        // $area = M('region')->where(array('parent_id' => $user_info['city'], 'level' => 3))->select();
        // $this->assign('province', $province);
        // $this->assign('city', $city);
        // $this->assign('area', $area);
        $this->assign('user', $user_info);
        $this->assign('level_name', $level_name);
        $this->assign('user_code', $user_code);
        // $this->assign('sex', C('SEX'));
        //从哪个修改用户信息页面进来，
        $dispaly = I('action');
        if ($dispaly != '') {
            return $this->fetch("$dispaly");
        }
        return $this->fetch();
    }

    /**
     * 修改绑定手机
     * @return mixed
     */
    public function setMobile(){
        $userLogic = new UsersLogic();
        if (IS_POST) {
            $mobile = input('mobile');
            $mobile_code = input('mobile_code');
            $scene = input('post.scene', 6);
            $validate = I('validate',0);
            $status = I('status',0);
            $c = Db::name('users')->where(['mobile' => mobile, 'user_id' => ['<>', $this->user_id]])->count();
            $c && $this->error('手机已被使用');
            if (!$mobile_code)
                $this->error('请输入验证码');
            $check_code = $userLogic->check_validate_code($mobile_code, $mobile, 'phone', $this->session_id, $scene);
            if($check_code['status'] !=1){
                $this->error($check_code['msg']);
            }
            if($validate == 1 & $status == 0){
                $res = Db::name('users')->where(['user_id' => $this->user_id])->update(['mobile'=>$mobile]);
                if($res){
                    $this->success('修改成功',U('User/userinfo'));
                }
                $this->error('修改失败');
            }
        }

        $view = !input('step') ? '' : 'setMobile2';
        $this->assign('status',$status);
        return $this->fetch($view);
    }



    /*
    * 手机验证
    */
    public function mobile_validate()
    {
        $userLogic = new UsersLogic();
        $user_info = $userLogic->get_info($this->user_id); // 获取用户信息
        $user_info = $user_info['result'];
        $step = I('get.step', 1);
        //验证是否未绑定过
        if ($user_info['mobile_validated'] == 0)
            $step = 2;
        //原手机验证是否通过
        if ($user_info['mobile_validated'] == 1 && session('mobile_step1') == 1)
            $step = 2;
        if ($user_info['mobile_validated'] == 1 && session('mobile_step1') != 1)
            $step = 1;
        if (IS_POST) {
            $mobile = I('post.mobile');
            $code = I('post.code');
            $info = session('mobile_code');
            if (!$info)
                $this->error('非法操作');
            if ($info['email'] == $mobile || $info['code'] == $code) {
                if ($user_info['email_validated'] == 0 || session('email_step1') == 1) {
                    session('mobile_code', null);
                    session('mobile_step1', null);
                    if (!$userLogic->update_email_mobile($mobile, $this->user_id, 2))
                        $this->error('手机已存在');
                    $this->success('绑定成功', U('Home/User/index'));
                } else {
                    session('mobile_code', null);
                    session('email_step1', 1);
                    redirect(U('Home/User/mobile_validate', array('step' => 2)));
                }
                exit;
            }
            $this->error('验证码手机不匹配');
        }
        $this->assign('step', $step);
        return $this->fetch();
    }



    /**
     * 我的留言
     */
    public function message_list()
    {
        C('TOKEN_ON', true);
        if (IS_POST) {
            if(!$this->verifyHandle('message')){
                $this->error('验证码错误', U('User/message_list'));
            };

            $data = I('post.');
            $data['user_id'] = $this->user_id;
            $user = session('user');
            $data['user_name'] = $user['nickname'];
            $data['msg_time'] = time();
            if (M('feedback')->add($data)) {
                $this->success("留言成功", U('User/message_list'));
                exit;
            } else {
                $this->error('留言失败', U('User/message_list'));
                exit;
            }
        }
        $msg_type = array(0 => '留言', 1 => '投诉', 2 => '询问', 3 => '售后', 4 => '求购');
        $count = M('feedback')->where("user_id", $this->user_id)->count();
        $Page = new Page($count, 100);
        $Page->rollPage = 2;
        $message = M('feedback')->where("user_id", $this->user_id)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $showpage = $Page->show();
        header("Content-type:text/html;charset=utf-8");
        $this->assign('page', $showpage);
        $this->assign('message', $message);
        $this->assign('msg_type', $msg_type);
        return $this->fetch();
    }

    /**账户明细*/
    public function points()
    {
        $type = I('type', 'all');    //获取类型
        $this->assign('type', $type);
        if ($type == 'recharge') {
            //充值明细
            $count = M('recharge')->where("user_id", $this->user_id)->count();
            $Page = new Page($count, 16);
            $account_log = M('recharge')->where("user_id", $this->user_id)->order('order_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        } else if ($type == 'points') {
            //积分记录明细
            $count = M('account_log')->where(['user_id' => $this->user_id, 'pay_points' => ['<>', 0]])->count();
            $Page = new Page($count, 16);
            $account_log = M('account_log')->where(['user_id' => $this->user_id, 'pay_points' => ['<>', 0]])->order('log_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        } else {
            //全部
            $count = M('account_log')->where(['user_id' => $this->user_id])->count();
            $Page = new Page($count, 16);
            $account_log = M('account_log')->where(['user_id' => $this->user_id])->order('log_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        }
        $showpage = $Page->show();
        $this->assign('account_log', $account_log);
        $this->assign('page', $showpage);
        $this->assign('listRows', $Page->listRows);
        if ($_GET['is_ajax']) {
            return $this->fetch('ajax_points');
            exit;
        }
        return $this->fetch();
    }




    /*
     * 密码修改
     */
    public function password()
    {
        if (IS_POST) {
            $logic = new UsersLogic();
            $data = $logic->get_info($this->user_id);
            $user = $data['result'];
            if ($user['mobile'] == '' && $user['email'] == '')
                $this->ajaxReturn(['status' => -1, 'msg' => '请先绑定手机或邮箱', 'url' => U('/Mobile/User/index')]);
            $userLogic = new UsersLogic();
            $data = $userLogic->password($this->user_id, I('post.old_password'), I('post.new_password'), I('post.confirm_password'));
            if ($data['status'] == -1)
                $this->ajaxReturn(['status' => -1, 'msg' => $data['msg']]);
            $this->ajaxReturn(['status' => 1, 'msg' => $data['msg'], 'url' => U('/chuangke/User/index')]);
            exit;
        }
        return $this->fetch();
    }

    function forget_pwd()
    {
        if ($this->user_id > 0) {
            $this->redirect("User/index");
        }
        $username = I('username');
        if (IS_POST) {
            if (!empty($username)) {
                if (!$this->verifyHandle('forget')) {
                    $this->error("验证码错误");
                };
                $field = 'mobile';
                if (check_email($username)) {
                    $field = 'email';
                }
                //$user = M('users')->where("email", $username)->whereOr('mobile', $username)->find();
                $user = M('users')->where('mobile', $username)->find();
                if ($user) {
                    session('find_password', array('user_id' => $user['user_id'], 'username' => $username,
                        'email' => $user['email'], 'mobile' => $user['mobile'], 'type' => $field));
                    header("Location: " . U('User/find_pwd'));
                    exit;
                } else {
                    $this->error("手机号码不存在，请检查");
                }
            }
        }
        return $this->fetch();
    }

    function find_pwd()
    {
        if ($this->user_id > 0) {
            header("Location: " . U('User/index'));
        }
        $user = session('find_password');
        if (empty($user)) {
            $this->error("请先验证手机号码", U('User/forget_pwd'));
        }
        $this->assign('user', $user);
        return $this->fetch();
    }


    public function set_pwd()
    {
        if ($this->user_id > 0) {
            $this->redirect('Mobile/User/index');
        }
        $check = session('validate_code');
        if (empty($check)) {
            header("Location:" . U('User/forget_pwd'));
        } elseif ($check['is_check'] == 0) {
            $this->error('验证码还未验证通过', U('User/forget_pwd'));
        }
        if (IS_POST) {
            $password = I('post.password');
            $password2 = I('post.password2');
            if ($password2 != $password) {
                $this->error('两次密码不一致', U('User/forget_pwd'));
            }
            if ($check['is_check'] == 1) {
                $user = M('users')->where("mobile", $check['sender'])->whereOr('email', $check['sender'])->find();
                M('users')->where("user_id", $user['user_id'])->save(array('password' => encrypt($password)));
                session('validate_code', null);
                return $this->fetch('reset_pwd_sucess');
                exit;
            } else {
                $this->error('验证码还未验证通过', U('User/forget_pwd'));
            }
        }
        $is_set = I('is_set', 0);
        $this->assign('is_set', $is_set);
        return $this->fetch();
    }

    /**
     * 验证码验证
     * $id 验证码标示
     */
    private function verifyHandle($id)
    {
        $verify = new Verify();
        if (!$verify->check(I('post.verify_code'), $id ? $id : 'user_login')) {
            return false;
        }
        return true;
    }

    /**
     * 验证码获取
     */
    public function verify()
    {
        //验证码类型
        $type = I('get.type') ? I('get.type') : 'user_login';
        $config = array(
            'fontSize' => 30,
            'length' => 4,
            'imageH' =>  60,
            'imageW' =>  300,
            'fontttf' => '5.ttf',
            'useCurve' => true,
            'useNoise' => false,
        );
        $Verify = new Verify($config);
        $Verify->entry($type);
		exit();
    }

    /**
     * 账户管理
     */
    public function accountManage()
    {
        return $this->fetch();
    }

    public function recharge()
    {
        $order_id = I('order_id/d');
        $paymentList = M('Plugin')->where("`type`='payment' and code!='cod' and status = 1 and  scene in(0,1)")->select();
        //微信浏览器
        if (strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
            $paymentList = M('Plugin')->where("`type`='payment' and status = 1 and code='weixin'")->select();
        }
        $paymentList = convert_arr_key($paymentList, 'code');

        foreach ($paymentList as $key => $val) {
            $val['config_value'] = unserialize($val['config_value']);
            if ($val['config_value']['is_bank'] == 2) {
                $bankCodeList[$val['code']] = unserialize($val['bank_code']);
            }
        }
        $bank_img = include APP_PATH . 'home/bank.php'; // 银行对应图片
        $payment = M('Plugin')->where("`type`='payment' and status = 1")->select();
        $this->assign('paymentList', $paymentList);
        $this->assign('bank_img', $bank_img);
        $this->assign('bankCodeList', $bankCodeList);

        if ($order_id > 0) {
            $order = M('recharge')->where("order_id", $order_id)->find();
            $this->assign('order', $order);
        }
        return $this->fetch();
    }

    public function recharge_list(){
    	$usersLogic = new UsersLogic;
    	$result= $usersLogic->get_recharge_log($this->user_id);  //充值记录
    	$this->assign('page', $result['show']);
    	$this->assign('lists', $result['result']);
    	if (I('is_ajax')) {
    		return $this->fetch('ajax_recharge_list');
    	}
    	return $this->fetch();
    }

     /**
     * 申请提现记录
     */
    public function withdrawals()
    {
        C('TOKEN_ON', true);
        if (IS_POST) {

            $data = I('post.');
            $list['user_id'] = $this->user_id;
            $list['create_time'] = time();
            $distribut_min = tpCache('basic.min'); // 最少提现额度
            $bill_charge = tpCache('basic.bill_charge'); // 手续费比例

            $user_payment = Db::name('user_payment')->where(['type'=>$data['type'],'user_id'=>$this->user_id])->find();

            if(empty($user_payment)){
                if($data['type'] == 1){
                    $url = U('User/weixin_info');
                    $msg = '请先添加支付宝信息';
                }else if($data['type'] == 2){
                    $url = U('User/alipay_info');
                    $msg = '请先添加微信信息';
                }else if($data['type'] == 3){
                    $url = U('User/alipay_info');
                    $msg = '请先添加银行卡信息';
                }

                $this->ajaxReturn(['status' => 1, 'msg' => $msg, 'url' => $url]);
                exit;
            }

            if ($data['money'] < $distribut_min) {
                $this->ajaxReturn(['status' => 0, 'msg' => '每次最少提现额度' . $distribut_min]);
                exit;
            }
            if ($data['money'] > $this->user['user_money']) {
                $this->ajaxReturn(['status' => 0, 'msg' => "你最多可提现{$this->user['user_money']}账户余额."]);
                exit;
            }
            $withdrawal = M('withdrawals')->where(array('user_id' => $this->user_id, 'status' => 0))->sum('money');
            if ($this->user['user_money'] < ($withdrawal + $data['money'])) {
                $this->ajaxReturn(['status' => 0, 'msg' => '您有提现申请待处理，本次提现余额不足']);
            }

            //手续费金额
            $list['money'] = $data['money'];
            $list['taxfee'] = floatval($data['money']) * (floatval($bill_charge) / 100);
            $list['bill_charge'] = $bill_charge;

            if($data['type'] == 1){      //支付宝
                $list['bank_name'] = '支付宝';
                $list['bank_card'] = $user_payment['account'];//账号
                $list['realname']  = $user_payment['name'];   //真实姓名
                $list['qrcode_url']  = $user_payment['qrcode_url']; //收款码
            }else if($data['type'] == 2){//微信
                $list['bank_name'] = '微信';
                $list['bank_card'] = $user_payment['account'];//账号
                $list['realname']  = $user_payment['name'];   //真实姓名
                $list['qrcode_url']= $user_payment['qrcode_url'];  //收款码
            }else if($data['type'] == 3){//银行卡
                $list['bank_name'] = $user_payment['bank_name'];//银行名称
                $list['bank_card'] = $user_payment['account'];//账号
                $list['realname']  = $user_payment['name'];   //真实姓名
                $list['branch_name']  = $user_payment['branch_name']; //支行
            }
            
            if (M('withdrawals')->add($list)) {
                $this->ajaxReturn(['status' => 1, 'msg' => "已提交申请", 'url' => U('User/withdrawals_success',array('money'=>$data['money'],'bank_name'=>$list['bank_name']))]);
                exit;
            } else {
                $this->ajaxReturn(['status' => 0, 'msg' => '提交失败,联系客服!']);
                exit;
            }
        }
        $this->assign('user_money', $this->user['user_money']);//用户余额
        return $this->fetch();
    }
        //提现成功页面
    public function withdrawals_success(){
        $money = I('money');
        $bank_name  = I('bank_name');

        $this->assign('money', $money);
        $this->assign('bank_name', $bank_name);
        return $this->fetch();
    }

    /**
     * 申请记录列表
     */
    public function withdrawals_list()
    {
        $withdrawals_where['user_id'] = $this->user_id;
        $count = M('withdrawals')->where($withdrawals_where)->count();
        $pagesize = C('PAGESIZE');
        $page = new Page($count, $pagesize);
        $list = M('withdrawals')->where($withdrawals_where)->order("id desc")->limit("{$page->firstRow},{$page->listRows}")->select();

        $this->assign('page', $page->show());// 赋值分页输出
        $this->assign('list', $list); // 下线
        if (I('is_ajax')) {
            return $this->fetch('ajax_withdrawals_list');
        }
        return $this->fetch();
    }


    /**
     *  用户消息通知
     * @author dyr
     * @time 2016/09/01
     */
    public function message_notice()
    {
        return $this->fetch();
    }

    /**
     * ajax用户消息通知请求
     * @author dyr
     * @time 2016/09/01
     */
    public function ajax_message_notice()
    {
        $type = I('type');
        $user_logic = new UsersLogic();
        $message_model = new MessageLogic();
        if ($type === '0') {
            //系统消息
            $user_sys_message = $message_model->getUserMessageNotice();
        } else if ($type == 1) {
            //活动消息：后续开发
            $user_sys_message = array();
        } else {
            //全部消息：后续完善
            $user_sys_message = $message_model->getUserMessageNotice();
        }
        $this->assign('messages', $user_sys_message);
        return $this->fetch('ajax_message_notice');

    }

    /**
     * ajax用户消息通知请求
     */
    public function set_message_notice()
    {
        $type = I('type');
        $msg_id = I('msg_id');
        $user_logic = new UsersLogic();
        $res =$user_logic->setMessageForRead($type,$msg_id);
        $this->ajaxReturn($res);
    }


    /**
     * 设置消息通知
     */
    public function set_notice(){
        //暂无数据
        return $this->fetch();
    }

    /**
     * 浏览记录
     */
    public function visit_log()
    {
        $count = M('goods_visit')->where('user_id', $this->user_id)->count();
        $Page = new Page($count, 20);
        $visit = M('goods_visit')->alias('v')
            ->field('v.visit_id, v.goods_id, v.visittime, g.goods_name, g.shop_price, g.cat_id')
            ->join('__GOODS__ g', 'v.goods_id=g.goods_id')
            ->where('v.user_id', $this->user_id)
            ->order('v.visittime desc')
            ->limit($Page->firstRow, $Page->listRows)
            ->select();

        /* 浏览记录按日期分组 */
        $curyear = date('Y');
        $visit_list = [];
        foreach ($visit as $v) {
            if ($curyear == date('Y', $v['visittime'])) {
                $date = date('m月d日', $v['visittime']);
            } else {
                $date = date('Y年m月d日', $v['visittime']);
            }
            $visit_list[$date][] = $v;
        }

        $this->assign('visit_list', $visit_list);
        if (I('get.is_ajax', 0)) {
            return $this->fetch('ajax_visit_log');
        }
        return $this->fetch();
    }

    /**
     * 删除浏览记录
     */
    public function del_visit_log()
    {
        $visit_ids = I('get.visit_ids', 0);
        $row = M('goods_visit')->where('visit_id','IN', $visit_ids)->delete();

        if(!$row) {
            $this->error('操作失败',U('User/visit_log'));
        } else {
            $this->success("操作成功",U('User/visit_log'));
        }
    }

    /**
     * 清空浏览记录
     */
    public function clear_visit_log()
    {
        $row = M('goods_visit')->where('user_id', $this->user_id)->delete();

        if(!$row) {
            $this->error('操作失败',U('User/visit_log'));
        } else {
            $this->success("操作成功",U('User/visit_log'));
        }
    }

    /**
     * 支付密码
     * @return mixed
     */
    public function paypwd()
    {
        //检查是否第三方登录用户
        $user = M('users')->where('user_id', $this->user_id)->find();
        if(strrchr($_SERVER['HTTP_REFERER'],'/') =='/cart2.html'){  //用户从提交订单页来的，后面设置完有要返回去
            session('payPriorUrl',U('Mobile/Cart/cart2'));
        }
        if ($user['mobile'] == '')
            $this->error('请先绑定手机号',U('User/userinfo',['action'=>'mobile']));
        $step = I('step', 1);
        if ($step > 1) {
            $check = session('validate_code');
            if (empty($check)) {
                $this->error('验证码还未验证通过', U('mobile/User/paypwd'));
            }
        }
        if (IS_POST && $step == 2) {
            $new_password = trim(I('new_password'));
            $confirm_password = trim(I('confirm_password'));
            $oldpaypwd = trim(I('old_password'));
            //以前设置过就得验证原来密码
            if(!empty($user['paypwd']) && ($user['paypwd'] != encrypt($oldpaypwd))){
                $this->ajaxReturn(['status'=>-1,'msg'=>'原密码验证错误！','result'=>'']);
            }
            $userLogic = new UsersLogic();
            $data = $userLogic->paypwd($this->user_id, $new_password, $confirm_password);
            $this->ajaxReturn($data);
            exit;
        }
        $this->assign('step', $step);
        return $this->fetch();
    }
    /**
     *  点赞
     * @author lxl
     * @time  17-4-20
     * 拷多商家Order控制器
     */
    public function ajaxZan()
    {
        $comment_id = I('post.comment_id/d');
        $user_id = $this->user_id;
        $comment_info = M('comment')->where(array('comment_id' => $comment_id))->find();  //获取点赞用户ID
        $comment_user_id_array = explode(',', $comment_info['zan_userid']);
        if (in_array($user_id, $comment_user_id_array)) {  //判断用户有没点赞过
            $result['success'] = 0;
        } else {
            array_push($comment_user_id_array, $user_id);  //加入用户ID
            $comment_user_id_string = implode(',', $comment_user_id_array);
            $comment_data['zan_num'] = $comment_info['zan_num'] + 1;  //点赞数量加1
            $comment_data['zan_userid'] = $comment_user_id_string;
            M('comment')->where(array('comment_id' => $comment_id))->save($comment_data);
            $result['success'] = 1;
        }
        exit(json_encode($result));
    }


    /**
     * 会员签到积分奖励
     * 2017/9/28
     */
    public function sign() {
        $user_id = $this->user_id;
        $config = tpCache('sign');
        if (IS_AJAX) {
            $date = I('str'); //20170929
            //是否正确请求
            (date("Y-n-j", time()) != $date) && $this->ajaxReturn(['status' => -1, 'msg' => '请求错误！', 'result' => date("Y-n-j", time())]);

            $integral = $config['sign_integral'];
            $msg = "签到赠送" . $integral . "积分";
            //签到开关
            if ($config['sign_on_off'] > 0) {
                $map['lastsign'] = $date;
                $map['user_id'] = $user_id;
                $check = DB::name('user_sign')->where($map)->find();
                $check && $this->ajaxReturn(['status' => -1, 'msg' => '您今天已经签过啦！', 'result' => '']);
                if (!DB::name('user_sign')->where(['user_id' => $user_id])->find()) {
                    //第一次签到
                    $data = [];
                    $data['user_id'] = $user_id;
                    $data['signtotal'] = 1;
                    $data['lastsign'] = $date;
                    $data['cumtrapz'] = $config['sign_integral'];
                    $data['signtime'] = "$date";
                    $data['signcount'] = 1;
                    $data['thismonth'] = $config['sign_integral'];
                    if (M('user_sign')->add($data)) {
                        $status = ['status' => 1, 'msg' => '签到成功！', 'result' => $config['sign_integral']];
                    } else {
                        $status = ['status' => -1, 'msg' => '签到失败!', 'result' => ''];
                    }
                    $this->ajaxReturn($status);
                } else {
                    $update_data = array(
                        'signtotal' => ['exp', 'signtotal+' . 1], //累计签到天数
                        'lastsign' => ['exp', "'$date'"], //最后签到时间
                        'cumtrapz' => ['exp', 'cumtrapz+' . $config['sign_integral']], //累计签到获取积分
                        'signtime' => ['exp', "CONCAT_WS(',',signtime ,'$date')"], //历史签到记录
                        'signcount' => ['exp', 'signcount+' . 1], //连续签到天数
                        'thismonth' => ['exp', 'thismonth+' . $config['sign_integral']], //本月累计积分
                    );

                    $daya = Db::name('user_sign')->where('user_id', $user_id)->value('lastsign');    //上次签到时间
                    $dayb = date("Y-n-j", strtotime($date) - 86400);                                   //今天签到时间
                    //不是连续签
                    if ($daya != $dayb) {
                        $update_data['signcount'] = ['exp', 1];                                       //连续签到天数
                    }
                    $mb = date("m", strtotime($date));                                               //获取本次签到月份
                    //不是本月签到
                    if (intval($mb) != intval(date("m", strtotime($daya)))) {
                        $update_data['signcount'] = ['exp', 1];                                      //连续签到天数
                        $update_data['signtime'] = ['exp', "'$date'"];                                  //历史签到记录;
                        $update_data['thismonth'] = ['exp', $config['sign_integral']];              //本月累计积分
                    }

                    $update = Db::name('user_sign')->where(['user_id' => $user_id])->update($update_data);

                    (!$update) && $this->ajaxReturn(['status' => -1, 'msg' => '网络异常！', 'result' => '']);

                    $signcount = Db::name('user_sign')->where('user_id', $user_id)->value('signcount');
                    $integral = $config['sign_integral'];
                    //满足额外奖励
                    if (( $signcount >= $config['sign_signcount']) && ($config['sign_on_off'] > 0)) {
                        Db::name('user_sign')->where(['user_id' => $user_id])->update([
                            'cumtrapz' => ['exp', 'cumtrapz+' . $config['sign_award']],
                            'thismonth' => ['exp', 'thismonth+' . $config['sign_award']]
                        ]);
                        $integral = $config['sign_integral'] + $config['sign_award'];
                        $msg = "签到赠送" . $config['sign_integral'] . "积分，连续签到奖励" . $config['sign_award'] . "积分，共" . $integral . "积分";
                    }
                }
                if ($config['sign_integral'] > 0 && $config['sign_on_off'] > 0) {
                    accountLog($user_id, 0, $integral, $msg);
                    $status = ['status' => 1, 'msg' => '签到成功！', 'result' => $integral];
                } else {
                    $status = ['status' => -1, 'msg' => '签到失败!', 'result' => ''];
                }
                $this->ajaxReturn($status);
            } else {
                $this->ajaxReturn(['status' => -1, 'msg' => '该功能未开启！', 'result' => '']);
            }
        }
        $map = [];
        $map['us.user_id'] = $user_id;
        $field = [
            'u.user_id as user_id',
            'u.nickname',
            'u.mobile',
            'us.*',
        ];
        $join = [
            ['users u', 'u.user_id=us.user_id', 'left']
        ];
        $info = Db::name('user_sign')->alias('us')->field($field)
                        ->join($join)->where($map)->find();

        ($info['lastsign'] != date("Y-n-j", time())) && $tab = "1";

        $signtime = explode(",", $info['signtime']);
        $str = "";
        //是否标识历史签到
        if (date("m", strtotime($info['lastsign'])) == date("m", time())) {
            foreach ($signtime as $val) {
                $str .= date("j", strtotime($val)) . ',';
            }
            $this->assign('info', $info);
            $this->assign('str', $str);
        }

        $this->assign('cumtrapz', $info['cumtrapz']);
        $this->assign("jifen", ($config['sign_signcount'] * $config['sign_integral']) + $config['sign_award']);
        $this->assign('config', $config);
        $this->assign('tab', $tab);

        return $this->fetch();
    }

    public function accountSafe()
    {
        return $this->fetch();
    }


    

    /*我的二维码*/
    public function myqrcode()
    {
        //加载第三方类库
        vendor('phpqrcode.phpqrcode');

        //获取用户邀请码
        $invite_code = Db('tuijian_code')->where('user_id',$this->user_id)->value('code');
        //获取个人
        //$url = request()->domain().U('contactleader',['id'=>$this->user_id]);
        $url = request()->domain().U('mobile/User/reg',['invite_code'=>$invite_code]);

        $after_path = 'public/qrcode/'.md5($url).'.png';
        //保存路径
        $path =  ROOT_PATH.$after_path;

        //判断是该文件是否存在
        if(!is_file($path))
        {
            //实例化
            $qr = new \QRcode();
            //1:url,3: 容错级别：L、M、Q、H,4:点的大小：1到10
            $qr::png($url,'./'.$after_path, "M", 6,TRUE);
        }

        return request()->domain().'/'.$after_path;

    }

    /*关联上下级*/
    public function contactleader(){
        $parent_id = I('user_id/d');//上级id
        $user_id = $this->user_id;
        $users=M('users');
        $parent_info = $users->where(['user_id'=>$parent_id])->find();
        if($user_id==$parent_id)
            DataReturn::returnJson('400','不能成为自己的下级');
        if(empty($parent_info))
            DataReturn::returnJson('400','所绑定上级用户的信息有误');
        if($parent_info['first_leader']==$user_id)
            DataReturn::returnJson('400','您已是他的上级，不能绑定');
        $user_info = $users->where(['user_id'=>$user_id])->find();
        if($user_info['first_leader'])
            DataReturn::returnJson('400','您已经存在上级，不可以继续绑定');
        if(IS_POST){
            //绑定上下级关系
            $result = $users->where(['user_id'=>$user_id])->save(['first_leader'=>$parent_id]);
            return $result;
        }
        
        $this->assign('head_pic',$user_info['head_pic']);
        $this->assign('user',$parent_info);
        return $this->fetch();
    }

    

    public function test()
    {
        $a =  time() - 1523343949;

    }

    /*微信信息*/
    public function weixin_info(){

        $id = I('id');

        $list = Db::name('user_payment')->where('id',$id)->find();

        $this->assign('list',$list);
        
        return $this->fetch();
    }

    /*编辑微信信息*/
    public function edit_weixin_info(){

        $user_id = $this->user_id;
        if(IS_AJAX){

        $id = I('id');
        $account = I('account');
        $name    = I('name');
        $head_pic= I('head_pic');

        $data['account'] = $account;
        $data['name']    = $name;
        $data['qrcode_url']=$head_pic;
        $data['type'] = 2;
	$data['create_time'] = time();
        $data['user_id'] = $user_id;
                if(empty($id)){
                    if (M('user_payment')->add($data)) {

                        $this->ajaxReturn(['status' => 1, 'msg' => '保存成功','url'=>U('User/withdrawals')]);
                    } else {

                        $this->ajaxReturn(['status' => 1, 'msg' => '网络异常！']);
                    }
                }else{


                    $flag=db('user_payment')->where(array('user_id'=>$user_id,'id'=>$id))->update($data);

                    if($flag < 0 ){

                        $this->ajaxReturn(['status' => 1, 'msg' => '网络异常!']);
                    }

                    $this->ajaxReturn(['status' => 1, 'msg' => '保存成功','url'=>U('User/withdrawals')]);

                }
            }
        }

    /*支付宝信息*/
    public function alipay_info(){

        $id = I('id');

        $list = Db::name('user_payment')->where('id',$id)->find();

        $this->assign('list',$list);
        return $this->fetch();
    }

    /*编辑支付宝信息*/
    public function edit_alipay_info(){

        $user_id = $this->user_id;
        if(IS_AJAX){

            $id = I('id');
            $account = I('account');
            $name    = I('name');
            $head_pic= I('head_pic');

            $data['account'] = $account;
            $data['name']    = $name;
            $data['qrcode_url']=$head_pic;
            $data['type'] = 1;
            $data['create_time'] = time();
            $data['user_id'] = $user_id;
            if(empty($id)){
                if (M('user_payment')->add($data)) {

                    $this->ajaxReturn(['status' => 1, 'msg' => '保存成功','url'=>U('User/withdrawals')]);
                } else {

                    $this->ajaxReturn(['status' => 1, 'msg' => '网络异常！']);
                }
            }else{


                $flag=db('user_payment')->where(array('user_id'=>$user_id,'id'=>$id))->update($data);

                if($flag < 0 ){

                    $this->ajaxReturn(['status' => 1, 'msg' => '网络异常!']);
                }

                $this->ajaxReturn(['status' => 1, 'msg' => '保存成功','url'=>U('User/withdrawals')]);

            }

        }
    }


    //上传头像
    public function uploadimage(){
        if ($_FILES['head_pic']['tmp_name']) {
            if($_FILES['head_pic']['name'] == 'blob'){
                //给ios传的base64一个后缀名
                $_FILES['head_pic']['name'] = "blob.png";
            }
            $file = $this->request->file('head_pic');
            $image_upload_limit_size = config('image_upload_limit_size');
            $validate = ['size' => $image_upload_limit_size, 'ext' => 'jpg,png,gif,jpeg'];
            $dir = 'public/upload/head_pic/';

            if (! is_dir($dir))
                mkdirs($dir);
//            if (!($_exists = file_exists($dir))) {
//                $isMk = mkdir($dir);
//            }

            $parentDir = date('Ymd');
            $info = $file->validate($validate)->move($dir, true);

            if ($info) {
                $return['imgpath'] = '/' . $dir . $parentDir . '/' . $info->getFilename();
                exit(json_encode(['status'=>"success",'msg'=>"上传成功",'data'=>$return]));
                // DataReturn::returnJson('success', '上传成功', $return);
            } else {
                exit(json_encode(['status'=>"error",'msg'=>'上传失败']));
                // DataReturn::returnJson('error', '上传失败');
            }
        }
    }

  /*银行卡信息*/
    public function bank_info(){

        $id = I('id');

        $list = Db::name('user_payment')->where('id',$id)->find();

        $this->assign('list',$list);
        
        return $this->fetch();
    }

     /*编辑银行卡信息*/
    public function edit_bank_info(){

        $user_id = $this->user_id;

        if(IS_AJAX){

            $id = I('id');
            $account = I('account');
            $name    = I('name');
            $bank_name  = I('bank_name');
            $branch_name= I('branch_name');

            $data['account'] = $account;
            $data['name']    = $name;
            $data['bank_name']   = $bank_name;
            $data['branch_name'] = $branch_name;
            $data['type'] = 3;
            $data['create_time'] = time();
            $data['user_id'] = $user_id;
            if(empty($id)){
                if (M('user_payment')->add($data)) {

                    $this->ajaxReturn(['status' => 1, 'msg' => '保存成功','url'=>U('User/withdrawals')]);
                } else {

                    $this->ajaxReturn(['status' => 1, 'msg' => '网络异常！']);
                }
            }else{


                $flag=db('user_payment')->where(array('user_id'=>$user_id,'id'=>$id))->update($data);

                if($flag < 0 ){

                    $this->ajaxReturn(['status' => 1, 'msg' => '网络异常!']);
                }

                $this->ajaxReturn(['status' => 1, 'msg' => '保存成功','url'=>U('User/withdrawals')]);

            }

        }
    }
    /*意见反馈*/
    public function message_board(){

        if (IS_POST) {
            $data = I('post.');
            $data['user_id'] = $this->user_id;
            $data['create_time'] = time();
            if (M('message_board')->add($data)) {
                $this->ajaxReturn(['status' => 1, 'msg' => "提交成功",'url'=>U('User/index')]);
            } else {
                $this->ajaxReturn(['status' => 0, 'msg' => "提交失败"]);
            }
        }
        return $this->fetch();
    }

    /*帮助界面*/
     public function help(){

        $cat_id = I('cat_id');
        $article_title = Db::name('article_cat')->where('cat_id',$cat_id)->value('cat_name');
        $article = Db::name('article')->where('cat_id',$cat_id)->find();
        $help_title = $article['title'];       // 帮助标题
        $help_content = htmlspecialchars_decode($article['content']); // 帮助内容
        $this->assign('article_title',$article_title);
        $this->assign('help_title',$help_title);
        $this->assign('help_content',$help_content);
        return $this->fetch();
     }
     /*公告界面*/
     public function Notice(){
        $help_title = tpCache('shop_info.Notice_title');       // 公告标题
        $help_content = htmlspecialchars_decode(tpCache('shop_info.Notice_content')); // 公告内容
        $this->assign('help_title',$help_title);
        $this->assign('help_content',$help_content);
        return $this->fetch();
     
     
     }
    /*投诉界面*/
    public function complaint(){

        $user_id = $this->user_id;
        if(IS_AJAX){

            $data['content'] = I('content');
            $data['qrcode_url']=I('head_pic');
            $data['create_time'] = time();
            $data['user_id'] = $user_id;
     
            if (M('complaint_log')->add($data)) {

                $this->ajaxReturn(['status' => 1, 'msg' => '提交成功','url'=>U('User/index')]);
            } else {

                $this->ajaxReturn(['status' => 1, 'msg' => '网络异常！']);
            }
        }
        return $this->fetch();
    }
     
}
