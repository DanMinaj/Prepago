<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MBusAddressTranslation extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'mbus_address_translations';

    protected $primaryKey = '8digit';

    public $timestamps = false;

    public function getPermanentMeterAttribute()
    {
        return PermanentMeterData::whereRaw("( (meter_number LIKE '%".$this['8digit']."%') OR (scu_number LIKE '%".$this['8digit']."%') )")->first();
    }

    public function getDistrictMeterAttribute()
    {
        return DistrictHeatingMeter::whereRaw("(meter_number LIKE '%".$this['8digit']."%')")->first();
    }

    public function read($scheme_number = null, $timeout = 'never', $attempts = 3, $read = 'meter')
    {
        $elapsed = 0;
        $val = -1;
        $conversion = null;
        $temp = -1;

        try {
            $start_time = microtime(true);

            $meter = $this['8digit'];

            $dl = DataLogger::where('scheme_number', $scheme_number)->first();

            if (! $dl) {
                $potential_pmd = PermanentMeterData::whereRaw("(meter_number LIKE '%$meter%')")->orderBy('ID', 'DESC')->first();
                if ($potential_pmd) {
                    $scheme_number = $potential_pmd->scheme_number;
                    $dl = DataLogger::where('scheme_number', $scheme_number)->first();
                }
            }

            if (! $dl) {
                return false;
            }

            $districtMeter = DistrictHeatingMeter::whereRaw("(meter_number LIKE '%$meter%')")->orderBy('meter_ID', 'DESC')->first();
            $command = new PermanentMeterDataTelegramRelaycheckWebsite();
            $command->isTelegramReq = 1;
            $command->permanent_meter_id = 0;
            $command->scheme_number = $scheme_number;
            $command->ICCID = 'mbus_at';
            $command->data_logger_id = $dl->id;
            $command->meter_number = $meter;
            $command->watchdog = false;
            $command->save();

            $command = PermanentMeterDataTelegramRelaycheckWebsite::where('permanent_meter_id', 0)
            ->where('scheme_number', $scheme_number)
            ->where('ICCID', 'mbus_at')
            ->where('meter_number', $meter)
            ->orderBy('ID', 'desc')
            ->first();

            while ($command == null || ($command->fail == 0 && $command->complete == 0)) {
                sleep(1);

                if ($command == null) {
                    return;
                }

                $command = PermanentMeterDataTelegramRelaycheckWebsite::where('permanent_meter_id', 0)
                ->where('scheme_number', $scheme_number)
                ->where('ICCID', 'mbus_at')
                ->where('meter_number', $meter)
                ->orderBy('ID', 'desc')
                ->first();

                $end_time = microtime(true);
                $elapsed = ($end_time - $start_time);

                if ($timeout != 'never' && $elapsed >= $timeout) {
                    $timed_out = true;
                    break;
                }
            }

            $success = (strlen($command->telegram) > 20);

            if (! $success && $attempts > 0) {
                $attempts--;

                return $this->read($scheme_number, $timeout, $attempts);
            }

            if ($read == 'scu' && $success) {
                $xml = simplexml_load_string($command->telegram);
                foreach ($xml->DataRecord as $k => $v) {
                    if (strpos($v->Unit, 'Digital output') !== false) {
                        $val = $v->Value;
                    }
                }
                if ($districtMeter) {
                    $dhm = $districtMeter;
                    if ($val == '85') {
                        $temp = $val;
                        $val = 'open';
                        $dhm->last_valve_status = 'open';
                    } else {
                        $temp = $val;
                        $val = 'closed';
                        $dhm->last_valve_status = 'closed';
                    }

                    $dhm->last_valve_status_time = date('Y-m-d H:i:s');
                    $dhm->save();
                }
            }

            $found_energy = false;

            if (($read == 'meter_number' || $read == 'meter') && $success) {
                $xml = simplexml_load_string($command->telegram);
                foreach ($xml->DataRecord as $k => $v) {
                    if (strpos($v->Unit, 'Energy') !== false && ! $found_energy) {
                        $val = $v->Value;
                        if (strpos($v->Unit, '100') !== false) {
                            $val = (($val * 100)) / 1000;
                        }
                        $found_energy = true;
                    }
                }
                foreach ($xml->DataRecord as $k => $v) {
                    if (strpos($v->Unit, 'Flow temperature') !== false) {
                        $temp = $v->Value;
                        $conversion = str_replace('(', '', explode('Flow temperature ', $v->Unit)[1]);
                        $conversion = explode(' ', $conversion)[0];
                        $temp = $temp * $conversion;
                    }
                }
                if ($districtMeter) {
                    $dhm = $districtMeter;
                    if ($val > -1) {
                        $dhm->sudo_reading = $val;
                        $dhm->sudo_reading_time = date('Y-m-d H:i:s');
                    }
                    if ($temp > 0) {
                        $dhm->last_flow_temp = $temp;
                        $dhm->last_temp_time = date('Y-m-d H:i:s');
                    }
                    $dhm->save();
                }
            }

            return (object) [
                'success' => $success,
                'telegram' => $command->telegram,
                'val' => $val,
                'temp' => $temp,
                'attempts_remaining' => $attempts,
                'error' => '',
            ];
        } catch (Exception $e) {
            return (object) [
                'success' => false,
                'failed' => true,
                'val' => $val,
                'temp' => $temp,
                'error' => $e->getMessage().' ('.$e->getLine().')',
            ];
        }
    }
}
