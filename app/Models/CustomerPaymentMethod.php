<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class CustomerPaymentMethod extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'customers_stripe_sources';
}
