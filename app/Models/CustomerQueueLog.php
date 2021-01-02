<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerQueueLog extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'customers_queue_logs';

    public static function log($type, $log, $queue_id = null)
    {
        $entry = new self();
        $entry->queue_id = $queue_id;
        $entry->type = $type;
        $entry->log = $log;
        $entry->save();

        return $entry;
    }
}
