<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 18-5-6
 * Time: 下午7:19
 */

namespace XBlock\Kernel\Models;


class Header extends BlockBaseModel
{
    protected $table = 'system.header';

    public function property()
    {
        return $this->hasMany('XBlock\Kernel\Models\HeaderProperty', 'header_uuid');
    }
}
