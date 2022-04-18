<?php

namespace Andruby\Login\Middleware;

use Closure;
use Illuminate\Http\Request;
use EasyWeChat\Factory;

class WeixinWebLogin
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = request(config('deep_login.check_login_param'), null);

        if ($token) {
            return $next($request);
        }

        $app_id = $request->input('app_id');
        $config = config('deep_login.' . $app_id);

        if ($app_id && $config) {
            $config['oauth'] = $config['default_login'];

            $app = Factory::officialAccount($config);
            $oauth = $app->oauth;

            $target_url = $request->input('target_url', url()->full());
            $is_api = $request->input('is_api', false);

            $redirectUrl = $oauth->redirect(route('wxweb.default_login', ['app_id' => $app_id, 'target_url' => $target_url]));

            debug_log_info('redirectUrl = ' . $redirectUrl);
            if ($is_api) {
                $data['code'] = 200;
                $data['message'] = 'success';
                $data['redirectUrl'] = $redirectUrl->getTargetUrl();
                return response($data);
            } else {
                return \redirect($redirectUrl);
                // header("Location: {$redirectUrl}");
                // exit;
            }
        } else {
            $data['code'] = -1;
            $data['message'] = 'app_id is null';
            return response($data);
        }
    }
}
