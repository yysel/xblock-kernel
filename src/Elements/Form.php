<?php


namespace XBlock\Kernel\Elements;


use XBlock\Kernel\Elements\Fields\BaseField;

class Form extends Element
{
    //字段
    public $fields = [];
    protected $confirm_title = '确认';
    protected $cancel_title = '取消';
    /**
     * @var FieldCreator
     */
    public $field_creator = null;
    /**
     * @var ActionCreator
     */
    public $action_creator = null;
    public $actions = [];
    protected $info = null;
    protected $info_title = null;
    protected $info_type = null;
    protected $info_position = 'bottom';
    protected $attributes = ['fields', 'title', 'confirm_title', 'cancel_title', 'info', 'actions', 'info_title', 'info_type', 'info_position'];

    public function __construct()
    {
        parent::__construct();
        $this->field_creator = new  FieldCreator($this);
        $this->action_creator = new  ActionCreator($this);
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

    public function info($title, $message = null, $type = null): self
    {
        $this->info_title = $title;
        $this->info_type = $type;
        $this->info = $message;
        return $this;
    }

    public function infoPosition($position)
    {
        $position = $position === 'top' ? $position : 'bottom';
        $this->info_position = $position;
        return $this;
    }

    public function infoType($type)
    {
        $this->info_type = $type;
        return $this;
    }

    public static function response(\Closure $creator): self
    {
        return $creator(self::make());
    }

    public function handle($handle)
    {
        $action = request('form_action');
        if (!$action) return message(true)->data($this)->type('form');
        else {
            if (!is_array($handle)) return $handle();
            if (is_array($handle) && isset($handle[$action])) return $handle[$action]();
        }

    }


    //表单确认按钮的显示文字
    public function confirmTitle($confirm_title): self
    {
        $this->confirm_title = $confirm_title;
        return $this;
    }

    //表单取消按钮的显示文字
    public function cancelTitle($cancel_title): self
    {
        $this->cancel_title = $cancel_title;
        return $this;
    }

    //表单的宽度
    public function width($width): self
    {
        $this->width = $width;
        return $this;
    }

    //模态框表单显示标题
    public function title($title = ''): self
    {
        $this->title = $title;
        return $this;
    }
}
