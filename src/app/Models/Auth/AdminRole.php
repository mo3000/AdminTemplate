<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AdminRole extends Pivot
{
    protected $table = 'admin_role';
    protected $foreignKey = 'adminid';
    protected $relatedKey = 'roleid';

}
