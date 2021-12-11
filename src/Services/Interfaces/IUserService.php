<?php


namespace Andruby\Login\Services\Interfaces;


interface IUserService
{
    /**
     * 手机号注册
     */
    public const USER_TYPE_MOBILE = 0;
    /**
     * 微信公众号注册
     */
    public const USER_TYPE_WX_WEB = 1;
    /**
     * 微信小程序注册
     */
    public const USER_TYPE_WX_MINI = 2;
    /**
     * 微信App注册
     */
    public const USER_TYPE_WX_APP = 3;

    public function genToken($user_id);

    public function mobile($mobile);

    public function userInfo($user_id);

    public function defaultNickname($user_id);

    public function register($openid, $nickname,
                             $avatar, $unionid, $user_type = IUserService::USER_TYPE_WX_WEB, $access_token = null,
                             $refresh_token = null, $expires_in = null,
                             $scope = null);

}