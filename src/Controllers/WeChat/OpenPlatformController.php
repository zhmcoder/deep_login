<?php

namespace Andruby\Login\Controllers\WeChat;

use Andruby\Login\Controllers\BaseController;
use Andruby\Login\Models\WxAuthorization;
use Andruby\Login\Services\WeChatOffiaccountService;
use Illuminate\Http\Request;
use EasyWeChat\Kernel\Support\XML;
use Illuminate\Support\Facades\Log;
use Andruby\Login\Services\WeChatPlatformService;
use EasyWeChat\OpenPlatform\Server\Guard;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OpenPlatformController extends BaseController
{
    public function preAuthorizationJump($appId)
    {
        $dashUrl = rtrim(env('DASH_URL'), '/') . "/wxadmin/wxset";
        $view = view('wx.op.authorized')->with(['dash_url' => $dashUrl]);

        $openPlatform = WeChatPlatformService::platform($appId);

        $redirectUrl = join('/', [rtrim(config('app.url'), '/'), "Api/Wechat/authorized/{$appId}"]);

        try {
            $preAuthorizationUrl = $openPlatform->getPreAuthorizationUrl(
                $redirectUrl, ['auth_type' => 1, 'biz_appid' => trim($appId)]
            );

            return $view->with([
                'status' => true, 'title' => '点击下方按钮完成授权', 'click' => $preAuthorizationUrl, 'op' => '授权'
            ]);
        } catch (\Exception $e) {
            return $view->with([
                'status' => false, 'title' => "[PA05] 微信开放平台配置有误", 'tips' => $e->getMessage()
            ]);
        }
    }

    public function authorizee(Request $request, $appId = 0)
    {
        $code = $request->input('auth_code', null);
        $view = view('wx.op.authorized');
        $dashUrl = rtrim(env('DASH_URL'), '/') . "/wxadmin/wxset";

        if (!$appId || !$code) {
            return $view->with([
                'status' => false, 'title' => '[A01] 输入参数验证错误', 'dash_url' => $dashUrl
            ]);
        }

        $openPlatform = WeChatPlatformService::platform($appId);
        try {
            $response = $openPlatform->handleAuthorize($code);
        } catch (\Exception $e) {
            return $view->with([
                'status' => false, 'title' => "[A05] 授权错误", 'tips' => $e->getMessage(), 'dash_url' => $dashUrl
            ]);
        }

        if (isset($response['errcode']) && $response['errcode'] != 0) {
            return $view->with([
                'status' => false, 'title' => "[A06] 授权错误", 'tips' => $response['errmsg'] ?? '', 'dash_url' => $dashUrl
            ]);
        }

        $uuid = $response['authorization_info']['authorizer_appid'] ?? null;
        try {
            $mpInfo = $openPlatform->getAuthorizer($uuid);
        } catch (\Exception $e) {
            Log::warning(__METHOD__, ['error' => $e->getMessage()]);

            return $view->with([
                'status' => false, 'title' => "[A07] 授权错误", 'tips' => $e->getMessage()
            ]);
        }

        $new = [
            'functions' => $response['authorization_info']['func_info'] ?? null,
            'authorized_info' => array_merge($response, $mpInfo),
            'status' => 1,
            'access_token' => $response['authorization_info']['authorizer_access_token'] ?? null,
            'refresh_token' => $response['authorization_info']['authorizer_refresh_token'] ?? null,
            'name' => $mpInfo['authorizer_info']['nick_name'] ?? null,
            'logo' => $mpInfo['authorizer_info']['head_img'] ?? null,
            'qrcode' => $mpInfo['authorizer_info']['qrcode_url'] ?? null,
            'uuid' => $uuid,
            'platform_id' => $wxPlatformCfg->id ?? 0
        ];

        WxAuthorization::query()->updateOrCreate(['uuid' => $uuid], $new);

        $tips = '公众号';
        return $view->with([
            'status' => true, 'title' => '授权成功！', 'dash_url' => $dashUrl
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function ticket(Request $request)
    {
        try {
            $content = $request->getContent();
            $payload = XML::parse($content);
            Log::info(__METHOD__, [$content, $payload]);

            if (!$platform = WeChatPlatformService::platform($payload['AppId'] ?? '')) {
                Log::warning(__METHOD__, ['tips' => 'open platform not found']);

                $this->responseJson(self::CODE_SHOW_MSG, 'open platform not found');
            }

            $server = $platform->server;
            $server->push(function ($message) {
                WeChatPlatformService::authorized($message);
            }, Guard::EVENT_AUTHORIZED);
            $server->push(function ($message) {
                WeChatPlatformService::updateAuthorized($message);
            }, Guard::EVENT_UPDATE_AUTHORIZED);
            $server->push(function ($message) {
                WeChatPlatformService::unAuthorized($message);
            }, Guard::EVENT_UNAUTHORIZED);

            return $server->serve();
        } catch (\Exception $e) {
            error_log_info(__METHOD__ . $e->getMessage());
        }

        $this->responseJson(self::CODE_SHOW_MSG, '失败');
    }

    /**
     * @param Request $request
     * @param string $appId
     * @return string|Response
     */
    public function event(Request $request, string $appId)
    {
        try {
            $input = $request->input();
            $content = $request->getContent();
            debug_log_info(__METHOD__, ["content" => $content, "input" => $input]);
            return WeChatOffiaccountService::eventFormat($appId);
        } catch (\Exception $e) {
            error_log_info(__METHOD__, [$e->getMessage()]);
        }
    }
}