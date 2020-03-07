<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-11-22
 * Time: 上午11:15
 */

namespace XBlock\Kernel\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Laravel\Lumen\Auth\Authorizable;

class User extends BaseModel implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    protected $permission_array = [];

    protected $role_array = [];

    protected $hidden = ['password', 'created_at','updated_at'];

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($query) {
            $query->password = app('hash')->driver('bcrypt')->make($query->username);
            $query->createUuid();
            if (method_exists($query, 'customCreating')) {
                $query->customCreating($query);
            };
        });
    }

    public function getRoleAttribute(): array
    {
        if ($this->role_array) return $this->role_array;
        $role = json_decode($this->attributes['role'], true);
        return $this->role_array = $role ? $role : [];
    }

    public function getPermissionAttribute(): array
    {
        if ($this->permission_array) return $this->permission_array;
        $roles = $this->role;
        return $this->permission_array = Role::whereIn('uuid', $roles)->pluck('permission')->flatten()->unique()->values()->toArray();
    }

    public function setRoleAttribute($value)
    {
        $this->attributes['role'] = json_encode($value);
    }
}