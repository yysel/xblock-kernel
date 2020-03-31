<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-11-24
 * Time: 下午8:12
 */

namespace XBlock\Kernel\Services;

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

    public function getTree($uuid)
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
        $this->permission = collect($this->permission);
        $role = RunTimeService::getRoleModel(true)->where('id', $uuid)->first();
        if ($role) {
            $permission = $role->permission();
            $this->permission = collect($this->permission)->filter(function ($item) use ($permission) {
                return in_array($item['value'], $permission) || $item['type'] == 'block';
            });
        } elseif (!user('is_admin')) {
            $permission = user('permission', []);
            $this->permission = collect($this->permission)->filter(function ($item) use ($permission) {
                return in_array($item['value'], $permission) || $item['type'] == 'block';
            });
        }
        return $this->getPermissionTree();
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
        $block->getActionWithPermission()->map(function ($item) use (&$permission_list, $path) {
            $key = $path . "@{$item->index}";
            $this->permission[$key] = [
                'text' => $item->title,
                'value' => $key,
                'type' => 'action',
                'parent' => $path
            ];
        });
        $button_permission = $block->getButtonWithPermission();
        $button_permission->map(function ($item) use (&$permission_list, $button_permission, $path) {
            $hasDelete = $button_permission->first(function ($item) {
                return $item->index == 'delete';
            });
            if (!$hasDelete && ($item->index == 'restore' || $item->index == 'force_delete')) return;
            $key = $path . "@{$item->index}";
            $this->permission[$key] = [
                'text' => $item->title,
                'value' => $key,
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

    public function getPermissionTree($parent = null)
    {
        return $this->permission->filter(function ($item) use ($parent) {
            return $parent == $item['parent'];
        })->map(function ($item) {
            $children = $this->getPermissionTree($item['value']);
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