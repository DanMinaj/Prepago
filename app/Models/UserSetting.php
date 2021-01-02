<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $table = 'users_settings';

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $guarded = ['id'];
}
