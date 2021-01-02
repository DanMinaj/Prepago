<?php

class StripePaymentSource extends Eloquent
{
    protected $table = "customers_stripe_sources";
	
	public function getCustomerAttribute()
	{
		return Customer::find($this->customer_id);
	}
	
	
}
