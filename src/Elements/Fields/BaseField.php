<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-11-29
 * Time: 上午7:54
 */

namespace XBlock\Kernel\Elements\Fields;


use phpDocumentor\Reflection\Types\Boolean;
use XBlock\Helper\Tool;
use XBlock\Kernel\Elements\Element;

class BaseField extends Element
{
    static $condition = true;
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
    protected $group = null;
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
    protected $attributes = [
        'title', 'index', 'input', 'description', 'dict', 'relation',
        'visible', 'addable', 'editable', 'filterable', 'filter_position', 'sortable',
        'exportable', 'importable', 'require', 'default', 'value_type', 'fixed',
        'parent', 'width', 'link', 'render', 'unit', 'property', 'group'
    ];

    public function if(\Closure $func)
    {
        if (static::$condition) {
            $func($this);
        }
        return $this;
    }

    public function title($title): self
    {
        $this->title = $title;
        return $this;
    }

    public function index($index): self
    {
        $this->index = $index;
        return $this;
    }

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

    public function filterable($filterable = true): self
    {
        $this->filterable = $filterable;
        $this->filter_position = 'top';
        return $this;
    }

    public function addable($addable = true): self
    {
        $this->addable = $addable;
        return $this;
    }

    public function editable($editable = true): self
    {
        $this->editable = $editable;
        return $this;
    }

    public function writable($writable = true): self
    {
        $this->editable = $writable;
        $this->addable = $writable;
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

    public function group($group): self
    {
        $this->group = $group;
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
            $method = Tool::pascal($method);
            $object = app('field_dict');
            $this->dict = method_exists($object, $method) ? $object->$method() : [];
        } elseif (Tool::isOneArray($dict)) $this->dict = create_dict($dict);
        else $this->dict = $dict;

        return $this;
    }


//    public function __call($method, $param): self
//    {
//        $this->input = $method;
//        $this->dict = $param;
//        return $this;
//    }

    public function width($width)
    {
        $this->width = $width;
        return $this;
    }

    public function setProperty($key, $value)
    {
        $this->property[$key] = $value;
        return $this;
    }


    public function get($key)
    {
        return $this->{$key};
    }
}
