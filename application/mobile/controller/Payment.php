<?php

namespace app\mobile\controller;
use think\Request;
class Payment extends MobileBase {

    public $payment; //  具体的支付类
    public $pay_code; //  具体的支付code

    /**
     * 析构流函数
     */
    public function  __construct() {
        parent::__construct();
        // tpshop 订单支付提交
        $pay_radio = $_REQUEST['pay_radio'];
        if(!empty($pay_radio))
        {
            $pay_radio = parse_url_param($pay_radio);
            $this->pay_code = $pay_radio['pay_code']; // 支付 code
        }
        else // 第三方 支付商返回
        {
            //$_GET = I('get.');
            //file_put_contents('./a.html',$_GET,FILE_APPEND);
            $this->pay_code = I('get.pay_code');
            unset($_GET['pay_code']); // 用完之后删除, 以免进入签名判断里面去 导致错误
        }
        //获取通知的数据
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
		$xml = file_get_contents('php://input');
        if(empty($this->pay_code))
            exit('pay_code 不能为空');
        // 导入具体的支付类文件
        include_once  "plugins/payment/{$this->pay_code}/{$this->pay_code}.class.php"; // D:\wamp\www\svn_tpshop\www\plugins\payment\alipay\alipayPayment.class.php
        $code = '\\'.$this->pay_code; // \alipay
        $this->payment = new $code();
    }

    /**
     *  提交支付方式
     */
    public function getCode(){
            //C('TOKEN_ON',false); // 关闭 TOKEN_ON
            header("Content-type:text/html;charset=utf-8");
            $order_id = I('order_id/d'); // 订单id
            if(!session('user')) $this->error('请先登录',U('User/login'));
            // 修改订单的支付方式
            $payment_arr = M('Plugin')->where("`type` = 'payment'")->getField("code,name");
            M('order')->where("order_id", $order_id)->save(array('pay_code'=>$this->pay_code,'pay_name'=>$payment_arr[$this->pay_code]));
            $order = M('order')->where("order_id", $order_id)->find();
            if($order['pay_status'] == 1){
            	$this->error('此订单，已完成支付!');
            }
            //订单支付提交
            $pay_radio = $_REQUEST['pay_radio'];
            $config_value = parse_url_param($pay_radio); // 类似于 pay_code=alipay&bank_code=CCB-DEBIT 参数
            $payBody = getPayBody($order_id);
            $config_value['body'] = $payBody;
            //微信JS支付  && strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')
           if($this->pay_code == 'weixin' && $_SESSION['openid']){
               $code_str = $this->payment->getJSAPI($order);
               exit($code_str);
           }elseif($this->pay_code == 'miniAppPay'  && $_SESSION['openid']){
               $code_str = $this->payment->getJSAPI($order);
               exit($code_str);
           }else{
           	    $code_str = $this->payment->get_code($order,$config_value);
           }
            $this->assign('code_str', $code_str);
            $this->assign('order_id', $order_id);
            return $this->fetch('payment');  // 分跳转 和不 跳转
    }

    public function getPay(){
    	//手机端在线充值
        //C('TOKEN_ON',false); // 关闭 TOKEN_ON
        header("Content-type:text/html;charset=utf-8");
        $order_id = I('order_id/d'); //订单id
        $user = session('user');
        $data['account'] = 0.01;//I('account')
        if($order_id>0){
        	M('recharge')->where(array('order_id'=>$order_id,'user_id'=>$user['user_id']))->save($data);
        }else{
        	$data['user_id'] = $user['user_id'];
        	$data['nickname'] = $user['nickname'];
        	$data['order_sn'] = 'recharge'.get_rand_str(10,0,1);
        	$data['ctime'] = time();
        	$order_id = M('recharge')->add($data);
        }
        if($order_id){
        	$order = M('recharge')->where("order_id", $order_id)->find();
        	if(is_array($order) && $order['pay_status']==0){
        		$order['order_amount'] = $order['account'];
        		$pay_radio = $_REQUEST['pay_radio'];
        		$config_value = parse_url_param($pay_radio); // 类似于 pay_code=alipay&bank_code=CCB-DEBIT 参数
        		$payment_arr = M('Plugin')->where("`type` = 'payment'")->getField("code,name");
        		M('recharge')->where("order_id", $order_id)->save(array('pay_code'=>$this->pay_code,'pay_name'=>$payment_arr[$this->pay_code]));
        		//微信JS支付
        		if($this->pay_code == 'weixin' && $_SESSION['openid'] && strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
        			$code_str = $this->payment->getJSAPI($order);
        			exit($code_str);
        		}else{
        			$code_str = $this->payment->get_code($order,$config_value);
        		}
        	}else{
        		$this->error('此充值订单，已完成支付!');
        	}
        }else{
        	$this->error('提交失败,参数有误!');
        }
        $this->assign('code_str', $code_str);
        $this->assign('order_id', $order_id);
    	return $this->fetch('recharge'); //分跳转 和不 跳转
    }
        public function notifyUrl(){
            $this->payment->response();
            exit();
        }

        public function returnUrl(){
            $result = $this->payment->respond2(); // $result['order_sn'] = '201512241425288593';
            if(stripos($result['order_sn'],'recharge') !== false)
            {
            	$order = M('recharge')->where("order_sn", $result['order_sn'])->find();
            	$this->assign('order', $order);
            	if($result['status'] == 1)
            		return $this->fetch('recharge_success');
            	else
            		return $this->fetch('recharge_error');
            	exit();
            }
            $order = M('order')->where("order_sn", $result['order_sn'])->find();
            //预告所获得积分
            $points = M('order_goods')->where("order_id", $order['order_id'])->sum("give_integral * goods_num");


            $this->assign('order', $order);
            $this->assign('point',$points);
            if($result['status'] == 1)
                return $this->fetch('success');
            else
                return $this->fetch('error');
        }
}
