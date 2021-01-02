<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentStorageTest extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'test_payments_storage';

    protected $primaryKey = null;

    public $incrementing = false;

    protected $fillable = ['ref_number', 'customer_id', 'scheme_number', 'barcode', 'time_date', 'currency_code', 'amount', 'transaction_fee', 'acceptor_name_location_', 'payment_received', 'settlement_date', 'merchant_type', 'POS_entry_mode'];

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

    public function getCustomerAttribute()
    {
        return Customer::find($this->customer_id);
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
}
