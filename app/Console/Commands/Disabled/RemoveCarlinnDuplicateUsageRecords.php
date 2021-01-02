<?php

namespace App\Console\Commands\Disabled;

use App\Models\DistrictHeatingUsage;
use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;



class RemoveCarlinnDuplicateUsageRecords extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'carlinn:dhu-duplicates';

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
        $this->log = new Logger('Carlinn DHU Duplicates Removal Log');
        $this->log->pushHandler(new StreamHandler(storage_path('logs/carlinn_dhu_duplicates_removal_'.date('Y-m-d').'.log'), Logger::INFO));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $dhuCustomerID = null;

        $dhu = DistrictHeatingUsage
                                ::where('scheme_number', 3)
                                ->where('ev_meter_ID', 0)
                                ->where('date', \Carbon\Carbon::now()->toDateString())
                                ->orderBy('customer_id', 'DESC')
                                ->orderBy('ev_timestamp', 'ASC')
                                ->get();

        foreach ($dhu as $dhuReading) {
            if ($dhuCustomerID != $dhuReading->customer_id) {
                $dhuCustomerID = $dhuReading->customer_id;
                continue;
            }

            $this->log->addInfo('Deleting DHU entry ', $dhuReading->toArray());
            $dhuReading->delete();
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
