<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 2017-06-14
 * Time: 22:26
 */

namespace XBlock\Kernel\Exceptions;

use Exception;

class HookException extends Exception
{
    function __construct($msg = '注册钩子函数异常！')
    {
        parent::__construct($msg);
    }
}