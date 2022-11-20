<?php

namespace Andruby\Login\Controllers\WeChat;

use Andruby\Login\Controllers\BaseController;
use Andruby\Login\Models\Templatelist;
use Andruby\Login\Models\WxAuthorization;
use Andruby\Login\Services\WeChatOffiaccountService;
use Andruby\Login\Services\WeChatTemplateService;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use EasyWeChat\Kernel\Support\XML;
use Illuminate\Support\Facades\Log;
use Andruby\Login\Services\WeChatPlatformService;
use EasyWeChat\OpenPlatform\Server\Guard;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OpenPlatformController extends BaseController
{
    /**
     * 微信开放平台授权页面
     *
     * @param $appId
     * @return Application|Factory|View|\think\response\View
     */
    public function preAuthorizationJump($appId)
    {
        // 跳转页面
        $dashUrl = rtrim(env('DASH_URL'), '/');
        $view = view('wx.op.authorized')->with(['dash_url' => $dashUrl]);

        $openPlatform = WeChatPlatformService::platform($appId);

        // 重定向地址
        $redirectUrl = join('/', [rtrim(config('app.url'), '/'), "Api/Wechat/authorized/{$appId}"]);

        try {
            $preAuthorizationUrl = $openPlatform->getPreAuthorizationUrl(
                $redirectUrl, ['auth_type' => 1, 'biz_appid' => trim($appId)]
            );

            return $view->with([
                'status' => true, 'title' => '点击下方按钮完成授权', 'click' => $preAuthorizationUrl, 'op' => '公众号授权'
            ]);
        } catch (\Exception $e) {
            return $view->with([
                'status' => false, 'title' => "[PA05] 微信开放平台配置有误", 'tips' => $e->getMessage()
            ]);
        }
    }

    /**
     * 微信开放平台授权回调
     *
     * @param Request $request
     * @param int $appId
     * @return Application|Factory|View|\think\response\View
     */
    public function authorized(Request $request, $appId = 0)
    {
        $code = $request->input('auth_code', null);
        $view = view('wx.op.authorized');
        $dashUrl = rtrim(env('DASH_URL'), '/');

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

        return $view->with([
            'status' => true, 'title' => '授权成功！', 'dash_url' => $dashUrl
        ]);
    }

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

            if (!$platform = WeChatPlatformService::platform($payload['AppId'] ?? '')) {
                error_log_info(__METHOD__, ['tips' => 'open platform not found']);
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
            return WeChatOffiaccountService::eventFormat($appId);
        } catch (\Exception $e) {
            error_log_info(__METHOD__, [$e->getMessage()]);
        }
    }

    /**
     * 公众号 - 推送开关
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    public function smartPushSwitch(Request $request)
    {
        try {
            $switch = $request->input('switch', 1);
            $type = $request->input('type', 1);
            $wxAuth = WxAuthorization::where('status', 1)->first();

            if ($wxAuth) {
                switch ($type) {
                    case 1: // 商品推送体系怀
                        if ($switch) {
                            $tmpShortId = env('SMART_RECHARGE_NOTICE', 'OPENTM417049252');
                            $rechargeTemplate = Templatelist::query()->where('uuid', $wxAuth->uuid)->where('short_id', $tmpShortId)->first();
                            if (!$rechargeTemplate) {
                                $openPlatform = WeChatPlatformService::platform('');
                                $oa = $openPlatform->officialAccount($wxAuth->uuid, $wxAuth->refresh_token);
                                $res = $oa->template_message->addTemplate($tmpShortId);
                                debug_log_info('Open Template Msg  Resp：' . json_encode($res));
                                if (isset($res['errcode']) && $res['errcode'] != 0) {
                                    $msg = $res['errmsg'] ?? '-';
                                    $this->responseJson(self::CODE_SHOW_MSG, "开启充值成功提醒失败 ({$msg})");
                                }

                                $templateId = $res['template_id'] ?? '-';
                                $res1 = $oa->template_message->getPrivateTemplates();
                                if (isset($res1['errcode']) && $res1['errcode'] != 0) {
                                    error_log_info('Get Template info fail! Resp：' . json_encode($res1));
                                    $this->responseJson(self::CODE_SHOW_MSG, "Get Template info fail");
                                }
                                $templatelist = array_column($res1['template_list'], null, 'template_id');
                                $template = $templatelist[$templateId] ?? [];
                                $map = ['uuid' => $wxAuth->uuid, 'short_id' => $tmpShortId];
                                $new = [
                                    'title' => $template['title'] ?? '',
                                    'template_id' => $templateId,
                                    'content' => $template['content'] ?? '',
                                ];
                                $res2 = Templatelist::updateOrCreate($map, $new);
                                if (!$res2) {
                                    error_log_info('insert Template info fail! ', compact('map', 'new'));
                                    $this->responseJson(self::CODE_SHOW_MSG, "insert Template info fail!");
                                }
                            }
                        }

                        $resp = $wxAuth->update(['recharge_notice' => $switch]);
                        if (!$resp) {
                            $this->responseJson(self::CODE_SHOW_MSG, '操作失败');
                        }
                        break;
                }

                $this->responseJson(self::CODE_SUCCESS_CODE, '成功');
            } else {
                $this->responseJson(self::CODE_SHOW_MSG, '未查询到授权公众号');
            }
        } catch (\Exception $e) {
            error_log_info('Recharge Notice Switch Exception! Msg：' . $e->getMessage());
            $this->responseJson(self::CODE_SHOW_MSG, '失败');
        }
    }

    public function sendTemplateMsg($appId = '')
    {
        $userId = 3;

        WeChatTemplateService::sendTemplateMsg($appId, $userId);
    }
}
