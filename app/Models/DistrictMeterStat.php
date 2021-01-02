<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class DistrictMeterStat extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'district_heating_meters_stats';

    public $timestamps = false;
}
