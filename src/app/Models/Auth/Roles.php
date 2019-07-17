<?php

namespace App\Models\Auth;

use App\Admin;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    protected $table = 'roles';

    public function admins()
    {
        return $this->belongsToMany(Admin::class, 'admin_role', 'roleid', 'adminid');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permissions::class, 'role_permission',
            'roleid', 'permissionid')
            ->withTimestamps();
    }
}
