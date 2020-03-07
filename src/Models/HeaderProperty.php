<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 18-5-6
 * Time: 下午7:11
 */

namespace XBlock\Kernel\Models;


class HeaderProperty extends BlockBaseModel
{
    protected $table = 'system.header_property';
    protected $uuid_type= 'suid';
    protected $appends = [
        'title', 'index'
    ];

    protected $guarded = [];

    public function header()
    {
        return $this->belongsTo('XBlock\Kernel\Models\Header', 'header_uuid')->withDefault();
    }

    public function getTitleAttribute()
    {
        return $this->header->title;
    }

    public function getIndexAttribute()
    {
        return $this->header->index;
    }
}