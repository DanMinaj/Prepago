<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class PaymentStorage extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'payments_storage';

    protected $primaryKey = null;

    public $incrementing = false;

    protected $fillable = ['ref_number', 'customer_id', 'scheme_number', 'barcode', 'time_date', 'currency_code', 'amount', 'transaction_fee', 'acceptor_name_location_', 'payment_received', 'settlement_date', 'merchant_type', 'POS_entry_mode', 'balance_before', 'balance_after'];

    public $timestamps = false;

    public function scheme()
    {
        $scheme = Scheme::find($this->scheme_number);

        return $scheme;
    }

    public function customer()
    {
        $customer = Customer::find($this->customer_id);

        return $customer;
    }

    public function scopeInScheme($query, $schemeNumber)
    {
        return $query->where('scheme_number', $schemeNumber);
    }

    public static function no_months($from, $to)
    {
        $fromYear = date('Y', strtotime($from));
        $fromMonth = date('m', strtotime($from));
        $toYear = date('Y', strtotime($to));
        $toMonth = date('m', strtotime($to));
        if ($fromYear == $toYear) {
            return ($toMonth - $fromMonth) + 1;
        } else {
            return (12 - $fromMonth) + 1 + $toMonth - 1;
        }
    }

    public function getAcceptorNameLocationAttribute()
    {
        if (empty($this->attributes['acceptor_name_location_'])) {
            if (substr($this->ref_number, 0, 3) == 'PPR') {
                return 'paypoint';
            }
        }

        return $this->attributes['acceptor_name_location_'];
    }

    public function archive($reason = 'unspecified', $refund = false, $refund_amount = 0)
    {
        if (PaymentStorageArchived::where('ref_number', $this->ref_number)->first()) {
            return;
        }

        $psa = new PaymentStorageArchived();
        $psa->archived_reason = $reason;
        $psa->ref_number = $this->ref_number;
        $psa->customer_id = $this->customer_id;
        $psa->barcode = $this->barcode;
        $psa->time_date = $this->time_date;
        $psa->currency_code = $this->currency_code;
        $psa->amount = $this->amount;
        $psa->transaction_fee = $this->transaction_fee;
        $psa->acceptor_name_location_ = $this->acceptor_name_location_;
        $psa->payment_received = $this->payment_received;
        $psa->settlement_date = $this->settlement_date;
        $psa->merchant_type = $this->merchant_type;
        $psa->POS_entry_mode = $this->POS_entry_mode;
        $psa->balance_before = $this->balance_before;
        $psa->balance_after = $this->balance_after;
        $psa->test = 0;
        $psa->time_date_archived = date('Y-m-d H:i:s');
        $psa->refund = $refund;
        $psa->refund_amount = $refund_amount;
        if (Auth::check() && Auth::user() != null) {
            $psa->archived_by = Auth::user()->id;
        }
        $psa->save();
    }
}
