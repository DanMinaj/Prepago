<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class DistrictHeatingUsageLog extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'district_heating_usage_logs';

    public $timestamps = false;

    public static function log($mID, $msg)
    {
        $customer = Customer::where('meter_ID', $mID)->first();
        if (! $customer) {
            return;
        }

        $meter = DistrictHeatingMeter::where('meter_ID', $mID)->first();
        if (! $meter) {
            return;
        }

        $log = new self();
        $log->customer_ID = $customer->id;
        $log->meter_number = $meter->meter_number;
        $log->permanent_meter_ID = $meter->permanent_meter_ID;
        $log->log = $msg;
        $log->timestamp = date('Y-m-d H:i:s');
        $log->save();
    }
}
