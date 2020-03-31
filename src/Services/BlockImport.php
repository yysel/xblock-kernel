<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 20-3-31
 * Time: 下午4:22
 */

namespace XBlock\Kernel\Services;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use XBlock\Helper\Response\ErrorCode;
use XBlock\Kernel\Blocks\Block;
use XBlock\Kernel\Events\CallHook;


class BlockImport implements ToArray, WithHeadingRow, WithMultipleSheets, WithChunkReading
{
    protected $block;

    protected $header = []; //拥有字典的字段

    protected $headers = [];

    protected $result;

    public function __construct(Block $block)
    {
        $header = [];
        $block->getFields()->map(function ($item) use (&$header) {
            if ($item->importable) {
                $header[$item->title] = $item;
                $this->header[$item->index] = $item;
                $this->headers[] = $item;
            }

        })->toArray();
        HeadingRowFormatter::extend('xblock', function ($value) use ($header) {
            return isset($header[$value]) ? $header[$value]->index : null;
        });
        HeadingRowFormatter::default('xblock');
        $this->block = $block;
    }


    public function array($array)
    {
        $this->result = collect($array)->map(function ($item, $key) {
            $model = $this->block->origin;
            try {
                $data = $this->formatData($item);
                if ($data) {
                    $model = new $model($data);
                    $hook = CallHook::call('beforeImport', $model, $this->block);
                    if ($hook instanceof ErrorCode) {
                        $res = [
                            'import_status' => false,
                            'import_error' => $hook->message,
                        ];
                    } else {
                        $save_res = $model->save();
                        $res = $save_res ? [
                            'import_status' => true,
                            'import_error' => null
                        ] : [
                            'import_status' => false,
                            'import_error' => '保存失败'
                        ];
                    }
                } else {
                    $res = [
                        'import_status' => false,
                        'import_error' => '未识别到该行数据',
                    ];
                }

            } catch (\Exception $exception) {
                $res = [
                    'import_status' => false,
                    'import_error' => $exception->getMessage()
                ];
            }

            $res['import_line'] = $key + 2;
            $res['import_format'] = $data;
            $import_data = $item + $res;
            return $import_data;
        });
    }


    public function headingRow(): int
    {
        return 1;
    }

    public function sheets(): array
    {
        return [$this];
    }

    public function chunkSize(): int
    {
        return 300;
    }

    public function formatData($value)
    {
        $formatData = [];
        foreach ($this->header as $header) {
            $index = $header->index;
            $dict = $header->dict;
            if (isset($value[$index])) {
                $formatData[$index] = $value[$index];
                if ($dict) {
                    if (!($dict instanceof Collection)) $dict = collect($dict);
                    if ($dict->count()) {
                        $formatDict = $dict->first(function ($item) use ($value, $header, $index) {
                            $item = (array)$item;
                            return $item['text'] === $value[$index];
                        });
                        if ($formatDict) $formatData[$index] = $formatDict['value'];
                    }
                }
            }
        }

        return $formatData;
    }

    public function getResult(): Collection
    {
        return $this->result;
    }

    public function getSuccess(): Collection
    {
        return $this->result->filter(function ($item) {
            return $item['import_status'];
        });
    }

    public function getError(): Collection
    {
        return $this->result->filter(function ($item) {
            return !$item['import_status'];
        });
    }

    public function getHeaders()
    {
        return $this->headers;
    }

}