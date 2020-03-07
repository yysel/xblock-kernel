<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-12-2
 * Time: 下午4:56
 */

namespace XBlock\Kernel\Elements\Fields;


trait WhereDate
{
    public function where($query, $value, $key = null)
    {
        $key = $key ? $key : $this->index;
        return $query->whereDate($key, $value);
    }

    public function whereCollection($collection, $value)
    {
        return $collection->where($this->index, $value);
    }
}