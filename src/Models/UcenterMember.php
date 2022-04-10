<?php

namespace Andruby\Login\Models;

use Andruby\ApiToken\HasApiToken;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\UcenterMember
 *
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User query()
 * @mixin \Eloquent
 * @property int $id 用户ID
 * @property string $username 用户名，手机注册时候是手机号，微信注册时是unionid
 * @property string $password 密码
 * @property string $email 用户邮箱
 * @property string $mobile 用户手机
 * @property string|null $phone 小鹅通绑定手机号
 * @property \Illuminate\Support\Carbon $reg_time 注册时间
 * @property int $reg_ip 注册IP
 * @property int $last_login_time 最后登录时间
 * @property int $last_login_ip 最后登录IP
 * @property \Illuminate\Support\Carbon $update_time 更新时间
 * @property int|null $status 用户状态
 * @property int $user_type 0手机注册，1微信
 * @property string|null $unionid 微信unionid
 * @property string|null $access_token 微信的access_token
 * @property int|null $expires_in 微信授权过期时间
 * @property string|null $refresh_token 微信刷新token
 * @property string|null $scope 微信授权范围
 * @property-read \Illuminate\Database\Eloquent\Collection|\Andruby\ApiToken\ApiToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|UcenterMember whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UcenterMember whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UcenterMember whereExpiresIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UcenterMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UcenterMember whereLastLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UcenterMember whereLastLoginTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UcenterMember whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UcenterMember wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UcenterMember wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UcenterMember whereRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UcenterMember whereRegIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UcenterMember whereRegTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UcenterMember whereScope($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UcenterMember whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UcenterMember whereUnionid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UcenterMember whereUpdateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UcenterMember whereUserType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UcenterMember whereUsername($value)
 */
class UcenterMember extends Authenticatable
{
    use Notifiable, HasApiToken;

    protected $table = 'ucenter_member';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password', 'mobile', 'phone',
        'reg_ip', 'last_login_time', 'last_login_ip', 'status', 'user_type',
        'unionid', 'access_token', 'expires_in', 'refresh_token', 'scope'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'updated_at', 'created_at', 'mobile', 'phone'
    ];


    protected $visible = ['id', 'username', 'token'];

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

    public static function mobile_register($mobile)
    {
        $data['username'] = $mobile;
        $data['password'] = $mobile . 'wrqwre';
        $data['email'] = $mobile . '@163.com';
        $data['mobile'] = $mobile;
        $data['reg_time'] = time();
        $data['reg_ip'] = '122';
        $data['status'] = '1';
        $data['user_type'] = '0';

        $data = UcenterMember::create($data);
        return $data['id'];
    }

    public static function wx_register($openid, $unionid, $user_type = 1, $access_token = null,
                                       $refresh_token = null, $expires_in = null, $scope = null)
    {
        $data['username'] = $openid;
        $data['password'] = $data['username'] . 'wrqwre';
        $data['email'] = $data['username'] . '@163.com';
        $data['access_token'] = $access_token;
        $data['refresh_token'] = $refresh_token;
        $data['expires_in'] = $expires_in;
        $data['unionid'] = $unionid;
        $data['scope'] = $scope;

        $data['reg_ip'] = request()->getClientIp();
        $data['status'] = 1;
        $data['user_type'] = $user_type;

        $data = UcenterMember::create($data);
        return $data['id'];
    }
}
