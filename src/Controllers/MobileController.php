<?php

namespace Andruby\Login\Controllers;

use Andruby\Login\Models\Member;
use Andruby\Login\Validates\MobileValidate;
use EasyWeChat\Factory;
use Illuminate\Http\Request;

/**
 * 手机号登录
 * Class MobileController
 * @package Andruby\Login\Controllers
 */
class MobileController extends BaseController
{
    public function verify_code(Request $request, MobileValidate $validate)
    {
        $validate_result = $validate->verify_code($request->only(['mobile', 'img_code']));
        if ($validate_result) {
            $mobile = $request->input('mobile');
            $img_code = $request->input('img_code');
            $smsService = config('deep_login.sms_service');
            $smsService = new $smsService;
            $msg = '验证码已发送！';
            $data = [];
            if (empty($img_code)) {
                if ($smsService->isImgCode($mobile)) {
                    $data['img_code'] = route('img.get_img_code', [md5(config('deep_login.aes_key') . $mobile)]);
                    $verify_code = true;
                    $msg = '图片验证码已发送！';
                } else {
                    $verify_code = $smsService->sendVerifyCode($mobile);
                }
            } else {
                if ($smsService->verifyImgCode(md5(config('deep_login.aes_key') . $mobile), $img_code)) {
                    $verify_code = $smsService->sendVerifyCode($mobile);
                } else {
                    $verify_code = false;
                    $msg = '图片验证码验证失败';
                }
            }
            $this->responseJson($verify_code ? self::CODE_SUCCESS_CODE : self::CODE_SHOW_MSG, $msg, $data);
        } else {
            $this->responseJson(self::CODE_ERROR_CODE, $validate->message);
        }
    }

    public function login(Request $request, MobileValidate $validate)
    {
        $validate_result = $validate->verify_code($request->only(['mobile', 'verify_code']));
        if ($validate_result) {
            $mobile = $request->input('mobile');
            $verify_code = $request->input('verify_code');
            $smsService = config('deep_login.sms_service');
            $smsService = new $smsService;
//            $smsService = new SmsService();
            if ($smsService->verifyCode($mobile, $verify_code)) {
                $userService = config('deep_login.user_service');
                $userService = new $userService;
//                $userService = new UserService();
                $userId = $userService->mobile($mobile);
                if ($userId) {
                    $userInfo = $userService->userInfo($userId);
                    $this->responseJson(self::CODE_SUCCESS_CODE, '登录成功', $userInfo);
                } else {
                    $this->responseJson(self::CODE_SHOW_MSG, '登录失败');
                }
            } else {
                $this->responseJson(self::CODE_SHOW_MSG, '验证码失效');
            }
        } else {
            $this->responseJson(self::CODE_ERROR_CODE, $validate->message);
        }
    }

    // 手机号登录 & 微信静默授权
    public function wx_login(Request $request, MobileValidate $validate)
    {
        $validate_result = $validate->verify_code($request->only(['mobile', 'verify_code']));
        if ($validate_result) {
            $mobile = $request->input('mobile');
            $verify_code = $request->input('verify_code');
            $target_url = $request->input('target_url', url()->full()); // 重定向地址
            $app_id = env('APP_ID') ?? $request->input('app_id');
            $source = $request->input('source');
            $config = config('deep_login.' . $app_id);

            $smsService = config('deep_login.sms_service');
            $smsService = new $smsService;
            if ($smsService->verifyCode($mobile, $verify_code)) {

                $userService = config('deep_login.user_service');
                $userService = new $userService;

                $userId = $userService->mobile($mobile);
                if ($userId) {
                    $userInfo = $userService->userInfo($userId);

                    $userInfo['redirectUrl'] = '';
                    if ($app_id && $config && $config['wx_login'] && empty($userInfo['openid']) && $source == Member::SOURCE_WX) {
                        $config['oauth'] = $config['wx_login'];
                        $app = Factory::officialAccount($config);
                        $oauth = $app->oauth;
                        $redirectUrl = $oauth->redirect(route('wxweb.wx_login', ['app_id' => $app_id, 'target_url' => $target_url, 'user_token' => $userInfo['token']]));
                        debug_log_info('redirectUrl = ' . $redirectUrl);
                        $userInfo['redirectUrl'] = $redirectUrl;
                    }

                    $this->responseJson(self::CODE_SUCCESS_CODE, '登录成功', $userInfo);
                } else {
                    $this->responseJson(self::CODE_SHOW_MSG, '登录失败');
                }
            } else {
                $this->responseJson(self::CODE_SHOW_MSG, '验证码失效');
            }
        } else {
            $this->responseJson(self::CODE_ERROR_CODE, $validate->message);
        }
    }

    public function callback(Request $request)
    {
        $app_id = env('APP_ID') ?? $request->input('app_id');
        debug_log_info('mobile callback app_id = ' . $app_id);

        $app = Factory::officialAccount(config('deep_login.' . $app_id));
        return $app->server->serve();
    }
}

