<?php

namespace app\chuangke\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\Db;
use think\facade\Log;
use app\common\logic\MemberLogic;
/**
 * 自动审核十分钟的实名认证
 * use app\vpay\command\ReleaseAssetsRundle;
 */

class AutomaticAudit extends Command
{
    protected function configure()
    {
        $this->setName('AutomaticAudit')->setDescription('自动审核10分钟的实名认证');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("自动审核10分钟的实名认证 begin");

        $config = tpCache('shop_info');


        //获取10分钟之前未通过的实名认证
        $model = new MemberLogic();
        $ahtu_time = time()-($config['auth_time']*60);
        $auth_list = $model->getAuthList($ahtu_time);

        M('users')->startTrans();
        M('record')->startTrans();
        M('user_authentication')->startTrans();
        try {
        foreach($auth_list as $k=>$v){
            //更改状态
            M('user_authentication')->where(array('id'=>$v['id']))->update(array('status'=>1));
            //更改昵称
            M('users')->where(array('user_id'=>$v['user_id']))->update(array('nickname'=>$v['user_name']));
            //分佣保证金
            $model->earnestSend($v['user_id'],1,'');
        }
        }catch (\Exception $e) {  //如书写为（Exception $e）将无效
            M('users')->rollback();
            M('record')->rollback();
            M('user_authentication')->rollback();
            dump($e->getMessage());
            exit;
        }
        M('users')->commit();
        M('record')->commit();
        M('user_authentication')->commit();
        Db::commit();// 提交事务

        $output->writeln("自动审核10分钟的实名认证  end");

    }
}