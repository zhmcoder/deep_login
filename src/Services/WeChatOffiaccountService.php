<?php

namespace Andruby\Login\Services;

use Andruby\Login\Models\UserOfficialAccount;
use Andruby\Login\Models\WechatAutoReply;
use Andruby\Login\Models\WechatResponse;
use Andruby\Login\Models\WxAuthorization;
use Andruby\Login\Utils\AppConstent;
use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\MiniProgramPage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class WeChatOffiaccountService
{
    public static $officialAccount;
    public static $wxAuthorization;

    /**
     * 生成公众号带参二维码
     * @param $appId
     * @param $userId
     * @return string|Response
     * @author fans
     */
    public static function qrcode($appId, $userId)
    {
        try {
            $where = [
                'uuid' => $appId,
                'status' => 1,
                'type' => 0,
            ];
            $wxAuthorization = WxAuthorization::with("platform")->where($where)->first();
            if (!$wxAuthorization) {
                return '';
            }

            $refreshToken = $wxAuthorization->refresh_token ?? null;
            $openPlatform = WeChatPlatformService::platform($wxAuthorization->platform->uuid);
            $officialAccount = $openPlatform->officialAccount($appId, $refreshToken);

            $result = $officialAccount->qrcode->temporary($userId, 1 * 24 * 3600);

            return $officialAccount->qrcode->url($result['ticket']);
        } catch (\Exception $e) {
            error_log_info($e->getMessage());
        }
    }

    /**
     * @param $appId
     * @return string|Response
     */
    public static function eventFormat($appId)
    {
        try {
            self::$wxAuthorization = WxAuthorization::with("platform")->where("uuid", $appId)->where("status", 1)->where("type", 0)->first();
            if (!self::$wxAuthorization) {
                error_log_info("未查询到授权公众号或者已取消授权：{$appId}");
                return "success";
            }
            $refreshToken = self::$wxAuthorization->refresh_token ?? null;
            $adminId = self::$wxAuthorization->admin_id ?? null;
            $openPlatform = WeChatPlatformService::platform(self::$wxAuthorization->platform->uuid);
            self::$officialAccount = $openPlatform->officialAccount($appId, $refreshToken);
            $server = self::$officialAccount->server;
            $server->push(function ($message) use ($adminId) {
                return self::eventCallBack($message, $adminId);
            });
            return $server->serve();
        } catch (\Exception $e) {
            error_log_info($e->getMessage());
        }
    }


    /**
     * @param $message
     * @param $adminId
     * @return Text|null
     */
    public static function eventCallBack($message, $adminId)
    {
        Log::info('[ WeChat ] [ MP ] [ API ] Message: ' . json_encode($message));
        $replayData = null;
        $msgType = $message['MsgType'] ?? "";
        switch ($msgType) {
            case AppConstent::MESSAGE_TYPE_EVENT://收到事件消息
                switch ($message["Event"]) {
                    case AppConstent::MESSAGE_EVENT_SCAN: //永久二维码已关注
                        $replayData = self::getSubscribe($message, $adminId);
                        break;
                    case AppConstent::MESSAGE_EVENT_SUBSCRIBE: // 关注
                        $replayData = self::eventSubscribe($message, $adminId);
                        break;
                    case AppConstent::MESSAGE_EVENT_UNSUBSCRIBE: // 取消关注
                        //取消订阅不需要回复
                        self::eventUnsubscribe($message, $adminId);
                        break;
                    case AppConstent::MESSAGE_EVENT_CLICK: // 点击
                        $replayData = self::autoReplyEvent($message, $adminId);
                        self::pushClickMenuDelayedMessage($message["FromUserName"], $adminId); // 点击菜单 延时消息
                        break;
                    case AppConstent::MESSAGE_EVENT_VIEW: // 点击连接
                        self::pushClickMenuDelayedMessage($message["FromUserName"], $adminId); // 点击菜单 延时消息
                        break;
                    case AppConstent::MESSAGE_EVENT_MASSSENDJOBFINISH: // 群发回调
                        break;
                    case "default":
                        //不处理的事件类型后期在考虑
                        Log::info("关注回复异常: 不处理的事件类型后期在考虑:" . json_encode($message));
                        return null;
                }
                break;
            case AppConstent::MESSAGE_TYPE_TEXT://收到文字消息
//                $res = new Text('Hello world2!');
                $replayData = self::eventText($message, $adminId);
                break;
            case AppConstent::MESSAGE_TYPE_IMAGE://收到图片消息
            case AppConstent::MESSAGE_TYPE_VOICE://收到语音消息
            case AppConstent::MESSAGE_TYPE_VIDEO://收到视频消息
            case AppConstent::MESSAGE_TYPE_LOCATION://收到坐标消息
            case AppConstent::MESSAGE_TYPE_LINK://收到链接消息
            case AppConstent::MESSAGE_TYPE_FILE://收到文件消息
                // ... 其它消息
            default:
                $replayData = new Text('未检测到相关内容!');
                break;
        }
        return $replayData;
    }

    /**
     * @param $message
     * @param $adminId
     * @return Text|string|void|null
     */
    public static function getSubscribe($message, $adminId)
    {
        $eventKey = explode('_', $message["EventKey"]);
        //检查是否是永久二维码关注
        if ($eventKey && count($eventKey) == 2 && $eventKey[0] == 'qrscene') {
            $key = $eventKey[1] ?? "";
            //根据key返回相关内容后期添加
            $replayData = "";
            if ($replayData) {
                return $replayData;
            }
        }

        // 已关注未保存关注数据 & 已关注绑定
        if (!empty($message["EventKey"])) {
            $user = self::$officialAccount->user->get($message["FromUserName"]);
            $userOfficialAccount = UserOfficialAccount::getUserInfo($message["FromUserName"], self::$wxAuthorization->uuid, $adminId);

            $date = date("Y-m-d H:i:s");
            if (!$userOfficialAccount) {
                Log::info(__METHOD__, ['message' => '用户数据不存在创建用户', 'adminId' => $adminId, 'user' => json_encode($user)]);
                $userOfficialAccountData = [
                    "subscribe_time" => $date,
                    "optimizer_id" => $adminId ?? "",
                    "city" => $user["city"] ?? "",
                    "province" => $user["province"] ?? "",
                    "country" => $user["country"] ?? "",
                    "sex" => $user["sex"] ?? 0,//值为1时是男性，值为2时是女性，值为0时是未知
                    "operate_time" => $date,
                    "open_id" => $user["openid"] ?? "",
                    "nickname" => $user["nickname"] ?? "",
                    "uuid" => self::$wxAuthorization->uuid ?? "",
                    "unionid" => $user["unionid"] ?? "",
                    'user_id' => $message["EventKey"],
                ];
                UserOfficialAccount::create($userOfficialAccountData);
            } else {
                $userOfficialAccount->subscribe_time = $user["subscribe_time"] ? date("Y-m-d H:i:s", $user["subscribe_time"]) : $date;
                $userOfficialAccount->operate_time = $date;
                $userOfficialAccount->is_subscribe = 1;
                if ($userId = $message["EventKey"]) {
                    $userOfficialAccount->user_id = $userId;
                }
                $userOfficialAccount->save();
            }
        }

        //判断公众号是否关闭了自动回复
        return self::autoReplyEvent($message, $adminId);
    }

    /**
     * @param $message
     * @param $adminId
     * @return Text|void|null
     */
    public static function autoReplyEvent($message, $adminId)
    {
        $msg = $message["Event"] ?? "";
        $openid = $message["FromUserName"] ?? "";
        //更新交互操作时间
        $event_key = "";
        switch ($msg) {
            case '$openid$':   //内置规则 查看当前访问用户openid
                return new Text($openid);
            case '$userid$':
                $userOfficialAccount = UserOfficialAccount::getUserInfo($openid, self::$wxAuthorization->uuid, $adminId);
                $userId = $userOfficialAccount->user_id ?? "";
                return new Text($userId ? (string)$userId : '未绑定用户');
            case 'subscribe':   //关注事件
                $autoReply = WechatAutoReply::where([
                    'text' => $msg,
                    'uuid' => self::$wxAuthorization->uuid ?? "",
                    'status' => 1,
                    'optimizer_id' => $adminId,
                ])->first();
                $event_key = $autoReply->event_key ?? "";
                break;
            case 'CLICK':   //点击事件
                $event_key = $message["EventKey"];
        }
        if (!$event_key) {
            return null;
        }
        $result = WechatResponse::where([
            'event_key' => $event_key,
            'status' => 1,
            'optimizer_id' => $adminId,
        ])->first();
        return self::formatAutoReply($result, $message["FromUserName"]);
    }

    /**
     * @param $result
     * @param $open_id
     * @return Image|News|Text|null
     */
    public static function formatAutoReply($result, $open_id)
    {
        try {
            Log::info(__METHOD__, ['result' => json_encode($result), 'open_id' => $open_id]);

            if (!$result) return null;

            $search = ['{MP_OPEN_ID}'];
            $fillOpenId = UserOfficialAccount::where(['open_id' => $open_id])
                ->whereNull('user_id')
                ->exists();

            /**
             * 返回内容处理
             */
            if (isset($result) && $result) {
                switch ($result['type']) {
                    case 'text':
                        $content = $result["content"] ?? "";
                        $content = $fillOpenId
                            ? Str::replace($search, [$open_id], $content)
                            : $content;

                        return new Text((string)$content);
                    case 'miniprogrampage':
                        $content = $result["content"] ?? [];
                        $media_id = $content["media_id"] ?? "";
                        $thumb_media_id = $content["thumb_media_id"] ?? "";
                        $thumb_media_id = $media_id ? $media_id : $thumb_media_id;
                        $pagepath = $content["pagepath"] ?? "";
                        $pagepath = $pagepath && $fillOpenId
                            ? Str::replace($search, [$open_id], $pagepath)
                            : $pagepath;
                        $data = [
                            "title" => $content["title"] ?? "",
                            "appid" => $content["appid"] ?? "",
                            "pagepath" => $pagepath,
                            "thumb_media_id" => $thumb_media_id,
                        ];
                        $res = new MiniProgramPage($data);
                        if (!$data['appid'] || !$data["pagepath"] || !$data["thumb_media_id"]) {
                            log::error("关注回复异常: 卡片模式存储数据异常" . json_encode($data));
                        }
                        self::$officialAccount->customer_service->message($res)->to($open_id)->send();
                        return null;
                    case 'image':
                        $content = $result["content"] ?? [];
                        $media_id = $content['media_id'] ?? "";
                        if (!$media_id) {
                            Log::error("关注回复异常: 未发现图片回复相关资源");
                            return null;
                        }
                        return new Image($media_id);
                    case 'news':
                        $content = $result["content"] ?? [];
                        if (!is_array($content)) {
                            Log::error("关注回复异常: content不是数组请检测");
                            return null;
                        }
                        $items = [];
                        foreach ($content as $v) {
                            $url = $v["url"] ?? "";
                            if ($url && $fillOpenId) {
                                $bindParam = "platform=wechat&mp_open_id={$open_id}";
                                $url = Str::contains($url, '?')
                                    ? $url . "&" . $bindParam
                                    : $url . "?" . $bindParam;
                            }

                            $items[] = new NewsItem([
                                'title' => $v["title"] ?? "",
                                'description' => $v["description"] ?? "",
                                'url' => $url,
                                'image' => $v["pic_url"] ?? "",
                            ]);
                        }
                        if (!$items) {
                            Log::error("关注回复异常: 未发现图文回复相关资源");
                            return null;
                        }
                        return new News($items);
                    default:
                        return null;
                }
            }
            return null;
        } catch (\Throwable $e) {
            Log::error(__METHOD__, ["message" => $e->getMessage()]);
            return null;
        }
    }

    /**
     * @param $message
     * @param $adminId
     * @return Text|void|null
     */
    public static function eventSubscribe($message, $adminId)
    {
        try {
            Log::info(__METHOD__, ['message' => json_encode($message), 'adminId' => $adminId]);
            //注册用户
            $user = self::$officialAccount->user->get($message["FromUserName"]);
            $userOfficialAccount = UserOfficialAccount::getUserInfo($message["FromUserName"], self::$wxAuthorization->uuid, $adminId);
            $data = date("Y-m-d H:i:s");
            if (!$userOfficialAccount) {
                Log::info(__METHOD__, ['message' => '用户数据不存在创建用户', 'adminId' => $adminId, 'user' => json_encode($user)]);
                $userOfficialAccountData = [
                    "subscribe_time" => $data,
                    "optimizer_id" => $adminId ?? "",
                    "city" => $user["city"] ?? "",
                    "province" => $user["province"] ?? "",
                    "country" => $user["country"] ?? "",
                    "sex" => $user["sex"] ?? 0,//值为1时是男性，值为2时是女性，值为0时是未知
                    "operate_time" => $data,
                    "open_id" => $user["openid"] ?? "",
                    "nickname" => $user["nickname"] ?? "",
                    "uuid" => self::$wxAuthorization->uuid ?? "",
                    "unionid" => $user["unionid"] ?? "",
                    'user_id' => self::getEventKey($message) ?? '',
                ];
                UserOfficialAccount::create($userOfficialAccountData);
            } else {
                $userOfficialAccount->subscribe_time = $user["subscribe_time"] ? date("Y-m-d H:i:s", $user["subscribe_time"]) : $data;
                $userOfficialAccount->operate_time = $data;
                $userOfficialAccount->is_subscribe = 1;
                if ($userId = self::getEventKey($message)) {
                    $userOfficialAccount->user_id = $userId;
                }
                $userOfficialAccount->save();
            }

            //回复消息
            return self::autoReplyEvent($message, $adminId);
        } catch (\Throwable $e) {
            Log::error(__METHOD__, ["message" => $e->getMessage()]);
            return null;
        }
    }

    /**
     * @param $message
     * @param $adminId
     */
    public static function eventUnsubscribe($message, $adminId)
    {
        //更新用户表数据
        $userOfficialAccount = UserOfficialAccount::getUserInfo($message["FromUserName"], self::$wxAuthorization->uuid, $adminId);
        if ($userOfficialAccount) {
            $userOfficialAccount->is_subscribe = 2;
            $userOfficialAccount->save();
        } else {
            Log::info("关注回复异常: 未检测到取关用户信息");
        }
    }

    /**
     * @param $message
     * @param $adminId
     * @return Text|void|null
     */
    public static function eventText($message, $adminId)
    {
        return self::autoReplyText($message, $adminId);
    }

    /**
     * @param $message
     * @param $adminId
     * @return Text|void|null
     */
    public static function autoReplyText($message, $adminId)
    {
        $msg = $message["Content"] ?? "";
        $openid = $message["FromUserName"] ?? "";
        //更新交互操作时间
        switch ($msg) {
            case '$openid$':   //内置规则 查看当前访问用户openid
                return new Text($openid);
            case '$userid$':
                $userOfficialAccount = UserOfficialAccount::getUserInfo($openid, self::$wxAuthorization->uuid, $adminId);
                $userId = $userOfficialAccount->user_id ?? "";
                return new Text($userId ? (string)$userId : '未绑定用户');
        }

        $autoReply = WechatAutoReply::where([
            'text' => $msg,
            'uuid' => self::$wxAuthorization->uuid ?? "",
            'status' => 1,
            'optimizer_id' => $adminId,
        ])->first();
        $result = null;
        if ($autoReply) { //具有自动回复
            $result = WechatResponse::where([
                'event_key' => $autoReply->event_key,
                'status' => 1,
            ])->first();
        }
        if (!$result) {
            return new Text('很抱歉，未找到您要查询的内容～');
        }
        return self::formatAutoReply($result, $message["FromUserName"]);
    }

    /**
     * 获取关注二维码参数
     * @param $message
     * @return string
     */
    public static function getEventKey($message): string
    {
        $eventKey = explode('_', $message["EventKey"]);

        if ($eventKey && count($eventKey) == 2 && $eventKey[0] == 'qrscene') {
            return $eventKey[1] ?? "";
        }

        return '';
    }
}
