<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-11-29
 * Time: 上午7:54
 */

namespace XBlock\Kernel\Elements\Fields;


use XBlock\Kernel\Elements\Element;

class BaseField extends Element
{
    protected $input = 'text';
    protected $dict = [];
    protected $relation;
    protected $visible = true;
    protected $addable = false;
    protected $editable = false;
    protected $filterable = false;
    protected $sortable = false;
    protected $exportable = true;
    protected $importable = true;
    protected $description;
    protected $require = false;
    protected $default;
    protected $value_type = 'normal';
    protected $fixed = false;
    protected $parent;
    protected $width = 0;
    protected $link;
    protected $render = 'text';
    protected $filter_position;
    protected $unit;
    protected $format_func;
    protected $property = [];


    public function invisible(): self
    {
        $this->visible = false;
        return $this;
    }

    public function visible($visible = true): self
    {
        $this->visible = $visible;
        return $this;
    }

    public function sortable($sortable = true): self
    {
        $this->sortable = $sortable;
        return $this;
    }

    public function filterable($position = 'top'): self
    {
        $this->filterable = true;
        $this->filter_position = in_array($position, ['top', 'header']) ? $position : 'top';
        return $this;
    }

    public function addable(): self
    {
        $this->addable = true;
        return $this;
    }

    public function require($require = true): self
    {
        $this->require = $require;
        return $this;
    }

    public function valueType($value_type): self
    {
        $this->value_type = $value_type;
        return $this;
    }

    public function default($default): self
    {
        $this->default = $default;
        return $this;
    }

    public function description($description): self
    {
        $this->description = $description;
        return $this;
    }

    public function editable(): self
    {
        $this->editable = true;
        return $this;
    }

    public function writable(): self
    {
        $this->editable = true;
        $this->addable = true;
        return $this;
    }

    public function disExportable(): self
    {
        $this->exportable = false;
        return $this;
    }

    public function disImportable(): self
    {
        $this->importable = false;
        return $this;
    }

    public function parent($parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    public function unit($unit): self
    {
        $this->unit = $unit;
        return $this;
    }

    public function render($render): self
    {
        $this->render = $render;
        return $this;
    }

    public function relation($relation): self
    {
        $this->relation = $relation;
        return $this;
    }

    public function link($link): self
    {
        $this->link = $link;
        return $this;
    }

    public function format(\Closure $func): self
    {
        $this->format_func = $func;
        return $this;
    }

    public function dict($dict = []): self
    {
        if (!$dict || is_string($dict)) {
            $method = $dict ? $dict : $this->index;
            $method = pascal($method);
            $object = app('field_dict');
            $this->dict = method_exists($object, $method) ? $object->$method() : [];
        } elseif (isOneArray($dict)) $this->dict = create_dict($dict);
        else $this->dict = $dict;

        return $this;
    }


    public function __call($method, $param): self
    {
        $this->input = $method;
        $this->dict = $param;
        return $this;
    }

    public function width($width)
    {
        $this->width = $width;
        return $this;
    }


    protected function toJson(): array
    {
        return [
            'title' => $this->title,
            'index' => $this->index,
            'input' => $this->input,
            'permission' => $this->index,
            'description' => $this->description,
            'dict' => $this->dict,
            'relation' => $this->relation,
            'visible' => $this->visible,
            'addable' => $this->addable,
            'editable' => $this->editable,
            'filterable' => $this->filterable,
            'filter_position' => $this->filter_position,
            'sortable' => $this->sortable,
            'exportable' => $this->exportable,
            'importable' => $this->importable,
            'require' => $this->require,
            'default' => $this->default,
            'value_type' => $this->value_type,
            'fixed' => $this->fixed,
            'parent' => $this->parent,
            'width' => $this->width,
            'link' => $this->link,
            'render' => $this->render,
            'unit' => $this->unit,
            'property' => $this->property
        ];
    }

    public function get($key)
    {
        return $this->{$key};
    }
}