<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-11-30
 * Time: 下午3:46
 */

namespace XBlock\Kernel\Elements\Buttons;


use XBlock\Kernel\Elements\Button;

class Small extends BaseButton
{
    protected $component = 'small';

    protected $position = Button::INNER;

}