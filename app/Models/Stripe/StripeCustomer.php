<?php

class StripeCustomer extends Eloquent
{
    
	protected $table = "customers_stripe";
	
	public function getCustomerAttribute()
	{
		return Customer::find($this->customer_id);
	}
	
	public function getSourcesAttribute()
	{
		return StripePaymentSource::where('customer_id', $this->customer_id)
		->orderBy('id', 'DESC')->get();
	}
	
	public function getIntentsAttribute()
	{
		return StripePaymentIntent::where('customer_id', $this->customer_id)
		->orderBy('id', 'DESC')->get();
	}
	
	public function getLastTopupAttribute()
	{
		return StripeCustomerPayment::where('customer_id', $this->customer_id)
		->orderBy('id', 'DESC')->first();
	}
}
