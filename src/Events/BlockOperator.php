<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 20-4-1
 * Time: 上午3:20
 */

namespace XBlock\Kernel\Events;


use Illuminate\Support\Collection;
use XBlock\Helper\Tool;
use XBlock\Kernel\Blocks\Block;
use XBlock\Kernel\Elements\Action;
use XBlock\Kernel\Elements\ActionCreator;
use ReflectionMethod;
use XBlock\Kernel\Elements\EventCreator;
use XBlock\Kernel\Elements\FieldCreator;
use Exception;

class BlockOperator
{
    public $block;

    public function __construct(Block $block)
    {
        $this->block = $block;
    }

    public function getIndex()
    {
        if ($this->block->index) return $this->block->index;
        $class = get_class($this->block);
        return Tool::unpascal(last(explode('\\', $class)));

    }

    final public function getRecycleActions(): Collection
    {
        return collect([
            Action::small('restore', '恢复')
                ->position('inner')
                ->permission($this->createPermissionName('restore')),
            Action::small('force_delete', '清除')
                ->position('inner')
                ->confirm('清除后，数据不可再恢复！确定吗？')
                ->permission($this->createPermissionName('restore'))
                ->color('#F85054'),
        ]);
    }

    public function getActions(): Collection
    {
        if (method_exists($this->block, 'actions')) {
            $creator = $this->getActionCreator();
            $this->block->actions($creator);
            return collect($this->block->actions);

        } else {
            return collect($this->block->button())->map(function ($item) {
                $item->permission = $item->permission ? $item->permission : $this->createPermissionName($item->index);
                return $item;
            });
        }
    }

    //当前应显示的操作
    public function currentActions(): Collection
    {
        if (parameter('__deleted', false)) return $this->checkAccess($this->block->recycle_actions);
        return $this->checkAccess($this->block->actions);
    }


    final public function createPermissionName($index)
    {
        return "{$this->block->index}@{$index}";
    }

    public function checkAccess(Collection $element)
    {
        if (user('is_admin')) return $element;
        return $element->filter(function ($item) {
            return in_array($item->permission, user('permission', []));
        })->values();
    }

    public function getFieldCreator()
    {
        return $this->getReflection(FieldCreator::class, 'fields');
    }

    public function getActionCreator()
    {
        return $this->getReflection(ActionCreator::class, 'actions');
    }

    public function getEventCreator()
    {
        return $this->getReflection(EventCreator::class, 'events');
    }

    public function getReflection($class, $method)
    {
        $obj = new ReflectionMethod($this->block, $method);
        $res = $obj->getParameters();
        if (isset($res[0])) {
            $class_name = $res[0]->getClass();
            if (!$class_name) return new $class($this->block);
            $class_name = $class_name->name;
            $creator = new $class_name($this->block);
            if ($creator instanceof $class) return $creator;
            else throw  new Exception('方法[' . $method . ']的参数必须继承自[' . $class . ']');
        }
    }


}
