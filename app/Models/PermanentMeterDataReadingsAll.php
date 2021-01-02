<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermanentMeterDataReadingsAll extends Model
{
    protected $table = 'permanent_meter_data_readings_all';

    public $timestamps = false;

    protected $guarded = [];

    protected $primaryKey = 'ID';

    public function permanentMeterData()
    {
        return $this->hasOne('App\Models\PermanentMeterData', 'ID', 'permanent_meter_id');
    }

    public function scheme()
    {
        return $this->belongsTo('App\Models\Scheme', 'scheme_number', 'scheme_number');
    }
}
