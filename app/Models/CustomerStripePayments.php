<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class CustomerStripePayments extends Model
{
    public static function getPendingNotifications($customer_id)
    {
        try {
            $successful = DB::table('customers_stripe_payments')
            ->where('customer_id', $customer_id)
            ->where('notified_customer', 0)
            ->get();

            $unsuccessful = DB::table('customers_stripe_failed_payments')
            ->where('customer_id', $customer_id)
            ->where('notified_customer', 0)
            ->get();

            return [
                'successful' => $successful,
                'unsuccessful' => $unsuccessful,
            ];
        } catch (Exception $e) {
            return [
                'error' => 'Could not grab: '.$e->getMessage(),
            ];
        }
    }
}
