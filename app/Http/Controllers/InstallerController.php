<?php

use Illuminate\Support\Facades\Input;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class InstallerController extends Controller
{
    protected $layout = 'layouts.prepago_installer';

    public function dashboard()
    {
        $search_key = Input::get('search');
        $searched = false;

        if ($search_key) {
            $searched = true;
            $installed_units = PermanentMeterData::where('scheme_number', '=', Auth::user()->scheme_number)
            ->where(function ($query) {
                $query->orWhere('meter_number', 'like', '%'.Input::get('search').'%')
                      ->orWhere('house_name_number', 'like', '%'.Input::get('search').'%')
                      ->orWhere('street1', 'like', '%'.Input::get('search').'%')
                      ->orWhere('town', 'like', '%'.Input::get('search').'%')
                      ->orWhere('county', 'like', '%'.Input::get('search').'%')
                      ->orWhere('ev_rs_address', 'like', '%'.Input::get('search').'%')
                      ->orWhere('ev_rs_code', 'like', '%'.Input::get('search').'%');
            })
            ->get();
        } else {
            $installed_units = PermanentMeterData::where('scheme_number', '=', Auth::user()->scheme_number)->get();

            /*
            $installed_units = DB::select(DB::raw('SELECT username, (select 16digit from mbus_address_translations where mbus_address_translations.8digit = p.scu_number) as SCUReady,
            (select 16digit from mbus_address_translations where mbus_address_translations.8digit = substring(p.meter_number, locate("_",p.meter_number)+1 ) ) as MeterReady, p.*
            FROM `permanent_meter_data` as p  WHERE p.`scheme_number` = '.Auth::user()->scheme_number.'  '));
            foreach($installed_units as $u) {
                $pmd = PermanentMeterData::where('ID', $u->ID)->first();
                if($pmd) {
                    $u->pmd = $pmd;
                }
            }
            */
        }
        //print_r($installed_units);exit;
        $this->layout->page = view('dashboard.index')->with(['installed_units' => $installed_units, 'searched' => $searched]);
    }

    public function access_control()
    {
        $schemeIDs = getSchemes(Auth::user());
        $schemesCollection = Scheme::whereIn('id', $schemeIDs)->get();
        $schemes = [];
        foreach ($schemesCollection as $scheme) {
            $schemes[$scheme->id] = $scheme->nickname ?: $scheme->company_name;
        }

        $userChildren = getChildren(Auth::user()->id);

        $customers = [];
        if ($userChildren) {
            $customers = User::whereIn('id', $userChildren)->get();
        }

        $baseURL = URL::to('settings/access_control');

        $this->layout->page = view('dashboard/access_control', [
            'is_installer' => true,
            'customers' => $customers,
            'schemes' => $schemes,
            'baseURL' => $baseURL,
        ]);
    }

    public function addUnits($type = 'default')
    {
        $isEV = Input::has('type') && Input::get('type') == 'ev';

        $schemeInfo = Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->get()->first();
        $schemeStreet2 = $schemeInfo ? $schemeInfo->street2 : '';

        //get the information from the data_loggers table
        $dataLoggers = [];
        $dataLoggers = DataLogger::where('scheme_number', '=', Auth::user()->scheme_number)->lists('name', 'id');

        $last_installed_unit = PermanentMeterData::where('scheme_number', '=', Auth::user()->scheme_number)->orderby('ID', 'desc')->get()->first();
        if ($last_installed_unit) {
            $this->layout->page = view('dashboard.addUnits')->with([
                    'is_ev'					=> $isEV,
                    'type' 					=> $type,
                    'last_installed_unit' 	=> $last_installed_unit,
                    'schemeStreet2' 		=> $schemeStreet2,
                    'dataLoggers'			=> $dataLoggers,
            ]);
        } else {
            $this->layout->page = view('dashboard.addUnitsBlank')->with([
                    'is_ev'			=> $isEV,
                    'type' 			=> $type,
                    'schemeStreet2' => $schemeStreet2,
                    'dataLoggers'	=> $dataLoggers,
            ]);
        }
    }

    public function fetchEight()
    {
        $datalogger = DataLogger::where('scheme_number', Auth::user()->scheme_number)->first();

        if (! $datalogger) {
            return 'Datalogger not found';
        }

        return Response::json([
            'scu' => $datalogger->scu_last8,
            'meter' => $datalogger->meter_last8,
        ]);
    }

    public function try_grab_secondary($pmd, $datalogger)
    {
        if ($datalogger == null) {
            return;
        }

        if (is_object($pmd)) {
            $parts = explode($pmd, '.');
            $meter_number = $pmd->meter_number;
            $meter_make = $pmd->meter_make;
            $meter_model = $pmd->meter_make;
            $sim_ip = Simcard::where('ID', $datalogger->first()->sim_id)->first()->IP_Address;
            MBus::MeterSecondaryGrabber($sim_ip, $meter_number, $meter_make, $meter_model);
            exit;
        } else {
            $scheme_number = $pmd;
            $pmds = PermanentMeterData::where('scheme_number', $scheme_number)->all();
            $datalogger = DataLogger::where('scheme_number', $scheme_number)->first();
            $sim_ip = Simcard::where('ID', $datalogger->first()->sim_id)->first()->IP_Address;
            foreach ($pmds as $pmd) {
                MBus::MeterSecondaryGrabber($sim_ip, $meter_number, $meter_make, $meter_model);
            }
        }

        exit;
    }

    public function add_unit()
    {
        $isEV = Input::get('is_ev');

        $isMScuType = Input::get('scu_type') == 'm';

        $rules = [
            'scu_type' => 'required',
            'scu_number' => 'required',
            'meter_number' => 'required',
            'baud_rate' => 'required',
            'readings_per_day' => 'required',
            'md_make' => 'required',
            'md_model' => 'required',
            'md_manufacturer' => 'required',
            'hd_make' => 'required',
            'hd_model' => 'required',
            'hd_manufacturer' => 'required',
            'vd_make' => 'required',
            'vd_model' => 'required',
            'vd_manufacturer' => 'required',
            ];

        if ($isEV) {
            $rules['ev_rs_address'] = 'required|max:200';
            $rules['ev_rs_code'] = 'required|max:100|unique:permanent_meter_data,ev_rs_code';
        } else {
            $rules['house_apartment_number'] = 'required';
            $rules['building_street_name'] = 'required';
            $rules['dataLogger'] = 'required';
        }

        if (! $isMScuType) {
            $rules['iccid'] = 'required';
            $rules['service_control_port'] = 'required';
            $rules['heat_control_port'] = 'required';
        }

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            return redirect('prepago_installer/add-units'.($isEV ? '?type=ev' : ''))->withErrors($validator)->withInput();
        }

        try {
            $service_control_port = Input::get('scu_type') == 'm' ? '1' : Input::get('service_control_port');
            $heat_control_port = Input::get('scu_type') == 'm' ? '-1' : Input::get('heat_control_port');

            //get the post code from the schemes table
            $schemeInfo = Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->get()->first();
            $schemePostcode = $schemeInfo ? $schemeInfo->post_code : '';

            $scheme = Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->get()->first();
            if (! $isMScuType) {
                $iccid = Input::get('iccid');
                $simcard = Simcard::where('ICCID', '=', $iccid)->get()->first();
                $sim_ID = $simcard['ID'];
            } else {
                $sim_ID = '';
            }

            $scu = Input::get('scu_number');
            $meter_number = Input::get('meter_number');
            $scu_18 = Input::get('iniSCU');
            $meter_18 = Input::get('iniMeter');
            $inserted_scu = false;
            $inserted_meter = false;

            /* Extra check to make sure it has the last 8 digits for 16 digit mbus */
            $datalogger = DataLogger::where('scheme_number', Auth::user()->scheme_number)->first();
            $scu_last8 = $datalogger->scu_last8;
            $meter_last8 = $datalogger->meter_last8;

            if (strlen($scu_18) < 16) {
                $scu_18 = $scu.$scu_last8;
            }

            if (strlen($meter_18) < 16) {
                $meter_18 = $meter_number.$meter_last8;
            }
            /* End check */

            $scu_check = MBusAddressTranslation::where('8digit', $scu)->first();
            if (! $scu_check) {
                DB::table('mbus_address_translations')->insert([
                    '8digit' => $scu,
                    '16digit' => $scu_18,
                ]);
                $inserted_scu = true;
            }

            $meter_check = MBusAddressTranslation::where('8digit', $meter_number)->first();
            if (! $meter_check) {
                DB::table('mbus_address_translations')->insert([
                    '8digit' => $meter_number,
                    '16digit' => $meter_18,
                ]);
                $inserted_meter = true;
            }

            $pmd = new PermanentMeterData();
            $pmd->scheme_number = Auth::user()->scheme_number;
            $pmd->meter_type = $isEV ? 'EV' : 'SCU Custom';
            $pmd->meter_number = $scheme['prefix'].Input::get('meter_number');
            $pmd->meter_number2 = Input::get('meter_number2') ? $scheme['prefix'].Input::get('meter_number2') : 'N/A';
            $pmd->install_date = date('Y-m-d');
            $pmd->scu_type = Input::get('scu_type');
            $pmd->scu_number = Input::get('scu_number');
            $pmd->scu_port = $service_control_port;
            $pmd->heat_port = $heat_control_port;
            $pmd->in_use = 0;
            $pmd->shut_off = 0;
            $pmd->is_boiler_room_meter = (Input::get('is_boiler_meter')) ? 1 : 0;
            $pmd->is_bill_paid_customer = (Input::get('is_bill_paid_customer')) ? 1 : 0;
            $pmd->meter_make = Input::get('md_make');
            $pmd->meter_model = Input::get('md_model');
            $pmd->meter_manufacturer = Input::get('md_manufacturer');
            $pmd->HIU_make = Input::get('hd_make');
            $pmd->HIU_model = Input::get('hd_model');
            $pmd->HIU_manufacturer = Input::get('hd_manufacturer');
            $pmd->valve_make = Input::get('vd_make');
            $pmd->valve_model = Input::get('vd_model');
            $pmd->valve_manufacturer = Input::get('vd_manufacturer');
            $pmd->meter_baud_rate = Input::get('baud_rate');
            $pmd->username = $isEV ? $this->composeUsername(Input::get('ev_rs_address')) : $this->composeUsername(Input::get('house_apartment_number').Input::get('building_street_name'));
            $pmd->house_name_number = Input::get('house_apartment_number');
            $pmd->street1 = Input::get('building_street_name');
            $pmd->street2 = Input::get('street2');
            $pmd->ev_rs_address = Input::get('ev_rs_address');
            $pmd->ev_rs_code = $this->validateRSCode(Input::get('ev_rs_code'));
            $pmd->town = $scheme['town'];
            $pmd->county = $scheme['county'];
            $pmd->country = $scheme['country'];
            $pmd->sim_ID = $sim_ID;
            $pmd->readings_per_day = Input::get('readings_per_day');
            $pmd->data_logger_id = Input::get('dataLogger');
            $pmd->installation_confirmed = 0;
            $pmd->postcode = $schemePostcode;
            $pmd->m_bus_relay_id = Input::get('scu_number');

            $datalogger = DataLogger::where('scheme_number', $pmd->scheme_number);
            //	$this->try_grab_secondary($pmd, $datalogger);

            if (! $pmd->save()) {
                throw new Exception('Error saving the permanent meter data');
            }

            if ($isEV) {
                DistrictHeatingMeter::create([
                    'meter_number' => $pmd->meter_number,
                    'latest_reading' => 0,
                    'sudo_reading' => 0,
                    'sudo_reading_time' => '0000-00-00 00:00:00',
                    'sudo_usage' => '',
                    'last_polled' => '0000-00-00 00:00:00',
                    'latest_reading_time' => '0000-00-00 00:00:00',
                    'start_of_month_reading' => 0,
                    'previous_monthly_readings' => '',
                    'shut_off_reading' => 0,
                    'last_shut_off_time' => '0000-00-00 00:00:00',
                    'shut_off_device_number' => 0,
                    'meter_contact_number' => isset($simcard) ? $simcard['IP_Address'] : '',
                    'shut_off_device_contact_number' => isset($simcard) ? $simcard['IP_Address'] : '',
                    'pin' => 0,
                    'port' => $pmd->scu_port,
                    'scu_type' => Input::get('scu_type'),
                    'permanent_meter_ID' => $pmd->ID,
                    'scheme_number' => Auth::user()->scheme_number,
                ]);

                $pmd->in_use = 1;
                if (! $pmd->save()) {
                    throw new Exception('Error saving the permanent meter data');
                }

                // add entry in the remote control status table
                $rcs = new RemoteControlStatus();
                $rcs->permanent_meter_id = $pmd->ID;
                if (! $rcs->save()) {
                    throw new Exception('Error saving the remote control status data');
                }
            }

            /*
            $pmdmrw = new PermanentMeterDataMeterReadWebsite();
            $pmdmrw->permanent_meter_id = $pmd->ID;
            $pmdmrw->scheme_number = Auth::user()->scheme_number;
            $pmdmrw->ICCID = $iccid;
            $pmdmrw->data_logger_id = Input::get('dataLogger');
            $pmdmrw->meter_number = Input::get('meter_number');
            if (!$pmdmrw->save()) {
                throw new Exception('Error saving the permanent meter data meter read information');
            }
            */

            if ($inserted_scu && $inserted_meter) {
                Session::flash('successMessage', 'Successfully inserted scu & meter into mbus_address_translations.');
            }

            if ($inserted_scu && ! $inserted_meter) {
                Session::flash('successMessage', 'Successfully inserted scu into mbus_address_translations.');
            }

            if (! $inserted_scu && $inserted_meter) {
                Session::flash('successMessage', 'Successfully inserted meter into mbus_address_translations.');
            }

            return redirect('prepago_installer/test-unit/'.$pmd->ID);
        } catch (Exception $e) {
            Session::flash('unitAddError', 'Please double check your input or try again later: '.$e->getMessage());

            return redirect('prepago_installer/add-units'.($isEV ? '?type=ev' : ''))->withInput();
        }
    }

    public function testUnit($unitID)
    {
        $unit = PermanentMeterData::where('ID', '=', $unitID)->get()->first();
        $this->layout->page = view('dashboard.test-unit', ['unitID' => $unitID, 'unit' => $unit]);
    }

    public function readAllCustomersMeters()
    {
        $log = new Logger('Automated Meters Reading');
        $log->pushHandler(new StreamHandler(storage_path('logs/automated_meters_reading.log'), Logger::INFO));
        $log->addInfo('Automated Meters Reading initiated', ['customer_id' => Auth::user()->id]);

        DB::beginTransaction();

        $schemeNumber = Auth::user()->scheme_number;
        $scheme = Scheme::where('scheme_number', '=', $schemeNumber)->first();
        $customers = Customer::where('status', '=', 1)->where('scheme_number', '=', $schemeNumber)->get();

        try {
            foreach ($customers as $customer) {
                if (! $customer->districtHeatingMeter || ! $customer->permanentMeter()) {
                    $log->addInfo('Customer does not have a district heating meter or a permanent meter', ['customer_id' => $customer->id]);
                    continue;
                }
                $log->addInfo('Automated Reading started for customer', ['customer_id' => $customer->id]);
                $permanentMeter = $customer->permanentMeter();
                $permanentMeter->performManualReading($schemeNumber, $scheme->prefix, Auth::user()->id);
                $log->addInfo('Automated Reading for permanent meter', ['pm_id' => $permanentMeter->ID]);
            }
        } catch (\Exception $e) {
            DB::rollback();

            return redirect('welcome')->with('errorMessage', 'There was an error while automatically taking the customers\' meter readings.');
        }

        DB::commit();

        return redirect('welcome')->with('successMessage', 'We are collecting the meter readings now.');
    }

    public function meter_read_test($unitID)
    {
        header('Access-Control-Allow-Origin: *');

        try {
            $pmd = PermanentMeterData::where('ID', '=', $unitID)->get()->first();
            $sim = Simcard::where('ID', '=', $pmd->sim_ID)->get()->first();

            $scheme = Scheme::where('scheme_number', '=', $pmd->scheme_number)->get()->first();

            $pmdmrwMeterNumber = str_replace($scheme['prefix'], '', $pmd->meter_number);
            $pmdmrwMeterNumber2 = str_replace($scheme['prefix'], '', $pmd->meter_number2);

            $pmdmrw = new PermanentMeterDataMeterReadWebsite();

            $pmdmrw->permanent_meter_id = $unitID;
            $pmdmrw->scheme_number = $pmd->scheme_number;
            $pmdmrw->ICCID = $sim['ICCID'];
            $pmdmrw->data_logger_id = $pmd->data_logger_id;
            $pmdmrw->meter_number = $pmdmrwMeterNumber;
            $pmdmrw->meter_number2 = $pmdmrwMeterNumber2 ?: 'N/A';
            $pmdmrw->save();

            return 'success';
        } catch (Exception $e) {
            return 'failed';
        }
    }

    public function meter_read_test_confirm($unitID)
    {
        $pmdmrw = PermanentMeterDataMeterReadWebsite::where('permanent_meter_id', '=', $unitID)->orderBy('time_date', 'desc')->first();
        $scheme = Scheme::where('scheme_number', '=', $pmdmrw->scheme_number)->get()->first();

        $dhm = DistrictHeatingMeter::where('permanent_meter_ID', $unitID)->first();

        if ($pmdmrw['complete'] == 1) {
            if ($dhm) {
                $temp = $dhm->last_flow_temp;
            } else {
                $temp = DistrictMeterStat::where('permanent_meter_ID', $unitID)->orderBy('id', 'DESC')->first();
                if ($temp) {
                    $temp = $temp->flow_temp;
                } else {
                    $temp = 0;
                }
            }

            $scheme->status_ok = 1;
            $scheme->status_checked = date('Y-m-d H:i:s');
            $scheme->save();

            return $pmdmrw['reading'].' '.$scheme['unit_abbreviation']."<br/><font size='0.9em'>$temp&deg;C</font>";
        } else {
            return 'failed';
        }
    }

    public function meter_read_test_confirm_alt($unitID)
    {
        $pmdmrw = PermanentMeterDataMeterReadWebsite::where('permanent_meter_id', '=', $unitID)->orderBy('time_date', 'desc')->first();
        $scheme = Scheme::where('scheme_number', '=', $pmdmrw->scheme_number)->get()->first();

        $dhm = DistrictHeatingMeter::where('permanent_meter_ID', $unitID)->first();

        if ($pmdmrw->complete == 0 && $pmdmrw->failed == 0) {
            return 'processing';
        }

        if ($pmdmrw->complete == 1) {
            if ($dhm) {
                $temp = $dhm->last_flow_temp;
            } else {
                $temp = DistrictMeterStat::where('permanent_meter_ID', $unitID)->orderBy('id', 'DESC')->first();
                if ($temp) {
                    $temp = $temp->flow_temp;
                } else {
                    $temp = 0;
                }
            }

            $scheme->status_ok = 1;
            $scheme->status_checked = date('Y-m-d H:i:s');
            $scheme->save();

            return $pmdmrw['reading'].' '.$scheme['unit_abbreviation']." <font size='0.9em'>$temp&deg;C</font>";
        }

        return 'failed';
    }

    public function service_control_test($unitID)
    {
        try {
            $pmd = PermanentMeterData::where('ID', '=', $unitID)->get()->first();
            $sim = Simcard::where('ID', '=', $pmd->sim_ID)->get()->first();

            $rtu = new RTUCommandQueWebsite();
            $rtu->port = $pmd['scu_port'];
            if (Auth::user()) {
                $rtu->automated_by_user_ID = Auth::user()->id;
            }
            $rtu->turn_service_on = 1;
            $rtu->turn_service_off = 0;
            $rtu->permanent_meter_id = $unitID;
            $rtu->scheme_number = $pmd->scheme_number;
            $rtu->ICCID = $sim['ICCID'];
            $rtu->scu_type = $pmd['scu_type'];
            $rtu->m_bus_relay_id = $pmd['scu_number'];
            $rtu->data_logger_id = $pmd['data_logger_id'];
            $rtu->save();

            return 'success';
        } catch (Exception $e) {
            return 'failed';
        }
    }

    public function service_control_test_confirm($unitID)
    {
        $pmd = PermanentMeterData::where('ID', '=', $unitID)->get()->first();
        $sim = Simcard::where('ID', '=', $pmd->sim_ID)->get()->first();
        $rtu = RTUCommandQueWebsite::where('permanent_meter_id', '=', $unitID)->where('port', '=', $pmd['scu_port'])->orderBy('time_date', 'desc')->first();
        if ($rtu['complete'] == 1) {
            $rtu = new RTUCommandQueWebsite();
            $rtu->port = $pmd['scu_port'];
            if (Auth::user()) {
                $rtu->automated_by_user_ID = Auth::user()->id;
            }
            $rtu->turn_service_on = 0;
            $rtu->turn_service_off = 1;
            $rtu->permanent_meter_id = $unitID;
            $rtu->scheme_number = $pmd->scheme_number;
            $rtu->ICCID = $sim['ICCID'];
            $rtu->scu_type = $pmd['scu_type'];
            $rtu->m_bus_relay_id = $pmd['scu_number'];
            $rtu->data_logger_id = $pmd['data_logger_id'];
            $rtu->save();

            return 'success';
        } else {
            return 'failed';
        }
    }

    public function service_control_test_switch($unitID, $action, $port = 2221, $attempts = 1)
    {
        if ($action == 'restart') {
            try {
                $pmd = PermanentMeterData::where('ID', '=', $unitID)->get()->first();
                $sim = Simcard::where('ID', '=', $pmd->sim_ID)->get()->first();

                $rtu = new RTUCommandQueWebsite();
                $rtu->port = $port;
                if (Auth::user()) {
                    $rtu->automated_by_user_ID = Auth::user()->id;
                }
                $rtu->attempts_to_try = $attempts;
                $rtu->restart_service = true;
                $rtu->turn_service_on = 0;
                $rtu->turn_service_off = 0;
                $rtu->permanent_meter_id = $unitID;
                $rtu->scheme_number = $pmd->scheme_number;
                $rtu->ICCID = $sim['ICCID'];
                $rtu->scu_type = $pmd['scu_type'];
                $rtu->m_bus_relay_id = $pmd['scu_number'];
                $rtu->data_logger_id = $pmd['data_logger_id'];
                $rtu->save();

                return 'success';
            } catch (Exception $e) {
                return 'failed';
            }
        }

        if ($action == 'on') {
            try {
                $pmd = PermanentMeterData::where('ID', '=', $unitID)->get()->first();
                $sim = Simcard::where('ID', '=', $pmd->sim_ID)->get()->first();

                $rtu = new RTUCommandQueWebsite();
                $rtu->port = $port;
                if (Auth::user()) {
                    $rtu->automated_by_user_ID = Auth::user()->id;
                }
                $rtu->attempts_to_try = $attempts;
                $rtu->turn_service_on = 1;
                $rtu->turn_service_off = 0;
                $rtu->permanent_meter_id = $unitID;
                $rtu->scheme_number = $pmd->scheme_number;
                $rtu->ICCID = $sim['ICCID'];
                $rtu->scu_type = $pmd['scu_type'];
                $rtu->m_bus_relay_id = $pmd['scu_number'];
                $rtu->data_logger_id = $pmd['data_logger_id'];
                $rtu->save();

                return 'success';
            } catch (Exception $e) {
                return 'failed';
            }
        }

        if ($action == 'off') {
            try {
                $pmd = PermanentMeterData::where('ID', '=', $unitID)->get()->first();
                $sim = Simcard::where('ID', '=', $pmd->sim_ID)->get()->first();

                $rtu = new RTUCommandQueWebsite();
                $rtu->port = $port;
                if (Auth::user()) {
                    $rtu->automated_by_user_ID = Auth::user()->id;
                }
                $rtu->attempts_to_try = $attempts;
                $rtu->turn_service_on = 0;
                $rtu->turn_service_off = 1;
                $rtu->permanent_meter_id = $unitID;
                $rtu->scheme_number = $pmd->scheme_number;
                $rtu->ICCID = $sim['ICCID'];
                $rtu->scu_type = $pmd['scu_type'];
                $rtu->m_bus_relay_id = $pmd['scu_number'];
                $rtu->data_logger_id = $pmd['data_logger_id'];
                $rtu->save();

                return 'success';
            } catch (Exception $e) {
                return 'failed';
            }
        }
    }

    public function service_control_test_switch_confirm($unitID, $action)
    {
        $pmd = PermanentMeterData::where('ID', '=', $unitID)->get()->first();
        $sim = Simcard::where('ID', '=', $pmd->sim_ID)->get()->first();
        $scheme = Scheme::find($pmd->scheme_number);

        switch ($action) {

            case 'restart':

                $rtu = RTUCommandQueWebsite::where('permanent_meter_id', $unitID)->where('restart_service', 1)->orderBy('ID', 'DESC')->first();

            break;

            case 'on':

                $rtu = RTUCommandQueWebsite::where('permanent_meter_id', $unitID)->where('turn_service_on', 1)->orderBy('ID', 'DESC')->first();

            break;

            case 'off':

                $rtu = RTUCommandQueWebsite::where('permanent_meter_id', $unitID)->where('turn_service_off', 1)->orderBy('ID', 'DESC')->first();

            break;

        }

        if ($rtu['complete'] == 1) {
            $this->updateValveStatus($pmd, $action, $rtu);

            $scheme->status_ok = 1;
            $scheme->status_checked = date('Y-m-d H:i:s');
            $scheme->save();

            return 'success';
        }

        if ($rtu['failed'] == 1) {
            return 'failed';
        }

        /*
        if($scheme->statusCode != "1") {
            return 'failed|scheme_offline|' . $scheme->statusCode;
        }*/

        if ($rtu['complete'] == 0 && $rtu['failed'] == 0) {
            return 'processing';
        }
    }

    public function heat_control_test($unitID)
    {
        try {
            $pmd = PermanentMeterData::where('ID', '=', $unitID)->get()->first();
            $sim = Simcard::where('ID', '=', $pmd->sim_ID)->get()->first();

            $rtu = new RTUCommandQueWebsite();
            $rtu->port = $pmd['heat_port'];
            if (Auth::user()) {
                $rtu->automated_by_user_ID = Auth::user()->id;
            }
            $rtu->turn_service_on = 1;
            $rtu->turn_service_off = 0;
            $rtu->permanent_meter_id = $unitID;
            $rtu->scheme_number = $pmd->scheme_number;
            $rtu->ICCID = $sim['ICCID'];
            $rtu->scu_type = $pmd['scu_type'];
            $rtu->m_bus_relay_id = $pmd['scu_number'];
            $rtu->data_logger_id = $pmd['data_logger_id'];
            $rtu->save();

            return 'success';
        } catch (Exception $e) {
            return 'failed';
        }
    }

    public function heat_control_test_confirm($unitID)
    {
        $pmd = PermanentMeterData::where('ID', '=', $unitID)->get()->first();
        $sim = Simcard::where('ID', '=', $pmd->sim_ID)->get()->first();
        $rtu = RTUCommandQueWebsite::where('permanent_meter_id', '=', $unitID)->where('port', '=', $pmd['heat_port'])->orderBy('time_date', 'desc')->first();
        if ($rtu['complete'] == 1) {
            $rtu = new RTUCommandQueWebsite();
            $rtu->port = $pmd['heat_port'];
            if (Auth::user()) {
                $rtu->automated_by_user_ID = Auth::user()->id;
            }
            $rtu->turn_service_on = 0;
            $rtu->turn_service_off = 1;
            $rtu->permanent_meter_id = $unitID;
            $rtu->scheme_number = $pmd->scheme_number;
            $rtu->ICCID = $sim['ICCID'];
            $rtu->scu_type = $pmd['scu_type'];
            $rtu->m_bus_relay_id = $pmd['scu_number'];
            $rtu->data_logger_id = $pmd['data_logger_id'];
            $rtu->save();

            return 'success';
        } else {
            return 'failed';
        }
    }

    public function heat_control_test_switch($unitID, $action)
    {
        if ($action == 'on') {
            try {
                $pmd = PermanentMeterData::where('ID', '=', $unitID)->get()->first();
                $sim = Simcard::where('ID', '=', $pmd->sim_ID)->get()->first();

                $rtu = new RTUCommandQueWebsite();
                $rtu->port = $pmd['heat_port'];
                if (Auth::user()) {
                    $rtu->automated_by_user_ID = Auth::user()->id;
                }
                $rtu->turn_service_on = 1;
                $rtu->turn_service_off = 0;
                $rtu->permanent_meter_id = $unitID;
                $rtu->scheme_number = $pmd->scheme_number;
                $rtu->ICCID = $sim['ICCID'];
                $rtu->scu_type = $pmd['scu_type'];
                $rtu->m_bus_relay_id = $pmd['scu_number'];
                $rtu->data_logger_id = $pmd['data_logger_id'];
                $rtu->save();

                return 'success';
            } catch (Exception $e) {
                return 'failed';
            }
        }

        if ($action == 'off') {
            try {
                $pmd = PermanentMeterData::where('ID', '=', $unitID)->get()->first();
                $sim = Simcard::where('ID', '=', $pmd->sim_ID)->get()->first();

                $rtu = new RTUCommandQueWebsite();
                $rtu->port = $pmd['heat_port'];
                if (Auth::user()) {
                    $rtu->automated_by_user_ID = Auth::user()->id;
                }
                $rtu->turn_service_on = 0;
                $rtu->turn_service_off = 1;
                $rtu->permanent_meter_id = $unitID;
                $rtu->scheme_number = $pmd->scheme_number;
                $rtu->ICCID = $sim['ICCID'];
                $rtu->scu_type = $pmd['scu_type'];
                $rtu->m_bus_relay_id = $pmd['scu_number'];
                $rtu->data_logger_id = $pmd['data_logger_id'];
                $rtu->save();

                return 'success';
            } catch (Exception $e) {
                return 'failed';
            }
        }
    }

    public function complete_install()
    {
        $unitID = Input::get('unitID');
        PermanentMeterData::where('ID', '=', $unitID)->update(['installation_confirmed' => 1]);

        return redirect('prepago_installer');
    }

    public function incomplete_install()
    {
        $unitID = Input::get('unitID');
        PermanentMeterData::where('ID', '=', $unitID)->update(['installation_confirmed' => 0]);

        return redirect('prepago_installer');
    }

    public function editUnit($unitID)
    {
        $schemeInfo = Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->get()->first();
        $schemeStreet2 = $schemeInfo ? $schemeInfo->street2 : '';

        $unit = PermanentMeterData::where('ID', '=', $unitID)->get()->first();

        $mbusAddressSCU = '';
        $mbusAddressMeter = '';
        $SCUReady = false;
        $MeterReady = false;
        if ($unit) {
            if ($unit['scu_type'] == 'm') {
                $mbusAddressSCU = DB::table('mbus_address_translations')->where('8digit', '=', $unit['scu_number'])->first();
                $SCUReady = $mbusAddressSCU ? true : false;
                if (strpos($unit['meter_number'], '_') !== false) {
                    $meternumber = explode('_', $unit['meter_number']);
                    $meternumber = $meternumber[1];
                } else {
                    $meternumber = $unit['meter_number'];
                }
                $mbusAddressMeter = DB::table('mbus_address_translations')->where('8digit', '=', $meternumber)->first();
                $MeterReady = $mbusAddressMeter ? true : false;
            }
        }

        $simcard = Simcard::where('ID', '=', $unit['sim_ID'])->get()->first();

        $this->layout->page = view('dashboard.editUnit', [
            'unitID' => $unitID,
            'unit' => $unit,
            'simcard' => $simcard,
            'schemeStreet2' => $schemeStreet2,
            'SCUReady' => $SCUReady,
            'MeterReady' => $MeterReady,
        ]);
    }

    public function saveEditedUnitInformation($unitID)
    {
        $code = Input::get('ev_rs_code');

        if (Input::has('ev_rs_code') && ! $code) {
            return redirect('prepago_installer/edit-unit/'.$unitID)->with('errorMessage', 'The Recharge Station Code is required.');
        } elseif ($code && PermanentMeterData::where('ID', '!=', $unitID)->where('ev_rs_code', $code)->count()) {
            return redirect('prepago_installer/edit-unit/'.$unitID)->with('errorMessage', 'The entered Recharge Station Code already exists.');
        }

        // save the information in the DB
        $dataToUpdate = [
            'ev_rs_code' => $code,
            'scu_number' => Input::get('scu_number'),
            'meter_number'=> Input::get('meter_number'),
            'm_bus_relay_id' => Input::get('scu_number') == '00000000' ? '' : Input::get('scu_number'),
        ];

        PermanentMeterData
            ::where('ID', '=', $unitID)
            ->update($dataToUpdate);

        return redirect('prepago_installer/edit-unit/'.$unitID)->with('successMessage', 'The unit information was updated successfully.');
    }

    public function deleteUnit($unitID)
    {
        $unitID = (int) $unitID;
        if (! PermanentMeterData::find($unitID)->delete()) {
            return redirect('prepago_installer')->with('errorMessage', 'The unit information cannot be deleted');
        }

        return redirect('prepago_installer')->with('successMessage', 'The unit information was deleted successfully');
    }

    public function saveUnit($unitID = null)
    {
        $rules = [
            'house_apartment_number' => 'required',
            'building_street_name' => 'required',
            'scu_number' => 'required',
            'service_control_port' => 'required',
            'heat_control_port' => 'required',
            'meter_number' => 'required',
            'is_boiler_meter' => 'required',
            'baud_rate' => 'required',
            'readings_per_day' => 'required',
            'md_make' => 'required',
            'md_model' => 'required',
            'md_manufacturer' => 'required',
            'hd_make' => 'required',
            'hd_model' => 'required',
            'hd_manufacturer' => 'required',
            'vd_make' => 'required',
            'vd_model' => 'required',
            'vd_manufacturer' => 'required',
            ];

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            //return redirect('prepago_installer/save-unit')->withErrors($validator)->withInput();
            return redirect('prepago_installer/edit-unit/'.$unitID)->withErrors($validator)->withInput();
        }

        try {

            //get the post code from the schemes table
            $schemeInfo = Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->get()->first();
            $schemePostcode = $schemeInfo ? $schemeInfo->post_code : '';

            $pmd = PermanentMeterData::where('ID', '=', Input::get('ID'))->get()->first();
            $pmd->meter_number = Input::get('meter_number');
            $pmd->scu_number = Input::get('scu_number');
            $pmd->scu_port = Input::get('service_control_port');
            $pmd->heat_port = Input::get('heat_control_port');
            $pmd->is_boiler_room_meter = (Input::get('is_boiler_meter')) ? 1 : 0;
            $pmd->is_bill_paid_customer = (Input::get('is_bill_paid_customer')) ? 1 : 0;
            $pmd->meter_make = Input::get('md_make');
            $pmd->meter_model = Input::get('md_model');
            $pmd->meter_manufacturer = Input::get('md_manufacturer');
            $pmd->HIU_make = Input::get('hd_make');
            $pmd->HIU_model = Input::get('hd_model');
            $pmd->HIU_manufacturer = Input::get('hd_manufacturer');
            $pmd->valve_make = Input::get('vd_make');
            $pmd->valve_model = Input::get('vd_model');
            $pmd->valve_manufacturer = Input::get('vd_manufacturer');
            $pmd->meter_baud_rate = Input::get('baud_rate');
            $pmd->username = $this->composeUsername(Input::get('house_apartment_number').Input::get('building_street_name'));
            $pmd->house_name_number = Input::get('house_apartment_number');
            $pmd->street1 = Input::get('building_street_name');
            $pmd->street2 = Input::get('street2');
            $pmd->readings_per_day = Input::get('readings_per_day');
            $pmd->postcode = $schemePostcode;
            $pmd->save();

            return redirect('prepago_installer');
        } catch (Exception $e) {
            Session::flash('unitSaveError', 'Please double check your input or try again later.');
            //return redirect('prepago_installer/save-unit')->withInput();
            return redirect('prepago_installer/edit-unit/'.$unitID)->withInput();
        }
    }

    public function tools()
    {
        $this->layout->page = view('dashboard.tools');
    }

    public function help()
    {
        $this->layout->page = view('dashboard.help');
    }

    private function composeUsername($houseNameNumber = '', $street1 = '')
    {
        $houseNameNumber = strtolower($houseNameNumber);
        $houseNameNumber = preg_replace('/\s+/', '', $houseNameNumber);
        $houseNameNumber = preg_replace('/\'/', '', $houseNameNumber);
        $street1 = strtolower($street1);
        $street1 = preg_replace('/\s+/', '', $street1);

        $username = $houseNameNumber.$street1;

        return $username;
    }

    private function validateRSCode($code)
    {
        if ($code && PermanentMeterData::getModel()->rsCodeExists($code)) {
            Session::flash('unitAddError', 'The entered Recharge Station Code already exists.');

            return redirect('prepago_installer/add-units?type=ev')->withInput();
        }

        return $code;
    }

    public function addressTranslations()
    {
        $addresstranslations = MBusAddressTranslation::take(100)->get();

        $scu_translations = DB::select(DB::raw('SELECT (select 16digit from mbus_address_translations where mbus_address_translations.8digit = p.scu_number) as sixteen, 
			(select 8digit from mbus_address_translations where mbus_address_translations.8digit = p.scu_number) as eight
			FROM `permanent_meter_data` as p  WHERE p.`scheme_number` = '.Auth::user()->scheme_number.'  '));

        $meter_translations = DB::select(DB::raw('SELECT (select 8digit from mbus_address_translations where mbus_address_translations.8digit = substring(p.meter_number, locate("_",p.meter_number)+1 ) ) as eight,
			(select 16digit from mbus_address_translations where mbus_address_translations.8digit = substring(p.meter_number, locate("_",p.meter_number)+1 ) ) as sixteen, p.*
			FROM `permanent_meter_data` as p  WHERE p.`scheme_number` = '.Auth::user()->scheme_number.'  '));

        $this->layout->page = view('dashboard.address_translations')
        ->with('scu_translations', $scu_translations)
        ->with('meter_translations', $meter_translations)
        ->with('search', false);
    }

    public function searchTranslations()
    {
        $search_key = Input::get('search');
        if (! $search_key) {
            $this->layout->page = view('dashboard.address_translations');
        }

        $addresstranslations = MBusAddressTranslation::where('8digit', 'like', "%$search_key%")
        ->orWhere('16digit', 'like', '%search_key%')->get();

        $this->layout->page = view('dashboard.address_translations')
        ->with('addresstranslations', $addresstranslations)
        ->with('search', true)
        ->with('searching', $search_key);
    }

    public function addAddressTranslation()
    {
        $eight = Input::get('8digit');
        $sixteen = Input::get('16digit');

        $addresstranslations = MBusAddressTranslation::where('8digit', $eight)
        ->orWhere('16digit', $sixteen)->first();

        if ($addresstranslations) {
            return redirect('prepago_installer/address_translations')->with('errorMessage', 'The unit information cannot be deleted');
        }

        $new_addresstranslation = new MBusAddressTranslation();
        $new_addresstranslation['8digit'] = $eight;
        $new_addresstranslation['16digit'] = $sixteen;
        $new_addresstranslation->save();

        return redirect('prepago_installer/address_translations')->with('successMessage', "Successfully created new address translation: $eight - $sixteen");
    }

    public function deleteAddressTranslation($digit)
    {
        $eight = $digit;

        $addresstranslation = MBusAddressTranslation::where('8digit', $eight)->first();
        if ($addresstranslation) {
            $cached8 = $addresstranslation['8digit'];
            $cached16 = $addresstranslation['16digit'];

            DB::table('mbus_address_translations')->where('8digit', $eight)->delete();

            return redirect('prepago_installer/address_translations')->with('successMessage', "Successfully deleted address translation: $cached8 - $cached16");
        }

        return redirect('prepago_installer/address_translations')->with('errorMessage', "Failed to delete address translation: $eight");
    }

    public function editAddressTranslation($digit)
    {
        $addresstranslation = MBusAddressTranslation::where('8digit', $digit)->first();
        $eight = $addresstranslation['8digit'];
        $sixteen = $addresstranslation['16digit'];
        $this->layout->page = view('dashboard.editAddressTranslation', ['eight' => $eight, 'sixteen' => $sixteen]);
    }

    public function editSubmitAddressTranslation($digit)
    {
        $old_eight = $digit;
        $new_eight = Input::get('eight');
        $new_sixteen = Input::get('sixteen');

        $addresstranslation = MBusAddressTranslation::where('8digit', $old_eight)->first();

        if ($addresstranslation) {
            $old_sixteen = $addresstranslation['16digit'];

            DB::table('mbus_address_translations')->where('8digit', $old_eight)->update(['8digit' => $new_eight, '16digit' => $new_sixteen]);

            return redirect('prepago_installer/edit-address-translation/'.$new_eight)->with('successMessage', "Successfully edited address translation: from $old_eight::$old_sixteen to $new_eight::$new_sixteen");
        }

        return redirect('prepago_installer/edit-address-translation/'.$digit)->with('errorMessage', "Failed to edit address translation: $old_eight");
    }

    public function cancelShutOffSchedule($unitID)
    {
        try {
            $unit = PermanentMeterData::where('ID', '=', $unitID)->get()->first();

            if (! $unit) {
                return;
            }

            $dhm = $unit->districtHeatingMeters()->first();

            if (! $dhm) {
                return;
            }

            $customer = Customer::where('meter_ID', $dhm->meter_ID)->first();

            if (! $customer) {
                return;
            }

            $customer->clearShutOff(true);

            DistrictHeatingUsageLog::log($dhm->meter_ID, 'ID '.Auth::user()->id.' Cancelled schedule to shut off customer '.$customer->id);

            $res = [

                'error' => false,
                'success' => true,
                'success_msg' => 'Successfully cancelled schedule to shut off customer '.$customer->id,
                'pmd_id' => $unit->ID,
                'dhm_id' => $dhm->meter_ID,
                'dhm_mn' => $dhm->meter_number,
                'customer' => ($customer) ? $customer->id.'|'.$customer->username : 'null',

            ];
        } catch (Exception $e) {
            $res = [

                'error' => true,
                'error_msg' => $e->getMessage(),
            ];

            return Response::json($res);
        }

        return Response::json($res);
    }

    public function test_sim($IP)
    {
        $online = Simcard::online($IP, 4);

        if ($online) {
            return "<font color='green'>Sim Online!</font>";
        } else {
            return "<font color='red'>Sim Offline!</font>";
        }
    }

    /**
     ** Update the last_valve_status column in district_heating_meters for a specific meter.
     **/
    private function updateValveStatus($pmd, $action, $rtu)
    {
        try {
            $dhm = $pmd->districtHeatingMeters()->first();

            if (! $dhm) {
                $pmd->last_valve = (($action == 'on' || $action == 'restart') ? 'open' : 'closed');
                $pmd->last_valve_time = date('Y-m-d H:i:s');
                $pmd->save();

                return;
            }

            if ($rtu['complete'] == 1) {
                switch ($action) {
                    case 'on':
                    $dhm->last_valve_status = 'open';
                    $dhm->last_valve_status_time = date('Y-m-d H:i:s');
                    $dhm->last_command_sent = 'Success Meter On @'.$dhm->last_valve_status_time;
                    $dhm->last_command_sent_time = $dhm->last_valve_status_time;
                    $dhm->save();
                    break;
                    case 'off':
                    $dhm->last_valve_status = 'closed';
                    $dhm->last_valve_status_time = date('Y-m-d H:i:s');
                    $dhm->last_command_sent = 'Success Meter Off @'.$dhm->last_valve_status_time;
                    $dhm->last_command_sent_time = $dhm->last_valve_status_time;
                    $dhm->save();
                    break;
                    case 'restart':
                    $dhm->last_valve_status = 'open';
                    $dhm->last_valve_status_time = date('Y-m-d H:i:s');
                    $dhm->last_command_sent = 'Success Meter Restart @'.$dhm->last_valve_status_time;
                    $dhm->last_command_sent_time = $dhm->last_valve_status_time;
                    $dhm->save();
                    break;
                }
            }

            if ($rtu['failed'] == 1) {
                $dhm->last_valve_status = 'unknown';
                $dhm->last_valve_status_time = date('Y-m-d H:i:s');
                $dhm->save();
            }
        } catch (Exception $e) {
        }
    }

    public function quick_telegram()
    {
        $pmd_ID = Input::get('ID');
        $type = Input::has('type') ? Input::get('type') : 'meter';
        $attempts = Input::has('attempts') ? Input::get('attempts') : 3;
        $timeout = Input::has('timeout') ? Input::get('timeout') : 'never';

        $pmd = PermanentMeterData::where('ID', $pmd_ID)->first();

        if ($type != 'meter' && $type != 'meter_number' && $type != 'scu') {
            return Response::json([
                'error' => "Invalid telegram type; '$type'",
            ]);
        }

        if (! $pmd) {
            return Response::json([
                'error' => "PMD '$pmd' does not exist!",
            ]);
        }

        $response = $pmd->read($timeout, $attempts, $type);

        return Response::json([
            'response' => $response,
        ]);
    }
}
