<?php

use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class GenerateCustomersUsage extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'generate:dhu';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert usage for missed customers into district_heating_usage';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('Insert Customers District Usage');
        $this->log->pushHandler(new StreamHandler(storage_path('logs/all_schemes/insert-usage-logs-'.date('Y-m-d').'.log'), Logger::INFO));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        ini_set('memory_limit', '-1');

        $targetID = [

            1386,

        ];

        $customerss = Customer::whereIn('id', $targetID)
        ->orderBy('id', 'DESC')
        ->get();

        foreach ($customerss as $c) {

            //$start_date = new DateTime("2018-09-01");
            //$end_date = new DateTime(

            if (! $c->permanentMeter) {
                continue;
            }

            $current_date = '2019-01-20';
            $end_date = '2019-01-22';

            while ($current_date != $end_date) {
                $file_exists = BillingEngineLogs::logExists($c->id, $current_date);
                if (! $file_exists) {
                    $this->info("Billing Log for $current_date does not exist! Skipping date.");

                    $current_date = (new DateTime($current_date));
                    $current_date->modify('+1 day');
                    $current_date = $current_date->format('Y-m-d');
                    continue;
                }

                $dhuExists = DistrictHeatingUsage::where('customer_id', $c->id)->where('date', $current_date)->first();
                if ($dhuExists) {
                    $dhuExists->delete();
                }

                $startDayReading = BillingEngineLogs::getStartDayReading($c->id, $current_date);
                $endDayReading = BillingEngineLogs::getEndDayReading($c->id, $current_date);

                $actualKwh = $endDayReading - $startDayReading;
                $totalUsage = BillingEngineLogs::getKwh($c->id, $current_date);
                $unitCharge = BillingEngineLogs::getUnitCharge($c->id, $current_date);
                $standingCharge = BillingEngineLogs::getStanding($c->id, $current_date);
                $arrearsRepayment = BillingEngineLogs::getArrearsCharge($c->id, $current_date);

                $tariff = Tariff::find($c->scheme_number);

                $this->info("\n");
                $this->info('Customer: #'.$c->id);
                $this->info('Date: '.$current_date); // mark
                $this->info('Username: '.$c->username);
                $this->info('Permanent Meter ID: '.$c->permanentMeter->ID);

                $this->info('Start Day Reading: '.$startDayReading); // mark
                $this->info('End Day Reading: '.$endDayReading); // mark

                $totalUsage = $actualKwh;
                $unitCharge = $tariff->tariff_1 * $totalUsage;
                /*
                if($actualKwh > $totalUsage) {
                    $this->info("Variables need to be recalculated. Actual usage > Calculated usage");

                }*/

                $costOfDay = $standingCharge + $unitCharge + $arrearsRepayment;

                $this->info('Total Usage: '.$totalUsage); // mark
                $this->info('Unit Charge: '.$unitCharge); // mark
                $this->info('Standing Charge: '.$standingCharge); // mark
                $this->info('Arrears Repayment: '.$arrearsRepayment); // mark
                $this->info('Cost Of Day: '.$costOfDay); // mark

                $dhu = new DistrictHeatingUsage();
                $dhu->customer_id = $c->id;
                $dhu->permanent_meter_id = $c->permanentMeter->ID;
                $dhu->ev_meter_ID = 0;
                $dhu->ev_timestamp = $current_date.' 00:05:35';
                $dhu->scheme_number = $c->scheme_number;
                $dhu->date = $current_date;
                $dhu->cost_of_day = $costOfDay;
                $dhu->start_day_reading = $startDayReading;
                $dhu->end_day_reading = $endDayReading;
                $dhu->total_usage = $totalUsage;
                $dhu->standing_charge = $standingCharge;
                $dhu->unit_charge = $unitCharge;
                $dhu->arrears_repayment = $arrearsRepayment;
                $dhu->manual = 1;
                $dhu->save();

                /**
                #
                # Increment to the next date for customer
                #
                 **/
                $current_date = (new DateTime($current_date));
                $current_date->modify('+1 day');
                $current_date = $current_date->format('Y-m-d');
            }
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
