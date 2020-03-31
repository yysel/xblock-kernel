<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-3
 * Time: 上午12:23
 */

namespace XBlock\Kernel\Elements\Actions;


use XBlock\Kernel\Elements\Element;

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


    protected function toJson(): array
    {
        return [
            'title' => $this->title,
            'index' => $this->index,
            'component' => $this->component,
            'permission' => $this->index,
            'color' => $this->color,
            'position' => $this->position,
            'icon_site' => $this->icon_site,
            'icon' => $this->icon,
            'link' => $this->link,
            'confirm' => $this->confirm,
            'visible' => $this->visible,
            'confirm_description' => $this->confirm_description,
        ];
    }


}