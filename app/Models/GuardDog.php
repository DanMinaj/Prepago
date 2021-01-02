<?php

class GuardDog extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'guarddogs';
    protected $appends = ['topups'];

    public static function execute($customer_ids = [])
    {
        $gds = [];

        foreach ($customer_ids as $k => $c_id) {
            $customer = Customer::where('username', $c_id)->first();

            if (! $customer) {
                $customer = Customer::find($c_id);
            }

            if (! $customer) {
                throw new Exception("Customer '$c_id' does not exist!");
            }

            $gd = self::whereRaw("(username = '$c_id' OR customer_id = '$c_id')")
            ->where('completed', 0)->first();

            if (! $gd) {
                $gd = new self();
            } else {
                $arr = [];
                $gd->log = serialize($arr);
                $gd->created_at = date('Y-m-d H:i:s');
            }

            $gd->customer_id = $customer->id;
            $gd->username = $customer->username;
            $gd->scheme_number = $customer->scheme_number;
            $gd->operator_id = Auth::user()->id;
            $gd->completed = 0;
            $gd->save();

            array_push($gds, $gd);
        }

        return $gds;
    }

    public static function active()
    {
        return self::where('completed', 0)
        ->orderBy('scheme_number', 'DESC')->get();
    }

    public function getTopupsAttribute()
    {
        $successful = StripeCustomerPayment::where('customer_id', $this->customer_id)
        ->whereRaw("(created_at >= '".$this->created_at."')")->get();

        $unsuccessful = StripeCustomerFailedPayment::where('customer_id', $this->customer_id)
        ->whereRaw("(created_at >= '".$this->created_at."')")->get();

        $intents = StripePaymentIntent::where('customer_id', $this->customer_id)
        ->whereRaw("(created_at >= '".$this->created_at."')")->get();

        foreach ($intents as $k => $i) {
            $p = StripeCustomerPayment::where('customer_id', $i->customer_id)->where('created_at', $i->created_at)->first();
            if (! $p) {
                $p = new StripeCustomerPayment();
                $p->status = 'abandoned';
                $p->created_at = $i->created_at;
                $p->amount = $i->amount;
                $p->description = $i->description;
                $p->time = Carbon\Carbon::parse($i->created_at)->diffForHumans();
            }
            array_push($payments, $p);
        }

        $payments = [];

        foreach ($successful as $k => $p) {
            array_push($payments, $p);
        }

        foreach ($unsuccessful as $k => $p) {
            array_push($payments, $p);
        }

        usort($payments, function ($a, $b) {
            return Carbon\Carbon::parse($a->created_at)->gt($b->created_at);
        });

        return $payments;
    }

    public function getLogAttribute()
    {
        if (strlen($this->attributes['log']) <= 1) {
            return [];
        }

        try {
            $log = unserialize($this->attributes['log']);

            return $log;
        } catch (Exception $e) {
            return [];
        }
    }
}
