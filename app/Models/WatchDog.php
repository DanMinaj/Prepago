<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WatchDog extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'watchdogs';

    public static function execute($permanent_meter_id, $run_every, $run_times, $port, $customer_id = 0)
    {
        $wd = new self();
        $wd->telegram_returned = '';
        $wd->operator_id = Auth::user()->id;
        $wd->scheme_number = Auth::user()->scheme_number;
        $wd->permanent_meter_id = $permanent_meter_id;
        $wd->customer_id = $customer_id;
        $wd->port = $port;
        $wd->run_every = $run_every;
        $wd->run_times = $run_times;
        $wd->run_next = date('Y-m-d H:i:s');
        $wd->failed_attempts = 0;
        $wd->max_failed_attempts = 5;
        $wd->completed = 0;
        $wd->completed_at = null;
        $wd->created_at = date('Y-m-d H:i:s');
        $wd->updated_at = date('Y-m-d H:i:s');
        $wd->save();

        //$wd->run();

        $res = '';
        $res .= 'ID:'.$wd->id."\n";
        $res .= 'Run every:'.$wd->run_every." hrs\n";
        $res .= 'Run times:'.$wd->run_times." times\n";
        $res .= 'PM ID:'.$wd->permanent_meter_id."\n";

        //Artisan::call("watchdog:run");

        return $res;
    }

    public static function lastWatchDog($customer_id)
    {
        return self::where('customer_id', $customer_id)
        ->orderBy('id', 'DESC')->first();
    }

    public function stop()
    {
        $this->completed = true;
        //$this->ran_times = $this->run_times;
        $this->completed_at = date('Y-m-d H:i:s');
        $this->save();
    }

    public function run()
    {
        $pmd = PermanentMeterData::where('ID', '=', $this->permanent_meter_id)->get()->first();
        if (! $pmd) {
            return false;
        }

        $sim = Simcard::where('ID', '=', $pmd->sim_ID)->get()->first();
        if (! $sim) {
            return false;
        }

        $scheme = Scheme::where('scheme_number', '=', $this->scheme_number)->get()->first();

        if (! $scheme) {
            return false;
        }

        $pmdmrwMeterNumber2 = str_replace($scheme['prefix'], '', $pmd->meter_number2);

        $pmdtrw = new PermanentMeterDataTelegramRelaycheckWebsite();
        $pmdtrw->isTelegramReq = 1;
        $pmdtrw->permanent_meter_id = $this->permanent_meter_id;
        $pmdtrw->scheme_number = $this->scheme_number;
        $pmdtrw->ICCID = $sim['ICCID'];
        $pmdtrw->data_logger_id = $pmd->data_logger_id;
        $pmdtrw->meter_number = str_replace($scheme['prefix'], '', $pmd->meter_number);
        $pmdtrw->meter_number2 = $pmdmrwMeterNumber2 ?: 'N/A';
        $pmdtrw->watchdog = true;

        $pmdtrw->port = $this->port;

        $pmdtrw->save();

        return true;
    }

    public function getLastRelayAttribute()
    {
        return PermanentMeterDataTelegramRelaycheckWebsite::where('permanent_meter_id', '=', $this->permanent_meter_id)->where('watchdog', true)->orderBy('time_date', 'desc')->first();
    }

    public function getAwaitingAcknowledgementAttribute()
    {
        if (! $this->lastRelay) {
            return false;
        }

        if ($this->lastRelay->acknowledged == false) {
            return true;
        }
    }

    public function getWaitingAttribute()
    {
        if (! $this->lastRelay) {
            return false;
        }

        if ($this->lastRelay->complete == 0 && $this->lastRelay->fail == 0) {
            return true;
        }
    }

    public function acknowledgeLastRelay()
    {
        $lastRelay = $this->lastRelay;

        DB::table('permanent_meter_data_telegram_relaycheck_website')
        ->where('ID', $this->lastRelay->ID)
        ->update(
            [
                'acknowledged' => 1,
            ]
        );

        $this->touch();
    }

    public function getInfo()
    {
        $info = '';

        $scheme = Scheme::where('id', $this->scheme_number)->first();
        $pmd = PermanentMeterData::where('ID', $this->permanent_meter_id)->first();
        $customer = Customer::where('username', $pmd->username)->first();

        if ($customer) {
            $customer_id = $customer->id;
        } else {
            $customer_id = 'null';
        }

        $info .= 'Scheme: '.$scheme->company_name."\n";
        $info .= 'Username: '.$pmd->username."\n";
        $info .= "Customer: $customer_id\n";
        $info .= 'Last updated: '.Carbon\Carbon::parse($this->updated_at)->diffForHumans()."\n";

        if ($this->run_times <= 0) {
            $info .= "0% complete\n";
        } else {
            $info .= ceil(($this->ran_times / $this->run_times) * 100)."% complete\n";
        }

        return $info;
    }

    public function permanentMeter()
    {
        $pmd = PermanentMeterData::where('ID', '=', $this->permanent_meter_id)->get()->first();

        return $pmd;
    }

    public function getStatusCss()
    {
        if ($this->completed) {
            return '<div style="width: 75%; height: 5px; line-height: 5px; margin: 0px;" class="alert alert-success alert-block"> <center> Completed </center> </div>';
        } else {
            return '<div style="width: 75%; height: 5px; line-height: 5px; margin: 0px;" class="alert alert-warning alert-block"> <center> Running </center> </div>';
        }
    }

    public function getTelegram($arr = true)
    {
        $watchdog_parts = preg_split('/\r\n|\r|\n/', $this->telegram_returned);
        $telegrams = [];
        $telegrams_success = [];
        $i = 0;
        $data = '';

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

        if ($arr) {
            return ['telegrams' => $telegrams, 'telegrams_success' => $telegrams_success];
        } else {
            $data .= '<b>Customer:</b> '.$this->permanentMeter()->username."\n";
            $data .= '<b>Scheme:</b> '.Scheme::where('id', $this->scheme_number)->first()->company_name."\n";
            $data .= '<b>Started at:</b> '.$this->created_at."\n";
            $data .= '<b>Completed at:</b> '.$this->completed_at."\n";
            $data .= '<b>Duration:</b> '.($this->run_times)." runs\n";
            $data .= '<b>Port:</b> '.$this->port."\n";

            $data .= "\n\n";

            foreach ($telegrams as $key => $t) {
                if (empty($t)) {
                    continue;
                }
                if (strlen($t) < 30) {
                    continue;
                }

                $data .= '<b>Telegram #'.($key + 1)."</b>\n";
                $data .= '<b>Retrieved at:</b> '.$telegrams_success[$key]."\n";
                $telegram_lines = preg_split('/\r\n|\r|\n/', $t);
                foreach ($telegram_lines as $line) {
                    $data .= $line."\n";
                }

                $data .= "\n\n\n";
            }

            return $data;
        }
    }

    public function markViewed()
    {
        if (! $this->operator_viewed) {
            $this->operator_viewed = true;
            $this->save();
        }
    }

    public function emailTelegram($email, $title)
    {
        $msg = '';
        $msg .= 'Dear operator <br/><br/>';
        $msg .= 'A copy of <b>watchdog #'.$this->id."</b> was requested to be emailed to you by the operator '".Auth::user()->username."'. <br/><br/>";
        $msg .= nl2br($this->getTelegram(false));

        Email::quick_send($msg, $title, $email, 'support@prepago.ie', 'Prepago Watchdog Administrator');

        return true;
    }

    public static function runningInScheme($scheme_number = null)
    {
        if ($scheme_number == null) {
            $scheme_number = Auth::user()->scheme_number;
        }

        $last_watchdog = self::where('scheme_number', $scheme_number)->orderBy('id', 'DESC')->first();

        if ($last_watchdog) {
            if ($last_watchdog->completed) {
                return false;
            } else {
                return $last_watchdog;
            }
        }

        return false;
    }

    public static function unconfirmedWatchDog($pmd)
    {
        $unconfirmedWatchDog = self::where('permanent_meter_id', $pmd)->orderBy('id', 'ASC')->where('operator_viewed', false)->first();

        return $unconfirmedWatchDog;
    }

    public static function watchDogCompleted($pmd)
    {
        $wd = self::where('permanent_meter_id', $pmd)->orderBy('id', 'ASC')->where('operator_viewed', false)->first();

        if (! $wd->completed) {
            return false;
        }

        return true;
    }

    public static function getCurrent($customer)
    {
        echo $customer;
    }

    public function getCustomerAttribute()
    {
        return Customer::find($this->customer_id);
    }

    public function getNextIterationAttribute()
    {
        try {
            $lastRan = Carbon\Carbon::parse($this->updated_at);
            $now = Carbon\Carbon::parse(date('Y-m-d H:i:s'));
            $minsPassed = $now->diffInMinutes($lastRan);
            $secsToBePassed = $this->run_every * 60 * 60;

            $now = new DateTime($this->updated_at);
            $now->modify("+ $secsToBePassed seconds");

            return $now->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return 'n/a';
        }
    }
}
