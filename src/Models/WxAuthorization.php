<?php

namespace Andruby\Login\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WxAuthorization extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'wx_authorizations';

    /**
     * @var string[]
     */
    protected $guarded = ['id'];

    /**
     * @var string[]
     */
    protected $casts = [
        'functions' => 'json',
        'authorized_info' => 'json',
        'menu' => 'json'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function platform()
    {
        return $this->hasOne(WxPlatform::class, 'id', 'platform_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function owner()
    {
        return $this->hasOne(Admin::class, 'id', 'admin_id');
    }
}
