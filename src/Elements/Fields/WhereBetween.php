<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-12-2
 * Time: 下午4:55
 */

namespace XBlock\Kernel\Elements\Fields;


trait WhereBetween
{
    public function where($query, array $value, $key = null)
    {
        $key = $key ? $key : $this->index;
        return $query->whereBetween($key, $value);
    }

    public function whereCollection($collection, $value)
    {
        return $collection->filter(function ($item) use ($value) {
            if (!isset($item[$this->index])) return false;
            return $item[$this->index] >= $value[0] && $item[$this->index] <= $value[1];
        });
    }

}
