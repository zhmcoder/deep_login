<?php

namespace Andruby\Login\Models\WeChat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WechatAutoReply  extends Model
{
    // 软删除
    use SoftDeletes;
    protected $table = "wechat_autoreply";
    /**
     * 指示模型是否自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * 可以被批量赋值的属性。
     *
     * @var array
     */
//    protected $fillable = [
//        'optimizer_id',
//        'type',
//        'event_keys',
//        'text_content',
//        'news_content',
//        'status'
//    ];

//    /**
//     * 应该被转换成原生类型的属性。
//     *
//     * @var array
//     */
//    protected $casts = [
//        'event_keys' => 'json',
//        'text_content' => 'json',
//        'news_content' => 'json',
//    ];


}
