<?php

namespace Andruby\Login\Services\WeChat;

use Andruby\Login\Models\WeChat\Templatelist;
use Andruby\Login\Models\WeChat\UserOfficialAccount;
use Andruby\Login\Models\WeChat\WxAuthorization;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use GuzzleHttp\Exception\GuzzleException;

class TemplateService
{
    /**
     * 公众号模板消息
     *
     * @param $appId
     * @param $unionid
     * @param string $pagePath
     * @param array $pushMsg
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     */
    public static function sendTemplateMsg($appId, $unionid, $pagePath = '', $pushMsg = [])
    {
        // 公众号关联小程序信息
        $wxAuthorization = WxAuthorization::where("status", 1)->where("type", 0)->first();
        if (!$wxAuthorization) {
            debug_log_info(__METHOD__ . " 未查询到授权公众号或者已取消授权");
        }
        // 充值提醒开启
        if ($wxAuthorization->recharge_notice) {
            $toUser = UserOfficialAccount::where('unionid', $unionid)->where('uuid', $wxAuthorization->uuid)->value('open_id');

            $template_id = Templatelist::where('uuid', $wxAuthorization->uuid)->where('short_id', env('SMART_RECHARGE_NOTICE', 'OPENTM417049252'))->value('template_id');

            debug_log_info(__METHOD__ . " Send Template Msg {$template_id} To User {$toUser}....");

            if ($toUser && $template_id) {
                $data = [
                    'touser' => $toUser,
                    'template_id' => $template_id,
                    'miniprogram' => [
                        'appid' => $appId,
                        'pagepath' => $pagePath,
                    ],
                    'client_msg_id' => md5($toUser . $unionid . time()),
                    'data' => $pushMsg,
                ];
                debug_log_info(__METHOD__ . " Send Template Msg ", $data);

                $refreshToken = $wxAuthorization->refresh_token ?? null;
                $openPlatform = PlatformService::platform(); // 三方平台
                $officialAccount = $openPlatform->officialAccount($wxAuthorization->uuid, $refreshToken);  // 微信公众号
                $response = $officialAccount->template_message->send($data);

                debug_log_info(__METHOD__ . " Send Template Result ", $response);
                if ($response['errcode'] != 0) {
                    error_log_info(__METHOD__ . " Send Template Fail! ", $response);
                }
            }
        } else {
            debug_log_info(__METHOD__ . " Recharge Notice Not Open! ");
        }
    }
}
