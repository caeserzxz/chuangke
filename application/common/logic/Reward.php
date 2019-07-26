<?php
/*
 *奖励实现类
 *@Author Lizhengyu <1290077346@qq.com>
 *@Date 1290077346@qq.com
 */
namespace app\common\logic;

use think\Db;
use think\Model;

class Reward extends Model{

	/*
	 *[rerance 奖项统一入口]
     */
	public function rerance($user_id,$level,$price)
	{
		//验证
		if(empty($user_id) || empty($level) || empty($price)){
			return false;
		}
		
		//检查是否触发其他奖项
		if($level == 2){
			//触发直推奖
			$res_data = $this->direct_award($user_id,$level,$price);

			//触发见点奖
			$res_data = $this->meet_award($user_id,$level,$price);

			return $res_data;
		}elseif($level == 6){
			//触发见点奖
			$res_data = $this->meet_award($user_id,$level,$price);
		}

		//触发升级奖
		$res_data =  $this->upgrade_award($user_id,$level,$price);

		return $res_data;
	}
	/*
	 *升级奖
	 *user_id int 用户id
	 *level int 用户升的等级
	 *price int 升级费用
	 */
	private function upgrade_award($user_id,$level,$price)
	{
		//获取用户信息
		$user_info = Db('users')->field('user_id,level,first_leader')->where('user_id',$user_id)->find();
		//处理数据
		$upgrade_amount = (tpCache('reward_set.upgrade_rate') / 100) * $price;
		//寻找上级
		$leader_level = $level;
		$leader_id = $this->search_leader($user_info['first_leader'],$leader_level);
		
		if(empty($leader_id)){
			return false;
		}

		accountLog($leader_id, $upgrade_amount , 0 , '会员ID（'.$user_id.'）升级'.($level-1).'星，返升级奖',$upgrade_amount,0,0,2,2); // 记录日志流水

		$res_data = ['code'=>200,'msg'=>'返佣成功'];
		return $res_data;
	}

	/*
	 *直推奖
	 *user_id int 用户id
	 *level int 用户升的等级
	 *price int 升级费用
	 */
	private function direct_award($user_id,$level,$price)
	{
		//获取用户信息
		$user_info = Db('users')->field('user_id,level,first_leader')->where('user_id',$user_id)->find();
		//获取上级
		$leader_id = Db('users')->where('user_id',$user_id)->value('first_leader');
		if(empty($leader_id)){
			return false;
		}
		//处理数据
		$direct_amount = (tpCache('reward_set.direct_rate') / 100) * $price;

		accountLog($leader_id, $direct_amount , 0 , '会员ID（'.$user_id.'）升级'.($level-1).'星，返直推奖',$direct_amount,0,0,2,3); // 记录日志流水

		$res_data = ['code'=>200,'msg'=>'返佣成功'];
		return $res_data;

	}


	/*
	 *见点奖
	 *user_id int 用户id
	 *level int 用户升的等级
	 *price int 升级费用
	*/
	private function meet_award($user_id,$level,$price)
	{
		//获取用户信息
		$user_info = Db('users')->field('user_id,level,first_leader')->where('user_id',$user_id)->find();
		
		//处理数据
		if($level == 2){
			$leader_level = 6;
			$meet_amount = (tpCache('reward_set.one_meet_rate') / 100) * $price;
		}elseif($level == 6){
			$leader_level = 9;
			$meet_amount = (tpCache('reward_set.five_meet_rate') / 100) * $price;
		}
		
		//寻找上级
		$leader_id = $this->search_leader($user_info['first_leader'],$leader_level);

		if(empty($leader_id)){
			return false;
		}

		accountLog($leader_id, $meet_amount , 0 , '会员ID（'.$user_id.'）升级'.($level-1).'星，返见点奖',$meet_amount,0,0,2,4); // 记录日志流水

		$res_data = ['code'=>200,'msg'=>'返佣成功'];
		return $res_data;
	}


	/*
	 *寻找上级
	 *first_leader int 上级id
	 *leader_level int 上级的等级
	*/
	private function search_leader($first_leader,$leader_level)
	{
		$user_info = Db('users')->field('user_id,level,first_leader')->where('user_id',$first_leader)->find();

		if(empty($user_info)){
			return 0;
		}

		if($user_info['level'] == $leader_level){
			return $user_info['user_id'];
		}else{
			return $this->search_leader($user_info['first_leader'],$leader_level);
		}
	}


  /**
    * 会员等级升级
    * $user_uid    会员ID
    * $user_level  会员等级
    * $distribut_money 累计收益
    */
      public function chuangke_conditions($user_uid,$user_level,$distribut_money)
    {   
 		
 		$user_level_id = $user_level + 1;
        //查询等级累计收入与报单升级金额
        $level_moeny = Db::name('user_level')->where('level_id',$user_level_id)->value('conditions_1');
        
        //会员的等级
        if($user_level == 1){         //注册用户

                M('users')->where('user_id',$user_uid)->update(['level' => 2]); //升级一星会员

        } else if($user_level == 2){  //一星会员

            if( floatval($distribut_money) >= floatval($level_moeny) ){

                M('users')->where('user_id',$user_uid)->update(['level' => 3]); //升级二星会员
            }

        } else if($user_level == 3){  //二星会员

            if( floatval($distribut_money) >= floatval($level_moeny) ){

                M('users')->where('user_id',$user_uid)->update(['level' => 4]); //升级三星会员
            }

        } else if($user_level == 4){  //三星会员

            if( floatval($distribut_money) >= floatval($level_moeny) ){

                M('users')->where('user_id',$user_uid)->update(['level' => 5]); //升级四星会员
            }

        } else if($user_level == 5){  //四星会员

            if( floatval($distribut_money) >= floatval($level_moeny) ){

                M('users')->where('user_id',$user_uid)->update(['level' => 6]); //升级五星会员
            }

        } else if($user_level == 6){  //五星会员

            if( floatval($distribut_money) >= floatval($level_moeny) ){	

                M('users')->where('user_id',$user_uid)->update(['level' => 7]); //升级六星会员
            }

        } else if($user_level == 7){  //六星会员

            if(floatval($distribut_money) >= floatval($level_moeny) ){

                M('users')->where('user_id',$user_uid)->update(['level' => 8]); //升级七星会员
            }

        } else if($user_level == 8){  //七星会员

            if(floatval($distribut_money) >= floatval($level_moeny) ){

                M('users')->where('user_id',$user_uid)->update(['level' => 9]); //升级八星会员
            }

        } else if($user_level == 9){  //八星会员

            if(floatval($distribut_money) >= floatval($level_moeny) ){

                M('users')->where('user_id',$user_uid)->update(['level' => 10]);//升级九星会员
            }

        }
          
    }

}


