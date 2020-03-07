<?php

namespace XBlock\Kernel\Services;

use Maatwebsite\Excel\Excel;
use \Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class  ExcelService
{

    public function exportData($block)
    {
        $data = $block->get();
        $title = $block->title;
        $index = $block->index;
        $data = $this->formatData($data, $index);
        Excel::create($title, function ($excel) use ($data, $title, $index) {
            $this->sheet($excel, $data, $title, $index);
        })->export('xlsx');
    }


    public function sheet($excel, $data, $title = '', $block)
    {
        $cellData = [[$title], ['制表单位：' . user('model')->department->title . '             制表日期：' . date('Y-m-d', time())],];
        $callback = [];
        $callback_on = true;
        $header = $this->getSheetHeader($data['header']);
        extract($header);
        $cellData[] = $header_title;
        $cellData[] = $header_title_sub;
        $start_row = 4;
        foreach ($data['content'] as $key => $value) {
            foreach ($header_name as $cell_key => $val) {
                $cell_value = isset($value[$val]) ? $value[$val] : null;
                $row_key = $key + $start_row;
                if (is_array($cell_value)) {
                    if (isset($cell_value['text'])) $cellData[$row_key][$cell_key] = $cell_value['text'];
                    else $cellData[$row_key][$cell_key] = json_encode($cell_value);
                } else if (array_key_exists($val, $header_has_dict)) {
                    $dict = collect($header_has_dict[$val]);
                    $dict_value = $dict->filter(function ($item) use ($cell_value) {
                        $item_array = $item instanceof Model ? $item->toArray() : (array)$item;
                        return $item_array['value'] == $cell_value;
                    })->first();
//                    $dict_value = (array)$dict_value;
                    $cellData[$row_key][$cell_key] = $dict_value ? $dict_value['text'] : $cell_value;
                } else $cellData[$row_key][$cell_key] = $cell_value;
            }
            if ($callback_on) {
                $callback_res = $this->callBack($key + 1, $value, $block);
                if ($callback_res) $callback[] = $callback_res;
            }
        }
        $high = $this->setHigh(count($cellData[2]), 30, 50);
        $width = $this->setWidth(count($cellData[2]), 25);
        $excel->sheet($title, function ($sheet) use ($cellData, $width, $high, $callback, $merge) {
            $h = count($cellData);  //表格的总高度
            $count = count($cellData[2]) - 1 < 0 ? 0 : count($cellData[2]) - 1;
            $w = $this->numToLatter($count);  //表格的总宽度
            $sheet->rows($cellData);
            $sheet->setWidth($width);
            $sheet->setHeight($high);
            foreach ($merge as $value) {
                $sheet->mergeCells($value);
            }
            $sheet->mergeCells('A1:' . $w . '1');     //合并标题
            $sheet->mergeCells('A2:' . $w . '2');     //合并副标题
            $sheet->cells('A1:' . $w . $h, function ($cell) {
                $cell->setAlignment('center');
                $cell->setValignment('center');
            });
            $sheet->cells('A3:' . $w . '3', function ($cells) {
                $cells->setBackground('#EEEEEE');
            });
            $sheet->cells('A1:' . $w . '1', function ($cells) {
                $cells->setFont(array(
                    'size' => '18',
                    'bold' => true
                ));
            });
            if ($callback) {
                foreach ($callback as $value) {
                    $sheet->cells('A' . $value['key'] . ':' . $w . $value['key'], function ($cells) use ($value) {
                        if (!empty($value['color'])) $cells->setBackground($value['color']);
                    });
                }
            }

            $sheet->cells('A3:' . $w . $h, function ($cells) {
                $cells->setBorder([
                    'allborders' => [
                        'style' => 'thin',
                        'color' => ['argb' => '##ccccffcc']
                    ],
                ]);
            });
        });

    }

    //格式化处理一些特殊的block,这些block可能存在数据重排
    public function formatData($data, $block)
    {
        switch ($block) {
            default:
                return $data;
        }
    }


    public function getSheetHeader($header)
    {
        $header_title = [];
        $header_title_sub = [];
        $header_name = [];
        $header_has_dict = [];
        $merge = [];
        $has_sub = false;
        foreach ($header as $v) {
            $title = $v['unit'] ? $v['title'] . '（' . $v['unit'] . '）' : $v['title'];
            if (!$v['is_export']) continue;
            if (!empty($v['sub_headers'])) {
                $has_sub = true;
                $merge[] = $this->numToLatter(count($header_title)) . '3:' . $this->numToLatter(count($v['sub_headers']) + count($header_title) - 1) . '3';
                foreach ($v['sub_headers'] as $v_sub) {
                    $header_title[] = $title;
                    $header_title_sub[] = $v_sub['title'];
                    $header_name[] = $v_sub['index'];
                }
            } else {
                if (!empty($v['filter_item'])) $header_has_dict[$v['index']] = $v['filter_item'];
                $merge[] = $this->numToLatter(count($header_title)) . '3:' . $this->numToLatter(count($header_title)) . '4';
                $header_title[] = $title;
                $header_title_sub[] = $title;
                $header_name[] = $v['index'];
            }
        }
        return compact('header_title', 'header_title_sub', 'header_name', 'merge', 'has_sub', 'header_has_dict');
    }


    //下载导出模板
    public function downloadImportTemplate($block)
    {
        $header = $this->getTemplateHeader($block->header);
        $title = $block->title . '导入模板';
        Excel::create($title, function ($excel) use ($header, $title) {
            $excel->sheet($title, function ($sheet) use ($header) {
                $width = $this->setWidth(count($header), 12);
                $high = $this->setHigh(count($header), 30, 30);
                $sheet->rows([$header]);
                $sheet->setWidth($width);
                $sheet->setHeight($high);
            });
        })->export('xlsx');
    }

    //获取导出模板的表头
    public function getTemplateHeader($header)
    {
        return $header->filter(function ($item) {
            return $item['is_import'];
        })->map(function ($item) {
            return $item['title'];
        })->all();
    }


    //为block定制样式回调
    public function callBack($key, $val, $block)
    {
        switch ($block) {
            case 'station_monitor_today':
                if ($val['heat_perdict'] > $val['heat_planned'] && $val['heat_planned'] > 0) return ['key' => $key, 'color' => '#FFE4E1'];
                return null;
                break;
            default;
                return null;
        }
    }

    //Excel 宽高设置辅助方法
    public function setHigh($count = 0, $high, $first)
    {
        $res = [];
        for ($i = 1; $i <= $count; $i++) {
            $res[$i] = $high;
        }
        $res[1] = $first;
        return $res;
    }

    public function setWidth($count = 0, $width)
    {
        $res = [];
        for ($i = 1; $i <= $count; $i++) {
            $res[$this->numToLatter($i - 1)] = $width;
        }
        return $res;
    }

    public function numToLatter($key)
    {
        $abc = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
            , 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ'];
        if (is_numeric($key)) return $abc[$key];
        return $abc;
    }


    public function chr($chr)
    {
        $len = strlen($chr);
        $m = 0;
        for ($i = 0; $i < $len; $i++) {
            $str = (string)$chr[$i];
            $m += (ord($str) - 64) + (($len - 1 - $i) * 26);
        }
        return $m + 63;
    }

    //导入的时候需要修改laravelExcel 的配置文件import=>["heading"=>"original"]
    public function importBlock($block)
    {
        if ($block->data_source_type != 'model') return modal("ERROR")->info('该模块不支持自动导入！');
        $file = request()->file('file');
        if ($file) {
            $ext = $file->getClientOriginalExtension();
            if (in_array($ext, ['xlsx', 'xls'])) {
                $res = [];
                Excel::load($file->getPathname(), function ($reader) use ($block, &$res) {
                    $datas = $reader->all();
                    $model = $block->getDataModel();
                    $dict = [];
                    $header = $block->header->mapWithKeys(function ($item) use (&$dict) {
                        if (isset($item['filter_item'])) $dict[$item['title']] = $item['filter_item'];
                        return [$item['title'] => $item['index']];
                    });
                    try {
                        foreach ($datas as $data) {
                            $res[] = (bool)$this->saveImportData($model, $header, $data, $dict);
                        }
                    } catch (\Exception $exception) {
                        \Log::error($exception->getMessage());
                        return $res[] = 0;
                    }

                });
                return checkRes($res);
            };
            return modal("ERROR")->info('上传的文件格式不正确！');
        } else return modal("ERROR")->info('没有找到要上传的文件！');
    }

    protected function saveImportData($model, $header, $data, $dict_list)
    {
        $saveData = [];
        foreach ($data as $key => $item) {
            $item = $item instanceof Carbon ? $item->toDateTimeString() : denoising($item);
            if ($item === null) continue;
            $dict = isset($dict_list[$key]) ? $dict_list[$key] : null;
            $dict = collect($dict)->filter(function ($it) use ($item) {
                if (isset($it->text)) return $it->text == $item;
                elseif (isset($it['text'])) return $it['text'] == $item;
            })->first();
            if ($dict) {
                if (isset($dict->value)) $value = $dict->value;
                elseif (isset($dict['value'])) $value = $dict['value'];
            } else $value = $item;
            $saveData[$header[$key]] = $value;
        };
        if ($saveData) return $model->create($saveData);
        return true;
    }

}