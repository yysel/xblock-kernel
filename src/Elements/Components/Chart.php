<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-6
 * Time: 上午5:48
 */

namespace XBlock\Kernel\Elements\Components;


class Chart extends Base
{
    protected $component = 'chart';

    protected $property = [];


    public function type($link = 'line'): Chart
    {
        return $this->setProperty('type', $link);
    }
    public function line(): Chart
    {
        return $this->setProperty('type', 'line');
    }

    public function bar(): Chart
    {
        return $this->setProperty('type', 'bar');
    }

    public function ring(): Chart
    {
        return $this->setProperty('type', 'ring');
    }

    public function pie(): Chart
    {
        return $this->setProperty('type', 'pie');
    }
}