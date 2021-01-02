<?php
use Illuminate\Database\Eloquent\Model;

class DataLogger extends Model
{
    protected $table = 'data_loggers';

    public $timestamps = false;

    public function getSIMAttribute()
    {
        return Simcard::where('ID', $this->sim_id)->first();
    }
}
