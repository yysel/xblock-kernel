<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-2
 * Time: 下午4:32
 */

namespace XBlock\Kernel\Blocks;


use XBlock\Kernel\Events\ModelDefaultEvent;
use XBlock\Kernel\Fetch\Fetch;
use XBlock\Kernel\Fetch\ModelFetch;

class ModelBlock extends Block
{

    public $origin = 'content';

    use ModelDefaultEvent;

    protected function getFetch(): Fetch
    {
        return new  ModelFetch($this);
    }

}