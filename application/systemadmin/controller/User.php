<?php

namespace app\systemadmin\controller;
use app\systemadmin\logic\OrderLogic;
use think\AjaxPage;
use think\Page;
use think\Verify;
use think\Db;
use app\systemadmin\logic\UsersLogic;
use think\Loader;

class User extends Base {

    public function index(){
        return $this->fetch();
    }

    /**
     * 会员列表
     */
    public function ajaxindex(){
        // 搜索条件
        $condition = array();
        I('mobile') ? $condition['mobile'] = I('mobile') : false;
        I('wx_number') ? $condition['wx_number'] = I('wx_number') : false;
        I('is_lock') ? $condition['is_lock'] = I('is_lock') : false;

        $user_id=I('user_id'); 
        $tier=I('tier'); 
        switch ($tier) {
            case 1://一级下线
                $user_ids=M('users')->where('first_leader',$user_id)->column('user_id');
                break;
            case 2://二级下线
                $first_ids=M('users')->where('first_leader',$user_id)->column('user_id');
                $user_ids=$first_ids ? M('users')->where('first_leader','in',$first_ids)->column('user_id') : 0;
                break;
            case 2://三级下线
                $first_ids=M('users')->where('first_leader',$user_id)->column('user_id');
                $second_ids=$first_ids ? M('users')->where('first_leader','in',$first_ids)->column('user_id') : 0;
                $user_ids=$second_ids ? M('users')->where('first_leader','in',$second_ids)->column('user_id') : 0;
                break;
        }
        if($user_ids){
            $condition['user_id']=['in',$user_ids];
        }
        $sort_order = I('order_by').' '.I('sort');
        $sort_order = $sort_order?$sort_order:"user_id desc";
        $model = M('users');
        $count = $model->where($condition)->count();
        $Page  = new AjaxPage($count,10);
        if($user_id){
            $Page->parameter['user_id']   =   urlencode($user_id);
            $Page->parameter['tier']   =   urlencode($tier);
        }
        $userList = $model->where($condition)->order($sort_order)->limit($Page->firstRow.','.$Page->listRows)->select();
        $withdrawalsModel = Db('withdrawals');
        $usersModel = Db('users');
        $tuijianCodeModel = Db('tuijian_code');
        foreach($userList as $key => $val) {
            $userList[$key]['total_withdraw'] = $withdrawalsModel->where(['user_id'=>$val['user_id'],'status'=>2])->sum('money');
            $userList[$key]['direct_sum'] = $usersModel->where(['first_leader'=>$val['user_id']])->count();
            $userList[$key]['leader_mobile'] = $usersModel->where(['user_id'=>$val['first_leader']])->value('mobile');
            $userList[$key]['tuijian_code'] = $tuijianCodeModel->where(['user_id'=>$val['user_id']])->value('code');
            // 是否有实名认证
            $is_real = M('user_authentication')->where(['user_id' => $val['user_id'],'status' => 1])->find();
            if ($is_real) {
                $userList[$key]['nickname'] = $is_real['user_name'];
            }else{
                $userList[$key]['nickname'] = $val['mobile'];
            }
        }
        $show = $Page->show();
        $this->assign('userList',$userList);
        $this->assign('level',M('user_level')->getField('level_id,level_name'));
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('pager',$Page);
        return $this->fetch();
    }

