<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 18-5-4
 * Time: 下午4:53
 */

namespace XBlock\Kernel\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class BaseModel extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    protected $guarded = [];
    public $incrementing = false;
    protected $uuid_type = 'guid';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (!$this->connection) $this->connection = config('database.default');
        if ($this->connection == 'mysql' || $this->connection == 'sqlite') $this->table = str_replace('.', '_', $this->table);
    }

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($query) {
            $query->createUuid();
            if (method_exists($query, 'customCreating')) {
                $query->customCreating($query);
            };
            if (method_exists($query, 'customCreate')) {
                $query->customCreate($query);
            };
        });
    }

    protected function createUuid()
    {
        $method = in_array($this->uuid_type, ['guid', 'suid', 'muid', 'luid']) ? $this->uuid_type : 'guid';
        if (!$this->uuid) $this->uuid = $method();
    }

    public function getFields()
    {
        $table = $this->table;
        $default = config('database.default');
        $connection = config('database.connections.' . $default);
        if (isset($connection['schema'])) {
            $table = str_replace($connection['schema'] . '.', '', $table);
        }
        return Schema::getColumnListing($table);
    }

    //判断是否是彻底删除
    public function isDelete()
    {
        return is_string($this->deleted_at) ? true : false;
    }

    public static function getDict($text = 'title', $value = 'uuid', $func = null)
    {
        return static::when($func, $func)->get(["{$text} as text", "{$value} as value"]);
    }

}