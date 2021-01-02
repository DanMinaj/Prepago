<?php

class StripeLog extends Eloquent
{
	
    protected $table = "stripe_logs";
	
	public function getLogFormattedAttribute()
	{
		
		
		$log = unserialize($this->log);
		
		
	}
}
