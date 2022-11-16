<?php

namespace Andruby\Login\Services;

use EasyWeChat\Factory;
use EasyWeChat\OpenPlatform\Application;
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
}
