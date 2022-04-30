<?php

namespace Andruby\Login\Controllers;

use Andruby\Login\Validates\MobileValidate;
use Illuminate\Http\Request;

/**
 * 邮箱登录
 * Class EmailController
 * @package Andruby\Login\Controllers
 */
class EmailController extends BaseController
{
    public function login(Request $request, MobileValidate $validate)
    {
        $validate_result = $validate->email($request->only(['email', 'password']));
        if ($validate_result) {
            $email = $request->input('email');
            $password = $request->input('password');

            $userService = config('deep_login.user_service');
            $userService = new $userService;

            $userId = $userService->email($email, $password);
            if ($userId) {
                $userInfo = $userService->userInfo($userId);
                $this->responseJson(self::CODE_SUCCESS_CODE, '登录成功', $userInfo);
            } else {
                $this->responseJson(self::CODE_SHOW_MSG, '登录失败');
            }
        } else {
            $this->responseJson(self::CODE_ERROR_CODE, $validate->message);
        }
    }

    public function register(Request $request, MobileValidate $validate)
    {
        $validate_result = $validate->email($request->only(['email', 'password']));
        if ($validate_result) {
            $userService = config('deep_login.user_service');
            $userService = new $userService;

            $userId = $userService->email_register();
            if ($userId) {
                $userInfo = $userService->userInfo($userId);
                $this->responseJson(self::CODE_SUCCESS_CODE, '注册成功', $userInfo);
            } else {
                $this->responseJson(self::CODE_SHOW_MSG, '注册失败');
            }
        } else {
            $this->responseJson(self::CODE_ERROR_CODE, $validate->message);
        }
    }

    public function get_img_code($id)
    {
        if ($id) {
            $smsService = config('deep_login.sms_service');
            $smsService = new $smsService;

            $smsService->getImgCode($id);
        } else {
            $this->responseJson('-1', '参数错误');
        }
    }
}

