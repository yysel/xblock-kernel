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

class BlockBaseModel extends BaseModel
{
    use SoftDeletes;
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    protected $guarded = [];
    public $incrementing = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (!$this->connection) $this->connection = config('database.default');
        if ($this->connection == 'mysql' || $this->connection == 'sqlite') $this->table = str_replace('.', '_', $this->table);
    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($query) {
            $method = in_array($query->uuid_type, ['guid', 'suid', 'muid', 'luid']) ? $query->uuid_type : 'guid';
            if (!$query->uuid) $query->uuid = $method();
        });
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

}