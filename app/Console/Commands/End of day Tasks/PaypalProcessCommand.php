<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class PaypalProcessCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'paypal:process';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Process any payments that were missed. Send notification of any paypal disputes. ';

	/**
	 *
	 * Set the log file & log file name
	 *
	 */
	private function setLog($name = "default") 
	{
		$this->log = new Logger($name);
		$this->log->pushHandler(new StreamHandler(__DIR__ . "/PaypalProcessCommand/" . date('Y-m-d') . ".log", Logger::INFO));
	}
	
	/**
	 *
	 * Create a log entry in the log file
	 *
	 */
	private function createLog($msg, $print = true)
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
		
		$a_start = null;
		$a_end = null;
		
		$this->info("Loading a list of customers..");
		$cs = Customer::where('status', 1)->get(['first_name', 'surname', 'id', 'username', 'mobile_number']);
		
		$this->setLog();
		$paypal = new Paypal();
		$time_begin = microtime(true);
		
		// Get the time -48 hrs ago
		$start = new DateTime(date('Y-m-d H:i:s'));
		$end = new DateTime(date('Y-m-d H:i:s'));
		$end = $end->modify('-500 hours');
		$a_start = $start;
		$a_end = new DateTime(date('Y-m-d H:i:s'));
		
		$total_payments = 0;
		$payment_grand_total = 0;
		$total_missing_payments = 0;
		$missing = [];
		
		$this->info("Process 1: Checking for Missing Paypal Payments (now-" . $end->format('Y-m-d') . ")");
		
		while($start > $end) {

			// Add 4 hours to start after this iteration
			$start_minus_4 = new DateTime($start->format('Y-m-d H:i:s'));
			$start_minus_4->modify('-5 hours');
			
			// Get payments from $start - $start + 4 hrs
			$this->info("Getting payments from " . $start->format('Y-m-d H:i:s') . " --> " . $start_minus_4->format('Y-m-d H:i:s'));
			$time_start = microtime(true); 
			
			$range_payments = Paypal::getPaymentsNew($start_minus_4->format('Y-m-d H:i:s'), $start->format('Y-m-d H:i:s'));
	
			$total_payments += count($range_payments);
			$payment_amnt_total = 0;
			$missing_payments = 0;
			$local_missing = [];
			
			if(!is_array($range_payments)) {
				$this->info("Failed to get payments.. skipping as range_payments is not an array!");
				$start = $start_minus_4;
				$end = new DateTime(date('Y-m-d H:i:s'));
				continue;
			}
			
			foreach($range_payments as $p) {
				
				$payment_amnt_total += $p->amount;
				$payment_grand_total += $p->amount;
				
				// Found a missing payment
				$entry_exists = PaymentStorage::where('ref_number', $p->id)->first();
				if(!$entry_exists) {
					$total_missing_payments++;
					$missing_payments++;
					array_push($missing, $p);
					array_push($local_missing, $p);
				}
				
				
				//$this->info("Refund URL: " . $p->refund_url);
			
			}
			
			$this->info("Process took " . number_format((microtime(true) - $time_start), 0) . " seconds. Found " . count($range_payments) . " payments. Found $missing_payments missing payments. Total of €$payment_amnt_total.");
			
			
			if(count($local_missing) > 0) {
				$this->info("\n");
				foreach($local_missing as $k => $v) {
					$this->info("Missing payment #$k");
					$this->info("State: " . $v->state);
					$this->info("Name: " . $v->name);
					$this->info("Email: " . $v->email);
					$this->info("ID: " . $v->id);
					$this->info("Sale ID: " . $v->saleid);
					$this->info("Account: " . $v->from);
					$this->info("Time: " . $v->time);
					$this->info("Amount: " . $v->amount);
					if($v->username)
					$this->info("Username: " . $v->username);
					
					
					
					$psp = PaymentStorageProcess::where('last_payment_id', $v->id)->first();
					$ps = PaymentStorage::where('ref_number', $v->id)->first();
					if($psp && !$ps) {
						$this->info("-- POTENTIAL AUTOMATIC INSERTION DETECTED --");
					} else if($v->username) {
						$this->info("-- POTENTIAL AUTOMATIC INSERTION DETECTED --");
					}
				}
				$this->info("\n");
			}
			
			$this->info("\n\n");
			
			// SKIP PROCESS 
			/*
			if(count($missing) > 0){
					$start = new DateTime(date('Y-m-d H:i:s'));
					break;
			}
			*/
			
			// Increment $start + 4 hrs 
			$start = $start_minus_4;
			
			//$this->info("Start: " . $start->format('Y-m-d H:i:s'));
			//$this->info("End: " . $end->format('Y-m-d H:i:s'));
		
		
		}
	
		$this->info("================================================\n\n");
		$this->info("Entire process took a total of " . number_format((microtime(true) - $time_begin), 0) . " seconds. Found a total of $total_payments payments. Totalling €$payment_grand_total.");
		$this->info("Found $total_missing_payments missing payments");
		
		if($total_missing_payments > 0) {
			
			$body = "";
			$body .= "There are " . count($missing) . " paypal payment(s) that have not yet been processed in the Prepago system (from " . $a_start->format('Y-m-d H:i:s') . " -> " . $a_end->format('Y-m-d H:i:s') . ").<br/>Please find the customers associated with them & issue them with credit.<br/><br/>";
			foreach($missing AS $m) {
				
				$customer = null;
				
				$body .= $m->time . "<br/>";
				$body .= "€" . $m->amount . "<br/>";
				$body .= "Name: " . $m->name . "<br/>";
				$body .= "Transaction ID: " . $m->id . "<br/>";
				$body .= "To Paypal: " . $m->from . "<br/>";
				$body .= "Email: " . $m->email . "<br/>";
				$body .= "Phone: " . $m->phone . "<br/>";
				$customer = Customer::where('username', $m->username)->first();
				$psp = PaymentStorageProcess::where('last_payment_id', $m->id)->first();
				$ps = PaymentStorage::where('ref_number', $m->id)->first();
				$inserted = false;
				
				if($m->username) {
					if($customer) {
						$body .= "<b>Certain</b> customer: <a target='_blank' href='http://prepagoplatform.com/customer_tabview_controller/show/" . $m->username . "'>" . $m->username . "</a><br/>";			
						$result = $customer->addPayment($m->id, $m->amount, $m->time);
						if($result) {
							$this->info("Automatic payment process SUCCESS: " . $m->id . ". Customer has been credited!");
							$body .= "Automatic process: <font color='green'>SUCCESS</font><br/>";	
							$inserted = true;
						} else {
							$this->info("Automatic payment process failed: " . $m->id);
							$body .= "Automatic process: Failed<br/>";
						}
					}
				}

				if(!$inserted && $psp && !$ps) {
					$customer = Customer::find($psp->customer_id);
					if($customer) {
						$m->username = $customer->username;
						$body .= "<b>Certain</b> customer: <a target='_blank' href='http://prepagoplatform.com/customer_tabview_controller/show/" . $m->username . "'>" . $m->username . "</a><br/>";			
						$result = $customer->addPayment($m->id, $m->amount, $m->time);
						if($result) {
							$this->info("Automatic payment process SUCCESS: " . $m->id . ". Customer has been credited!");
							$body .= "Automatic process: <font color='green'>SUCCESS</font><br/>";	
							$inserted = true;
						} else {
							$this->info("Automatic payment process failed: " . $m->id);
							$body .= "Automatic process: Failed<br/>";
						}
					}
				}
				
				$potential_customers = Customer::get("name", $m->name);
				
				if($potential_customers) {
					$body .= "Potential customer(s): <br/>";
					foreach($potential_customers as $k => $v) {
						$body .= " <a target='_blank' href='http://prepagoplatform.com/customer_tabview_controller/show/" . $v->username . "'>" . $v->username . "</a>";
					}
				}

				$body .= "<br/><hr/>";
			}
			$this->info("Notifying administrator(s) of missing payments..");
			
			Email::admins("Prepago Payments: Un-processed Paypal Payment(s)", $body);
		}
		$this->info("\n================================================\n\n");
		
		
		$this->info("Process 2: Checking for Open Paypal Disputes");
		
		
		} catch(Exception $e) {
			$this->info("[COMMAND] Failed to get payment range: " . $e->getMessage());
		}
	}
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire2()
	{
		/*
		$this->setLog();
		
		
		$start = new DateTime(date('Y-m-d'));
		$start = $start->modify('-1 days');
		$start = $start->format('Y-m-d');
		$this->createLog("Grabbing payments from $start -> $end");
		
		
		$end = date('Y-m-d');
		
		
		$todays_payments =  $paypal->getPayments($start, $end, 'all');
		
		$found = false;
		
		$missing = [];
		
		
		foreach($todays_payments as $p) {
			
			$entry_exists = PaymentStorage::where('ref_number', $p->id)->first();
			$this->info("Checking payment: " . $p->id);
			
			if(!$entry_exists) {
				
			
				$this->createLog("No entry for payment " . $p->id . ". Notifying system administrator");
				
				array_push($missing, $p);
				
				$found = true;
				
			} else {
				$this->createLog("Entry found! Inserted at " . $entry_exists->time_date . " for Customer " . $entry_exists->customer_id);
			}
			
		}
		
		if(!$found) {
			$this->createLog("No missed payments today");
		}
		else {
			
			
			$body = "";
			$body .= "There are " . count($missing) . " paypal payment(s) that have not yet been processed in the Prepago system (from $start -> $end).<br/>Please find the customers associated with them & issue them with credit.<br/><br/>";
			foreach($missing AS $m) {
				$body .= $m->time . "<br/>";
				$body .= "€" . $m->amount . "<br/>";
				$body .= $m->name . "<br/>";
				$body .= $m->id . "<br/>";
				$body .= $m->email . "<br/>";
				$body .= $m->phone . "<br/>";
				$body .= "<br/><hr/>";
			}
			
			//Email::quick_send($body, "TEST EMAIL - IGNORE", ['aidan@prepago.ie', 'daniel@prepago.ie'], 'info@prepago.ie', 'Prepago Monitor');
			Email::quick_send($body, "Prepago Monitor: Un-processed Paypal Payment(s)", ['aidan@prepago.ie', 'daniel@prepago.ie'], 'info@prepago.ie', 'Prepago Monitor');
				
			
		}
		*/
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
