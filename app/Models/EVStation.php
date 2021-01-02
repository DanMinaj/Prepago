<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class EVStation extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ev_stations';

    protected $guarded = ['id'];

    //public $timestamps = false;

    public function getLastCustomerAttribute()
    {
        return Customer::find($this->last_in_use_customer);
    }

    public function getLastUsageAttribute()
    {
        return EVUsage::where('ev_meter_ID', $this->id)->orderBy('id', 'DESC')->first();
    }
}
