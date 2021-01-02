<?php

use \Illuminate\Support\Facades\Redirect;
use \Carbon\Carbon;

class CustomerSetupController extends BaseController
{
    protected $layout = 'layouts.admin_website';

	public function index()
	{
		
		
		  $this->layout->page = View::make('home/customer_setup/index', [
			
		  ]);
		  
	}
	
}