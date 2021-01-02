<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class PrepayGoReportController extends ReportsBaseController
{
    protected $layout = 'layouts.admin_website';

    public function index()
    {
        $recharges = EVUsage::orderBy('id', 'DESC')
        ->where('ev_timestamp', '>=', '2019-11-08')->get();

        $topups = PaymentStorageTest::where('acceptor_name_location_', 'stripe_prepaygo')->orderBy('time_date', 'DESC')->get();
        $this->layout->page = View::make('report/prepaygo/index')->with([
            'recharges' => $recharges,
            'topups' => $topups,
        ]);
    }
}
