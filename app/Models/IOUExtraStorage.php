<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IOUExtraStorage extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'iou_extra_storage';

    protected $fillable = ['customer_id', 'date_time', 'scheme_number', 'charge', 'paid'];

    public $timestamps = false;
}
