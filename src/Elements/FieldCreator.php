<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 20-4-1
 * Time: 上午12:54
 */

namespace XBlock\Kernel\Elements;


use XBlock\Kernel\Elements\Fields\BaseField;
use XBlock\Kernel\Elements\Fields\DateRange;
use XBlock\Kernel\Elements\Fields\DateTime;

class FieldCreator
{
    protected $payload;
    protected $group_name = null;
    protected $default = [];

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function key(...$key)
    {
        return $this->create(Field::key(...$key));
    }

    public function uuid()
    {
        return $this->create(Field::uuid());
    }

    public function text($index, $title = null)
    {
        return $this->create(Field::text($index, $title));
    }

    public function password($index, $title = null)
    {
        return $this->create(Field::password($index, $title));
    }


    public function textArea($index, $title = null)
    {
        return $this->create(Field::textArea($index, $title));
    }

    public function editor($index, $title = null)
    {
        return $this->create(Field::editor($index, $title));
    }

    public function radio($index, $title = null)
    {
        return $this->create(Field::radio($index, $title));
    }

    public function select($index, $title = null)
    {
        return $this->create(Field::select($index, $title));
    }

    public function selectMulti($index, $title = null)
    {
        return $this->create(Field::selectMulti($index, $title));
    }

    public function date($index, $title = null)
    {
        return $this->create(Field::date($index, $title));
    }

    public function datetime($index, $title = null): DateTime
    {
        return $this->create(Field::datetime($index, $title));

    }

    public function dateRange($index, $title = null): DateRange
    {
        return $this->create(Field::dateRange($index, $title));

    }

    public function month($index, $title = null)
    {
        return $this->create(Field::month($index, $title));
    }

    public function switch($index, $title = null)
    {
        return $this->create(Field::switch($index, $title));
    }


    public function checkbox($index, $title = null)
    {
        return $this->create(Field::checkbox($index, $title));
    }

    public function upload($index, $title = null)
    {
        return $this->create(Field::upload($index, $title));
    }

    public function cascadeRadio($index, $title = null)
    {
        return $this->create(Field::cascadeRadio($index, $title));
    }

    public function cascadeCheckbox($index, $title = null)
    {
        return $this->create(Field::cascadeCheckbox($index, $title));
    }

    public function cascadeCheckboxAllNode($index, $title = null)
    {
        return $this->create(Field::cascadeCheckboxAllNode($index, $title));
    }


    public function create(BaseField $field)
    {
        if ($this->default) {
            foreach ($this->default as $key => $value) {
                $field->{$key}($value);
            }
        }
        if ($this->group_name) $field->group($this->group_name);
        $this->payload->fields[] = $field;
        return $field;
    }

    public function group($name, $func)
    {
        $this->group_name = $name;
        $func($this);
        $this->group_name = null;
        return $this;
    }

    public function setDefault($method, $value)
    {
        $this->default[$method] = $value;
    }
}
