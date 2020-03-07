<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-13
 * Time: 下午7:25
 */

namespace XBlock\Kernel;

use XBlock\Kernel\Elements\Menu;

class KernelController
{
    public function menu()
    {
        $menu = Menu::getMenuTree(true);
        return message(true)->data($menu);
    }

    public function notification()
    {
        return message(true)->data([]);
    }
}