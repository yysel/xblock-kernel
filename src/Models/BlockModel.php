<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 18-5-4
 * Time: 下午4:52
 */

namespace XBlock\Kernel\Models;


use  \DB;
use \Closure;
use XBlock\Kernel\Elements\Button;
use XBlock\Kernel\Elements\Fields\BaseField;

class BlockModel extends BlockBaseModel
{
    protected $table = 'system_block';

    protected $header_list = [];

    protected $content_list = [];

    protected $level = null;  //返回级别 block | data | header | content

    public $is_order = null;

    protected $guarded = [];

    public $class_config;

    const DEFAULT_PAGE = 1;
    const DEFAULT_PER_PAGE = 10;


    public function header_property()
    {
        return $this->hasMany('XBlock\Kernel\Models\HeaderProperty', 'block_uuid')->orderBy('sequence', 'asc');
    }

    public function cache()
    {
        return $this->hasOne('XBlock\Kernel\Models\BlockCache', 'uuid', 'index');
    }

    public function headers()
    {
        return $this->belongsToMany('XBlock\Kernel\Models\Header', getTable('system.header_property'), 'block_uuid', 'header_uuid')
            ->withPivot(['is_visiable', 'is_sortable', 'is_filterable']);
    }

    public function button_property()
    {
        return $this->hasMany('XBlock\Kernel\Models\ButtonProperty', 'block_uuid', 'uuid')->when(user('permission', null), function ($q) {
            $q->whereIn('permission', user('permission'));
        })->orderBy('sequence', 'asc');
    }

    public function button_whithout_permission()
    {
        return $this->hasMany('XBlock\Kernel\Models\ButtonProperty', 'block_uuid', 'uuid')->orderBy('sequence', 'asc');
    }


    /**
     * 静态表头
     * @return array
     */
    protected function getStaticHeadersAttribute()
    {
        return $this->header_property()
            ->with('header')
            ->where(function ($q) {
                $q->where('filter_table', '=', '');
            })
            ->get()
            ->map(function ($header_property) {
                return $this->handleFilterHeader($header_property);
            });
    }

    /**
     * 动态表头
     * @return array
     */
    protected function getConditionHeadersAttribute()
    {
        return $this->header_property()
            ->with('header')
            ->where([['filter_table', '<>', '']])
            ->get()
            ->map(function ($header_property) {
                return $this->handleFilterHeader($header_property);
            });
    }


    public function getPropertyAttribute()
    {
        if ($this->attributes["property"]) return json_decode($this->attributes["property"], true);
        return [];
    }

    public function getButtonAttribute()
    {
        if (parameter('delete_history_data', false)) return [
            Button::restore(),
            Button::forceDelete()
        ];
        return $this->button_property->map(function ($item) {
            return Button::fill([
                'index' => $item->button->index,
                'title' => $item->button->title,
                'component' => $item->component,
                'link' => $item->link,
                'icon' => $item->icon,
                'icon_site' => $item->icon_site,
                'permission' => $item->permission,
                'position' => $item->position,
                'color' => $item->color,
                'confirm' => $item->confirm,
            ]);
        });
    }

    public function getHeaderAttribute()
    {
        return $this->header_property()
            ->with('header')
            ->get()->map(function ($item) {
                return BaseField::fill([
                    'title' => $item->header->title,
                    'index' => $item->header->index,
                    'unit' => $item->header->unit,
                    'sequence' => (int)$item->sequence,
                    'component' => $item->component,
                    'display' => $item->display,
                    'require' => (bool)$item->require,
                    'value_type' => $item->value_type,
                    'relation' => $item->relation,
                    'visible' => (boolean)$item->visible,
                    'addable' => (boolean)$item->addable,
                    'editable' => (boolean)$item->editable,
                    'filterable' => (boolean)$item->filterable,
                    'sortable' => (boolean)$item->sortable,
                    'exportable' => (boolean)$item->exportable,
                    'importable' => (boolean)$item->importable,
                    'default' => $item->default,
                    'fixed' => $item->fixed,
                    'parent' => $item->parent,
                    'width' => (int)$item->width,
                    'error_message' => $item->error_message,
                ]);
            });
    }


    public function getBlockWithoutCache()
    {
        $data = $this->data;
        $button = $this->button;
        $button_list = [];
        $button->map(function ($item) use (&$button_list, $data) {
            $hook = isset($this->config['button'][$item->type]) ? $this->config['button'][$item->type] : null;
            $show_hook = $this->config['buttonShow'];
            $method = camelize($item->type) . 'ButtonModel';
            if ($this->class_config && method_exists($this->class_config, $method)) {
                $res = $this->class_config->$method($item, $data['content']);
            } else if ($this->class_config && method_exists($this->class_config, 'buttonShow')) {
                $res = $this->class_config->buttonShow($item, $item->type, $data['content']);
            } else if ($hook && $hook instanceof Closure) {
                $res = $hook($item, $data['content']);
            } else if ($show_hook && $show_hook instanceof Closure) {
                $res = $show_hook($item, $item->type, $data['content']);
            } else {
                $res = $item;
            }
            if ($res instanceof ButtonProperty) $button_list[] = $res;
        });
        $block = [
            'uuid' => $this->uuid,
            'index' => $this->index,
            'link' => $this->link,
            'title' => $this->title,
            'width' => (int)$this->width,
            'height' => (int)($this->height == 0 ? null : $this->height),
            'has_card' => (bool)$this->has_card,
            'has_border' => (bool)$this->has_border,
            'tabs_from' => $this->tabs_from,
            'relation_index' => $this->relation_index,
            'display_graph' => $this->display_graph,
            'property' => $this->property,
            'button' => $button_list,
            'data' => $data,
        ];
        return $this->pushBlockDetail($block);
    }


