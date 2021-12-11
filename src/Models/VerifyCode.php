<?php

namespace Andruby\Login\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * 审核 直接验证通过
 */
define('HOST_PHONE', '13581714393');
define('HOST_PHONE_1', '13581714400');
define('HOST_PHONE_CODE', '1111');

/**
 * App\Models\VerifyCode
 *
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User query()
 * @mixin \Eloquent
 */
class VerifyCode extends Model
{

    public const STATUS_WAIT_USE = 0;
    public const STATUS_USED = 1;
    public const STATUS_UNUSED = 2;

    protected $table = 'verify_code';

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

