<?php

use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MoveTempPaymentsCommand extends Command
{
    protected $name = 'payments:movetmp';
    protected $description = 'Move temp payments to payments_storage';

    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('Temporary Payments Logs');
        $this->log->pushHandler(new StreamHandler(storage_path('logs/temporary_payments_'.date('Y-m-d').'.log'), Logger::INFO));
    }

    public function fire()
    {
        $this->movePayments();
    }

    public function movePayments()
    {
        //fetch the temporary payment which are NOT made within the last 24hours
        $tempPayments = TemporaryPayments::readyToMove()->get();
        foreach ($tempPayments as $tempPayment) {
            //convert the object to array to prepare it for saving
            $paymentData = $tempPayment->toArray();
            $paymentData['payment_received'] = 1;

            $this->info('Moving temp payment '.$tempPayment->ref_number);

            //move the records from the temporary_payments table to the payments_storage table
            if (! PaymentStorage::create($paymentData)) {
                $this->error('Temp payment '.$tempPayment->ref_number.' could not be moved to payments_storage');

                return;
            }

            //write the temporary payment information to a log file - just in case
            $this->log->addInfo('Temporary Payment Information', $paymentData);

            //delete current temporary payment
            if (! TemporaryPayments::where('ref_number', $tempPayment->ref_number)->delete()) {
                $this->error('Temp payment '.$tempPayment->ref_number.' could not be deleted');

                return;
            }
        }
    }

    protected function getArguments()
    {
        return [];
    }

    protected function getOptions()
    {
        return [];
    }
}
