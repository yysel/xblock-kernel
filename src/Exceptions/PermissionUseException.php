<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 2017-06-14
 * Time: 22:26
 */

namespace XBlock\Kernel\Exceptions;

use Exception;

class PermissionUseException extends Exception
{
    function __construct($permission)
    {
        parent::__construct("权限【{$permission}】 没有注册，无法使用！");
    }
}