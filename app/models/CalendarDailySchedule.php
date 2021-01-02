<?php

class CalendarDailySchedule extends Eloquent{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'calendar_daily_schedule';

	protected $primaryKey = 'id';
	 
	public $timestamps = false;
	
	public $programMappings = [
		
		'd'		=>	'Daily Records Engine',
		'pp'		=> 	'Paypoint File Program',
		'w'		=>	'Weather Program',
		'b'		=>	'Billing Engine',
		'b1'		=>	'Billing Engine run-type 1',
		'b2'		=>	'Billing Engine run-type 2',
		'wd'		=>	'Watch Dog',
		's'		=>	'Shut-Off Engine',
		'a'		=>	'PaypalAutotopup Engine',
		'tc'	=> 'Temperature Monitor',
		
	];
	
	public function getRowColourAttribute()
	{
		
		if($this->running)
			return "style='background: #fad3a0;'";
			
		if($this->time < date('H:i:s'))
			return "style='background: #d7ffe5;'";
		
		return '';
	}
	
	public function getTemperatureActionAttribute()
	{
		switch($this->run_type) {
			
			case 1:				
				return "Resend Failed Shut-Offs";
			break;
			
			case 2:
				return "Resend Failed Restorations";
			break;
			
			case 3:
				return "Resend Temperature Check";
			break;
			
			
		}
	}
	
	public function getProgramAttribute()
	{
		
			if( isset($this->programMappings[$this->attributes['program']]) )
				return $this->programMappings[$this->attributes['program']];

			return $this->attributes['program'];
	}
	
	public function setProgramAttribute($value)
	{
		if(strlen($value) <= 4)
		{
			/*
			if(!isset($this->programMappings[$value]))
				throw new Exception("The program '<b>$value</b>' does not exist.");
			*/
			$this->attributes['program'] = $value;
			return;
		}

		foreach($this->programMappings as $key=>$pm)
		{
			if($pm == $value)
			{
				$this->attributes['program'] = $key;
				return;
			}
		}
		
	}
	
	public function setTimeAttribute($value)
	{
		if(!preg_match("/^([0-1][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $value))
			throw new Exception("'<b>$value</b>' is an invalid time format. Must follow a hh:mm:ss format");
		else
			$this->attributes['time'] = $value;
	}
	
	public function setRunOnWeekendAndHolidayAttribute($value)
	{
		
			if($value == 'true')
				$value = 1;
			if($value == 'false')
				$value = 0;
			if($value == 'yes')
				$value = 1;
			if($value == 'no')
				$value = 0;
			
			if($value != 1 && $value != 0)
				throw new Exception("Invalid value for run on weekends: must be 0, 1 OR false, true");
			
			$this->attributes['run_on_weekend_and_holiday'] = $value;
	}
}