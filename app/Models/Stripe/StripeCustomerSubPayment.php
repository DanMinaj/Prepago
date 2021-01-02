<?php


class StripeCustomerSubPayment extends Eloquent
{
    protected $table = "customers_stripe_subs_payments";
	
	public function getCustomerAttribute()
	{
		return Customer::find($this->customer_id);
	}
	
}
