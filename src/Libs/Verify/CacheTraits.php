<?php

namespace Andruby\Login\Libs\Verify;

use Cache;

trait CacheTraits
{

    protected function get($key)
    {
       return Cache::get($key);
    }

    protected function put($key, $value, $expire)
    {
        Cache::put($key, $value, $expire);
    }

}
