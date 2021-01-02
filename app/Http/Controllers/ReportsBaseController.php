<?php

namespace App\Http\Controllers;

use Carbon\Carbon;



class ReportsBaseController extends Controller
{
    protected $fromDate;
    protected $toDate;
    protected $csvURL;

    public function __construct()
    {
        $this->middleware('canAccessSystemReports');
    }
}
