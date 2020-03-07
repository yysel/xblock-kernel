<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-12-23
 * Time: 下午6:43
 */

namespace XBlock\Kernel\Elements\Components;


class LineChart extends Chart
{
    public function __construct()
    {
        $this->line();
    }

    public function x($value): LineChart
    {
        return $this->setProperty('x', $value);
    }

    public function y($value): LineChart
    {
        return $this->setProperty('y', $value);
    }

    public function groupBy($value): LineChart
    {
        return $this->setProperty('group_by', $value);
    }

    public function hasPoint($value = true): LineChart
    {
        return $this->setProperty('has_point', $value);
    }

    public function color($value): LineChart
    {
        return $this->setProperty('color', $value);
    }
}