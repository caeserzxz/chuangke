<?php

namespace app\api\controller;

use app\common\logic\CommentLogic;
use app\common\logic\OrderLogic;
use app\common\logic\UsersLogic;
use app\common\model\TeamFound;
use My\DataReturn;
use think\db;

class chuangke extends Base
{   
  /**
    * 会员等级升级
    * $user_uid    会员ID
    * $user_level  会员等级
    * $order_money 报单金额
    */
    
    public function chuangke_conditions($user_uid,$user_level,$order_money)
    {   
        //查询当前用户累计收益金额
        $sum_money = Db::name('account_log')->where('user_id',$user_uid)->sum('user_money');

        //查询等级累计收入与报单升级金额
        $level_moeny = Db::name('user_level')->field('conditions_1,conditions_2')->where('user_id',$user_uid)->find();


        //已身的等级
        if($user_level == 1){

            if(floatval($sum_money) >= floatval($level_moeny['conditions_1']) && floatval($order_money) >= floatval($level_moeny['conditions_2'])){

                M('users')->where('user_id',$user_uid)->update(['level' => 2]);
            }

        } else if($user_level == 2){

            if(floatval($sum_money) >= floatval($level_moeny['conditions_1']) && floatval($order_money) >= floatval($level_moeny['conditions_2'])){

                M('users')->where('user_id',$user_uid)->update(['level' => 3]);
            }

        } else if($user_level == 3){

            if(floatval($sum_money) >= floatval($level_moeny['conditions_1']) && floatval($order_money) >= floatval($level_moeny['conditions_2'])){

                M('users')->where('user_id',$user_uid)->update(['level' => 4]);
            }

        } else if($user_level == 4){

            if(floatval($sum_money) >= floatval($level_moeny['conditions_1']) && floatval($order_money) >= floatval($level_moeny['conditions_2'])){

                M('users')->where('user_id',$user_uid)->update(['level' => 5]);
            }

        } else if($user_level == 5){

            if(floatval($sum_money) >= floatval($level_moeny['conditions_1']) && floatval($order_money) >= floatval($level_moeny['conditions_2'])){

                M('users')->where('user_id',$user_uid)->update(['level' => 6]);

            }

        } else if($user_level == 6){

            if(floatval($sum_money) >= floatval($level_moeny['conditions_1']) && floatval($order_money) >= floatval($level_moeny['conditions_2'])){

                M('users')->where('user_id',$user_uid)->update(['level' => 7]);

            }

        } else if($user_level == 7){

            if(floatval($sum_money) >= floatval($level_moeny['conditions_1']) && floatval($order_money) >= floatval($level_moeny['conditions_2'])){

                M('users')->where('user_id',$user_uid)->update(['level' => 8]);

            }

        } else if($user_level == 8){

            if(floatval($sum_money) >= floatval($level_moeny['conditions_1']) && floatval($order_money) >= floatval($level_moeny['conditions_2'])){

                M('users')->where('user_id',$user_uid)->update(['level' => 9]);

            }

        } else if($user_level == 9){

            if(floatval($sum_money) >= floatval($level_moeny['conditions_1']) && floatval($order_money) >= floatval($level_moeny['conditions_2'])){

                M('users')->where('user_id',$user_uid)->update(['level' => 10]);

            }

        }
          
    }
}
