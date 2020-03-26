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
        $this->permission = Permission::use($permission, 'menu');
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
                $block_array[] = $this->getBlockName($v);
            }
        } else {
            $block_array[] = $this->getBlockName($block);
        }
        $this->block = $block_array;
        return $this;
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

    public function children($func)
    {
        $func($this);
        $this->block = [];
        return $this;
    }


    public function getChildren($list, $auth = false)
    {
        return $list->filter(function ($item) {
            return $item->parent === $this->index;
        })->map(function (Menu $item) use ($list, $auth) {
            return [
                'path' => $item->path,
                'title' => $item->title,
                'permission' => $item->getPermission(),
                'visible' => $item->visible,
                'icon' => $item->icon,
                'block' => (user('is_admin') || !$auth) ? $item->block : array_filter((array)$item->block, function ($it) use ($item) {
                    return in_array(str_replace('/detail/:relation_uuid', '', $item->path) . '-' . $it . '-list', user('permission', []));
                }),
                'children' => $item->getChildren($list, $auth),
            ];
        })->values();

    }

    public function __get($key)
    {
        return $this->{$key};
    }

    public static function getMenuTree($auth = false): Collection
    {
        $menu_list = self::getMenuList();
        $menu = $menu_list->filter(function ($item) {
            return !$item->parent;
        })->map(function (Menu $item) use ($menu_list, $auth) {
            return [
                'path' => $item->path,
                'title' => $item->title,
                'permission' => $item->getPermission(),
                'visible' => $item->visible,
                'icon' => $item->icon,
                'block' => (user('is_admin') || !$auth) ? $item->block : array_filter((array)$item->block, function ($it) use ($item) {
                    return in_array(str_replace('/detail/:relation_uuid', '', $item->path) . '-' . $it . '-list', user('permission', []));
                }),
                'children' => $item->getChildren($menu_list, $auth),
            ];
        })->values();
        return $menu;
    }

    public static function getMenuList(): Collection
    {
        $register = config('xblock.register.menu', MenuRegister::class);
        $register = new $register;
        $register = $register instanceof MenuRegister ? $register : new MenuRegister();
        $register->register();
        $register->kernelRegister();
        return collect(MenuRegister::$menu);
    }

    protected function getPermission()
    {
        $permission = $this->permission ? $this->permission : str_replace('/detail/:relation_uuid', '', $this->path);
        if (user('is_admin')) return null;
        return $permission;
    }

}