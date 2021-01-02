<?php

use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MonthStartBillingIssue extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'billing:monthstart';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '1st day of the month billing issue workaround.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('Month Start Billing Logs');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/MonthStartBillingIssue/'.date('Y-m-d').'.log'), Logger::INFO);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        return;

        ini_set('memory_limit', '1024M');
        $this->log->addInfo('Starting the start of the month billing issue workaround script.');

        $yesterdaysUsage = DistrictHeatingUsage
                                        ::whereRaw('date = SUBDATE(current_date(), 1) AND (cost_of_day = 0 OR end_day_reading = 0)')
                                        ->where('cost_of_day', 0)
                                        ->get();

        foreach ($yesterdaysUsage as $yesterdayUsage) {
            $todayStartDayReading = DistrictHeatingUsage
                                            ::whereRaw('date = current_date()')
                                            ->where('customer_id', $yesterdayUsage->customer_id)
                                            ->where('start_day_reading', '>', 0)
                                            ->first();

            $tariff = Tariff::where('scheme_number', $yesterdayUsage->scheme_number)->first();
            $kwh = $tariff->tariff_1;
            $standing = $tariff->tariff_2;

            if ($todayStartDayReading) {
                $this->log->addInfo(
                    'Updating the end_day_reading of customer with id '.$yesterdayUsage->customer_id.' to '.$todayStartDayReading->start_day_reading
                );

                $yesterdayUsage->end_day_reading = $todayStartDayReading->start_day_reading;
                $yesterdayUsage->total_usage = $yesterdayUsage->end_day_reading - $yesterdayUsage->start_day_reading;
                $yesterdayUsage->unit_charge = $yesterdayUsage->total_usage * $kwh;
                //$yesterdayUsage->standing_charge = $standing;
                $yesterdayUsage->cost_of_day = $yesterdayUsage->unit_charge + $yesterdayUsage->standing_charge + $yesterdayUsage->arrears_repayment;
                $yesterdayUsage->save();
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
