<?php

namespace App;

use App\Models\AdminNotification;
use App\Models\Auth\AdminRole;
use App\Models\Auth\Roles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $table = 'admins';
    /**
     * Get the entity's notifications.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function notifications()
    {
        return $this->morphMany(AdminNotification::class, 'notifiable')
            ->orderBy('created_at', 'desc');
    }

    protected $guarded = [];

    protected $hidden = [
        'password',
    ];

    //具有角色
    public function hasRole(string $rolename) : bool
    {
		return $this->roles()
			->where('roles.name', $rolename)
			->exists();
    }

    public function roles()
    {
        return $this->belongsToMany(Roles::class, 'admin_role', 'adminid', 'roleid');
    }
}
