<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-2
 * Time: 下午4:32
 */

namespace XBlock\Kernel\Blocks;


use XBlock\Kernel\Fetch\CollectionFetch;
use XBlock\Kernel\Fetch\Fetch;

class CollectionBlock extends Block
{
    public $origin = 'content';

    public function getFetch(): Fetch
    {
        return new CollectionFetch($this);
    }

}