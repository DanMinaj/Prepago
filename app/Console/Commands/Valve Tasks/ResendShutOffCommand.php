<?php

use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ResendShutOffCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'shutoff:resend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'resend shutoff.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('Shut off resend log');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/ResendShutOffCommand/'.date('Y-m-d').'.log', Logger::INFO));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        ini_set('memory_limit', '-1');

        sleep(10);

        $customers_to_shut_off = Customer::pendingShutOff()
        ->where('customers.status', '=', 1)->get();

        if (count($customers_to_shut_off) == 0) {
            $this->info('No customers to shut off');
        }

        foreach ($customers_to_shut_off as $customer) {
            $meter = $customer->districtMeter;
            $pmd = $customer->permanentMeter;

            if ($meter) {

                /*
                if($meter->scheduled_to_shut_off == 0) {
                    $meter->scheduled_to_shut_off = 1;
                    $meter->save();
                }
                */

                if ($customer->balance <= 0.00 && ! $meter->scheduled_to_shut_off) {
                    $this->log->addInfo('Schedule shut off resent', ['customer_id' => $customer->id, 'balance' => $customer->balance]);
                    $this->info('Scheduling to shut off customer '.$customer->id.': Balance = '.$customer->balance.': Scheduled: '.(($customer->districtMeter) ? $customer->districtMeter->scheduled_to_shut_off : ''));

                    $meter->scheduled_to_shut_off = 1;
                    $meter->save();

                    /*
                    $meter->last_shut_off_time = date("Y-m-d H:i:s");
                    $meter->shut_off_device_status = 1;
                    $meter->save();
                    if($pmd) {
                        $pmd->shut_off = 1;
                        $pmd->save();
                    }
                    $customer->shut_off = 1;
                    $customer->save();
                    */
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
