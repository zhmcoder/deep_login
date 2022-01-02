<?php

use Illuminate\Routing\Router;

Route::group([
    'prefix' => 'Api',
    'namespace' => 'Andruby\\Login\\Controllers',
], function (Router $router) {
    $router->get('WxWeb/callback', 'WxWebController@callback')->name('wxweb.callback');
    $router->get('WxWeb/default_login', 'WxWebController@default_login')->name('wxweb.default_login');
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
    $router->get('WxWeb/is_login', 'WxWebController@is_login')->name('wxweb.is_login');
    $router->post('WxWeb/is_login', 'WxWebController@is_login')->name('wxweb.is_login');
});

