<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-11-30
 * Time: 下午3:53
 */

namespace XBlock\Kernel\Elements\Actions;


use XBlock\Kernel\Elements\Button;

class SwitchButton extends BaseAction
{
    protected $component = 'switch';
    protected $position = Button::INNER;
}