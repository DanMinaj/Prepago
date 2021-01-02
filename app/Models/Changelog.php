<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Changelog extends Model
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
        return $this->hasMany('App\Models\ChangelogComment', 'changelog_id', 'id');
    }
}
