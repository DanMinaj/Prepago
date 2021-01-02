<?php

class DataLogger extends Eloquent
{
    protected $table = 'data_loggers';

    public $timestamps = false;

    public function getSIMAttribute()
    {
        return Simcard::where('ID', $this->sim_id)->first();
    }
}
