<?php

namespace App\Console\Commands\Manual;

use App\Models\Customer;
use App\Models\DistrictHeatingMeter;
use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;



class FixCharlotte extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'fix:charlotte';

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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $charlotte_meters = DistrictHeatingMeter::where('scheme_number', 22)->get();

        foreach ($charlotte_meters as $meter) {
            $meterNum = $meter->meter_number;
            $meterID = $meter->meter_ID;
            $pmID = $meter->permanent_meter_ID;

            $has_duplicates = false;
            $check_duplicates = DistrictHeatingMeter::where('permanent_meter_ID', $pmID)->get();

            if ($check_duplicates->count() > 1) {
                $this->info($meterNum.' with permanent_meter_ID '.$pmID.' has duplicates.. ');
                foreach ($check_duplicates as $key=>$duplicate) {
                    $this->info(($key + 1).' meter_id: '.$duplicate->meter_ID.'');
                    $customer = Customer::where('meter_ID', $duplicate->meter_ID)->first();

                    if (! $customer) {
                        $this->info('Must delete meter_id: '.$duplicate->meter_ID.'');
                        $duplicate->delete();
                    }
                }
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
