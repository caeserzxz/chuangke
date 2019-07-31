<?php

namespace app\systemadmin\logic;

use think\Model;
use think\Db;

class UsersLogic extends Model
{

    /**
     * 获取指定用户信息
     * @param $uid int 用户UID
     * @param bool $relation 是否关联查询
     *
     * @return mixed 找到返回数组
     */
    public function detail($uid, $relation = true)
    {
        $user = M('users')->where(array('user_id' => $uid))->relation($relation)->find();
        return $user;
    }

    /**
     * 改变用户信息
     * @param int $uid
     * @param array $data
     * @return array
     */
    public function updateUser($uid = 0, $data = array())
    {
        $db_res = M('users')->where(array("user_id" => $uid))->data($data)->save();
        if ($db_res) {
            return array(1, "用户信息修改成功");
        } else {
            return array(0, "用户信息修改失败");
        }
    }


    /**
     * 添加用户
     * @param $user
     * @return array
     */
    public function addUser($user)
    {
		$user_count = Db::name('users')
				->where(function($query) use ($user){
					if ($user['email']) {
						$query->where('email',$user['email']);
					}
					if ($user['mobile']) {
						$query->whereOr('mobile',$user['mobile']);
					}
				})
				->count();
		if ($user_count > 0) {
			return array('status' => -1, 'msg' => '账号已存在');
		}
        $user['password'] = encrypt($user['password']);
        $user['reg_time'] = time();
        $user['nickname'] = $user['mobile'];
        $user_id = M('users')->add($user);
        if(!$user_id){
    		return array('status'=>-1,'msg'=>'注册');
    	}else{
    		$pay_points = tpCache('basic.reg_integral'); // 会员注册赠送积分
    		if($pay_points > 0)
    			accountLog($user_id, 0 , $pay_points , '会员注册赠送积分'); // 记录日志流水

            //更新关系链
            $first_leader = Db::name('users')->where('user_id',$user_id)->value('first_leader');
            if($first_leader){
                $leader = Db::name('users')->where('user_id',$first_leader)->value('leader_all');
                if($leader){
                    $leader = $leader.'_'.$user_id;
                }else{
                    $leader = $user_id;
                }

            }else{
                $leader = $user_id;
            }
            Db::name('users')->where('user_id',$user_id)->setField('leader_all',$leader);

    		return array('status'=>1,'msg'=>'注册成功','user_id'=>$user_id);
    	}
    }


}