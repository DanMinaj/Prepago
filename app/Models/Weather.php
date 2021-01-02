<?php

class Weather extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'weather_data';

    public $timestamps = false;
}
