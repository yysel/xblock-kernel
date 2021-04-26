<?php


namespace XBlock\Kernel\Elements\Fields;


use XBlock\Kernel\Elements\Render;

class DateTime extends BaseField
{
    use WhereEqual;

    protected $input = 'datetime';
    protected $render = Render::TEXT;

    public function setTimeFormat($format): self
    {
        return $this->setProperty('time_format', $format);
    }
}
