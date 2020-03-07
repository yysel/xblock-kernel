<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-11-29
 * Time: 上午8:16
 */

namespace XBlock\Kernel\Elements\Fields;


use XBlock\Kernel\Elements\Render;

class Date extends BaseField
{
    use WhereDate;
    protected $input = 'date';
    protected $render = Render::TEXT;
}