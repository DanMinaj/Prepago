<?php

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Changelog extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'changelog';

    public function getProgressClassAttribute()
    {
        if ($this->progress > 80 && $this->progress <= 100) {
            return 'progress-success';
        }

        return 'progress-warning';
    }

    public function comments()
    {
        return $this->hasMany('ChangelogComment', 'changelog_id', 'id');
    }
}
