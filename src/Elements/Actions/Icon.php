<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-11-30
 * Time: 下午3:50
 */

namespace XBlock\Kernel\Elements\Actions;


use XBlock\Kernel\Elements\Button;

class Icon extends BaseAction
{
    protected $component = 'icon';
    protected $position = Button::INNER;
}