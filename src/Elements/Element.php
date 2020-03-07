<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-3
 * Time: 下午5:29
 */

namespace XBlock\Kernel\Elements;

use JsonSerializable;
use Closure;

class Element implements JsonSerializable
{
    protected $title;

    protected $index;

    public function __construct($index = '', $title = '')
    {
        if ($index) $this->index = $index;
        if ($title) $this->title = $title;
    }

    static public function make($index = '', $title = '')
    {
        if (!$title) $title = $index;
        return new static($index, $title);
    }

    static public function fill(Array $attribute = [])
    {
        $ele = static::make();
        foreach ($attribute as $key => $value) {
            $ele->$key = $value;
        }
        return $ele;
    }


    public function jsonSerialize()
    {
        return $this->toJson();
    }

    public function __get($key)
    {
        return $this->$key;
    }

    public function when($bool, Closure $func, Closure $func2 = null)
    {
        if ($bool) return $func($this);
        elseif ($func2) return $func2($this);
        return $this;
    }


}