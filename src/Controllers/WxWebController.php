<?php

namespace Andruby\Login\Controllers;

use Andruby\Login\Services\Interfaces\IUserService;
use Illuminate\Http\Request;
use EasyWeChat\Factory;

/**
 * 微信公众号web相关登录
 * Class WxWebController
 * @package Andruby\Login\Controllers
 */
class WxWebController extends BaseController
{
    public function callback(Request $request)
    {
        $app_id = $request->input('app_id');
        $app = Factory::officialAccount(config('deep_login.' . $app_id));
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

        $app = Factory::officialAccount(config('deep_login.' . $app_id));
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
        $unionid = !empty($token_response['unionid']) ? $token_response['unionid'] : $user['id'];
        $user_id = $userService->register($user['id'], $user['nickname'], $user['avatar'],
            $unionid, null, IUserService::USER_TYPE_WX_WEB, $token_response['access_token'], $token_response['refresh_token'],
            $token_response['expires_in'], $token_response['scope']);
        $target_url = strpos($target_url, '?') > 0 ? ($target_url . '&openid=' . $user['id'])
            : ($target_url . '?openid=' . $user['id']);
        $target_url = $target_url . '&' . config('deep_login.check_login_param') . '=' . $userService->genToken($user_id);
        debug_log_info('target_url = ' . $target_url);
        header('Location:' . $target_url);
    }

    public function is_login(Request $request)
    {
        $token = $request->input('token', null);

        $data['token'] = $token;
        $this->responseJson(self::STATUS_SUCCESS, 'success', $data);
    }

    // 微信静默授权
    public function wx_login(Request $request)
    {
        $target_url = $request->input('target_url');
        $app_id = $request->input('app_id');
        $code = $request->input('code');
        $user_token = $request->input('user_token');

        debug_log_info('target_url = ' . $target_url);
        debug_log_info('app_id = ' . $app_id);
        debug_log_info('code = ' . $code);
        debug_log_info('user_token = ' . $user_token);

        $app = Factory::officialAccount(config('deep_login.' . $app_id));
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

        $userInfo = $userService->userInfoByToken($user_token);
        debug_log_info('userInfo = ' . json_encode($userInfo));
        $userService->updateOpenid($userInfo['id'], $user['id']);

        debug_log_info('target_url = ' . $target_url);
        header('Location:' . $target_url);
    }
}

