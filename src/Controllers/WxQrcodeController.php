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

        $this->responseJson(self::STATUS_SUCCESS, 'success', $result);
    }
}

