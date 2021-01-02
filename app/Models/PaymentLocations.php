<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentLocations extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'payment_locations';

    public $timestamps = false;
}
