<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Weather extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'weather_data';

    public $timestamps = false;
}
