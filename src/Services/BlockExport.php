<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-11-27
 * Time: 上午5:25
 */

namespace XBlock\Kernel\Services;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use XBlock\Kernel\Blocks\Block;


class BlockExport implements FromCollection, WithHeadings
{
    protected $block;
    protected $is_sample = false; //是否是导出样表

    public function __construct(Block $block)
    {
        $this->block = $block;
        $block->transform = true;
        $block->pageable = request('page', 'all') !== 'all';
        $this->is_sample = request('is_sample', false);
        $header = request('header', []);
        $block->fields = $block->getFields()->filter(function ($item) use ($header) {
            if ($this->is_sample) return $item->importable;
            return $item->exportable && (in_array($item->index, $header));
        });
    }

    public function collection()
    {
        return $this->is_sample ? collect([]) : $this->block->getContent();
    }

    public function headings(): array
    {
        return $this->block->fields->map(function ($item) {
            return $item->title;
        })->toArray();
    }


}