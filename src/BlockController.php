<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-2
 * Time: 下午4:12
 */

namespace XBlock\Kernel;


use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use XBlock\Helper\Tool;
use XBlock\Kernel\Blocks\Block;
use XBlock\Kernel\Elements\Action;
use XBlock\Helper\Response\CodeResponse;
use XBlock\Kernel\Elements\Buttons\BaseButton;
use XBlock\Kernel\Services\BlockService;

class BlockController
{
    protected $service;

    protected $block;
    protected $block_index;

    protected $action;
    protected $action_index;

    public function __construct(BlockService $service, Request $request)
    {
        $this->service = $service;

        $this->block_index = $request->block;

        $this->action_index = $request->action;

        $this->action = Tool::camelize($this->action_index);

        $this->block = $this->getBlockObject();
    }


    private function validity()
    {
        if (!$this->block) return message(false, '模块不存在！');
        if (!($this->block instanceof Block)) return message(false, '当前调用非Block');
        if (!method_exists($this->block, $this->action)) return message(false, "{$this->block_index}中的【{$this->action_index}】事件未定！");
        if (!user('is_admin')) {
            if (!$this->checkActionAccess()) return message(false, '您没有该事件的权限！');
            if (!$this->checkButtonAccess()) return message(false, '您没有该操作的权限！');
        }
        return true;
    }

    public function action(Request $request)
    {
        $validity = $this->validity();
        if ($validity === true) {
            $data = $this->block->{$this->action}($request);
            $log = !$this->block->checkCloseLog($this->action);
            if ($data instanceof CodeResponse || $data instanceof Response) $response = $data;
            else  $response = message($data)->data($data);
            if ($log) $this->actionLog($response);
            return $response;
        }
        return $validity;
    }

    protected function checkActionAccess()
    {
        if ($this->action_index === 'list') return in_array($this->block_index . '@' . 'list', user('permission', []));
        $events = $this->block->getActionWithPermission();

        $event = $events->first(function ($item) {
            return $item instanceof Action && $item->index == $this->action_index;
        });
        if ($event) {
            if ($event && $event->permission && !(in_array($event->permission, user('permission', [])))) return false;
        }
        return true;
    }

    public function checkButtonAccess()
    {
        $buttons = $this->block->getButtonWithPermission();
        $button = $buttons->first(function ($item) {
            return $item instanceof BaseButton && $item->index == $this->action_index;
        });
        if ($button) {
            if ($button && $button->permission && !(in_array($button->permission, user('permission', [])))) return false;
        }
        return true;
    }

    protected function actionLog($response)
    {
        if (method_exists($this->block, 'eventLog')) $this->block->eventLog($this->block, $this->action, $response);
        else {
            $globalHookClass = config('xblock.register.hook', GlobalHookRegister::class);
            if (class_exists($globalHookClass)) {
                $globalHook = new $globalHookClass;
                if (method_exists($globalHook, 'eventLog')) {
                    $globalHook->eventLog($this->block, $this->action, $response);
                }
            }
        }
    }


    public function getBlockObject($property = [])
    {
        $class_name = null;
        //todo 添加缓存
        if (!$class_name) {
            if (env('APP_ENV') === 'production') $class_name = $this->service->findBlockClassFormCache($this->block_index);
            else $class_name = $this->service->findBlockClass($this->block_index);
        }
        $block = ($class_name && class_exists($class_name)) ? (new $class_name($property)) : null;
        if ($block instanceof Block) {
            $block->index = $this->block_index;
            return $block;
        }
        return null;
    }


}