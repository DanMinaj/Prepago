<?php

class StripeCustomerSubscription extends Eloquent
{
    protected $table = 'customers_stripe_subs';

    protected $appends = ['start', 'end', 'force_end'];

    public function getCustomerAttribute()
    {
        return Customer::find($this->customer_id);
    }

    public function getForceEndAttribute()
    {
        return Carbon\Carbon::parse($this->force_end_at)->diffForHumans();
    }

    public function getStartAttribute()
    {
        return Carbon\Carbon::parse($this->start_at)->diffForHumans();
    }

    public function getEndAttribute()
    {
        return Carbon\Carbon::parse($this->end_at)->diffForHumans();
    }

    public static function APIInvoiceTotal($start, $end)
    {
        $obj = (object) [
            'amount' => 0,
            'count' => 0,
        ];

        $start = (new DateTime($start))->format('Y-m').'-01 00:00:00';
        $end = (new DateTime($end))->format('Y-m-t').' 23:59:59';
        $start_formatted = strtotime($start);
        $end_formatted = strtotime($end);

        Stripe::start();
        $invoices_in_range = \Stripe\Invoice::all(['status' => 'paid', 'limit' => 200, 'created' => ['gte' => $start_formatted, 'lte' => $end_formatted]]);

        foreach ($invoices_in_range->autoPagingIterator() as $k => $invoice) {
            $obj->count++;
            $obj->amount += ($invoice->amount_paid / 100);
            //echo Carbon\Carbon::createFromTimestamp($invoice->lines->data[0]->period->start) . "\n";
        }

        return $obj;
    }

    public function cancel($reason = '')
    {
        try {
            Stripe::start();

            $stripeSub = \Stripe\Subscription::retrieve($this->token);
            $stripeSub->delete();

            $this->cancellation_reason = $reason;
            $this->sent_email = 0;
            $this->active = 0;
            $this->save();

            $otherSubscriptions = self::where('customer_id', $this->customer_id)->where('id', '!=', $this->id)->get();

            foreach ($otherSubscriptions as $k => $s) {
                try {
                    $ssub = \Stripe\Subscription::retrieve($s->token);
                    $ssub->delete();
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    echo $e->getMessage().' ('.$e->getLine().')';
                } catch (Exception $e) {
                    echo $e->getMessage().' ('.$e->getLine().')';
                } finally {
                    $s->cancellation_reason = $reason;
                    $s->sent_email = 0;
                    $s->active = 0;
                    $s->save();
                }
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            echo $e->getMessage().' ('.$e->getLine().')';
        } catch (Exception $e) {
            echo $e->getMessage().' ('.$e->getLine().')';
        }
    }
}
