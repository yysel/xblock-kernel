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
use XBlock\Kernel\Services\BlockExport;
use XBlock\Kernel\Services\RunTimeService;

trait DefaultEvent
{

    public function list()
    {
        $this->closeLog('list');
        return message(true)->silence()->data($this->query());
    }

    public function contents()
    {
        $this->operator->getFields();
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

    public function export()
    {
        RunTimeService::openProvider(ExcelServiceProvider::class);
        $time = date('Y-m-d');
        return Excel::download(new BlockExport($this), request('filename', "{$this->title}[{$time}]") . ".xlsx");
    }




}
