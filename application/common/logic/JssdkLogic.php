<?php

namespace app\common\logic;
use think\Model;
/**
 * 分类逻辑定义
 * Class CatsLogic
 * @package common\Logic
 */
class JssdkLogic extends Model
{

  private $appId;
  private $appSecret;

  public function __construct($appId, $appSecret) {
    $this->appId = $appId;
    $this->appSecret = $appSecret;
  }
  // 签名
  public function getSignPackage($url='') {
      $jsapiTicket = $this->getJsApiTicket();
      // 注意 URL 一定要动态获取，不能 hardcode.
      $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
      $url = empty($url) ? "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" : $url;
      $timestamp = time();
      $nonceStr = $this->createNonceStr();
      // 这里参数的顺序要按照 key 值 ASCII 码升序排序
      $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
      $signature = sha1($string);
      $signPackage = array(
          "appId" => $this->appId,
          "nonceStr" => $nonceStr,
          "timestamp" => $timestamp,
          "url" => $url,
          "rawString" => $string,
          "signature" => $signature
      );
      return $signPackage;
  }


    /**
     * 随机字符串
     * @param int $length
     * @return string
     */
    private function createNonceStr($length = 16){
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }


    /**
     * 根据 access_token 获取 icket
     * @return type
     */
    public function getJsApiTicket(){
        $ticket = S('ticket');
        if (!empty($ticket)) return $ticket;
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token}&type=jsapi";
        $return = httpRequest($url, 'GET');
        $return = json_decode($return, 1);
        S('ticket', $return['ticket'], 6000);
        return $return['ticket'];
    }


    /**
     * 获取 网页授权登录access token
     * @return type
     */
    public function getAccessToken(){
        //判断是否过了缓存期
        $access_token = S('access_token');
        if (!empty($access_token)) return $access_token;
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appId}&secret={$this->appSecret}";
        $return = httpRequest($url, 'GET');
        $return = json_decode($return, 1);
        S('access_token', $return['access_token'], 6000);
        return $return['access_token'];
    }

    // 获取一般的 access_token
    public function get_access_token(){
        //判断是否过了缓存期
        $wechat = M('wx_user')->find();
        $expire_time = $wechat['web_expires'];
        if ($expire_time > time()) {
            return $wechat['web_access_token'];
        }
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$wechat['appid']}&secret={$wechat['appsecret']}";
        $return = httpRequest($url, 'GET');
        $return = json_decode($return, 1);
        $web_expires = time() + 6000; // 提前200秒过期
        M('wx_user')->where(array('id' => $wechat['id']))->save(array('web_access_token' => $return['access_token'], 'web_expires' => $web_expires));
        return $return['access_token'];
    }

    /*
     * 向用户推送消息
     */
    public function push_msg($openid,$content){
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
        $post_arr = array(
            'touser' => $openid,
            'msgtype' => 'text',
            'text' => array(
                'content' => $content,
            )
        );
        $post_str = json_encode($post_arr, JSON_UNESCAPED_UNICODE);
        $return = httpRequest($url, 'POST', $post_str);
        $return = json_decode($return, true);
    }

    public function send_template_message($order){
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";
        $open_id = M('users')->where(array('user_id' => $order['user_id']))->getField('openid');
        error_log($open_id . date('Ymd H:i:s'), 3, 'test.log');
        if (!empty($open_id)) {
            $tempalte = array(
                'touser' => $open_id,
                'template_id' => 'GiApFXcJXrxslu-_0S3Ynn56NP3emHVttcAieUfDEog',
                'url' => SITE_URL . '/mobile', //点击后跳转地址
                'topcolor' => '#7B68EE',
                'data' => array(
                    'first' => array('value' => urlencode("您好，您的订单已支付成功"), 'color' => '#743A3A'),
                    'product' => array('value' => urlencode($order['goods_name']), 'color' => "#173177"),
                    'price' => array('value' => urlencode($order['total_amount'] . "元"), 'color' => "#173177"),
                    'time' => array('value' => urlencode(date("Y年m月d日  H:i:s")), 'color' => "#173177"),
                    'remark' => array('value' => urlencode("您的订单已提交，我们将尽快发货。祝您生活愉快"), 'color' => "#173177"),
                ));
            $json_template = json_encode($tempalte);
            $res = httpRequest($url, 'post', urldecode($json_template));
            return json_decode($res, true);
    	}
    }

    /**
     * 获取无限制的小程序码
     * @param array    $post_arr     参数
     * @return boolean
     */
    public function getwxacodeunlimit($post_arr){  
        $access_token = $this->getAccessToken();
        if (!$access_token) {
            return false;
        }
        $return = $this->getAppletCodeid($post_arr,$access_token);
        return $return;
    }

    public function getAppletCodeid($userData, $accessToken){
        $data = [
            // 'width' => 430,
            'auto_color' => false,
            'line_color' => ['r' => 43, 'g' => 162, 'b' => 70],
        ];
        $data = array_merge($data, $userData);
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token={$accessToken}";
        $curl = curl_init(); //初始化
        curl_setopt($curl, CURLOPT_URL, $url); //设置选项，包括URL
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_HEADER, false); //设定是否输出页面内容
        curl_setopt($curl, CURLOPT_TIMEOUT, 10); //设置cURL允许执行的最长秒数。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //设定是否显示头信息
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data)); //把你分析的回复表单的参数分别赋值
        $output = curl_exec($curl);
        curl_close($curl);
        $file_content = chunk_split(base64_encode($output));//base64编码
        $img = 'data:image/png;base64,' . $file_content;//合成图片的base64编码
        $oldchar = array(" ", "\n", "\r",);
        $newchar = array("", "", "",);
        return $images = str_replace($oldchar, $newchar, $img);
    }
}