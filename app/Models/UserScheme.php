<?php

use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Auth\UserInterface;
use Illuminate\Support\Facades\Config;

class UserScheme extends Eloquent
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
