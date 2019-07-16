<?php

namespace App;

use App\Models\AdminNotification;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
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

    protected $fillable = [
        'name', 'password',
    ];

    protected $hidden = [
        'password',
    ];
}
