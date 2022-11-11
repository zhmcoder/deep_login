<?php

namespace Andruby\Login\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Templatelist extends Model
{
    // 软删除
    use SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'templatelist';
    /**
     * 这个属性应该被转换为原生类型.
     *
     * @var array
     */
    protected $casts = [
    ];

    protected $hidden =[
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
      'uuid', 'template_id', 'short_id', 'title', 'content'
    ];
}
