<?php

namespace app\mobile\controller;
use app\common\logic\CartLogic;
use app\common\logic\UsersLogic;
use think\Controller;
use think\Session;
use think\Db;

class MobileBase extends Controller {
    public $session_id;
    public $weixin_config;
    public $cateTrre = array();

    /**
     * 初始化操作
     */
    public function _initialize() {
        //echo base64_encode("陈洋（系统定制开发商）");
        //exit;
        $first_leader = I('first_leader/d', 0);
        if ($first_leader > 0) session('first_leader', $first_leader);
        session('user'); //不用这个在忘记密码不能获取session('validate_code');
        //Session::start();
        header("Cache-control: private");
        $this->session_id = session_id(); // 当前的 session_id
        define('SESSION_ID', $this->session_id); //将当前的session_id保存为常量，供其它方法调用
        // 判断当前用户是否手机
        if (isMobile()) {
            cookie('is_mobile', '1', 3600);
        } else {
            cookie('is_mobile', '0', 3600);
        }

        //更新缓存
        $userId   = Session('user_id');
        if($userId){
            $userInfo =M('users')->where('user_id',$userId)->find();
            //更新缓存
            if((time()-$userInfo['cache_time'])>600){
                if(empty($userInfo['cache'])){
                    M('users')->where(array('user_id'=>$userInfo['user_id']))->update(array('cache'=>rand(300,600),'cache_time'=>time()));
                }else{
                    $cache_arr['cache'] =$userInfo['cache']+rand(30,100);
                    $cache_arr['cache_time'] = time();
                    M('users')->where(array('user_id'=>$userInfo['user_id']))->update($cache_arr);
                }
            }
        }
        //获取安装包的参数
        $appType = I('appType');
        if($appType){
            if(empty(session('appType'))||$appType!=session('appType')){
                session('appType',$appType);
            }
        }else{
            if(empty(session('appType'))){
                session('appType','other');
            }
        }

        //判断是否允许网页登录
        $config = tpCache('shop_info');
        if(session('appType')=='other'){
            if($config['is_other_login']==0){
                $this->redirect('chuangke/Login/AppDownload');
            }
        }


        $wx_qr = M('wx_user')->cache(true)->value('qr'); //获取微信配置
        $this->assign('wx_qr',$wx_qr);
        $signPackage = getwxconfig(); //获取微信配置
        $this->assign('signPackage',$signPackage);
        //微信浏览器
        if(strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
            $this->assign('is_weixin_browser', 1);
            $user_temp = session('user');
            if (isset($user_temp['user_id']) && $user_temp['user_id']) {
                $user = M('users')->where("user_id", $user_temp['user_id'])->find();
                if (!$user) {
                    $_SESSION['openid'] = 0;
                    session('user', null);
                }
            }
            if (empty($_SESSION['openid']) || empty(session('user'))) {
                if ($_SESSION['openid']) $_SESSION['openid'] = 0;
                $this->weixin_config = M('wx_user')->find(); //获取微信配置
                $this->assign('wechat_config', $this->weixin_config);
                if(is_array($this->weixin_config) && $this->weixin_config['wait_access'] == 1){
                    $wxuser = $this->GetOpenid(); //授权获取openid以及微信用户信息
                    session('subscribe', $wxuser['subscribe']);// 当前这个用户是否关注了微信公众号
                    setcookie('subscribe', $wxuser['subscribe']);
                    $logic = new UsersLogic();
                    $is_bind_account = tpCache('basic.is_bind_account');
                    if ($is_bind_account) {
                        if ($wxuser['unionid']) {
                            $thirdUser = M('OauthUsers')->where(['unionid' => $wxuser['unionid'], 'oauth' => $wxuser['oauth']])->find();
                        } else {
                            $thirdUser = M('OauthUsers')->where(['openid' => $wxuser['openid'], 'oauth' => $wxuser['oauth']])->find();
                        }
                        if (empty($thirdUser)) {
                            //用户未关联账号, 跳到关联账号页
                            session('third_oauth', $wxuser);
                            $first_leader = I('first_leader');
                            return $this->redirect(U('Mobile/User/bind_guide', ['first_leader' => $first_leader]));
                        } else {
                            $data = $logic->thirdLogin_new($wxuser); //微信自动登录
                        }
                    } else {
                        $data = $logic->thirdLogin($wxuser);
                    }
                    if ($data['status'] == 1) {
                        //获取公众号openid,并保持到session的user中
                        $oauth_users = M('OauthUsers')->where(['user_id' => $data['result']['user_id'], 'oauth' => 'weixin', 'oauth_child' => 'mp'])->find();
                        $oauth_users && $data['result']['open_id'] = $oauth_users['open_id'];
                        session('user', $data['result']);
                        setcookie('user_id', $data['result']['user_id'], null, '/');
                        setcookie('is_distribut', $data['result']['is_distribut'], null, '/');
                        setcookie('uname', $data['result']['nickname'], null, '/');
                        // 登录后将购物车的商品的 user_id 改为当前登录的id
                        M('cart')->where("session_id", $this->session_id)->save(array('user_id' => $data['result']['user_id']));
                        $cartLogic = new CartLogic();
                        $cartLogic->doUserLoginHandle($this->session_id, $data['result']['user_id']);  //用户登录后 需要对购物车 一些操作
                    }
                }
            }
        }
        $this->assign('historyback',"javascript:history.back(-1);");
        $this->public_assign();
    }


