<?php

class TemporaryPayments extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'temporary_payments';

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    public function scopeInScheme($query, $schemeNumber)
    {
        return $query->where('scheme_number', $schemeNumber);
    }

    public function scopeReadyToMove($query)
    {
        $subtract24Hours = \Carbon\Carbon::now()->subHours(24)->toDateTimeString();

        return $query->where('time_date', '<', $subtract24Hours);
    }
}
