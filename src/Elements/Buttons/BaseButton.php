<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-3
 * Time: 上午12:23
 */

namespace XBlock\Kernel\Elements\Buttons;


use XBlock\Kernel\Elements\Element;

class BaseButton extends Element
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


    public function component($type): BaseButton
    {
        $this->component = $type;
        return $this;
    }

    public function link($link): BaseButton
    {
        $this->link = $link;
        return $this;
    }

    public function icon($icon): BaseButton
    {
        $this->icon = $icon;
        return $this;
    }

    public function permission($permission): BaseButton
    {
        $this->permission = $permission;
        return $this;
    }

    public function position($position): BaseButton
    {
        $this->position = $position;
        return $this;
    }

    public function inner(): BaseButton
    {
        $this->position = 'inner';
        return $this;
    }

    public function top(): BaseButton
    {
        $this->position = 'top';
        return $this;
    }

    public function color($color): BaseButton
    {
        $this->color = $color;
        return $this;
    }

    public function visible($visible): BaseButton
    {
        $this->visible = $visible;
        return $this;
    }

    public function confirm($description = null, $confirm = true): BaseButton
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