<?php

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PermanentMeterDataMeterReadWebsite extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'permanent_meter_data_meter_read_website';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $guarded = [];

    public function scopeUnprocessed($query, $schemeNumber = null)
    {
        $query = $query->where('processed', 0);

        if (! is_null($schemeNumber)) {
            $query = $query->where('scheme_number', $schemeNumber);
        }

        return $query;
    }

    public function scopeCompleted($query)
    {
        return $query->where('complete', 1);
    }

    public function scopeUncompleted($query)
    {
        return $query->where('complete', 0);
    }

    public function isReadyForProcessing()
    {
        if (! $this->automated_by_user_ID) {
            return $this->nonAutomatedWasTakenLongEnoughAgo();
        }

        return $this->belongsToUserWithReadingsAutomationPermissions() && $this->wasTakenLongEnoughAgo();
    }

    /**
     * Copy the readings information in the permanent_meter_data_readings_all table.
     */
    public function process()
    {
        if ($this->isSuccessful()) {
            $this->copyToMeterReadingsAllTable();
        }

        $this->processed = 1;

        $this->save();
    }

    public function belongsToUserWithReadingsAutomationPermissions()
    {
        return in_array($this->automated_by_user_ID, Config::get('prepago.meter_readings_automation_users'));
    }

    /**
     * If all readings made by the current user with automation permissions are complete, go ahead and process.
     *
     * If not all readings are complete and the time the reading was taken is less than an hour ago,
     * skip this reading until the next pass of the script
     *
     * @return bool
     */
    private function wasTakenLongEnoughAgo()
    {
        $userWhoTookTheReadings = User::findOrFail($this->automated_by_user_ID);
        if ($userWhoTookTheReadings->hasAllAutomatedReadingsMarkedAsComplete()) {
            return true;
        }

        return Carbon::createFromFormat('Y-m-d H:i:s', $this->time_date) <= Carbon::now()->subHour();
    }

    private function nonAutomatedWasTakenLongEnoughAgo()
    {
        if ($this->complete == 1) {
            return true;
        }

        return Carbon::createFromFormat('Y-m-d H:i:s', $this->time_date) <= Carbon::now()->subHour();
    }

    private function isSuccessful()
    {
        return (bool) $this->complete;
    }

    private function copyToMeterReadingsAllTable()
    {
        PermanentMeterDataReadingsAll::create([
            'time_date' => $this->time_date,
            'scheme_number' => $this->scheme_number,
            'permanent_meter_id' => $this->permanent_meter_id,
            'reading1' => $this->reading,
        ]);
    }
}
