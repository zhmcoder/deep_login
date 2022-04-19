<?php

namespace Andruby\Login\Services;

use Andruby\Login\Models\Member;
use Andruby\Login\Models\UcenterMember;
use Andruby\Login\Services\Interfaces\IUserService;

/**
 * @method static UserService instance()
 *
 * Class UserService
 * @package Andruby\Login\Services
 */
class UserService implements IUserService
{
    public static function __callStatic($method, $params): UserService
    {
        return new self();
    }

    public function register($openid, $nickname, $avatar, $unionid, $area_info = null,
                             $user_type = IUserService::USER_TYPE_WX_WEB,
                             $access_token = null, $refresh_token = null,
                             $expires_in = null, $scope = null)
    {
        $user_info = UcenterMember::where('username', $unionid)->first();

        if (empty($user_info)) {
            $user_id = UcenterMember::wx_register($openid, $unionid, $user_type,
                $access_token, $refresh_token, $expires_in, $scope);
            if (!Member::register($user_id, $nickname, $avatar)) {
                UcenterMember::where('id', $user_id)->delete();
                return 0;
            }
        } else {
            $user_id = $user_info['id'];
        }
        return $user_id;
    }

    public function mobile($mobile)
    {
        $user_info = UcenterMember::where('username', $mobile)->first();
        if (empty($user_info)) {
            $user_id = UcenterMember::mobile_register($mobile);
            if (!Member::mobile_register($user_id,
                $this->defaultNickname($user_id))) {
                UcenterMember::where('id', $user_id)->delete();
                return 0;
            }
        } else {
            $user_id = $user_info['id'];
        }
        return $user_id;
    }

    public function genToken($user_id)
    {
        $member = new UcenterMember();
        return $member->genToken($user_id);
    }

    public function userInfo($user_id)
    {
        $userInfo = Member::where('uid', $user_id)
            ->first(['uid as user_id', 'nickname', 'head_pic', 'head_pic_small']);
        if ($userInfo) {
            $userInfo = $userInfo->toArray();
            $userInfo['token'] = $this->genToken($user_id);
        }
        return $userInfo;
    }

    public function defaultNickname($user_id)
    {
        return config('deep_login.nickname_pre') . mt_rand(10, 99) . $user_id . mt_rand(10, 99);
    }

    public function dealWxInfo($wxInfo)
    {
        if (array_key_exists('nickname', $wxInfo)) {
            $userData['nickname'] = $wxInfo['nickname'];
        } else {
            $userData['nickname'] = $wxInfo['nickName'];
        }

        if (array_key_exists('headimgurl', $wxInfo)) {
            $userData['avatar_small'] = $wxInfo['headimgurl'];
            $userData['avatar'] = $wxInfo['headimgurl'];
        } else {
            $userData['avatar_small'] = $wxInfo['avatarUrl'];
            $userData['avatar'] = $wxInfo['avatarUrl'];
        }

        if (array_key_exists('sex', $wxInfo)) {
            $userData['sex'] = $wxInfo['sex'];
        } else {
            $userData['sex'] = $wxInfo['gender'];
        }

        if (array_key_exists('language', $wxInfo)) {
            $userData['language'] = $wxInfo['language'];
        } else {
            $userData['language'] = null;
        }

        if (array_key_exists('language', $wxInfo)) {
            $userData['language'] = $wxInfo['language'];
        } else {
            $userData['language'] = null;
        }
        if (array_key_exists('city', $wxInfo)) {
            $userData['city'] = $wxInfo['city'];
        } else {
            $userData['city'] = null;
        }
        if (array_key_exists('province', $wxInfo)) {
            $userData['province'] = $wxInfo['province'];
        } else {
            $userData['province'] = null;
        }
        if (array_key_exists('country', $wxInfo)) {
            $userData['country'] = $wxInfo['country'];
        } else {
            $userData['country'] = null;
        }
        if (array_key_exists('unionid', $wxInfo)) {
            $userData['unionid'] = $wxInfo['unionid'];
        } else {
            $userData['unionid'] = null;
        }

        if (array_key_exists('openId', $wxInfo)) {
            $userData['openId'] = $wxInfo['openId'];
        } else {
            $userData['openId'] = null;
        }

        return $userData;
    }


}
