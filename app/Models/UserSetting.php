<?php

class UserSetting extends Eloquent
{
    protected $table = 'users_settings';

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $guarded = ['id'];
}
