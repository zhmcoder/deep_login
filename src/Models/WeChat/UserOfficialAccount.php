<?php

namespace Andruby\Login\Models\WeChat;

use Illuminate\Database\Eloquent\Model;

class UserOfficialAccount extends Model
{
    /**
     * @var string
     */
    protected $table = 'user_official_accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function getUserInfo($open_id, $uuid, $optimizer_id)
    {
        $where = [
            'open_id' => $open_id,
            'uuid' => $uuid,
            'optimizer_id' => $optimizer_id,
        ];
        return self::where($where)->first();
    }
}
