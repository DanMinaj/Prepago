<?php

class CustomerArrears extends Eloquent{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'customer_arrears';

	public $timestamps = false;
	
	protected $guarded = ['id'];
}