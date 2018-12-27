<?php
/**
 * 微信配置
 * User: Pengfan
 * Date: 2018/12/5
 * Time: 9:33
 */
return [

    //应用APP_ID
    'app_id' => 'wx2421b1c4370ec43b',

    //secret应用秘钥
    'secret' => '31231',

    //商户ID
    'mch_id' => '10000100',

    //支付key
    'key' => '',

    //获取access_token
    'access_token_api' => 'https://api.weixin.qq.com/sns/oauth2/access_token',

    //刷新access_token
    'refresh_token' => 'https://api.weixin.qq.com/sns/oauth2/refresh_token',

    //校验token
    'check_token' => 'https://api.weixin.qq.com/sns/auth',

    //获取用户信息
    'getUser' => 'https://api.weixin.qq.com/sns/userinfo',

    //统一下单
    'unifiedorder' => 'https://api.mch.weixin.qq.com/pay/unifiedorder',

    //退款
    'refund' => 'https://api.mch.weixin.qq.com/secapi/pay/refund',

    //订单查询
    'orderquery' => 'https://api.mch.weixin.qq.com/pay/orderquery',

    //回调地址
    'notify_url' => '',

    //退款推掉
    'refund_notify_url' => ''
];