<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-3
 * Time: 下午5:29
 */

namespace XBlock\Kernel\Elements;


class Event extends Element
{
    protected $title;

    protected $index;

    protected $permission;

    protected $log_description;


    public function index($index)
    {
        $this->index = $index;
        return $this;
    }

    public function log($description = null)
    {
        $this->log = true;
        if ($description) $this->log_description = $description;
        else $this->log_description = $this->index;
        return $this;
    }

    public function permission($permission)
    {
        $this->permission = $permission;
        return $this;
    }

    protected function toJson()
    {
        return [
            'title' => $this->title,
            'index' => $this->index,
            'permission' => $this->index
        ];
    }


}