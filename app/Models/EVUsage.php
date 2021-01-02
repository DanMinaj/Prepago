<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class EVUsage extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ev_usage';

    protected $guarded = ['id'];

    public $timestamps = false;

    public function scopeWithEVMeterID($query, $meterID)
    {
        return $query->where('ev_meter_ID', $meterID);
    }

    public function getCustomerAttribute()
    {
        return Customer::find($this->customer_id);
    }

    public function getStationAttribute()
    {
        return EVStation::find($this->ev_meter_ID);
    }

    public function getInProgressAttribute()
    {
        $station = $this->station;

        if ($this->customer) {
            if ($station->in_use) {
                if ($station->in_use_customer = $this->customer->id && $station->id == $this->ev_meter_ID) {
                    $lastUsage = $station->lastUsage;

                    if ($lastUsage) {
                        if ($lastUsage->id == $this->id) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    public function getDurationAttribute()
    {
        $seconds = 0;

        if (strlen($this->started_at) > 3 && strlen($this->stopped_at) > 3) {
            $seconds = (Carbon\Carbon::parse($this->started_at))->diffInSeconds(Carbon\Carbon::parse($this->stopped_at));
        }

        if (strlen($this->started_at) > 3 && strlen($this->stopped_at) < 3) {
            $seconds = (Carbon\Carbon::parse($this->started_at))->diffInSeconds(Carbon\Carbon::parse(date('Y-m-d H:i:s')));
        }

        $secondsFormatted = "$seconds secs";

        if ($seconds > 60) {
            $secondsFormatted = number_format(($seconds / 60), 2).' mins';
        }

        if ($seconds > 3600) {
            $secondsFormatted = number_format(((($seconds / 60) / 60)), 2).' hrs';
        }

        return $secondsFormatted;
    }
}
