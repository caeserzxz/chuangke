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
        $this->assign('config', tpCache('shop_info'));
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
        $image_info = tpCache('image_info');
        $this->assign('image_info',$image_info);
        $this->assign('config', tpCache('shop_info'));
        $this->assign('cache',$cache);
        $this->assign('appType',session('appType'));
        $this->assign('is_account',$is_account);
        $this->assign('is_auth',$is_auth);
        $this->assign('auth_status',$auth_status);
        $this->assign('userInfo',$userInfo);
        if (tpCache('shop_info.template4') == 2) {
            return $this->fetch('plan/template4');
        }else{
            return $this->fetch();
        }
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

            $data['status'] = 0;
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
                //$model->earnestSend($userInfo['user_id'],1);

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
        $this->assign('shop_info',tpCache('shop_info'));
        $this->assign('appType',session('appType'));
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
        $Page = new Page($count, 15);
        
        $list = M('users')
            ->field('mobile,reg_time,head_pic,nickname')
            ->where(['first_leader' => $userInfo['user_id']])
            ->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->assign('list',$list);
        if (IS_AJAX) return $this->fetch('ajax_friend_list');
        $this->assign('userInfo',$userInfo);
        return $this->fetch();
    }

    /**
     * 申请代理
     */
    public function applicationAgency(){
        $image_info = tpCache('image_info');
        $this->assign('image_info',$image_info);
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
        $path = './'.UPLOAD_PATH.$img_type."/".date(Ymd,time()).'/';
        is_dir($path) OR mkdir($path, 0777, true);
        $prefix='nx_';
        $output_file = $prefix.time().'.png';
        $path = $path.$output_file;
        $ifp = fopen( $path, "wb" );
        fwrite( $ifp, base64_decode( $base_img) );
        fclose( $ifp );
        //return date(Ymd,time()).'/'.$output_file;
        $return['path'] = '/'.UPLOAD_PATH.$img_type."/".date(Ymd,time()).'/'.$output_file;
        $return['img_type'] = $img_type;
        $return['img_name'] = $img_name;
        $this->ajaxreturn($return);
        //return $return;
    }

    //上传头像
    public function uploda_head_pic(){
        $userInfo = $this->userInfo;
        $model  = new MemberLogic();
        $data['head_pic'] = I('head_pic');
//        if(empty($data['head_pic'])&&empty($_FILES['head_pic']['tmp_name'])){
//            return array('status' => -1, 'msg' => '请添加头像', 'result' => '');
//        }
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

    /*留言*/
    public function complaint(){
        $userInfo = $this->userInfo;
        if(IS_AJAX){

            $data['content'] = I('content');
            $data['qrcode_url']=I('qrcode_url');
            $data['create_time'] = time();
            $data['user_id'] =  $userInfo['user_id'];
            if(empty($data['qrcode_url'])&&empty($_FILES['qrcode_url']['tmp_name'])&&empty($data['content'])){
                return array('status' => -1, 'msg' => '提交内容不能为空', 'result' => '');
            }

            //上传图片
            $model  = new MemberLogic();
            if($_FILES['qrcode_url']['tmp_name']){//上传留言图片
                $card_positive = $model->upload_img('qrcode_url','qrcode_url');

                if($card_positive){
                    $data['qrcode_url'] = '/'.UPLOAD_PATH.'qrcode_url/'.$card_positive;
                }
            }

            if (M('complaint_log')->add($data)) {

                $this->ajaxReturn(['status' => 1, 'msg' => '提交成功','url'=>U('User/index')]);
            } else {

                $this->ajaxReturn(['status' => 1, 'msg' => '网络异常！']);
            }
        }

        $this->assign('appType',session('appType'));
        return $this->fetch();
    }

    public function save_wx_number(){
        $userInfo = $this->userInfo;
        $code = I('code');
        if(empty($code)){
            $this->ajaxReturn(['status' => -1, 'msg' => '微信号不能为空']);
        }
        $data['wx_number'] = trim($code);
        $res = M('users')->where(array('user_id'=>$userInfo['user_id']))->update($data);
        if($res){
            $this->ajaxReturn(['status' => 1, 'msg' => '提交成功']);
        }else{
            $this->ajaxReturn(['status' => -1, 'msg' => '修改微信失败']);
        }
    }



    public  function huahua(){
        $image_info = tpCache('image_info');
        if(empty($image_info['share_img'])){
            $im = './public/static/chuangke/images/shareBG.png';
        }else{
            $im = '.'.$image_info['share_img'];
        }

        $erweima = I('erweima');
        $wx_code = I('wx_code');
            $config = array(
                'image'=>array(
                    array(
                        'url'=>"$erweima",     //二维码资源
                        'stream'=>0,
                        'left'=>240,
                        'top'=>500,
                        'right'=>0,
                        'bottom'=>0,
                        'width'=>280,
                        'height'=>280,
                        'opacity'=>100
                    )
                ),
                'text'=> array(
                    array(
                        'text'=>"$wx_code",
                        'left'=>210,
                        'top'=>850,
                        'fontSize'=>30,       //字号
                        'fontColor'=>'255,240,245', //字体颜色
                        'angle'=>0,
                    )
                ),
                'background'=>$im,         //背景图
            );
        $filename = 'public/qrcode/'.time().'.png';
        return  $this->createPoster($config,$filename);
        exit;
    }


    function createPoster($config=array(),$filename=""){
        header("Content-type: text/html; charset=utf-8");
        //如果要看报什么错，可以先注释调这个header
        if(empty($filename))
            header("content-type: image/png");
        $background = $config['background'];//海报最底层得背景
        //背景方法
        $backgroundInfo = getimagesize($background);
        $backgroundFun = 'imagecreatefrom'.image_type_to_extension($backgroundInfo[2], false);
        $background = $backgroundFun($background);
        $backgroundWidth = imagesx($background);  //背景宽度
        $backgroundHeight = imagesy($background);  //背景高度
        $imageRes = imageCreatetruecolor($backgroundWidth,$backgroundHeight);
        $color = imagecolorallocate($imageRes, 255, 255, 255);
        imagefill($imageRes, 0, 0, $color);
        // imageColorTransparent($imageRes, $color);  //颜色透明
        imagecopyresampled($imageRes,$background,0,0,0,0,imagesx($background),imagesy($background),imagesx($background),imagesy($background));

        if(!empty($config['image'])){
            foreach ($config['image'] as $key => $val) {
//                $val = array_merge($imageDefault,$val);
                $info = getimagesize($val['url']);
                $function = 'imagecreatefrom'.image_type_to_extension($info[2], false);

                if($val['stream']){   //如果传的是字符串图像流
                    $info = getimagesizefromstring($val['url']);
                    $function = 'imagecreatefromstring';
                }
                $res = $function($val['url']);
                $resWidth = $info[0];
                $resHeight = $info[1];
                //建立画板 ，缩放图片至指定尺寸

                $canvas=imagecreatetruecolor($val['width'], $val['height']);
                imagefill($canvas, 0, 0, $color);
                //关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h）
                imagecopyresampled($canvas, $res, 0, 0, 0, 0, $val['width'], $val['height'],$resWidth,$resHeight);
                $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']) - $val['width']:$val['left'];
                $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']) - $val['height']:$val['top'];
                //放置图像
                imagecopymerge($imageRes,$canvas, $val['left'],$val['top'],$val['right'],$val['bottom'],$val['width'],$val['height'],$val['opacity']);//左，上，右，下，宽度，高度，透明度
            }
        }

        //处理文字
        if(!empty($config['text'])){
            foreach ($config['text'] as $key => $val) {
//                $val = array_merge($textDefault,$val);
                list($R,$G,$B) = explode(',', $val['fontColor']);
                $fontColor = imagecolorallocate($imageRes, $R, $G, $B);
                $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']):$val['left'];
                $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']):$val['top'];

                imagettftext($imageRes,$val['fontSize'],$val['angle'],$val['left'],$val['top'],$fontColor,"./wryahei.ttf",$val['text']);
            }
        }
        //生成图片
        if(!empty($filename)){
            $res = imagejpeg ($imageRes,$filename,90); //保存到本地
            imagedestroy($imageRes);
            if(!$res) return false;
            return $this->imgToBase64($filename);
        }else{
            imagejpeg ($imageRes);     //在浏览器上显示
            imagedestroy($imageRes);
        }
    }


    /**
     * 获取图片的Base64编码(不支持url)
     * @date 2017-02-20 19:41:22
     *
     * @param $img_file 传入本地图片地址
     *
     * @return string
     */
    function imgToBase64($img_file) {

        $img_base64 = '';
        if (file_exists($img_file)) {
            $app_img_file = $img_file; // 图片路径
            $img_info = getimagesize($app_img_file); // 取得图片的大小，类型等

            //echo '<pre>' . print_r($img_info, true) . '</pre><br>';
            $fp = fopen($app_img_file, "r"); // 图片是否可读权限

            if ($fp) {
                $filesize = filesize($app_img_file);
                $content = fread($fp, $filesize);
                $file_content = chunk_split(base64_encode($content)); // base64编码
                switch ($img_info[2]) {           //判读图片类型
                    case 1: $img_type = "gif";
                        break;
                    case 2: $img_type = "jpg";
                        break;
                    case 3: $img_type = "png";
                        break;
                }

                $img_base64 = 'data:image/' . $img_type . ';base64,' . $file_content;//合成图片的base64编码

            }
            fclose($fp);
        }

        return $img_base64; //返回图片的base64
    }

}