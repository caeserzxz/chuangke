<?php

namespace app\mobile\controller;
use app\common\logic\JssdkLogic;
use Think\Db;
use think\Page;
use think\Cache;
class Index extends MobileBase {


    /*------------------------------------------------------ */
	//-- 自定义首页
	/*------------------------------------------------------ */
	public function shopIndex(){
		$mkey = 'shopIndex_web';
		$theme = M('shop_page_theme')->find();
		if (empty($theme)) return $this->redirect('index');
		//$body = Cache::get($mkey);
		if (empty($body)){
			$ShopPageTheme = new \app\shoppage\model\ShopPageTheme();
			$d_products = $ShopPageTheme->d_products();			
			$page = json_decode($theme['page'],true);
			$Goods = M('Goods');
			foreach ($page['pageElement'] as $key=>$row){
				$_body = '';
				if ($row['componentType'] != 'search'){				
					if ($_body){
						$body .= $_body;
						continue;
					}
				}
				if ($row['componentType'] == 'products'){
					 foreach ($row['data'] as $keyb=>$rowb){
						 if ($rowb['visible'] == 0) continue;
						 if (empty($rowb['goodsDataType']) || $rowb['goodsDataType'] == 'custom'){
							  foreach ($rowb['products'] as $keyc=>$rowc){
								  if (!is_numeric($rowc['id'])){
									$rowb['products'][$keyc] = $d_products[$rowc['id']];
								  }else{
									  unset($map);
									  $map['goods_id'] = $rowc['id'];								 
									  $grow = $Goods->where($map)->find();
									  if(!$grow['is_on_sale'])
									  {
										 unset($rowb['products'][$keyc]);
										 continue; 
									  }
									  $rowc['thumb']['url'] = $grow['original_img'];
									  $rowc['name'] = $grow['goods_name'];
									  $rowc['par_price'] = $grow['market_price'];
									  $rowc['sale_price'] = $grow['shop_price'];
									  $rowc['vip_price'] = $grow['vip_price'];
									  $rowc['sale_count'] = $grow['sales_sum'];
									  $rowb['products'][$keyc] = $rowc;
								  }
							  }
						 }else{
							unset($map,$rowb['products']);							
							if ($rowb['goodsDataType'] == 'recommend'){
								$map['is_recommend'] = 1;
							}elseif ($rowb['goodsDataType'] == 'new'){
								$map['is_new'] = 1;
							}else{
								$map['is_hot'] = 1;
							}
							$map['is_on_sale'] = 1;	 
							$grows = $Goods->field('goods_id,original_img,goods_name,market_price,shop_price,sales_sum')->where($map)->order('sort desc')->limit($row['dataLimit'])->select();							
							foreach ($grows as $grow){
								$rowc['id'] = $grow['goods_id'];
								$rowc['thumb']['url'] = $grow['original_img'];
								$rowc['name'] = $grow['goods_name'];
								$rowc['par_price'] = $grow['market_price'];
								$rowc['sale_price'] = $grow['shop_price'];
								$rowc['vip_price'] = $grow['vip_price'];
								$rowc['sale_count'] = $grow['sales_sum'];
								$rowb['products'][$rowc['id']] = $rowc;
							}
						 }						
						 $row['data'][$keyb] = $rowb;
					 }
					 
				}
				
				$this->assign('_key', $_key);
				$this->assign('theme_row', $row);
				$_body = $this->fetch('page/'.$row['componentType']);				
				$body .= $_body;
			}
			Cache::set($mkey,$body,30);
		}
		$this->assign('theme', $theme);
		$this->assign('body', $body);
		return $this->fetch('page/index');
	}

    public function index(){
		//return $this->redirect('shopIndex');
        $thems = M('goods_category')->where('level=1')->order('sort_order')->limit(9)->cache(true, TPSHOP_CACHE_TIME)->select();
        $this->assign('thems', $thems);

        //秒杀商品
        $now_time = time();  //当前时间
        if (is_int($now_time / 7200)) {      //双整点时间，如：10:00, 12:00
            $start_time = $now_time;
        } else {
            $start_time = floor($now_time / 7200) * 7200; //取得前一个双整点时间
        }
        $end_time = $start_time + 7200;   //结束时间
//        $flash_sale_list = M('goods')->alias('g')
//            ->field('g.goods_id,f.price,s.item_id')
//            ->join('flash_sale f', 'g.goods_id = f.goods_id', 'LEFT')
//            ->join('__SPEC_GOODS_PRICE__ s', 's.prom_id = f.id AND g.goods_id = s.goods_id', 'LEFT')
//            ->where("start_time = $start_time and end_time = $end_time")
//            ->limit(3)->select();
//        $this->assign('flash_sale_list', $flash_sale_list);
        $this->assign('start_time', $start_time);
        $this->assign('end_time', $end_time);

        $where = array('is_on_sale' => 1, 'prom_type' => 0, 'is_recommend' => 1,);
        $pagesize = C('PAGESIZE');  //每页显示数
        $list = M('goods')->where($where)->field(['goods_id', 'goods_name', 'shop_price'])->page(1, $pagesize)->order('sort desc,goods_id desc')->select();
        $this->assign('list', $list);
        //查询购物车商品数量
        $cartLogic=new \app\common\logic\CartLogic;
        $user = session('user');
        $cartLogic->setUserId($user['user_id']);
        $this->assign('cart_goods_num', $cartLogic->getUserCartGoodsNum());
        return $this->fetch();
    }

    public function _index_goods()
    {
        $where = array('is_on_sale' => 1, 'prom_type' => 0, 'is_recommend' => 1,);
        $pagesize = C('PAGESIZE');  //每页显示数
        $p = I('p') ? I('p') : 1;
        $list = M('goods')->where($where)->field(['goods_id', 'goods_name', 'shop_price'])->page($p, $pagesize)->order('sort desc,goods_id desc')->select();
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 分类列表显示
     */
    public function categoryList(){
        return $this->fetch();
    }

    /**
     * 商品列表页
     */
    public function goodsList(){
        $id = I('get.id/d', 0); // 当前分类id
        $lists = getCatGrandson($id);
        $this->assign('lists', $lists);
        return $this->fetch();
    }

    public function ajaxGetMore(){
        $p = I('p/d', 1);
        $where = ['is_recommend' => 1, 'is_on_sale' => 1, 'virtual_indate' => ['exp', ' = 0 OR virtual_indate > ' . time()]];
        $favourite_goods = Db::name('goods')->where($where)->order('goods_id DESC')->page($p, C('PAGESIZE'))->cache(true, TPSHOP_CACHE_TIME)->select();//首页推荐商品
        $this->assign('favourite_goods', $favourite_goods);
        return $this->fetch();
    }

    //微信Jssdk 操作类 用分享朋友圈 JS
    public function ajaxGetWxConfig(){
        $askUrl = I('askUrl');//分享URL
        $weixin_config = M('wx_user')->find(); //获取微信配置
        $jssdk = new JssdkLogic($weixin_config['appid'], $weixin_config['appsecret']);
        $signPackage = $jssdk->GetSignPackage(urldecode($askUrl));
        if ($signPackage) {
            $this->ajaxReturn($signPackage, 'JSON');
        } else {
            return false;
        }
    }

}