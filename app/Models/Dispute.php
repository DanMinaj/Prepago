<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Dispute extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'payments_storage_disputes';

    public $timestamps = false;
}
