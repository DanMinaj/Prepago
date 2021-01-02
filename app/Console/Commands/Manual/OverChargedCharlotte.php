<?php

namespace App\Console\Commands\Manual;

use App\Models\BillingEngineLogs;
use App\Models\Customer;
use App\Models\DistrictHeatingUsage;
use App\Models\Tariff;
use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;



class OverChargedCharlotte extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'check:charlotte';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check customers in charlotte who were overcharged';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('Check');
        $this->log->pushHandler(new StreamHandler(storage_path('logs/charlotte/insert-usage-logs-'.date('Y-m-d').'.log'), Logger::INFO));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        ini_set('memory_limit', '-1');

        $targetID = 1339;

        $charlotteCustomers = Customer::where('scheme_number', 22)
        ->where('id', $targetID)
        ->orderBy('id', 'DESC')
        ->get();

        $tariff = Tariff::where('scheme_number', 22)->first();

        foreach ($charlotteCustomers as $c) {
            $totalUsage = 0;
            $totalStandingCharge = 0;
            $totalUnitCharge = 0;
            $totalCOD = 0;

            $totalActualUsage = 0;
            $totalActualStandingCharge = 0;
            $totalActualUnitCharge = 0;
            $totalActualCOD = 0;

            $startReading = 0;
            $endReading = 0;

            if (! $c->permanentMeter) {
                continue;
            }

            $current_date = $c->commencement_date;
            $end_date = '2018-11-26';

            while ($current_date != $end_date) {
                $dhu = DistrictHeatingUsage::where('customer_id', $c->id)->where('date', $current_date)->first();

                if (! $dhu) {
                    $current_date = (new DateTime($current_date));
                    $current_date->modify('+1 day');
                    $current_date = $current_date->format('Y-m-d');

                    continue;
                }

                $startDayReading = BillingEngineLogs::getStartDayReading($c->id, $current_date);
                $endDayReading = BillingEngineLogs::getEndDayReading($c->id, $current_date);
                $expectedUnitCharge = $tariff->tariff_1 * (abs($endDayReading - $startDayReading));

                $actualUnitCharge = $dhu->unit_charge;
                $actualStandingCharge = $dhu->standing_charge;

                if ($startReading == 0) {
                    $startReading = $startDayReading;
                }

                $endReading = $endDayReading;

                //$this->info("Processing date $current_date");

                /*
                $this->info("Date " . $current_date );
                $this->info("Start Day Reading: " . $startDayReading);
                $this->info("End Day Reading: " . $endDayReading);
                $this->info("Expected Unit Charge: " . $expectedUnitCharge);
                $this->info("Actual Unit Charge: " . $actualUnitCharge);
                */

                $totalStandingCharge += $tariff->tariff_2;

                $totalActualUsage += abs($dhu->end_day_reading - $dhu->start_day_reading);
                $totalActualStandingCharge += $actualStandingCharge;
                $totalActualUnitCharge += $actualUnitCharge;
                $totalActualCOD += ($actualStandingCharge + $actualUnitCharge);

                $current_date = (new DateTime($current_date));
                $current_date->modify('+1 day');
                $current_date = $current_date->format('Y-m-d');
            }

            $totalUsage = ($endReading - $startReading);
            $totalUnitCharge = ($endReading - $startReading) * $tariff->tariff_1;
            $totalCOD = ($totalUnitCharge + $totalStandingCharge);

            $this->info("\n");
            $this->info('[Static variables]');
            $this->info('Standing charge: €'.$tariff->tariff_2);
            $this->info('Unit charge: €'.$tariff->tariff_1.'');
            $this->info("[/Static variables]\n");

            $this->info('Customer ID: '.$c->id."\n");

            $this->info("Reading range: $startReading -> $endReading ");
            $this->info("Expected Total kWh Usage: $totalUsage kWh ");
            $this->info("Actual Total kWh Usage: $totalActualUsage kWh \n");

            $this->info('Expected Total Cost: €'.$totalCOD);
            $this->info('Actual Total Cost: €'.$totalActualCOD."\n");

            $this->info('Expected Total kWh Cost: €'.$totalUnitCharge);
            $this->info('Actual Total kWh Cost: €'.$totalActualUnitCharge."\n");

            $this->info('Expected Total Standing Charge: €'.$totalStandingCharge);
            $this->info('Actual Total Standing Charge: €'.$totalActualStandingCharge."\n");

            if ($totalActualCOD > $totalCOD) {
                $this->info('Overcharged customer!: Must refund €'.($totalActualCOD - $totalCOD));
            }
            if ($totalActualCOD < $totalCOD) {
                $this->info('Undercharged customer!: Must deduct €'.($totalCOD - $totalActualCOD));
            }

            $this->info("\n");
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
