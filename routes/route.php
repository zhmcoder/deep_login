<?php

use Illuminate\Routing\Router;

Route::group([
    'prefix' => 'Api',
    'namespace' => 'Andruby\\Login\\Controllers',
], function (Router $router) {
    // 微信授权登录
    $router->get('WxWeb/callback', 'WxWebController@callback')->name('wxweb.callback');
    $router->get('WxWeb/default_login', 'WxWebController@default_login')->name('wxweb.default_login');
    //
    $router->get('WxQrcode/qrcode', 'WxQrcodeController@qrcode')->name('WxQrcode.qrcode');
    $router->get('WxQrcode/default_login', 'WxQrcodeController@default_login')->name('WxQrcode.default_login');

    // 微信开放平台接口
    $router->post('Wechat/ticket', 'WeChat\OpenPlatformController@ticket'); // 授权事件接收配置
    $router->post('Wechat/event/{appId}', 'WeChat\OpenPlatformController@event'); // 公众号事件
    $router->get('Wechat/sendTemplateMsg/{appId}', 'WeChat\OpenPlatformController@sendTemplateMsg'); // 发送消息

    // 微信开放平台配置
    $router->get('Wechat/pre-authorized/{appId}', 'WeChat\MpController@preAuthorizationJump'); // 微信开放平台授权页面
    $router->get('Wechat/authorized/{appId}', 'WeChat\MpController@authorized'); // 微信开放平台授权回调
    $router->get('Wechat/pushSwitch/{appId}', 'WeChat\MpController@smartPushSwitch'); // 公众号 - 推送开关
});

Route::group([
    'prefix' => 'Api',
    'namespace' => 'Andruby\\Login\\Controllers',
], function (Router $router) {
    //手机号登录
    $router->post('Mobile/verify_code', 'MobileController@verify_code')->name('mobile.verify_code');
    $router->post('Mobile/login', 'MobileController@login')->name('mobile.login');
    // 手机号登录 & 微信静默授权
    $router->post('Mobile/wx_login', 'MobileController@wx_login')->name('mobile.wx_login');
    $router->get('WxWeb/wx_login', 'WxWebController@wx_login')->name('wxweb.wx_login');
    $router->get('Mobile/callback', 'MobileController@callback')->name('mobile.callback');
    //小程序登录
    $router->post('WxMini/login', 'WxMiniController@login')->name('wx_mini.login');
    //小程序静默登录
    $router->post('WxMini/wx_login', 'WxMiniController@wx_login')->name('wx_mini.wx_login');
    // 图形验证码
    $router->get('img/get_img_code/{id}', 'EmailController@get_img_code')->middleware('imgCode')->name('img.get_img_code');
    // 邮箱登录
    $router->post('Email/login', 'EmailController@login')->name('email.login');
});

Route::group([
    'prefix' => 'Api',
    'namespace' => 'Andruby\\Login\\Controllers',
    'middleware' => 'login.weixin.web'
], function (Router $router) {
    $router->get('WxWeb/is_login', 'WxWebController@is_login')->name('wxweb.is_login');
    $router->post('WxWeb/is_login', 'WxWebController@is_login')->name('wxweb.is_login');
});

