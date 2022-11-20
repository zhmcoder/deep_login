<?php

namespace Andruby\Login\Services;

use Andruby\Login\Models\Templatelist;
use Andruby\Login\Models\UserOfficialAccount;
use Andruby\Login\Models\WxAuthorization;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use GuzzleHttp\Exception\GuzzleException;

class WeChatTemplateService
{
    /**
     * 公众号模板消息
     *
     * @param $appId
     * @param $userId
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     */
    public static function sendTemplateMsg($appId, $userId)
    {
        // 公众号关联小程序信息
        $wxAuthorization = WxAuthorization::with("platform")->where("status", 1)->where("type", 0)->first();
        if (!$wxAuthorization) {
            debug_log_info(__METHOD__ . " 未查询到授权公众号或者已取消授权");
        }
        // 充值提醒开启
        if ($wxAuthorization->recharge_notice) {
            $toUser = UserOfficialAccount::where('user_id', $userId)->where('uuid', $wxAuthorization->uuid)->value('open_id');

            $template_id = Templatelist::where('uuid', $wxAuthorization->uuid)->where('short_id', env('SMART_RECHARGE_NOTICE', 'OPENTM417049252'))->value('template_id');

            debug_log_info(__METHOD__ . " Send Template Msg {$template_id} To User {$toUser}....");

            if ($toUser && $template_id) {
                $data = [
                    'touser' => $toUser,
                    'template_id' => $template_id,
                    'miniprogram' => [
                        'appid' => $appId,
                        'pagepath' => 'pages/index/index', // 首页
                    ],
                    'client_msg_id' => md5($toUser . $userId),
                    'data' => [
                        'first' => ['value' => '恭喜您，充值成功，快去继续观看吧~'],
                        'keyword1' => ['value' => '余额充值'],
                        'keyword2' => ['value' => sprintf('%.2f', 1)],
                        'keyword3' => ['value' => date('Y年m月d日 H:m', time())],
                        'remark' => ['value' => '👉 点击此消息继续播放后续精彩内容~'],
                    ]
                ];
                debug_log_info(__METHOD__ . " Send Template Msg ", $data);

                $refreshToken = $wxAuthorization->refresh_token ?? null;
                $openPlatform = WeChatPlatformService::platform($wxAuthorization->platform->uuid); // 三方平台
                $officialAccount = $openPlatform->officialAccount($wxAuthorization->uuid, $refreshToken);  // 微信公众号
                $response = $officialAccount->template_message->send($data);
                if ($response['errcode'] != 0) {
                    error_log_info(__METHOD__ . " Send Template Fail! ", $response);
                }
            }
        } else {
            debug_log_info(__METHOD__ . " Recharge Notice Not Open! ");
        }
    }
}
