<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class MeterStatisticsController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function index($customer_id)
    {
        $this->layout->page = view('home/meter_stats/index', [

        ]);
    }
}
