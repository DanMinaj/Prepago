<?php

use Illuminate\Support\Facades\Redirect;

class SchemeController extends Controller
{
    protected $layout = 'layouts.admin_website';
    private $validator;

    public function __construct(BaseValidator $validator)
    {
        $this->validator = $validator;
    }

    public function index()
    {
        $schemes = Scheme::withoutArchived()->orderBy('scheme_number', 'ASC')->get();
        $this->layout->page = view('schemes/index', ['schemes' => $schemes]);
    }

    public function show($schemeID)
    {
        $fieldsVersions = getSchemeSetupCountryVersions();
        $scheme = Scheme::find($schemeID);

        $this->layout->page = view('schemes/show')
            ->with('scheme', $scheme)
            ->with('fieldsVersions', $fieldsVersions);

        return redirect('welcome');
        $this->layout->page = view('schemes/show')->with('scheme', $scheme);
    }

    public function update($schemeID)
    {
        $fieldsVersions = getSchemeSetupCountryVersions();

        if (! $this->validator->isValid(Scheme::rules($schemeID))) {
            $errors = $this->validator->getErrors();

            return redirect('schemes/'.$schemeID)->withInput()->withErrors($errors);
        }

        $schemeSetupCtrl = App::make('SchemeSetUpController');
        $data = $schemeSetupCtrl->prepareData(Input::all(), $fieldsVersions, true);

        //check if a new sms password was set
        if ($data['sms_password'] == '') {
            unset($data['sms_password']);
        }

        $scheme = Scheme::findOrFail($schemeID);
        $scheme->fill($data);
        //update the scheme info
        if (! $scheme->save()) {
            return redirect('schemes/'.$schemeID)->with('errorMessage', 'There was an error updating the scheme information');
        }

        //update the vat_rate in the tariffs table
        $tariff = Tariff::find($scheme->scheme_number);
        if ($tariff) {
            $tariff->fill([
                'vat_rate' => $data['vat_rate'],
                'vat_rate_new' => $data['vat_rate'],
            ]);
            if (! $tariff->save()) {
                return redirect('schemes/'.$schemeID)->with('errorMessage', 'There was an error updating the tariff vat information');
            }
        }

        return redirect('schemes')->with('successMessage', 'The scheme information was updated successfully');
    }

    public function destroy($schemeID)
    {
        if (! Scheme::find($schemeID)->delete()) {
            Session::flash('errorMessage', 'Cannot delete scheme');
        } else {
            Session::flash('successMessage', 'Scheme was deleted successfully');
        }

        return redirect('schemes');
    }

    public function scheme_settings()
    {
        $dl = DataLogger::where('scheme_number', Auth::user()->scheme_number)->first();

        $scheme = Scheme::find(Auth::user()->scheme_number);

        if (! $scheme) {
            return redirect('welcome-schemes')->with([
                'errorMessage' => 'Invalid scheme ID. Please re-select a scheme',
            ]);
        }

        if ($dl) {
            $dl_sim = Simcard::where('ID', $dl->sim_id)->first();
            $meter = MeterLookup::where('applied_schemes', 'like', '%'.$scheme->id.'%')->first();
            $existing_meters = MeterLookup::all();
            $meter_lookup = MeterLookup::all();
        }

        $shut_off_periods = '';
        try {
            $shut_off_periods = json_decode($scheme->shut_off_periods);
        } catch (Exception $e) {
        }

        $user_schemes = UserScheme::where('scheme_id', Auth::user()->scheme_number)->get();
        $operators = User::where('id', '-1')->get();
        foreach ($user_schemes as $key => $value) {
            $user = User::where('id', $value->user_id)->first();
            if ($user) {
                $operators->add($user);
            }
        }

        $operators = $operators->sortByDesc('is_online_time');

        $meters = PermanentMeterData::where('scheme_number', Auth::user()->scheme_number)
        ->where('is_cme3100', 0)->get();
        $cme3100_meters = PermanentMeterData::where('scheme_number', Auth::user()->scheme_number)
        ->where('is_cme3100', 1)->get();

        $scheme_daily_readings = PermanentMeterData::where('scheme_number', Auth::user()->scheme_number)
        ->groupBy('readings_per_day')->first();

        if (! $scheme_daily_readings) {
            $scheme_daily_readings = '';
        } else {
            $scheme_daily_readings = $scheme_daily_readings->readings_per_day;
        }

        $this->layout->page = view('settings/scheme_settings', [
            'dl' => $dl,
            'dl_sim' => $dl_sim,
            'meter' => $meter,
            'existing_meters' => $existing_meters,
            'meter_lookup' => $meter_lookup,
            'shut_off_periods' => $shut_off_periods,
            'operators' => $operators,
            'meters' => $meters,
            'cme3100_meters' => $cme3100_meters,
            'scheme_daily_readings' => $scheme_daily_readings,
        ]);
    }

