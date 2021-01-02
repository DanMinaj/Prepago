<?php

class CSVPayoutController extends CSVBaseController {

    protected $repo;

    public function __construct(PayoutReportRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index($from = null, $to = null, $schemeNumber = null)
    {
        $vat = Input::get('vat');

        //convert "from" and "to" dates from Y-m-d to d-m-Y
        $fromFormatted = $this->convertDateToFormat('d-m-Y', $from);
        $toFormatted = $this->convertDateToFormat('d-m-Y', $to);

        $this->repo->setFromDate($fromFormatted, true);
        $this->repo->setToDate($toFormatted, true);

        $csvData = ",,\n";
		
		$currentSchemeNumber = is_null($schemeNumber) ? Auth::user()->scheme_number : $schemeNumber;
        $this->repo->setSchemeNumber($currentSchemeNumber);

        $data = [];
        $data['number_of_days'] = $this->repo->calculateDaysDiff() . ' days';
        $data['number_of_payments'] = $this->repo->getPaymentsInfo('count');
        $data['value_of_payments'] = $this->repo->getPaymentsInfo('sum');
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

        $csvData .= "Set Date, " . $from . " - " . $to . "\n";
        $csvData .= "Number Of Days, " . $data['number_of_days'] . "\n";
        $csvData .= "VAT rate, " . $vat . "\n";
        $csvData .= "Number of Payments, " . $data['number_of_payments'] . "\n";
        $csvData .= "Value Of Payments, " . $data['value_of_payments'] . "\n";
        $csvData .= "Number of SMS Messages, " . $data['number_of_sms'] . "\n";
        $csvData .= "Apps Installed, " . $data['apps_installed'] . "\n";
        $csvData .= "IOU Chargeable, " . $data['IOU_chargeable'] . "\n";
        $csvData .= "IOU Number, " . $data['IOU_number'] . "\n";
        $csvData .= "Number Of Meters, " . $data['number_of_meters'] . "\n";
        $csvData .= "Meters Total, " . $data['meter_total'] . "\n";
		$csvData .= "Number Of Meters (All), " . $data['number_of_meters_all'] . "\n";
        $csvData .= "Meters Total (All), " . $data['meter_total_all'] . "\n";
		$csvData .= "Total Heat Sold, " . $data['scheme_total_usage'] . "\n";
		$csvData .= "Scheme Average Usage, " . number_format((float)$data['scheme_avg_usage'], 2, '.', '') . "\n";
        $csvData .= "Scheme Average Cost, " . number_format((float)$data['scheme_avg_cost'], 2, '.', '') . "\n";
		
		$statements_issued = SnugzoneAppStatement::whereRaw("(created_at >= '$from 00:00:00' AND created_at <= '$to 23:59:59')")->get();
		foreach($statements_issued as $k => $v) {
			$customer = Customer::find($v->customer_id);
			if(!$customer || $currentSchemeNumber != $customer->scheme_number) {
				$statements_issued->forget($k);
			}
		}
		
		$closed_accounts = DB::table('customers')->whereRaw("(deleted_at IS NOT NULL AND deleted_at >= '$from 00:00:00' AND deleted_at <= '$to 23:59:59' AND scheme_number = '$currentSchemeNumber')")->count();
        $csvData .= "Closed accounts, " . $closed_accounts . "\n";
        $csvData .= "Account statements issued, " . count($statements_issued) . "\n";

        $csvFilename = 'payout_report_for_scheme_number_' . $currentSchemeNumber;

        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=" . $csvFilename .".csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        print $csvData;
    }

}