    public function pushBlockDetail(&$block)
    {
        switch ($this->display_graph) {
            case 'column_list':
                return $block = $block + [
                        'icon' => $this->icon,
                    ];
            case 'common_list':
                return $block = $block + [
                        'link' => $this->link,
                    ];
            case 'table_tabs':
                return $block = $block + [
                        'tabs' => $this->tabs,
                    ];
            case 'column_card_array':
                return $block = $block + [
                        'group_by' => $this->group_by,
                    ];
            case 'common_map':
                return $block = $block + [
                        'map_style' => $this->map_style,
                    ];
            case 'table':
            default:
                return $block;
        }
    }

    /**
     * 根据filter_type 格式化可筛选的表头
     * @param HeaderProperty $header_property
     * @return array
     */
    protected function handleFilterHeader(HeaderProperty $header_property)
    {
        return $header_property;
        $header = [
            'uuid' => $header_property->uuid,
            'title' => $header_property->header->title,
            'index' => $header_property->header->index,
            'unit' => $header_property->header->unit,
            'sequence' => (int)$header_property->sequence,
            'require' => (bool)$header_property->require,
            'error_message' => $header_property->error_message,
            'value_type' => $header_property->value_type,
            'axis_type' => $header_property->axis_type,
            'relation' => $header_property->relation,
            'is_visiable' => (boolean)$header_property->is_visiable,
            'is_sortable' => (boolean)$header_property->is_sortable,
            'is_editable' => (boolean)$header_property->is_editable,
            'is_addable' => (boolean)$header_property->is_addable,
            'is_filterable' => (boolean)$header_property->is_filterable,
            'is_import' => (boolean)$header_property->is_import,
            'is_export' => (boolean)$header_property->is_export,
            'default' => $header_property->default,
            'width' => (int)$header_property->width,
        ];

        if (in_array($this->display_graph, ['table', 'tab_table'])) $header += [
            'fixed' => (bool)$header_property->fixed,
        ];

        $header = empty($header_property->link) ? $header : $header + ['link' => $header_property->link];
        $header = empty($header_property->render) ? $header : $header + ['render' => $header_property->render];
        if ($header_property->parent) {
            $header['parent'] = $header_property->parent;
        }
        if ($header_property->is_filterable || $header_property->is_editable || in_array($header_property->filter_type, ['radio', 'checkbox', 'radio_temporary', 'checkbox_temporary', 'radio_list'])) {
            $header = array_merge($header, [
                'filter_type' => $header_property->filter_type,
                'filter_position' => $header_property->filter_position,
                'filter_item' => $this->getFilterItem($header_property),
            ]);

        }
        return $header;
    }


    public function getFilterItem($header_property)
    {
        $method = camelize($header_property->header->index) . 'Item';
        $filter_item = null;
        if ($this->class_config && method_exists($this->class_config, $method)) $filter_item = $this->class_config->$method();
        if (!$filter_item) $filter_item = config("block.{$this->index}.header_item.{$header_property->header->index}");
        $table = strpos($header_property->filter_table, '.') ? $header_property->filter_table : 'dict.' . $header_property->filter_table;
        if (!$filter_item && $header_property->filter_table) $filter_item = app('DBService')->table($table)->get()->toArray();
        if (!$filter_item) $filter_item = config("dict.{$header_property->header->index}", []);
        $filter_item = $filter_item instanceof \Closure ? $filter_item() : $filter_item;
        return collect($filter_item)->filter(function ($item) {
            if (is_array($item) && isset($item['department_uuid'])) return in_array($item['department_uuid'], user('all_department_uuid', ['*', '']));
            else if (is_object($item) && isset($item->department_uuid)) return in_array($item->department_uuid, user('all_department_uuid', ['*', '']));
            else return true;
        })->values();

    }

    public function getOneHeaderDict($index)
    {
        $header = $this->headers()->where('index', $index)->first();
        if ($header && ($header_property = $header->property->where('block_uuid', $this->uuid)->first()) && ($dict = $this->getFilterItem($header_property))) return $dict;
        else return [];
    }


    public static function boot()
    {
        parent::boot();
        self::creating(function ($query) {
            $query->uuid = guid();
            $header_props = [
                'header_uuid' => '113A4789-9AE4-CE76-16FB-0AD8ADF70E72',
                'block_uuid' => $query->uuid,
                'is_visiable' => 0,
            ];
            if ($query->display_graph === 'detail_list') {
                $header_props = $header_props + [
                        'is_filterable' => 1,
                        'filter_type' => 'strict_text',
                    ];
            }
            HeaderProperty::create($header_props);
        });

        self::addGlobalScope('module', function ($query) {
            return $query->where(function ($q) {
                $q->whereIn('module', get_module())->orWhere('module', '');
            });
        });
        self::deleted(function ($query) {
            $query->header_property()->delete();
            $query->button_whithout_permission()->delete();
        });
    }

    public function setPropertyAttribute($value)
    {
        if (is_array($value)) $this->attributes['property'] = json_encode($value);
        else $this->attributes['property'] = $value;
    }


}