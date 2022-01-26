<?php

return [

    //implements IUserService
    'user_service' => \Andruby\Login\Services\UserService::class,
    //implements ISmsService
    'sms_service' => \Andruby\Login\Services\SmsService::class,

    'check_login_type' => 'url',//cookie,url
    'check_login_param' => 'token',
    'default_head_url' => 'http://img.zdapk.cn/head_defualt.jpeg',
    'nickname_pre' => '用户',

    'default_sms_code' => '1111',
    'ignore_mobile' => ['13581714392'],
    'sms_resend_time' => 60,
    'sms_expired_time' => 10 * 60,
    'sms_send' => \Andruby\Login\Libs\Sms\AliSms::class,

    'netease_sms' => [
        'app_key' => '',
        'app_secret' => '',
        'nonce' => '',
        'template_id' => [
            'default_app_id' => ''
        ],//短信登录注册模板
        'template_id_notify' => [
            'default_app_id' => ''
        ],//消息通知模板
    ],

    'ali_sms' => [
        'access_key_id' => '',
        'access_key_secret' => '',
        'sign_name' => [
            'default_app_id' => ''
        ],
        'template_code' => [
            'default_app_id' => ''
        ]
    ],
    //小程序登录配置
    'wx_mini_app_id_default' => '',
    'wx21e205bfb5ccdefd' => [
        'app_secret' => '',
        'app_id' => 'wx21e205bfb5ccdefd',
    ],
];