    /**
     * 保存公告变量到 smarty中 比如 导航
     */
    public function public_assign()
    {
        $first_login = session('first_login');
        $this->assign('first_login', $first_login);
        if (!$first_login && ACTION_NAME == 'login') session('first_login', 1);
        $tpshop_config = array();
        $tp_config = M('config')->cache(true, TPSHOP_CACHE_TIME)->select();
        foreach ($tp_config as $k => $v) {
            if ($v['name'] == 'hot_keywords') {
                $tpshop_config['hot_keywords'] = explode('|', $v['value']);
            }
            $tpshop_config[$v['inc_type'] . '_' . $v['name']] = $v['value'];
        }
        $goods_category_tree = get_goods_category_tree();
        $this->cateTrre = $goods_category_tree;
        $this->assign('goods_category_tree', $goods_category_tree);
        $brand_list = M('brand')->cache(true, TPSHOP_CACHE_TIME)->field('id,cat_id,logo,is_hot')->where("cat_id>0")->select();
        $this->assign('brand_list', $brand_list);
        $this->assign('tpshop_config', $tpshop_config);
        /** 修复首次进入微商城不显示用户昵称问题 **/
        $user_id = cookie('user_id');
        $uname = cookie('uname');
        if (empty($user_id) && ($users = session('user'))) {
            $user_id = $users['user_id'];
            $uname = $users['nickname'];
        }
        $this->assign('user_id', $user_id);
        $this->assign('uname', $uname);
    }

    // 网页授权登录获取 OpendId
    public function GetOpenid()
    {
        if($_SESSION['openid'])
            return $_SESSION['openid'];
        //通过code获得openid
        if (!isset($_GET['code'])){
            //触发微信返回code码
            //$baseUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
            $baseUrl = urlencode($this->get_url());
            $url = $this->__CreateOauthUrlForCode($baseUrl); // 获取 code地址
            Header("Location: $url"); // 跳转到微信授权页面 需要用户确认登录的页面
            exit();
        } else {
            //上面获取到code后这里跳转回来
            $code = $_GET['code'];
            $data = $this->getOpenidFromMp($code);//获取网页授权access_token和用户openid
            $data2 = $this->GetUserInfo($data['access_token'],$data['openid']);//获取微信用户信息
            $data['nickname'] = empty($data2['nickname']) ? '微信用户' : trim($data2['nickname']);
            $data['sex'] = $data2['sex'];
            $data['head_pic'] = $data2['headimgurl'];
            $data['subscribe'] = $data2['subscribe'];
            $data['oauth_child'] = 'mp';
            $_SESSION['openid'] = $data['openid'];
            $data['oauth'] = 'weixin';
            if(isset($data2['unionid'])){
            	$data['unionid'] = $data2['unionid'];
            }
            return $data;
        }
    }

    /**
     * 获取当前的url 地址
     * @return type
     */
    private function get_url() {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
    }

