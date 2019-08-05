<?php

namespace app\chuangke\controller;
use think\Controller;
use think\Config;
use think\Db;
use app\mobile\controller\MobileBase;
use app\common\logic\MemberLogic;
use think\Page;

class Member extends  MobileBase
{
    public function __construct()
    {
        parent::__construct();
        $userId   = Session('user_id');
        if(empty($userId)){
            $this->redirect('chuangke/Login/index');
        }else{
            $userInfo =Db::name('users')
                ->where('user_id',$userId)
                ->find();
            $this->userInfo = $userInfo;
        }
    }

    /**
     * 首页
     */
    public function index(){
        $userInfo = $this->userInfo;
        
        $model  = new MemberLogic();
        //判断是否实名
        $auth = $model->getAuthenticationResult($userInfo['user_id']);
        if(empty($auth)){
            $is_auth = 1;//不存在
            $auth_status = -1;
        }else{
            $is_auth = 2;//存在
            $auth_status = $auth['status'];
        }

        //判断收款方式是否设置
        $account = $model->getAccount($userInfo['user_id']);
        if($account){
            $is_account = 1;
        }else{
            $is_account = 2;
        }
        //缓存
        $cache = sprintf("%.2f",$userInfo['cache']/1024);

        $this->assign('cache',$cache);
        $this->assign('appType',session('appType'));
        $this->assign('is_account',$is_account);
        $this->assign('is_auth',$is_auth);
        $this->assign('auth_status',$auth_status);
        $this->assign('userInfo',$userInfo);
        return $this->fetch();
    }

    /**
     * 实名认证
     */
    public function realNameAuthentication(){
        $userInfo = $this->userInfo;
        if($this->request->isPost()){
            $data = I('post.');
            $model  = new MemberLogic();

            if(empty($data['card_positive'])&&empty($_FILES['card_positive']['tmp_name'])){
                $return['status'] = -1;
                $return['msg'] = '请上传身份证正面';
                return $return;
            }
            if(empty($data['card_back'])&&empty($_FILES['card_back']['tmp_name'])){
                $return['status'] = -1;
                $return['msg'] = '请上传身份证背面';
                return $return;
            }

            if($_FILES['card_positive']['tmp_name']){//上传身份证正面
                $card_positive = $model->upload_img('card_positive','id_card');

                if($card_positive){
                    $data['card_positive'] = '/'.UPLOAD_PATH.'id_card/'.$card_positive;
                }
            }
            if($_FILES['card_back']['tmp_name']){//上传身份证背面
                $card_back = $model->upload_img('card_back','id_card');
                if($card_back){
                    $data['card_back'] = '/'.UPLOAD_PATH.'id_card/'.$card_back;
                }
            }

            $data['status'] = 1;
            $data['user_id'] = $userInfo['user_id'];
            $data['create_time'] = time();

            $auth = $model->getAuthenticationResult($userInfo['user_id']);

            if($auth){
                $res = Db::name('user_authentication')->where(array('user_id'=>$auth['user_id']))->update($data);
            }else{
                $res = Db::name('user_authentication')->insert($data);
            }
            if($res){
                //更新用户的昵称
                M('users')->where(array('user_id'=>$auth['user_id']))->update(array('nickname'=>$auth['user_name']));
                //分佣保证金
                $model->earnestSend($userInfo['user_id'],1);

                $return['status'] = 1;
                $return['msg'] = '提交成功';
                return $return;
            }else{
                $return['status'] = -1;
                $return['msg'] = '提交失败';
                return $return;
            }
        }else{
            $this->assign('appType',session('appType'));
            return $this->fetch();
        }
    }

    /**
     * 实名认证结果
     */
    public function authenticationResult(){
        $userInfo = $this->userInfo;
        $model  = new MemberLogic();
        $auth = $model->getAuthenticationResult($userInfo['user_id']);
        $auth['str_id_card'] = hidestr($auth['id_card'],4,10);
        $this->assign('auth',$auth);
        return $this->fetch();
    }

    /**
     * 收款方式
     */
    public function paymentMethod(){
        $userInfo = $this->userInfo;
        $model  = new MemberLogic();
        if($this->request->isPost()){
            $data = I('post.');
            $config = tpCache('shop_info');
            if($config['check_verify_code']==1){
                //验证验证码
                $check = check_verify_code($userInfo['mobile'],$data['verify_code']);
                if($check){
                    return  $check;
                }
            }

            if(empty($_FILES['account_code_img']['tmp_name'])&&empty($data['account_code_img'])){
                return array('status' => 500, 'msg' => '请上传收款码', 'result' => '');
            }
            //上传图片
            if($_FILES['account_code_img']['tmp_name']){//上传收款码
                $card_positive = $model->upload_img('account_code_img','account_code_img');

                if($card_positive){
                    $data['account_code_img'] = '/'.UPLOAD_PATH.'account_code_img/'.$card_positive;
                }
            }
            $data['user_id'] = $userInfo['user_id'];
            $data['create_time'] = time();

            //获取收款信息
            $account = $model->getAccount($userInfo['user_id']);
            if($account){
                $res = M('receipt_information')->where(array('user_id'=>$account['user_id']))->update($data);
            }else{
                $res = M('receipt_information')->insert($data);
            }

            if($res){
                return array('status' => 1, 'msg' => '操作成功', 'result' => '');
            }else{
                return array('status' => 500, 'msg' => '操作失败', 'result' => '');
            }
        }else{
            $account = $model->getAccount($userInfo['user_id']);

            $config = tpCache('shop_info');
            $this->assign('config',$config);
            $this->assign('appType',session('appType'));
            $this->assign('account',$account);
            $this->assign('userInfo',$userInfo);
            return $this->fetch();
        }
    }

