<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 2017-06-14
 * Time: 22:26
 */

namespace XBlock\Kernel\Exceptions;

use Exception;

class NoAuthException extends Exception
{
    function __construct($msg = '没有足够的访问权限！')
    {
        parent::__construct($msg);
    }
}