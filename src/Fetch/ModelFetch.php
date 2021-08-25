<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 18-5-6
 * Time: 下午2:44
 */

namespace XBlock\Kernel\Fetch;

class ModelFetch extends Fetch
{

    public function getContent()
    {
        $this->callWhereHook('beforeWhere')
            ->whitOutDelete()
//            ->authWhere()
            ->requestWhere()
            ->callWhereHook('where')
            ->callWhereHook('afterWhere')
            ->sortAndPage()
            ->formatContent()
            ->callWhereHook('finalWhere');
    }

    public function getBuilder()
    {
        $builder = new $this->block->origin;
        $this->block->primary_key = $builder->getKeyName();
        if (method_exists($builder, 'forceDelete')) $this->block->recyclable = true;
        return $builder;
    }

    protected function requestWhere()
    {
        $fields = collect($this->block->query_fields);
        foreach ($this->parameter as $key => $value) {
            if ($value == '__ALL__' || $value === null) continue;
            $field = $key == $this->block->tab_key ? $this->block->fields->first(function ($item) use ($key) {
                return $item->index == $key;
            }) : $fields->first(function ($item) use ($key) {
                return $item->index == $key && ($item->filterable || $key == $this->block->tab_key);
            });
            if ($field) {
                if ($field->relation) {
                    $relation = explode('.', $field->relation);
                    if (count($relation) > 1) {
                        $index = array_pop($relation);
                        $model = implode($relation, '.');
                        $this->builder = $this->builder->whereHas($model, function ($q) use ($value, $field, $index) {
                            $field->where($q, $value, $index);
                        });
                    } else $this->builder = $field->where($this->builder, $value, $field->relation);
                } else   $this->builder = $field->where($this->builder, $value);
            }
            $this->builder = $this->builder->when($key == 'relation_uuid' && $this->block->relation_index, function ($query) use ($value) {
                $query->where($this->block->relation_index, $value);
            });

        }
        return $this;
    }


    protected function whitOutDelete()
    {
        if (parameter('__deleted', false)) $this->builder = $this->builder->onlyTrashed();
        return $this;
    }

    final protected function sortAndPage(): Fetch
    {
        $this->getDataCount();
        foreach ($this->sorting as $field => $order) {
            $this->builder = $this->builder->orderBy($field, $order);
        }
        $this->builder = $this->builder->orderBy('created_at', 'desc');
        $this->builder->when($this->pagination, function ($query) {
            $query->offset(($this->pagination['page'] - 1) * $this->pagination['size'])->limit($this->pagination['size']);
        });
        $this->builder = $this->getData();
        return $this;
    }


}
