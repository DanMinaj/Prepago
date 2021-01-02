<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Tariff extends Model
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
        return $this->belongsTo('App\Models\Scheme', 'scheme_number', 'scheme_number');
    }
}
