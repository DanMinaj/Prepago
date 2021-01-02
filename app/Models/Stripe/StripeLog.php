<?php

namespace App\Models\Stripe;

use Illuminate\Database\Eloquent\Model;

class StripeLog extends Model
{
    protected $table = 'stripe_logs';

    public function getLogFormattedAttribute()
    {
        $log = unserialize($this->log);
    }
}
