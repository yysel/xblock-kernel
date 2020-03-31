<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 20-3-25
 * Time: 下午6:17
 */

namespace XBlock\Kernel\Services;


class RunTimeService
{
    static public function openProvider($provider)
    {
        $ser = new $provider(app());
        $ser->boot();
        $ser->register();
    }

    static public function getRoleModel($new = false)
    {
        $model = config('xblock.access.role', 'XBlock\Access\Models\Role');
        return $new ? new $model : $model;
    }
}