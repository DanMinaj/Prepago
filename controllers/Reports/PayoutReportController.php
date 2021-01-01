<?php

use \Carbon\Carbon;
use \Illuminate\Support\Facades\Redirect;

class PayoutReportController extends ReportsBaseController {

    protected $layout = 'layouts.admin_website';
    protected $repo;

    public function __construct(PayoutReportRepository $repo)
    {
        $this->repo = $repo;
        $this->repo->setCSVURL('create_csv/payout_reports');
        parent::__construct();
    }

    public function index($schemeNumber = null)
    {
		
		//return Redirect::to('system_reports/advice_notes');
        if (Request::isMethod('post') || (Input::get('from') && Input::get('to')))
        {
            if (
                (Input::get('to') && !$this->repo->validateDatepickerDate(Input::get('to'))) ||
                (Input::get('from') && !$this->repo->validateDatepickerDate(Input::get('from')))
            )
            {
                return Redirect::to('system_reports/payout_reports')->with('errorMessage', 'Invalid date format');
            }

            if (!Input::get('from'))
            {
                return Redirect::to('system_reports/payout_reports')->with('errorMessage', 'Select a From Date');
            }

            if (!Input::get('to'))
            {
                return Redirect::to('system_reports/payout_reports')->with('errorMessage', 'Select a To Date');
            }

            $this->repo->setFromDate(Input::get('from'), true);
            $this->repo->setToDate(Input::get('to'), true);

        }
        else
        {
            $this->repo->setDefaultFromDate();
            $this->repo->setDefaultToDate();
        }

        /*
        //move temporary payments to payments storage
        if ($res = $this->repo->movePayments() !== true)
        {
            die('The temporary payment with ref number ' . $res . ' can not be moved to payment storage');
        }
        */
		
		$currentSchemeNumber = is_null($schemeNumber) ? Auth::user()->scheme_number : $schemeNumber;

        $schemes = Auth::user()->isUserTest() ?
                        Scheme::withoutArchived()->where('scheme_number', '!=', $currentSchemeNumber)->select('company_name', 'scheme_number')->lists('company_name', 'scheme_number') :
                        new \Illuminate\Support\Collection();

        $currentSchemeName = Scheme::where('scheme_number', $currentSchemeNumber)->select('company_name')->first()->company_name;

        $this->repo->setSchemeNumber($currentSchemeNumber);

        //get report data
        $data = $this->getReportData();

        $data['start_date'] = null;
        $data['end_date'] = null;
        if (Request::isMethod('post')) {
            $data['start_date'] = $this->repo->getDate('from', false, true)->format('d-m-Y');
            $data['end_date'] = $this->repo->getDate('to', false, true)->format('d-m-Y');
        }
		
		$data['tariff_1'] = $this->repo->getT1();
		$data['tariff_2'] = $this->repo->getT2();

		
		$from = Carbon::parse($this->repo->getDate('from'))->format('Y-m-d H:i:s');
		$to = Carbon::parse($this->repo->getDate('to'))->format('Y-m-d') . ' 23:59:59';
		
		$data['statements_issued'] = SnugzoneAppStatement::whereRaw("(created_at >= '$from' AND created_at <= '$to')")->get();
		foreach($data['statements_issued'] as $k => $v) {
			$customer = Customer::find($v->customer_id);
			if(!$customer || $currentSchemeNumber != $customer->scheme_number) {
				$data['statements_issued']->forget($k);
			}
		}
		
		$data['closed_accounts'] = DB::table('customers')->whereRaw("(deleted_at IS NOT NULL AND deleted_at >= '$from' AND deleted_at <= '$to' AND scheme_number = '$currentSchemeNumber')")->count();
        //get the csv URL
        $this->csvURL = is_null($schemeNumber) ? $this->repo->getCsvURL() : $this->repo->getCsvURL() . '/' . $currentSchemeNumber;

		if (is_null($schemeNumber)) {
            $this->layout->page = View::make('report/payout')
                                        ->with('csvURL', $this->csvURL)
                                        ->with('schemes', $schemes)
                                        ->with('scheme_number', $currentSchemeNumber)
                                        ->with('scheme_name', $currentSchemeName)
                                        ->with('data', $data);
        }
        else {		
			return View::make('report/payout_content')
							->with('csvURL', $this->csvURL)
							->with('scheme_number', $currentSchemeNumber)
							->with('scheme_name', $currentSchemeName)
							->with('data', $data);
		}
    }



    private function getReportData()
    {
        $data = [];
        $data['set_date'] = $this->repo->getDate('from') . ' - ' . $this->repo->getDate('to');
        $data['number_of_days'] = $this->repo->calculateDaysDiff() . ' days';
        $data['number_of_payments'] = $this->repo->getPaymentsInfo('count');
        $data['value_of_payments'] = $this->currencySign . ' '  . $this->repo->getPaymentsInfo('sum');
        $data['number_of_sms'] = $this->repo->getNumberOfSMS();
        $data['apps_installed'] = $this->repo->getAppsInstalled();
        $data['IOU_chargeable'] = $this->repo->getIOUChargeable() ? 'Yes' : 'No';
        $data['IOU_number'] = $this->repo->getIOUChargeableInfo('count');
        $data['number_of_meters'] = $this->repo->getMetersInfo('count');
        $data['number_of_meters_all'] = $this->repo->getMetersInfo('count', true);
        $data['meter_total'] = $this->repo->getMetersInfo('total');
        $data['meter_total_all'] = $this->repo->getMetersInfo('total', true);
		$data['scheme_total_usage'] = $this->repo->getSchemeTotalUsage();
		$data['scheme_avg_usage'] = $this->repo->getSchemeAvgUsage();
        $data['scheme_avg_cost'] = $this->repo->getSchemeAvgCost();
        $data['tariff_1'] = $this->repo->getT1();
        $data['tariff_2'] = $this->repo->getT2();

        return $data;
    }
	
}