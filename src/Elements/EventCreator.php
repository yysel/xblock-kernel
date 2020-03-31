<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 20-4-1
 * Time: 上午1:53
 */

namespace XBlock\Kernel\Elements;


use XBlock\Kernel\Blocks\Block;

class EventCreator
{
    protected $block;

    public function __construct(Block $block)
    {
        $this->block = $block;
    }

    public function make($index, $title = '')
    {
        return $this->create(Event::make($index, $title));
    }

    public function create(Event $event)
    {
        $event->permission($this->block->index . '@' . $event->index);
        $this->block->actions[] = $event;
        return $event;
    }
}