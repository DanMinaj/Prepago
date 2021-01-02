<?php
use Illuminate\Database\Eloquent\Model;

class SMSMessage extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sms_messages';

    public $timestamps = false;

    public function scopeCharged($query)
    {
        return $query->whereRaw('(charge > 0.0 AND charge < 0.25)');
    }

    public function scopePremiumCharged($query)
    {
        return $query->whereRaw('(charge >= 0.25 AND charge <= 0.50)');
    }

    public function scopeInScheme($query, $schemeNumber)
    {
        return $query->where('scheme_number', $schemeNumber);
    }

    public static function recentlyTextedWarning($customer_id)
    {
        return self::where('customer_id', $customer_id)->where('date_time', 'like', '%'.date('Y-m-d').'%')
        ->where('message', 'like', '%You have been scheduled to shut off.%')->first();
    }
}
