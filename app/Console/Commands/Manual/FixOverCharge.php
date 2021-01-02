<?php

use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class FixOverCharge extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'fix:overcharge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
    }

    public function delete_duplicate_dhu($date = '2019-03-12')
    {
        ob_start();
        ini_set('memory_limit', '-1');

        try {
            $customers = Customer::where('status', 1)->get();

            foreach ($customers as $c) {
                $dhu = DistrictHeatingUsage::where('customer_id', $c->id)
                ->where('date', $date)->orderBy('id', 'ASC')->first();

                if ($dhu) {
                    $this->info('Customer: '.$c->id);
                    $this->info('Original DHU: '.$dhu->id);
                    $this->info('Cost of day: '.$dhu->cost_of_day);
                    $this->info('Total usage: '.$dhu->total_usage);
                    $this->info('Unit charge: '.$dhu->unit_charge);

                    $duplicates = DistrictHeatingUsage::where('customer_id', $c->id)
                    ->where('date', $date)->where('id', '!=', $dhu->id)->get();

                    if ($duplicates) {
                        foreach ($duplicates as $key => $d) {
                            $this->info('Duplicate #'.($key + 1).' found!');
                            $this->info('ID: '.$d->id.' | Total usage: '.$d->total_usage.' | Standing charge: '.$d->standing_charge.' | '.$d->unit_charge);

                            $deletion = new DistrictHeatingUsageDeletion();
                            $deletion->customer_ID = $d->customer_id;
                            $deletion->data = json_encode($d);
                            $deletion->timestamp = date('Y-m-d H:i:s');
                            $deletion->save();

                            $d->delete();
                        }
                    }

                    $this->info("\n\n-----------------------");
                }
            }
        } catch (Exception $e) {
            $this->info('Error: '.'Line '.$e->getLine().': '.$e->getMessage());
        }
    }

    /**
     * Refund dhu other charges for a certain date.
     *
     * @return mixed
     */
    public function refund_surplus_for_date($date = '2019-03-11')
    {
        ob_start();
        ini_set('memory_limit', '-1');

        try {
            $customers = Customer::where('status', 1)->get();

            foreach ($customers as $c) {
                $tariff = Tariff::where('scheme_number', $c->scheme_number)->first();

                if (! $tariff) {
                    continue;
                }

                $per_kwh = $tariff->tariff_1;
                $standing = $tariff->tariff_2;

                $yesterdays_usages = DistrictHeatingUsage::where('date', $date)
            ->where('customer_id', $c->id)->orderBy('id', 'ASC')->first();

                if (! $yesterdays_usages) {
                    continue;
                }

                $unit_charge = $per_kwh * $yesterdays_usages->total_usage;

                $actual_cod = $yesterdays_usages->cost_of_day;
                $expected_cod = $yesterdays_usages->standing_charge + $unit_charge + $yesterdays_usages->arrears_repayment;

                $surplus = $actual_cod - $expected_cod;

                if ($surplus > 0.01) {
                    $this->info('ID: '.$yesterdays_usages->id);
                    $this->info('Date: '.$yesterdays_usages->date);
                    $this->info('Customer: '.$yesterdays_usages->customer_id.'('.$c->username.')');
                    $this->info('Per kwh: '.$per_kwh);
                    $this->info('Usage: '.$yesterdays_usages->total_usage);
                    $this->info('Unit charge: '.$unit_charge);
                    $this->info('Expected C.O.D: '.$expected_cod);
                    $this->info('Actual C.O.D: '.$actual_cod);
                    $this->info('Surplus: '.$surplus);

                    $duplicate = DistrictHeatingUsage::where('date', '2019-03-11')->where('customer_id', $yesterdays_usages->customer_id)->where('id', '!=', $yesterdays_usages->id)->get();
                    if ($duplicate->first()) {
                        foreach ($duplicate as $d) {
                            $this->info('Duplicate found! ID: '.$d->id);
                        }
                    }

                    $c->balance += $surplus;
                    $c->save();
                    $yesterdays_usages->unit_charge = $unit_charge;
                    $yesterdays_usages->cost_of_day = $expected_cod;
                    $yesterdays_usages->save();

                    $dhl = new DistrictHeatingUsageLog();
                    $dhl->customer_ID = $yesterdays_usages->customer_id;
                    $dhl->meter_number = $c->districtMeter->meter_number;
                    $dhl->permanent_meter_ID = $yesterdays_usages->permanent_meter_id;
                    $dhl->log = "Refunded surplus charge of $surplus to customer ".$c->id.'.';
                    $dhl->timestamp = date('Y-m-d H:i:s');
                    $dhl->save();

                    $this->info("\n\n-----------------------");
                } else {
                    if ($yesterdays_usages->cost_of_day != $expected_cod) {
                        $this->info('ID: '.$yesterdays_usages->id);
                        $this->info('Date: '.$yesterdays_usages->date);
                        $this->info('Customer: '.$yesterdays_usages->customer_id.'('.$c->username.')');
                        $this->info('Per kwh: '.$per_kwh);
                        $this->info('Usage: '.$yesterdays_usages->total_usage);
                        $this->info('Unit charge: '.$unit_charge);
                        $this->info('Expected C.O.D: '.$expected_cod);
                        $this->info('Actual C.O.D: '.$actual_cod);

                        $yesterdays_usages->cost_of_day = $expected_cod;
                        $yesterdays_usages->save();
                        $this->info("\n\n-----------------------");
                    }
                }
            }
        } catch (Exception $e) {
            $this->info('Error: '.'Line '.$e->getLine().': '.$e->getMessage());
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
