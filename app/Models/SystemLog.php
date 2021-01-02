<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'system_logs';

    public static function log($type, $message)
    {
        $log = new self();
        $log->type = $type;
        $log->message = $message;
        $log->save();

        return $log;
    }
}
