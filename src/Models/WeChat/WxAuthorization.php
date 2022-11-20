<?php

namespace Andruby\Login\Models\WeChat;

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
}
