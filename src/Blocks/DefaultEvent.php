<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-2
 * Time: 下午9:31
 */

namespace XBlock\Kernel\Blocks;


use Maatwebsite\Excel\Facades\Excel;
use XBlock\Helper\ErrorCode;
use DB;
use XBlock\Kernel\GlobalHookRegister;
use XBlock\Kernel\Services\BlockExport;

trait DefaultEvent
{

    public function contents()
    {
        $this->getHeader();
        $content = $this->getContent();
        return message(true)->data($content)->silence();
    }

    public function detail()
    {
        $data = [];
        if (request('uuid', null)) {
            $data = $this->model(request('uuid'));
        } elseif ($this->relation_index && request($this->relation_index)) {
            $data = $this->model()->where($this->relation_index, request($this->relation_index))->first();
        }

        return message(true)->data($data)->silence();
    }

    public function add($request)
    {
        DB::beginTransaction();
        $model = $this->model();
        $this->getHeader()->each(function ($item) use (&$model, $request) {
            if ((($item->addable && !in_array($item->index, $this->add_except)) || in_array($item->index, $this->add_include)) && $request->has($item->index)) {
                $model->{$item->index} = $request->input($item->index);
            }
        });
        if ($this->relation_index && $relation = relation_uuid()) {
            $model->{$this->relation_index} = $relation;
        }
        $hook_res = $this->handleChangeHook('beforeAdd', $model, $request);
        if ($hook_res instanceof ErrorCode) {
            DB::rollBack();
            return $hook_res;
        }
        $res = $model->save();
        if ($res) {
            $this->handleChangeHook('afterAdd', $model, $request);
            DB::commit();
            return message(true, '创建成功！', $model);
        }
        DB::rollBack();
        return message(false, '创建失败！');
    }

    public function edit($request)
    {
        DB::beginTransaction();
        $model = $this->model();
        $primary = $model->getKeyName();
        $primary_value = $request->input($primary);
        $model = $model->find($primary_value);
        if (!$model) {
            DB::rollBack();
            return message(false, '该数据不存在！');
        }
        $this->getHeader()->each(function ($item) use (&$model, $request) {
            if ((($item->editable && !in_array($item->index, $this->edit_except)) || in_array($item->index, $this->edit_include)) && $request->has($item->index)) {
                $model->{$item->index} = $request->input($item->index);
            }
        });
        $hook_res = $this->handleChangeHook('beforeEdit', $model, $request);
        if ($hook_res instanceof ErrorCode) {
            DB::rollBack();
            return $hook_res;
        }
        $res = $model->save();
        if ($res) {
            $this->handleChangeHook('afterEdit', $model, $request);
            DB::commit();
            return message(true, '修改成功！', $model);
        }
        DB::rollBack();
        return message(false, '修改失败！');
    }

    public function delete($request)
    {
        DB::beginTransaction();
        $model = $this->model();
        $primary = $model->getKeyName();
        $primary_value = $request->input($primary);
        $model = $model->withoutGlobalScopes()->find($primary_value);
        if (!$model) {
            DB::rollBack();
            return message(false)->info('该数据不存在!');
        }
        $hook_res = $this->handleChangeHook('beforeDelete', $model, $request);
        if ($hook_res instanceof ErrorCode) {
            DB::rollBack();
            return $hook_res;
        }
        $relation_model = $model->delete_relation ? $model->delete_relation : [];
        $res = $model->delete();
        foreach ($relation_model as $method) {
            $model->$method()->delete();
        }
        if ($res) {
            $this->handleChangeHook('afterDelete', $model, $request);
            DB::commit();
            return message(true, '删除成功！', $model);
        }
        DB::rollBack();
        return message(false, '删除失败！');
    }

    public function forceDelete($request)
    {
        $model = $this->model();
        if (!method_exists($model, 'forceDelete')) return message(false);
        DB::beginTransaction();
        $primary = $model->getKeyName();
        $primary_value = $request->input($primary);
        $model = $model->withTrashed()->withoutGlobalScopes()->find($primary_value);
        if (!$model) {
            DB::rollBack();
            return message(false)->info('该数据不存在!');
        }
        $hook_res = $this->handleChangeHook('beforeForceDelete', $model, $request);
        if ($hook_res instanceof ErrorCode) {
            DB::rollBack();
            return $hook_res;
        }

        $relation_model = $model->delete_relation ? $model->delete_relation : [];
        $res = $model->forceDelete();
        foreach ($relation_model as $method) {
            $model->$method()->forceDelete();
        }
        if ($res) {
            $this->handleChangeHook('afterForceDelete', $model, $request);
            DB::commit();
            return message(true, '删除成功！');
        }
        DB::rollBack();
        return message(false, '删除失败！');
    }

    public function restore($request)
    {
        $model = $this->model();
        if (!method_exists($model, 'restore')) return message(false);
        DB::beginTransaction();
        $primary = $model->getKeyName();
        $primary_value = $request->input($primary);
        $model = $model->withTrashed()->withoutGlobalScopes()->find($primary_value);
        if (!$model) {
            DB::rollBack();
            return message(false)->info('该数据不存在!');
        }
        $hook_res = $this->handleChangeHook('beforeRestore', $model, $request);
        if ($hook_res instanceof ErrorCode) {
            DB::rollBack();
            return $hook_res;
        }
        $res = $model->restore();
        if ($relation_model = $model->delete_relation) {
            foreach ($relation_model as $method) {
                $model->$method()->restore();
            }
        }
        if ($res) {
            $this->handleChangeHook('afterRestore', $model, $request);
            DB::commit();
            return message(true, '数据恢复成功！');
        }
        DB::rollBack();
        return message(false, '数据恢复失败！');
    }

    public function export()
    {
        $time = date('Y-m-d');
        return Excel::download(new BlockExport($this), request('filename', "{$this->title}[{$time}]") . ".xlsx");
    }

    public function import()
    {

    }

    final protected function handleChangeHook($key, &$model, $request)
    {
        $register = config('xblock.register.hook', false);
        if ($register) {
            $register_object = new $register;
            $global_hook_res = $register_object->$key($model, $this->index, $request);
            if ($global_hook_res instanceof ErrorCode) return $global_hook_res;
        }
        if (method_exists($this, $key)) {
            return $this->$key($model, $request);
        }
        return true;
    }


}