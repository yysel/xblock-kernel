<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 20-4-1
 * Time: 上午1:38
 */

namespace XBlock\Kernel\Elements;


use XBlock\Kernel\Blocks\Block;
use XBlock\Kernel\Elements\Actions\BaseAction;

class ActionCreator
{
    protected $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function default($index, $title = null)
    {
        return $this->create(Action::default($index, $title));
    }

    public function large($index, $title = null)
    {
        return $this->create(Action::large($index, $title));
    }

    public function link($index, $title = null)
    {
        return $this->create(Action::link($index, $title));
    }


    public function small($index, $title = null)
    {
        return $this->create(Action::small($index, $title));
    }

    public function icon($index, $title = null, $icon = null)
    {
        return $this->create(Action::icon($index, $title, $icon));
    }

    public function filledIcon($index, $title = null, $icon = null)
    {
        return $this->create(Action::filledIcon($index, $title, $icon));
    }

    public function switch($index, $title = '')
    {
        return $this->create(Action::switch($index, $title));
    }

    public function switchIcon($index, $title = '')
    {
        return $this->create(Action::switchIcon($index, $title));
    }


    public function add($component = 'large')
    {
        return $this->create(Action::add($component));
    }

    public function delete($component = 'small')
    {
        return $this->create(Action::delete($component));
    }

    public function batchDelete($component = 'large')
    {
        return $this->create(Action::batchDelete($component));
    }

    public function edit($component = 'small')
    {
        return $this->create(Action::edit($component));
    }

    public function export($component = 'large')
    {
        return $this->create(Action::export($component));
    }

    public function import($component = 'large')
    {
        return $this->create(Action::import($component));
    }

    public function create(BaseAction $action)
    {
        $action->permission($this->payload->index . '@' . $action->index);
        $this->payload->actions[] = $action;
        return $action;
    }
}
