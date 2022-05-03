<?php

namespace Andruby\Login\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Api\Models\Member
 *
 * @property int|null $uid 用户ID
 * @property string $nickname 昵称
 * @property int|null $sex 性别
 * @property string|null $head_pic
 * @property string|null $head_pic_small
 * @property string|null $head_pic_src
 * @property string|null $birthday 生日
 * @property string|null $qq qq号
 * @property int|null $score 用户积分
 * @property int|null $login 登录次数
 * @property int|null $reg_ip 注册IP
 * @property \Illuminate\Support\Carbon|null $reg_time 注册时间
 * @property int|null $last_login_ip 最后登录IP
 * @property int|null $last_login_time 最后登录时间
 * @property int|null $status 会员状态
 * @property int|null $notify
 * @property int|null $pushPlatform
 * @property string|null $clientId
 * @property string|null $regId
 * @property string|null $deviceToken
 * @property string|null $alias
 * @property string|null $city 城市(微信)
 * @property string|null $province 省(微信)
 * @property string|null $country 国家(微信)
 * @property string|null $language 语言(微信)
 * @property string|null $xiaoe_id 小鹅通用户id
 * @property int|null $coin
 * @property \Illuminate\Support\Carbon|null $update_time
 * @property string|null $imei
 * @property string|null $android_id
 * @property string|null $idfa
 * @property string|null $idfv
 * @property string|null $appid
 * @property string|null $channel
 * @property string|null $os_type
 * @property string|null $deleted_at
 * @property int|null $create_time
 * @method static \Illuminate\Database\Eloquent\Builder|Member newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Member newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Member query()
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereAndroidId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereAppid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereCoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereCreateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereDeviceToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereHeadPic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereHeadPicSmall($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereHeadPicSrc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereIdfa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereIdfv($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereImei($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereLastLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereLastLoginTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereNotify($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereOsType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member wherePushPlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereQq($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereRegId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereRegIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereRegTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereUpdateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Member whereXiaoeId($value)
 * @mixin \Eloquent
 */
class Member extends Model
{
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

    const SOURCE_WX = 1;
    const SOURCE_H5 = 2;
    const SOURCE_PC = 3;

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
            Member::create($data);
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
            Member::create($data);
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
            Member::create($data);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
