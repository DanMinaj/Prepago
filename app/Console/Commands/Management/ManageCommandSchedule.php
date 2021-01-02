<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ManageCommandSchedule extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'manage';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Manage crontab schedules';

	
	public $ran = 0;
	
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setLog("ManageCommandSchedule", "cron_job_manager.log");
	}
	
	private function setLog($name, $file_path) 
	{
		$this->log = new Logger($name);
		$this->log->pushHandler(new StreamHandler(__DIR__ . '/' . $file_path, Logger::INFO));
		
	}
	
	private function createLog($msg)
	{
		$this->log->info($msg);
	}

	private function cronjobs()
	{
		return Cronjob::where('active', 1)->get();
	}
	
	private function pending_cronjobs()
	{
		return Cronjob::where('active', 1)
		->where('ran_today', 0)
		->whereRaw('time <= now()')
		->get();
	}
	
	private function remaining_cronjobs()
	{
		return Cronjob::where('active', 1)
		->where('ran_today', 0)
		->whereRaw('time >= now()')
		->get();
	}
	
	private function is_new_day()
	{	
		$system_date = SystemStat::get('current_date');
		$current_date = date('Y-m-d');
		
		SystemStat::set('current_date', $current_date);
		
		return ($system_date != $current_date);
	}
	
	private function handle_new_day()
	{
		if($this->is_new_day()) {
			Cronjob::where('ran_today', 1)->update([
				'ran_today' => 0
			]);
		}
		
		SystemStat::set('current_date', date('Y-m-d'));
	}
	
	private function handle_tariff_changes()
	{
		try {
			
			$changes = TariffChanges::where('complete', 0)
			->where('cancelled', 0)
			->where('date_entered', '>=', '2019-01-01 00:00:00')
			->get();
			
			foreach($changes as $k => $v) {
				
				$scheme = Scheme::where('scheme_number', $v->scheme_number)->first();
				$tariff = $scheme->tariff;
				if($scheme && $tariff) {
					
					$change_date = $v->change_date;
					
					if(date('Y-m-d') >= $change_date) {
						
						
						$tariff_to_change = $v->tariff_to_change;
						$change_value = $v->new_value;
						$old_tariff = $tariff->$tariff_to_change;
						$new_tariff = $change_value;
						
						$log = "\n";
						$log .= "Changing " . $scheme->scheme_nickname . "'s " . $tariff_to_change . " from $old_tariff -> $new_tariff";
						$this->info($log);
						$this->createLog($log);
						
						$tariff->$tariff_to_change = $change_value;
						$tariff->save();
						
						
						$v->complete = 1;
						$v->save();
						
						$log = "Successfully changed!";
						$log .= "\n";
						$this->info($log);
						$this->createLog($log);
						
						
					} else {
						
						$log = "\n";
						$log .= "Due to change tariff for " . $scheme->scheme_nickname . " on " . $change_date . ". Skipping";
						$log .= "\n";
						
						$this->info($log);
						$this->createLog($log);
					}
				}
				
			}
		} catch(Exception $e) {
			$log = "\n";
			$log .= "Error occured: " . $e->getMessage() . " (" . $e->getLine() . ")";
			$log .= "\n";
			
			$this->info($log);
			$this->createLog($log);
		}
	}
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{	
		
		$this->handle_tariff_changes();
		$this->handle_new_day();

		foreach($this->pending_cronjobs() as $cronjob) {
			try {		
				$cronjob->execute();
				$this->ran++;
			} catch(Exception $e) {
				$cronjob->log($e->getMessage());			
			}
		}
		
		$log = "\n";
		$log .= "Ran: " . $this->ran . " cronjobs\n";
		$log .= "Remaining: " . $this->remaining_cronjobs()->count() . " cronjobs";
		$log .= "\n";
		
		$this->info($log);
		$this->createLog($log);
		
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
