<?php
use Illuminate\Database\Eloquent\Model;

class TariffChanges extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tariff_changes';

    public $timestamps = false;

    public function scheme()
    {
        return $this->belongsTo('Scheme', 'scheme_number', 'scheme_number');
    }

    public function tarrif()
    {
        return $this->belongsTo('Tariff', 'scheme_number', 'scheme_number');
    }
}
