<?php

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class MeterStatisticsController extends BaseController
{
    protected $layout = 'layouts.admin_website';

    public function index($customer_id)
    {
        $this->layout->page = View::make('home/meter_stats/index', [

        ]);
    }
}
