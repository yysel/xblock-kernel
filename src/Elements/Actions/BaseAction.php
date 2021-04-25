<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-3
 * Time: 上午12:23
 */

namespace XBlock\Kernel\Elements\Actions;


use XBlock\Kernel\Elements\Element;
use XBlock\Kernel\Elements\Form;

class BaseAction extends Element
{
    protected $index;

    protected $title;

    protected $component;

    protected $link;

    protected $icon;

    protected $icon_site;

    public $permission;

    protected $position = 'top';

    protected $color;

    protected $confirm = false;

    protected $confirm_description = null;

    protected $visible = true;

    protected $form = null;

    protected $attributes = [
        'title', 'index', 'component', 'permission', 'color', 'position',
        'icon_site', 'icon', 'link', 'confirm', 'visible', 'confirm_description', 'form'
    ];

    public function component($component): BaseAction
    {
        $this->component = $component;
        return $this;
    }

    public function link($link): BaseAction
    {
        $this->link = $link;
        return $this;
    }

    public function icon($icon): BaseAction
    {
        $this->icon = $icon;
        return $this;
    }

    public function permission($permission): BaseAction
    {
        $this->permission = $permission;
        return $this;
    }

    public function position($position): BaseAction
    {
        $this->position = $position;
        return $this;
    }

    public function inner(): BaseAction
    {
        $this->position = 'inner';
        return $this;
    }

    public function top(): BaseAction
    {
        $this->position = 'top';
        return $this;
    }

    public function color($color): BaseAction
    {
        $this->color = $color;
        return $this;
    }

    public function visible($visible = true): BaseAction
    {
        $this->visible = $visible;
        return $this;
    }

    public function confirm($description = null, $confirm = true): BaseAction
    {
        $this->confirm = $confirm;
        $this->confirm_description = $description;
        return $this;
    }

    //构造表单
    public function form($fields = []): BaseAction
    {
        $form = Form::make();
        if ($fields instanceof \Closure) {
            $fields($form);
        } else {
            $form->fields($fields);
        }
        $this->form = $form;
        return $this;
    }

}