    public function scheme_settings_save()
    {
        try {
            if (Input::get('toggle_cme3100')) {
                $dl_id = Input::get('dl_id');
                $dl = DataLogger::find($dl_id);

                if (! $dl) {
                    throw new Exception("Datalogger $dl_id not found!");
                }
                $value = Input::get('cme3100');

                if ($value == 'on') {
                    $dl->cme3100_in_use = true;
                    $successMsg = 'Successfully enabled CMe3100';
                } else {
                    $dl->cme3100_in_use = false;
                    $successMsg = 'Successfully disabled CMe3100';
                }

                $dl->save();

                return Redirect::back()->with([
                    'successMessage' => $successMsg,
                ]);
            }

            $datalogger_id = Input::get('datalogger_id');
            $dl = DataLogger::where('id', $datalogger_id)->first();

            if (Input::get('cme3100_ip')) {
                $cme3100_ip = Input::get('cme3100_ip');
                $cme3100_port = Input::get('cme3100_port');

                $dl->cme3100_ip = $cme3100_ip;
                $dl->cme3100_port = $cme3100_port;
            }

            $sim_name = Input::get('dl_sim_Name');
            $old_sim_id = Input::get('old_sim_id');
            $new_sim_id = Input::get('new_sim_id');
            $target_sim_id = $old_sim_id;

            if (! $dl) {
                throw new Exception("A DataLogger entry with the id '$datalogger_id' does not exist!");
            }
            if ($old_sim_id != $new_sim_id) {
                $target_sim_id = $new_sim_id;
            }

            $simcard = Simcard::find($target_sim_id);
            if (! $simcard) {
                throw new Exception("A simcard with the ID '$target_sim_id' does not exist!");
            }
            $port1 = Input::get('dl_sim_port1');
            $port2 = Input::get('dl_sim_port2');

            $sim_IP = Input::get('dl_sim_IP_Address');
            $sim_ICCID = Input::get('dl_sim_ICCID');
            $sim_MSISDN = Input::get('dl_sim_MSISDN');

            $simcard->Name = $sim_name;
            $simcard->IP_Address = $sim_IP;
            $simcard->ICCID = $sim_ICCID;
            $simcard->MSISDN = $sim_MSISDN;
            $simcard->save();

            $dl->sim_id = $target_sim_id;
            $dl->port1 = $port1;
            $dl->port2 = $port2;
            $dl->save();

            $saveMeter = Input::get('save');
            if ($saveMeter == 0) {
                $existingMeterSelected = (Input::get('select_meter') == 'yes');
                $createMeter = (Input::get('create_meter') == 'yes');

                if ($createMeter) {
                    // We're creating a new meter for the meter_lookup table

                    $missing_inputs = false;

                    if (empty(Input::get('n_meter_make')) || empty(Input::get('n_last_eight')) || empty(Input::get('n_meter_model')) || empty(Input::get('n_scu_last_eight'))
                        || empty(Input::get('n_scu_make')) || empty(Input::get('n_meter_model')) || empty(Input::get('n_scu_model'))) {
                        $missing_inputs = true;
                    }

                    if (! $missing_inputs) {
                        $newMeter = new MeterLookup();
                        $newMeter->applied_schemes = Auth::user()->scheme_number;
                        $newMeter->meter_make = Input::get('n_meter_make');
                        $newMeter->meter_model = Input::get('n_meter_model');
                        $newMeter->meter_HIU = Input::get('n_meter_HIU');
                        $newMeter->scu_make = Input::get('n_scu_make');
                        $newMeter->scu_model = Input::get('n_scu_model');
                        $newMeter->scu_last_eight = Input::get('n_scu_last_eight');
                        $newMeter->last_eight = Input::get('n_last_eight');
                        $newMeter->reg_test_ip = '';
                        $newMeter->reg_test_primary = '';
                        $newMeter->reg_completed = 0;
                        $newMeter->reg_date = date('Y-m-d H:i:s');
                        $newMeter->created_at = date('Y-m-d H:i:s');
                        $newMeter->save();
                    }
                }

                if ($existingMeterSelected) {
                    $meter_selected = Input::get('meter_selected');

                    if ($meter_selected == '0') {
                    } else {
                        $selectedMeter = MeterLookup::find($meter_selected);
                        $selectedMeter->add(Auth::user()->scheme_number);
                        $selectedMeter->save();
                    }
                }
            } else {
                $mls = Input::get('ml');
                $arr = [];
                if (! empty($mls)) {
                    foreach ($mls as $key => $m) {
                        array_push($arr, $key);
                    }
                }

                MeterLookup::mass_add(Auth::user()->scheme_number, $arr);
            }

            return Redirect::back()->with([
                'successMessage' => 'Successfully saved changes',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    public function scheme_settings_shut_off_save($scheme_number)
    {
        try {
            $inputs = Input::all();

            $ids = [];

            $periods = new stdClass();
            $periods->Days = [];

            foreach ($inputs as $key => $value) {
                $id = explode('|', $key)[0];

                if (in_array($id, $ids)) {
                    continue;
                }

                array_push($ids, $id);

                $obj = new stdClass();
                $obj->Day = Input::get("$id|Day");
                $obj->Shut_Off_Start = Input::get("$id|Shut_Off_Start");
                $obj->Shut_Off_End = Input::get("$id|Shut_Off_End");
                $obj->Active = (int) Input::get("$id|Active");

                if (empty($obj->Shut_Off_Start) || empty($obj->Shut_Off_End)) {
                    throw new Exception('All times must be filled in!');
                }
                if ($obj->Active == null) {
                    $obj->Active = 0;
                }

                array_push($periods->Days, $obj);
            }

            $periods = json_encode($periods);

            $scheme = Scheme::find($scheme_number);
            if ($scheme) {
                $scheme->shut_off_periods = $periods;
                $scheme->save();
            }

            return Redirect::back()->with([
                'successMessage' => 'Successfully saved changes',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    public function scheme_settings_add_operator()
    {
        $username = Input::get('username');

        try {
            $operator = User::where('username', $username)->first();

            if (! $operator) {
                throw new Exception("Operator '$username' does not exist!");
            }
            $scheme = Auth::user()->scheme;

            $entry = new UserScheme();
            $entry->user_id = $operator->id;
            $entry->scheme_id = $scheme->scheme_number;
            $entry->save();

            return Redirect::back()->with([
                'successMessage' => "Successfully added operator $username to scheme #".$scheme->scheme_number.' ('.$scheme->scheme_nickname.')',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    public function scheme_settings_remove_operator()
    {
        $operator_id = Input::get('operator_id');

        try {
            $operator = User::find($operator_id);

            if (! $operator) {
                throw new Exception("Operator #'$operator_id' does not exist!");
            }
            $scheme = Auth::user()->scheme;

            $entry = UserScheme::where('user_id', $operator->id)
            ->where('scheme_id', $scheme->scheme_number)->first();

            if (! $entry) {
                throw new Exception("Operator #'$operator_id' does not belong to this scheme!");
            }
            $entry->delete();

            return Redirect::back()->with([
                'successMessage' => "Successfully removed operator '".$operator->username."' from scheme #".$scheme->scheme_number.' ('.$scheme->scheme_nickname.')',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    public function scheme_settings_manage_operator($operator_id)
    {
        $operator = User::where('id', $operator_id)->first();

        if (! $operator || (! $operator->inScheme(Auth::user()->scheme_number) && ! Auth::user()->isUserTest())) {
            $operator = false;
        }

        $this->layout->page = view('settings/scheme_settings_manage_operator', [
            'operator' => $operator,
        ]);
    }

    public function scheme_settings_manage_operator_save()
    {
        try {
            $operator_id = Input::get('operator_id');

            $operator = User::where('id', $operator_id)->first();

            if (! $operator) {
                throw new Exception("Operator doesn't exist!");
            }

            if ((! $operator->inScheme(Auth::user()->scheme_number) && ! Auth::user()->isUserTest())) {
                throw new Exception("This operator '".$operator->username."' does not belong to your scheme!");
            }

            $change_password = (Input::get('change_password') == 'yes');
            $successMsg = 'Successfully saved changes to operator: '.$operator->username;

            if ($change_password) {
                $new_password = Hash::make(Input::get('new_password'));
                $operator->password = $new_password;
                $successMsg = 'Successfully saved changed password for operator: '.$operator->username;
            }

            foreach (Input::all() as $key => $value) {
                if ($key == 'change_password' || $key == 'operator_id' || $key == 'new_password') {
                    continue;
                }

                $operator->$key = $value;
            }

            $operator->updated_at = time();

            $operator->save();

            return Redirect::back()->with([
                'successMessage' => $successMsg,

            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    public function scheme_settings_manage_operator_lock($operator_id)
    {
        $operator = User::where('id', $operator_id)->first();

        if (! $operator || (! $operator->inScheme(Auth::user()->scheme_number) && ! Auth::user()->isUserTest())) {
            return Redirect::back()->with([
                'errorMessage' => 'Operator not found!',
            ]);
        }

        $lock = ($operator->locked) ? false : true;

        $operator->locked = $lock;
        $operator->save();

        if ($lock == 1) {
            return Redirect::back()->with([
                'successMessage' => 'Successfully <b>locked operator</b> '.$operator->username,
            ]);
        } else {
            return Redirect::back()->with([
                'successMessage' => 'Successfully <b>unlocked</b> operator '.$operator->username,
            ]);
        }
    }

    public function scheme_settings_manage_meter($pmd_id)
    {
        if (! Auth::user()->inScheme(Auth::user()->scheme_number) && ! Auth::user()->isUserTest()) {
            $meter = PermanentMeterData::where('ID', $pmd_id)->where('scheme_number', Auth::user()->scheme_number)->first();
        } else {
            $meter = PermanentMeterData::where('ID', $pmd_id)->first();
        }

        $this->layout->page = view('settings/scheme_settings_manage_meter', [
            'meter' => $meter,
        ]);
    }

    public function scheme_settings_manage_meter_save()
    {
        try {
            $pmd_id = Input::get('pmd_id');
            $pmd = PermanentMeterData::where('ID', $pmd_id)->first();
            if (! $pmd) {
                throw new Exception("Permanent Meter ID $pmd_id not found");
            }

            $meter_lookup = MeterLookup::where('applied_schemes', 'like', '%'.$pmd->scheme_number.'%')->first();
            if (! $meter_lookup) {
                $meter_lookup = MeterLookup::find(1);
            }

            $fix_meter = (Input::get('fix_meter') == 1) ? true : false;
            $fix_scu = (Input::get('fix_scu') == 1) ? true : false;

            if ($fix_meter) {
                $meter_8 = Input::get('meter_number');
                $meter_last_8 = $meter_lookup->last_eight;
                $remaing_m_0 = 8 - strlen($meter_8);
                for ($i = $remaing_m_0; $i > 0; $i--) {
                    $meter_last_8 .= '0';
                }

                if (strpos($meter_8, '_') !== false) {
                    $meter_8 = explode('_', $meter_8)[1];
                }
                //
                $mbus = MBusAddressTranslation::where('8digit', $meter_8)->first();
                if (! $mbus) {
                    $new_mbus = new MBusAddressTranslation();
                    $new_mbus['8digit'] = $meter_8;
                    $new_mbus['16digit'] = $meter_8.$meter_last_8;
                    $new_mbus->save();
                    throw new Exception("Insertion ($meter_8) was not found in mbus_address_translation.8digit. Inserted new record.");
                }

                $mbus['16digit'] = $meter_8.$meter_last_8;
                $mbus->save();

                $successMsg = 'Successfully fixed meter mbus_address_translation insertion';

                return Redirect::back()->with([
                    'successMessage' => $successMsg,
                ]);
            }

            if ($fix_scu) {
                $scu_8 = Input::get('scu_number');
                $scu_last_8 = $meter_lookup->scu_last_eight;
                $remaing_s_0 = 8 - strlen($scu_8);
                for ($i = $remaing_s_0; $i > 0; $i--) {
                    $scu_last_8 .= '0';
                }

                $mbus = MBusAddressTranslation::where('8digit', $scu_8)->first();
                if (! $mbus) {
                    $new_mbus = new MBusAddressTranslation();
                    $new_mbus['8digit'] = $scu_8;
                    $new_mbus['16digit'] = $scu_8.$scu_last_8;
                    $new_mbus->save();
                    throw new Exception("Insertion ($scu_8) was not found in mbus_address_translation.8digit. Inserted new record.");
                }

                $mbus['16digit'] = $scu_8.$scu_last_8;
                $mbus->save();

                $successMsg = 'Successfully fixed SCU mbus_address_translation insertion';

                return Redirect::back()->with([
                    'successMessage' => $successMsg,
                ]);
            }

            $pmd = PermanentMeterData::where('ID', Input::get('pmd_id'))->first();

            $successMsg = 'Successfully saved changes to PMD_meter #'.$pmd->ID;

            $mbus_meter = $pmd->getMBus('meter');
            $mbus_scu = $pmd->getMBus('scu');
            $scheme = $pmd->scheme;
            $scheme_prefix = $scheme->prefix;

            if ($mbus_meter) {
                $new_mbus_16 = Input::get('meter_sixteen');
                $new_meter_number_noprefix = preg_replace('/\s+/', '', ((strpos(Input::get('meter_number'), '_') !== false) ? (explode('_', Input::get('meter_number'))[1]) : (Input::get('meter_number'))));
                $meter_number_in_use = PermanentMeterData::whereRaw("(meter_number = '$new_meter_number_noprefix')")
                ->where('ID', '!=', $pmd->ID)->first();
                if ($meter_number_in_use) {
                    throw new Exception('The new meter number you entered is already in use by PMD #'.$meter_number_in_use->ID." (<a href='/settings/scheme_settings/manage_meter/".$meter_number_in_use->ID."'>".$meter_number_in_use->username.'</a>)');
                }
                $new_meter_number_mbus_exists = MBusAddressTranslation::where('8digit', $new_meter_number_noprefix)->first();
                if (! $new_meter_number_mbus_exists) {
                    $new_meter_number_mbus_16 = $new_meter_number_noprefix.PermanentMeterData::getMBusEnding($scheme->scheme_number);
                    $new_meter_number_mbus_16_exists = MBusAddressTranslation::where('16digit', $new_meter_number_mbus_16)->first();
                    if (! $new_meter_number_mbus_16_exists) {
                        $new_meter_number_mbus_exists = new MBusAddressTranslation();
                        $new_meter_number_mbus_exists['8digit'] = $new_meter_number_noprefix;
                        $new_meter_number_mbus_exists['16digit'] = $new_meter_number_mbus_16;
                        $new_meter_number_mbus_exists->save();
                    }
                }
            }

            if ($mbus_scu) {
                $new_s_sixteen = Input::get('scu_sixteen');
                $new_s_eight = Input::get('scu_number');
                $o_scu_number = Input::get('o_scu_number');
                $mbus_scu = MBusAddressTranslation::where('8digit', $o_scu_number)->first();
                if ($mbus_scu['8digit'] == $new_s_eight) {
                } else {
                    $new_exists = MBusAddressTranslation::where('8digit', $new_s_eight)->first();
                    if ($new_exists) {
                        $pmd->scu_number = $new_s_eight;
                        $pmd->m_bus_relay_id = $new_s_eight;
                    } else {
                        $mbus_scu['16digit'] = PermanentMeterData::fill_digits(str_replace($o_scu_number, $new_s_eight, $new_s_sixteen));
                        $mbus_scu['8digit'] = $new_s_eight;
                        $mbus_scu->save();
                    }
                }

                $new_scu_number = Input::get('scu_number');
                $scu_number_in_use = PermanentMeterData::whereRaw("(scu_number = '$new_scu_number')")
                ->where('ID', '!=', $pmd->ID)->first();
                if ($scu_number_in_use) {
                    throw new Exception('The new SCU number you entered is already in use by PMD #'.$scu_number_in_use->ID." (<a href='/settings/scheme_settings/manage_meter/".$scu_number_in_use->ID."'>".$scu_number_in_use->username.'</a>)');
                }
                $new_scu_number_mbus_exists = MBusAddressTranslation::where('8digit', $new_scu_number)->first();
                if (! $new_scu_number_mbus_exists) {
                    $new_scu_number_mbus_16 = $new_scu_number.PermanentMeterData::getMBusEnding($scheme->scheme_number, 'scu');
                    $new_scu_number_mbus_16_exists = MBusAddressTranslation::where('16digit', $new_scu_number_mbus_16)->first();
                    if (! $new_scu_number_mbus_16_exists) {
                        $new_scu_number_mbus_16_exists = new MBusAddressTranslation();
                        $new_scu_number_mbus_16_exists['8digit'] = $new_scu_number;
                        $new_scu_number_mbus_16_exists['16digit'] = $new_scu_number_mbus_16;
                        $new_scu_number_mbus_16_exists->save();
                    }
                }
            }

            foreach (Input::all() as $key => $value) {
                if ($key == 'pmd_id' || $key == 'o_meter_sixteen' || $key == 'meter_sixteen' || $key == 'meter_eight' || $key == 'scu_sixteen' || $key == 'scu_eight' || $key == 'o_scu_number' || $key == 'o_meter_number') {
                    continue;
                }
                if ($key == 'name') {
                    continue;
                }

                $pmd->$key = $value;
            }

            $pmd->save();

            return Redirect::back()->with([
                'successMessage' => $successMsg,

            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => 'Error: '.$e->getMessage().' | '.$e->getLine(),
            ]);
        }
    }

    public function scheme_settings_manage_extra_save()
    {
        try {
            $scheme_number = Input::get('scheme_number');
            $scheme_daily_readings = Input::get('scheme_daily_readings');

            PermanentMeterData::where('scheme_number', $scheme_number)
            ->update([
                'readings_per_day' => $scheme_daily_readings,
            ]);

            $successMsg = 'Successfully saved extra settings.';

            return Redirect::back()->with([
                'successMessage' => $successMsg,
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    public function toggle_schemes_sms_block($iccid)
    {
        $sim = Simcard::where('ICCID', $iccid)->first();

        $scheme = $sim->scheme;

        $scheme->block_reboots = ! $scheme->block_reboots;
        $scheme->save();

        $block_reboots = $scheme->block_reboots;

        $success = ($block_reboots) ? 'Successfully unblocked reboot commands' : 'Successfully <b>blocked</b> reboot commands';

        return Redirect::back()->with([
            'successMessage' => $success,
        ]);
    }

    public function reboot_scheme_sim($track = false)
    {
        $scheme_id = Input::get('scheme_id');

        $scheme = Scheme::find($scheme_id);

        $res = $scheme->reboot();

        if ($track) {
            $action = new TrackingOperatorAction();
            $action->operator_id = Auth::user()->id;
            $action->scheme_id = Auth::user()->scheme_number;
            $action->action = 'reboot_scheme';
            $action->desc = Auth::user()->username." clicked 'Reboot' button in ".Input::get('url').', for Scheme '.$scheme->company_name;
            $action->save();
        }

        return Response::json($res);
    }

    public function tariffSetup($scheme_id)
    {
        try {
            $scheme = Scheme::find($scheme_id);
            $tariff = $scheme->tariff;

            $this->layout->page = view('settings.tariff_setup', [
                'scheme' => $scheme,
                'tariff' => $tariff,
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function tariffSetupSubmit($scheme_id)
    {
        try {
            $scheme = Scheme::find(Input::get('scheme_number'));

            $tariff = $scheme->tariff;

            if (! $tariff) {
                $last_tariff = Tariff::orderBy('scheme_number', 'DESC')->first();

                if (empty(Input::get('tariff_1')) && empty(Input::get('tariff_2')) && empty(Input::get('tariff_3'))) {
                    return Redirect::back()->with([
                        'errorMessage' => 'You must enter a value for each tariff!',
                    ]);
                }

                $tariff = new Tariff();
                $tariff->scheme_number = $scheme->scheme_number;
                $tariff->vat_rate = 0.135;
                $tariff->tariff_1 = Input::get('tariff_1');
                $tariff->tariff_2 = Input::get('tariff_2');
                $tariff->tariff_3 = Input::get('tariff_3');
                $tariff->tariff_4 = 0;
                $tariff->tariff_5 = 0;
                $tariff->vat_rate_new = 0.135;
                $tariff->tariff_1_new = 0;
                $tariff->tariff_2_new = 0;
                $tariff->tariff_3_new = 0;
                $tariff->tariff_4_new = 0;
                $tariff->tariff_5_new = 0;
                $tariff->tariff_1_new = 0;
                $tariff->save();

                $scheme->refreshFAQ();

                return Redirect::back()->with([
                    'successMessage' => 'Successfully created new tariffs for scheme <b>'.$scheme->scheme_nickname.'</b>!',
                ]);
            } else {
                if (empty(Input::get('tariff_1')) && empty(Input::get('tariff_2')) && empty(Input::get('tariff_3'))) {
                    $tariff->delete();

                    return Redirect::back()->with([
                        'errorMessage' => 'Deleted <b>'.$scheme->scheme_nickname."'s</b> tariffs",
                    ]);
                }

                $tariff->tariff_1 = Input::get('tariff_1');
                $tariff->tariff_2 = Input::get('tariff_2');
                $tariff->tariff_3 = Input::get('tariff_3');
                $tariff->save();

                $scheme->refreshFAQ();

                return Redirect::back()->with([
                    'successMessage' => 'Successfully saved changes to <b>'.$scheme->scheme_nickname."'s</b> tariffs",
                ]);
            }
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => 'Error occured creating tariff for <b>'.$scheme->scheme_nickname.'</b>: '.$e->getMessage(),
            ]);
        }
    }
}
