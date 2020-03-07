<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-11-30
 * Time: 下午3:50
 */

namespace XBlock\Kernel\Elements\Buttons;


use XBlock\Kernel\Elements\Button;

class SolidIcon extends BaseButton
{
    protected $component = 'solid_icon';
    protected $position = Button::INNER;
}