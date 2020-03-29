<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-3
 * Time: 上午12:23
 */

namespace XBlock\Kernel\Elements;


use XBlock\Kernel\Elements\Fields\BaseField;
use XBlock\Kernel\Elements\Fields\CascadeMulti;
use XBlock\Kernel\Elements\Fields\CascadeCheckBox;
use XBlock\Kernel\Elements\Fields\Cascade;
use XBlock\Kernel\Elements\Fields\Checkbox;
use XBlock\Kernel\Elements\Fields\Date;
use XBlock\Kernel\Elements\Fields\Editor;
use XBlock\Kernel\Elements\Fields\Month;
use XBlock\Kernel\Elements\Fields\Password;
use XBlock\Kernel\Elements\Fields\Select;
use XBlock\Kernel\Elements\Fields\Radio;
use XBlock\Kernel\Elements\Fields\SelectMulti;
use XBlock\Kernel\Elements\Fields\SwitchField;
use XBlock\Kernel\Elements\Fields\Text;
use XBlock\Kernel\Elements\Fields\TextArea;
use XBlock\Kernel\Elements\Fields\Upload;

class Field
{

    static public function uuid(): Text
    {
        return Text::make('uuid', 'UUID')->invisible()->disExportable()->disImportable();
    }

    static public function key($key = 'id'): Text
    {
        return Text::make($key, strtoupper($key))->invisible()->disExportable()->disImportable();
    }

    static public function text($index, $title = null): Text
    {
        return Text::make($index, $title);
    }

    static public function password($index, $title = null): Password
    {
        return Password::make($index, $title);
    }


    static public function textArea($index, $title = null): TextArea
    {
        return TextArea::make($index, $title);
    }

    static public function editor($index = null, $title = null): Editor
    {
        return Editor::make($index, $title);
    }

    static public function radio($index = null, $title = null): Radio
    {
        return Radio::make($index, $title);
    }

    static public function select($index = null, $title = null): Select
    {
        return Select::make($index, $title);
    }

    static public function selectMulti($index = null, $title = null): SelectMulti
    {
        return SelectMulti::make($index, $title);
    }

    static public function date($index, $title = null): Date
    {
        return Date::make($index, $title);
    }

    static public function month($index, $title = null): Month
    {
        return Month::make($index, $title);
    }

    static public function switch($index, $title = null): SwitchField
    {
        return SwitchField::make($index, $title);
    }


    static public function checkbox($index, $title = null): Checkbox
    {
        return Checkbox::make($index, $title);
    }


    static public function upload($index, $title = null): BaseField
    {
        return Upload::make($index, $title)->disExportable()->disImportable();
    }

    static public function cascadeRadio($index, $title = null): Cascade
    {
        return Cascade::make($index, $title);
    }

    static public function cascadeCheckbox($index, $title = null): CascadeMulti
    {
        return CascadeMulti::make($index, $title);

    }

    static public function cascadeCheckboxAllNode($index, $title = null): CascadeCheckBox
    {
        return CascadeCheckBox::make($index, $title);
    }

    static public function make($input, $index, $title): BaseField
    {
        return BaseField::fill(compact('input', 'index', 'title'));
    }


}