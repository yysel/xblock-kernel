<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-6
 * Time: 上午5:48
 */

namespace XBlock\Kernel\Elements\Components;



class Detail extends Base
{
    protected $component = 'detail';

    protected $property = [];


    public function column($value): Detail
    {
        return $this->setProperty('column', $value);
    }

    public function border($value = true): Detail
    {
        return $this->setProperty('has_border', $value);
    }

    public function openEdit($value = true): Detail
    {
        return $this->setProperty('open_edit', $value);
    }

}