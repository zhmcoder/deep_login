<?php

namespace Andruby\Login\Libs\Sms;

use Andruby\Login\Libs\Utils\HttpUtil;

/**
 * Created by PhpStorm.
 * User: zhm
 * Date: 2017/4/8
 * Time: 上午9:33
 *
 * 蜜堂有信短信发送接口封装
 */
class MiTangSms implements ISmsSend
{
    function sendSMSCode($mobile, $app_id = 'default_app_id')
    {
        $template_id = config('deep_login.netease_sms.template_id');
        if (key_exists($app_id, $template_id)) {
            $template_id = config('deep_login.netease_sms.template_id')[$app_id];
            $sms_code = mt_rand(1001, 9999);
            return $this->send_sms($mobile, $sms_code);
        } else {
            return false;
        }
    }

    public function send_sms($mobile, $sms_code)
    {
        $appcode = "d050293973c644c6a0734f22598d8048"; //
        $url = "https://miitangs09.market.alicloudapi.com/v1/tools/sms/code/sender";
        $param['phoneNumber'] = $mobile;
        $param['smsSignId'] = 'QM426241';//签名模板
        $param['smsTemplateNo'] = '0003';//短信模板id
        $param['verifyCode'] = $sms_code;//短信验证码

        $header[] = "Authorization:APPCODE " . $appcode;
        $header[] = 'X-Ca-Nonce:' . $this->create_uuid();
        $header[] = 'Content-Type:application/x-www-form-urlencoded; charset=UTF-8';

        $result = HttpUtil::httpPost($url, $param, $header);
        $header = HttpUtil::getResponseHeader();

        debug_log_info('MiTang send sms header = ' . json_encode($header) . ', mobile = ' . $mobile);

        debug_log_info('MiTang send sms result = ' . json_encode($result) . ', mobile = ' . $mobile);

        return $result;
    }

    private function create_uuid($prefix = "")
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr($chars, 0, 8) . '-'
            . substr($chars, 8, 4) . '-'
            . substr($chars, 12, 4) . '-'
            . substr($chars, 16, 4) . '-'
            . substr($chars, 20, 12);
        return $prefix . $uuid;
    }
}
