<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-11-29
 * Time: 上午7:58
 */

namespace XBlock\Kernel\Elements\Fields;


use XBlock\Kernel\Elements\Render;

class Password extends BaseField
{
    use WhereEqual;
    protected $input = 'password';
    protected $render = Render::TEXT;

}