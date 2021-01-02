<?php
use Illuminate\Database\Eloquent\Model;

class CustomerBalanceChange extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'customer_balance_changes';

    public $timestamps = false;
}
