<?php

namespace App\Http\Controllers;

use App\Models\CalendarDailySchedule;
use App\Models\System;
use App\Models\SystemSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class TemperatureControlController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function index()
    {
        $tcp_customers = System::getTcpCustomers();

        $restored = $tcp_customers['require_restoration'];
        $shutOff = $tcp_customers['require_shut_off'];
        $shutOffMetersAwayMode = $tcp_customers['require_away_mode'];

        //$time = '23:46:00';
        $time = date('H:i:s');

        $settings = SystemSetting::where('type', 'valve')->get();
        $schedule = CalendarDailySchedule::where('program', 'tc')->orderBy('time', 'ASC')->get();
        $next_tc = CalendarDailySchedule::where('program', 'tc')->whereRaw("
			(time >= '$time')
		")->orderBy('time', 'ASC')->first();

        if (! $next_tc) {
            $next_tc = CalendarDailySchedule::where('program', 'tc')->orderBy('time', 'ASC')->first();
            $next_tc->tomorrow = true;
        }

        $this->layout->page = view('home/programs/temp_control',
        [
            'settings' => $settings,
            'restored' => $restored, // require restoration
            'shutOff' => $shutOff, // require shut off
            'shutOffMetersAwayMode' => $shutOffMetersAwayMode, // require close valve for away mode
            'schedule' => $schedule,
            'next_tc' => $next_tc,
        ]);
    }

    public function edit_schedule()
    {
        try {
            foreach (Input::all() as $key => $input) {
                if (strpos($input, 'input') === -1) {
                    continue;
                }

                $scheduleID = explode('_', $key)[0];
                $schedule = CalendarDailySchedule::where('id', $scheduleID)->first();
                $newValue = $input;

                if ($schedule) {
                    $schedule->time = $newValue;
                    $schedule->save();
                }
            }

            return Redirect::back()->with(['successMessage' => 'Successfully edited schedule']);
        } catch (Exception $e) {
            return Redirect::back()->with(['errorMessage' => $e->getMessage()]);
        }
    }
}
