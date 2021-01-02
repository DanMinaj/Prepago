<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IOUStorage extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'iou_storage';

    protected $fillable = ['customer_id', 'scheme_number', 'time_date', 'charge', 'paid'];

    public $timestamps = false;

    public function scopeInScheme($query, $schemeNumber)
    {
        return $query->where('scheme_number', $schemeNumber);
    }
}
