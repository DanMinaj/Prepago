<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ResetShutOffCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'shutoff:reset';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Resets customer\'s shut_off field from 1 to 0 where the balance > 0.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->log = new Logger('Shut off reset log');
		$this->log->pushHandler(new StreamHandler(__DIR__ . "/ResetShutOffCommand/" . date('Y-m-d') . ".log", Logger::INFO));
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		
		
		try {
			
			ini_set('memory_limit','-1');
			
			sleep(10);
			
			$customers_to_reset = Customer::resetShutOffCandidates()->get();
			$count = 0;
			
			foreach ($customers_to_reset as $customer) {
				
				
				$this->log->addInfo('Shut off reset', ['customer_id' => $customer->id, 'balance' => $customer->balance]);
				$this->info("Customer #" . $customer->id);
				$this->info("Balance: " . $customer->balance);
				if($customer->districtMeter) {
					$this->info("Shut_off_device_status: " . $customer->districtMeter->shut_off_device_status);
					$this->info("Scheduled to shut off: " . $customer->districtMeter->scheduled_to_shut_off);
				}
				$this->info("\n\n");
				
				$customer->clearShutOff(true);
				
				$count++;
			}
			
			if($count == 0) {
				$this->log->addInfo("No customers to reset shut off");
				$this->info("No customers to reset shut off");
			}
		} catch(Exception $e) {
			
			$this->info("An error occured: " . $e->getMessage());
			
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
