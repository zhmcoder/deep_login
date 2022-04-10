<?php

namespace App\Api\Models;

use Illuminate\Database\Eloquent\Model;

define('HOST_PHONE', '13581714393');
define('HOST_PHONE_1', '13581714400');
define('HOST_PHONE_CODE', '1111');

/**
 * 审核 直接验证通过
 *
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyCode query()
 * @mixin \Eloquent
 * @property int $id
 * @property string $mobile 手机号
 * @property string $code 验证码
 * @property string|null $ctime 创建时间
 * @property string|null $utime 使用时间
 * @property int $status -1失效0未使用1已使用
 * @property int $sendStatus 0表示发送 1表示发送成功，2表示发送失败
 * @property string|null $sendTime
 * @property string|null $smsCreated
 * @property string|null $smsSid
 * @property string|null $smsStatus
 * @property string|null $client_ip
 * @property int|null $created_at
 * @property int|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyCode whereClientIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyCode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyCode whereCtime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyCode whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyCode whereSendStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyCode whereSendTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyCode whereSmsCreated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyCode whereSmsSid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyCode whereSmsStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyCode whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyCode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VerifyCode whereUtime($value)
 */
class VerifyCode extends Model
{
    public const STATUS_WAIT_USE = 0;
    public const STATUS_USED = 1;
    public const STATUS_UNUSED = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mobile', 'code', 'sendStatus', 'sendTime', 'client_ip', 'utime'
    ];

    protected $casts = [
        'created_at' => "timestamp",
        'updated_at' => "timestamp",
    ];

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

}

