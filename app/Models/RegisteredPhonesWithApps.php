<?php
use Illuminate\Database\Eloquent\Model;

class RegisteredPhonesWithApps extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'registered_phones_with_apps';

    protected $fillable = ['phone_UID', 'customer_ID', 'customers_rc_id', 'date_added', 'scheme_number',
                            'paid', 'make_model', 'charge', ];

    public $timestamps = false;

    public function scopeInScheme($query, $schemeNumber)
    {
        return $query->where('scheme_number', $schemeNumber);
    }

    public static function getNewApps($scheme, $from, $to)
    {
        if ($from >= '2020-01-01') {
            return Customer::whereRaw("(commencement_date >= '$from' AND commencement_date <= '$to' AND scheme_number = '$scheme')")->count();
        }

        return self::inScheme($scheme)
                ->whereBetween('date_added', [$from, $to])
                ->count();
    }
}
