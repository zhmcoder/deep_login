<?php

use Illuminate\Support\Facades\Log;

if (!function_exists('deep_login_info')) {
    function deep_login_info($message, array $context = array())
    {
        Log::channel('deep_login_info')->info($message, $context);
    }
}
