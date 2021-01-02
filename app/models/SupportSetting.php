<?php

class SupportSetting extends Eloquent{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'support_settings';

	public $timestamps = false;


	
	public static function get($setting)
	{
		
		$setting = SupportSetting::where('name', $setting)->first();
		
		if($setting) return $setting->value;
		
		
		return 'null';
		
	}

	
}