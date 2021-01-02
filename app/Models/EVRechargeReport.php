<?php

class EVRechargeReport extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ev_recharge_reports';

    protected $guarded = ['id'];

    public $timestamps = false;

    public function scopeWithEVMeterID($query, $meterID)
    {
        return $query->where('ev_meter_ID', $meterID);
    }
}
