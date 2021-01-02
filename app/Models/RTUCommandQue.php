<?php

class RTUCommandQue extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rtu_command_que';

    //protected $fillable = ['customer_ID', 'meter_id', 'turn_service_on', 'shut_off_device_contact_number', 'port'];
    protected $guarded = ['ID'];

    public $timestamps = false;

    public static function resendOpenValve($data)
    {
        self::create($data);
    }

    public static function resendCloseValve($data)
    {
        self::create($data);
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

        return '<center>-</center>';
    }
}
