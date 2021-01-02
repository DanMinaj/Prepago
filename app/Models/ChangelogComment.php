<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;



class ChangelogComment extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'changelog_comments';
}
