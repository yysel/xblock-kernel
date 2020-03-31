<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 18-5-6
 * Time: 下午2:44
 */

namespace XBlock\Kernel\Fetch;


use Illuminate\Support\Collection;

class CollectionFetch extends Fetch
{


    public function getContent()
    {
        $this->requestWhere()
            ->callWhereHook('where')
            ->sortAndPage()
            ->formatContent();
        return $this->builder;
    }

    public function getBuilder()
    {
        $builder = $this->block->{$this->block->origin}();
        return $builder instanceof Collection ? $builder : collect($builder);
    }


    public function requestWhere()
    {
        $fields = collect($this->block->fields);
        foreach ($this->parameter as $key => $value) {
            if ($value == '__ALL__' || $value === null) continue;
            if ($key == $this->block->tab_key && in_array($this->block->tab_key, $this->block->where_except)) continue;
            if ($filed = $fields->first(function ($item) use ($key) {
                return $item->index == $key && $item->filterable;
            })) {
                $this->builder = $filed->whereCollection($this->builder, $value);
            }
        }
        return $this;
    }

    protected function sortAndPage(): Fetch
    {
        $this->getDataCount();
        if ($this->sorting) {
            foreach ($this->sorting as $field => $order) {
                $sortMethod = $order == 'asc' ? 'sortBy' : 'sortByDesc';
                $this->builder = $this->builder->$sortMethod($field);
            }
        }
        if ($this->pagination) {
            $this->builder = $this->builder->forPage($this->pagination['page'], $this->pagination['size']);
        }
        return  $this;
    }
}
