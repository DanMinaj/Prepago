<?php

namespace App\Models\Stripe;

use Illuminate\Database\Eloquent\Model;


class StripePaymentIntent extends Model
{
    protected $table = 'customers_stripe_payment_intents';
}
