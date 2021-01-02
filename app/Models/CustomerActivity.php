<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class CustomerActivity extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tracking_customer_activity';

    public $timestamps = false;
}
