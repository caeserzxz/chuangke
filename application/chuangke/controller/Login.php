<?php

namespace app\chuangke\controller;

use think\Controller;
use think\Session;
use think\Db;
use think\Config;
use app\systemadmin\logic\UsersLogic;
use think\Verify;
class Login extends Controller
{
    public function __construct()
    {
        parent::__construct();
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
        $config = tpCache('shop_info');
        $this->assign('config',$config);
    }
    /**
     * 登录
     */
    public function index(){
        //获取安装包的参数
        $appType = I('appType')?I('appType'):I('apptype');
        if(empty(session('appType'))){
            if($appType=='IOS'||$appType=='Android'){
                session('appType',$appType);
            }else{
                session('appType','other');
            }
        }
        //判断是否允许网页登录
        $config = tpCache('shop_info');
        if(session('appType')=='other'){
            if($config['is_other_login']==0){
                //$this->redirect('chuangke/Login/AppDownload');
            }
        }
        return $this->fetch();
    }

    public function login_check(){
        $input = input();

        $user_data = db('users')
            ->where('mobile', $input['mobile'])
            ->where('password', encrypt($input['password']))
            ->find();
        if (!$user_data) {
            return array('status' => 500, 'msg' => '账户或密码出错', 'result' => '');
        }

        //判断用户是否被冻结
        if ($user_data['is_lock'] == '1') {
            return array('status' => 500, 'msg' => '用户被冻结', 'result' => '');
        }

        session('user', $user_data);
        session('user_id', $user_data['user_id']);

        //5、跳转到首页
        return array('status' => 1, 'msg' => '登录成功', 'result' => $user_data['user_id']);
    }

    /**
 * 注册
 */
    public function register(){
        $recommendId = I('rec_id') ? I('rec_id') : 0;  //扫码分享的推荐人id
        if($recommendId!=0){
            session('recommendId',$recommendId);
        }else{
            $recommendId = session('recommendId');
        }

        $tuijian_code = M('tuijian_code')->where(array('user_id'=>$recommendId))->getField('code');

        $config = tpCache('shop_info');
//        if($config['is_other_login']==1){
//            $config['is_other_login']=1;
//        }else{
//            $config['is_other_login']=2;
//        }

        $this->assign('appType',session('appType'));
        $this->assign('config',$config);
        $this->assign('tuijian_code',$tuijian_code);
        return $this->fetch();
    }

    /**
     * 注册
     */
    public function appregister(){
        $recommendId = I('rec_id') ? I('rec_id') : 0;  //扫码分享的推荐人id
        if($recommendId!=0){
            session('recommendId',$recommendId);
        }else{
            $recommendId = session('recommendId');
        }

        $tuijian_code = M('tuijian_code')->where(array('user_id'=>$recommendId))->getField('code');

        $config = tpCache('shop_info');
        $this->assign('config',$config);
        $this->assign('tuijian_code',$tuijian_code);
        return $this->fetch();
    }

    public  function ajaxRegister(){

        $data = I('post.');
        $user_obj = new UsersLogic();

        $config = tpCache('shop_info');
        if($config['check_verify_code']==1){
            //验证验证码
            $check = check_verify_code($data['mobile'],$data['verify_code']);
            if($check){
                return  $check;
            }
        }

        if(empty($data['tuijian_code'])){
            return array('status' => 500, 'msg' => '请填写邀请码', 'result' => '');
        }else{
            $top_code = M('tuijian_code')->where(array('code'=>$data['tuijian_code']))->find();
            if(empty($top_code)){
                return array('status' => 500, 'msg' => '推荐人不存在', 'result' => '');
            }else{
                $data['first_leader'] =  $top_code['user_id'];
            }
        }

        $is_user = M('users')->where(array('mobile'=>$data['mobile']))->find();
        if($is_user){
            return array('status' => 500, 'msg' => '手机号已被注册', 'result' => '');
        }

        $p_res = $this->checkPwd($data['password']);//验证密码强度
        if ($p_res !== true) {
            return array('status' => 500, 'msg' => $p_res, 'result' => '');
        }

        $res = $user_obj->addUser($data);

        if($res['status'] == 1){
            //添加团队
            Db::name('users_team')->save(['user_id'=>$res['user_id']]);
            //添加推荐码
            $code = getWelcode();
            Db::name('tuijian_code')->save(['user_id'=>$res['user_id'],'code'=>$code]);
            if (tpCache('shop_info.new_mess') == 1 && $data['first_leader']) {
                // 注册给直推人发送短信 刘雄杰
                $mobile = M('users')->where(['user_id' => $data['first_leader']])->value('mobile');
                jh_message($mobile,Config::get('database.type_new'),$data['mobile']);
            }
            return array('status'=>1,'msg'=>'注册成功');
        }else{
            return array('status'=>-1,'msg'=>'注册失败');
        }

    }


