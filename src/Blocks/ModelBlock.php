<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-2
 * Time: 下午4:32
 */

namespace XBlock\Kernel\Blocks;


use XBlock\Kernel\Events\ModelDefaultEvent;

class ModelBlock extends Block
{
    public $origin_type = 'model';

    public $origin = 'content';

    use ModelDefaultEvent;


}