    /**
     * 会员详细信息查看
     */
    public function detail(){
        $uid = I('get.id');
        $user = D('users')->where(array('user_id'=>$uid))->find();
        $level=Db::name('user_level')->field('level_name,level_id')->select();
        
        if(!$user)
            exit($this->error('会员不存在'));
        if(IS_POST){
            //  会员信息编辑
            $password = I('post.password');
            $password2 = I('post.password2');
            if($password != '' && $password != $password2){
                exit($this->error('两次输入密码不同'));
            }
            if($password == '' && $password2 == ''){
                unset($_POST['password']);
            }else{
                $_POST['password'] = encrypt($_POST['password']);
            }

            if(!empty($_POST['email']))
            {   $email = trim($_POST['email']);
                $c = M('users')->where("user_id != $uid and email = '$email'")->count();
                $c && exit($this->error('邮箱不得和已有用户重复'));
            }

            if(!empty($_POST['mobile']))
            {   $mobile = trim($_POST['mobile']);
                $c = M('users')->where("user_id != $uid and mobile = '$mobile'")->count();
                $c && exit($this->error('手机号不得和已有用户重复'));
            }
            // 是否更改等级
            if ($_POST['level'] != $user['level']) {
                // 是否有正在审核的等级
                $is_check = M('ck_apply')->where(['user_id' => $uid,'apply_status' => 0])->count();
                if ($is_check > 0) $this->error('该用户有申请正在审核中');
            }
            $row = M('users')->where(array('user_id'=>$uid))->save($_POST);
            if($row)
                exit($this->success('修改成功'));
            exit($this->error('未作内容修改或修改失败'));
        }
        //获取上二、三级编号
        $user['second_leader'] = $user['first_leader'] ? M('users')->where("user_id", $user['first_leader'])->value('first_leader') : 0;
        $user['third_leader'] = $user['second_leader'] ? M('users')->where("user_id", $user['second_leader'])->value('first_leader') : 0;
        //获取一、二、三层下线人数
        $usersLogic = new \app\common\logic\UsersLogic();
        $number_data=$usersLogic->layer_number($user['user_id']);
        $user['first_lower']=$number_data['first_lower'];
        $user['second_lower']=$number_data['second_lower'];
        $user['third_lower']=$number_data['third_lower'];
        $this->assign('user',$user);
        $this->assign('level',$level);
        return $this->fetch();
    }

    public function add_user(){
    	if(IS_POST){
    		$data = I('post.');

            if(!empty($data['first_leader'])){
                $add_users = Db::name("users")->where('user_id',$data['first_leader'])->find();
                if(!empty($add_users)){
                    $data['first_leader'] = $data['first_leader'];
                }else{
                    $this->error('该上级ID不存在');exit;
                }
            }

			$user_obj = new UsersLogic();
			$res = $user_obj->addUser($data);
            
			if($res['status'] == 1){
                $code = getWelcode();
                Db::name('tuijian_code')->save(['user_id'=>$res['user_id'],'code'=>$code]);
                //添加团队
                Db::name('users_team')->save(['user_id'=>$res['user_id']]);
				$this->success('添加成功',U('User/index'));exit;
			}else{
				$this->error('添加失败,'.$res['msg'],U('User/index'));
			}
    	}
    	return $this->fetch();
    }

    public function export_user(){
    	$strTable ='<table width="500" border="1">';
    	$strTable .= '<tr>';
    	$strTable .= '<td style="text-align:center;font-size:12px;width:120px;">会员ID</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="100">会员昵称</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">会员等级</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">手机号</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">微信号码</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">注册时间</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">最后登陆</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">余额</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">累计收益金额</td>';
    	// $strTable .= '<td style="text-align:center;font-size:12px;" width="*">积分</td>';
    	// $strTable .= '<td style="text-align:center;font-size:12px;" width="*">累计消费</td>';
    	$strTable .= '</tr>';
    	$count = M('users')->count();
    	$p = ceil($count/5000);
    	for($i=0;$i<$p;$i++){
    		$start = $i*5000;
    		$end = ($i+1)*5000;
    		$userList = M('users')->order('user_id')->limit($start.','.$end)->select();
    		if(is_array($userList)){
    			foreach($userList as $k=>$val){
    				$strTable .= '<tr>';
    				$strTable .= '<td style="text-align:center;font-size:12px;">'.$val['user_id'].'</td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['nickname'].' </td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['level'].'</td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['mobile'].'</td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['wx_number'].'</td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.date('Y-m-d H:i',$val['reg_time']).'</td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.date('Y-m-d H:i',$val['last_login']).'</td>';
                    $strTable .= '<td style="text-align:left;font-size:12px;">'.$val['user_money'].'</td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['distribut_money'].'</td>';
    				// $strTable .= '<td style="text-align:left;font-size:12px;">'.$val['pay_points'].' </td>';
    				// $strTable .= '<td style="text-align:left;font-size:12px;">'.$val['total_amount'].' </td>';
    				$strTable .= '</tr>';
    			}
    			unset($userList);
    		}
    	}
    	$strTable .='</table>';
    	downloadExcel($strTable,'users_'.$i);
    	exit();
    }

