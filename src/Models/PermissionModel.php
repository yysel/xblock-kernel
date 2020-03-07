<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 18-5-27
 * Time: 下午12:04
 */

namespace XBlock\Kernel\Models;


class PermissionModel extends BlockBaseModel
{
    protected $table = 'system.permission';


    public function getSwitchStatusAttribute()
    {
        $role_id = parameter('relation_uuid', '0');
        return (boolean)RolePermission::where(['role_uuid' => $role_id, 'permission_uuid' => $this->uuid])->first();
    }

    public function role()
    {
        return $this->hasMany('XBlock\Kernel\Models\RolePermission', 'permission_uuid', 'uuid');
    }


    public static function boot()
    {
        parent::boot();

        self::creating(function ($query) {
            $uuid = guid();
            $query->uuid = $uuid;
            $role = Role::where('index', 'super_admin')->first();
            if ($role) {
                RolePermission::firstOrCreate(['role_uuid' => $role->uuid, 'permission_uuid' => $uuid]);
            }
        });
    }

    public function userUuids($where = [])
    {
        if ($where) return $this->users($where)->pluck('uuid')->toArray();
        return $this->getUserUUids();
    }

    public function getUserUUids()
    {
        $role = $this->role()->pluck('role_uuid')->toArray();
        if ($role) {
            $user_uuid = UserRole::whereIn('role_uuid', $role)->pluck('user_uuid')->toArray();
            return $user_uuid;
        }
    }

    public function users($where = [])
    {
        $uuid = $this->getUserUUids();
        return user_model()->whereIn('uuid', $uuid)->when($where instanceof \Closure, $where, function ($query) use ($where) {
            if ($where) $query->where($where);
        })->get();
    }

}