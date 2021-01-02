<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerSession extends Model
{
    protected $table = 'customers_sessions';

    protected $guarded = ['id'];

    //public $timestamps = false;

    public static function generate($customer_id, $platform, $uuid, $ip)
    {
        $session = new self();
        $session->customer_id = $customer_id;
        $session->token = '';
        $session->platform = $platform;
        $session->uuid = $uuid;
        $session->ip = $ip;
        $session->save();
    }

    public static function valid($customer_id, $token, $uuid = null, $ip = null)
    {
        $session = self::where('customer_id', $customer_id)->where('token', $token)->first();

        if ($session) {
            return true;
        }

        return false;
    }
}
