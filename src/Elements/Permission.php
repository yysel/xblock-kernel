<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-6
 * Time: 下午2:39
 */

namespace XBlock\Kernel\Elements;


use XBlock\Kernel\Exceptions\PermissionBindException;
use XBlock\Kernel\Exceptions\PermissionUseException;
use XBlock\Kernel\PermissionRegister;

class Permission
{
    protected $index;
    protected $title;
    protected $type;
    protected $description;
    protected $module = '默认';

    public function __construct($index = null, $title = null, $type = null, $module = null)
    {
        if ($index) $this->index = $index;
        if ($title) $this->title = $title;
        if ($type) $this->type = $type;
        if ($module) $this->module = $module;
    }

    static public function menu($index, $title, $module = null)
    {
        return new static($index, $title, 'menu', $module);
    }

    static public function filed($index, $title, $module = null)
    {
        return new static($index, $title, 'filed', $module);
    }

    static public function action($index, $title, $module = null)
    {
        return new static($index, $title, 'action', $module);
    }

    static public function flow($index, $title, $module = null)
    {
        return new static($index, $title, 'flow', $module);
    }

    static public function block($index, $title, $module = null)
    {
        return new static($index, $title, 'block', $module);
    }

    public function description($description)
    {
        $this->description = $description;
        return $this;
    }

    public function module($module)
    {
        $this->module = $module;
        return $this;
    }

    static public function use($index, $type = null)
    {
        if (env('APP_ENV') == 'production' || !$index) return $index;
        $permission = static::getPermissionList()->first(function ($item) use ($index) {
            return $item->index == $index;
        });
        if (!$permission) throw (new PermissionUseException($index));
        if ($type) $permission->checkTypeOrError($type);
        return $permission->index;
    }

    private function checkType($type)
    {
        return $type === $this->type;
    }

    public function checkTypeOrError($type)
    {
        if (!$this->checkType($type)) throw (new PermissionBindException($type, $this->title));
        return true;
    }

    static public function getPermissionList()
    {
        if (PermissionRegister::$lock !== true) {
            $register = config('xblock.register.permission', PermissionRegister::class);
            $register = new $register;
            $register = $register instanceof PermissionRegister ? $register : new PermissionRegister();
            $register->register();
            $register->kernelRegister();
            PermissionRegister::$lock = true;
        }
        return collect(PermissionRegister::$permission_list);
    }


}