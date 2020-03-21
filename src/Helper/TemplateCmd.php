<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-12
 * Time: 下午5:32
 */

namespace XBlock\Kernel\Helper;

use Core\Common\Models\Address;
use Illuminate\Console\Command;
use XBlock\Kernel\Services\BlockService;

class TemplateCmd extends Command
{
    protected $signature = 'xblock:temp {name}';
    protected $description = '创建block模板！';
    protected $ser;

    public function __construct()
    {
        parent::__construct();
        $this->ser = new BlockService();
    }

    public function handle()
    {
        $module_list = $this->getModule();
        $block = $this->argument('name');
        $module = count($module_list) > 1 ? $this->choice('What is your name?', $module_list, $module_list[0]) : $module_list[0];
        if ($block && $module) {
            $factory = new  FileFactory();
            $res = $factory->makeBlockConfig($block, $module, $this->ser->getNameSpaceFormFile($module));
            if ($res === true) $this->info('创建成功！');
            else $this->error($res);
        }

    }


    protected function getModule()
    {
        $ser = new BlockService();
        return $ser->getAllBlockPaths();
    }


}