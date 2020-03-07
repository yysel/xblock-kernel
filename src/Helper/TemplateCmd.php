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

class TemplateCmd extends Command
{
    protected $signature = 'xblock:temp {name}';
    protected $description = '创建block模板！';


    public function handle()
    {
        $module_list = $this->getModule();
        $block = $this->argument('name');
        $module = $this->choice('What is your name?', $module_list, $module_list[0]);
        if ($block && $module) {
            $fileCreater = new  FileFactory($module);
            $fileCreater->makeBlockConfig($block);
            $this->info('创建成功！');
        }

    }

    public function createJson()
    {
        $res = Address::where('level', 0)->get(['area_code as value', 'name as text', 'parent_code as parent'])->toArray();
        $res1 = Address::where('level', 1)->get(['area_code as value', 'name as text', 'parent_code as parent'])->toArray();
        $res2 = Address::where('level', 2)->get(['area_code as value', 'name as text', 'parent_code as parent'])->toArray();
        $res = json_encode([$res, $res1, $res2], JSON_UNESCAPED_UNICODE);
        file_put_contents(base_path() . '/address.js', 'export default ' . $res);
    }

    protected function getModule()
    {
        $core = read_dir(core_path());
        return array_keys($core);
    }


}