<?php

namespace App\Http\Controllers\Reports;

use App\Models\EVUsage;
use App\Models\PaymentStorageTest;
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
        $this->layout->page = view('report/prepaygo/index')->with([
            'recharges' => $recharges,
            'topups' => $topups,
        ]);
    }
}
