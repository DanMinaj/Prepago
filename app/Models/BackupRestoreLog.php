<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class BackupRestoreLog extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'backup_restore_logs';

    public $timestamps = false;
}
