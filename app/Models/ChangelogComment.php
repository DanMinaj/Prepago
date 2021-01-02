<?php

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
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
