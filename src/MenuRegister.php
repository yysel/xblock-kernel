<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-13
 * Time: 下午2:22
 */

namespace XBlock\Kernel;


use XBlock\Kernel\Elements\Menu;

class MenuRegister
{
    static $menu = [];


    public function register()
    {

    }

    final public function kernelRegister()
    {

    }


    final  protected function make($index, $title)
    {
        $menu = new Menu($index, $title);
        $menu->path = '/' . trim($index, '/');
        static::$menu[$index] = &$menu;
        return $menu;
    }


}