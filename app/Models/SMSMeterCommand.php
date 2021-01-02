<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class SMSMeterCommand extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sms_meter_commands';

    public $timestamps = false;
}
