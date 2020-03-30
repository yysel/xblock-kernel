<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-2
 * Time: 下午9:31
 */

namespace XBlock\Kernel\Events;

use XBlock\Helper\Response\ErrorCode;
use Illuminate\Support\Facades\DB;


trait ModelDefaultEvent
{

    public function add($request)
    {
        if ($this->origin_type !== 'model') return message(false, '未定义【add】事件');
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
        $hook_res = CallHook::call('beforeAdd', $model, $this);
        if ($hook_res instanceof ErrorCode) {
            DB::rollBack();
            return $hook_res;
        }
        $res = $model->save();
        if ($res) {
            CallHook::call('afterAdd', $model, $this);
            DB::commit();
            return message(true, '创建成功！', $model);
        }
        DB::rollBack();
        return message(false, '创建失败！');
    }

    public function edit($request)
    {
        if ($this->origin_type !== 'model') return message(false, '未定义【edit】事件');
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
        $hook_res = CallHook::call('beforeEdit', $model, $this);
        if ($hook_res instanceof ErrorCode) {
            DB::rollBack();
            return $hook_res;
        }
        $res = $model->save();
        if ($res) {
            CallHook::call('afterEdit', $model, $this);
            DB::commit();
            return message(true, '修改成功！', $model);
        }
        DB::rollBack();
        return message(false, '修改失败！');
    }

    public function delete($request)
    {
        if ($this->origin_type !== 'model') return message(false, '未定义【delete】事件');
        DB::beginTransaction();
        $model = $this->model();
        $primary = $model->getKeyName();
        $primary_value = $request->input($primary);
        $model = $model->withoutGlobalScopes()->find($primary_value);
        if (!$model) {
            DB::rollBack();
            return message(false)->info('该数据不存在!');
        }
        $hook_res = CallHook::call('beforeDelete', $model, $this);
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
            CallHook::call('afterDelete', $model, $this);
            DB::commit();
            return message(true, '删除成功！', $model);
        }
        DB::rollBack();
        return message(false, '删除失败！');
    }

    public function forceDelete($request)
    {
        if ($this->origin_type !== 'model') return message(false, '未定义【forceDelete】事件');
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
        $hook_res = CallHook::call('beforeForceDelete', $model, $this);
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
            CallHook::call('afterForceDelete', $model, $this);
            DB::commit();
            return message(true, '删除成功！');
        }
        DB::rollBack();
        return message(false, '删除失败！');
    }

    public function restore($request)
    {
        if ($this->origin_type !== 'model') return message(false, '未定义【restore】事件');
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
        $hook_res = CallHook::call('beforeRestore', $model, $this);
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
            CallHook::call('afterRestore', $model, $this);
            DB::commit();
            return message(true, '数据恢复成功！');
        }
        DB::rollBack();
        return message(false, '数据恢复失败！');
    }


}