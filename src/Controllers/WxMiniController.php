<?php

namespace Andruby\Login\Controllers;

use Illuminate\Http\Request;
use EasyWeChat\Factory;

/**
 * 微信小程序登录
 * Class WeixinController
 * @package App\Api\Controllers
 */
class WxMiniController extends BaseController
{

    public function callback(Request $request)
    {
        $app_id = $request->input('app_id');
        $app = Factory::officialAccount(config('huaidan.' . $app_id));
        return $app->server->serve();
    }

    public function default_login(Request $request)
    {
        $target_url = $request->input('target_url');
        $app_id = $request->input('app_id');
        $code = $request->input('code');
        debug_log_info('target_url = ' . $target_url);
        debug_log_info('app_id = ' . $app_id);
        debug_log_info('code = ' . $code);

        $app = Factory::officialAccount(config('huaidan.' . $app_id));
        $oauth = $app->oauth;

        $user = $oauth->userFromCode($code);
        debug_log_info('user = ' . json_encode($user));
        $user = $user->toArray();

        $userService = config('deep_login.user_service');
        $userService = new $userService;
        debug_log_info('user open id = ' . $user['id']);
        if (empty($user['id'])) {
            $user['id'] = $user['token_response']['openid'];
        }
        $token_response = $user['token_response'];
        $user_id = $userService->register($user['id'], $user['nickname'], $user['avatar'],
            null, $token_response['access_token'], $token_response['refresh_token'], $token_response['expires_in'],
            $token_response['scope']);

        $target_url = strpos($target_url, '?') > 0 ? ($target_url . '&openid=' . $user['id'])
            : ($target_url . '?openid=' . $user['id']);
        $target_url = $target_url . '&' . config('deep_login.check_login_param') . '=' . $userService->gen_token($user_id);
        debug_log_info('target_url = ' . $target_url);
        header('Location:' . $target_url);
    }

    public function is_login(Request $request)
    {
        $token = $request->input('token', null);
        $data['token'] = $token;
        $this->response(0, 'success', $data);
    }

    public function login(){

    }

}

