<?php

class Tariff extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tariffs';

    protected $primaryKey = 'scheme_number';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    public function scheme()
    {
        return $this->belongsTo('Scheme', 'scheme_number', 'scheme_number');
    }
}
