<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-11-29
 * Time: 上午7:10
 */

namespace XBlock\Kernel\Elements\Components;


class Base
{
    protected $component = 'table';
    protected $property = [];

    public function jsonSerialize()
    {
        return $this->property;
    }

    public function setProperty($key, $value)
    {
        $this->property[$key] = $value;
        return $this;
    }

    public function getComponent()
    {
        return $this->component;
    }

    public function getProperty()
    {
        return $this->property;
    }
}