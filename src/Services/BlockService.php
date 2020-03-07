<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-11-24
 * Time: 下午10:46
 */

namespace XBlock\Kernel\Services;


class BlockService
{
    public function findBlockClass($block_name, $file_list = [])
    {
         $this->findBlockClassList();
        $block_name = pascal($block_name);
        $core = $file_list ? $file_list : read_dir(core_path());
        foreach ($core as $project => $dir) {
            $block_dir = $dir . '/Blocks';
            if (is_dir($block_dir)) {
                $block_configs = read_dir($block_dir, 'all');
                if (isset($block_configs[$block_name])) {
                    return is_dir($block_configs[$block_name]) ? '\Core\\' . $project . '\Blocks\\' . $block_name . '\Block' : '\Core\\' . $project . '\Blocks\\' . $block_name;
                }
            }
        }
    }

    public function findBlockClassList()
    {
        $core = read_dir(core_path());
        $block_list = [];
        foreach ($core as $project => $dir) {
            $block_dir = $dir . '/Blocks';
            if (is_dir($block_dir)) {
                $block_configs = read_dir($block_dir, 'all');
                foreach ($block_configs as $key => $value) {
                    if (is_dir($value)) {
                        $block_list[unpascal($key)] = '\Core\\' . $project . '\Blocks\\' . $key . '\Block';
                    } else $block_list[unpascal($key)] = '\Core\\' . $project . '\Blocks\\' . $key;
                }
            }
        }
        return $block_list;
    }
}