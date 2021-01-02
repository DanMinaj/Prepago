<?php

namespace App\Models;

class Stripe
{
    public static $mode = 'live';
    public static $stripe_api_ver = '2019-10-08';

    public static $publicLiveKey = 'pk_live_F53iYKOgeiOCKvMOeDfKjB7F00eRoC0YdI';
    public static $secretLiveKey = 'sk_live_2nzV6nJbXai53kWCh674VZ2O008ZLMCIXk';

    public static $publicTestKey = 'pk_test_Ps1j65qYWE2ubRu64EfFhUzw00cMBwltBi';
    public static $secretTestKey = 'sk_test_Uez27VRzvYPK2xOHf7ivVyaU00mvqbmBut';

    /**
        Webhook secrets
     **/
    public static $verifyAppPayment_test = 'whsec_qfNeASk2jgViCTWkj42h2zqxmw9M5oln';
    public static $failedAppPayment_test = 'whsec_EGHyDiO3NS4ElJL7aEwkUXVy2Lb7bwhA';
    public static $confirmPaymentIntent_test = 'whsec_kjXVVP6kZ2KC2A0UIe8YJYypTYpH7SZQ';
    public static $addStripeCustomer_test = 'whsec_O5C0LFI8V4BYbj5wVjeZa9PxRErFxNSN';
    public static $addPaymentSource_test = 'whsec_ujd3DEtJhMrXticnVgaYV28yaWJbt18x';
    public static $addPaymentIntent_test = 'whsec_EDOL5u6fShVO8FkHvNVl6EGcByxETws5';
    public static $removeStripeCustomer_test = 'whsec_IsHmZwgfhAAcyWJbjlAH26zBFCyAdu0L';
    public static $removePaymentSource_test = 'whsec_QvhSczqTMr33C13esXEJ1i3XneKW3UkT';
    public static $removePaymentIntent_test = 'whsec_iaGJhKY0xjTSVM8TnH11Q9aNLAA3hIcj';

    public static $verifyAppPayment = 'whsec_I0IKptYQX9NJxQkehB4LGgMTKcoK26oV';
    public static $failedAppPayment = '';
    public static $confirmPaymentIntent = '';
    public static $addStripeCustomer = 'whsec_V7s4C15nHWPxNsYRDMBgvXhlcZN6csYK';
    public static $addPaymentSource = 'whsec_I0Zp7qCewvlEpe4dyqH1XaNwmMzRdAnk';
    public static $addPaymentIntent = '';
    public static $removeStripeCustomer = 'whsec_ppdU9PMekPkedV6CoQOnyhurrbfbRb5C';
    public static $removePaymentSource = 'whsec_x1JZ3q6JJsX1VEjOU4CM4s7dBkzKrgZV';
    public static $removePaymentIntent = '';

    public static function start()
    {
        if (self::$mode == 'test') {
            \Stripe\Stripe::setApiKey(self::$secretTestKey);
        } else {
            \Stripe\Stripe::setApiKey(self::$secretLiveKey);
        }
    }

    public static function getPendingBalance()
    {
        $balance = \Stripe\Balance::retrieve();

        $pending = $balance->pending;
        $amount = 0;

        foreach ($pending as $k => $p) {
            $amount += ($p->amount / 100);
        }

        return $amount;
    }

    public static function getPayouts($num = 0, $pending_only = true)
    {
        if ($num == 0) {
            $payouts = \Stripe\Payout::all(['limit' => 100]);
        } else {
            $payouts = \Stripe\Payout::all(['limit' => $num]);
        }

        $ret = [];
        foreach ($payouts as $p) {
            if ($pending_only && $p->status != 'in_transit' && $p->status != 'pending') {
                continue;
            }

            if ($num > 0) {
                $arrival = Carbon\Carbon::createFromTimestamp($p->arrival_date)->format('d M Y');
                array_push($ret, (object) [
                    'amount' => ($p->amount / 100),
                    'status' => $p->status,
                    'arrival_date' => $arrival,
                ]);

                $num--;
            }
        }

        return $ret;
    }

    public static function getAutotopupPlan()
    {
        $plans = self::getPlans();

        return $plans[0]->id;
    }

    public static function getPlans()
    {
        $plans = \Stripe\Plan::all(['limit' => 100]);
        $plans = $plans->data;

        $return = [];

        foreach ($plans as $k => $v) {

            //var_dump($v); echo "<hr/>";

            array_push($return, (object) [
                'id' => $v->id,
                'product_id' => $v->product,
                'amount' => $v->amount / 100,
                'name' => $v->nickname,
                'interval' => $v->interval,
            ]);
        }

        if (count($return) == 0) {
            $new_plan = self::createPlan('SnugZone Autopup', 'snugzone_1', 3, 'Monthly');

            return self::getPlans();
        }

        return $return;
    }

    public static function createPlan($product, $unique_name_id, $amount, $nickname, $interval = 'month')
    {
        $plan = \Stripe\Plan::create([
          'amount' => $amount,
          'interval' => $interval,
          'nickname' => $nickname,
          'product' => [
            'name' => $product,
          ],
          'currency' => 'eur',
          'id' => $unique_name_id,
        ]);

        return $plan;
    }

    public static function reset($testMode = true)
    {
        return 'disabled';

        if ($testMode) {
            \Stripe\Stripe::setApiKey(self::$secretTestKey);
            echo 'Reset test data';
        } else {
            \Stripe\Stripe::setApiKey(self::$secretLiveKey);
            echo 'Reset LIVE data';
        }

        StripeLog::truncate();
        StripeErrorLog::truncate();

        $customers = \Stripe\Customer::all(['limit' => 100]);
        foreach ($customers->data as $customer) {
            $c = \Stripe\Customer::retrieve($customer->id);
            $c->delete();
        }
        // delete customers stripe
        StripeCustomer::truncate();
        StripePaymentSource::truncate();
        StripeCustomerPayment::truncate();
        StripeCustomerFailedPayment::truncate();
        // delete customers' sources

        $subscriptions = \Stripe\Subscription::all(['limit' => 100]);
        foreach ($subscriptions->data as $subscription) {
            $sub = \Stripe\Subscription::retrieve($subscription->id);
            $sub->cancel();
        }
        StripeCustomerSubscription::truncate();
        // delete customers subscriptions

        $plans = \Stripe\Plan::all(['limit' => 100]);
        foreach ($plans->data as $plan) {
            $p = \Stripe\Plan::retrieve($plan->id);
            $p->delete();
        }

        $products = \Stripe\Product::all(['limit' => 100]);
        foreach ($products->data as $product) {
            $p = \Stripe\Product::retrieve($product->id);
            $p->delete();
        }

        $intents = \Stripe\PaymentIntent::all(['limit' => 100]);
        foreach ($intents->data as $intent) {
            $p = \Stripe\PaymentIntent::retrieve($intent->id);
            if ($p->status != 'canceled' && $p->status != 'succeeded') {
                $p->cancel([
                    'cancellation_reason' => 'abandoned',
                ]);
            }
        }
    }
}
