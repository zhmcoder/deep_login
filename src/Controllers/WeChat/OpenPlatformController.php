<?php

namespace Andruby\Login\Controllers\WeChat;

use Andruby\Login\Controllers\BaseController;
use Andruby\Login\Services\WeChat\OffiaccountService;
use Andruby\Login\Services\WeChat\PlatformService;
use Andruby\Login\Services\WeChat\TemplateService;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use EasyWeChat\Kernel\Support\XML;
use EasyWeChat\OpenPlatform\Server\Guard;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OpenPlatformController extends BaseController
{
    /**
     * 授权事件接收配置 ticket
     *
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function ticket(Request $request)
    {
        try {
            $content = $request->getContent();
            $payload = XML::parse($content);
            debug_log_info(__METHOD__, [$content, $payload]);

            if (!$platform = PlatformService::platform()) {
                error_log_info(__METHOD__, ['tips' => 'open platform not found']);
                $this->responseJson(self::CODE_SHOW_MSG, 'open platform not found');
            }

            $server = $platform->server;
            $server->push(function ($message) {
                PlatformService::authorized($message);
            }, Guard::EVENT_AUTHORIZED);
            $server->push(function ($message) {
                PlatformService::updateAuthorized($message);
            }, Guard::EVENT_UPDATE_AUTHORIZED);
            $server->push(function ($message) {
                PlatformService::unAuthorized($message);
            }, Guard::EVENT_UNAUTHORIZED);

            return $server->serve();
        } catch (\Exception $e) {
            error_log_info(__METHOD__ . $e->getMessage());
        }

        $this->responseJson(self::CODE_SHOW_MSG, '失败');
    }


    /**
     * 公众号事件（关注、取关、消息）
     *
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
            return OffiaccountService::eventFormat($appId);
        } catch (\Exception $e) {
            error_log_info(__METHOD__, [$e->getMessage()]);
        }
    }

    /**
     * 公众号 - 模板消息用例
     *
     * @param string $appId
     * @throws GuzzleException
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     */
    public function sendTemplateMsg($appId = '')
    {
        $unionid = 'ozv9e5w__oXbRZM74BuzTEGLXbgk';

        $pagePath = 'pages/shop/shop';

        $pushMsg = [
            'first' => ['value' => '您关注的团长：嘻蜜团发布了新的团购'],
            'keyword1' => ['value' => '商品描述'],
            'keyword2' => ['value' => '2022-11-12 12:33:31'],
            'keyword3' => ['value' => '嘻蜜团'],
            'keyword4' => ['value' => '商品标题'],
            'remark' => ['value' => '该消息仅推送给已订阅用户，如有打扰可在小程序内"退订”'],
        ];

        TemplateService::sendTemplateMsg($appId, $unionid, $pagePath, $pushMsg);
    }
}
