<?php

namespace Andruby\Login\Controllers;

use Andruby\Login\Libs\Sms\AliSms;
use Andruby\Login\Services\SmsService;
use Andruby\Login\Services\UserService;
use Andruby\Login\Validates\MobileValidate;
use Illuminate\Http\Request;
use EasyWeChat\Factory;

/**
 * 手机号登录
 * Class WeixinController
 * @package App\Api\Controllers
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
            if (empty($img_code)) {
                if ($smsService->isImgCode($mobile)) {
                    $data['img_code'] = $smsService->genImgCode($mobile);
                    $verify_code = true;
                    $msg = '图片验证码已发送！';
                } else {
                    $verify_code = $smsService->sendVerifyCode($mobile);
                }
            } else {
                if ($smsService->verifyImgCode($mobile, $img_code)) {
                    $verify_code = $smsService->sendVerifyCode($mobile);
                } else {
                    $verify_code = false;
                    $msg = '图片验证码验证失败';
                }
            }
            return $this->responseJson($verify_code ? '0' : '-1', $msg);

        } else {
            return $this->responseJson('-1', $validate->message);
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
                    return $this->responseJson('0', '登录成功', $userInfo);
                } else {
                    return $this->responseJson('-1', '登录成功失败');
                }


            } else {
                return $this->responseJson('-1', '验证码失效');
            }
        } else {
            return $this->responseJson('-1', $validate->message);
        }
    }
}

