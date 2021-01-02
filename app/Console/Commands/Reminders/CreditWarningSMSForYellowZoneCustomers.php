<?php

use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreditWarningSMSForYellowZoneCustomers extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'sms:credit-warning-yellow-zone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends SMS to all customers from all schemes in the yellow zone who have received a credit warning, but are not shut off yet.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('Credit Warning SMS Yellow Zone log');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/CreditWarningSMSForYellowZoneCustomers/'.date('Y-m-d').'.log'), Logger::INFO);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $sms = new SMSController();
        $found = false;
        $sent = 0;

        foreach (Scheme::withoutArchived()->get() as $scheme) {
            $yellowCustomers = Customer::pendingShutOff()
                                        ->where('customers.status', '=', 1)
                                        ->where('customers.credit_warning_sent', 1)
                                        ->where('customers.scheme_number', '=', $scheme->scheme_number)
                                        ->get();

            foreach ($yellowCustomers as $customer) {
                try {
                    $found = true;
                    $sent++;
                    $this->log->addInfo('Sending credit warning SMS to customer '.$customer->username);

                    $sms->credit_warning($customer->id, $scheme->scheme_number, $scheme->sms_password);

                    if ($customer->districtMeter) {
                        if ($customer->districtMeter->scheduled_to_shut_off != 1) {
                        }
                    }

                    $this->log->addInfo('Credit warning SMS sent to customer '.$customer->username);
                } catch (Exception $e) {
                    $this->log->addInfo('An error occurred during credit warning SMS sending for customer '.$customer->username);
                }
            }
        }

        if (! $found) {
            $this->log->addInfo('No customers to send Credit warning SMS to');
            $this->info('No customers to send Credit warning SMS to');
        } else {
            $this->log->addInfo('Sent Credit warning SMS to '.$sent.' customers!');
            $this->info('Sent Credit warning SMS to '.$sent.' customers!');
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
