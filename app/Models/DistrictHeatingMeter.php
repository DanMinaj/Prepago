<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class DistrictHeatingMeter extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'district_heating_meters';

    protected $primaryKey = 'meter_ID';

    protected $guarded = ['meter_ID'];

    public $timestamps = false;

    public function scopeRequireShutoff($query)
    {
        $service_on_min_temp = SystemSetting::get('service_on_min_temp');
        $service_off_max_temp = SystemSetting::get('service_off_max_temp');

        return $query->leftJoin('remote_control_status', 'district_heating_meters.permanent_meter_ID', '=', 'remote_control_status.permanent_meter_id')
        ->join('permanent_meter_data', 'district_heating_meters.permanent_meter_ID', '=', 'permanent_meter_data.ID')
        ->join('mbus_address_translations', 'permanent_meter_data.scu_number', '=', 'mbus_address_translations.8digit')
        ->join('schemes', 'district_heating_meters.scheme_number', '=', 'schemes.id')
        ->join('data_loggers', 'district_heating_meters.scheme_number', '=', 'data_loggers.scheme_number')
        ->join('sim_cards', 'data_loggers.sim_id', '=', 'sim_cards.ID')
        ->where('district_heating_meters.scheme_number', '!=', 6)
        ->whereRaw('(permanent_meter_data.is_bill_paid_customer = 0)')
        ->whereRaw("( ( (district_heating_meters.shut_off_device_status = 1) AND (district_heating_meters.last_flow_temp >= $service_off_max_temp) ) OR 
		(remote_control_status.away_mode_on IS NOT NULL AND remote_control_status.away_mode_on = 1 AND district_heating_meters.last_flow_temp >= $service_off_max_temp) )")
        ->orderBy('district_heating_meters.scheme_number', 'DESC');
    }

    public function getPermanentMeterAttribute()
    {
        return PermanentMeterData::where('ID', $this->permanent_meter_ID)->first();
    }

    public function scopeRequireAwayMode($query)
    {
        $service_on_min_temp = SystemSetting::get('service_on_min_temp');
        $service_off_max_temp = SystemSetting::get('service_off_max_temp');

        return $query->leftJoin('remote_control_status', 'district_heating_meters.permanent_meter_ID', '=', 'remote_control_status.permanent_meter_id')
        ->join('permanent_meter_data', 'district_heating_meters.permanent_meter_ID', '=', 'permanent_meter_data.ID')
        ->join('mbus_address_translations', 'permanent_meter_data.scu_number', '=', 'mbus_address_translations.8digit')
        ->join('schemes', 'district_heating_meters.scheme_number', '=', 'schemes.id')
        ->join('data_loggers', 'district_heating_meters.scheme_number', '=', 'data_loggers.scheme_number')
        ->join('sim_cards', 'data_loggers.sim_id', '=', 'sim_cards.ID')
        ->where('district_heating_meters.scheme_number', '!=', 6)
        ->whereRaw("(remote_control_status.away_mode_on IS NOT NULL AND remote_control_status.away_mode_on = 1 AND district_heating_meters.last_flow_temp >= $service_off_max_temp)")
        ->orderBy('district_heating_meters.scheme_number', 'DESC');
    }

    public function scopeRequireRestoration($query)
    {
        $service_on_min_temp = SystemSetting::get('service_on_min_temp');
        $service_off_max_temp = SystemSetting::get('service_off_max_temp');

        return $query->leftJoin('remote_control_status', 'district_heating_meters.permanent_meter_ID', '=', 'remote_control_status.permanent_meter_id')
        ->join('permanent_meter_data', 'district_heating_meters.permanent_meter_ID', '=', 'permanent_meter_data.ID')
        ->join('mbus_address_translations', 'permanent_meter_data.scu_number', '=', 'mbus_address_translations.8digit')
        ->join('schemes', 'district_heating_meters.scheme_number', '=', 'schemes.id')
        ->join('data_loggers', 'district_heating_meters.scheme_number', '=', 'data_loggers.scheme_number')
        ->join('sim_cards', 'data_loggers.sim_id', '=', 'sim_cards.ID')
        ->where('district_heating_meters.permanent_meter_ID', '!=', 0)
        ->where('district_heating_meters.scheme_number', '!=', 6)
        ->whereRaw("(remote_control_status.away_mode_on IS NULL OR remote_control_status.away_mode_on = 0) AND 
		( (district_heating_meters.shut_off_device_status = 0) AND district_heating_meters.last_flow_temp < $service_on_min_temp AND district_heating_meters.last_flow_temp != 0)  ")
        ->orderBy('district_heating_meters.scheme_number', 'DESC');
    }

    public function getSchemeAttribute()
    {
        return Scheme::find($this->scheme_number);
    }

    public function permanentMeterData()
    {
        return $this->hasOne('App\Models\PermanentMeterData', 'ID', 'permanent_meter_ID');
    }

    public function districtHeatingUsage()
    {
        return $this->hasMany('App\Models\DistrictHeatingUsage', 'ev_meter_ID', 'meter_ID');
    }

    public function EVUsage()
    {
        return $this->hasMany('App\Models\EVUsage', 'ev_meter_ID', 'meter_ID');
    }

    public function customers()
    {
        return $this->hasOne('App\Models\Customer', 'meter_ID', 'meter_ID');
    }

    public function getCustomerAttribute()
    {
        return Customer::where('meter_ID', $this->meter_ID)
        ->whereRaw('(deleted_at IS NULL AND status = 1)')->first();
    }

    public function emptyLatestReading()
    {
        $this->update([
            'latest_reading' => 0,
        ]);
    }

    public function scheduleToShutOff()
    {
        $this->update([
            'scheduled_to_shut_off' => 1,
        ]);
    }

    public function shutOff()
    {
    }

    public function getEVLatestReading()
    {
        return $this->EVUsage()->orderBy('date', 'DESC')->first();
    }

    public function getOffCommandsAttribute()
    {
        $offCommands = RTUCommandQueWebsite::where('permanent_meter_id', $this->permanent_meter_ID)
        ->where('turn_service_off', 1)
        ->orderBy('ID', 'DESC')
        ->get();

        $offCommandsR = RTUCommandQue::where('permanent_meter_id', $this->permanent_meter_ID)
        ->where('turn_service_off', 1)
        ->orderBy('ID', 'DESC')
        ->get();

        foreach ($offCommandsR as $off) {
            $nO = new RTUCommandQueWebsite();
            $nO->customer_ID = $off->customer_ID;
            $nO->time_date = $off->time_date;
            $nO->port = $off->port;
            $nO->attempts_to_try = 3;
            $nO->turn_service_on = $off->turn_service_on;
            $nO->turn_service_off = $off->turn_service_off;
            $nO->restart_service = 0;
            $nO->complete = $off->complete;
            $nO->permanent_meter_id = $off->permanent_meter_id;
            $nO->scheme_number = $off->scheme_number;
            $nO->ICCID = '1234567891234567890';
            $nO->failed = $off->failed;
            $nO->response = '';
            $nO->scu_type = 'm';
            if ($this->permanentMeterData()->first()) {
                $nO->m_bus_relay_id = $this->permanentMeterData()->first()->scu_number;
                $nO->data_logger_id = $this->permanentMeterData()->first()->data_logger_id;
            } else {
                $nO->m_bus_relay_id = '';
                $nO->data_logger_id = 0;
            }
            $nO->rtu_command_que = true;
            $nO->away_mode_initiated = $off->away_mode_initiated;
            $nO->shut_off_engine_initiated = $off->shut_off_engine_initiated;
            $nO->topup_initiated = $off->topup_initiated;

            $offCommands->add($nO);
        }

        $offCommands->sortByDesc('time_date');

        return $offCommands;
    }

    public function getOnCommandsAttribute()
    {
        $onCommands = RTUCommandQueWebsite::where('permanent_meter_id', $this->permanent_meter_ID)
        ->where('turn_service_on', 1)
        ->orderBy('ID', 'DESC')
        ->get();

        $onCommandsR = RTUCommandQue::where('permanent_meter_id', $this->permanent_meter_ID)
        ->where('turn_service_on', 1)
        ->orderBy('ID', 'DESC')
        ->get();

        foreach ($onCommandsR as $on) {
            $nO = new RTUCommandQueWebsite();
            $nO->customer_ID = $on->customer_ID;
            $nO->time_date = $on->time_date;
            $nO->port = $on->port;
            $nO->attempts_to_try = 3;
            $nO->turn_service_on = $on->turn_service_on;
            $nO->turn_service_off = $on->turn_service_off;
            $nO->restart_service = 0;
            $nO->complete = $on->complete;
            $nO->permanent_meter_id = $on->permanent_meter_id;
            $nO->scheme_number = $on->scheme_number;
            $nO->ICCID = '1234567891234567890';
            $nO->failed = $on->failed;
            $nO->response = '';
            $nO->scu_type = 'm';
            if ($this->permanentMeterData()->first()) {
                $nO->m_bus_relay_id = $this->permanentMeterData()->first()->scu_number;
                $nO->data_logger_id = $this->permanentMeterData()->first()->data_logger_id;
            } else {
                $nO->m_bus_relay_id = '';
                $nO->data_logger_id = 0;
            }
            $nO->rtu_command_que = true;
            $nO->away_mode_initiated = $on->away_mode_initiated;
            $nO->shut_off_engine_initiated = $on->shut_off_engine_initiated;
            $nO->topup_initiated = $on->topup_initiated;

            $onCommands->add($nO);
        }

        $onCommands->sortByDesc('time_date');

        return $onCommands;
    }

    public function getLastCommandAttribute()
    {
        $onCommands = RTUCommandQueWebsite::where('permanent_meter_id', $this->permanent_meter_ID)
        ->orderBy('ID', 'DESC')
        ->first();

        $onCommandsR = RTUCommandQue::where('permanent_meter_id', $this->permanent_meter_ID)
        ->orderBy('ID', 'DESC')
        ->first();

        if (! $onCommands) {
            return $onCommandsR;
        }

        if (! $onCommandsR) {
            return $onCommands;
        }

        if ($onCommands->time_date > $onCommandsR->time_date) {
            return $onCommands;
        } else {
            return $onCommandsR;
        }
    }

    public function getMeterNumberCleanAttribute()
    {
        return (strpos($this->meter_number, '_') !== false) ? explode('_', $this->meter_number)[1] : $this->meter_number;
    }

    public function getAwayModeTimeAttribute()
    {
        $pmd = $this->permanentMeterData()->first();

        if ($pmd) {
            $rcs = RemoteControlStatus::where('permanent_meter_id', $pmd->ID)->first();
            if ($rcs) {
                if ($rcs->last_start) {
                    return $rcs->last_start->date_time;
                }
            }
        }

        return 'undefined';
    }

    public static function nonReadingMeters()
    {
        $maxHours = 48;
        $nonReadingMeters = [];
        $customers = Customer::getActiveCustomers();

        foreach ($customers as $k => $c) {
            $pmd = $c->permanentMeter;
            $dhm = $c->districtMeter;
            $scheme = $c->scheme;

            if ($c->simulator > 0) {
                continue;
            }

            if (! $scheme || ! $pmd || ! $dhm) {
                continue;
            }

            if ($pmd->ID == 0 || $dhm->meter_ID == 0) {
                continue;
            }

            if ($scheme->scheme_number == 0) {
                continue;
            }

            $last_reading = PermanentMeterDataReadingsAll::where('permanent_meter_id', $pmd->ID)
            ->whereRaw('(reading1 >= 0)')
            ->orderBy('ID', 'DESC')->first();

            if (Carbon\Carbon::parse($dhm->sudo_reading_time)->diffInHours() < $maxHours) {
                continue;
            }

            if (! $last_reading) {
                array_push($nonReadingMeters, (object) [
                    'permanent_meter_id' => $pmd->ID,
                    'dhm_id' => $dhm->meter_ID,
                    'customer_id' => $c->id,
                    'customer_username' => $c->username,
                    'meter_number' => $dhm->meter_number,
                    'scheme_name' => ucfirst($scheme->scheme_nickname),
                    'lastReadingTime' => 'Never',
                    'lastReading' => '0',
                ]);

                continue;
            }

            if (Carbon\Carbon::parse($last_reading->time_date)->diffInHours() >= $maxHours) {
                array_push($nonReadingMeters, (object) [
                    'permanent_meter_id' => $pmd->ID,
                    'dhm_id' => $dhm->meter_ID,
                    'customer_id' => $c->id,
                    'customer_username' => $c->username,
                    'meter_number' => $dhm->meter_number,
                    'scheme_name' => ucfirst($scheme->scheme_nickname),
                    'lastReadingTime' => $last_reading->time_date,
                    'lastReading' => $last_reading->reading1,
                ]);

                continue;
            }
        }

        return $nonReadingMeters;
        // return DistrictHeatingMeter::whereRaw('sudo_reading_time < DATE_SUB(NOW(), INTERVAL 24 HOUR) AND invalid_reading_attempts >= 4')
            // ->where('permanent_meter_ID', '>', 0)
            // ->where('shut_off_device_status', 0)
            // ->leftjoin('customers', 'district_heating_meters.meter_ID', '=', 'customers.meter_ID')
            // ->leftjoin('schemes', 'schemes.scheme_number', '=', 'customers.scheme_number')
            // ->select(
                // 'district_heating_meters.permanent_meter_ID as permanent_meter_id',
                // 'district_heating_meters.meter_ID as dhm_id',
                // 'customers.id as customer_id',
                // 'customers.username as customer_username',
                // 'schemes.company_name as scheme_name',
                // 'district_heating_meters.invalid_reading_attempts as invalid_reading_attempts'
            // )
            // ->where('schemes.archived', 0)
            // ->whereRaw('(customers.deleted_at IS NULL AND customers.simulator = 0)')
            // ->where('schemes.scheme_number', '!=', 3)
            // ->orderBy('schemes.scheme_number', 'DESC')
            // ->orderBy('district_heating_meters.sudo_reading_time', 'DESC')
            // ->get();
    }
}
