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
        $sign_id = config('deep_login.mi_tang_sms.sign_id');
        if (key_exists($app_id, $sign_id)) {
            $smsSignId = config('deep_login.mi_tang_sms.sign_id')[$app_id];
            $smsTemplateNo = config('deep_login.mi_tang_sms.template_no')[$app_id];

            $verifyCode = mt_rand(1001, 9999);
            return $this->send_sms($mobile, $verifyCode, $smsSignId, $smsTemplateNo);
        } else {
            return false;
        }
    }

    public function send_sms($mobile, $verifyCode, $smsSignId, $smsTemplateNo)
    {
        $appcode = config('deep_login.mi_tang_sms.appcode'); //
        $url = "https://miitangs09.market.alicloudapi.com/v1/tools/sms/code/sender";
        $param['phoneNumber'] = $mobile;
        $param['smsSignId'] = $smsSignId;//签名模板
        $param['smsTemplateNo'] = $smsTemplateNo;//短信模板id
        $param['verifyCode'] = $verifyCode;//短信验证码

        $header[] = "Authorization:APPCODE " . $appcode;
        $header[] = 'X-Ca-Nonce:' . $this->create_uuid();
        $header[] = 'Content-Type:application/x-www-form-urlencoded; charset=UTF-8';

        $result = HttpUtil::httpPost($url, $param, $header);
        $header = HttpUtil::getResponseHeader();

        debug_log_info('MiTang send sms header = ' . json_encode($header) . ', mobile = ' . $mobile);
        debug_log_info('MiTang send sms result = ' . json_encode($result) . ', mobile = ' . $mobile);

        $result = json_decode($result, true);
        if ($result['code'] && $result['code'] == 'FP00000') {
            return [
                'status' => 200,
                'sms_code' => $result['verifyCode'] ?? $result['verificationCode'],
            ];
        } else {
            error_log_info('MiTang send sms  error = ' . json_encode($result) . ', mobile = ' . $mobile);
            return [
                'status' => -1,
                'sms_code' => '',
            ];
        }
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
