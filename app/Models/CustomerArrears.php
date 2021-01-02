<?php
use Illuminate\Database\Eloquent\Model;

class CustomerArrears extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'customer_arrears';

    public $timestamps = false;

    protected $guarded = ['id'];
}
