<?php

use Carbon\Carbon as Carbon;

class SchemeWatch extends Eloquent
{
    //protected $appends = ['permissions'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'schemes_watch';

    protected $primaryKey = 'id';

    public function getSchemeAttribute()
    {
        return Scheme::find($this->scheme_number);
    }

    public function log($log)
    {
        $logs = [];
        if (strlen($this->logs) <= 0) {
            $logs = [];
        } else {
            $logs = unserialize($this->logs);
        }

        array_push($logs, [
            'time' => date('Y-m-d H:i:s'),
            'entry' => $log,
        ]);

        $logs = serialize($logs);
        $this->logs = $logs;
        $this->save();
    }
}
