<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 20-3-30
 * Time: 下午8:18
 */

namespace XBlock\Kernel\Events;


use XBlock\Helper\Response\ErrorCode;

class CallHook
{
    static public function call($key, &$model, $object)
    {
        $request = request();
        $register = config('xblock.register.hook', false);
        if ($register) {
            $register_object = new $register;
            $global_hook_res = $register_object->$key($model, $object->index, $request);
            if ($global_hook_res instanceof ErrorCode) return $global_hook_res;
        }
        if (method_exists($object, $key)) {
            return $object->$key($model, $request);
        }
        return true;
    }
}