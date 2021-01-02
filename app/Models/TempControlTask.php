<?php

class TempControlTask extends Eloquent{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'scheduled_temp_control_tasks';
	
	public function getPmdAttribute() 
	{
		return PermanentMeterData::where('username', $this->username)->orderBy('ID', 'DESC')->first();
	}
}