    /**
     * 用户收货地址查看
     */
    public function address(){
        $uid = I('get.id');
        $lists = D('user_address')->where(array('user_id'=>$uid))->select();
        $regionList = get_region_list();
        $this->assign('regionList',$regionList);
        $this->assign('lists',$lists);
        return $this->fetch();
    }

    /**
     * 删除会员
     */
    public function delete(){
        $uid = I('get.id');
        $row = M('users')->where(array('user_id'=>$uid))->delete();
        if($row){
            $this->success('成功删除会员');
        }else{
            $this->error('操作失败');
        }
    }
    /**
     * 删除会员
     */
    public function ajax_delete(){
        $uid = I('id');
        if($uid){
            $row = M('users')->where(array('user_id'=>$uid))->delete();
            if($row !== false){
                $this->ajaxReturn(array('status' => 1, 'msg' => '删除成功', 'data' => ''));
            }else{
                $this->ajaxReturn(array('status' => 0, 'msg' => '删除失败', 'data' => ''));
            }
        }else{
            $this->ajaxReturn(array('status' => 0, 'msg' => '参数错误', 'data' => ''));
        }
    }

    /**
     * 账户资金记录
     */
    public function account_log(){
        $user_id = I('get.id');
        //获取类型
        $type = I('get.type');
        //获取记录总数
        $count = M('account_log')->where(array('user_id'=>$user_id))->count();
        $page = new Page($count);
        $lists  = M('account_log')->where(array('user_id'=>$user_id))->order('change_time desc')->limit($page->firstRow.','.$page->listRows)->select();

        $this->assign('user_id',$user_id);
        $this->assign('page',$page->show());
        $this->assign('lists',$lists);
        return $this->fetch();
    }

    /**
     * 账户资金调节
     */
    public function account_edit(){
        $user_id = I('user_id');
        if(!$user_id > 0) $this->ajaxReturn(['status'=>0,'msg'=>"参数有误"]);
        $user = M('users')->field('user_id,user_money,frozen_money,pay_points,is_lock')->where('user_id',$user_id)->find();
        if(IS_POST){
            $desc = I('post.desc');
            if(!$desc)
                $this->ajaxReturn(['status'=>0,'msg'=>"请填写操作说明"]);
            //加减用户资金
            $m_op_type = I('post.money_act_type');
            $user_money = I('post.user_money/f');
            $user_money =  $m_op_type ? $user_money : 0-$user_money;
            //加减用户积分
            $p_op_type = I('post.point_act_type');
            $pay_points = I('post.pay_points/d');
            $pay_points =  $p_op_type ? $pay_points : 0-$pay_points;
            //加减冻结资金
            $f_op_type = I('post.frozen_act_type');
            $revision_frozen_money = I('post.frozen_money/f');
            if( $revision_frozen_money != 0){    //有加减冻结资金的时候
                $frozen_money =  $f_op_type ? $revision_frozen_money : 0-$revision_frozen_money;
                $frozen_money = $user['frozen_money']+$frozen_money;    //计算用户被冻结的资金
                if($f_op_type==1 and $revision_frozen_money > $user['user_money'])
                {
                    $this->ajaxReturn(['status'=>0,'msg'=>"用户剩余资金不足！！"]);
                }
                if($f_op_type==0 and $revision_frozen_money > $user['frozen_money'])
                {
                    $this->ajaxReturn(['status'=>0,'msg'=>"冻结的资金不足！！"]);
                }
                $user_money = $f_op_type ? 0-$revision_frozen_money : $revision_frozen_money ;    //计算用户剩余资金
                M('users')->where('user_id',$user_id)->update(['frozen_money' => $frozen_money]);
            }
            if(accountLog($user_id,$user_money,$pay_points,$desc,0))
            {
                $this->ajaxReturn(['status'=>1,'msg'=>"操作成功",'url'=>U("User/account_log",array('id'=>$user_id))]);
            }else{
                $this->ajaxReturn(['status'=>-1,'msg'=>"操作失败"]);
            }
            exit;
        }
        $this->assign('user_id',$user_id);
        $this->assign('user',$user);
        return $this->fetch();
    }

