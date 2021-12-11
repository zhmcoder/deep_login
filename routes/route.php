<?php

use Illuminate\Routing\Router;

Route::group([
    'prefix' => 'Api',
    'namespace' => 'Andruby\\Login\\Controllers',
], function (Router $router) {
    $router->get('Weixin/callback', 'WxWebController@callback')->name('weixin.callback');
    $router->get('Weixin/default_login', 'WxWebController@default_login')->name('weixin.default_login');
});

Route::group([
    'prefix' => 'Api',
    'namespace' => 'Andruby\\Login\\Controllers',
], function (Router $router) {
    $router->post('Mobile/verify_code', 'MobileController@verify_code')->name('mobile.verify_code');
    $router->post('Mobile/login', 'MobileController@login')->name('mobile.login');
});

Route::group([
    'prefix' => 'Api',
    'namespace' => 'Andruby\\Login\\Controllers',
    'middleware' => 'login.weixin.web'
], function (Router $router) {
    $router->get('Weixin/is_login', 'WxWebController@is_login')->name('weixin.is_login');
    $router->post('Weixin/is_login', 'WxWebController@is_login')->name('weixin.is_login');
});

