<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeterLookup extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'meter_lookup';

    public function applied($scheme_number)
    {
        $scheme = Scheme::find($scheme_number);
        if (! $scheme) {
            return;
        }

        if (strpos($this->applied_schemes, $scheme_number) !== false) {
            return true;
        }

        return false;
    }

    public function remove($scheme_number)
    {
        $this->applied_schemes = str_replace(' '.$scheme_number, '', $this->applied_schemes);
        $this->applied_schemes = str_replace($scheme_number, '', $this->applied_schemes);
        $this->save();
    }

    public function add($scheme_number)
    {
        if (empty(str_replace(' ', '', $this->applied_schemes))) {
            $this->applied_schemes = $scheme_number;
        } else {
            $this->applied_schemes = $this->applied_schemes.' '.$scheme_number;
        }

        $this->save();
    }

    public static function mass_add($scheme_number, $arr)
    {
        foreach (self::all() as $m) {
            if (! in_array($m->id, $arr) && $m->applied($scheme_number)) {
                $m->remove($scheme_number);
                continue;
            }

            if (in_array($m->id, $arr) && ! $m->applied($scheme_number)) {
                $m->add($scheme_number);
                continue;
            }
        }
    }
}
