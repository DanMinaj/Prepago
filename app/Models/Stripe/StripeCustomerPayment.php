<?php


class StripeCustomerPayment extends Eloquent
{
    protected $table = "customers_stripe_payments";
	
	protected $appends = ['time'];
	
	public function getCustomerAttribute()
	{
		return Customer::find($this->customer_id);
	}
	
	public function getTimeAttribute()
	{
		return Carbon\Carbon::parse($this->created_at)->diffForHumans();
	}
	
}
