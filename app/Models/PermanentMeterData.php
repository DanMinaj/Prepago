<?php
use Illuminate\Database\Eloquent\Model;

class PermanentMeterData extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'permanent_meter_data';
    protected $primaryKey = 'ID';
    protected $guarded = ['ID'];

    public $timestamps = false;

    public static $rules = [
        'selected_unit' => 'required',
        'username'		=> 'required',
    ];

    public static $customAttributeNames = [];

    public static $customErrorMessages = [
        'selected_unit.required'		=> 'Please select one of the available units.',
        'username.required'				=> 'Please enter a username.',
    ];

    public function rctimes()
    {
        return $this->hasMany('RemoteControlTimes', 'permanent_meter_id');
    }

    public function scopeInScheme($query, $schemeNumber)
    {
        return $query->where('scheme_number', $schemeNumber);
    }

    public function readings()
    {
        return $this->hasMany('PermanentMeterDataReadings', 'permanent_meter_id', 'ID');
    }

    public function simcard()
    {
        return $this->belongsTo('Simcard', 'sim_ID', 'ID');
    }

    public function latestReadings()
    {
        return $this->hasMany('PermanentMeterDataReadings', 'permanent_meter_id', 'ID')->orderBy('time_date', 'desc');
    }

    public function districtHeatingMeters()
    {
        return $this->hasMany('DistrictHeatingMeter', 'permanent_meter_ID', 'ID');
    }

    public function getDistrictMeterAttribute()
    {
        return DistrictHeatingMeter::where('permanent_meter_ID', $this->ID)->first();
    }

    public function getCustomer()
    {
        return Customer::where('username', $this->username)->first();
    }

    public function getCustomerAttribute()
    {
        if ($this->districtMeter) {
            return Customer::where('meter_ID', $this->districtMeter->meter_ID)->first();
        }

        return null;
    }

    public function scopeIsEV($query)
    {
        return $query->where('meter_type', 'EV');
    }

    public function rsCodeExists($code)
    {
        return $this->where('ev_rs_code', $code)->count();
    }

    public function scopeWithRSCode($query, $code)
    {
        return $query->where('ev_rs_code', $code);
    }

    public function inUse()
    {
        return $this->in_use;
    }

    public function rechargeInProgress()
    {
        return $this->recharge_in_progress;
    }

    public function block()
    {
        $this->update([
            'recharge_in_progress' => 1,
        ]);
    }

    public function unblock()
    {
        $this->update([
            'recharge_in_progress' => 0,
        ]);
    }

    public function performManualReading($schemeNumber, $schemePrefix, $automatedByUserID = null)
    {
        $sim = $this->simcard;
        $pmdmrwMeterNumber = str_replace($schemePrefix, '', $this->meter_number);
        $pmdmrwMeterNumber2 = str_replace($schemePrefix, '', $this->meter_number2);

        return PermanentMeterDataMeterReadWebsite::create([
                    'automated_by_user_ID'	=> $automatedByUserID,
                    'permanent_meter_id' 	=> $this->ID,
                    'scheme_number' 		=> $schemeNumber,
                    'ICCID' 				=> $sim->ICCID,
                    'data_logger_id' 		=> $this->data_logger_id,
                    'meter_number' 			=> $pmdmrwMeterNumber,
                    'meter_number2' 		=> $pmdmrwMeterNumber2 ?: 'N/A',
                ]);
    }

    public function openValve($schemeNumber = null, $data = null)
    {
        $sim = $this->simcard;

        // If data variable isn't null then we're making an entry for rtu_command_que
        if ($data != null) {
            RTUCommandQue::create($data);

            return;
        }

        if ($schemeNumber == null) {
            $schemeNumber = $this->scheme_number;
        }

        if (strlen($this->scu_port) < 4) {
            $this->scu_port = 2221;
        }

        // If data variable is null then we're making an entry for rtu_command_que_website
        $command = new RTUCommandQueWebsite();
        $command->port = $this->scu_port;
        $command->turn_service_on = 1;
        $command->turn_service_off = 0;
        $command->permanent_meter_id = $this->ID;
        $command->scheme_number = $schemeNumber;
        $command->ICCID = 'PERMANENT_METER_DATA.openValve';
        $command->scu_type = $this->scu_type;
        $command->m_bus_relay_id = $this->scu_number;
        $command->data_logger_id = $this->data_logger_id;
        $command->save();

        $lastCommand = RTUCommandQueWebsite::where('turn_service_on', 1)
        ->where('permanent_meter_id', $this->ID)
        ->where('m_bus_relay_id', $this->scu_number)
        ->orderBy('ID', 'DESC')
        ->first();

        return $lastCommand;
    }

    public function closeValve($schemeNumber = null, $data = null)
    {
        $sim = $this->simcard;

        // If data variable isn't null then we're making an entry for rtu_command_que
        if ($data != null) {
            RTUCommandQue::create($data);

            return;
        }

        if ($schemeNumber == null) {
            $schemeNumber = $this->scheme_number;
        }

        if (strlen($this->scu_port) < 4) {
            $this->scu_port = 2221;
        }

        // If data variable is null then we're making an entry for rtu_command_que_website
        $command = new RTUCommandQueWebsite();
        $command->port = $this->scu_port;
        $command->turn_service_on = 0;
        $command->turn_service_off = 1;
        $command->permanent_meter_id = $this->ID;
        $command->scheme_number = $schemeNumber;
        $command->ICCID = 'PERMANENT_METER_DATA.closeValve';
        $command->scu_type = $this->scu_type;
        $command->m_bus_relay_id = $this->scu_number;
        $command->data_logger_id = $this->data_logger_id;
        $command->save();

        $lastCommand = RTUCommandQueWebsite::where('turn_service_off', 1)
        ->where('permanent_meter_id', $this->ID)
        ->where('m_bus_relay_id', $this->scu_number)
        ->orderBy('ID', 'DESC')
        ->first();

        return $lastCommand;
    }

    public function getAutomaticReadingsAttribute()
    {
        return PermanentMeterDataReadings::where('permanent_meter_id', $this->ID)->where('time_date', 'LIKE', '%'.date('Y-m-d').'%')->count();
    }

    public function remoteControlStatus()
    {
        return $this->hasOne('RemoteControlStatus', 'permanent_meter_id', 'ID');
    }

    public function getMeterNumberCleanAttribute()
    {
        return (strpos($this->meter_number, '_') !== false) ? explode('_', $this->meter_number)[1] : $this->meter_number;
    }

    public function getSchemeAttribute()
    {
        return Scheme::find($this->scheme_number);
    }

    public static function getMBusEnding($scheme, $type = 'meter')
    {
        $meter_lookup = MeterLookup::where('applied_schemes', 'like', '%'.$scheme.'%')->first();
        if (! $meter_lookup) {
            $meter_lookup = MeterLookup::find(1);
        }

        if ($type == 'scu') {
            return $meter_lookup->scu_last_eight;
        }

        return $meter_lookup->last_eight;
    }

    public static function createPMD($scheme_number, $meter_number, $scu_number, $house_no, $action, $username, $street, $meter_number2 = 'N/A')
    {
        try {
            $scheme = Scheme::find($scheme_number);

            // If that scheme ID doesn't exist, stop the process
            if (! $scheme) {
                return null;
            }

            $pmd = self::where('username', $username)->first();

            // Fetch datalogger for scheme
            $datalogger = DataLogger::where('scheme_number', $scheme->id)->first();

            $nickname = strtolower($scheme->scheme_nickname);

            // Fetch meter/scu lookup table for specific scheme
            $meter_lookup = MeterLookup::where('applied_schemes', 'like', '%'.$scheme->id.'%')->first();
            if (! $meter_lookup) {
                $meter_lookup = MeterLookup::find(1);
            }

            // Mark last 8 digits of scu & meter
            $meter_last_8 = $meter_lookup->last_eight;
            $scu_last_8 = $meter_lookup->scu_last_eight;

            // House number contains decimal point which indicates apartment
            if (strpos($house_no, '.') !== false) {
                $parts = explode('.', $house_no);
                $apt_no = $parts[0];
                $house_no = $parts[1];
                $house_no = "Apt $apt_no, $house_no";
            }

            if ($action != 'preview') {
                // Check if meter number was inserted into mbus_address_translations - if not insert it
                $meter_inserted = MBusAddressTranslation::where('8digit', $meter_number)->first();
                if (! $meter_inserted && strlen($meter_number) >= 8) {
                    $remaing_m_0 = 8 - strlen($meter_number);
                    for ($i = $remaing_m_0; $i > 0; $i--) {
                        $meter_last_8 .= '0';
                    }

                    $meter_inserted = new MBusAddressTranslation();
                    $meter_inserted['8digit'] = $meter_number;
                    $meter_inserted['16digit'] = $meter_number.$meter_last_8;
                    $meter_inserted->save();
                }

                $meter_inserted2 = MBusAddressTranslation::where('8digit', $meter_number2)->first();
                if (! $meter_inserted2 && strlen($meter_number2) >= 8) {
                    $remaing_m_0 = 8 - strlen($meter_number2);
                    for ($i = $remaing_m_0; $i > 0; $i--) {
                        $meter_last_8 .= '0';
                    }

                    $meter_inserted2 = new MBusAddressTranslation();
                    $meter_inserted2['8digit'] = $meter_number2;
                    $meter_inserted2['16digit'] = $meter_number2.$meter_last_8;
                    $meter_inserted2->save();
                }

                // Check if scu number was inserted into mbus_address_translations - if not insert it
                $scu_inserted = MBusAddressTranslation::where('8digit', $scu_number)->first();
                if (! $scu_inserted && strlen($scu_number) >= 8) {
                    $remaining_s_0 = 8 - strlen($scu_number);
                    for ($i = $remaining_s_0; $i > 0; $i--) {
                        $scu_last_8 .= '0';
                    }

                    $scu_inserted = new MBusAddressTranslation();
                    $scu_inserted['8digit'] = $scu_number;
                    $scu_inserted['16digit'] = $scu_number.$scu_last_8;
                    $scu_inserted->save();
                } else {
                    $scu_inserted['8digit'] = $scu_number;
                    $scu_inserted['16digit'] = $scu_number.$scu_last_8;
                    $scu_inserted->save();
                }
            }

            // Create a new permanent_meter_data entry for that meter & cu

            if (! $pmd) {
                $pmd = new self();
            } else {
                //throw new Exception('it exists');
            }

            $pmd->scheme_number = $scheme->id;
            $pmd->meter_type = 'SCU Custom';

            if (strlen($meter_number) >= 8 || 1 == 1) {
                $pmd->meter_number = $scheme->prefix.$meter_number;
            }

            if (strlen($meter_number2) >= 8 || 1 == 1) {
                $pmd->meter_number2 = $scheme->prefix.$meter_number2;
            }

            $pmd->m_bus_relay_id = $scu_number;
            $pmd->scu_type = 'm';
            $pmd->scu_number = $scu_number;
            $pmd->scu_port = 1;
            $pmd->heat_port = -1;
            $pmd->sim_ID = 0;
            $pmd->data_logger_id = $datalogger->id;
            $pmd->ev_fake_reading = 0;
            $pmd->ev_rs_code = null;
            $pmd->ev_thirdparty_id = null;
            $pmd->ev_rs_address = null;
            $pmd->ev_lat = null;
            $pmd->ev_lng = null;
            $pmd->in_use = 0;
            $pmd->recharge_in_progress = 0;
            $pmd->shut_off = 0;

            $pmd->meter_make = $meter_lookup->meter_make;
            $pmd->meter_model = $meter_lookup->meter_model;
            $pmd->meter_manufacturer = $meter_lookup->meter_make;
            $pmd->HIU_make = $meter_lookup->meter_make;
            $pmd->HIU_model = $meter_lookup->meter_HIU;
            $pmd->meter_baud_rate = '2400';
            $pmd->HIU_manufacturer = $meter_lookup->meter_make;

            $pmd->valve_make = $meter_lookup->scu_make;
            $pmd->valve_model = $meter_lookup->scu_model;
            $pmd->valve_manufacturer = $meter_lookup->scu_make;

            $pmd->username = strtolower($username);
            $pmd->house_name_number = $house_no;
            $pmd->street1 = $scheme->scheme_nickname.((! empty($street)) ? ", $street" : '');
            $pmd->street2 = $scheme->street2;
            $pmd->town = $scheme->county;
            $pmd->county = $scheme->county;
            $pmd->country = $scheme->country;
            $pmd->installation_confirmed = '0';
            $pmd->longford_wire_change = 0;
            $pmd->readings_per_day = 6;
            $pmd->postcode = $scheme->post_code;
            $pmd->meter_number2 = $meter_number2;
            $pmd->install_date = date('Y-m-d');
            $pmd->is_bill_paid_customer = 0;
            $pmd->is_boiler_room_meter = 0;

            if ($action != 'preview') {
                $pmd->save();
            }

            return $pmd;
        } catch (Exception $e) {
            return [
                'username' => $pmd->username,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getAwayModeAttribute()
    {
        $rcs = RemoteControlStatus::where('permanent_meter_id', $this->ID)->first();

        if ($rcs) {
            return $rcs->away_mode_on;
        }

        return false;
    }

    public function getAwayModeDataAttribute()
    {
        $rcs = RemoteControlStatus::where('permanent_meter_id', $this->ID)->first();

        if ($rcs) {
            return $rcs;
        }

        return null;
    }

    public function getMBus($type)
    {
        $type = strtolower($type);

        if ($type == 'meter') {
            $meter_no_prefix = (strpos($this->meter_number, '_') !== false) ? (explode('_', $this->meter_number)[1]) : $this->meter_number;

            return MBusAddressTranslation::where('8digit', 'like', '%'.$meter_no_prefix.'%')->first();
        }

        if ($type == 'scu') {
            return MBusAddressTranslation::where('8digit', 'like', '%'.$this->scu_number.'%')->first();
        }
    }

    public function getAssignedAttribute()
    {
        $c = Customer::where('username', $this->username)->first();

        return $c;
    }

    public static function fill_digits($expected_16)
    {
        $remaing_0 = 16 - strlen($expected_16);
        for ($i = $remaing_0; $i > 0; $i--) {
            $expected_16 .= '0';
        }

        return $expected_16;
    }

    public function getAddress($get, $type)
    {
        $type = strtolower($type);

        if ($type == 'meter') {
            $meter_no_prefix = (strpos($this->meter_number, '_') !== false) ? (explode('_', $this->meter_number)[1]) : $this->meter_number;
            $digit = MBusAddressTranslation::where('8digit', $meter_no_prefix)->first();
            if ($digit) {
                return $digit[$get];
            } else {
                return '';
            }
        }
        if ($type == 'scu') {
            $digit = MBusAddressTranslation::where('8digit', $this->scu_number)->first();
            if ($digit) {
                return $digit[$get];
            } else {
                return '';
            }
        }
    }

    public function getSCUReadyAttribute()
    {
        if ($this->getAddress('16digit', 'scu') == '') {
            return false;
        }

        return true;
    }

    public function getMeterReadyAttribute()
    {
        if ($this->getAddress('16digit', 'meter') == '') {
            return false;
        }

        return true;
    }

    public function getLastReadingAttribute()
    {
        $ds = DistrictMeterStat::where('permanent_meter_ID', $this->ID)->orderBy('id', 'DESC')->first();
        if ($ds) {
            return $ds->reading;
        } else {
            return 'n/a';
        }
    }

    public function getLastTempAttribute()
    {
        $ds = DistrictMeterStat::where('permanent_meter_ID', $this->ID)->orderBy('id', 'DESC')->first();
        if ($ds) {
            return $ds->flow_temp;
        } else {
            return 'n/a';
        }
    }

    public function getLastPollAttribute()
    {
        $ds = DistrictMeterStat::where('permanent_meter_ID', $this->ID)->orderBy('id', 'DESC')->first();
        if ($ds) {
            return $ds->timestamp;
        } else {
            return 'n/a';
        }
    }

    public function getIPAttribute()
    {
        $dl = DataLogger::where('scheme_number', $this->scheme_number)->first();

        if ($dl) {
            $sim_ID = $dl->sim_id;

            $SIM = Simcard::where('ID', $sim_ID)->first();

            if ($SIM) {
                return $SIM->IP_Address;
            }
        }
    }

    public function open($timeout = 'never', $attempts = 3)
    {
        $command_sent = $this->openValve();
        $ID = $command_sent->ID;
        $success = false;
        $timed_out = false;
        $elapsed = 0;
        $start_time = microtime(true);
        $end_time = null;

        try {
            while ($command_sent->complete == 0 && $command_sent->failed == 0) {
                $command_sent = RTUCommandQueWebsite::where('ID', $ID)->first();

                $end_time = microtime(true);
                $elapsed = ($end_time - $start_time);

                if ($timeout != 'never' && $elapsed >= $timeout) {
                    $timed_out = true;
                    break;
                }
            }

            $command_sent = RTUCommandQueWebsite::where('ID', $ID)->first();

            $success = ($command_sent->failed == 0);

            if (! $success && $attempts > 0) {
                $attempts--;

                return $this->open($timeout, $attempts);
            }

            $end_time = microtime(true);
            $elapsed = ($end_time - $start_time);

            return (object) [
                'success' => $success,
                'attempts_remaining' => $attempts,
                'elapsed' => $elapsed,
                'timed_out' => $timed_out,
            ];
        } catch (Exception $e) {
            return (object) [
                'success' => $success,
                'attempts_remaining' => $attempts,
                'elapsed' => $elapsed,
                'timed_out' => $timed_out,
                'error' => $e->getMessage().' ('.$e->getLine().')',
            ];
        }
    }

    public function close($timeout = 'never', $attempts = 3)
    {
        $command_sent = $this->closeValve();
        $ID = $command_sent->ID;
        $success = false;
        $timed_out = false;
        $elapsed = 0;
        $start_time = microtime(true);
        $end_time = null;

        try {
            while ($command_sent->complete == 0 && $command_sent->failed == 0) {
                $command_sent = RTUCommandQueWebsite::where('ID', $ID)->first();

                $end_time = microtime(true);
                $elapsed = ($end_time - $start_time);

                if ($timeout != 'never' && $elapsed >= $timeout) {
                    $timed_out = true;
                    break;
                }
            }

            $command_sent = RTUCommandQueWebsite::where('ID', $ID)->first();

            $success = ($command_sent->failed == 0);

            if (! $success && $attempts > 0) {
                $attempts--;

                return $this->close($timeout, $attempts);
            }

            $end_time = microtime(true);
            $elapsed = ($end_time - $start_time);

            return (object) [
                'success' => $success,
                'attempts_remaining' => $attempts,
                'elapsed' => $elapsed,
                'timed_out' => $timed_out,
            ];
        } catch (Exception $e) {
            return (object) [
                'success' => $success,
                'attempts_remaining' => $attempts,
                'elapsed' => $elapsed,
                'timed_out' => $timed_out,
                'error' => $e->getMessage().' ('.$e->getLine().')',
            ];
        }
    }

    public function read($timeout = 'never', $attempts = 3, $read = 'meter_number', $watchdog = false)
    {
        $elapsed = 0;
        $val = -1;
        $conversion = null;
        $temp = -1;

        try {
            $start_time = microtime(true);

            $dl = DataLogger::where('scheme_number', $this->scheme_number)->first();

            if (! $dl) {
                return false;
            }

            if ($read == 'meter_number' || $read == 'meter') {
                $meter = explode('_', $this->meter_number)[1];
            } else {
                $meter = $this->scu_number;
            }

            $command = new PermanentMeterDataTelegramRelaycheckWebsite();
            $command->isTelegramReq = 1;
            $command->permanent_meter_id = $this->ID;
            $command->scheme_number = $this->scheme_number;
            $command->ICCID = 'man';
            $command->data_logger_id = $dl->id;
            $command->meter_number = $meter;
            $command->watchdog = $watchdog;
            $command->save();

            sleep(1);

            $command = PermanentMeterDataTelegramRelaycheckWebsite::where('permanent_meter_id', $this->ID)
            ->where('scheme_number', $this->scheme_number)
            ->where('ICCID', 'man')
            ->where('meter_number', $meter)
            ->orderBy('ID', 'desc')
            ->first();

            while ($command->fail == 0 && $command->complete == 0) {
                sleep(1);

                $command = PermanentMeterDataTelegramRelaycheckWebsite::where('permanent_meter_id', $this->ID)
                ->where('scheme_number', $this->scheme_number)
                ->where('ICCID', 'man')
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

                return $this->read($timeout, $attempts, $read, $watchdog);
            }

            if ($read == 'scu' && $success) {
                $xml = simplexml_load_string($command->telegram);
                foreach ($xml->DataRecord as $k => $v) {
                    if (strpos($v->Unit, 'Digital output') !== false) {
                        $val = $v->Value;
                    }
                }
                if ($this->districtMeter) {
                    $dhm = $this->districtMeter;
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

            if (($read == 'meter_number' || $read == 'meter') && $success) {
                $xml = simplexml_load_string($command->telegram);
                foreach ($xml->DataRecord as $k => $v) {
                    if (strpos($v->Unit, 'Energy (kWh)') !== false) {
                        $val = $v->Value;
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
                if ($this->districtMeter) {
                    $dhm = $this->districtMeter;
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
            ];
        } catch (Exception $e) {
            return (object) [
                'success' => false,
                'failed' => true,
                'val' => $val,
                'temp' => $temp,
                'error' => $e->getMessage(),
            ];
        }
    }
}
