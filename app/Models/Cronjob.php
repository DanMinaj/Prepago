<?php

class Cronjob extends Eloquent{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'cronjobs';

	public $timestamps = false;

	public function execute($force_run = false)
	{
		
		$time_start = microtime(true); 
		
		try {		
			
			$command = $this->artisan_command;
			Artisan::call($command);
			
		} catch(Exception $e) {
			if(strpos($e->getMessage(), "is not defined") !== false)
				$this->log("'" . $command . "' in cronjob#" . $this->id . " is not a valid artisan command!: " . $e->getMessage());
		}
		
		$time_end = microtime(true);
		$execution_time = ($time_end - $time_start);
		
		if(!$force_run)
			$this->ran_today = true;
	
		$this->run_time = $execution_time;
		$this->save();
		
	}
	
	public function log($msg)
	{
		$cl = new CronjobLog();
		$cl->message = $msg;
		$cl->save();
	}
	
	public function getTimes()
	{
		return Cronjob::where('name', $this->name)->orderBy('time', 'ASC')->get();
	}
	
	public function getRan()
	{
		return Cronjob::where('name', $this->name)->where('ran_today', 1)->count();
	}
	
	public function getAvgRunTime()
	{
		return number_format(Cronjob::where('name', $this->name)->avg('run_time'), 6);
	}
	
	public function changeName($newName)
	{
	
		foreach($this->getTimes() as $entry) {
			$entry->name = $newName;
			$entry->save();
		}
		
	}

	public function getRanTodayStyleAttribute()
	{
		if($this->ran_today) {
			return 'background:#dff0d8;';
		} else {
			
		}
	}
}