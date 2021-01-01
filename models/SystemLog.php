<?php

class SystemLog extends Eloquent{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'system_logs';
	
	public static function log($type, $message)
	{
		$log = new SystemLog();
		$log->type = $type;
		$log->message = $message;
		$log->save();
		
		return $log;
	}
	
}