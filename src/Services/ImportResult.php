<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 20-3-31
 * Time: 下午8:56
 */

namespace XBlock\Kernel\Services;


use XBlock\Kernel\Blocks\CollectionBlock;
use XBlock\Kernel\Elements\Component;
use XBlock\Kernel\Elements\Field;

class ImportResult extends CollectionBlock
{
    public $primary_key = 'import_line';

    public function component()
    {
        return Component::table()->border();
    }

    final public function setContent($content)
    {
        $this->content = $content;
    }

    final public function setHeader(array $header)
    {
        $this->fields = collect(array_merge($header, [
            Field::text('import_line', '失败行号'),
            Field::text('import_error', '失败原因'),
        ]));

    }
}