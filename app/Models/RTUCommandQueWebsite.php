<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class RTUCommandQueWebsite extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rtu_command_que_website';

    public $timestamps = false;

    protected $guarded = [];

    public function setPermanentMeterIdAttribute($value)
    {
        $this->attributes['permanent_meter_id'] = $value;

        $pmd = PermanentMeterData::where('ID', $value)->first();
        if ($pmd) {
            $customer = $pmd->getCustomer();
            if ($customer) {
                $this->customer_ID = $customer->id;
            }
        }
    }

    public function getShutOffEngineInitiatedIconAttribute()
    {

        /* From approx 2018-11-06 @ 20:45, the column "shut_off_engine_initiated" was created,
        so the previous rtu commands have no definable value for this since they could have been either
        initiated manually (via prepago-admin website, or via the shut off engine), so we will classify them as un-determinable.
        */

        if ($this->time_date < '2018-11-06 20:45') {
            return "<i class='fa fa-archive'></i>";
        }

        if ($this->shut_off_engine_initiated) {
            return "<i class='fa fa-check-circle'></i>";
        }

        return "<i class='fa fa-times-circle'></i>";
    }

    public function getInitiatedAttribute()
    {
        if ($this->shut_off_engine_initiated) {
            return 'shut-off engine';
        }

        if ($this->away_mode_initiated) {
            return 'away_mode';
        }

        if ($this->topup_initiated) {
            return 'top-up';
        }

        if ($this->automated_by_user_ID != null) {
            $user = User::find($this->automated_by_user_ID);
            if ($user) {
                return "<a href='".URL::to('settings/scheme_settings/manage_operator/'.$user->id)."' target='_blank'>".$user->username.'</a>';
            }
        }

        return '<center>-</center>';
    }

    public function getStyleAttribute()
    {
        if ($this->complete == 0 && $this->failed == 0) {
            return 'background-color: #ffecd2;';
        }

        if ($this->complete == 1 && $this->failed == 0) {
            return 'background-color: #62c4624d;';
        }

        if ($this->failed == 1) {
            return 'background-color: #ffd2d2;';
        }
    }
}
