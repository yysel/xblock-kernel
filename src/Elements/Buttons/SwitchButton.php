<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-11-30
 * Time: 下午3:53
 */

namespace XBlock\Kernel\Elements\Buttons;


use XBlock\Kernel\Elements\Button;

class SwitchButton extends BaseButton
{
    protected $component = 'switch';
    protected $position = Button::INNER;
}