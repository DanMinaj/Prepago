<?php

class CustomerQueueLog extends Eloquent
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
