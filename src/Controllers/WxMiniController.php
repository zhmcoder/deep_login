<?php

namespace Andruby\Login\Controllers;

use Andruby\Login\Services\Interfaces\IUserService;
use Andruby\Login\Services\UserService;
use Andruby\Login\Services\XcxService;
use Illuminate\Http\Request;

/**
 * 微信小程序登录
 * Class WxMiniController
 * @package Andruby\Login\Controllers
 */
class WxMiniController extends BaseController
{
    public function login(Request $request)
    {
        $code = $request->input('code');
        $loginData = $request->input('loginData');

        if (is_string($loginData)) {
            $loginData = json_decode($loginData, true);
        }

        $mini_app_id = $request->input('mini_app_id', config('deep_login.wx_mini_app_id_default'));


        $wxSession = XcxService::getXcxSession($mini_app_id,
            config('deep_login.' . $mini_app_id . '.app_secret'), $code);
        if (!$wxSession) {
            $this->responseJson(-1, "微信授权失败,请重新授权。", null);
        }

        if (key_exists('encryptedData', $loginData) && key_exists('iv', $loginData)) {
            $user_info = XcxService::decryptData($mini_app_id, $wxSession['openid'],
                $loginData['encryptedData'], $loginData['iv']);
            debug_log_info('user info = ' . json_encode($user_info));
        } else {
            $user_info = $loginData['userInfo'];
        }
        debug_log_info('user info 1 = ' . json_encode($loginData['userInfo']));

        debug_log_info('user info = ' . json_encode($user_info));
        if (empty($user_info) || (key_exists('status', $user_info) && $user_info['status'] == '1001')) {
            $this->responseJson('1001', '需要登录');
        }
        $user_info['openId'] = $wxSession['openid'];

        //create ucentermember
        $userService = config('deep_login.user_service');
//        $userService = new $userService;
        $userService = new UserService();
        $user_info = $userService->dealWxInfo($user_info);

        $user_id = $userService->register($user_info['openId'], $user_info['nickname'],
            $user_info['avatar'], $user_info['unionid'], null,
            IUserService::USER_TYPE_WX_MINI, null,
            null, null);
        $data = $userService->userInfo($user_id);
        $data['token'] = $userService->genToken($user_id);
        $this->responseJson('0', '', $data);
    }


}

