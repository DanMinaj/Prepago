<?php

namespace App\Models;

use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Auth\UserInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class UserScheme extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users_schemes';

    protected $guarded = ['id'];

    public $timestamps = false;
}
