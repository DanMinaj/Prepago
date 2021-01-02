<?php

class PermanentMeterDataReadings extends Eloquent{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'permanent_meter_data_readings';

	public $timestamps = false;
	
	protected $primaryKey = 'ID'; 

	public function permanentMeter()
	{
		return $this->belongsTo('PermanentMeterData', 'permanent_meter_id', 'ID');
	}

}