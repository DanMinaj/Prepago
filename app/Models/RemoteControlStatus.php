<?php
use Illuminate\Database\Eloquent\Model;

class RemoteControlStatus extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'remote_control_status';
    protected $primaryKey = 'permanent_meter_id';

    protected $fillable = ['permanent_meter_id',
                            'heating_on',
                            'heating_turned_on_at_datetime',
                            'heating_to_be_turned_off_at_datetime',
                            'away_mode_on',
                            'away_mode_end_datetime',
                            'away_mode_permanent',
                            'away_mode_relay_status',
                            'away_mode_cancelled',
                            'heating_boost_on',
                            'heating_boost_end_datetime',
                            'heating_boost_cancelled',
                            'user_change_notification',
                          ];

    public $timestamps = false;

    public function getLastStartAttribute()
    {
        $log = RemoteControlLogging::where('permanent_meter_id', $this->permanent_meter_id)->where('action', 'like', '%Away Mode Starting%')->orderBy('id', 'DESC')->first();

        return $log;
    }

    public function getLastEndAttribute()
    {
        $log = RemoteControlLogging::where('permanent_meter_id', $this->permanent_meter_id)->where('action', 'like', '%Away Mode Ending%')->orderBy('id', 'DESC')->first();

        return $log;
    }
}
