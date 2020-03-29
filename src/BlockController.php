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
use XBlock\Kernel\Services\BlockService;

class BlockController
{
    protected $service;

    public function __construct(BlockService $service)
    {
        $this->service = $service;
    }

    public function action($block, $action, Request $request)
    {
        $blockObject = $this->getBlockObject($block);
        if (!$blockObject) return message(false, '模块不存在！');
        if (!($blockObject instanceof Block)) return message(false, '当前调用非Block');
        $action = Tool::camelize($action);
        if (method_exists($blockObject, $action)) {
            $data = $blockObject->$action($request);
            $log = !$blockObject->checkCloseLog($action);
            if ($data instanceof CodeResponse || $data instanceof Response) $response = $data;
            else  $response = message($data)->data($data);
            if ($log) $this->actionLog($blockObject, $action, $response);
            return $response;
        }
        return message(false, "{$block}中的【{$action}】事件不存在！");
    }

    protected function parseAction($event, $action)
    {
        $event = $event->first(function ($item) use ($action) {
            return $item instanceof Action && $item->index == $action;
        });
        if (!$event) return null;
        $location = request()->header('location');
        $event->permission = $event->permission ? $event->permission : str_replace('/detail/:relation_uuid', '', $location);
        if ($event && $event->permission && !(in_array($event->permission, user('permission', [])))) return false;
        return $event;
    }

    protected function actionLog($block, $action, $response)
    {
        if (method_exists($block, 'eventLog')) $block->eventLog($block, $action, $response);
        else {
            $globalHookClass = config('xblock.register.hook', GlobalHookRegister::class);
            if (class_exists($globalHookClass)) {
                $globalHook = new $globalHookClass;
                if (method_exists($globalHook, 'eventLog')) {
                    $globalHook->eventLog($block, $action, $response);
                }
            }
        }
    }


    public function getBlockObject($index, $property = [])
    {
        $class_name = null;
        //todo 添加缓存
        if (!$class_name) {
            $class_name = $this->service->findBlockClass($index);
        }
        $block = ($class_name && class_exists($class_name)) ? (new $class_name($property)) : null;
        if ($block instanceof Block) {
            $block->index = $index;
            return $block;
        }
        return null;
    }


}