<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-12-2
 * Time: 下午4:55
 */

namespace XBlock\Kernel\Elements\Fields;


trait WhereDateBetween
{
    public function where($query, array $value, $key = null)
    {

        return $query->where(function ($query) use ($key, $value) {
            $key = $key ? $key : $this->index;
            $query->WhereDate($key, '>=', $value[0])->whereDate($key, '<=', $value[1]);
        });
    }

    public function whereCollection($collection, $value)
    {
        return $collection->filter(function ($item) use ($value) {
            return $item[$this->index] >= $value[0] && $item[$this->index] <= $value[1];
        });
    }

}
