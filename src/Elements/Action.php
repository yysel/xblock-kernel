<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-3
 * Time: 上午12:23
 */

namespace XBlock\Kernel\Elements;


use XBlock\Kernel\Elements\Actions\BaseAction;
use XBlock\Kernel\Elements\Actions\SolidIcon;
use XBlock\Kernel\Elements\Actions\Icon;
use XBlock\Kernel\Elements\Actions\Large;
use XBlock\Kernel\Elements\Actions\Link;
use XBlock\Kernel\Elements\Actions\Small;
use XBlock\Kernel\Elements\Actions\SwitchButton;
use XBlock\Kernel\Elements\Actions\SwitchIcon;

class Action extends Element
{
    const INNER = 'inner';
    const TOP = 'top';

    static public function default($index, $title = null): BaseAction
    {
        return Large::make($index, $title);
    }

    static public function large($index, $title = null): BaseAction
    {
        return Large::make($index, $title);
    }

    static public function link($index, $title = null): BaseAction
    {
        return Link::make($index, $title);
    }


    static public function small($index, $title = null): BaseAction
    {
        return Small::make($index, $title);
    }

    static public function icon($index, $title = null, $icon = null): BaseAction
    {
        return Icon::make($index, $title)->icon($icon);
    }
    static public function filledIcon($index, $title = null, $icon = null): BaseAction
    {
        return SolidIcon::make($index, $title)->icon($icon);
    }

    static public function switch($index, $title = ''): BaseAction
    {
        return SwitchButton::make($index, $title);
    }

    static public function switchIcon($index, $title = ''): BaseAction
    {
        return SwitchIcon::make($index, $title);
    }


    static public function add($component = 'large'): BaseAction
    {
        return Large::make('add', '新增')
            ->component($component)
            ->icon('plus');
    }

    static public function delete($component = 'small'): BaseAction
    {
        return Small::make('delete', '删除')
            ->component($component)
            ->icon('delete')
            ->confirm()
            ->color('#F85054');
    }

    static public function edit($component = 'small'): BaseAction
    {
        return Small::make('edit', '编辑')
            ->component($component)
            ->icon('edit');
    }

    static public function export($component = 'large'): BaseAction
    {
        return Large::make('export', '导出')
            ->component($component)
            ->icon('download')
            ->color('#009966');
    }

    static public function import($component = 'large'): BaseAction
    {
        return Large::make('import', '导入')
            ->component($component)
            ->icon('download')
            ->color('#009966');
    }
}