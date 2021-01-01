<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class QueuedCustomersCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'customers:queue';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Queue for customer open/close account';

	/**
	 *
	 * Set the log file & log file name
	 *
	 */
	private function setLog($name = "default") 
	{
		$this->log = new Logger($name);
		$this->log->pushHandler(new StreamHandler(__DIR__ . "/QueuedCustomersCommand/" . date('Y-m-d') . ".log", Logger::INFO));
	}
	
	/**
	 *
	 * Create a log entry in the log file
	 *
	 */
	public function log($msg, $print = true)
	{
		$this->log->info($msg);

		if($print) {
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
			
			ini_set('memory_limit', '-1');
			
			$this->setLog();
			
			$this->closeQueue();

			$this->openQueue();
			
		} catch(Exception $e) {
			$this->info($e->getMessage());
		}
	}
	

	public function openQueue()
	{
		
		$queues = CustomerQueue::where('type', 'open_account')
		->where('completed', 0)
		->get();
		
		$this->log("\n\n-- Open Account Queue --");
		if(count($queues) == 0) {
			$this->log("0 customers found in queue..\n\n");
		}

		foreach($queues as $k => $q) {
			
			if($q->processing) {
				$this->log("Already Processing Queue #" . $q->id . "..skipping.");
				$this->log("\n\n");
				continue;
			}
			
			if($q->commencement_date == date('Y-m-d')) {
				$this->log("Setting up Customer " . $q->username . ".\n");
				$q->log("open_account", "Setting up Customer " . $q->username . ".\n");
			} else {
				$now = Carbon\Carbon::parse(date('Y-m-d'));
				$due = Carbon\Carbon::parse($q->commencement_date);
				$days_left = $due->diffInDays($now);
				$this->log("Skipping customer Queue #" . $q->id . ".. Setup due for " . $q->commencement_date . " ($days_left day(s) left)..");
				$this->log("\n\n");
			}
			
			$customer_exists = Customer::where('username', $q->username)->orderBy('id', 'DESC')->first();
			if($customer_exists)
			{
				$this->log("Customer in queue #" . $q->id . " already exists as customer #" . $customer_exists->id . "..skipping.");
				$this->log("\n\n");
				$q->processing = false;
				$q->completed = true;
				$q->completed_at = date('Y-m-d H:i:s');
				$q->failed_msg = "Customer in queue #" . $q->id . " already exists as customer #" . $customer_exists->id . "..skipping.";
				$q->save();
				continue;
			}
			
			// Set as processing
			$q->processing = true;
			$q->save();

			try {
	
				$newCustomer = Customer::openAccount([
					'username' => $q->username,
					'scheme_number' => $q->scheme_number,
					'email_address' => $q->email_address,
					'mobile_number' => $q->mobile_number,
					'nominated_telephone' => $q->nominated_telephone,
					'commencement_date' => $q->commencement_date,
					'selectedUnit' => $q->meter_number,
					'role' => 'normal',
					'balance' => $q->balance,
					'starting_balance' => $q->starting_balance,
					'first_name' => $q->first_name,
					'surname' => $q->surname,
					'arrears' => $q->arrears,
					'arrears_daily_repayment' => $q->arrears_daily_repayment,
				]);
				
				$q->log("open_account", "Executed openAccount() for " . $q->username);

				if($newCustomer) {
					$this->log("Customer ID: " . $newCustomer->id . "");
					$this->log("Username: " . $newCustomer->username . "");
					$this->log("Commencement date: " . $newCustomer->commencement_date . "");
					$this->log("\n\n");
					$q->customer_id = $newCustomer->id;
					$q->failed = 0;
					$q->failed_id = 0;
					$q->failed_msg = '';
					$q->save();
					$q->log("open_account", "Successful openAccount() for " . $q->username . ". Customer #" . $newCustomer->id . " created");
				} else {
					$q->log("open_account", "Unknown status in openAccount() for " . $q->username);
				}

			} catch(Exception $e) {
				
				$msg = "To whom it may concern<br/><br/>";
				$msg .= "An error occured during Customer Setup Scheduler:<br/><b>Queue #:</b> " . $q->id . "<br/><b>Username:</b> " . $q->username . "<br/><br/>";
				$customer_exists = Customer::whereRaw("username = '" . $q->username . "' AND commencement_date = '" . $q->commencement_date . "' AND starting_balance = '" . $q->starting_balance . "'")->first();
				if($customer_exists) {
					$msg .= "Please be advised, the customer <b>was still created</b>, however they may have incomplete information.<br/>
				Please verify if that is the case & fix if needed.<br/>
				&bull; Check SCU is correct<br/>
				&bull; Check Meter is correct<br/>
				&bull; Make sure customer was sent their login details<br/>
				(<a href='https://prepagoplatform.com/customer/" . $q->username . "'>View the customer.</a>)<br/>";
				} else {
					$msg .= "The customer <b>was NOT setup</b> as a result. Please <a href='https://prepagoplatform.com/open_account'>Create a new Customer Setup Queue</a> with the following information: <br/>
					&bull; <b>Username:</b> " . $q->username . "<br/>
					&bull; <b>Firstname:</b> " . $q->first_name . "<br/>
					&bull; <b>Surname:</b> " . $q->surname . "<br/>
					&bull; <b>Email address:</b> " . $q->email_address . "<br/>
					&bull; <b>Mobile #:</b> " . $q->mobile_number . "<br/>
					&bull; <b>Nominated Telephone #:</b> " . $q->nominated_telephone . "<br/>
					&bull; <b>Starting balance:</b> " . $q->balance . "<br/>
					&bull; <b>Arrears:</b> " . $q->arrears . "<br/>
					&bull; <b>Arrears Daily Repayment:</b> " . $q->arrears_daily_repayment . "<br/>
					&bull; <b>Commencement Date:</b> " . $q->commencement_date . "<br/>
					";
				}
				$msg .= "<br/><b>Details of the error:</b><br/>";
				$msg .= $e->getMessage() . "<br/>";
				$msg .= "Occured on Line #" . $e->getLine() . "<br/>";
				$msg .= "<br/>Kind regards,<br/>SnugZone";
				$title = "Prepago Customer Setup Error: " . $q->username . " (Queue #" . $q->id . ")";
				$to = ["daniel@prepago.ie", "aidan@prepago.ie"];
				$from = "info@prepago.ie";
				$sender = "Prepago Notification System";
				Email::quick_send($msg, $title, $to, $from, $sender);
				
				$q->log("open_account", "Fatal error in openAccount() for " . $q->username . ": " . $e->getMessage() . " (" . $e->getLine() . ")");
				$this->log("-- FATAL OpenAccount Error for Queue #" . $q->id . " --\n");
				$this->log($e->getMessage() . "\n");
				$this->log("Line: " . $e->getLine());
				$this->log("\n\n");

				$q->failed = 1;
				$q->failed_id = 0;
				$q->failed_msg = $e->getMessage() . "\n\n (" . $e->getLine() . ")";
				$q->save();

			} finally {
				$q->processing = false;
				$q->completed = true;
				$q->completed_at = date('Y-m-d H:i:s');
				$q->save();
			}
			
		}

	}


	public function closeQueue()
	{

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
