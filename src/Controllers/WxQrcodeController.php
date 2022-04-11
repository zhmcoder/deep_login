<?php

namespace Andruby\Login\Controllers;

use Illuminate\Http\Request;
use EasyWeChat\Factory;

/**
 * 微信公众号扫码相关登录
 * Class WxQrcodeController
 * @package Andruby\Login\Controllers
 */
class WxQrcodeController extends BaseController
{
    public function qrcode(Request $request)
    {
        $app_id = $request->input('app_id');

        $app = Factory::officialAccount(config('deep_login.' . $app_id));
        $qrcode = $app->qrcode;

        $result = $qrcode->forever(config('deep_login.' . $app_id . '.qrcode.scene'));
        debug_log_info('qrcode result = ' . json_encode($result));

        if (!empty($result['ticket'])) {
            $url = $qrcode->url($result['ticket']);
            debug_log_info('qrcode url = ' . $url);

            $content = file_get_contents($url);
            $filename = md5($content) . '.jpeg';
            $dir = storage_path('app/public/qrcode/');
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            $qrcodeUrl = $dir . $filename;
            file_put_contents($qrcodeUrl, $content);

            $data['qrcode_url'] = $qrcodeUrl;
            $this->responseJson(self::STATUS_SUCCESS, 'success', $data);
        } else {
            $this->responseJson(self::STATUS_FAILED, 'fail', $result);
        }
    }
}