    /**
     *
     * 通过code从工作平台获取openid机器access_token
     * @param string $code 微信跳转回来带上的code
     *
     * @return openid
     */
    public function GetOpenidFromMp($code)
    {
        //通过code获取网页授权access_token 和 openid 。网页授权access_token是一次性的，而基础支持的access_token的是有时间限制的：7200s。
    	//1、微信网页授权是通过OAuth2.0机制实现的，在用户授权给公众号后，公众号可以获取到一个网页授权特有的接口调用凭证（网页授权access_token），通过网页授权access_token可以进行授权后接口调用，如获取用户基本信息；
    	//2、其他微信接口，需要通过基础支持中的“获取access_token”接口来获取到的普通access_token调用。
        $url = $this->__CreateOauthUrlForOpenid($code);
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);//设置超时
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $res = curl_exec($ch);//运行curl，结果以jason形式返回
        $data = json_decode($res,true);
        curl_close($ch);
        return $data;
    }


        /**
     *
     * 通过access_token openid 从工作平台获取UserInfo
     * @return openid
     */
    public function GetUserInfo($access_token,$openid)
    {
        // 获取用户 信息
        $url = $this->__CreateOauthUrlForUserinfo($access_token,$openid);
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);//设置超时
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $res = curl_exec($ch);//运行curl，结果以jason形式返回
        $data = json_decode($res,true);
        curl_close($ch);
        //获取用户是否关注了微信公众号， 再来判断是否提示用户 关注
        if(!isset($data['unionid'])){
        	$access_token2 = $this->get_access_token();//获取基础支持的access_token
        	$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token2&openid=$openid";
        	$subscribe_info = httpRequest($url,'GET');
        	$subscribe_info = json_decode($subscribe_info,true);
        	$data['subscribe'] = $subscribe_info['subscribe'];
        }
        return $data;
    }


    public function get_access_token(){
        //判断是否过了缓存期
        $expire_time = $this->weixin_config['web_expires'];
        if($expire_time > time()){
           return $this->weixin_config['web_access_token'];
        }
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->weixin_config[appid]}&secret={$this->weixin_config[appsecret]}";
        $return = httpRequest($url,'GET');
        $return = json_decode($return,1);
        $web_expires = time() + 7140; // 提前60秒过期
        M('wx_user')->where(array('id'=>$this->weixin_config['id']))->save(array('web_access_token'=>$return['access_token'],'web_expires'=>$web_expires));
        return $return['access_token'];
    }

    /**
     *
     * 构造获取code的url连接
     * @param string $redirectUrl 微信服务器回跳的url，需要url编码
     *
     * @return 返回构造好的url
     */
    private function __CreateOauthUrlForCode($redirectUrl)
    {
        $urlObj["appid"] = $this->weixin_config['appid'];
        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["response_type"] = "code";
//        $urlObj["scope"] = "snsapi_base";
        $urlObj["scope"] = "snsapi_userinfo";
        $urlObj["state"] = "STATE"."#wechat_redirect";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
    }

    /**
     *
     * 构造获取open和access_toke的url地址
     * @param string $code，微信跳转带回的code
     *
     * @return 请求的url
     */
    private function __CreateOauthUrlForOpenid($code)
    {
        $urlObj["appid"] = $this->weixin_config['appid'];
        $urlObj["secret"] = $this->weixin_config['appsecret'];
        $urlObj["code"] = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
    }

    /**
     *
     * 构造获取拉取用户信息(需scope为 snsapi_userinfo)的url地址
     * @return 请求的url
     */
    private function __CreateOauthUrlForUserinfo($access_token,$openid)
    {
        $urlObj["access_token"] = $access_token;
        $urlObj["openid"] = $openid;
        $urlObj["lang"] = 'zh_CN';
        $bizString = $this->ToUrlParams($urlObj);
        return "https://api.weixin.qq.com/sns/userinfo?".$bizString;
    }

    /**
     *
     * 拼接签名字符串
     * @param array $urlObj
     *
     * @return 返回已经拼接好的字符串
     */
    private function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v)
        {
            if($k != "sign"){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }
    public function ajaxReturn($data){
        exit(json_encode($data));
    }

}