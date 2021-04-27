<?php


namespace XBlock\Kernel\Elements\Fields;


use XBlock\Kernel\Elements\Render;

class DateRange extends BaseField
{
    use WhereDateBetween;

    protected $input = 'daterange';
    protected $render = Render::TEXT;

    public function setTimeFormat($format): self
    {
        return $this->setProperty('time_format', $format);
    }
}
