<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;



class CustomerSetupController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function index()
    {
        $this->layout->page = view('home/customer_setup/index', [

          ]);
    }
}
