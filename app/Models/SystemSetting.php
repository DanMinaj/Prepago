<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class SystemSetting extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'system_settings';

    public function getValueAttribute()
    {
        $req_url = '';
        try {
            $req_url = Request::getRequestUri();
        } catch (Exception $e) {
        }

        $value = $this->attributes['value'];

        if (strpos($req_url, 'settings/autotopup') === false) {
            $value = preg_replace_callback('/[$][a-zA-Z0-9]+/', function ($varname) {
                return self::get(str_replace('$', '', $varname[0]));
            }, $value);
        }

        return $value;
    }

    public static function get($setting)
    {
        $setting = self::where('name', $setting)->first();

        if ($setting) {
            return $setting->value;
        }

        return null;
    }

    public static function build($key, $value, $desc = null, $type = null)
    {
        $setting = new self();
        $setting->name = $key;
        $setting->value = $value;
        $setting->desc = $desc;
        $setting->type = $type;
        $setting->save();

        return $setting;
    }

    public static function modify($key, $modifying, $newvalue, $type = 'unassigned')
    {
        $setting = self::where('name', $key)->first();

        if (! $setting) {
            $setting = new self();
            $setting->name = $key;
            $setting->type = $type;
            $setting->value = $newvalue;
        }

        $setting->$modifying = $newvalue;
        $setting->save();

        return $setting;
    }
}
