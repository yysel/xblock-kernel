<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-11-24
 * Time: 下午8:12
 */

namespace XBlock\Kernel\Services;

use Illuminate\Support\Facades\Cache;
use XBlock\Kernel\Blocks\Block;
use XBlock\Kernel\Elements\Menu;
use XBlock\Kernel\Elements\Permission as KernelPermission;


class PermissionService
{
    protected $detail_menu_list = [];
    protected $menu_list = [];
    protected $block_list = [];
    protected $permission = [];

    public static function get()
    {
        return KernelPermission::getPermissionList();
    }

    public function getPermissionTree(\Closure $func)
    {
        $this->permission = env('APP_ENV') === 'production' ? Cache::remember('xblock_access_permission', 24 * 3600, function () {
            return $this->getPermissionList();
        }) : $this->getPermissionList();
        $this->permission = $func($this->permission);
        return $this->createPermissionTree();
    }

    protected function menuPermission($item)
    {
        $this->permission[$item->path] = [
            'text' => $item->title,
            'value' => $item->path,
            'type' => 'menu',
            'parent' => $item->parent ? '/' . trim($item->parent, '/') : null,
        ];
    }

    protected function blockPermission($blocks, $path)
    {
        if ($blocks) {
            foreach ($blocks as $index) {
                $class = $this->block_list[$index];
                $block = ($class && class_exists($class)) ? (new $class()) : null;
                if ($block instanceof Block) {
                    $this->permission[$index] = [
                        'text' => $block->title,
                        'value' => $index,
                        'type' => 'block',
                        'parent' => $path,
                    ];
                    $this->actionPermission($block, $index);
                }
            }
        }
    }

    protected function actionPermission(Block $block, $path)
    {
        if ($block->auth) {
            $this->permission[$path . '@list'] = [
                'text' => '查看',
                'value' => $path . '@list',
                'type' => 'action',
                'parent' => $path
            ];
        }
        $block->getEvents()->map(function ($item) use ($path) {
            $key = $path . "@{$item->index}";
            $this->permission[$key] = [
                'text' => $item->title,
                'value' => $key,
                'type' => 'action',
                'parent' => $path
            ];
        });
        $actions = $block->all_actions;
        $hasDelete = $actions->first(function ($item) {
            return $item->index == 'delete';
        });
        $actions->map(function ($item) use ($path, $hasDelete) {
            if (!$hasDelete && ($item->index == 'restore' || $item->index == 'force_delete')) return;
            $key = $path . "@{$item->index}";
            $this->permission[$key] = [
                'text' => $item->title,
                'value' => $item->permission?$item->permission: $key,
                'type' => 'action',
                'parent' => $path
            ];
        });
    }


    public function getBlockClassList()
    {
        $blocker = new BlockService();
        return $blocker->findBlockClassList();
    }

    public function getPermissionList()
    {
        $this->block_list = $this->getBlockClassList();
        Menu::getMenuList()->map(function (Menu $item) {
            if ($item->register) {
                if (strpos($item->path, '/:relation_uuid') === false) {
                    $this->menuPermission($item);
                }
                if ($item->block) $this->blockPermission($item->block, str_replace('/detail/:relation_uuid', '', $item->path), count((array)$item->block));
            }
        });
        return collect($this->permission);
    }

    public function createPermissionTree($parent = null)
    {
        return $this->permission->filter(function ($item) use ($parent) {
            return $parent == $item['parent'];
        })->map(function ($item) {
            $children = $this->createPermissionTree($item['value']);
            if ($children->count() == 1 && ($first = $children->first())['type'] == 'block') {
                $children = $first['children'];
            }
            return [
                'text' => $item['text'],
                'value' => $item['value'],
                'type' => $item['type'],
                'children' => $children
            ];
        })->values();
    }

}
