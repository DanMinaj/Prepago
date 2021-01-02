<?php
use Illuminate\Database\Eloquent\Model;

class CronjobLog extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cronjobs_logs';

    public $timestamps = false;
}
