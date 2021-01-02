<?php

namespace App\Http\Controllers\Reports;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;

class BoilerReportController extends ReportsBaseController
{
    protected $layout = 'layouts.admin_website';
    protected $repo;

    public function __construct(BoilerReportRepository $repo)
    {
        $this->repo = $repo;
        $this->repo->setCSVURL('create_csv/boiler_report');
        parent::__construct();
    }

    public function index()
    {
        if (Request::isMethod('post')) {
            if (
                (Input::get('to') && ! $this->repo->validateDatepickerDate(Input::get('to'))) ||
                (Input::get('from') && ! $this->repo->validateDatepickerDate(Input::get('from')))
            ) {
                return redirect('system_reports/boiler_report')->with('errorMessage', 'Invalid date format');
            }

            if (! Input::get('from')) {
                return redirect('system_reports/boiler_report')->with('errorMessage', 'Select a From Date');
            }

            if (! Input::get('to')) {
                return redirect('system_reports/boiler_report')->with('errorMessage', 'Select a To Date');
            }

            $this->repo->setFromDate(Input::get('from'), true);
            $this->repo->setToDate(Input::get('to'), true);
        }

        $meters = $this->repo->getReportData();

        //get the csv URL
        $csvURL = $this->repo->getCsvURL();

        $this->layout->page = view('report/boiler_report')->with('csv_url', $csvURL)->with('meters', $meters);
    }
}