    public function recharge(){
    	$timegap = urldecode(I('timegap'));
    	$nickname = I('nickname');
    	$map = array();
    	if($timegap){
    		$gap = explode(',', $timegap);
    		$begin = $gap[0];
    		$end = $gap[1];
    		$map['ctime'] = array('between',array(strtotime($begin),strtotime($end)));
    	}
    	if($nickname){
    		$map['nickname'] = array('like',"%$nickname%");
    	}
    	$count = M('recharge')->where($map)->count();
    	$page = new Page($count);
    	$lists  = M('recharge')->where($map)->order('ctime desc')->limit($page->firstRow.','.$page->listRows)->select();
    	$this->assign('page',$page->show());
        $this->assign('pager',$page);
    	$this->assign('lists',$lists);
    	return $this->fetch();
    }

    public function level(){
    	$act = I('get.act','add');
    	$this->assign('act',$act);
    	$level_id = I('get.level_id');
    	if($level_id){
    		$level_info = D('user_level')->where('level_id='.$level_id)->find();
            // 推荐条件
            $recom_condition = unserialize($level_info['recom_condition']);
            $this->assign('recom_condition',$recom_condition);
    		$this->assign('info',$level_info);
    	}
        $all_level = M('user_level')->where(1)->select();
        $this->assign('all_level',$all_level);

    	return $this->fetch();
    }

    public function levelList(){
    	$Ad =  M('user_level');
        $p = $this->request->param('p');
    	$res = $Ad->order('level_id')->page($p.',10')->select();
    	if($res){
    		foreach ($res as $val){
    			$list[] = $val;
    		}
    	}
    	$this->assign('list',$list);
    	$count = $Ad->count();
    	$Page = new Page($count,10);
    	$show = $Page->show();
    	$this->assign('page',$show);
    	return $this->fetch();
    }

        /**
     * 更换上级
     */
    function change_leader(){

        $uid = I('id');

        $list = M('users')->where('user_id',$uid)->find();

        if(IS_POST){

            $first_leader = I('first_leader');
            
            if($first_leader == 0){

                $first_leader = 0;
            }else{

                $user_first = M('users')->where('user_id',$first_leader)->find();

                if(empty($user_first)){
                    $this->ajaxReturn(array('status' => 0, 'message' => 'ID不存在', 'data' => ''));
                }else{
                    $leader_all = $user_first['leader_all'].'_'.$list['user_id'];
                }
            }

            if(intval($uid) == intval($first_leader)){
                $this->ajaxReturn(array('status' => 0, 'message' => '修改失败,不能填自己的ID', 'data' => ''));
            }

            $r = M('users')->where(array('user_id'=>$uid))->update(['first_leader'=>$first_leader,'leader_all'=>$leader_all]);

            if($r){
                $this->ajaxReturn(array('status' => 1, 'message' => '更改成功'));
            }else{
                $this->ajaxReturn(array('status' => 0, 'message' => '更改失败'));
            }

            

        }
        $this->assign('list',$list);

        return $this->fetch();
    }

    /**
     * 会员等级添加编辑删除
     */
    public function levelHandle()
    {
        $data = I('post.');
        $userLevelValidate = Loader::validate('UserLevel');
        $return = ['status' => 0, 'msg' => '参数错误', 'result' => ''];//初始化返回信息
        if ($data['act'] == 'add') {
            // if (!$userLevelValidate->batch()->check($data)) {
            //     $return = ['status' => 0, 'msg' => '添加失败', 'result' => $userLevelValidate->getError()];
            // } else {
                $r = D('user_level')->add($data);
                if ($r !== false) {
                    $return = ['status' => 1, 'msg' => '添加成功', 'result' => $userLevelValidate->getError()];
                } else {
                    $return = ['status' => 0, 'msg' => '添加失败，数据库未响应', 'result' => ''];
                }
            // }
        }
        if ($data['act'] == 'edit') {
            // if (!$userLevelValidate->scene('edit')->batch()->check($data)) {
            //     $return = ['status' => 0, 'msg' => '编辑失败', 'result' => $userLevelValidate->getError()];
            // } else {
                $data['recom_condition'] = serialize($data['recom_condition']);

                $r = D('user_level')->where('level_id=' . $data['level_id'])->save($data);
                if ($r !== false) {
                    $return = ['status' => 1, 'msg' => '编辑成功', 'result' => $userLevelValidate->getError()];
                } else {
                    $return = ['status' => 0, 'msg' => '编辑失败，数据库未响应', 'result' => ''];
                }
            // }
        }
        if ($data['act'] == 'del') {
            $r = D('user_level')->where('level_id=' . $data['level_id'])->delete();
            if ($r !== false) {
                $return = ['status' => 1, 'msg' => '删除成功', 'result' => ''];
            } else {
                $return = ['status' => 0, 'msg' => '删除失败，数据库未响应', 'result' => ''];
            }
        }
        $this->ajaxReturn($return);
    }

