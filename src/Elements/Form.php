<?php


namespace XBlock\Kernel\Elements;


use XBlock\Kernel\Elements\Fields\BaseField;

class Form extends Element
{
    //字段
    public $fields = [];
    protected $confirm_title = '确认';
    protected $cancel_title = '取消';
    public $field_creator = null;
    protected $attributes = ['fields', 'title', 'confirm_title', 'cancel_title'];


    public function __construct()
    {
        $this->field_creator = new  FieldCreator($this);
        $this->field_creator->setDefault('writable', true);
    }

    public function field(BaseField $field): self
    {
        $field->setAttribute([
            'title', 'index', 'input', 'description', 'dict', 'relation',
            'visible', 'require', 'default', 'value_type',
            'parent', 'width', 'link', 'property',
        ]);
        $this->fields[] = $field;

        return $this;
    }

    public function fields($fields): self
    {
        foreach ($fields as $field) {
            if ($field instanceof BaseField) {
                $field->setAttribute([
                    'title', 'index', 'input', 'description', 'dict', 'relation',
                    'visible', 'require', 'default', 'value_type',
                    'parent', 'width', 'link', 'property',
                ]);
                $this->fields[] = $fields;
            }

        }

        return $this;
    }


    public function confirmTitle($confirm_title): self
    {
        $this->confirm_title = $confirm_title;
        return $this;
    }

    public function cancelTitle($cancel_title): self
    {
        $this->cancel_title = $cancel_title;
        return $this;
    }

    public function width($width): self
    {
        $this->width = $width;
        return $this;
    }

    public function title($title = ''): self
    {
        $this->title = $title;
        return $this;
    }
}
