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
    public $block;

    public function __construct(Block $block)
    {
        $this->block = $block;
        $block->transform = true;
        $block->pageable = request('page', 'all') !== 'all';
        $block->header = $block->getHeader()->filter(function ( $item) {
            return $item->exportable && (in_array($item->index, request('header', [])));
        });

    }

    public function collection()
    {
        return $this->block->getContent();
    }

    public function headings(): array
    {
        return $this->block->header->map(function ($item) {
            return $item->title;
        })->toArray();
    }


}