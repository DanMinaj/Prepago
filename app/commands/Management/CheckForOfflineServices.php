<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class CheckForOfflineServices extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'services:checkstatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check whether there are services that should be running but are offline.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('Offline Services');
        $this->log->pushHandler(new StreamHandler(__DIR__ . '/OfflineServices/' . date('Y-m-d') . '.log'), Logger::INFO);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $servicesThatShouldBeRunning = PrepagoService::allServices()->lists('name');
        $servicesThatAreCurrentlyRunning = (new \Illuminate\Support\Collection(PrepagoService::getServicesFromSSH()))->lists('name');
        $offlineServices = array_diff($servicesThatShouldBeRunning, $servicesThatAreCurrentlyRunning);

        if ( ! $offlineServices) {
            $this->log->addInfo('No offline services detected');
            return;
        }

        $this->log->addInfo('The following services are currently offline : ' . implode(', ', $offlineServices));
    }

    protected function readingsWasTakenInThePast5Minutes($reading)
    {
        return $reading->time_date >= \Carbon\Carbon::now()->subMinutes(5);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array();
    }
}