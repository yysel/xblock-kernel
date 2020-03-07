<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-12-2
 * Time: 下午4:53
 */

namespace XBlock\Kernel\Elements\Fields;


trait WhereEqual
{
    public function where($query, $value, $key = null)
    {
        $key = $key ? $key : $this->index;
        return $query->where($key, $value);
    }

    public function whereCollection($collection, $value)
    {
        return $collection->where($this->index, $value);
    }
}