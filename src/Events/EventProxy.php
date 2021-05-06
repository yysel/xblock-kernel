<?php


namespace XBlock\Kernel\Events;


use XBlock\Kernel\Blocks\Block;

class EventProxy
{
    /**
     * @var Block
     */
    protected $block;

    public function __construct(Block $block)
    {
        $this->block = $block;
    }
}