<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-12-2
 * Time: 下午4:55
 */

namespace XBlock\Kernel\Elements\Fields;


trait WhereIn
{
    public function where($query, array $value, $key = null)
    {
        $key = $key ? $key : $this->index;
        return $query->whereIn($key, $value);
    }

    public function whereCollection($collection, array $value)
    {
        return $collection->whereIn($this->index, $value);
    }
}