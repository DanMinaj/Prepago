<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Scheme;
use App\Models\WatchDog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;



class WatchDogController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function startWatchDog()
    {
        try {
            $permanent_meter_id = Input::get('permanent_meter_id');
            $customer_id = Input::get('customer_id');
            $run_every = Input::get('run_every');
            $run_times = Input::get('run_times');
            $port = Input::get('port');

            WatchDog::execute($permanent_meter_id, $run_every, $run_times, $port, $customer_id);

            return Redirect::back()->with([
                'successMessage' => 'Successfully started watchdog for customer #'.$customer_id,
            ]);
        } catch (Exception $e) {
            return 'Error: '.$e->getMessage();
        }
    }

    public function stopWatchDog()
    {
        $runningWatchDog = WatchDog::runningInScheme();

        $runningWatchDog->stop();

        return Redirect::back()->with(['successMessage' => 'Successfully stopped the watch dog running in this scheme']);
    }

    public function emailWatchDog()
    {
        $id = Input::get('id');

        $watchdog = WatchDog::where('id', $id)->first();

        if (! $watchdog) {
            return Response::json([
                'error' => true,
                'error_msg' => 'This watchdog ID does not exist!',
                'success' => false,
                'success_msg' => '',
            ]);
        }

        $email = Input::get('email');
        $title = Input::get('title');
        $watchdog->emailTelegram($email, $title);

        return Response::json([
            'success' => true,
            'success_msg' => 'Successfully sent copy of watchdog to '.$email,
            'error' => false,
            'error_msg' => '',
        ]);
    }

    public function watchdogs($customer_id = null)
    {
        $customer = null;
        $show_all = false;
        $watchdogs = $watchdogs = WatchDog::orderBy('id', 'DESC')->get();
        if (! isset($customer_id)) {
            $show_all = true;
        }

        if (isset($customer_id)) {
            $customer = Customer::where('id', $customer_id)->first();
        }

        if (! $customer) {
            $show_all = true;
        } else {
            $show_all = false;
            $watchdogs = WatchDog::where('permanent_meter_id', $customer->permanentMeter()->ID)->orderBy('id', 'DESC')->get();
        }

        $this->layout->page = view('home/watchdog/watchdogs', ['show_all' => $show_all, 'customer' => $customer, 'watchdogs' => $watchdogs]);
    }

    public function view_watchdog($id)
    {
        $watchdog = WatchDog::where('id', $id)->first();

        if (! $watchdog) {
            return Redirect::back()->with(['errorMessage', 'This watchdog id does not exist']);
        }

        $watchdog->markViewed();

        $this->layout->page = view('home/watchdog/view_watchdog', ['watchdog' => $watchdog]);
    }

    public function view_csv_watchdog($id)
    {
        $watchdog = WatchDog::where('id', $id)->first();

        if (! $watchdog) {
            return Redirect::back()->with(['errorMessage', 'This watchdog id does not exist']);
        }

        ini_set('max_execution_time', 1800);
        ini_set('memory_limit', '1024M');

        $data = '';

        $watchdog_parts = preg_split('/\r\n|\r|\n/', $watchdog->telegram_returned);
        $telegrams = [];
        $telegrams_success = [];
        $i = 0;

        $watchdog->markViewed();

        foreach ($watchdog_parts as $line) {
            if (trim($line) == '') {
                continue;
            }

            if (strpos($line, '--------------------') !== false) {
                $i++;
                continue;
            }

            if (! isset($telegrams[$i])) {
                $telegrams[$i] = $line."\n";
            } else {
                $telegrams[$i] .= $line."\n";
            }

            if (strpos($line, 'Success') !== false) {
                $telegrams_success[$i] = explode('Successful attempt at ', $line)[1];
                continue;
            } else {
                $telegrams_success[$i] = '';
            }
        }

        $data .= 'Customer:, '.$watchdog->customer_id.' ('.($watchdog->customer ? $watchdog->customer->username : '').")\n";
        $data .= 'Scheme:, '.Scheme::where('id', $watchdog->scheme_number)->first()->company_name."\n";
        $data .= 'Started at:, '.$watchdog->created_at."\n";
        $data .= 'Completed at:, '.$watchdog->completed_at."\n";
        $data .= 'Type:, '.($watchdog->run_times).' runs every '.$watchdog->run_every." minutes\n";
        $data .= 'Port:, '.$watchdog->port."\n";

        $data .= "\n\n";

        foreach ($telegrams as $key => $t) {
            if (empty($t)) {
                continue;
            }
            if (strlen($t) < 30) {
                continue;
            }

            $data .= 'Telegram, #'.($key + 1)."\n";
            $data .= 'Retrieved at, '.$telegrams_success[$key]."\n";
            $telegram_lines = preg_split('/\r\n|\r|\n/', $t);
            foreach ($telegram_lines as $line) {
                $data .= $line."\n";
            }

            $data .= "\n\n\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=watchdog_'.$watchdog->id.'_report.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;

        die();
    }
}
