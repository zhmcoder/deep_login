<?php

namespace Andruby\Login\Controllers\WeChat;

use Andruby\Login\Controllers\BaseController;
use Andruby\Login\Models\WxPlatform;
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
    public function authorized($appId)
    {
        $openPlatform = WeChatPlatformService::platform($appId);
        $redirectUrl = join('/', [rtrim(config('app.url'), '/'), "wx/mp/authorized/"]);

        $openPlatform->getPreAuthorizationUrl('https://dev.c.me.ink/Api/sts/sd'); // 传入回调URI即可

        print_r($openPlatform);
        die;
        $preAuthorizationUrl = $openPlatform->getPreAuthorizationUrl(
            $redirectUrl, ['auth_type' => 1, 'biz_appid' => trim($appId)]
        );
        echo $preAuthorizationUrl;
        die;
        try {
            $preAuthorizationUrl = $openPlatform->getPreAuthorizationUrl(
                $redirectUrl, ['auth_type' => 1, 'biz_appid' => trim($appId)]
            );
            echo $preAuthorizationUrl;
            die;

            redirect($preAuthorizationUrl);
        } catch (\Exception $e) {

        }
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
            error_log_info(__METHOD__, $e->getMessage());
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
