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
class NeteaseApi
{
    /**
     * 发送短信验证
     *
     * @param $mobile
     * @param $template_id
     * @param null $info
     * @return mixed
     */
    static public function send_sms($mobile, $template_id, $info = null)
    {
        $data = array(
            'mobile' => $mobile,
            'templateid' => $template_id
        ); // 定义参数
        if (!empty($info)) {
            $data['params'] = $info;
        }
        $data = @http_build_query($data); // 把参数转换成URL数据
        $curTime = time();
        $nonce = config('deep_login.netease_sms.nonce');
        $appKey = config('deep_login.netease_sms.app_key');
        $AppSecret = config('deep_login.netease_sms.app_secret');
        $aContext = array(
            'http' => array(
                'method' => 'POST',
                'header' => array(
                    'Content-Type: application/x-www-form-urlencoded',
                    'AppKey: ' . $appKey,
                    'CurTime: ' . $curTime,
                    'CheckSum: ' . sha1($AppSecret . $nonce . $curTime),
                    'Nonce: ' . $nonce,
                    'charset: utf-8'
                ),
                'content' => $data
            )
        );
        $cxContext = stream_context_create($aContext);
        $sUrl = 'https://api.netease.im/sms/sendcode.action';
        $context = @file_get_contents($sUrl, false, $cxContext);
        $tokenJson = json_decode($context, true);
        $status['status'] = $tokenJson['code'];
        $status['sms_code'] = $tokenJson['obj'];
        return $status;
    }
}
