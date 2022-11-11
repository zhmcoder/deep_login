<?php

namespace Andruby\Login\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WxPlatform extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'wx_platforms';

    /**
     * @var string[]
     */
    protected $guarded = ['id'];
}
