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

    public function getAllActions(): Collection
    {
        if ($this->block->actions) return $this->block->actions;
        if ($this->block->recyclable()) return $this->block->actions = $this->getActions()->concat($this->getRecycleActions());
        return $this->block->actions = $this->getActions();
    }

    public function getAccessActions()
    {
        return $this->checkAccess($this->block->getActions(false));
    }

    public function getActions(): Collection
    {
        if (method_exists($this, 'actions')) {
            $creator = new ActionCreator($this->block);
            $this->actions($creator);
            return collect($this->block->actions);

        } else {
            return collect($this->block->button())->map(function ($item) {
                $item->permission = $item->permission ? $item->permission : $this->createPermissionName($item->index);
                return $item;
            });
        }
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
        });
    }


}