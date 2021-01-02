<?php
use Illuminate\Database\Eloquent\Model;

class StripeErrorLog extends Model
{
    protected $table = 'stripe_error_logs';

    public static function log($type, $msg)
    {
        $log = new self();
        $log->type = $type;
        $log->log = $msg;
        $log->save();

        return $log;
    }
}
