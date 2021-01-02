<?php

class SystemStat extends Eloquent{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'system_stats';
	
	public static function get($setting)
	{
		return SystemStat::first()->$setting;
	}
	
	public static function set($setting, $value)
	{
		DB::table('system_stats')->update([$setting => $value]);
	}
	

	
}