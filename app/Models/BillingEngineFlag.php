<?php

class BillingEngineFlag extends Eloquent{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'engine_billing_flags';

	public $timestamps = true;
	
	public static function pending()
	{
		return BillingEngineFlag::where('approved', 0)->where('declined', 0);
	}
	
	public function avgUsage($days = 7)
	{
		$lastReadingDay = DistrictMeterStat::where('permanent_meter_ID', $this->customer->permanentMeter->ID)
		->where('reading', $this->latest_reading)->orderBy('id', 'DESC')->first();
		
		
		if(!$lastReadingDay)
			return;
		
		$lastReadingDay = $lastReadingDay->timestamp;
		
		return DistrictHeatingUsage::where('customer_id', $this->customer_ID)
		->whereRaw("date BETWEEN DATE('" . $lastReadingDay . "') - INTERVAL " . ($days-1) . " DAY AND DATE('" . $lastReadingDay . "')")
		->avg('total_usage');
	}
	
	public function getMissingDaysAttribute()
	{
		if(!$this->customer)
			return;
		
		if(!$this->customer->permanentMeter)
			return;
		
		$lastReadingDay = DistrictMeterStat::where('permanent_meter_ID', $this->customer->permanentMeter->ID)
		->where('reading', $this->latest_reading)->orderBy('id', 'DESC')->first();
		
		
		if(!$lastReadingDay)
			return;
		
		$lastReadingDay = $lastReadingDay->timestamp;
		
		return DistrictHeatingUsage::whereRaw("(customer_id = '" . $this->customer_ID . "' AND date >= '" . $lastReadingDay . "' AND total_usage <= '0')")
		->count();
		
	}
	
	public function getCustomerAttribute()
	{
		return Customer::find($this->customer_ID);
	}
}