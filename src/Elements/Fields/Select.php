<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-11-29
 * Time: 上午8:16
 */

namespace XBlock\Kernel\Elements\Fields;


use XBlock\Kernel\Elements\Render;

class Select extends BaseField
{
    use WhereEqual;
    protected $input = 'select';
    protected $render = Render::TEXT;

    public function customAble(): self
    {
        $this->property['custom'] = true;
        return $this;
    }
}