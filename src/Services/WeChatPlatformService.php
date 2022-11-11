<?php

namespace Andruby\Login\Services;

use EasyWeChat\Factory;
use Andruby\Login\Models\WxPlatform;
use Illuminate\Support\Facades\Redis;
use EasyWeChat\OpenPlatform\Application;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class WeChatPlatformService
{
    /**
     * @param string $appId
     * @return Application|null
     */
    public static function platform(string $appId): ?Application
    {
        static $pCache;
        if (!empty($pCache[$appId])) {
            return $pCache[$appId];
        }

        if (!$cfg = WxPlatform::where(['uuid' => $appId])->first()) {
            error_log_info(__METHOD__, ['tips' => 'open platform not found.']);
            return null;
        }

        $options = [
            'app_id' => $cfg->uuid ?? '', 'secret' => $cfg->secret ?? '',
            'token' => $cfg->verify_token ?? '', 'aes_key' => $cfg->verify_key ?? '',
        ];

        $platform = Factory::openPlatform($options);
        $platform->rebind('cache', new RedisAdapter(
            Redis::connection()->client()
        ));

        return $pCache[$appId] = $platform;
    }
}
