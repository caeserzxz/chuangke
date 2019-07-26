<?php


use think\Db;
use think\Cache;
use app\common\logic\JssdkLogic;

function getwxconfig()
{
    $wx_config = Cache::get('weixin_config');
    if (!$wx_config) {
        $wx_config = M('wx_user')->find(); //获取微信配置
        Cache::set('weixin_config', $wx_config, 0);
    }
    $jssdk = new JssdkLogic($wx_config['appid'], $wx_config['appsecret']);
    $signPackage = $jssdk->GetSignPackage();
    return $signPackage;
}

function addressToCart2Url($address_id){
    $cookie = cookie('url_cart2');
    $cookie['address_id'] = $address_id;
    switch($cookie['source']){
        //购物车
        case 'cart2':
            $url = U('/Mobile/Cart/cart2',$cookie);
            break;
        case 'buy_now':
            //直接购买
            $cookie['action'] = $cookie['source'];
            $url = U('/Mobile/Cart/cart2',$cookie);
            break;
        case 'integral':
            //积分
            //array('address_id'=>$list['address_id'],'goods_id'=>$Request.param.goods_id,'goods_num'=>$Request.param.goods_num,'item_id'=>$Request.param.item_id))
            $url = U('/Mobile/Cart/integral',$cookie);
            break;
        default:
            $url = '';
            break;
    }
    return $url;
}
