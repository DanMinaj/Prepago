<?php
use Illuminate\Database\Eloquent\Model;

class TrackingCustomerActivity extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tracking_customer_activity';

    protected $primaryKey = 'id';

    public $timestamps = false;
}
