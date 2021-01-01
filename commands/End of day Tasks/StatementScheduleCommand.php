<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class StatementScheduleCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'statement_schedule';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 *
	 * Set the log file & log file name
	 *
	 */
	private function setLog($name = "default") 
	{
		$this->log = new Logger($name);
		$this->log->pushHandler(new StreamHandler(__DIR__ . "/StatementScheduleCommand/" . date('Y-m-d') . ".log", Logger::INFO));
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

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		
		$time_start = microtime(true);
		$schedules = SnugzoneAppStatementSchedule::where('active', 1)->get();
		$from = date("Y-m-d",strtotime("-1 month"));
		$to = date("Y-m-d");
		
		echo "\n=== Found ". count($schedules) . " active schedules ===\n";
		
		foreach($schedules as $k => $s) {
			
			$customer = Customer::find($s->customer_id);
			
			if(!$customer) {
				$s->active = 0;
				$s->save();
				continue;
			}
			
			$emails = explode(' ', $s->emails);
			
			
			if(count($emails) <= 0 || !is_array($emails) || $emails == null || empty($s->emails) || strlen($s->email) <= 2) {
				$s->emails = $customer->email_address;
				$s->save();
				$emails = [
					$s->emails,
				];		
			}
			
			echo "\nCustomer #" . $customer->id . " (" . $customer->username . ")\n";
			// send it now
			if(date('Y-m-d') >= $s->next_sent) {
				echo "Time to send out schedule to: " . $s->emails . "\n";
				$customer->sendStatement($emails, [], null);
				$s->last_sent = date('Y-m-d');
				$s->sent_times++;
				$s->save();
			} else {
				$from = Carbon\Carbon::parse(date('Y-m-d'));
				$to = Carbon\Carbon::parse($s->next_sent);
				$days = $to->diffInDays($from);
				echo "Next schedule for: " . $s->next_sent . " (" . $days. " days left)..\n";
			}
			
			if($s->last_sent == $s->next_sent) {
				if(strtolower($s->frequency) == 'every month') {
					$time = strtotime($s->last_sent);
					$s->next_sent = date("Y-m-d", strtotime("+1 month", $time));
					echo "Next schedule for: " . $s->next_sent . "..\n";
					$s->save();
				}
				if(strtolower($s->frequency) == 'every fortnight') {
					$time = strtotime($s->last_sent);
					$s->next_sent = date("Y-m-d", strtotime("+2 weeks", $time));
					echo "Next schedule for: " . $s->next_sent . "..\n";
					$s->save();
				}
				if(strpos(strtolower($s->frequency), 'days') !== false) {
					$days_parts = explode(' ', $s->frequency);
					$days = $days_parts[1];
					$time = strtotime($s->last_sent);
					$s->next_sent = date("Y-m-d", strtotime("+$days days", $time));
					echo "Next schedule for: " . $s->next_sent . "..\n";
					$s->save();
				}
			}
		}
		
		$this->info("Total execution time: " . (microtime(true) - $time_start) . " seconds");
		
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
