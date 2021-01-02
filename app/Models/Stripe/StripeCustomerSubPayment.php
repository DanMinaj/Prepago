<?php

namespace App\Models\Stripe;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;


class StripeCustomerSubPayment extends Model
{
    protected $table = 'customers_stripe_subs_payments';

    public function getCustomerAttribute()
    {
        return Customer::find($this->customer_id);
    }
}