    /**
     * 我的好友
     */
    public function myGoodFriend(){
        $user = $this->userInfo;

        //加载第三方类库
        vendor('phpqrcode.phpqrcode');

        //获取个人
        //$url = request()->domain().U('contactleader',['id'=>$user['user_id']]);
        $url = 'http://'.$_SERVER['SERVER_NAME'].'/chuangke/Login/register?rec_id='.$user['user_id'];
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
        $img = request()->domain().'/'.$after_path;

        $code = M('tuijian_code')->where(['user_id' => $user['user_id']])->value('code');

        $data['url'] = $url;
        $data['img'] = $img;
        $data['code'] = $code;

        $this->assign('user',$user);
        $this->assign('data',$data);

        return $this->fetch();
    }

    /**
     * 好友列表
     */
    public function goodFriendList(){
        $userInfo = $this->userInfo;
        $count = M('users')->where("first_leader", $userInfo['user_id'])->count();
        $Page = new Page($count, 12);
        
        $list = M('users')
            ->field('mobile,reg_time,head_pic,nickname')
            ->where(['first_leader' => $userInfo['user_id']])
            ->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->assign('list',$list);
        if (IS_AJAX) return $this->fetch('ajax_friend_list');
        return $this->fetch();
    }

    /**
     * 申请代理
     */
    public function applicationAgency(){

        return $this->fetch();
    }

    /**
     * 联系我们
     */
    public function callMeBaby(){
        $config = tpCache('shop_info');
        $this->assign('config',$config);
        return $this->fetch();
    }

    public function uploadimage(){
        //$base_img是获取到前端传递的src里面的值，也就是我们的数据流文件
        $base_img = $_POST['img'];
        $img_type = $_POST['img_type'];//图片文件夹名
        $img_name = $_POST['img_name'];//图片类型

        $base_img = str_replace('data:image/png;base64,', '', $base_img);
        //设置文件路径和文件前缀名称
        $path = '/'.UPLOAD_PATH."/".$img_type."/".date(Ymd,time()).'/';
        is_dir($path) OR mkdir($path, 0777, true);
        $prefix='nx_';
        $output_file = $prefix.time().'.png';
        $path = $path.$output_file;
        $ifp = fopen( $path, "wb" );
        fwrite( $ifp, base64_decode( $base_img) );
        fclose( $ifp );
        //return date(Ymd,time()).'/'.$output_file;
        $retrun['path'] = $path.$output_file;
        $return['image_path'] = date(Ymd,time()).'/'.$output_file;
        $return['img_type'] = $img_type;
        $return['img_name'] = $img_name;
        return $return;
    }

    //上传头像
    public function uploda_head_pic(){
        $userInfo = $this->userInfo;
        $model  = new MemberLogic();
        $data['head_pic'] = I('head_pic');
        if(empty($data['head_pic'])&&empty($_FILES['head_pic']['tmp_name'])){
            return array('status' => -1, 'msg' => '请添加头像', 'result' => '');
        }
        //上传图片
        if($_FILES['head_pic']['tmp_name']){//上传收款码
            $card_positive = $model->upload_img('head_pic','head_pic');

            if($card_positive){
                $data['head_pic'] = '/'.UPLOAD_PATH.'head_pic/'.$card_positive;
            }
        }

        if($data['head_pic']){
            $res = M('users')->where(array('user_id'=>$userInfo['user_id']))->update($data);
            if($res){
                return array('status' => 1, 'msg' => '操作成功', 'result' => '');
            }else{
                return array('status' => -1, 'msg' => '操作失败', 'result' => '');
            }
        }
    }

    //清除缓存
    public function clear_m(){
        $userInfo = $this->userInfo;
        if(empty($userInfo['cache'])){
            return array('status'=>1,'msg'=>'清除成功');
        }else{
            $map['cache'] = 0;
            $res = M('users')->where(array('user_id'=>$userInfo['user_id']))->update($map);
            if($res){
                return array('status'=>1,'msg'=>'清除成功');
            }else{
                return array('status'=>-1,'msg'=>'清除失败');
            }
        }

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