    //获取手机验证码
    public function captcha()
    {

        $config = tpCache('shop_info');
        if($config['check_verify_code']==0){
            return array('status' => 500, 'msg' => '无需验证码');
            exit;
        }

        $input = input();

        //1、校验user表手机号用户是否存在
        $user_data=db('users')->where('mobile',$input['mobile'])->find();
//        if($user_data)
//        {
//            return array('status' => 500, 'msg' => '手机用户已存在', 'result' => '');
//        }
        //生成验证码
        $captcha = mt_rand(100000, 999999);
        //发送验证码接口（阿里云短信）
        ####################

        //        set_time_limit(0);
        //        header('Content-Type: text/plain; charset=utf-8');
        //        $SmsDemoAli= new SmsDemoAli;
        //        $result_data=$SmsDemoAli->sendSms($input['mobile'],$captcha);
        //
        //        if($result_data->Message != OK)
        //        {
        //            return array('status' => 500, 'msg' => '发送失败', 'result' => "");
        //        }

        #################################
        //聚合数据短信
        #################################
        $send = jh_message($input['mobile'],Config::get('database.type_code'),$captcha);
        if ($send['error_code']==0) {
            //验证码入库
            $res = db('n_mobile_captcha')->insert([
                'mobile' => $input['mobile'],
                'expire_in' => (time() + 1200),
                'captcha' => $captcha,
                'create_time' => time(),
            ]);

            return array('status' => 200, 'msg' => '发送成功', 'result' => $captcha);
        } else {
            return array('status' => 500, 'msg' =>$send['reason'] , 'result' => "");
            //请求异常
        }
        ################################



    }

    /**
     * 聚合数据
     * 请求接口返回内容
     * @param  string $url [请求的URL地址]
     * @param  string $params [请求的参数]
     * @param  int $ipost [是否采用POST形式]
     * @return  string
     */
    function juheCurl($url, $params = false, $ispost = 0)
    {
        $httpInfo = array();
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'JuheData');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                curl_setopt($ch, CURLOPT_URL, $url.'?'.$params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }
    /**
     * 忘记密码
     */
    public function forgotPassword(){
        if(IS_POST){
            $data = I('post.');
            $user_obj = new UsersLogic();
            $config = tpCache('shop_info');
            if($config['check_verify_code']==1){
                //验证验证码
                $check = check_verify_code($data['mobile'],$data['verify_code']);
                if($check){
                    return  $check;
                }
            }
            $users = Db::name('users')->where('mobile',$data['mobile'])->find();
            if(empty($users)){
                return array('status' => 500, 'msg' => '用户不存在', 'result' => '');
            }else{
                $p_res = $this->checkPwd($data['password']);//验证密码强度
                if ($p_res !== true) {
                    return array('status' => 500, 'msg' => $p_res, 'result' => '');
                }
                if($data['password']!=$data['re_password']){
                    return array('status' => 500, 'msg' => '两次密码不同', 'result' => '');
                }
                if(encrypt($data['password'])==$users['password']){
                    return array('status' => 500, 'msg' => '新密码与原密码相同', 'result' => '');
                }
                $map['password'] = encrypt($data['password']);
                $res = Db::name('users')->where(['user_id'=>$users['user_id']])->save($map);
                if($res){
                    return array('status' => 1, 'msg' => '修改成功', 'result' => '');
                }else{
                    return array('status' => 500, 'msg' => '修改失败', 'result' => '');
                }
            }

        }else{
            return $this->fetch();
        }

    }

    /**
     * 退出登录
     */
    public function layOut(){
        session('user_id', null);
        session('users', null);
        if(empty( session('user_id'))&&empty( session('users'))){
            return array('status' => 1, 'msg' => '退出成功', 'result' => '');
        }else{
            return array('status' => -1, 'msg' => '退出失败', 'result' => '');
        }
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

    /*------------------------------------------------------ */
    //-- 验证密码强度
    /*------------------------------------------------------ */
    private function checkPwd($pwd)
    {
        $pwd = trim($pwd);
        if (empty($pwd)) {
            return '密码不能为空';
        }
        if (strlen($pwd) < 6) {//必须大于8个字符
            return '密码必须大于六字符';
        }
//        if (preg_match("/^[0-9]+$/", $pwd)) { //必须含有特殊字符
//            return '密码不能全是数字，请包含数字，字母大小写或者特殊字符';
//        }
//        if (preg_match("/^[a-zA-Z]+$/", $pwd)) {
//            return '密码不能全是字母，请包含数字，字母大小写或者特殊字符';
//        }
//        if (preg_match("/^[0-9A-Z]+$/", $pwd)) {
//            return '请包含数字，字母大小写或者特殊字符';
//        }
//        if (preg_match("/^[0-9a-z]+$/", $pwd)) {
//            return '请包含数字，字母大小写或者特殊字符';
//        }
        return true;
    }

    /**
     * APP下载页
     */
    public function AppDownload(){
        $config = tpCache('shop_info');
        $this->assign('config',$config);
        return $this->fetch();
    }

}