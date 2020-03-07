<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-3
 * Time: 下午5:29
 */

namespace XBlock\Kernel\Elements;


class Action extends Element
{
    protected $title;

    protected $index;

    protected $permission;

    protected $log_description;


    public function permission(Permission $permission)
    {
        $this->permission = Permission::use($permission,'action');
        return $this;
    }

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

    protected function toJson()
    {
        return [
            'title' => $this->title,
            'index' => $this->index,
            'permission' => $this->index
        ];
    }


}