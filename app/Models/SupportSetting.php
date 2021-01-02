<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class SupportSetting extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'support_settings';

    public $timestamps = false;

    public static function get($setting)
    {
        $setting = self::where('name', $setting)->first();

        if ($setting) {
            return $setting->value;
        }

        return 'null';
    }
}
