<?php

namespace Andruby\Login\Controllers;

use Andruby\Login\Services\Interfaces\IUserService;
use Andruby\Login\Services\UserService;
use Andruby\Login\Services\XcxService;
use Andruby\Login\Validates\WxMiniValidate;
use Illuminate\Http\Request;

/**
 * 微信小程序登录
 * Class WxMiniController
 * @package Andruby\Login\Controllers
 */
class WxMiniController extends BaseController
{
    public function login(Request $request, WxMiniValidate $validate)
    {
        $validate_result = $validate->login($request->only(['code', 'loginData']));
        if ($validate_result) {
            $code = $request->input('code');
            $loginData = $request->input('loginData');

            if (is_string($loginData)) {
                $loginData = json_decode($loginData, true);
            }

            $mini_app_id = $request->input('mini_app_id', config('deep_login.wx_mini_app_id_default'));

            $wxSession = XcxService::getXcxSession($mini_app_id, config('deep_login.' . $mini_app_id . '.app_secret'), $code);
            if (!$wxSession) {
                $this->responseJson(self::CODE_ERROR_CODE, "微信授权失败,请重新授权。");
            }

            if (key_exists('encryptedData', $loginData) && key_exists('iv', $loginData)) {
                $userInfo = XcxService::decryptData($mini_app_id, $wxSession['openid'], $loginData['encryptedData'], $loginData['iv']);
                debug_log_info('userInfo = ' . json_encode($userInfo));
            } else {
                $userInfo = $loginData['userInfo'];
            }
            debug_log_info('loginData userInfo = ' . json_encode($loginData['userInfo']));
            debug_log_info('userInfo = ' . json_encode($userInfo));

            if (empty($userInfo) || (key_exists('status', $userInfo) && $userInfo['status'] == '1001')) {
                $this->responseJson('1001', '需要登录');
            }
            $userInfo['openId'] = $wxSession['openid'];
            $userInfo['unionid'] = $wxSession['unionid'] ?: $wxSession['openid'];

            $userService = config('deep_login.user_service');
            $userService = new $userService;
            $userInfo = $userService->dealWxInfo($userInfo);

            $user_id = $userService->register($userInfo['openId'], $userInfo['nickname'],
                $userInfo['avatar'], $userInfo['unionid'], null,
                IUserService::USER_TYPE_WX_MINI, null,
                null, null);

            $data = $userService->userInfo($user_id);
            // $data['token'] = $userService->genToken($user_id);
            $this->responseJson(self::CODE_SUCCESS_CODE, 'success', $data);

        } else {
            $this->responseJson(self::CODE_ERROR_CODE, $validate->message);
        }
    }
}

