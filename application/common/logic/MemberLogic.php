<?php

namespace app\common\logic;

use think\Model;
use think\Db;
use think\Config;
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

    //获取个人信息
    public function getUsers($user_id){
        $users = Db::name('users')->where(array('user_id'=>$user_id))->find();
        return $users;
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

    //保证金操作
    public function earnestMoney($user_id,$money){
        $users = $this->getUsers($user_id);
        $map['earnest_money'] = $users['earnest_money'] + $money;
        $res = M('users')->where(array('user_id'=>$user_id))->update($map);
        return  $res;
    }

    /**
     * 添加流水记录
     * @param $user_id 用户id $source_id 来源id  $content描述 $money金额 $type类型
     * @return
     */
    public function addRecord($user_id,$source_id,$content,$money,$type){
            $map['user_id'] = $user_id;
            $map['source_id'] = $source_id;
            $map['content'] = $content;
            $map['money'] = $money;
            $map['type'] = $type;
            $map['create_time'] = time();
            $res =  M('record')->add($map);
            return $res;
    }

    //获取流水记录
    public function getRecord($user_id,$p){
        $count = M('record')->where(array('user_id'=>$user_id))->count();
        $pagesize = 12;
        $pagestart = ($p-1)*$pagesize;
        $list = M('record')->where(array('user_id'=>$user_id))->order('create_time desc')->limit($pagestart,$pagesize)->select();
        return  $list;
    }


    //实名认证通过后分佣保证金
    public function earnestSend($user_id,$status){
        $user = M('users')->where(array('user_id'=>$user_id))->find();
        $config = tpCache('shop_info');

        if($status==1){
            //自己获得保证金
            $this->earnestMoney($user['user_id'],$config['earnest_money']);
            //添加消息
            add_message($user['user_id'],'实名认证成功');
            add_message($user['user_id'],'实名认证成功,获得'.$config['earnest_money'].'保证金');
            //添加保证金流水
            $this->addRecord($user['user_id'],'','实名认证成功,获得'.$config['earnest_money'].'保证金',$config['earnest_money'],1);


            //推荐人获得保证金
            if(!empty($user['first_leader'])){
                //更新账户保证金
                $this->earnestMoney($user['first_leader'],$config['safe_money']);
                //添加消息
                add_message($user['first_leader'],'下级用户'.$user['mobile'].'实名认证成功,获得'.$config['safe_money'].'保证金');
                //添加保证金流水
                $this->addRecord($user['first_leader'],$user['user_id'],'下级用户'.$user['mobile'].'实名认证成功,获得'.$config['safe_money'].'保证金',$config['safe_money'],1);
            }
        }else if($status == 2){
            //扣除自己获得保证金
            $this->earnestMoney($user['user_id'],-$config['earnest_money']);
            //添加消息
            add_message($user['user_id'],'实名认证失败');
            add_message($user['user_id'],'实名认证失败,扣除'.$config['earnest_money'].'保证金');
            //发送短信
            jh_message($user['mobile'], Config::get('database.type_auth'),'');
            //添加保证金流水
            $this->addRecord($user['user_id'],'','实名认证失败,扣除'.$config['earnest_money'].'保证金',-$config['earnest_money'],1);


            //扣除推荐人获得保证金
            if(!empty($user['first_leader'])){
                //更新账户保证金
                $this->earnestMoney($user['first_leader'],-$config['safe_money']);
                //添加消息
                add_message($user['first_leader'],'下级用户'.$user['mobile'].'实名认证失败,扣除'.$config['safe_money'].'保证金');
                //添加保证金流水
                $this->addRecord($user['first_leader'],$user['user_id'],'下级用户'.$user['mobile'].'实名认证失败,扣除'.$config['safe_money'].'保证金',-$config['safe_money'],1);
            }
        }

    }
}