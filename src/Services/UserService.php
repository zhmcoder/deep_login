<?php


namespace Andruby\Login\Services;

use Andruby\Login\Models\MemberInfo;
use Andruby\Login\Models\UcenterMember;
use Andruby\Login\Services\Interfaces\IUserService;

/**
 * @method static UserService instance()
 *
 * Class ChargeService
 * @package App\Api\Services
 */
class UserService implements IUserService
{
    public static function __callStatic($method, $params): UserService
    {
        return new self();
    }

    public function register($openid, $nickname, $avatar, $unionid,
                             $user_type = IUserService::USER_TYPE_WX_WEB,
                             $access_token = null, $refresh_token = null,
                             $expires_in = null, $scope = null)
    {
        $user_info = UcenterMember::where('username', $openid)->first();

        if (empty($user_info)) {
            $user_id = UcenterMember::wx_register($openid, $unionid,
                $access_token, $refresh_token, $expires_in, $scope);
            if (!MemberInfo::register($user_id, $nickname, $avatar)) {
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
            if (!MemberInfo::mobile_register($user_id,
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
        $userInfo = MemberInfo::where('uid', $user_id)
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


}
