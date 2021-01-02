<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorporateCitizenship extends Model
{
    protected $table = 'corporate_citizenship_initiative';

    protected $primaryKey = 'id';

    protected $guarded = ['id'];
}
