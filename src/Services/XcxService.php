<?php

namespace Andruby\Login\Services;

use Andruby\Login\Libs\WXBizDataCrypt;
use Cache;

class XcxService
{
    public static function getXcxSession($appid, $appSecret, $code)
    {
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid="
            . $appid
            . "&secret=" . $appSecret
            . "&js_code=" . $code
            . "&grant_type=authorization_code";
        $data = file_get_contents($url);
        debug_log_info('wx xcx session = ' . $data);
        $data = json_decode($data, true);
        if (array_key_exists('errcode', $data)) {
            $data = file_get_contents($url);
            debug_log_info('wx xcx session = ' . $data);
            $data = json_decode($data, true);
        }

        if (array_key_exists('errcode', $data)) {
            return false;
        }

        debug_log_info('wx xcx session_key = ' . $data['session_key']);
        if ($data['session_key']) {
            self::cache_session($appid, $data['openid'], $data['session_key']);
            return $data;
        } else {
            return null;
        }
    }

    public static function cache_session($appid, $openid, $session, $expired_in = 0)
    {
        if ($expired_in > 0) {
            Cache::put('cxc_session_' . $appid . '_' . $openid, $session, $expired_in);
        } else {
            Cache::put('cxc_session_' . $appid . '_' . $openid, $session);
        }

    }

    public static function getSession($appid, $openid)
    {
        return Cache::get('cxc_session_' . $appid . '_' . $openid);
    }

    public static function decryptData($appid, $openid, $data, $iv)
    {
        $session_key = self::getSession($appid, $openid);
        debug_log_info('appid = ' . $appid . ', openid = ' . $openid . ', session_key = ' . $session_key);
        if ($session_key) {
            $dataCrypt = new WXBizDataCrypt($appid, $session_key);
            $res = $dataCrypt->decryptData($data, $iv, $loginData);
            debug_log_info('decryptData res = ' . json_encode($res));
            debug_log_info('loginData info = ' . $loginData);
            return json_decode($loginData, true);
        } else {
            $data['code'] = 1001;
            $data['message'] = '小程序需要重新登录';
            return $data;
        }
    }
}
