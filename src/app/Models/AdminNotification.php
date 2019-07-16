<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;

class AdminNotification extends DatabaseNotification
{
    protected $table = 'admin_notification';
}
