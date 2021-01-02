<?php

class BackupRestoreLog extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'backup_restore_logs';

    public $timestamps = false;
}
