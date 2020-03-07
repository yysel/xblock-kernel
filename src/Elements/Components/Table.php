<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-6
 * Time: 上午5:48
 */

namespace XBlock\Kernel\Elements\Components;


class Table extends Base
{
    protected $component = 'table';

    protected $property = [];


    public function link($link): Table
    {
        return $this->setProperty('link', $link);
    }

    public function buttonShow($value): Table
    {
        return $this->setProperty('button_show', $value);
    }

    public function border($value = true): Table
    {
        return $this->setProperty('has_border', $value);
    }

}