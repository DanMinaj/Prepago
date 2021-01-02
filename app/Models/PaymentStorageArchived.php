<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentStorageArchived extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'payments_storage_archived';

    public $timestamps = false;
}
