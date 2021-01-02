<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class CachedDistrictHeatingUsage extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cached_district_heating_usage';

    protected $primaryKey = 'id';
}
