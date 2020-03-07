<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 2017-06-14
 * Time: 22:26
 */

namespace XBlock\Kernel\Exceptions;

use Exception;

class PermissionBindException extends Exception
{
    function __construct($type, $permission)
    {
        parent::__construct("权限【{$permission}】将被绑定到一个【{$type}】元素上，但它的类型并不属于【{$type}】");
    }
}