<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 18-5-29
 * Time: 下午7:18
 */

namespace XBlock\Kernel\Models;


class MenuModel extends BlockBaseModel
{

    protected $table = 'system.menu';
    protected $guarded = [];
    protected $config = [];


    public function children()
    {
        return $this->hasMany('XBlock\Kernel\Models\MenuModel', 'p_uuid', 'uuid')->orderBy('is_detail', 'asc')->orderBy('sequence', 'asc');
    }

    public function father()
    {
        return $this->belongsTo('XBlock\Kernel\Models\MenuModel', 'p_uuid', 'uuid')->withDefault();
    }

    public function blocks()
    {
        return $this->hasMany('XBlock\Kernel\Models\MenuBlock', 'menu_uuid', 'uuid')->orderBy('sequence', 'asc');
    }


    public function getMenuTreeAttribute()
    {
        $data = new MenuItem([
            'uuid' => $this->uuid,
            'name' => $this->title,
            'icon' => $this->icon,
            'path' => $this->path,
            'display_type' => $this->display_type,
            'authority' => $this->permission,
            'is_detail' => (bool)$this->is_detail,
            'hideInMenu' => (boolean)!$this->is_visiable,
            'module' => $this->module,
            'block' => $this->block,
            'bottom_button' => $this->bottom_button,
            'children' => $this->children()->whereIn('permission', user('permission'))->get()->map(function ($item) {
                return $item->menu_tree;
            }),
        ]);
        if (request('dynamic', true)) {
            $config = config('menu', []);;
            if (isset($config[$this->index]) && $config[$this->index] instanceof \Closure) {
                $hook = \Closure::bind($config[$this->index], $data);
                $hook();
            }
        }
        return $data;
    }

    public function getBlockAttribute()
    {
        $indexs = $this->blocks->pluck('block_index');
        return $indexs ? $indexs : null;
    }

    public function getBlockIndexAttribute()
    {
        return $this->blocks->pluck('block_index')->toArray();
//        return BlockModel::whereIn('uuid', $block)->get(['uuid AS value', 'title as text','index']);
    }

    public function setFillable($fill)
    {
        $this->fillable = $fill;
        return $this;
    }

    public static function boot()
    {
        parent::boot();
        self::addGlobalScope('module', function ($query) {
            return $query->where(function ($q) {
                if (!request('no_module', false)) $q->whereIn('module', get_module())->orWhere('module', '');
            });
        });
        self::creating(function ($query) {
            $query->uuid = guid();
            $father = $query->father;
            $module = $query->module;
            $index = $query->path . '_menu';
            $title = $query->title;
            $query->index = $father->path . '/' . $query->path;
            if ($father->uuid) {
                $index = $father->path . '_' . $query->path . '_menu';
                $title = $father->title . '_' . $query->title;
            }
            $permission = Permission::create(compact('module', 'index', 'title'));
            if ($permission) $query->permission = $index;

            if (request()->has('block_index')) {
                $block = request('block_index', []);
                $query->saveMenuBlock($block);
                $block_index = implode(',', $block);
                $query->block_index = $block_index;
            }

        });
        self::updating(function ($query) {
            $father = $query->father;
            $query->index = $father->path . '/' . $query->path;
            $premission = Permission::where('index', $query->permission)->first();
            if ($premission) {
                $father = $query->father;
                $module = $query->module;
                $index = $query->path . '_menu';
                $title = $query->title;
                if ($father->uuid) {
                    $index = $father->path . '_' . $query->path . '_menu';
                    $title = $father->title . '_' . $query->title;
                }
                $premission->index = $index;
                $premission->title = $title;
                $premission->module = $module;
                $premission->save();
                $query->permission = $index;
            }

            if (request()->has('block_index')) {
                $block = request('block_index', []);
                $query->saveMenuBlock($block);
                $block_index = implode(',', $block);
                $query->block_index = $block_index;
            }
        });

        self::deleted(function ($query) {
            $permission = Permission::where('index', $query->permission)->first();
            if ($permission) {
                RolePermission::where('permission_uuid', $permission->uuid)->delete();
                $permission->delete();
            }
        });
    }

    public function saveMenuBlock($blocks)
    {
        MenuBlock::where(['menu_uuid' => $this->uuid,])->forceDelete();
        foreach ($blocks as $k => $block) {
            MenuBlock::create(['menu_uuid' => $this->uuid, 'block_index' => $block, 'sequence' => $k]);
        }
    }

}