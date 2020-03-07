<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 18-5-27
 * Time: 下午12:04
 */

namespace XBlock\Kernel\Models;


class Role extends BlockBaseModel
{
    protected $table = 'role';


    public function getPermissionAttribute()
    {
        return collect(json_decode($this->attributes['permission']))->flatten();
    }

    public function setPermissionAttribute($value)
    {
        $this->attributes['permission'] = json_encode($value);
    }

}