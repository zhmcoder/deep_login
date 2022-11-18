<?php

namespace Andruby\Login\Services;

use Andruby\Login\Models\WxAuthorization;
use EasyWeChat\Factory;
use EasyWeChat\OpenPlatform\Application;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class WeChatPlatformService
{
    /**
     * 获取三方平台配置信息
     *
     * @param string $appId
     * @return Application
     */
    public static function platform(string $appId)
    {
        $options = config('deep_login.open_app_config');

        $platform = Factory::openPlatform($options);

        $platform->rebind('cache', new RedisAdapter(
            Redis::connection('wechat')->client()
        ));

        return $platform;
    }

    /**
     * 授权通知
     *
     * @param array $message
     * @return void
     */
    public static function authorized(array $message)
    {
        debug_log_info(__METHOD__, [$message]);
    }

    /**
     * 授权更新通知
     *
     * @param array $message
     * @return void
     */
    public static function updateAuthorized(array $message)
    {
        debug_log_info(__METHOD__, [$message]);
    }

    /**
     * 取消授权通知
     *
     * @param array $message
     * @return void
     */
    public static function unAuthorized(array $message)
    {
        $appId = $message['AuthorizerAppid'] ?? '_-_';
        $status = WxAuthorization::query()->where(['uuid' => $appId])->update(['status' => 0]);
        debug_log_info(__METHOD__, [$appId, $status, $message]);
    }
}
