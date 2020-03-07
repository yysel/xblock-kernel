<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-12-2
 * Time: 下午4:56
 */

namespace XBlock\Kernel\Elements\Fields;


trait WhereLike
{
    public function where($query, $value, $key = null)
    {
        $key = $key ? $key : $this->index;
        return $query->where($key, 'like', "%{$value}%");
    }

    public function whereCollection($collection, $value)
    {
        return $collection->filter(function ($item) use ($value) {
            return strpos($item[$this->index], $value) !== false || $item[$this->index] == $value;
        });
    }
}