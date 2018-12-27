<?php
/**
 * wx.
 * User: Pengfan
 * Date: 2018/12/5
 * Time: 9:29
 */
namespace wx;

require 'function.php';

class weChat
{

    private $config;


    //初始化
    public function __construct()
    {
        $config = require 'config.php';
        $this->config = $config;
    }


    /**
     * 获取access_token
     *
     * @param    string    $code   换取授权码code
     * @return   object
     */
    public function getAccessToken($code)
    {
        //请求地址
        $url = $this->config['access_token_api'];
        $url .= "?appid={$this->config['app_id']}&secret={$this->config['secret']}&code={$code}&grant_type=authorization_code";

        //获取数据
        $result = $this->curl_request($url);
        return $result;
    }



    /**
     * 刷新access_token
     *
     * @param    string     $refresh_token   刷新的token
     * @return   object
     */
    public function refreshToken($refresh_token)
    {
        $url = $this->config['refresh_token'];
        $url .= "?appid={$this->config['app_id']}&grant_type=refresh_token&refresh_token={$refresh_token}";

        //请求
        $data = $this->curl_request($url);
        return $data;
    }



    /**
     * 校验token是否有效
     *
     * @param     string     $openid        普通用户标识，对该公众帐号唯一
     * @param     string     $access_token  调用接口凭证
     * @return    object
     */
    public function  checkToken($openid,$access_token)
    {
        $url = $this->config['checkToken'];
        $url .= "?access_token={$access_token}&openid={$openid}";

        //请求
        $data = $this->curl_request($url);
        return $data;
    }



    /**
     * 获取用户个人信息
     *
     * @param     string     $access_token   调用凭证
     * @param     string     $openid         普通用户的标识，对当前开发者帐号唯一
     * @return    object
     */
    public function getUserInfo($access_token,$openid)
    {
        $url = $this->config['getUser'];
        $url .= "?access_token={$access_token}&openid={$openid}";

        //请求
        $data = $this->curl_request($url);
        return $data;
    }


    /**
     * 下订单
     *
     * @param    array    $params    订单参数
     * @return   string
     */
    public function unifiedOrder($params)
    {
        $api = strtolower(__FUNCTION__);
        $api = $this->config[$api];
        $xml = assemble($params);
        return $data = http($xml,$api);
    }


    /**
     * 订单查询
     *
     * @param   array   $params   订单数据
     * @return  boolean | null
     */
    public function orderQuery($params)
    {
        $api = strtolower(__FUNCTION__);
        $api = $this->config[$api];
        $xmlData = orderQuery($params);
        $resData = http($xmlData,$api);
        return $resData;
    }



    /**
     * 异步回调
     *
     * @return    array | boolean
     */
    public function notify_url()
    {
        $params = file_get_contents("php://input");
        $data = xmlToArr($params);
        if(!$data) return false;
        $sign = $data['sign'];
        unset($data['sign']);
        if($sign === createSign($data))
        {
            if($data['result_code'] === 'SUCCESS')
            {
                success();
                //支付成功回调
                //do-business....
            }
        }
    }



    /**
     * 退款
     *
     * @return   array | null
     */
    public function refund($params)
    {
        $api = strtolower(__FUNCTION__);
        $api = $this->$api;
        $data = refund($params);
        $data = http($data,$api);
        return $data;
    }




    /**
     * http请求
     *
     * @param     string     $url           请求地址
     * @param     string     $post          post数据(不填则为GET)
     * @param     string     $cookie        提交的$cookies
     * @param     string     $returnCookie  是否返回$cookies
     * @return    object
     */
    public function curl_request($url, $post = '', $cookie = '', $returnCookie = 0){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
        if($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        if($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        if($returnCookie){
            list($header, $body) = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie']  = substr($matches[1][0], 1);
            $info['content'] = $body;
            return $info;
        }else{
            return $data;
        }
    }



    public function __set($name, $value)
    {
        $this->$name = $value;
    }


    public function __get($name)
    {
        return $this->$name;
    }

}

