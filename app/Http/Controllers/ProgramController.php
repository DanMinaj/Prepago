<?php

class ProgramController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function run_cronjob($name)
    {
        $cronjob = Cronjob::where('name', $name)->first();

        if (! $cronjob) {
            return redirect('settings/system_programs/cronjobs')->with([
                'errorMessage' => 'No cronjob called '.$name,
            ]);
        }

        $cronjob->execute(true);

        return redirect('settings/system_programs/cronjobs')->with([
            'successMessage' => 'Successfully ran cronjob '.$cronjob->name.' ['.$cronjob->artisan_command.']',
        ]);
    }

    public function manage_cronjobs()
    {
        $cronjobs = Cronjob::where('active', 1)->groupBy('name')->orderByRaw('count(name) DESC')->get();

        $this->layout->page = view('home/programs/cronjobs', [
            'cronjobs' => $cronjobs,
        ]);
    }

    public function save_manage_cronjobs()
    {
        try {

            // Save changes to any new cronjob times
            foreach (Input::all() as $name => $value) {
                if (strpos($name, 'existing_time_') !== false) {
                    $cron_parts = explode('existing_time_', $name)[1];
                    $parts = explode('|', $cron_parts);
                    $name = $parts[0];
                    $time = $parts[1];
                    $cron = Cronjob::where('name', $name)->where('time', $time)->first();
                    if ($cron) {
                        $cron->time = $value;
                        if ($cron->time > date('H:i:s')) {
                            $cron->ran_today = 0;
                            $cron->run_time = 0;
                        }
                        $cron->save();
                    }
                }
            }

            // Save changes to any old cronjob names
            foreach (Input::all() as $name => $value) {
                if (strpos($name, 'existing_name_') !== false) {
                    $cron_name = explode('existing_name_', $name)[1];
                    $cron = Cronjob::where('name', $cron_name)->first();
                    if ($cron && $cron->name != $value) {
                        $cron->log("Changed cronjob name from $cron_name to $value");
                        $cron->changeName($value);
                    }
                }
            }

            // Create any new cronjob schedules

            $new_cronjob = new Cronjob();

            foreach (Input::all() as $name => $value) {
                if (strpos($name, 'new_') !== false) {
                    $actual_name = explode('new_', $name)[1];
                    $new_cronjob->$actual_name = $value;
                }
            }

            if (empty($new_cronjob->time) || empty($new_cronjob->name) || empty($new_cronjob->artisan_command)) {
                //throw new Exception("Unable to create new cronjob. Please specify a time, name & command!");
            } else {
                /*
                if(Cronjob::where('time', $new_cronjob->time)->count() > 0) {
                    throw new Exception("Unable to create new cronjob. A cronjob with a execution time @ " . $new_cronjob->time . " already exists!");
                }
                */

                $new_cronjob->ran_today = 0;
                $new_cronjob->run_time = 0;
                $new_cronjob->active = true;
                $new_cronjob->save();
            }

            return Redirect::back()->with([
                'successMessage' => 'Successfully saved changes!',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => 'Some changes were not set: '.$e->getMessage(),
            ]);
        }
    }

    public function index_programs()
    {
        $this->layout->page = view('home/programs/index_programs', []);
    }

    public function index_remote_execution()
    {
        $schemes = Scheme::withoutArchived()->get(['id', 'company_name']);
        $calendar_remote_executions = CalendarRemoteProgram::where('completed', false)->get();

        $this->layout->page = view('home/programs/remote_execution', ['schemes' => $schemes, 'calendar_remote_executions' => $calendar_remote_executions]);
    }

    public function create_remote_execution()
    {
        try {
            $program = Input::get('program_to_run');
            $program_execution_delay = Input::get('program_execute_delay');
            $run_type = Input::get('program_runtype');
            $run_for = Input::get('program_runfor');

            $run_for_scheme = Input::get('program_scheme');
            $run_for_customer = Input::get('program_customer');

            $newRemoteProgram = new CalendarRemoteProgram();
            $newRemoteProgram->program = $program;
            $newRemoteProgram->run_type = $run_type;
            $newRemoteProgram->customer_ID = $run_for_customer;
            $newRemoteProgram->scheme_ID = $run_for_scheme;
            $newRemoteProgram->run_after = $program_execution_delay;
            $newRemoteProgram->processed = false;
            $newRemoteProgram->completed = false;
            $newRemoteProgram->created_by = Auth::user()->id;
            $newRemoteProgram->created_at = Carbon\Carbon::now();
            $newRemoteProgram->save();
        } catch (Exception $e) {
            return redirect('system_programs/remote_execution')->with('errorMessage', '<b>Error:</b> '.$e->getMessage());
        }

        return redirect('system_programs/remote_execution')->with('successMessage', '<b>Success:</b> Created scheduled execution of '.$program.' run-type '.$run_type.'  for  '.(($run_for == 'all') ? 'all' : ((! empty($run_for_customer) ? ('Customer'.$run_for_customer) : 'Scheme '.$run_for_scheme))));
    }

    public function stop_remote_execution()
    {
        try {
            $runningRemoteProgram = CalendarRemoteProgram::find(Input::get('remote_id'));

            if ($runningRemoteProgram) {
                $runningRemoteProgram->cancelled = true;
                $runningRemoteProgram->completed = true;
                $runningRemoteProgram->save();
            } else {
                throw new Exception("This remote program does not exist!'");
            }
        } catch (Exception $e) {
            return redirect('system_programs/remote_execution')->with('errorMessage', '<b>Error:</b> '.$e->getMessage());
        }

        return redirect('system_programs/remote_execution')->with('successMessage', 'Successfully stopped remote execution of task #'.$runningRemoteProgram->id.'-'.$runningRemoteProgram->program.$runningRemoteProgram->run_type);
    }

    public function index_manage_schedule()
    {
        $daily_schedules = CalendarDailySchedule::orderBy('time', 'ASC')->get();

        $this->layout->page = view('home/programs/manage_schedule', ['daily_schedules'  => $daily_schedules]);
    }

    public function switch_schedule($old, $new)
    {
    }

    public function manage_schedule()
    {
        try {
            $new_programs_to_add = [];

            foreach (Input::all() as $key => $input) {
                if (strpos($key, 'new') === false) {
                    continue;
                }

                $new_id = substr($key, -1);

                if (! isset($new_programs_to_add[$new_id])) {
                    $new_programs_to_add[$new_id][$key] = $input;
                } else {
                    $new_programs_to_add[$new_id][$key] = $input;
                }
            }

            foreach ($new_programs_to_add as $p_id => $p) {
                $new_time = $p['new_time_'.$p_id];
                $new_program = $p['new_program_'.$p_id];
                $new_run_type = $p['new_run_type_'.$p_id];
                $new_run_on_weekend_and_holiday = $p['new_run_on_weekend_and_holiday_'.$p_id];

                if (empty($new_time)) {
                    continue;
                }
                if (empty($new_program)) {
                    continue;
                }
                if (empty($new_run_type)) {
                    $new_run_type = 0;
                }
                if (empty($new_run_on_weekend_and_holiday)) {
                    $new_run_on_weekend_and_holiday = 0;
                }

                $new_schedule = new CalendarDailySchedule();
                $new_schedule->time = $new_time;
                $new_schedule->program = $new_program;
                $new_schedule->run_type = $new_run_type;
                $new_schedule->run_on_weekend_and_holiday = $new_run_on_weekend_and_holiday;
                $new_schedule->save();
            }

            $schedule_count = CalendarDailySchedule::all()->count();

            // Save changes to existing programs edits
            foreach (Input::all() as $key => $input) {
                if (strpos($key, 'input') === false) {
                    continue;
                }

                $time_assoc = explode('_', $key)[1];

                $schedule = CalendarDailySchedule::where('time', $time_assoc)->first();
                if ($schedule) {
                    $input1 = Input::get('input1_'.$time_assoc);
                    $input2 = Input::get('input2_'.$time_assoc);
                    $input3 = Input::get('input3_'.$time_assoc);
                    $input4 = Input::get('input4_'.$time_assoc);

                    if (empty($input1) && empty($input2) && empty($input3) && empty($input4)) {
                        $schedule->delete();
                    }

                    if (strpos($key, 'input1') !== false) {
                        $schedule->time = $input;
                    }

                    if (strpos($key, 'input2') !== false) {
                        $schedule->program = $input;
                    }

                    if (strpos($key, 'input3') !== false) {
                        $schedule->run_type = $input;
                    }

                    if (strpos($key, 'input4') !== false) {
                        $schedule->run_on_weekend_and_holiday = $input;
                    }

                    $schedule->save();
                } else {
                    continue;
                }
            }
        } catch (Exception $e) {
            return Redirect::back()->with('errorMessage', '<b>Error:</b> '.$e->getMessage());
        }

        return Redirect::back()->with('successMessage', 'Successfully saved changes');
    }

    public function index_billing_engine_settings()
    {
        $settings = BillingEngineSetting::all();

        $enabled = BillingEngineSetting::where('name', 'billing_engine_new_enabled')->first();
        $old_enabled = CalendarDailySchedule::where('program', 'b')->get();

        if ($old_enabled->count() <= 0) {
            $old_enabled = false;
        } else {
            $old_enabled = true;
        }

        $this->layout->page = view('home/programs/billing_engine_settings',
        [
            'settings' => $settings,
            'enabled' => $enabled,
            'old_enabled' => $old_enabled,
        ]);
    }

    public function save_programs_billing_engine_settings()
    {
        try {
            $new = (Input::get('new_billing_engine') == 'on') ? true : false;
            $old = (Input::get('old_billing_engine') == 'on') ? true : false;

            $setting = BillingEngineSetting::where('name', 'billing_engine_new_enabled')->first();

            if (($new && $old) || (! $new && ! $old) || (! $new && $old)) {
                $new = false;
                if ($setting) {
                    $setting->value = 'false';
                    $setting->save();
                }

                CalendarDailySchedule::where('program', 'nb')->update([
                    'program' => 'b',
                ]);

                return Redirect::back()->with('successMessage', 'Successfully enabled the Old Billing Engine.');
            }

            if ($new && ! $old) {
                if ($setting) {
                    $setting->value = 'true';
                    $setting->save();
                }

                CalendarDailySchedule::where('program', 'b')->update([
                    'program' => 'nb',
                ]);

                return Redirect::back()->with('successMessage', 'Successfully enabled the New Billing Engine.');
            }
        } catch (Exception $e) {
            return Redirect::back()->with('errorMessage', '<b>Error:</b> '.$e->getMessage());
        }
    }

    public function save_billing_engine_settings()
    {
        try {
            $setting_id = Input::get('setting_id');
            $setting = BillingEngineSetting::find($setting_id);

            if ($setting) {
                $new_name = Input::get('settings_name');
                $new_value = Input::get('settings_value');
                $setting->name = $new_name;
                $setting->value = $new_value;
                $setting->save();
            }

            return Redirect::back()->with('successMessage', 'Saved changes to setting');
        } catch (Exception $e) {
            return Redirect::back()->with('errorMessage', '<b>Error:</b> '.$e->getMessage());
        }
    }

    public function delete_billing_engine_settings()
    {
        try {
            $setting_id = Input::get('setting_id');
            $setting = BillingEngineSetting::find($setting_id);

            if ($setting) {
                $setting->delete();
            }

            return Redirect::back()->with('successMessage', '<b>Success:</b> Deleted setting');
        } catch (Exception $e) {
            return Redirect::back()->with('errorMessage', '<b>Error:</b> '.$e->getMessage());
        }
    }

    public function add_billing_engine_settings()
    {
        try {
            $setting_name = Input::get('setting_name');
            $setting_value = Input::get('setting_value');

            $newSetting = new BillingEngineSetting();
            $newSetting->name = $setting_name;
            $newSetting->value = $setting_value;
            $newSetting->save();

            if ($newSetting) {
                return Redirect::back()->with('successMessage', "<b>Success:</b> Added new setting: $setting_name : $setting_value");
            }
        } catch (Exception$e) {
            return Redirect::back()->with('errorMessage', '<b>Error:</b> '.$e->getMessage());
        }
    }

    public function index_shut_off_engine_settings()
    {
        $settings = SystemSetting::where('type', 'shut_off_engine')->get();

        $enabled = SystemSetting::where('name', 'shut_off_engine_new_enabled')->first();
        $old_enabled = CalendarDailySchedule::where('program', 'b')->get();

        if ($old_enabled->count() <= 0) {
            $old_enabled = false;
        } else {
            $old_enabled = true;
        }

        $this->layout->page = view('home/programs/shut_off_engine_settings',
        [
            'settings' => $settings,
            'enabled' => $enabled,
            'old_enabled' => $old_enabled,
        ]);
    }

    public function save_programs_shut_off_engine_settings()
    {
        try {
            $new = (Input::get('new_shut_off_engine') == 'on') ? true : false;
            $old = (Input::get('old_shut_off_engine') == 'on') ? true : false;

            $setting = SystemSetting::where('name', 'shut_off_engine_new_enabled')->first();

            if (($new && $old) || (! $new && ! $old) || (! $new && $old)) {
                $new = false;
                if ($setting) {
                    $setting->value = 'false';
                    $setting->save();
                }

                CalendarDailySchedule::where('program', 'ns')->update([
                    'program' => 's',
                ]);

                return Redirect::back()->with('successMessage', 'Successfully enabled the Old Shut Off Engine.');
            }

            if ($new && ! $old) {
                if ($setting) {
                    $setting->value = 'true';
                    $setting->save();
                }

                CalendarDailySchedule::where('program', 's')->update([
                    'program' => 'ns',
                ]);

                return Redirect::back()->with('successMessage', 'Successfully enabled the New Shut Off Engine.');
            }
        } catch (Exception $e) {
            return Redirect::back()->with('errorMessage', '<b>Error:</b> '.$e->getMessage());
        }
    }

    public function save_shut_off_engine_settings()
    {
        try {
            $setting_id = Input::get('setting_id');
            $setting = SystemSetting::find($setting_id);

            if ($setting) {
                $new_name = Input::get('settings_name');
                $new_value = Input::get('settings_value');
                $setting->name = $new_name;
                $setting->value = $new_value;
                $setting->save();
            } else {
                throw new Exception("Setting # $setting_id does not exist.");
            }

            return Redirect::back()->with('successMessage', 'Saved changes to setting');
        } catch (Exception $e) {
            return Redirect::back()->with('errorMessage', '<b>Error:</b> '.$e->getMessage());
        }
    }

    public function delete_shut_off_engine_settings()
    {
        try {
            $setting_id = Input::get('setting_id');
            $setting = SystemSetting::find($setting_id);

            if ($setting) {
                $setting->delete();
            } else {
                throw new Exception("Setting # $setting_id does not exist, therefore cannot delete.");
            }

            return Redirect::back()->with('successMessage', '<b>Success:</b> Deleted setting');
        } catch (Exception $e) {
            return Redirect::back()->with('errorMessage', '<b>Error:</b> '.$e->getMessage());
        }
    }

    public function add_shut_off_engine_settings()
    {
        try {
            $setting_name = Input::get('setting_name');
            $setting_value = Input::get('setting_value');

            $newSetting = new SystemSetting();
            $newSetting->type = 'shut_off_engine';
            $newSetting->name = $setting_name;
            $newSetting->value = $setting_value;
            $newSetting->save();

            if ($newSetting) {
                return Redirect::back()->with('successMessage', "<b>Success:</b> Added new setting: $setting_name : $setting_value");
            }
        } catch (Exception$e) {
            return Redirect::back()->with('errorMessage', '<b>Error:</b> '.$e->getMessage());
        }
    }

    public function update()
    {
    }

    public function shut_off_engine_manager()
    {
        $holiday_enabled = SystemSetting::get('holiday_service');
        $holiday_days = SystemSetting::get('holiday_days');

        $this->layout->page = view('home/programs/shut_off_engine', [
            'holiday_enabled' => $holiday_enabled,
            'holiday_days' => json_decode($holiday_days),
        ]);
    }

    public function shut_off_engine_manager_save()
    {
        try {
            $holiday_enabled = (Input::get('holiday_enabled') == 'on') ? 1 : 0;

            SystemSetting::modify('holiday_service', 'value', $holiday_enabled);

            $holiday_days = Input::get('holiday_days');
            $holiday_days = preg_replace('/[[:^print:]]/', '', $holiday_days);

            SystemSetting::modify('holiday_days', 'value', $holiday_days);

            /*
            foreach($holiday_days as $key => $day) {

                $name = $day->name;
                $date = $day->date;

            }
            */

            return Redirect::back()->with([
                'successMessage' => 'Successfully saved changes to shut off engine!',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }
}
