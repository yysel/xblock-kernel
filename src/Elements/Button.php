<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-3
 * Time: 上午12:23
 */

namespace XBlock\Kernel\Elements;


use XBlock\Kernel\Elements\Buttons\BaseButton;
use XBlock\Kernel\Elements\Buttons\SolidIcon;
use XBlock\Kernel\Elements\Buttons\Icon;
use XBlock\Kernel\Elements\Buttons\Large;
use XBlock\Kernel\Elements\Buttons\Link;
use XBlock\Kernel\Elements\Buttons\Small;
use XBlock\Kernel\Elements\Buttons\SwitchButton;
use XBlock\Kernel\Elements\Buttons\SwitchIcon;

class Button extends Element
{
    const INNER = 'inner';
    const TOP = 'top';

    static public function large($index, $title = null): BaseButton
    {
        return Large::make($index, $title);
    }

    static public function link($index, $title = null): BaseButton
    {
        return Link::make($index, $title);
    }


    static public function small($index, $title = null): BaseButton
    {
        return Small::make($index, $title);
    }

    static public function icon($index, $title = null, $icon = null): BaseButton
    {
        return Icon::make($index, $title)->icon($icon);
    }
    static public function filledIcon($index, $title = null, $icon = null): BaseButton
    {
        return SolidIcon::make($index, $title)->icon($icon);
    }

    static public function switch($index, $title = ''): BaseButton
    {
        return SwitchButton::make($index, $title);
    }

    static public function switchIcon($index, $title = ''): BaseButton
    {
        return SwitchIcon::make($index, $title);
    }


    static public function add($component = 'large'): BaseButton
    {
        return Large::make('add', '新增')
            ->component($component)
            ->icon('plus');
    }

    static public function delete($component = 'small'): BaseButton
    {
        return Small::make('delete', '删除')
            ->component($component)
            ->icon('delete')
            ->confirm()
            ->color('#F85054');
    }

    static public function edit($component = 'small'): BaseButton
    {
        return Small::make('edit', '编辑')
            ->component($component)
            ->icon('edit');
    }

    static public function export($component = 'large'): BaseButton
    {
        return Large::make('export', '导出')
            ->component($component)
            ->icon('download')
            ->color('#009966');
    }

    static public function import($component = 'large'): BaseButton
    {
        return Large::make('import', '导入')
            ->component($component)
            ->icon('download')
            ->color('#009966');
    }
}