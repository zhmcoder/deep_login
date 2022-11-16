<?php

namespace Andruby\Login\Models;

use Illuminate\Database\Eloquent\Model;

class WechatResponse  extends Model
{

    protected $table = "wechat_response";
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

    /**
     * 应该被转换成原生类型的属性。
     *
     * @var array
     */
    protected $casts = [
        'content' => 'json',
    ];


}
