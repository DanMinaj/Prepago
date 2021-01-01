<?php

class SnugzoneAppError extends Eloquent{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'snugzone_app_error_logs';


	public static function log($log, $file, $customer_id)
	{
		$log = new SnugzoneAppError();
		$log->log = $log;
		$log->file = $file;
		$log->customer_id = $customer_id;
		$log->save();
		
		return $log;
	}
    
}