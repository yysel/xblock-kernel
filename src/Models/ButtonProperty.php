<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 18-5-6
 * Time: 下午7:11
 */

namespace XBlock\Kernel\Models;


class ButtonProperty extends BlockBaseModel
{
    protected $table = 'system.button_property';
    protected $uuid_type = 'suid';
    protected $appends = [
        'text', 'type', 'mode',
    ];

    public function permissionModel()
    {
        return $this->belongsTo('XBlock\Kernel\Models\Permission', 'permission', 'index')->withDefault();
    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($query) {
            $query->uuid = guid();
            $query->style = 'button';
            if ($query->button_uuid == '244ACBD7-9B15-1796-A0DB-9DB77E7E67DE') $query->confirm = 1;
        });
    }

    public function button()
    {
        return $this->belongsTo('XBlock\Kernel\Models\ButtonModel', 'button_uuid')->withDefault();
    }







}