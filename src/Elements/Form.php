<?php


namespace XBlock\Kernel\Elements;


use XBlock\Kernel\Elements\Fields\BaseField;

class Form extends Element
{
    //字段
    protected $fields = [];
    protected $confirm_title = '确认';
    protected $cancel_title = '取消';
    protected $attributes = ['fields', 'title', 'confirm_title', 'cancel_title'];

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


    public function confirm_title($confirm_title): self
    {
        $this->confirm_title = $confirm_title;
        return $this;
    }

    public function cancel_title($cancel_title): self
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
