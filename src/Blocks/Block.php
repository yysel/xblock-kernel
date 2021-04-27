<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-2
 * Time: 下午4:32
 */

namespace XBlock\Kernel\Blocks;

use Illuminate\Support\Collection;
use XBlock\Helper\Tool;
use XBlock\Kernel\Elements\Component;
use XBlock\Kernel\Elements\Components\Base;
use XBlock\Kernel\Elements\Fields\BaseField;
use XBlock\Kernel\Events\BlockOperator;
use XBlock\Kernel\Events\DefaultEvent;
use XBlock\Kernel\Fetch\Fetch;
use XBlock\Kernel\Fetch\ModelFetch;


class Block
{
    public $title;

    public $index;

    public $component = 'table';

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

    public $all_actions;

    public $recycle_actions;

    public $events;

    protected $content = [];

    protected $fetch;

    protected $driver = 'class';

    private $close_log_list = [];

    protected $location;

    public $auth = true;

    public static $permission;

    use  DefaultEvent;

    public $operator;

    final public function __construct($data = [])
    {
        $this->operator = new BlockOperator($this);
        if ($data) {
            foreach ($data as $key => $value) $this->$key = $value;
        }
        if (method_exists($this, 'boot')) $this->boot();
        $this->index = static::getIndex();
        if ($this->auth) static::$permission = static::getPermission();
        $component = $this->component();
        if ($component instanceof Base) {
            $this->component = $component->getComponent();
            $this->property = $component->getProperty();
        }

        $this->location = request()->header('location');
        $this->fetch = $this->getFetch();
        $this->actions = $this->operator->getActions();
        $this->recycle_actions = $this->operator->getRecycleActions();
        $this->all_actions = $this->actions->concat($this->recycle_actions);

    }

    protected function component()
    {
        return Component::table();
    }

    public function header()
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
    protected function getFetch(): Fetch
    {
        return new  ModelFetch($this);
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


    final public function getEvents(): Collection
    {
        if ($this->events && $this->events instanceof Collection) return $this->events;
        if (method_exists($this, 'events')) {
            $creator = $this->operator->getEventCreator();
            $this->fields($creator);
        }
        return $this->events = collect($this->events);
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

    final static public function getIndex(): string
    {
        $explode = explode('\\', static::class);
        $name = end($explode);
        $name = Tool::unpascal($name);
        return $name ;
    }

    final static public function getPermission(): string
    {
        if (static::$permission) return static::$permission;
        return static::getIndex() . '@list';
    }


    final  public function checkCloseLog($action)
    {
        return in_array($action, $this->close_log_list);
    }

    final public function recyclable()
    {
        $this->model();
        return $this->recyclable;
    }

    final  public function query(): array
    {
        $array = [
            'header' => $this->operator->getFields(),
            'button' => $this->operator->currentActions(),
            'content' => $this->getContent(),
            'pagination' => $this->fetch->getPagination(),
            'parameter' => $this->fetch->parameter,
            'sorting' => $this->fetch->sorting,
        ];
        $attributes = [
            'index', 'title', 'component', 'property', 'relation_index',
            'has_card', 'tab_key', 'width', 'height', 'recyclable', 'primary_key'
        ];
        foreach ($attributes as $attribute) $array[$attribute] = $this->{$attribute};
        return $array;
    }


}
