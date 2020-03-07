<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-11-30
 * Time: 下午3:46
 */

namespace XBlock\Kernel\Elements\Buttons;


use XBlock\Kernel\Elements\Button;

class Link extends BaseButton
{
    protected $component = 'link';

    protected $position = Button::INNER;
}