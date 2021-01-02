<?php
use Illuminate\Database\Eloquent\Model;

class EVRechargeReport extends Model
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
