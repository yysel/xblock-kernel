<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-2
 * Time: 下午4:32
 */

namespace XBlock\Kernel\Blocks;

use Illuminate\Support\Collection;
use XBlock\Kernel\Elements\Component;
use XBlock\Kernel\Elements\Components\Base;
use XBlock\Kernel\Elements\FieldCreator;
use XBlock\Kernel\Elements\Fields\BaseField;
use XBlock\Kernel\Events\BlockOperator;
use XBlock\Kernel\Events\DefaultEvent;
use XBlock\Kernel\Fetch\Fetch;


class Block
{
    public $title;

    public $index;

    public $component = 'table';

    public $origin_type = 'model';

    public $origin;

    public $pageable = true;

    public $sortable = true;

    public $property = [];

    public $relation_index;

    public $transform;

    public $has_card = true;

    public $tab_key = null;

    public $width;

    public $height;


    public $recyclable = false;

    public $where_except = [];

    public $add_except = [];
    public $add_include = [];

    public $edit_except = [];
    public $edit_include = [];

    public $primary_key = 'id';

    public $fields;

    public $actions;

    public $events;

    protected $content = [];

    protected $fetch;

    protected $driver = 'class';

    private $close_log_list = [];

    protected $location;

    public $auth = true;

    private $permission;

    use  DefaultEvent;

    private $operator;

    final public function __construct($data = [])
    {
        $this->operator = new BlockOperator($this);
        if ($data) {
            foreach ($data as $key => $value) $this->$key = $value;
        }
        if (method_exists($this, 'boot')) $this->boot();
        $this->index = $this->operator->getIndex();
        if ($this->auth) $this->permission = $this->index . '@list';
        $component = $this->component();
        if ($component instanceof Base) {
            $this->component = $component->getComponent();
            $this->property = $component->getProperty();
        }

        $this->location = request()->header('location');
        $this->fetch = $this->getFetch();

    }

    protected function component()
    {
        return Component::table();
    }

    protected function header()
    {
        return [];
    }

    public function button()
    {
        return [];
    }

    public function event()
    {
        return [];
    }

    public function content()
    {
        return [];
    }


    /**
     * 获取Fetch的实例化
     * @return Fetch
     */
    final  private function getFetch(): Fetch
    {
        $type = ucfirst($this->origin_type);
        $fetch_class = "XBlock\Kernel\Fetch\\{$type}Fetch";
        $fetch = new  $fetch_class;
        $fetch->block = $this;
        return $fetch;
    }

    final public function initParameter($key, $value, $force = false)
    {
        if (!isset(parameter()[$key]) || $force) {
            $parameter = parameter();
            $parameter[$key] = $value;
            request()->offsetSet('parameter', $parameter);
        }
        return $this;
    }

    /**
     * @param null $uuid
     * @return  \Illuminate\Database\Eloquent\Model;
     */
    final protected function model($uuid = null)
    {
        $model = $this->fetch->getBuilder();
        if ($uuid) return $model->find($uuid);
        return $model;
    }


    final public function getContent()
    {
        if ($this->content) return $this->content;
        return $this->fetch->init();
    }

    final public function getFields(): Collection
    {
        if ($this->fields && $this->fields instanceof Collection) return $this->fields;
        if (method_exists($this, 'fields')) {
            $creator = new FieldCreator($this);
            $this->fields($creator);
            return $this->fields = collect($this->fields);
        } else {
            return $this->fields = collect($this->header())->filter(function ($item) {
                return $item instanceof BaseField;
            })->values();
        }

    }

    final public function getActions($all = true): Collection
    {
        if ($all) return $this->operator->getAllActions();
        if ($this->actions) return $this->actions;
        if (parameter('__deleted', false)) return $this->actions = $this->operator->getRecycleActions();
        else  return $this->actions = $this->operator->getActions();

    }

    final public function getEvents(): Collection
    {
        if (!$this->events) {
            if (method_exists($this, 'fields')) {
                $creator = new FieldCreator($this);
                $this->fields($creator);
            }
        }
        return $this->events = $this->events instanceof Collection ? $this->events : collect($this->events);
    }


    final protected function closeLog($action = null)
    {
        if (!$action) {
            $trace = debug_backtrace()[1];
            $action = $trace['function'];
        }
        if (is_array($action)) array_merge($this->close_log_list, $action);
        else $this->close_log_list[] = $action;
        return $this;
    }


    final  public function checkCloseLog($action)
    {
        return in_array($action, $this->close_log_list);
    }

    public function recyclable()
    {
        $this->model();
        return $this->recyclable;
    }

    final  public function query(): array
    {
        return [
            'index' => $this->index,
            'title' => $this->title,
            'component' => $this->component,
            'property' => $this->property,
            'relation_index' => $this->relation_index,
            'has_card' => $this->has_card,
            'tab_key' => $this->tab_key,
            'width' => $this->width,
            'height' => $this->height,
            'header' => $this->getFields(),
            'button' => $this->operator->getAccessActions(),
            'content' => $this->getContent(),
            'parameter' => $this->fetch->parameter,
            'sorting' => $this->fetch->sorting,
            'pagination' => $this->fetch->getPagination(),
            'recyclable' => $this->recyclable,
            'primary_key' => $this->primary_key,
        ];
    }


}
