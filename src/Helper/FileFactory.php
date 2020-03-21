<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 2017-07-15
 * Time: 20:08
 */

namespace XBlock\Kernel\Helper;


class FileFactory
{
    protected $core_base_path;
    protected $core_name;
    protected $core_path;
    protected $app_path;
    protected $app_name;
    protected $space;
    protected $file_contents;
    protected $count = 0;


    public function __construct($app_name = '')
    {
        $this->oldumask = umask(0);
        $this->app_name = $app_name;
        $this->core_name = config('kernel.core.name', 'core');
        $this->core_base_path = config('kernel.core.path', base_path());
        $this->space = ucfirst(strtolower(strtr($this->core_base_path, [base_path() => '', '/' => '', '\\' => ''])));
        if ($this->space) $this->space = $this->space . '\\';
        $this->core_path = $this->core_base_path . '/' . $this->core_name;
        $this->file_contents = new FillContent();
    }

    public function makeDirIfNotExist($path, $model = 0766, $r = true)
    {
        if (!is_dir($path)) {
            return mkdir($path, $model, $r);
        }
        return false;
    }

    public function getCoreName()
    {
        return $this->core_name;
    }

    public function getCorePath()
    {
        return $this->core_base_path;
    }

    public function getSpace()
    {
        return $this->space;
    }

    protected function getAppDirPath()
    {
        return $app_dir = $this->core_path . '/' . $this->app_name;
    }

    public function makeBlocksDir()
    {
        $app_dir = $this->getAppDirPath();

        return $this->makeDirIfNotExist($app_dir . '/Blocks', 0777);
    }


    public function makeBlockConfig($name,$path , $namespace)
    {
        try {
            if (is_file(rtrim($path) . '/' . $name . '.php')) return '创建失败，配置文件已存在！';
            $model = $this->checkDir($path)->makeFileIfNotExsit(rtrim($path) . '/' . $name . '.php');
            $content = $this->file_contents::BlockConfig;
            $content = strtr($content, ['{namespace}' => $namespace, '{name}' => $name]);
            if (fwrite($model, $content)) return true;
        } catch (\Exception $exception) {
            return '创建失败：' . $exception->getMessage();
        }
    }

    public function checkDir($dir)
    {
        is_dir($dir) || $this->makeBlocksDir();
        return $this;
    }


    protected function makeFileIfNotExsit($name)
    {
//        $mode = file_exists($name) ? 'a' : 'w';

        return $file = fopen($name, $mode = 'w');
    }

    public function updateComposer()
    {
        $composer = (json_decode(file_get_contents(base_path('composer.json')), true));
        $composer['autoload']['psr-4'][ucfirst(strtolower($this->core_name)) . '\\'] = $this->core_name . '/';
        $composer = json_encode($composer, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents(base_path('composer.json'), $composer);
        return true;
    }

    public function makeUserModels()
    {
        $app_dir = $this->getAppDirPath();
        $this->makeDirIfNotExist($app_dir . '/Models', 0777);
        $core_name = ucfirst(strtolower($this->core_name));
        $namespace = $core_name . '\\' . $this->app_name . '\Models';
        $name = 'User';
        $path = $this->getAppDirPath() . '/Models';
        $model = $this->checkDir($path)->makeFileIfNotExsit(rtrim($path) . '/' . $name . '.php');
        $content = $this->file_contents::UserModel;
        $content = strtr($content, ['{namespace}' => $namespace, '{name}' => $name,]);
        return fwrite($model, $content);
    }


    public function makeFieldMigration($table, $field, $content, $type, $action = 'field')
    {
        $file_table = str_replace('.', '_', $table);
        $file_name = date('Y_m_d_') . substr(time(), 4, 6) . '_' . $type . '_' . $file_table . '_' . $field . '_' . $action . '.php';
        $class_name = pascal($type . '_' . $file_table . '_' . $field . '_' . $action);
        $action = $action == 'table' ? 'create' : 'table';
        return $this->makeMigration($file_name, 'Migration', ['{MigrationClassName}' => $class_name, '{action}' => $action, '{table}' => $table, '{content}' => $content]);
    }

    public function makeCreateTableMigration($table, $content)
    {
        $file_table = str_replace('.', '_', $table);
        $file_name = date('Y_m_d_') . substr(time(), 4, 6) . "_create_{$file_table}_table.php";
        $class_name = pascal("create_{$file_table}_table");
        return $this->makeMigration($file_name, 'Migration', ['{MigrationClassName}' => $class_name, '{action}' => 'create', '{table}' => $table, '{content}' => $content]);
    }

    public function makeDropTableMigration($table)
    {
        $file_table = str_replace('.', '_', $table);
        $file_name = date('Y_m_d_') . substr(time(), 4, 6) . "_drop_{$file_table}_table.php";
        $class_name = pascal("drop_{$file_table}_table");
        return $this->makeMigration($file_name, 'DropTable', ['{MigrationClassName}' => $class_name, '{table}' => $table]);
    }


    public function makeMigration($file_name, $key, Array $map = [])
    {
        $migration_path = database_path('migrations');
        $this->makeDirIfNotExist($migration_path, 0777);
        $filepath = rtrim($migration_path) . '/' . $file_name;
        $file = $this->makeFileIfNotExsit($filepath);
        $file_content = $this->file_contents->{$key};
        $content = $map ? strtr($file_content, $map) : $file_content;
        if (fwrite($file, $content)) return $file_name;
    }

    public function __destruct()
    {
        umask($this->oldumask);
    }

    function insertToStr($str, $i, $substr)
    {
        //指定插入位置前的字符串
        $startstr = "";
        for ($j = 0; $j < $i; $j++) {
            $startstr .= $str[$j];
        }
        //指定插入位置后的字符串
        $laststr = "";
        for ($j = $i; $j < strlen($str); $j++) {
            $laststr .= $str[$j];
        }
        //将插入位置前，要插入的，插入位置后三个字符串拼接起来
        return $startstr . $substr . $laststr;
    }

    public function strEndPlace($str, $substr)
    {
        $star = strpos($str, $substr);
        return $star + strlen($substr);
    }
}