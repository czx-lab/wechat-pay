<?php
/**
 * 共用方法.
 * User: Pengfan
 * Date: 2018/12/7
 * Time: 15:55
 */




/**
 * 下单参数组装
 *
 * @param    array   $params   参数集合
 * @return   string
 */
function assemble($params)
{
    $app_id = $params['app_id'];
    $mch_id = $params['mch_id'];
    $notify_url = $params['notify_url'];
    $last_param = [
        'appid' => $app_id,
        'mch_id' => $mch_id,
        'nonce_str' => nonce_str(32),
        'body' => $params['body'],
        'out_trade_no' => $params['out_trade_no'],
        'total_fee' => $params['total_amount'] * 100,
        'spbill_create_ip' => spbill_create_ip(),
        'notify_url' => $notify_url,
        'trade_type' => $params['trade_type'],
        'time_expire' => date("YmdHis",time()+3600)
    ];
    $sign = createSign($last_param);
    $last_param['sign'] = $sign;
    $xml = arrToXml($last_param);
    return $xml;
}




/**
 * 退款参数组装
 *
 * @param    array   $params   参数集合
 * @return   string
 */
function refund($params)
{
    $app_id = $params['app_id'];
    $mch_id = $params['mch_id'];
    $notify_url = $params['notify_url'];
    $data = [
        'appid' => $app_id,
        'mch_id' => $mch_id,
        'nonce_str' => nonce_str(32),
        'out_trade_no' => $params['out_trade_no'],
        'out_refund_no' => $params['out_refund_no'],
        'total_fee' => $params['total_fee'] * 100,
        'refund_fee' => $params['refund_fee'] * 100,
        'notify_url' => $notify_url
    ];
    $sign = createSign($data);
    $data['sign'] = $sign;
    $xml = arrToXml($data);
    return $xml;
}




/**
 * 获取正式IP
 *
 * @return   string
 */
function spbill_create_ip()
{
    if($_SERVER['REQUEST_ADDR'])
    {
        $cip = $_SERVER['REMOTE_ADDR'];
    }
    elseif(getenv("REMOTE_ADDR"))
    {
        $cip = getenv("REMOTE_ADDR");
    }
    elseif(getenv("HTTP_CLIENT_TP"))
    {
        $cip = getenv("HTTP_CLIENT_IP");
    }
    else
    {
        $cip = "unknown";
    }
    return $cip;
}


/**
 * 随机字符
 *
 * @param   int   $len   长度
 * @return  string
 */
function nonce_str($len)
{
    $str = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $last = '';
    for ($i = 0; $i < $len; $i ++)
    {
        $last .= $str[mt_rand(0,strlen($str) - 1)];
    }
    return $last;
}



/**
 * 返回微信成功
 */
function success()
{
    echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
    die();
}


/**
 * 订单查询
 *
 * @param   array   $params   订单数据
 * @param   string  $api      请求地址
 * @return  string
 */
function orderQuery($params)
{
    $app_id = $params['app_id'];
    $mch_id = $params['mch_id'];
    $out_trade_no = $params['order_id'];
    $nonce_str = nonce_str(32);
    $param['app_id'] = $app_id;
    $param['mch_id'] = $mch_id;
    $param['out_trade_no'] = $out_trade_no;
    $param['nonce_str'] = $nonce_str;
    $sign = createSign($param);
    $param['sign'] = $sign;
    $xml = arrToXml($param);
    return $xml;
}


/**
 * 生成签名
 *
 * @param   array   $arr   需要签名的数组集合
 * @return   string
 */
function createSign($arr)
{
    //去空
    $arr = array_filter($arr);
    if(isset($arr['sign'])) unset($arr['sign']);

    ksort($arr);
    $key = (require 'config.php')['key'];
    $str = http_build_query($arr)."&key=".$key;
    $str = urldecode($str);
    return  strtoupper(md5($str));
}



/**
 * xml转为数组
 *
 * @param    string    $xml   需要转换的字符
 * @return   array | null
 */
function xmlToArr($xml)
{
    if(!$xml) return null;
    libxml_disable_entity_loader(true);
    $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $data;
}



/**
 * array 转化为 xml
 *
 * @param    array   $arr   需要签名的数组集合
 * @return   string || Exception
 */
function arrToXml($arr)
{
    if(!is_array($arr) || count($arr) < 0){
        throw new Exception("签名数组异常");
    };
    $xml = '<xml>';
    foreach ($arr as $k => $v)
    {
        if(is_numeric($v)) $xml .= "<{$k}>{$v}</{$k}>";
        $xml .= "<{$k}><![CDATA[{$v}]]}></{$k}>";
    }
    $xml .= '</xml>';
    return $xml;
}



/**
 * http请求
 *
 * @param    string   $xml    请求参数
 * @param    string   $url    请求地址
 * @param    int      $second 超时时间
 * @return   object | false
 *
 */
function http($xml, $url, $second = 30)
{
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL, $url);
    if(stripos($url,"https://")!==FALSE)
    {
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    else
    {
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2); //严格校验
    }
    //设置header
    curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    //要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    //设置超时
    curl_setopt($ch, CURLOPT_TIMEOUT, $second);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);//传输文件
    //运行
    $data = curl_exec($ch);
    if($data) {curl_close($ch);return $data;}
    $error = curl_errno($ch);
    curl_close($ch);
    return false;
}