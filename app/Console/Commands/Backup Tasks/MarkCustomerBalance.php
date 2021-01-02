<?php

use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MarkCustomerBalance extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'mark:customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Mark customer's balance at start of day & end of day";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('Mark Customer Balance Log');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/MarkCustomerBalance/'.date('Y-m-d').'.log'), Logger::INFO);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        try {
            die();

            ini_set('memory_limit', '-1');

            $customers = Customer::where('status', 1)->orderby('id', 'ASC')->get();

            $this->log->addInfo("Marking customer's balance at ".date('d-m-Y H:i:s'));
            $this->info("Marking customer's balance at ".date('d-m-Y H:i:s'));

            foreach ($customers as $customer) {
                $balanceChangeForToday = CustomerBalanceChange::where('customer_id', $customer->id)
                ->where('date', date('Y-m-d'))->first();

                if (! $balanceChangeForToday) {
                    $newBalanceChange = new CustomerBalanceChange();
                    $newBalanceChange->customer_id = $customer->id;
                    $newBalanceChange->start_balance = $customer->balance;
                    $newBalanceChange->date = date('Y-m-d');
                    $newBalanceChange->start_time = date('Y-m-d H:i:s');
                    $newBalanceChange->save();
                } else {
                    $balanceChangeForToday->end_balance = $customer->balance;
                    $balanceChangeForToday->end_time = date('Y-m-d H:i:s');
                    $balanceChangeForToday->save();
                }
            }
        } catch (Exception $e) {
            $this->log->addInfo("Error occured while marking customer's balance at  ".date('d-m-Y H:i:s').' : '.$e->getMessage());
            $this->info('Error occurred  '.$e->getMessage());
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