    /**
     * 搜索用户名
     */
    public function search_user()
    {
        $search_key = trim(I('search_key'));
        if(strstr($search_key,'@'))
        {
            $list = M('users')->where(" email like '%$search_key%' ")->select();
            foreach($list as $key => $val)
            {
                echo "<option value='{$val['user_id']}'>{$val['email']}</option>";
            }
        }
        else
        {
            $list = M('users')->where(" mobile like '%$search_key%' ")->select();
            foreach($list as $key => $val)
            {
                echo "<option value='{$val['user_id']}'>{$val['mobile']}</option>";
            }
        }
        exit;
    }

    /**
     * 分销树状关系
     */
    public function ajax_distribut_tree()
    {
          $list = M('users')->where("first_leader = 1")->select();
          return $this->fetch();
    }

    /**
     *
     * @time 2016/08/31
     * @author dyr
     * 发送站内信
     */
    public function sendMessage()
    {
        $user_id_array = I('get.user_id_array');
        $users = array();
        if (!empty($user_id_array)) {
            $users = M('users')->field('user_id,nickname')->where(array('user_id' => array('IN', $user_id_array)))->select();
        }
        $this->assign('users',$users);
        return $this->fetch();
    }

    /**
     * 发送系统消息
     * @author dyr
     * @time  2016/09/01
     */
    public function doSendMessage()
    {
        $call_back = I('call_back');//回调方法
        $text= I('post.text');//内容
        $type = I('post.type', 0);//个体or全体
        $admin_id = session('admin_id');
        $users = I('post.user/a');//个体id
        $message = array(
            'admin_id' => $admin_id,
            'message' => $text,
            'category' => 0,
            'send_time' => time()
        );

        if ($type == 1) {
            //全体用户系统消息
            $message['type'] = 1;
            M('Message')->add($message);
        } else {
            //个体消息
            $message['type'] = 0;
            if (!empty($users)) {
                $create_message_id = M('Message')->add($message);
                foreach ($users as $key) {
                    M('user_message')->add(array('user_id' => $key, 'message_id' => $create_message_id, 'status' => 0, 'category' => 0));
                }
            }
        }
        echo "<script>parent.{$call_back}(1);</script>";
        exit();
    }

    /**
     *
     * @time 2016/09/03
     * @author dyr
     * 发送邮件
     */
    public function sendMail()
    {
        $user_id_array = I('get.user_id_array');
        $users = array();
        if (!empty($user_id_array)) {
            $user_where = array(
                'user_id' => array('IN', $user_id_array),
                'email' => array('neq', '')
            );
            $users = M('users')->field('user_id,nickname,email')->where($user_where)->select();
        }
        $this->assign('smtp', tpCache('smtp'));
        $this->assign('users', $users);
        return $this->fetch();
    }


    /**
     * 意见反馈
     * @return mixed
     */
    public function messageBoard()
    {
        $count = M('complaint_log')->count();
        $Page = new Page($count, 10);
        $list = M('complaint_log')->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->assign('list', $list);
        $this->assign('page', $Page);
        return $this->fetch();
    }

