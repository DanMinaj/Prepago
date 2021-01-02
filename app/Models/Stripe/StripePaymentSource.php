<?php

namespace App\Models\Stripe;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;


class StripePaymentSource extends Model
{
    protected $table = 'customers_stripe_sources';

    public function getCustomerAttribute()
    {
        return Customer::find($this->customer_id);
    }
}
