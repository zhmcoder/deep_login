<?php

namespace Andruby\Login\Validates;


class  MobileValidate extends Validate
{

    public function login($request_data)
    {
        $rules = [
            'mobile' => 'required|regex:/^1[3456789][0-9]{9}$/',
            'verify_code' => 'required|digits:4',
        ];
        $message = [
            'mobile.required' => '手机号不能为空',
            'mobile.regex' => '手机号格式不正确',
            'verify_code.required' => '验证码不能为空',
            'verify_code.digits' => '验证码位数不正确'
        ];
        return $this->validate($request_data, $rules, $message);
    }

    public function verify_code($request_data)
    {
        $rules = [
            'mobile' => 'required|regex:/^1[3456789][0-9]{9}$/',
            'img_code' => 'sometimes|required|size:4',
        ];
        $message = [
            'mobile.required' => '手机号不能为空',
            'mobile.regex' => '手机号格式不正确',
        ];
        return $this->validate($request_data, $rules, $message);
    }

    public function guest($request_data)
    {
        $rules = [
            'mobile' => 'required|string'
        ];
        $message = [
            'mobile.required' => '手机号不能为空',
            'mobile.string' => '手机号格式不正确',
        ];
        return $this->validate($request_data, $rules, $message);
    }

    public function weixinLogin($request_data)
    {
        $rules = [
            'openid' => 'required',
            'unionid' => 'required',
            'access_token' => 'required'
        ];
        $message = [
            'openid.required' => 'openid不能为空',
            'unionid.required' => 'required不能为空',
            'access_token.required' => 'access_token不能为空',
        ];
        return $this->validate($request_data, $rules, $message);
    }

    public function aliAutoLogin($request_data)
    {
        $rules = [
            'accessToken' => 'required',
        ];
        $message = [
            'accessToken.required' => '参数不能为空',
        ];
        return $this->validate($request_data, $rules, $message);
    }

    public function email($request_data)
    {
        $rules = [
            'email' => 'required|string',
            'password' => 'required|string',
        ];
        $message = [
            'email.required' => '账号不能为空',
            'email.string' => '账号格式不正确',
            'password.required' => '密码不能为空',
            'password.string' => '密码格式不正确'
        ];
        return $this->validate($request_data, $rules, $message);
    }
}
