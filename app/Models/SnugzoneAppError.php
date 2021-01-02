<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SnugzoneAppError extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'snugzone_app_error_logs';

    public static function log($log, $file, $customer_id)
    {
        $log = new self();
        $log->log = $log;
        $log->file = $file;
        $log->customer_id = $customer_id;
        $log->save();

        return $log;
    }
}
