<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarRemoteProgram extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'calendar_remote_programs';

    public $timestamps = false;

    public function getProgramAttribute()
    {
        if ($this->attributes['program'] == 'b') {
            return 'Billing Engine';
        }

        if ($this->attributes['program'] == 's') {
            return 'Shut-Off Engine';
        }
    }

    public function getRunningForAttribute()
    {
        if ($this->attributes['customer_ID'] != 0) {
            return 'Customer '.$this->attributes['customer_ID'];
        }

        if ($this->attributes['scheme_ID'] != 0) {
            return Scheme::find($this->attributes['scheme_ID'])->company_name;
        }

        return 'All customers';
    }

    public function setRunAfterAttribute($value)
    {
        if ($value == 0) {
            $this->attributes['run_after'] = Carbon\Carbon::now();
        } else {
            $this->attributes['run_after'] = Carbon\Carbon::now()->addSeconds($value);
        }
    }

    public function setProgramAttribute($value)
    {
        if ($value == 'Billing Engine') {
            $this->attributes['program'] = 'b';
        }

        if ($value == 'Shut-Off Engine') {
            $this->attributes['program'] = 's';
        }
    }
}
