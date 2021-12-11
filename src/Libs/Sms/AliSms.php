<?php

namespace Andruby\Login\Libs\Sms;


/**
 * Created by PhpStorm.
 * User: zhm
 * Date: 2017/4/8
 * Time: 上午9:33
 *
 * 阿里短信发送接口封装
 */
class AliSms implements ISmsSend
{

    function sendSMSCode($mobile, $app_id = 'default_app_id')
    {
        $sign_name = config('deep_login.ali_sms.sign_name');
        if (key_exists($app_id, $sign_name)) {
            $sign_name = $sign_name[$app_id];
            $template_code = config('deep_login.ali_sms.template_code')[$app_id];
            $sms_code = mt_rand(1001, 9999);
            return AliCloud::send_sms($mobile, $sign_name, $template_code, $sms_code);
        } else {
            return false;
        }

    }
}
