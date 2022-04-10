<?php

namespace Andruby\Login\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MemberInfo
 *
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User query()
 * @mixin \Eloquent
 * @property int $uid 用户ID
 * @property string $nickname 昵称
 * @property int $sex 性别
 * @property string|null $head_pic
 * @property string|null $head_pic_small
 * @property string|null $head_pic_src
 * @property string|null $birthday 生日
 * @property string $qq qq号
 * @property int $score 用户积分
 * @property int $login 登录次数
 * @property int $reg_ip 注册IP
 * @property \Illuminate\Support\Carbon $reg_time 注册时间
 * @property int $last_login_ip 最后登录IP
 * @property int $last_login_time 最后登录时间
 * @property int $status 会员状态
 * @property int $notify
 * @property int $pushPlatform
 * @property string $clientId
 * @property string $regId
 * @property string $deviceToken
 * @property string $alias
 * @property string|null $city 城市(微信)
 * @property string|null $province 省(微信)
 * @property string|null $country 国家(微信)
 * @property string|null $language 语言(微信)
 * @property string|null $xiaoe_id 小鹅通用户id
 * @property int $coin
 * @property \Illuminate\Support\Carbon $update_time
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereCoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereDeviceToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereHeadPic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereHeadPicSmall($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereHeadPicSrc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereLastLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereLastLoginTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereNotify($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo wherePushPlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereQq($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereRegId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereRegIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereRegTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereUpdateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MemberInfo whereXiaoeId($value)
 */
class MemberInfo extends Model
{

    protected $table = 'member';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid', 'nickname', 'sex', 'head_pic', 'head_pic_small', 'head_pic_src',
        'birthday', 'qq', 'score', 'login', 'last_login_ip', 'last_login_time', 'status',
        'notify', 'pushPlatform', 'clientid', 'deviceToken', 'alias', 'city', 'province',
        'country', 'language'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'updated_at', 'created_at'
    ];

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'reg_time';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'update_time';


    /**
     * 获取当前时间
     *
     * @return int
     */
    public function freshTimestamp()
    {
        return time();
    }

    /**
     * 避免转换时间戳为时间字符串
     *
     * @param DateTime|int $value
     * @return DateTime|int
     */
    public function fromDateTime($value)
    {
        return $value;
    }

    protected $visible = ['uid', 'user_id', 'nickname', 'sex', 'head_pic', 'head_pic_small', 'coin', 'token', 'alipay_id'];


    public static function mobile_register($user_id, $nickName)
    {

        $data = array('nickname' => $nickName, 'status' => 1,
            'head_pic' => config('deep_login.default_head_url'),
            'head_pic_small' => config('deep_login.default_head_url'),
            'uid' => $user_id
        );

        try {
            MemberInfo::create($data);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function wx_register($wxInfo, $user_id)
    {
        if (array_key_exists('nickname', $wxInfo)) {
            $data['nickname'] = $wxInfo['nickname'];
        } else {
            $data['nickname'] = $wxInfo['nickName'];
        }

        if (array_key_exists('headimgurl', $wxInfo)) {
            $data['head_pic_small'] = $wxInfo['headimgurl'];
            $data['head_pic'] = $wxInfo['headimgurl'];
        } else {
            $data['head_pic_small'] = $wxInfo['avatarUrl'];
            $data['head_pic'] = $wxInfo['avatarUrl'];
        }

        if (array_key_exists('sex', $wxInfo)) {
            $data['sex'] = $wxInfo['sex'];
        } else {
            $data['sex'] = $wxInfo['gender'];
        }

        if (array_key_exists('language', $wxInfo)) {
            $data['language'] = $wxInfo['language'];
        }

        if (array_key_exists('language', $wxInfo)) {
            $data['language'] = $wxInfo['language'];
        }
        if (array_key_exists('city', $wxInfo)) {
            $data['city'] = $wxInfo['city'];
        }
        if (array_key_exists('province', $wxInfo)) {
            $data['province'] = $wxInfo['province'];
        }
        if (array_key_exists('country', $wxInfo)) {
            $data['country'] = $wxInfo['country'];
        }

        $data['uid'] = $user_id;

        try {
            MemberInfo::create($data);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function register($user_id, $nickname, $avatar)
    {
        $nickName = empty($nickname) ? ('用户' . $user_id) : $nickname;
        $avatar = empty($avatar) ? config('deep_login.default_head_url') : $avatar;
        $data = array('nickname' => $nickName,
            'status' => 1,
            'uid' => $user_id,
            'head_pic_small' => $avatar,
            'head_pic' => $avatar,
        );

        try {
            MemberInfo::create($data);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
