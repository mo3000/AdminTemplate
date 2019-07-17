<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class Permissions extends Model
{
    protected $table = 'permissions';

    public function roles()
    {
        return $this->belongsToMany(Roles::class, 'role_permission', 'permissionid', 'roleid')
            ->withTimestamps();
    }
}
