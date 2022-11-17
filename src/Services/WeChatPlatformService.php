<?php

namespace Andruby\Login\Services;

use EasyWeChat\Factory;
use EasyWeChat\OpenPlatform\Application;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class WeChatPlatformService
{
    /**
     * @param string $appId
     * @return Application
     */
    public static function platform(string $appId)
    {
        $options = config('deep_login.' . $appId);

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
        Log::info(__METHOD__, [$message]);
    }

    /**
     * 授权更新通知
     *
     * @param array $message
     * @return void
     */
    public static function updateAuthorized(array $message)
    {
        Log::info(__METHOD__, [$message]);
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
        $status = WxAuthorization::where(['uuid' => $appId])->update(['status' => 0]);
        Log::info(__METHOD__, [$appId, $status, $message]);
    }
}
