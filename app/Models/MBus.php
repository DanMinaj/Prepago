<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;



class MBus
{
    // Run a scan/check to get the meters 16 digit address & insert the 8digit&16digit into the database
    public static function MeterSecondaryGrabber($sim_ip, $meter_number, $meter_make, $meter_model)
    {
        return;

        if (strpos($meter_number, '_') !== false) {
            $meter_number = explode('_', $meter_number)[1];
        }

        $eight = $meter_number;

        $sixteen = '';

        if (self::hasSecondary($meter_number)) {
            $response = [
                'message' => $meter_number.' already has a secondary address',
            ];

            exit;
        }

        // We were able to guess the 16 digit meter address from a predefined dictionary
        if (self::lookupSecondary($meter_make, $meter_model, $sixteen)) {
            $response = [
                'message' => 'The last eight digits are '.$sixteen,
                'last_eight' => $sixteen,
            ];

            self::insertSecondary($eight, $eight.''.$sixteen);

            echo json_encode($response);

            return;
        } else {
            // Put it in the lookup table
            DB::table('meter_lookup')->insert([
                'meter_make' => $meter_make,
                'meter_model' => $meter_model,
                'last_eight' => '',
                'reg_test_ip' => $sim_ip,
                'reg_test_primary' => $eight,
                'reg_completed' => false,
                'reg_date' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public static function lookupSecondary($meter_make, $meter_model, &$sixteen)
    {
        $lookupRow = DB::table('meter_lookup')
            ->where('meter_make', $meter_make)
            ->where('meter_model', $meter_model);

        if ($lookupRow->count() > 0) {
            $sixteen = $lookupRow->first()->last_eight;

            return true;
        }

        return false;
    }

    public static function insertSecondary($eight, $sixteen)
    {
        echo "Looking for $eight and $sixteen";

        $mbus_translation = MBusAddressTranslation::where('8digit', $eight)->orWhere('16digit', $sixteen)->first();
        if ($mbus_translation) {
        } else {
            DB::table('mbus_address_translations')->insert([
                '8digit' => $eight,
                '16digit' => $sixteen,
            ]);
        }
    }

    public static function hasSecondary($meter_number)
    {
        $mbus_translation = MBusAddressTranslation::where('8digit', $eight)->where('16digit', $sixteen)->first();
        if ($mbus_translation) {
            return true;
        }
    }
}
