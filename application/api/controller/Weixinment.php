<?php

namespace app\api\controller;
use think\Request;
use My\WeixinPay;
use My\DataReturn;
class Weixinment extends Base {

    public function getPay(){
        $session = session('user');
        $user = M('users')->where('user_id',$session['user_id'])->find();
        $data['account'] = I('account');
        $openid = $user['openid'];
        $data['user_id'] = $user['user_id'];
        $data['nickname'] = $user['nickname'];
        $data['order_sn'] = 'recharge'.get_rand_str(10,0,1);
        $data['ctime'] = time();
        $order_id = M('recharge')->add($data);
        $payment_arr = M('Plugin')->where("`type` = 'payment'")->getField("code,name");
        M('recharge')->where("order_id", $order_id)->save(array('pay_code'=>'miniAppPay','pay_name'=>$payment_arr['miniAppPay']));
        if($order_id){
            DataReturn::returnJson('200','生成订单成功',$data);
        }else{
            DataReturn::returnJson('400','生成订单失败');
        }
    }
    //发起请求支付
    public function payfree()
    {
        $user_id = I('user_id');
        $order_sn  = I('order_sn');
        $total_fee = I('total_fee');
        $user = M('users')->where('user_id',$user_id)->find();
        $paymentPlugin = M('Plugin')->where("code='miniAppPay' and  type = 'payment' ")->find(); // 找到微信支付插件的配置
        $config_value = unserialize($paymentPlugin['config_value']); // 配置反序列化
        $appid = $config_value['appid']; // * APPID：绑定支付的APPID（必须配置，开户邮件中可查看）
        $mch_id = $config_value['mchid']; // * MCHID：商户号（必须配置，开户邮件中可查看）
        $key    = $config_value['key']; //
        $open_id   = $user['openid'];
        if (!$open_id || !$order_sn || !$total_fee) {
            DataReturn::returnJson('400', '系统发生错误，请稍候重试！');
        }
        $recharge = M('recharge')->where('order_sn', $order_sn)->find();
        $total_fee = $recharge['account']*100;
        if (!$recharge) {
            DataReturn::returnJson('400', '订单不存在！');
        }
        $body = "账户充值";
        $total_fee = 1;
        $weixinpay = new WeixinPay($appid, $open_id, $mch_id, $key, $order_sn, $body, $total_fee);
        $return    = $weixinpay->pay();
        DataReturn::returnJson('200', '支付返回结果！', $return);
    }

    //支付回调处理
    public function wxpayment()
    {
        $postXml = $GLOBALS["HTTP_RAW_POST_DATA"]; //接收微信参数
        if (empty($postXml)) {
            return false;
        } else {
            $weixinReturn = $this->xmlToArray($postXml);
            $openid       = $weixinReturn['openid'];
            $out_trade_no = $weixinReturn['out_trade_no'];
            $transaction_id = $weixinReturn['transaction_id'];
            $return = $this->orderquery($transaction_id);
            // M('test')->add(['name'=>json_encode($return)]);
            if ($return['trade_state'] == 'SUCCESS') {
                $recharge = M('recharge')->where('order_sn', $out_trade_no)->find();
                if ($out_trade_no && $recharge['pay_status'] == 0) {
                    $data['pay_status']   = 1;
                    $data['pay_time'] = time();
                    $buseness    = M('users')->where('user_id', $recharge['user_id'])->setInc('user_money',$recharge['account']);
                    $transferlog = M('recharge')->where('order_sn', $out_trade_no)->update($data);
                }
                return '<xml>
                      <return_code><![CDATA[SUCCESS]]></return_code>
                      <return_msg><![CDATA[OK]]></return_msg>
                      </xml>';
            } else {
                $data['status']   = 2;
                $data['pay_time'] = time();
                $recharge      = M('recharge')->where('order_sn', $out_trade_no)->update($data);
                return '<xml>
                      <return_code><![CDATA[FAIL]]></return_code>
                      <return_msg><![CDATA[NO]]></return_msg>
                      </xml>';
            }
        }
    }
    //查询订单接口
    private function orderquery($transaction_id) {
        $paymentPlugin = M('Plugin')->where("code='miniAppPay' and  type = 'payment' ")->find(); // 找到微信支付插件的配置
        $config_value  = unserialize($paymentPlugin['config_value']); // 配置反序列化
        $appid  = $config_value['appid']; // * APPID：绑定支付的APPID（必须配置，开户邮件中可查看）
        $mch_id = $config_value['mchid']; // * MCHID：商户号（必须配置，开户邮件中可查看）
        $key    = $config_value['key']; //
        $url    = "https://api.mch.weixin.qq.com/pay/orderquery";
        $parameters = [
            'appid'          => $appid, //小程序ID
            'mch_id'         => $mch_id, //商户号
            'nonce_str'      => $this->createNoncestr(), //随机字符串
            'transaction_id' => $transaction_id,
        ];
        $parameters['sign'] = $this->getSign($parameters,$key);//签名
        // M('test')->add(['name'=>json_encode($parameters)]);
        $xmlData = $this->arrayToXml($parameters);
        $return = $this->xmlToArray($this->postXmlCurl($xmlData, $url, 60));
        return $return;
    }
    //作用：产生随机字符串，不长于32位
    private function createNoncestr($length = 10) {
        $random = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $random = str_split($random);
        shuffle($random);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= $random[$i];
        }
        return md5(time().$string);
    }
    //数组转换成xml
    private function arrayToXml($arr) {
        $xml = "<root>";
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $xml .= "<" . $key . ">" . arrayToXml($val) . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            }
        }
        $xml .= "</root>";
        return $xml;
    }
    private function postXmlCurl($xml, $url, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); //严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);


        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);
        set_time_limit(0);


        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new WxPayException("curl出错，错误码:$error");
        }
    }
    //作用：生成签名
    private function getSign($Obj,$key) {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //签名步骤二：在string后加入KEY
        $String = $String . "&key=" . $key;
        //签名步骤三：MD5加密
        $String = md5($String);
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        return $result_;
    }


    ///作用：格式化参数，签名过程需要使用
    private function formatBizQueryParaMap($paraMap, $urlencode) {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar;
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    //将xml格式转换成数组
    private function xmlToArray($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val       = json_decode(json_encode($xmlstring), true);
        return $val;
    }
}
