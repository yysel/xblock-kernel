<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-13
 * Time: 下午10:09
 */

namespace XBlock\Kernel;


use XBlock\Kernel\Elements\Permission;

class PermissionRegister
{
    static $permission_list = [];
    static $lock;

    public function register()
    {

    }

    final public function kernelRegister()
    {

    }

    final public function menu($index, $type)
    {
        $permission = Permission::menu($index, $type);
        self::$permission_list[] = $permission;
        return $permission;
    }

    final public function block($index, $type)
    {
        $permission = Permission::block($index, $type);
        self::$permission_list[] = $permission;
        return $permission;
    }

    final public function filed($index, $type)
    {
        $permission = Permission::filed($index, $type);
        self::$permission_list[] = $permission;
        return $permission;
    }

    final public function action($index, $type)
    {
        $permission = Permission::action($index, $type);
        self::$permission_list[] = $permission;
        return $permission;
    }

    final public function flow($index, $type)
    {
        $permission = Permission::flow($index, $type);
        self::$permission_list[] = $permission;
        return $permission;
    }


}