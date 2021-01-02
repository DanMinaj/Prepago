<?php

namespace App\Http\Controllers\Reports;

use App\Models\Customer;
use Illuminate\Support\Facades\URL;


class CarlinnReportController extends ReportController
{
    protected $layout = 'layouts.admin_website';

    public function index()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $to = '2017-03-01';
        $from = '2016-02-28';

        $customers = Customer::where('customers.deleted_at', '=', null)->where('scheme_number', 3)->get();

        foreach ($customers as $customer) {
            $customer->readings = $customer->districtHeatingUsage()
                                        ->where('district_heating_usage.date', '>=', $from)
                                        ->where('district_heating_usage.date', '<=', $to)
                                        ->orderby('date', 'desc')
                                        ->get();
        }

        $csv_url = URL::to('create_csv/carlinn_report');

        $this->layout->page = view('report/carlinn_report', [
            'customers' => $customers,
            'csv_url' => $csv_url,
        ]);
    }
}
