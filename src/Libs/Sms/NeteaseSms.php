<?php

namespace Andruby\Login\Libs\Sms;

/**
 * Created by PhpStorm.
 * User: zhm
 * Date: 2017/4/8
 * Time: 上午9:33
 *
 * 网易短信发送接口封装
 */
class NeteaseSms implements ISmsSend
{


    function sendSMSCode($mobile, $app_id = 'default_app_id')
    {
        $template_id = config('deep_login.netease_sms.template_id');
        if (key_exists($app_id, $template_id)) {
            $template_id = config('deep_login.netease_sms.template_id')[$app_id];
            $sms_code = mt_rand(1001, 9999);
            return NeteaseApi::send_sms($mobile, $template_id, $sms_code);
        } else {
            return false;
        }
    }
}
