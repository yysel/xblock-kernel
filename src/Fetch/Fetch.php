<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 18-5-4
 * Time: 下午5:30
 */

namespace XBlock\Kernel\Fetch;

use \Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use XBlock\Kernel\Exceptions\HookException;

abstract class Fetch
{
    public $block;

    protected $builder;

    public $parameter;

    public $sorting = [];

    public $pagination;

    protected $request;

    protected $data_count;


    public function __construct()
    {
        $this->request = app('request');
    }

    /*×暴露在外部返回格式化以后的数据
     *@return Array
     */
    final public function init()
    {
        $this->builder = $this->getBuilder();
        $this->initParameter()->initSorting()->initPagination()->getContent();
        return $this->builder->values();
    }

    /**通过连续调用本类中的方法来获取数据
     * @return $this;
     */
    abstract protected function getContent();


    /**获取
     * @return $this;
     */
    abstract public function getBuilder();


    abstract protected function requestWhere();


    final protected function initParameter()
    {
        $except = $this->block->where_except;
        $parameter = $this->request->input('parameter', []);
        if ((array)$except) {
            foreach ($except as $param) {
                if (isset($parameter[$param])) unset($parameter[$param]);
            }
        }
        $this->parameter = $parameter;
        return $this->tabBlockParameter();
    }

    final protected function initSorting()
    {
        $sortable = $this->request->has('sortable') ? $this->request->input('sortable') : $this->block->sortable;
        $sorts = $this->request->input('sorting', []);
        if ($sortable && $sorts) {

            foreach ($sorts as $field => $order) {
                if ($this->block->fields->first(function ($item) use ($field) {
                    return $item->sortable && $item->index === $field;
                })) $this->sorting[$field] = in_array($order, ['desc', 'asc']) ? $order : 'asc';
            }
        }
        return $this;
    }

    final protected function initPagination()
    {
        $pageable = $this->request->has('pageable') ? $this->request->input('pageable') : $this->block->pageable;
        if ($pageable) {
            $pagination = $this->request->input('pagination', []);
            $this->pagination = [
                'page' => empty($pagination['page']) ? 1 : $pagination['page'],
                'size' => empty($pagination['size']) ? 10 : $pagination['size']
            ];
        }
        return $this;
    }

    private function tabBlockParameter()
    {
        $tab_key = $this->block->tab_key;
        $default_value = parameter($tab_key, null);
        if ($tab_key) {
            if ($default_value === null) {
                $field = $this->block->fields->first(function ($item) use ($tab_key) {
                    return $item->index == $tab_key;
                });
                if ($field && $field->dict) {
                    $first_item = $field->dict[0];
                    $default_value = isset($first_item['value']) ? $first_item['value'] : null;
                }
            }
            $this->parameter[$tab_key] = $default_value;
        }
        return $this;
    }

    abstract protected function sortAndPage(): self;

    /**利用表头格式化数据
     * @return $this;
     */
    final protected function formatContent()
    {
        if ($this->request->has('transform')) $transform = $this->request->input('transform', null);
        else $transform = $this->block->transform;
        $this->builder = $this->builder->map(function ($item) use ($transform) {
            if (is_array($item)) $item = (object)$item;
            if ($this->block->fields) {
                $temp_restult = [];
                foreach ($this->block->fields as $field) {
                    $index = $field->index;
                    $value = isset($item->{$index}) ? $item->{$index} : null;
                    if ($field->relation) $value = $this->getRelationModelValue($item, $field->relation, $value);
                    $method = $field->format_func;
                    if ($method instanceof \Closure) {
                        $value = $method($value, $item, $this->block);
                    } else {
                        if (($transform && $field->dict)) {
                            if (is_array($value)) {
                                $value = collect($field->dict)->whereIn('value', $value)->implode('text', '、');
                            } else {
                                $dict = collect($field->dict)->first(function ($it) use ($item, $index, $value) {
                                    return ((object)$it)->value == $value;
                                });
                                if ($dict) {
                                    if ($transform === 'dict') $value = $dict;
                                    else $value = is_array($dict) ? $dict['text'] : $dict->text;
                                } else {
                                    if ($transform === 'dict') $value = ['text' => null, 'value' => $value];
                                }
                            }

                        }
                        if ($value instanceof Carbon) $value = $value->format('Y-m-d H:i:s');
                    }
                    $temp_restult[$index] = $value;
                }
                return $temp_restult;
            }
            return $item;
        });

        return $this;
    }

    final protected function getRelationModelValue($data, $realkey, $default)
    {
        $keys = explode('.', $realkey);
        try {
            foreach ($keys as $value) {
                $data = $data->{$value};
            }
        } catch (\Exception $exception) {
            $data = $default;
        }
        return $data;
    }


    final protected function getDataCount()
    {
        if (is_array($this->builder)) $this->data_count = count($this->builder);
        else  $this->data_count = $this->builder->count();
        return $this;
    }


    final public function getPagination()
    {
        $pagination = $this->pagination;
        if (!$pagination) return false;
        $pagination['total'] = $this->data_count;
        return $pagination;
    }

    final protected function callWhereHook($hook_name)
    {
        $has_hook = false;
        if (method_exists($this->block, $hook_name)) {
            if ($hook_name == 'afterWhere') $this->builder = $this->getData();
            $this->builder = $this->block->$hook_name($this->builder);
            $has_hook = true;
        }
        if (!$this->builder && $has_hook) throw new HookException("钩子函数【{$hook_name}】没有返回值或返回值为空！（应返回一个构造器或者集合）");
        return $this;
    }


    final protected function getData()
    {
        if ($this->builder instanceof Builder) {
            $this->queryList = $this->builder->getQuery()->wheres;
            return $this->builder->cursor();
        }
        return $this->builder->get();
    }


}