    /**
     * 发送邮箱
     * @author dyr
     * @time  2016/09/03
     */
    public function doSendMail()
    {
        $call_back = I('call_back');//回调方法
        $message = I('post.text');//内容
        $title = I('post.title');//标题
        $users = I('post.user/a');
        $email= I('post.email');
        if (!empty($users)) {
            $user_id_array = implode(',', $users);
            $users = M('users')->field('email')->where(array('user_id' => array('IN', $user_id_array)))->select();
            $to = array();
            foreach ($users as $user) {
                if (check_email($user['email'])) {
                    $to[] = $user['email'];
                }
            }
            $res = send_email($to, $title, $message);
            echo "<script>parent.{$call_back}({$res['status']});</script>";
            exit();
        }
        if($email){
            $res = send_email($email, $title, $message);
            echo "<script>parent.{$call_back}({$res['status']});</script>";
            exit();
        }
    }

    /**
     * 提现申请记录
     */
    public function withdrawals()
    {
    	$this->get_withdrawals_list('0');
        return $this->fetch();
    }

    public function get_withdrawals_list($status=''){
    	$user_id = I('user_id/d');
        $realname = I('realname');
        $bank_card = I('bank_card');

        $start_time = I('start_time');
        $end_time = I('end_time');
        
        if($start_time && $end_time){   
            $where['w.create_time'] =  array(array('gt', strtotime($start_time), array('lt', strtotime($end_time))));
            $this->assign('start_time',$start_time);
            $this->assign('end_time',$end_time);
        }
        $status = empty($status) ? I('status') : $status;
        if(empty($status) || $status === '0'){
            $where['w.status'] =  array('lt',1);
        }
        if($status === '0' || $status > 0) {
            $where['w.status'] = $status;
        }
        $user_id && $where['u.user_id'] = $user_id;
        $realname && $where['w.realname'] = array('like','%'.$realname.'%');
        $bank_card && $where['w.bank_card'] = array('like','%'.$bank_card.'%');
        $export = I('export');
        if($export == 1){
            $strTable ='<table width="500" border="1">';
            $strTable .= '<tr>';
            $strTable .= '<td style="text-align:center;font-size:12px;width:120px;">申请人</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="100">提现金额</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">银行名称</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">银行账号</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">开户人姓名</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">申请时间</td>';
            $strTable .= '<td style="text-align:center;font-size:12px;" width="*">提现备注</td>';
            $strTable .= '</tr>';
            $remittanceList = Db::name('withdrawals')->alias('w')->field('w.*,u.nickname')->join('__USERS__ u', 'u.user_id = w.user_id', 'INNER')->where($where)->order("w.id desc")->select();
            if(is_array($remittanceList)){
                foreach($remittanceList as $k=>$val){
                    $strTable .= '<tr>';
                    $strTable .= '<td style="text-align:center;font-size:12px;">'.$val['nickname'].'</td>';
                    $strTable .= '<td style="text-align:left;font-size:12px;">'.$val['money'].' </td>';
                    $strTable .= '<td style="text-align:left;font-size:12px;">'.$val['bank_name'].'</td>';
                    $strTable .= '<td style="vnd.ms-excel.numberformat:@">'.$val['bank_card'].'</td>';
                    $strTable .= '<td style="text-align:left;font-size:12px;">'.$val['realname'].'</td>';
                    $strTable .= '<td style="text-align:left;font-size:12px;">'.date('Y-m-d H:i:s',$val['create_time']).'</td>';
                    $strTable .= '<td style="text-align:left;font-size:12px;">'.$val['remark'].'</td>';
                    $strTable .= '</tr>';
                }
            }
            $strTable .='</table>';
            unset($remittanceList);
            downloadExcel($strTable,'remittance');
            exit();
        }
        $count = Db::name('withdrawals')->alias('w')->join('__USERS__ u', 'u.user_id = w.user_id', 'INNER')->where($where)->count();
        $Page  = new Page($count,20);
        $list = Db::name('withdrawals')->alias('w')->field('w.*,u.nickname')->join('__USERS__ u', 'u.user_id = w.user_id', 'INNER')->where($where)->order("w.id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
        $show  = $Page->show();
        $this->assign('show',$show);
        $this->assign('list',$list);
        $this->assign('pager',$Page);
        C('TOKEN_ON',false);
    }

    /**
     * 删除申请记录
     */
    public function delWithdrawals()
    {
        $model = M("withdrawals");
        $model->where('id ='.$_GET['id'])->delete();
        $return_arr = array('status' => 1,'msg' => '操作成功','data'  =>'',);   //$return_arr = array('status' => -1,'msg' => '删除失败','data'  =>'',);
        $this->ajaxReturn($return_arr);
    }

    /**
     * 修改编辑 申请提现
     */
    public  function editWithdrawals(){
       $id = I('id');
       $model = M("withdrawals");
       $withdrawals = $model->find($id);
       $user = M('users')->where("user_id = {$withdrawals[user_id]}")->find();
       if($user['nickname'])
           $withdrawals['user_name'] = $user['nickname'];
       elseif($user['email'])
           $withdrawals['user_name'] = $user['email'];
       elseif($user['mobile'])
           $withdrawals['user_name'] = $user['mobile'];

       $this->assign('user',$user);
       $this->assign('data',$withdrawals);
       return $this->fetch();
    }

    /**
     *  处理会员提现申请
     */
    public function withdrawals_update(){
    	$id = I('id/a');
        $data['status']=$status = I('status');
    	$data['remark'] = I('remark');
        if($status == 1) $data['check_time'] = time();
        if($status != 1) $data['refuse_time'] = time();
        $r = M('withdrawals')->where('id in ('.implode(',', $id).')')->update($data);
    	if($r){
    		$this->ajaxReturn(array('status'=>1,'msg'=>"操作成功"),'JSON');
    	}else{
    		$this->ajaxReturn(array('status'=>0,'msg'=>"操作失败"),'JSON');
    	}
    }
    // 用户申请提现
    public function transfer(){
    	$id = I('selected/a');
    	if(empty($id))$this->error('请至少选择一条记录');
    	$atype = I('atype');
    	if(is_array($id)){
    		$withdrawals = M('withdrawals')->where('id in ('.implode(',', $id).')')->select();
    	}else{
    		$withdrawals = M('withdrawals')->where(array('id'=>$id))->select();
    	}
    	$alipay['batch_num'] = 0;
    	$alipay['batch_fee'] = 0;
    	foreach($withdrawals as $val){
            if(!in_array($val['status'],['0','1'])){
                continue;
            }
    		$user = M('users')->where(array('user_id'=>$val['user_id']))->find();
    		if($user['user_money'] < $val['money'])
    		{
    			$data = array('status'=>-2,'remark'=>'账户余额不足');
    			M('withdrawals')->where(array('id'=>$val['id']))->save($data);
    			$this->error('账户余额不足');
    		}else{
    			$rdata = array('type'=>1,'money'=>$val['money'],'log_type_id'=>$val['id'],'user_id'=>$val['user_id']);
    			if($atype == 'online'){
			header("Content-type: text/html; charset=utf-8");
exit("功能正在开发中。。。");
    			}else{
    				accountLog($val['user_id'], ($val['money'] * -1), 0,"管理员处理用户提现申请");//手动转账，默认视为已通过线下转方式处理了该笔提现申请
    				$r = M('withdrawals')->where(array('id'=>$val['id'],'status'=>['in','0,1']))->save(array('status'=>2,'pay_time'=>time()));
    				expenseLog($rdata);//支出记录日志
    			}
    		}
    	}
    	if($alipay['batch_num']>0){
    		//支付宝在线批量付款
    		include_once  PLUGIN_PATH."payment/alipay/alipay.class.php";
    		$alipay_obj = new \alipay();
    		$alipay_obj->transfer($alipay);
    	}
    	if($atype=='hand'){
            $this->success("操作成功!",U('remittance'),3);
        }else{
            $this->ajaxReturn(array('status'=>1,'msg'=>"操作成功"),'JSON');
        }
    }

    /**
     *  转账汇款记录
     */
    public function remittance(){
    	$status = I('status',1);
    	$this->assign('status',$status);
    	$this->get_withdrawals_list($status);
        return $this->fetch();
    }

    /**
     *  审核升级记录
     */
    public function upgrade_level(){

        I('user_id') ? $where['u.user_id']  =   I('user_id') : false; 
        I('apply_status') ? $where['w.apply_status']  =   I('apply_status') : false; 
        I('shopping_type') ? $where['w.shopping_type']  =   I('shopping_type') : false; 
        // var_dump($where);die;
        // $user_id && $where['u.user_id'] = $user_id;

        $count = Db::name('ck_apply')->alias('w')->join('__USERS__ u', 'u.user_id = w.user_id', 'INNER')->where($where)->count();
        $Page  = new Page($count,20);
        $list = Db::name('ck_apply')->alias('w')->field('w.*,u.nickname')->join('__USERS__ u', 'u.user_id = w.user_id', 'INNER')->where($where)->order("w.id desc")->limit($Page->firstRow.','.$Page->listRows)->select();

        foreach ($list as $key => $value) {
            $list[$key]['level_name'] = Db::name('user_level')->where('level_id',$value['level'])->value('level_name');
            $list[$key]['leader_nickname']= Db::name('users')->where('user_id',$value['check_leader_1'])->value('nickname');
            $list[$key]['leader_nickname_2']= Db::name('users')->where('user_id',$value['check_leader_2'])->value('nickname');
            $list[$key]['province'] = M('region')->where('id',$value['province'])->value('name');
            $list[$key]['city']     = M('region')->where('id',$value['city'])->value('name');
            $list[$key]['district'] = M('region')->where('id',$value['district'])->value('name');
        }
        $status = ['-1' => '审核不通过','0' => '审核中','1' => '审核成功'];
        $this->assign('status',$status);

        $show  = $Page->show();
        $this->assign('show',$show);
        $this->assign('list',$list);
        $this->assign('pager',$Page);

        return $this->fetch();
    }

    /**
     *  手动审核升级
     */
    public function manual_check(){
        $id = I('id');
        $info = M('ck_apply')->where(['id' => $id])->find();
        $user = M('users')->where(['user_id' => $info['user_id']])->find();
        if ($info['apply_status'] != 0) $this->ajaxReturn(['status'=>0,'msg'=>"非审核中状态,无法操作"]);
        if ($info['check_leader_2'] && !$info['check_status_2']) {
            $updata['check_status_2'] = 1;
            $updata['check_time_2'] = time();
        }
        if (!$info['check_status_1']) {
            $updata['check_status_1'] = 1;
            $updata['check_time_1'] = time();
        }
        $updata['apply_status'] = 1;
        $updata['apply_time'] = time();

        Db::startTrans();
        try {
            $res = Db::name('ck_apply')->where('id',$id)->save($updata);
            if (!$res) {
                Db::rollback();
                $this->ajaxReturn(['status'=>0,'msg'=>'数据更新失败']);
            }
            if($updata['apply_status'] == 1){
                //审核通过 更新用户等级
                $res1 = Db::name('users')->where('user_id',$info['user_id'])->setField('level',$info['level']);
                if (!$res1) {
                    Db::rollback();
                    $this->ajaxReturn(['status'=>0,'msg'=>'等级更新失败']);
                }
                # 升一星时添加对应层级激活人数
                if ($info['level'] == 2) {
                    $leader_arr = explode('_',$user['leader_all']);        
                    krsort($leader_arr);
                    $leader = array_values($leader_arr);
                    if (count($leader) > 1) {
                        foreach ($leader as $key => $value) {
                            if ($key >= 10 || $key < 1) continue;
                            $res2 = M('users_team')->where(['user_id' => $value])->setInc('team_'.$key,1);
                            if (!$res2) break;
                        }
                        if (!$res2) {
                            Db::rollback();
                            $this->ajaxReturn(['status'=>0,'msg'=>'激活人数更新失败']);
                        }
                    }                        
                }
            }
            Db::commit();
            $this->ajaxReturn(['status'=>1,'msg'=>'审核成功']);
        } catch (\Exception $e){
            $this->ajaxReturn(['status'=>0,'msg'=>'操作失败']);
        }
    }

    /**
     * 签到列表
     * @date 2017/09/28
     */
    public function signList() {
    header("Content-type: text/html; charset=utf-8");
exit("功能正在开发中。。。");
    }


    /**
     * 会员签到 ajax
     * @date 2017/09/28
     */
    public function ajaxsignList() {
    header("Content-type: text/html; charset=utf-8");
exit("功能正在开发中。。。");
    }

    /**
     * 签到规则设置
     * @date 2017/09/28
     */
    public function signRule() {
    header("Content-type: text/html; charset=utf-8");
exit("功能正在开发中。。。");
    }
}