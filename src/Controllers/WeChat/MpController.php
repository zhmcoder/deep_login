<?php

namespace Andruby\Login\Controllers\WeChat;

use Andruby\Login\Controllers\BaseController;
use Andruby\Login\Models\WeChat\Templatelist;
use Andruby\Login\Models\WeChat\WxAuthorization;
use Andruby\Login\Services\WeChat\PlatformService;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;

class MpController extends BaseController
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
        $dashUrl = rtrim(env('DASH_URL'), '/dadmin#/');
        $view = view('wx.op.authorized')->with(['dash_url' => $dashUrl]);

        $openPlatform = PlatformService::platform();

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
            error_log_info(__METHOD__, ['appId' => $appId, 'code' => $code]);

            return $view->with([
                'status' => false, 'title' => '[A01] 输入参数验证错误', 'dash_url' => $dashUrl
            ]);
        }

        $openPlatform = PlatformService::platform();
        try {
            $response = $openPlatform->handleAuthorize($code);
        } catch (\Exception $e) {
            error_log_info(__METHOD__, ['error' => $e->getMessage()]);

            return $view->with([
                'status' => false, 'title' => "[A05] 授权错误", 'tips' => $e->getMessage(), 'dash_url' => $dashUrl
            ]);
        }

        if (isset($response['errcode']) && $response['errcode'] != 0) {
            error_log_info(__METHOD__, ['response' => $response]);

            return $view->with([
                'status' => false, 'title' => "[A06] 授权错误", 'tips' => $response['errmsg'] ?? '', 'dash_url' => $dashUrl
            ]);
        }

        $uuid = $response['authorization_info']['authorizer_appid'] ?? null;
        try {
            $mpInfo = $openPlatform->getAuthorizer($uuid);
        } catch (\Exception $e) {
            error_log_info(__METHOD__, ['error' => $e->getMessage()]);

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
            'uuid' => $uuid
        ];

        WxAuthorization::query()->updateOrCreate(['uuid' => $uuid], $new);

        debug_log_info(__METHOD__, ['message' => '授权成功', 'appId' => $appId, 'code' => $code, 'WxAuthorization' => $new]);

        return $view->with([
            'status' => true, 'title' => '授权成功！', 'dash_url' => $dashUrl
        ]);
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
                    case 1: // 商品推送
                        if ($switch) {
                            $tmpShortId = env('SMART_RECHARGE_NOTICE', 'OPENTM417049252');
                            $rechargeTemplate = Templatelist::query()->where('uuid', $wxAuth->uuid)->where('short_id', $tmpShortId)->first();
                            if (!$rechargeTemplate) {
                                $openPlatform = PlatformService::platform();
                                $oa = $openPlatform->officialAccount($wxAuth->uuid, $wxAuth->refresh_token);
                                $res = $oa->template_message->addTemplate($tmpShortId);
                                debug_log_info(__METHOD__, ['message' => 'Open Template Msg  Resp', $res]);

                                if (isset($res['errcode']) && $res['errcode'] != 0) {
                                    error_log_info(__METHOD__, ['message' => '开启推送失败', $res]);

                                    $msg = $res['errmsg'] ?? '-';
                                    $this->responseJson(self::CODE_SHOW_MSG, "开启推送失败 ({$msg})");
                                }

                                $templateId = $res['template_id'] ?? '-';
                                $res1 = $oa->template_message->getPrivateTemplates();
                                debug_log_info(__METHOD__, ['message' => 'Get Template info fail! Resp', $res1]);

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
}
