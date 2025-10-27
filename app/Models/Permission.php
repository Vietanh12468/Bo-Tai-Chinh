<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'routes',
        'description',
    ];

    protected $casts = [
        'routes' => 'array',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_permissions', 'permission_id', 'user_id')
            ->select('users.*', 'user_permissions.start_at as permission_start_at', 'user_permissions.expires_at as permission_expires_at');
    }

    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class, 'permission_id', 'id');
    }
}
