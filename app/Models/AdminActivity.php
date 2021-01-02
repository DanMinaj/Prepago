<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class AdminActivity extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tracking_admin_activity';

    public $timestamps = false;
}
