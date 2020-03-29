<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-11-24
 * Time: 下午10:46
 */

namespace XBlock\Kernel\Services;


use Illuminate\Support\Facades\Cache;
use XBlock\Helper\Tool;

class BlockService
{
    public function findBlockClass($block_name)
    {
        $block_list = $this->findBlockClassList();
        return isset($block_list[$block_name]) ? $block_list[$block_name] : null;
    }

    public function findBlockClassFormCache($block_name)
    {
        return Cache::remember('block_class_name', 24 * 3600, function () use ($block_name) {
            return $this->findBlockClass($block_name);
        });
    }

    public function findBlockClassList()
    {
        $files = $this->getAllBlockFiles();
        $class = [];
        foreach ($files as $key => $file) {
            $class[Tool::unpascal($key)] = $this->getNameSpaceFormFile($file);
        }
        return $class;
    }

    public function getNameSpaceFormFile($file)
    {
        return ucfirst(strtr($file, [base_path() . '/' => '', '.php' => '', '/' => '\\', '//' => '\\']));
    }

    public function getAllBlockFiles()
    {
        $all_paths = $this->getAllBlockPaths();
        $block_lists = [];
        foreach ($all_paths as $path) {
            $block_lists = array_merge($block_lists, Tool::readDir($path, 'file'));
        }
        return $block_lists;
    }

    public function getAllBlockPaths()
    {
        $block_path = config('xblock.block_path', [base_path('app/Blocks')]);
        $path_lists = [];
        foreach ($block_path as $path) {
            if (strpos($path, '*') === false) $path_lists[] = $path;
            else $path_lists = array_merge($path_lists, $this->scanFuzzyPath($path));
        }
        return $path_lists;
    }

    public function scanFuzzyPath($path)
    {
        list($base, $inner) = explode('*', $path);
        $paths = [];
        $second_paths = Tool::readDir($base, 'dir');
        foreach ($second_paths as $second_path) {
            $paths[] = rtrim($second_path . $inner, '/');
        }
        return $paths;
    }
}