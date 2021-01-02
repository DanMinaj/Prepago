<?php

use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class BillingForecastCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'billing:forecast';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Forecast a customers billing';

    /**
     * Set the log file & log file name.
     */
    private function setLog($name = 'default')
    {
        $this->log = new Logger($name);
        $this->log->pushHandler(new StreamHandler(__DIR__.'/BillingForecastCommand/'.date('Y-m-d').'.log', Logger::INFO));
    }

    /**
     * Create a log entry in the log file.
     */
    public function log($msg, $print = true)
    {
        $this->log->info($msg);

        if ($print) {
            $this->info($msg);
        }
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

    public function fire()
    {
        try {
            $time_start = microtime(true);

            ini_set('memory_limit', '-1');

            $this->setLog();

            $customers = Customer::getActiveCustomers();

            $customer_avgs = [

            ];

            foreach (Scheme::active(false) as $k => $scheme) {
                $customer_avgs[(string) $scheme->scheme_number] = [];
            }

            foreach ($customers as $k => $customer) {
                echo 'Customer: '.$customer->id.' ('.$customer->username.")\n";
                echo 'Balance: '.$customer->balance."\n";

                $dhu = DistrictHeatingUsage::where('customer_id', $customer->id)->get();

                foreach ($dhu as $k => $d) {
                    if (! in_array($d->scheme_number, $customer_avgs)) {
                        continue;
                    }

                    if (! in_array($d->date, $customer_avgs[(string) $d->scheme_number])) {
                        echo "\n\n";
                        echo 'Inserted new entry for date '.$d->date."\n";
                        $customer_avgs[(string) $d->scheme_number][$d->date] = $d->total_usage;
                    } else {
                    }
                }

                echo "\n\n";
            }

            $this->info('Took '.number_format((microtime(true) - $time_start), 0).' seconds to complete');
        } catch (Exception $e) {
            $this->info($e->getMessage());
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
