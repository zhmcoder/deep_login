<?php

namespace Andruby\Login\Validates;

class WxMiniValidate extends Validate
{
    public function login($request_data)
    {
        $rules = [
            'code' => 'required',
            'loginData' => 'sometimes|required',
        ];
        $message = [
            'code.required' => '小程序标识不能为空',
            'loginData.required' => '用户信息不能为空',
        ];
        return $this->validate($request_data, $rules, $message);
    }
}
