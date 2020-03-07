<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 18-5-29
 * Time: 下午7:18
 */

namespace XBlock\Kernel\Models;


class MenuItem
{

    public $name;
    public $icon;
    public $path;
    public $display_type;
    public $authority;
    public $is_detail;
    public $hideInMenu;
    public $block;
    public $children;

    public function __construct(Array $data = [])
    {
        if ($data) foreach ($data as $key => $value) $this->$key = $value;
    }


}