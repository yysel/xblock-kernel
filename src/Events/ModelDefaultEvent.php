<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-2
 * Time: 下午9:31
 */

namespace XBlock\Kernel\Events;

use Maatwebsite\Excel\ExcelServiceProvider;
use Maatwebsite\Excel\Facades\Excel;
use XBlock\Helper\Response\ErrorCode;
use Illuminate\Support\Facades\DB;
use XBlock\Kernel\Services\BlockImport;
use XBlock\Kernel\Services\ImportResult;
use XBlock\Kernel\Services\RunTimeService;


trait ModelDefaultEvent
{

    public function add($request)
    {
        DB::beginTransaction();
        $model = $this->model();
        $this->operator->getFields()->each(function ($item) use (&$model, $request) {
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
        DB::beginTransaction();
        $model = $this->model();
        $primary = $model->getKeyName();
        $primary_value = $request->input($primary);
        $model = $model->find($primary_value);
        if (!$model) {
            DB::rollBack();
            return message(false, '该数据不存在！');
        }
        $this->operator->getFields()->each(function ($item) use (&$model, $request) {
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


    public function import()
    {
        RunTimeService::openProvider(ExcelServiceProvider::class);
        $file = request()->file('file');
        if (!$file) return modal(false)->info('没有找到要上传的文件！');
        $ext = $file->getClientOriginalExtension();
        if (!in_array($ext, ['xlsx', 'xls'])) return modal(false)->info('上传的文件格式不正确！');
        $importer = new BlockImport($this);
        Excel::import($importer, $file);
        $error_line = $importer->getError();
        $success = $importer->getSuccess()->count();
        if ($importer->getError()->count()) {
            $block = new ImportResult();
            $block->setContent($error_line);
            $block->setHeader($importer->getHeaders());
            return message(false)->data([
                'success' => $success,
                'error' => $error_line->count(),
                'block' => $block->query()
            ]);
        } else return modal(true)->data($success);
    }


}
