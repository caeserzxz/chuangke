<?php

namespace app\common\logic;

use think\Model;
use think\Db;
/**
 *  个人中心
 * Class CatsLogic
 * @package Home\Logic
 */
class MemberLogic extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->session_id = session_id();
    }

    //上传付款凭证
    public function upload_img($img_name,$path){
        $file = request()->file($img_name);
        if($file){
            $info = $file->move(UPLOAD_PATH."$path");
            if($info){
                return $info->getSaveName();
            }else{
                return $info->getError();die;
            }
        }
    }

    //获取实名认证结果
    public function getAuthenticationResult($user_id){
        if($user_id) $where['user_id'] = $user_id;
        $data = Db::name('user_authentication')->where($where)->find();
        return $data;
    }

    //获取收款信息
    public function getAccount($user_id){
        if($user_id) $where['user_id'] = $user_id;
        $data = Db::name('receipt_information')->where($where)->find();
        return $data;
    }
}