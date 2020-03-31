<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-11-30
 * Time: 下午3:46
 */

namespace XBlock\Kernel\Elements\Actions;


use XBlock\Kernel\Elements\Button;

class Link extends BaseAction
{
    protected $component = 'link';

    protected $position = Button::INNER;
}