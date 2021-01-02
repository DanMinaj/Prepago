<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UtilityNote extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'utility_notes';

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $guarded = ['id'];
}
