<?php


namespace Andruby\Login\Services;


use Andruby\Login\Libs\WXBizDataCrypt;
use Cache;

class XcxService
{
    public static function getXcxSession($appid, $appsecret, $code)
    {
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid="
            . $appid
            . "&secret=" . $appsecret
            . "&js_code=" . $code
            . "&grant_type=authorization_code";
        $data = file_get_contents($url);
        debug_log_info('wx xcx session = ' . $data);
        $data = json_decode($data, true);
        if (array_key_exists('errcode', $data)) {
            $data = file_get_contents($url);
            $data = json_decode($data, true);
        }

        if (array_key_exists('errcode', $data)) {
            return false;
        }

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
        if ($session_key) {
            $dataCrypt = new WXBizDataCrypt($appid, $session_key);
            $dataCrypt->decryptData($data, $iv, $data_info);
            debug_log_info('login data info = ' . $data_info);
            return json_decode($data_info, true);
        } else {
            $data['status'] = 1001;
            $data['msg'] = '小程序需要重新登录';
            return $data;
        }
    }
}
