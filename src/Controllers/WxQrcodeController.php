<?php

namespace Andruby\Login\Controllers;

use Andruby\Login\Services\Interfaces\IUserService;
use Illuminate\Http\Request;
use EasyWeChat\Factory;
use EasyWeChat\OfficialAccount\Application;

/**
 * 微信公众号扫码相关登录
 * Class WxQrcodeController
 * @package Andruby\Login\Controllers
 */
class WxQrcodeController extends BaseController
{
    public function callback(Request $request)
    {
        $app_id = $request->input('app_id');
        $app = Factory::officialAccount(config('deep_login.' . $app_id));

        $app->server->push(function ($message) {
            switch ($message['MsgType']) {
                case 'event':
                    if ($message['Event'] == 'subscribe') {
                        return '关注';
                    } else if ($message['Event'] == 'unsubscribe') {
                        return '取消关注';
                    }
            }
        });

        return $app->server->serve();
    }

    public function qrcode(Request $request)
    {
        $app_id = $request->input('app_id');

        $app = Factory::officialAccount(config('deep_login.' . $app_id));
        $qrcode = $app->qrcode;

        $result = $qrcode->forever(config('deep_login.' . $app_id . '.qrcode.scene'));
        debug_log_info('qrcode result = ' . json_encode($result));

        if (!empty($result['ticket'])) {
            $url = $qrcode->url($result['ticket']);
            debug_log_info('qrcode url = ' . $url);

            $content = file_get_contents($url);
            $filename = md5($content) . '.jpeg';
            $dir = storage_path('app/public/qrcode/');
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            $qrcodeUrl = $dir . $filename;
            file_put_contents($qrcodeUrl, $content);

            $data['qrcode_url'] = http_path('qrcode/' . $filename);
            $this->responseJson(self::CODE_SUCCESS_CODE, 'success', $data);
        } else {
            $this->responseJson(self::CODE_ERROR_CODE, 'fail', $result);
        }
    }

    public function default_login(Request $request)
    {
        $app_id = $request->input('app_id');
        $code = $request->input('code');
        debug_log_info('app_id = ' . $app_id);
        debug_log_info('code = ' . $code);

        $app = Factory::officialAccount(config('deep_login.' . $app_id));
        $oauth = $app->oauth;

        $user = $oauth->userFromCode($code);
        debug_log_info('user = ' . json_encode($user));
        $user = $user->toArray();

        $userService = config('deep_login.user_service');
        $userService = new $userService;
        debug_log_info('user open id = ' . $user['id']);
        if (empty($user['id'])) {
            $user['id'] = $user['token_response']['openid'];
        }
        $token_response = $user['token_response'];
        $user_id = $userService->register($user['id'], $user['nickname'], $user['avatar'],
            null, null, IUserService::USER_TYPE_WX_WEB, $token_response['access_token'], $token_response['refresh_token'],
            $token_response['expires_in'], $token_response['scope']);

        $data = [
            'token' => $userService->genToken($user_id),
            'openid' => $user['id'],
        ];
        $this->responseJson(self::CODE_SUCCESS_CODE, 'success', $data);
    }
}

