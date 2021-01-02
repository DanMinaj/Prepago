<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestScan extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'test_scans';

    protected $primaryKey = 'id';
}
