<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SystemStat extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'system_stats';

    public static function get($setting)
    {
        return self::first()->$setting;
    }

    public static function set($setting, $value)
    {
        DB::table('system_stats')->update([$setting => $value]);
    }
}
