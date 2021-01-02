<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;

class CustomerSetupController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function index()
    {
        $this->layout->page = View::make('home/customer_setup/index', [

          ]);
    }
}
