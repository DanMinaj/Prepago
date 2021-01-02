<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistrictHeatingUsageAdvanced extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'district_heating_usage_advanced';

    protected $guarded = ['id'];

    public $timestamps = false;
}
