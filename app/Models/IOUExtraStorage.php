<?php

class IOUExtraStorage extends Eloquent
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
