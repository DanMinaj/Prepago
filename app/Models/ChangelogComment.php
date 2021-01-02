<?php

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class ChangelogComment extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'changelog_comments';
}
