<?php

namespace App\Console\Commands;

use App\Models\BillingEngineLogsNew;
use App\Models\Customer;
use App\Models\DistrictHeatingUsage;
use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;



class QuickCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'quick';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quick command';

    /**
     * Set the log file & log file name.
     */
    private function setLog($name = 'default', $file_path = '/Default Logs/quickcommand.log')
    {
        $this->log = new Logger($name);
        $this->log->pushHandler(new StreamHandler(__DIR__.$file_path, Logger::INFO));
    }

    /**
     * Create a log entry in the log file.
     */
    private function createLog($msg)
    {
        $this->log->info($msg);
    }

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        ini_set('memory_limit', '-1');

        $this->setLog();

        $customers = Customer::where('status', 1)->get();

        foreach ($customers as $c) {
            ob_start();

            if (! $c->permanentMeter) {
                continue;
            }

            if (! $c->tariff) {
                continue;
            }

            $per_kwh = $c->tariff->tariff_1;
            $stand = $c->tariff->tariff_2;

            if ($stand == 0) {
                continue;
            }

            $start_date = '2019-03-29';
            $end_date = '2019-04-03';

            $this->info('Customer ID: '.$c->id);

            //$readings = PermanentMeterDataReadingsAll::where('permanent_meter_id', $c->permanentMeter->ID)->whereRaw("time_date >= '2019-03-05'")->first();
            $start_dhu = DistrictHeatingUsage::where('customer_id', $c->id)->where('date', '=', $start_date)->first();

            while ($start_date != $end_date) {
                $prev_date = ((new DateTime($start_date))->modify('- 1 day'));
                $prev_date = $prev_date->format('Y-m-d');

                $prev_dhu = DistrictHeatingUsage::where('customer_id', $c->id)->where('date', '=', $prev_date)->first();
                $start_dhu = DistrictHeatingUsage::where('customer_id', $c->id)->where('date', '=', $start_date)->first();

                if (! $prev_dhu || ! $start_dhu) {
                    break;
                }

                $log = BillingEngineLogsNew::getLog($c->id, $start_date);

                if ($start_date == '2019-03-31' || $start_date == '2019-04-01' || $start_date == '2019-04-02') {
                    $this->info('Date: '.$start_date);
                    $this->info('Log total: '.$log->total_usage);
                    $this->info('DHU total: '.$start_dhu->total_usage);
                    $this->info('Log unit_charge: '.$log->unit_charge);
                    $this->info('DHU unit_charge: '.$start_dhu->unit_charge);
                    $this->info('Log start_day_reading: '.$log->start_day_reading);
                    $this->info('DHU start_day_reading: '.$start_dhu->start_day_reading);
                    $this->info('Log end_day_reading: '.$log->end_day_reading);
                    $this->info('DHU end_day_reading: '.$start_dhu->end_day_reading);

                    if ($log->start_day_reading != 0 && $log->end_day_reading != 0) {
                        $start_dhu->start_day_reading = $log->start_day_reading;
                        $start_dhu->end_day_reading = $log->end_day_reading;
                        $start_dhu->total_usage = $log->total_usage;
                        $start_dhu->unit_charge = $log->unit_charge;
                        $start_dhu->cost_of_day = $start_dhu->unit_charge + $start_dhu->standing_charge + $start_dhu->arrears_repayment;
                        $start_dhu->save();
                    } else {
                        $start_dhu->end_day_reading = $start_dhu->start_day_reading + $start_dhu->total_usage;
                        $start_dhu->save();
                    }

                    if ($start_dhu->start_day_reading != $prev_dhu->end_day_reading) {
                        $this->info('Found inconsistency in '.$start_dhu->date);

                        $start_dhu->start_day_reading = $prev_dhu->end_day_reading;
                        $start_dhu->total_usage = ($start_dhu->end_day_reading - $start_dhu->start_day_reading);
                        $start_dhu->unit_charge = ($start_dhu->total_usage * $per_kwh);
                        $start_dhu->cost_of_day = $start_dhu->unit_charge + $start_dhu->standing_charge + $start_dhu->arrears_repayment;
                        $start_dhu->save();
                    }
                }

                $start_date = ((new DateTime($start_date))->modify('+ 1 day'));
                $start_date = $start_date->format('Y-m-d');
            }

            /*
            if($readings) {

                $reading = $readings->reading1;

                $dhu = DistrictHeatingUsage::where('customer_id', $c->id)
                ->whereRaw("date >= '2019-03-06' AND (start_day_reading < '$reading' OR end_day_reading < '$reading')")->get();

                foreach($dhu as $d) {


                $this->info("Customer " . $c->id . " | Date: " . $d->date);


                    $per_kwh = $c->tariff->tariff_1;
                    $stand = $c->tariff->tariff_2;


                    if($reading > $d->start_day_reading) {
                        $found = true;
                        $d->start_day_reading*=10;
                    }

                    if($reading > $d->end_day_reading) {
                        $found = true;
                        $d->end_day_reading*=10;
                    }

                    $d->standing_charge = $stand;
                    $d->total_usage = $d->end_day_reading - $d->start_day_reading;
                    $d->unit_charge = ($d->total_usage * $per_kwh);
                    $d->cost_of_day = ($d->unit_charge + $d->standing_charge + $d->arrears_repayment);


                    if($found) {
                        $this->info("Total usage: " . $d->total_usage);
                        $this->info("Unit charge: " . $d->unit_charge);
                        $this->info("Cost of day: " . $d->cost_of_day);
                    }


                    $d->save();
                }

            }
            */

            /*
            if($readings) {


                $this->info("Customer " . $c->id);

                $reading = $readings->reading1;

                $in = PermanentMeterDataReadingsAll::where('permanent_meter_id', $c->permanentMeter->ID)->whereRaw("time_date >= '2019-03-06' AND reading1 < '$reading'")->get();
                $in2 = PermanentMeterDataReadings::where('permanent_meter_id', $c->permanentMeter->ID)->whereRaw("time_date >= '2019-03-06' AND reading1 < '$reading'")->get();


                foreach($in2 as $d1) {

                    $this->info("1 ID " . $d1->ID . " [Original $reading] - Fixed changed " . $d1->reading1 . " to " . $d1->reading1*10 . "");
                    $d1->reading1 = $d1->reading1*10;
                    $d1->save();
                }

                foreach($in as $d) {
                    $this->info("2 ID " . $d1->ID . " [Original $reading] - Fixed changed " . $d->reading1 . " to " . $d->reading1*10 . "");
                    $d->reading1 = $d->reading1*10;
                    $d->save();
                }


            }
            */

            ob_end_flush();
        }
        //$this->createLog("Just ran ExampleCommand");
    }

    /**
        Created this script on 2nd April 2019 to fix
        the issue with customers end_day_reading-start_day_reading not conforming to the
        total_usage after the introduction of the new billing engine

        The script grabs the customers true charges from 01st April - 2nd April (or other desired dates),
        calculates their end_day_reading & start_day_reading from these log files & updates the DHU
        with those values.
     **/
    public function fixFunkyUsage()
    {
        $customers = Customer::where('status', 1)->get();

        foreach ($customers as $c) {
            ob_start();

            if (! $c->permanentMeter) {
                continue;
            }

            if (! $c->tariff) {
                continue;
            }

            $per_kwh = $c->tariff->tariff_1;
            $stand = $c->tariff->tariff_2;

            if ($stand == 0) {
                continue;
            }

            $start_date = '2019-03-29';
            $end_date = '2019-04-03';

            $this->info('Customer ID: '.$c->id);

            //$readings = PermanentMeterDataReadingsAll::where('permanent_meter_id', $c->permanentMeter->ID)->whereRaw("time_date >= '2019-03-05'")->first();
            $start_dhu = DistrictHeatingUsage::where('customer_id', $c->id)->where('date', '=', $start_date)->first();

            while ($start_date != $end_date) {
                $prev_date = ((new DateTime($start_date))->modify('- 1 day'));
                $prev_date = $prev_date->format('Y-m-d');

                $prev_dhu = DistrictHeatingUsage::where('customer_id', $c->id)->where('date', '=', $prev_date)->first();
                $start_dhu = DistrictHeatingUsage::where('customer_id', $c->id)->where('date', '=', $start_date)->first();

                if (! $prev_dhu || ! $start_dhu) {
                    break;
                }

                $log = BillingEngineLogsNew::getLog($c->id, $start_date);

                if ($start_date == '2019-03-31' || $start_date == '2019-04-01' || $start_date == '2019-04-02') {
                    $this->info('Date: '.$start_date);
                    $this->info('Log total: '.$log->total_usage);
                    $this->info('DHU total: '.$start_dhu->total_usage);
                    $this->info('Log unit_charge: '.$log->unit_charge);
                    $this->info('DHU unit_charge: '.$start_dhu->unit_charge);
                    $this->info('Log start_day_reading: '.$log->start_day_reading);
                    $this->info('DHU start_day_reading: '.$start_dhu->start_day_reading);
                    $this->info('Log end_day_reading: '.$log->end_day_reading);
                    $this->info('DHU end_day_reading: '.$start_dhu->end_day_reading);

                    if ($log->start_day_reading != 0 && $log->end_day_reading != 0) {
                        $start_dhu->start_day_reading = $log->start_day_reading;
                        $start_dhu->end_day_reading = $log->end_day_reading;
                        $start_dhu->total_usage = $log->total_usage;
                        $start_dhu->unit_charge = $log->unit_charge;
                        $start_dhu->cost_of_day = $start_dhu->unit_charge + $start_dhu->standing_charge + $start_dhu->arrears_repayment;
                        $start_dhu->save();
                    } else {
                        $start_dhu->end_day_reading = $start_dhu->start_day_reading + $start_dhu->total_usage;
                        $start_dhu->save();
                    }

                    if ($start_dhu->start_day_reading != $prev_dhu->end_day_reading) {
                        $this->info('Found inconsistency in '.$start_dhu->date);

                        $start_dhu->start_day_reading = $prev_dhu->end_day_reading;
                        $start_dhu->total_usage = ($start_dhu->end_day_reading - $start_dhu->start_day_reading);
                        $start_dhu->unit_charge = ($start_dhu->total_usage * $per_kwh);
                        $start_dhu->cost_of_day = $start_dhu->unit_charge + $start_dhu->standing_charge + $start_dhu->arrears_repayment;
                        $start_dhu->save();
                    }
                }

                $start_date = ((new DateTime($start_date))->modify('+ 1 day'));
                $start_date = $start_date->format('Y-m-d');
            }

            ob_end_flush();
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }
}
