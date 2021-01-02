<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: Mariana
 * Date: 11-Nov-15
 * Time: 9:53 PM.
 */
class RemoteControlLogging extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'remote_control_logging';

    public $timestamps = false;
}
