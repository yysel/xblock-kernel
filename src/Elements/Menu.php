<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-6
 * Time: 下午1:45
 */

namespace XBlock\Kernel\Elements;

use Illuminate\Support\Collection;
use XBlock\Helper\Tool;
use XBlock\Kernel\MenuRegister;

class Menu
{

    protected $index;
    protected $title;
    protected $permission;
    protected $icon;
    protected $parent = null;
    protected $visible = true;
    protected $block = [];
    protected $register = true;
    public $path = '/';
    public $check_auth = false;

    public function __construct($index = '', $title = '', $block = null)
    {
        if ($index) $this->index = $index;
        if ($title) $this->title = $title;
        if ($block) $this->block = $block;
    }

    public function make($index, $title, $block = null)
    {
        $menu = new static($index, $title, $block);
        if ($this->index) {
            $menu->parent = $this->index;
            $menu->path = $this->path . '/' . trim($index, '/');
        }
        MenuRegister::$menu[] = &$menu;
        return $menu;
    }

    public function hasDetail($block = null, $props = [])
    {
        $this->make('detail/:relation_uuid', $this->title . '-详情页')
            ->disable()
            ->parent($this->parent)
            ->block($block)
            ->permission($this->permission)->props($props);
        return $this;
    }

    public function props($props)
    {
        foreach ($props as $key => $value) {
            $this->{$key} = $value;
        }
        return $this;
    }

    public function permission($permission)
    {
        $this->permission = $permission;
        return $this;
    }

    public function icon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    public function disable()
    {
        $this->visible = false;
        $this->permission = null;
        return $this;
    }

    public function parent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    public function disRegister()
    {
        $this->register = false;
        return $this;
    }

    public function block($block)
    {
        $block_array = [];
        if (is_array($block)) {
            foreach ($block as $v) {
                $block_name = $this->checkPermission($v);
                if ($block_name) $block_array[] = $block_name;
            }
        } else {
            $block_name = $this->checkPermission($block);
            if ($block_name) $block_array[] = $block_name;
        }
        $this->block = $block_array;
        return $this;
    }

    private function checkPermission($block)
    {
        $name = $this->getBlockName($block);
        if ((user('is_admin') || !MenuRegister::$check_auth)) return $name;
        else if (in_array($block::getPermission(), user('permission', []))) return $name;
    }

    private function getBlockName($block)
    {
        if (class_exists($block)) {
            $explode = explode('\\', $block);
            $name = end($explode);
            $name = Tool::unpascal($name);
            return $name;
        };
        throw new \Exception($block . '不存在');

    }

    public function children(\Closure $func)
    {
        $func($this);
        $this->block = [];
        return $this;
    }


    public function getChildren($list)
    {
        return $list->filter(function ($item) {
            return $item->parent === $this->index;
        })->map(function (Menu $item) use ($list) {
            return [
                'path' => $item->path,
                'title' => $item->title,
                'permission' => $item->getPermission(),
                'visible' => $item->visible,
                'icon' => $item->icon,
                'block' => $item->block,
                'children' => $item->getChildren($list),
            ];
        })->values();

    }

    public function __get($key)
    {
        return $this->{$key};
    }

    public static function getMenuTree($auth = false): Collection
    {
        $menu_list = self::getMenuList($auth);
        $menu = $menu_list->filter(function ($item) {
            return !$item->parent;
        })->map(function (Menu $item) use ($menu_list, $auth) {
            return [
                'path' => $item->path,
                'title' => $item->title,
                'permission' => $item->getPermission(),
                'visible' => $item->visible,
                'icon' => $item->icon,
                'block' => $item->block,
                'children' => $item->getChildren($menu_list),
            ];
        })->values();
        return $menu;
    }

    public static function getMenuList($auth = false): Collection
    {
        $register = config('xblock.register.menu', MenuRegister::class);
        $register = new $register;
        $register = $register instanceof MenuRegister ? $register : new MenuRegister();
        MenuRegister::$check_auth = $auth;
        $register->register();
        $register->kernelRegister();
        return collect(MenuRegister::$menu);
    }

    protected function getPermission()
    {
        $permission = $this->permission ? $this->permission : str_replace('/detail/:relation_uuid', '', $this->path);
        if (!$this->visible) $permission = false;
        if (user('is_admin')) return null;
        return $permission;
    }

}
