<?php

use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class FixCommencementDate extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'customers:commencement';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix the commencement date charging of new custoemrs. I.e on the day of their commencement make sure latest_usage = sudo_usage';

    /**
     * Set the log file & log file name.
     */
    private function setLog($name = 'default')
    {
        $this->log = new Logger($name);
        $this->log->pushHandler(new StreamHandler(__DIR__.'/FixCommencementDate/'.date('Y-m-d').'.log', Logger::INFO));
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
            $customers = Customer::where('status', 1)
            ->where('commencement_date', date('Y-m-d'))
            ->get();

            foreach ($customers as $k => $c) {
                echo $c->username." is commencing today..making sure their latest_usage = sudo_usage\n";

                $dhu = $c->districtMeter;

                if ($dhu) {
                    if (($dhu->sudo_reading > $dhu->latest_reading) && empty($dhu->commenced_at)) {
                        echo "Sudo usage ($dhu->sudo_reading) > latest usage ($dhu->latest_reading). CORRECTED!";

                        $dhu->latest_reading = $dhu->sudo_reading;
                        $dhu->commenced_at = date('Y-m-d H:i:s');
                        $dhu->save();
                    } else {
                        $dhu->commenced_at = date('Y-m-d H:i:s');
                        $dhu->save();

                        echo "Sudo usage ($dhu->sudo_reading) <= latest usage ($dhu->latest_reading).. skipping!";
                    }
                }

                echo "\n\n";
            }
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
