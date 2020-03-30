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
use XBlock\Kernel\Elements\Button;
use XBlock\Kernel\Elements\Component;
use XBlock\Kernel\Elements\Components\Base;
use XBlock\Kernel\Elements\Fields\BaseField;
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

    public $header;

    public $button;

    protected $fetch;

    protected $driver = 'class';

    private $close_log_list = [];

    protected $location;

    use  DefaultEvent;

    final public function __construct($data = [])
    {
        if ($data) {
            foreach ($data as $key => $value) $this->$key = $value;
        }
        if (method_exists($this, 'boot')) $this->boot();
        if (!$this->index) {
            $class = get_class($this);
            $this->index = Tool::unpascal(last(explode('\\', $class)));
        }
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

    protected function button()
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

    final public function recycleButton()
    {
        return [
            Button::small('restore', '恢复')->position('inner'),
            Button::small('force_delete', '清除')->position('inner')->confirm('清除后，数据不可再恢复！确定吗？')->color('#F85054'),
        ];
    }


    /** 获取Block的返回值
     * @return array
     */
    final  public function get(): array
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
            'header' => $this->getHeader(),
            'button' => $this->getButton(),
            'content' => $this->getContent(),
            'parameter' => $this->fetch->parameter,
            'sorting' => $this->fetch->sorting,
            'pagination' => $this->fetch->getPagination(),
            'recyclable' => $this->recyclable,
            'primary_key' => $this->primary_key,
        ];
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

    final public function getHeader(): Collection
    {
        if ($this->header) return $this->header;
        return $this->header = collect($this->header())->filter(function ($item) {
            return $item instanceof BaseField;
        })->values();
    }

    final public function getContent()
    {
        return $this->fetch->init();
    }

    final public function getButtonWithPermission()
    {
        if ($this->button) return $this->button;
        $location = request()->header('location');
        return $this->button = collect(array_merge($this->button(), $this->recycleButton()))->map(function ($item) use ($location) {
            $item->permission = $item->permission ? $item->permission : $this->createPermissionName($item->index);
            return $item;
        });
    }

    final public function getActionWithPermission()
    {
        $location = request()->header('location');
        return collect($this->event())->map(function ($item) use ($location) {
            $item->permission = $item->permission ? $item->permission : $this->createPermissionName($item->index);
            return $item;
        });
    }

    final protected function getButton()
    {
        return $this->button = collect($this->getButtonWithPermission())->filter(function ($item) {
            $deleted = $item->index == 'restore' || $item->index == 'force_delete';
            if (parameter('__deleted', false)) return $deleted && (user('is_admin') || in_array($item->permission, user('permission', [])));
            return (user('is_admin') || in_array($item->permission, user('permission', []))) && !$deleted;
        })->values();
    }

    /** 取出或者判断传入值与前端调用的路径是否一致
     * @param string $path
     * @return bool | string
     */
    final protected function location(String $path = null)
    {
        $location = request()->header('location');
        if ($path) {
            $path = '/' . str_replace('/', "\/", trim($path, '/')) . "\/" . '/';
            return (bool)preg_match($path, '/' . trim($location) . '/');
        }
        return $location;
    }

    /** 取出或者判断调用客户端类型是否一致
     * @param string $type
     * @return bool  | string
     */
    final protected function client(String $type = null)
    {
        $client_type = request()->header('client_type');
        if ($type) return $type === $client_type;
        return $client_type;
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

    final private function createPermissionName($index)
    {
        return "{$this->index}@{$index}";
    }


}
