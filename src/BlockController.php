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
//use XBlock\Kernel\Models\BlockModel;
use XBlock\Helper\Response\CodeResponse;
use XBlock\Kernel\Services\BlockService;

class BlockController
{
    protected $service;

    public function __construct()
    {
        $this->service = new  BlockService();
    }

    public function action($block, $action, Request $request)
    {
        if ($action == 'list') return $this->list($block);
        $blockObject = $this->getBlockObject($block);
        if (!$blockObject) return message(false, '模块不存在！');
        $event_array = $blockObject->getActionWithPermission();
        $log = false;
        if ($event_array) {
            $event = $this->parseAction($event_array, $action);
            if ($event === false) return message(false, '没有调用该事件的权限！');
            elseif ($event) {
                $action = $event->index;
                $log = $event->log;
            }
        }
        $action = Tool::camelize($action);
        if (method_exists($blockObject, $action)) {
            $data = $blockObject->$action($request);
            if ($data instanceof CodeResponse || $data instanceof Response) $response = $data;
            else  $response = message($data)->data($data);
            if ($log) $this->actionLog($blockObject, $action, $response->success);
            return $response;
        }
        return message(false, "{$block}中的【{$action}】事件不存在！");
    }

    public function list($block)
    {
        if (config('kernel.driver', 'class') === 'database') {
            $block_model = BlockModel::where('index', $block)->first();
            if (!$block_model) return message(true)->silence()->data([]);
            $block_object = $this->getBlockObject($block, $block_model->getAttributes());
            $block_object = $block_object ? $block_object : new Block($block_model->getAttributes());
            $block_object->header = $block_model->header;
            $block_object->button = $block_model->button;

        } else {
            $block_object = $this->getBlockObject($block);
        }
        if (!$block_object) return message(false, '未找到模块【' . $block . '】');
        return message(true)->silence()->data($block_object->get());
    }


    protected function parseAction($event, $action)
    {
        $event = $event->first(function ($item) use ($action) {
            return $item instanceof Action && $item->index == $action;
        });
        if (!$event) return null;
        $location = request()->header('location');
        $event->permission = $event->permission ? $event->permission : str_replace('/detail/:relation_uuid', '', $location);
        if ($event && $event->permission && !(in_array($action->permission, user('permission', [])))) return false;
        return $event;
    }

    protected function actionLog($block, $action, $res)
    {

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