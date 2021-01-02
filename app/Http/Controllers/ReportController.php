<?php

class ReportController extends BaseController
{
    protected $layout = 'layouts.admin_website';

    public function __construct()
    {
        $this->beforeFilter('canAccessSystemReports', ['except' => 'barcode_reports']);
    }

    public function index()
    {
        $this->layout->page = View::make('report/index');
    }

    public function index_topup_reports()
    {
        $this->layout->page = View::make('report/index_topup_reports');
    }

    public function index_messaging_reports()
    {
        $this->layout->page = View::make('report/index_messaging_reports');
    }

    public function index_customer_supply_status()
    {
        $this->layout->page = View::make('report/index_customer_supply_status');
    }

    public function index_credit_issue_reports()
    {
        $this->layout->page = View::make('report/index_credit_issue_reports');
    }

    public function index_weather_reports()
    {
        $this->layout->page = View::make('report/index_weather_reports');
    }

    private function rand_color()
    {
        return '#'.str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }

    public function uptime_report($type = 1)
    {
        ini_set('memory_limit', '-1');

        $all_schemes = Scheme::where('archived', 0)->where('status_debug', 0)
        ->where('status_debug', 0)
        ->where('archived', 0)
        ->whereRaw('(scheme_number NOT in (23, 15, 6))')
        ->orderBy('id', 'DESC')->get();

        $today = date('Y-m-d');

        $to = (Input::get('to') ? Input::get('to') : "$today 00:00:00");
        $from = (Input::has('from') ? Input::get('from') : "$today 23:59:59");

        $this->layout->page = View::make('report/sim_report',
        [
            'all_schemes' => $all_schemes,
            'from' => $from,
            'to' => $to,

        ]);
    }

    public function get_uptime_report_data()
    {
        if (empty(Input::get('schemes'))) {
            return;
        }

        $selected_schemes = implode(',', Input::get('schemes'));

        $date = Input::get('date');
        $from = Input::get('from');
        if (empty($date)) {
            $date = date('Y-m-d');
        }

        $data = [];

        foreach (Input::get('schemes') as $k => $v) {
            $scheme = Scheme::find($v);

            $data[$scheme->scheme_nickname] = [
                'scheme' => $scheme->scheme_nickname,
                'chart_colour' => $scheme->chart_colour,
                'labels' => [

                ],
                'values' => [

                ],
            ];

            if (! empty($from)) {
                $tracking_data = TrackingScheme::whereRaw("(scheme_number = '$v')")
                ->whereRaw("(date >= '$from' AND date <= '$date')")
                ->get();
            } else {
                $tracking_data = TrackingScheme::whereRaw("(scheme_number = '$v')")
                ->where('date', $date)
                ->get();
            }

            foreach ($tracking_data as $p => $td) {
                $status_log = $td->status_log;
                try {
                    $status_log = unserialize($status_log);

                    if (is_array($status_log)) {
                        foreach ($status_log as $s => $sl) {
                            if (isset($sl[1])) {
                                $sf = (isset($sl[0])) ? $sl[0] : 1;
                                array_push($data[$scheme->scheme_nickname]['labels'], $sl[1]);
                                array_push($data[$scheme->scheme_nickname]['values'], $sf);
                            } else {
                                $sf = (isset($sl[0])) ? $sl[0] : 'blank';
                                array_push($data[$scheme->scheme_nickname]['labels'], $sf);
                                array_push($data[$scheme->scheme_nickname]['values'], 0);
                            }
                        }
                        //$data[$key]['values'] = $status_log;
                    } else {
                    }
                } catch (Exception $e) {
                    return $e->getMessage();
                }
            }
        }

        /*
        foreach($tracking_data as $key => $td) {


            $scheme = Scheme::find($td->scheme_number);

            $data[$key] = [
                'scheme' => $scheme->scheme_nickname,
                'chart_colour' => $scheme->chart_colour,
                'values' => [

                ]
            ];

            $status_log = $td->status_log;
            try {

                $status_log = unserialize($status_log);
                if(is_array($status_log)) {
                    $data[$key]['values'] = $status_log;
                }

            } catch(Exception $e) {

            }

        }
        */

        return Response::json($data);
    }

    public function get_uptime_report($scheme, $pdf = false)
    {
        if (! $pdf || $pdf == null) {
            return Redirect::to('system_reports/sim_reports')
            ->with([
                'set_scheme' => $scheme,
            ]);
        } else {
            return Redirect::to('system_reports/sim_reports')
            ->with([
                'set_scheme' => $scheme,
                'gen_report' => $scheme,
            ]);
        }
    }

    public function uptime_report_2()
    {
    }

    public function supply_report_units()
    {

        //ini_set('max_execution_time', 1800);
        ini_set('memory_limit', '-1');

        /*$customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,barcode,sum(total_usage) as  total_usage'))
        ->join('district_heating_usage', 'customers.id', '=', 'district_heating_usage.customer_id')
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->whereNull('customers.deleted_at')
        ->groupby('district_heating_usage.customer_id')
        ->get();

        $totalKWhUsage = DistrictHeatingUsage::where('scheme_number', '=', Auth::user()->scheme_number)->sum('total_usage');*/

        $from = (new \Carbon\Carbon('first day of this month'))->format('Y-m-d');
        $to = (new \Carbon\Carbon('last day of this month'))->format('Y-m-d');

        $customers = Customer::where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->orderBy('first_name', 'ASC')->get();

        foreach ($customers as $c) {
            $c->permanent_meter_number = $c->permanentMeter ? $c->permanentMeter->meter_number : 'No Permanent Meter Found';
            $c->permanent_meter_ID = $c->permanentMeter ? $c->permanentMeter->ID : 'No Permanent Meter Found';

            $readings = PermanentMeterDataReadingsAll::where('scheme_number', Auth::user()->scheme_number)
            ->where('permanent_meter_id', $c->permanent_meter_ID)
            ->where('time_date', '>=', ($from.' 00:00:00'))
            ->where('time_date', '<=', ($to.' 23:59:59'))
            ->where('reading1', '>', 0)
            ->get();

            $first_reading = 0;
            $last_reading = 0;

            if ($readings->count() > 0) {
                $first_reading = $readings[0]->reading1;
                $last_reading = $readings[$readings->count() - 1]->reading1;
            } else {
            }

            $total_usage = $last_reading - $first_reading;

            $dhu = [];

            foreach ($readings as $r) {
                $date = explode(' ', $r->time_date)[0];
                if (! isset($dhu[$date])) {
                    $dhu[$date] = [
                        'start_day_reading' => $r->reading1,
                        'end_day_reading' => $r->reading1,
                        'date' => $date,
                    ];
                } else {
                    if ($r->reading1 < $dhu[$date]['start_day_reading']) {
                        $dhu[$date]['start_day_reading'] = $r->reading1;
                    }
                    if ($r->reading1 > $dhu[$date]['end_day_reading']) {
                        $dhu[$date]['end_day_reading'] = $r->reading1;
                    }
                }
            }

            $c->dhu = $dhu;
            $c->total_usage = $total_usage;
            $c->readings = $readings;
        }

        $totalKWhUsage = $customers->sum('total_usage');

        $csv_url = URL::to('create_csv/supply_report_units');

        $this->layout->page = View::make('report/supply_report_units', ['customers' => $customers, 'total_usage' => $totalKWhUsage, 'csv_url' => $csv_url, 'from' => $from, 'to' => $to]);
    }

    public function search_supply_report_units()
    {
        $search_key = Input::get('search_box');

        /*$customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,barcode,sum(total_usage) as  total_usage'))
        ->join('district_heating_usage', 'customers.id', '=', 'district_heating_usage.customer_id')
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->where(function($query)
            {
                $query->orwhere('username', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('first_name', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('barcode', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('surname', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street1', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street2', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('email_address', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('mobile_number', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('town', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('county', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('nominated_telephone', 'like', '%'.Input::get('search_box').'%');
            })
        ->groupby('district_heating_usage.customer_id')
        ->get();

        $totalKWhUsage = DistrictHeatingUsage::where('scheme_number', '=', Auth::user()->scheme_number)->sum('total_usage');*/

        $customers = Customer::where('customers.scheme_number', '=', Auth::user()->scheme_number)
            ->where(function ($query) {
                $query->orwhere('username', 'like', '%'.Input::get('search_box').'%')
                            ->orWhere('first_name', 'like', '%'.Input::get('search_box').'%')
                            ->orWhere('barcode', 'like', '%'.Input::get('search_box').'%')
                            ->orWhere('surname', 'like', '%'.Input::get('search_box').'%')
                            ->orWhere('street1', 'like', '%'.Input::get('search_box').'%')
                            ->orWhere('street2', 'like', '%'.Input::get('search_box').'%')
                            ->orWhere('email_address', 'like', '%'.Input::get('search_box').'%')
                            ->orWhere('mobile_number', 'like', '%'.Input::get('search_box').'%')
                            ->orWhere('town', 'like', '%'.Input::get('search_box').'%')
                            ->orWhere('county', 'like', '%'.Input::get('search_box').'%')
                            ->orWhere('nominated_telephone', 'like', '%'.Input::get('search_box').'%');
            })
        ->orderBy('first_name', 'ASC')->get();

        foreach ($customers as $c) {
            $c->permanent_meter_number = $c->permanentMeter ? $c->permanentMeter->meter_number : 'No Permanent Meter Found';
            $c->permanent_meter_ID = $c->permanentMeter ? $c->permanentMeter->ID : 'No Permanent Meter Found';

            $readings = PermanentMeterDataReadingsAll::where('scheme_number', Auth::user()->scheme_number)
            ->where('permanent_meter_id', $c->permanent_meter_ID)
            ->where('time_date', '>=', ($from.' 00:00:00'))
            ->where('time_date', '<=', ($to.' 23:59:59'))
            ->where('reading1', '>', 0)
            ->get();

            $first_reading = 0;
            $last_reading = 0;

            if ($readings->count() > 0) {
                $first_reading = $readings[0]->reading1;
                $last_reading = $readings[$readings->count() - 1]->reading1;
            } else {
            }

            $total_usage = $last_reading - $first_reading;

            $dhu = [];

            foreach ($readings as $r) {
                $date = explode(' ', $r->time_date)[0];
                if (! isset($dhu[$date])) {
                    $dhu[$date] = [
                        'start_day_reading' => $r->reading1,
                        'end_day_reading' => $r->reading1,
                        'date' => $date,
                    ];
                } else {
                    if ($r->reading1 < $dhu[$date]['start_day_reading']) {
                        $dhu[$date]['start_day_reading'] = $r->reading1;
                    }
                    if ($r->reading1 > $dhu[$date]['end_day_reading']) {
                        $dhu[$date]['end_day_reading'] = $r->reading1;
                    }
                }
            }

            $c->dhu = $dhu;
            $c->total_usage = $total_usage;
            $c->readings = $readings;
        }

        $totalKWhUsage = $customers->sum('total_usage');

        $csv_url = URL::to('create_csv/search_supply_report_units/'.$search_key);

        $this->layout->page = View::make('report/supply_report_units', ['customers' => $customers, 'total_usage' => $totalKWhUsage, 'csv_url' => $csv_url]);
    }

    public function search_supply_report_units_by_date()
    {
        ini_set('max_execution_time', 1800);
        ini_set('memory_limit', '1024M');

        $to = date('Y-m-d', strtotime(Input::get('to')));
        $from = date('Y-m-d', strtotime(Input::get('from')));

        /*$customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,barcode,sum(total_usage) as  total_usage'))
        ->join('district_heating_usage', 'customers.id', '=', 'district_heating_usage.customer_id')
        ->where('district_heating_usage.date', '>=', $from)
        ->where('district_heating_usage.date', '<=', $to)
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->groupby('district_heating_usage.customer_id')
        ->get();


        $totalKWhUsage = DistrictHeatingUsage::where('scheme_number', '=', Auth::user()->scheme_number)->sum('total_usage');*/

        $customers = Customer::where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->orderBy('first_name', 'ASC')->get();

        foreach ($customers as $c) {
            $c->permanent_meter_number = $c->permanentMeter ? $c->permanentMeter->meter_number : 'No Permanent Meter Found';
            $c->permanent_meter_ID = $c->permanentMeter ? $c->permanentMeter->ID : 'No Permanent Meter Found';

            $readings = PermanentMeterDataReadingsAll::where('scheme_number', Auth::user()->scheme_number)
            ->where('permanent_meter_id', $c->permanent_meter_ID)
            ->where('time_date', '>=', ($from.' 00:00:00'))
            ->where('time_date', '<=', ($to.' 23:59:59'))
            ->where('reading1', '>', 0)
            ->get();

            $first_reading = 0;
            $last_reading = 0;

            if ($readings->count() > 0) {
                $first_reading = $readings[0]->reading1;
                $last_reading = $readings[$readings->count() - 1]->reading1;
            } else {
            }

            $total_usage = $last_reading - $first_reading;

            $dhu = [];

            foreach ($readings as $r) {
                $date = explode(' ', $r->time_date)[0];
                if (! isset($dhu[$date])) {
                    $dhu[$date] = [
                        'start_day_reading' => $r->reading1,
                        'end_day_reading' => $r->reading1,
                        'date' => $date,
                    ];
                } else {
                    if ($r->reading1 < $dhu[$date]['start_day_reading']) {
                        $dhu[$date]['start_day_reading'] = $r->reading1;
                    }
                    if ($r->reading1 > $dhu[$date]['end_day_reading']) {
                        $dhu[$date]['end_day_reading'] = $r->reading1;
                    }
                }
            }

            $c->dhu = $dhu;
            $c->total_usage = $total_usage;
            $c->readings = $readings;
        }

        $totalKWhUsage = $customers->sum('total_usage');

        $csv_url = URL::to('create_csv/search_supply_report_units_by_date/'.$to.'/'.$from);

        $this->layout->page = View::make('report/supply_report_units', ['customers' => $customers, 'total_usage' => $totalKWhUsage, 'csv_url' => $csv_url, 'from' => $from, 'to' => $to]);
    }

    public function pending_topups()
    {
        $customers = Customer::select(DB::raw('first_name, surname, time_date, amount'))
        ->join('temporary_payments', 'temporary_payments.customer_id', '=', 'customers.id')
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->orderby('time_date', 'desc')
        ->get();

        $total_amount = TemporaryPayments::where('scheme_number', '=', Auth::user()->scheme_number)->sum('amount');

        $csv_url = URL::to('create_csv/pending_topups');

        $this->layout->page = View::make('report/customer_pending_topups', ['customers' => $customers, 'total_amount' => $total_amount, 'csv_url' => $csv_url]);
    }

    public function pending_topups_by_search()
    {
        $search_key = Input::get('search_box');

        $customers = Customer::select(DB::raw('first_name, surname, time_date, amount'))
        ->join('temporary_payments', 'temporary_payments.customer_id', '=', 'customers.id')
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->where(function ($query) {
            $query->orwhere('username', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('first_name', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('surname', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street1', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street2', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('email_address', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('mobile_number', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('town', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('county', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('nominated_telephone', 'like', '%'.Input::get('search_box').'%');
        })
        ->orderby('time_date', 'desc')
        ->get();

        $total_amount = TemporaryPayments::where('scheme_number', '=', Auth::user()->scheme_number)->sum('amount');

        $csv_url = URL::to('create_csv/pending_topups_by_search/'.$search_key);

        $this->layout->page = View::make('report/customer_pending_topups', ['customers' => $customers, 'total_amount' => $total_amount, 'csv_url' => $csv_url]);
    }

    public function pending_topups_search_by_date()
    {
        $to = date('Y-m-d', strtotime(Input::get('to'))).' 23:59:59';
        $from = date('Y-m-d', strtotime(Input::get('from')));

        $query = Customer::select(DB::raw('first_name, surname, time_date, amount'))
                    ->join('temporary_payments', 'temporary_payments.customer_id', '=', 'customers.id')
                    ->where('time_date', '>=', $from)
                    ->where('time_date', '<=', $to)
                    ->where('customers.scheme_number', '=', Auth::user()->scheme_number);

        $customers = $query->orderby('time_date', 'desc')->get();

        $total_amount = $query->sum('amount');
        //$total_amount = TemporaryPayments::where('scheme_number', '=', Auth::user()->scheme_number)->sum('amount');

        $csv_url = URL::to('create_csv/pending_topups_by_search/'.$to.'/'.$from);

        $this->layout->page = View::make('report/customer_pending_topups', ['customers' => $customers, 'total_amount' => $total_amount, 'csv_url' => $csv_url]);
    }

    public function system_topup_history()
    {
        $customers = Customer::select(DB::raw('first_name, surname, time_date, amount'))
        ->join('payments_storage', 'payments_storage.customer_id', '=', 'customers.id')
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->orderby('time_date', 'desc')
        ->get();

        $total_amount = PaymentStorage::where('scheme_number', '=', Auth::user()->scheme_number)->sum('amount');

        $csv_url = URL::to('create_csv/system_topup_history');

        $this->layout->page = View::make('report/customer_topup_history', ['customers' => $customers, 'total_amount' => $total_amount, 'csv_url' => $csv_url]);
    }

    public function customer_topup_history()
    {
        $customers = Customer::where('scheme_number', Auth::user()->scheme_number)->get(['id', 'first_name', 'surname', 'scheme_number']);

        $payments = PaymentStorage::where('scheme_number', Auth::user()->scheme_number)->orderBy('time_date', 'DESC')->get(['ref_number', 'customer_id', 'time_date', 'amount', 'currency_code', 'acceptor_name_location', 'acceptor_name_location_']);
        $payments2 = PaymentStorage::where('scheme_number', Auth::user()->scheme_number);

        $total_amount = $payments->sum('amount');
        $total = $payments->count();
        $pp_payments = 0;
        $pp_payments_amount = 0;
        $ppo_payments = 0;
        $ppo_payments_amount = 0;
        $pz_payments = 0;
        $pz_payments_amount = 0;
        $s_payments = 0;
        $s_payments_amount = 0;

        foreach ($payments as $k => $v) {
            if (substr($v->ref_number, 0, 6) == 'PAYID-' || substr($v->ref_number, 0, 4) == 'PAY-' || $v->acceptor_name_location == 'paypal' || $v->acceptor_name_location_ == 'paypal') {
                $pp_payments++;
                $pp_payments_amount += $v->amount;
                continue;
            }

            if ($v->acceptor_name_location_ == 'paypoint' || $v->acceptor_name_location == 'paypoint' || substr($v->ref_number, 0, 3) == 'PPR') {
                $ppo_payments++;
                $ppo_payments_amount += $v->amount;
                continue;
            }

            if ($v->acceptor_name_location_ == 'payzone' || $v->acceptor_name_location == 'payzone' || substr($v->ref_number, 0, 3) == 'PZ-') {
                $pz_payments++;
                $pz_payments_amount += $v->amount;
                continue;
            }

            if ($v->acceptor_name_location_ == 'stripe' || $v->acceptor_name_location == 'stripe' || substr($v->ref_number, 0, 3) == 'ch_') {
                $s_payments++;
                $s_payments_amount += $v->amount;
                continue;
            }

            // if($paymentType == "") {
                //echo $v->ref_number . "\n";
                // continue;
            // }
        }

        $overload = false;
        if (count($payments) >= 100) {
            $overload = true;
        }

        //$total_amount = PaymentStorage::where('scheme_number', '=', Auth::user()->scheme_number)->sum('amount');

        $csv_url = URL::to('create_csv/customer_topup_history');
        $this->layout->page = View::make('report/customer_topup_history',
        [
            'overload' => $overload,
            'payments' => $payments,
            'pp_payments' => $pp_payments,
            'pp_payments_amount' => $pp_payments_amount,
            'ppo_payments' => $ppo_payments,
            'ppo_payments_amount' => $ppo_payments_amount,
            'pz_payments' => $pz_payments,
            'pz_payments_amount' => $pz_payments_amount,
            's_payments' => $s_payments,
            's_payments_amount' => $s_payments_amount,
            'total' => $total,
            'total_amount' => $total_amount,
            'csv_url' => $csv_url,
        ]);
    }

    public function customer_topup_history_by_search()
    {
        $search_key = Input::get('search_box');

        $customers = Customer::select(DB::raw('first_name, surname, time_date, amount'))
        ->join('payments_storage', 'payments_storage.customer_id', '=', 'customers.id')
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->where(function ($query) {
            $query->orwhere('username', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('first_name', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('surname', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street1', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street2', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('email_address', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('mobile_number', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('town', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('county', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('nominated_telephone', 'like', '%'.Input::get('search_box').'%');
        })
        ->orderby('time_date', 'desc')
        ->get();

        $total_amount = PaymentStorage::where('scheme_number', '=', Auth::user()->scheme_number)->sum('amount');

        $csv_url = URL::to('create_csv/customer_topup_history_by_search/'.$search_key);

        $this->layout->page = View::make('report/customer_topup_history', ['customers' => $customers, 'total_amount' => $total_amount, 'csv_url' => $csv_url]);
    }

    public function customer_topup_history_search_by_date()
    {
        $to = date('Y-m-d', strtotime(Input::get('to'))).' 23:59:59';
        $from = date('Y-m-d', strtotime(Input::get('from'))).' 00:00:00';

        $payments = PaymentStorage::where('scheme_number', Auth::user()->scheme_number)->whereBetween('time_date', [$from, $to])->orderBy('time_date', 'DESC')->get(['ref_number', 'customer_id', 'time_date', 'amount', 'currency_code', 'acceptor_name_location', 'acceptor_name_location_']);

        $total_amount = $payments->sum('amount');
        $total = $payments->count();
        $pp_payments = 0;
        $pp_payments_amount = 0;
        $ppo_payments = 0;
        $ppo_payments_amount = 0;
        $pz_payments = 0;
        $pz_payments_amount = 0;
        $s_payments = 0;
        $s_payments_amount = 0;

        foreach ($payments as $k => $v) {
            if (substr($v->ref_number, 0, 6) == 'PAYID-' || substr($v->ref_number, 0, 4) == 'PAY-' || $v->acceptor_name_location == 'paypal' || $v->acceptor_name_location_ == 'paypal') {
                $pp_payments++;
                $pp_payments_amount += $v->amount;
                continue;
            }

            if ($v->acceptor_name_location_ == 'paypoint' || $v->acceptor_name_location == 'paypoint' || substr($v->ref_number, 0, 3) == 'PPR') {
                $ppo_payments++;
                $ppo_payments_amount += $v->amount;
                continue;
            }

            if ($v->acceptor_name_location_ == 'payzone' || $v->acceptor_name_location == 'payzone' || substr($v->ref_number, 0, 3) == 'PZ-') {
                $pz_payments++;
                $pz_payments_amount += $v->amount;
                continue;
            }

            if ($v->acceptor_name_location_ == 'stripe' || $v->acceptor_name_location == 'stripe' || substr($v->ref_number, 0, 3) == 'ch_') {
                $s_payments++;
                $s_payments_amount += $v->amount;
                continue;
            }

            // if($paymentType == "") {
                //echo $v->ref_number . "\n";
                // continue;
            // }
        }

        $overload = false;
        if (count($payments) >= 100) {
            $overload = true;
        }

        $to_dt = new DateTime($to);
        $from_dt = new DateTime($from);

        $csv_url = URL::to('create_csv/customer_topup_history_search_by_date/'.$to.'/'.$from);

        $to = $to_dt->format('d-m-Y');
        $from = $from_dt->format('d-m-Y');

        $this->layout->page = View::make('report/customer_topup_history', [
            'from' => $from,
            'to' => $to,
            'overload' => false,
            'payments' => $payments,
            'total' => $total,
            'total_amount' => $total_amount,
            'csv_url' => $csv_url,
            'pp_payments' => $pp_payments,
            'pp_payments_amount' => $pp_payments_amount,
            'ppo_payments' => $ppo_payments,
            'ppo_payments_amount' => $ppo_payments_amount,
            'pz_payments' => $pz_payments,
            'pz_payments_amount' => $pz_payments_amount,
            's_payments' => $s_payments,
            's_payments_amount' => $s_payments_amount,
        ]);
    }

    public function tarrif_history()
    {
        $tarrifs = PrepagoDailyRecords::where('scheme_number', '=', Auth::user()->scheme_number)->orderby('record_date', 'desc')->get();

        $total_amount = DistrictHeatingUsage::where('scheme_number', '=', Auth::user()->scheme_number)->sum('total_usage');

        $csv_url = URL::to('create_csv/tarrif_history');

        $this->layout->page = View::make('report/tarrif_history_view', ['tarrifs' => $tarrifs, 'total_amount' => $total_amount, 'csv_url' => $csv_url]);
    }

    public function tarrif_history_by_date()
    {
        $to = date('Y-m-d', strtotime(Input::get('to')));
        $from = date('Y-m-d', strtotime(Input::get('from')));

        $tarrifs = PrepagoDailyRecords::where('scheme_number', '=', Auth::user()->scheme_number)
        ->where('record_date', '>=', $from)
        ->where('record_date', '<=', $to)
        ->orderby('record_date', 'desc')
        ->get();

        $total_amount = DistrictHeatingUsage::where('scheme_number', '=', Auth::user()->scheme_number)->sum('total_usage');

        $csv_url = URL::to('create_csv/tarrif_history_by_date/'.$to.'/'.$from);

        $this->layout->page = View::make('report/tarrif_history_view', ['tarrifs' => $tarrifs, 'total_amount' => $total_amount, 'csv_url' => $csv_url]);
    }

    public function barcode_reports()
    {
        $customers = Customer::select(DB::raw('first_name,surname,house_number_name,street1,street2,town,county,barcode,scheme_number'))
        ->where('status', '=', 1)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->get();

        $csv_url = URL::to('create_csv/barcode_reports');

        $this->layout->page = View::make('report/barcode_reports', ['customers' => $customers, 'csv_url' => $csv_url]);
    }

    public function search_barcode_reports()
    {
        $search_key = Input::get('search_box');

        $customers = Customer::select(DB::raw('first_name,surname,house_number_name,street1,street2,town,county,barcode'))
        ->where('status', '=', 1)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->where(function ($query) {
            $query->orwhere('username', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('first_name', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('surname', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street1', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street2', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('email_address', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('mobile_number', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('town', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('county', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('nominated_telephone', 'like', '%'.Input::get('search_box').'%');
        })
        ->get();

        $csv_url = URL::to('create_csv/search_barcode_reports/'.$search_key);

        $this->layout->page = View::make('report/barcode_reports', ['customers' => $customers, 'csv_url' => $csv_url]);
    }

    public function sms_messages()
    {
        $customers = Customer::select(DB::raw('first_name,surname,customers.mobile_number,message,date_time,charge,customers.scheme_number'))
        ->join('sms_messages', 'customers.id', '=', 'sms_messages.customer_id')
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->orderby('date_time', 'desc')
        ->get();

        $sms_count = Customer::join('sms_messages', 'customers.id', '=', 'sms_messages.customer_id')->where('sms_messages.scheme_number', '=', Auth::user()->scheme_number)->count();

        $total_amount = Customer::join('sms_messages', 'customers.id', '=', 'sms_messages.customer_id')->where('sms_messages.scheme_number', '=', Auth::user()->scheme_number)->sum('charge');

        $csv_url = URL::to('create_csv/sms_messages');

        $this->layout->page = View::make('report/sms_messages', ['customers' => $customers, 'sms_count' => $sms_count, 'total_amount' => number_format((float) $total_amount, 2, '.', ''), 'csv_url' => $csv_url]);
    }

    public function search_sms_messages()
    {
        $search_key = Input::get('search_box');

        $customers = Customer::select(DB::raw('first_name,surname,customers.mobile_number,message,date_time,charge,customers.scheme_number'))
        ->join('sms_messages', 'customers.id', '=', 'sms_messages.customer_id')
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->where(function ($query) {
            $query->orwhere('username', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('first_name', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('surname', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street1', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street2', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('email_address', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('customers.mobile_number', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('town', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('county', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('nominated_telephone', 'like', '%'.Input::get('search_box').'%');
        })
        ->orderby('date_time', 'desc')
        ->get();

        $sms_count = Customer::join('sms_messages', 'customers.id', '=', 'sms_messages.customer_id')->where('sms_messages.scheme_number', '=', Auth::user()->scheme_number)->count();

        $total_amount = Customer::join('sms_messages', 'customers.id', '=', 'sms_messages.customer_id')->where('sms_messages.scheme_number', '=', Auth::user()->scheme_number)->sum('charge');

        $csv_url = URL::to('create_csv/search_sms_messages/'.$search_key);

        $this->layout->page = View::make('report/sms_messages', ['customers' => $customers, 'sms_count' => $sms_count, 'total_amount' => number_format((float) $total_amount, 2, '.', ''), 'csv_url' => $csv_url]);
    }

    public function search_sms_messages_by_date()
    {
        $to = date('Y-m-d', strtotime(Input::get('to')));
        $from = date('Y-m-d', strtotime(Input::get('from')));

        $customers = Customer::select(DB::raw('first_name,surname,customers.mobile_number,message,date_time,charge,customers.scheme_number'))
        ->join('sms_messages', 'customers.id', '=', 'sms_messages.customer_id')
        ->where('date_time', '>=', $from)
        ->where('date_time', '<=', $to)
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->get();

        $sms_count = Customer::join('sms_messages', 'customers.id', '=', 'sms_messages.customer_id')->where('sms_messages.scheme_number', '=', Auth::user()->scheme_number)->count();

        $total_amount = Customer::join('sms_messages', 'customers.id', '=', 'sms_messages.customer_id')->where('sms_messages.scheme_number', '=', Auth::user()->scheme_number)->sum('charge');

        $csv_url = URL::to('create_csv/search_sms_messages_by_date/'.$to.'/'.$from);

        $this->layout->page = View::make('report/sms_messages', ['customers' => $customers, 'sms_count' => $sms_count, 'total_amount' => number_format((float) $total_amount, 2, '.', ''), 'csv_url' => $csv_url]);
    }

    public function in_app_messages()
    {
        $customers = Customer::select(DB::raw('first_name,surname,customers.smart_phone_id,message,date_time,charge,customers.scheme_number'))
        ->join('in_app_messages', 'customers.id', '=', 'in_app_messages.customer_id')
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->orderby('date_time', 'desc')
        ->get();

        $sms_count = Customer::join('in_app_messages', 'customers.id', '=', 'in_app_messages.customer_id')->where('in_app_messages.scheme_number', '=', Auth::user()->scheme_number)->count();

        $total_amount = Customer::join('in_app_messages', 'customers.id', '=', 'in_app_messages.customer_id')->where('in_app_messages.scheme_number', '=', Auth::user()->scheme_number)->sum('charge');

        $csv_url = URL::to('create_csv/in_app_messages');

        $this->layout->page = View::make('report/in_app_messages', ['customers' => $customers, 'sms_count' => $sms_count, 'total_amount' => number_format((float) $total_amount, 2, '.', ''), 'csv_url' => $csv_url]);
    }

    public function search_in_app_message()
    {
        $search_key = Input::get('search_box');

        $customers = Customer::select(DB::raw('first_name,surname,customers.smart_phone_id,message,date_time,charge,customers.scheme_number'))
        ->join('in_app_messages', 'customers.id', '=', 'in_app_messages.customer_id')
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->where(function ($query) {
            $query->orwhere('username', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('first_name', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('surname', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street1', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street2', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('email_address', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('mobile_number', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('town', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('county', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('nominated_telephone', 'like', '%'.Input::get('search_box').'%');
        })
        ->orderby('date_time', 'desc')
        ->get();

        $sms_count = Customer::join('in_app_messages', 'customers.id', '=', 'in_app_messages.customer_id')->where('in_app_messages.scheme_number', '=', Auth::user()->scheme_number)->count();

        $total_amount = Customer::join('in_app_messages', 'customers.id', '=', 'in_app_messages.customer_id')->where('in_app_messages.scheme_number', '=', Auth::user()->scheme_number)->sum('charge');

        $csv_url = URL::to('create_csv/search_in_app_message/'.$search_key);

        $this->layout->page = View::make('report/in_app_messages', ['customers' => $customers, 'sms_count' => $sms_count, 'total_amount' => number_format((float) $total_amount, 2, '.', ''), 'csv_url' => $csv_url]);
    }

    public function search_in_app_message_by_date()
    {
        $to = date('Y-m-d', strtotime(Input::get('to')));
        $from = date('Y-m-d', strtotime(Input::get('from')));

        $customers = Customer::select(DB::raw('first_name,surname,customers.smart_phone_id,message,date_time,charge,customers.scheme_number'))
        ->join('in_app_messages', 'customers.id', '=', 'in_app_messages.customer_id')
        ->where('date_time', '>=', $from)
        ->where('date_time', '<=', $to)
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->get();

        $sms_count = Customer::join('in_app_messages', 'customers.id', '=', 'in_app_messages.customer_id')->where('in_app_messages.scheme_number', '=', Auth::user()->scheme_number)->count();

        $total_amount = Customer::join('in_app_messages', 'customers.id', '=', 'in_app_messages.customer_id')->where('in_app_messages.scheme_number', '=', Auth::user()->scheme_number)->sum('charge');

        $csv_url = URL::to('create_csv/search_in_app_message_by_date/'.$to.'/'.$from);

        $this->layout->page = View::make('report/in_app_messages', ['customers' => $customers, 'sms_count' => $sms_count, 'total_amount' => number_format((float) $total_amount, 2, '.', ''), 'csv_url' => $csv_url]);
    }

    public function names_notes()
    {
        $customers = Customer::select(DB::raw('first_name,surname,utility_notes'))
        ->where('status', '=', 1)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->get();

        $csv_url = URL::to('create_csv/names_notes');

        $this->layout->page = View::make('report/customer_name_notes_view', ['customers' => $customers, 'csv_url' => $csv_url]);
    }

    public function names_notes_by_search()
    {
        $search_key = Input::get('search_box');

        $customers = Customer::select(DB::raw('first_name,surname,utility_notes'))
        ->where('status', '=', 1)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->where(function ($query) {
            $query->orwhere('username', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('first_name', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('surname', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street1', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street2', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('email_address', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('mobile_number', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('town', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('county', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('nominated_telephone', 'like', '%'.Input::get('search_box').'%');
        })
        ->get();

        $csv_url = URL::to('create_csv/names_notes_by_search/'.$search_key);

        $this->layout->page = View::make('report/customer_name_notes_view', ['customers' => $customers, 'csv_url' => $csv_url]);
    }

    public function names_mobile_numbers()
    {
        $customers = Customer::select(DB::raw('first_name,surname,mobile_number,nominated_telephone'))
        ->where('status', '=', 1)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->get();

        $csv_url = URL::to('create_csv/names_mobile_numbers');

        $this->layout->page = View::make('report/customer_name_mobile_view', ['customers' => $customers, 'csv_url' => $csv_url]);
    }

    public function names_mobile_numbers_by_search()
    {
        $search_key = Input::get('search_box');

        $customers = Customer::select(DB::raw('first_name,surname,mobile_number,nominated_telephone'))
        ->where('status', '=', 1)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->where(function ($query) {
            $query->orwhere('username', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('first_name', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('surname', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street1', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street2', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('email_address', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('mobile_number', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('town', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('county', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('nominated_telephone', 'like', '%'.Input::get('search_box').'%');
        })
        ->get();

        $csv_url = URL::to('create_csv/names_mobile_numbers_by_search/'.$search_key);

        $this->layout->page = View::make('report/customer_name_mobile_view', ['customers' => $customers, 'csv_url' => $csv_url]);
    }

    public function name_address()
    {
        $customers = Customer::select(DB::raw('first_name,surname,house_number_name,street1,street2,town,county'))
        ->where('status', '=', 1)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->get();

        $csv_url = URL::to('create_csv/name_address');

        $this->layout->page = View::make('report/customer_name_address_view', ['customers' => $customers, 'csv_url' => $csv_url]);
    }

    public function name_address_by_search()
    {
        $search_key = Input::get('search_box');

        $customers = Customer::select(DB::raw('first_name,surname,house_number_name,street1,street2,town,county'))
        ->where('status', '=', 1)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->where(function ($query) {
            $query->orwhere('username', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('first_name', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('surname', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street1', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street2', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('email_address', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('mobile_number', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('town', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('county', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('nominated_telephone', 'like', '%'.Input::get('search_box').'%');
        })
        ->get();

        $csv_url = URL::to('create_csv/name_address_by_search/'.$search_key);

        $this->layout->page = View::make('report/customer_name_address_view', ['customers' => $customers, 'csv_url' => $csv_url]);
    }

    public function names()
    {
        $customers = Customer::select(DB::raw('first_name,surname'))
        ->where('status', '=', 1)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->get();

        $csv_url = URL::to('create_csv/names');

        $this->layout->page = View::make('report/customer_name_view', ['customers' => $customers, 'csv_url' => $csv_url]);
    }

    public function names_by_search()
    {
        $search_key = Input::get('search_box');

        $customers = Customer::select(DB::raw('first_name,surname'))
        ->where('status', '=', 1)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->where(function ($query) {
            $query->orwhere('username', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('first_name', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('surname', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street1', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street2', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('email_address', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('mobile_number', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('town', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('county', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('nominated_telephone', 'like', '%'.Input::get('search_box').'%');
        })
        ->get();

        $csv_url = URL::to('create_csv/names_by_search/'.$search_key);

        $this->layout->page = View::make('report/customer_name_view', ['customers' => $customers, 'csv_url' => $csv_url]);
    }

    public function total_balance()
    {
        $balance = Customer::where('status', '=', 1)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->sum('balance');

        $csv_url = URL::to('create_csv/total_balance');

        $this->layout->page = View::make('report/total_balance_view', ['balance' => $balance, 'csv_url' => $csv_url]);
    }

    public function list_of_credit_user()
    {
        $customers = Customer::select(DB::raw('first_name,surname,barcode,balance,house_number_name,street1,street2,town,county'))
        ->where('status', '=', 1)
        ->where('balance', '>', 0)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->get();

        $csv_url = URL::to('create_csv/list_of_credit_user');

        $this->layout->page = View::make('report/credit_user_view', ['customers' => $customers, 'csv_url' => $csv_url]);
    }

    public function total_credit_users_by_search()
    {
        $search_key = Input::get('search_box');

        $customers = Customer::select(DB::raw('first_name,surname,barcode,balance,house_number_name,street1,street2,town,county'))
        ->where('status', '=', 1)
        ->where('balance', '>', 0)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->where(function ($query) {
            $query->orwhere('username', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('first_name', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('surname', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street1', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street2', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('email_address', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('mobile_number', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('town', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('county', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('nominated_telephone', 'like', '%'.Input::get('search_box').'%');
        })
        ->get();

        $csv_url = URL::to('create_csv/total_credit_users_by_search/'.$search_key);

        $this->layout->page = View::make('report/credit_user_view', ['customers' => $customers, 'csv_url' => $csv_url]);
    }

    public function list_of_debit_user()
    {
        $customers = Customer::select(DB::raw('first_name,surname,barcode,balance,house_number_name,street1,street2,town,county'))
        ->where('status', '=', 1)
        ->where('balance', '<', 0)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->get();

        $csv_url = URL::to('create_csv/list_of_debit_user');

        $this->layout->page = View::make('report/debit_user_view', ['customers' => $customers, 'csv_url' => $csv_url]);
    }

    public function total_debit_users_by_search()
    {
        $search_key = Input::get('search_box');

        $customers = Customer::select(DB::raw('first_name,surname,barcode,balance,house_number_name,street1,street2,town,county'))
        ->where('status', '=', 1)
        ->where('balance', '<', 0)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->where(function ($query) {
            $query->orwhere('username', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('first_name', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('surname', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street1', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street2', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('email_address', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('mobile_number', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('town', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('county', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('nominated_telephone', 'like', '%'.Input::get('search_box').'%');
        })
        ->get();

        $csv_url = URL::to('create_csv/total_debit_users_by_search/'.$search_key);

        $this->layout->page = View::make('report/debit_user_view', ['customers' => $customers, 'csv_url' => $csv_url]);
    }

    public function deposit_reports()
    {
        $customers = Customer::select(DB::raw('surname,first_name,deposit_amount,date'))
        ->join('customer_deposits', 'customer_deposits.customer_id', '=', 'customers.id')
        ->where('customer_deposits.scheme_number', '=', Auth::user()->scheme_number)
        ->orderby('date', 'desc')
        ->get();

        $csv_url = URL::to('create_csv/deposit_reports');

        $deposite_count = Customer::join('customer_deposits', 'customer_deposits.customer_id', '=', 'customers.id')->where('customer_deposits.scheme_number', '=', Auth::user()->scheme_number)->count();
        $total_amount = Customer::join('customer_deposits', 'customer_deposits.customer_id', '=', 'customers.id')->where('customer_deposits.scheme_number', '=', Auth::user()->scheme_number)->sum('deposit_amount');

        $this->layout->page = View::make('report/deposit_reports_view', ['customers' => $customers, 'deposite_count'=> $deposite_count, 'total_amount' => $total_amount, 'csv_url' => $csv_url]);
    }

    public function deposit_report_by_search()
    {
        $search_key = Input::get('search_box');

        $customers = Customer::select(DB::raw('surname,first_name,deposit_amount,date'))
        ->join('customer_deposits', 'customer_deposits.customer_id', '=', 'customers.id')
        ->where('customer_deposits.scheme_number', '=', Auth::user()->scheme_number)
        ->where(function ($query) {
            $query->orwhere('username', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('first_name', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('surname', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street1', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street2', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('email_address', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('mobile_number', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('town', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('county', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('nominated_telephone', 'like', '%'.Input::get('search_box').'%');
        })
        ->orderby('date', 'desc')
        ->get();

        $deposite_count = Customer::join('customer_deposits', 'customer_deposits.customer_id', '=', 'customers.id')->where('customer_deposits.scheme_number', '=', Auth::user()->scheme_number)->count();
        $total_amount = Customer::join('customer_deposits', 'customer_deposits.customer_id', '=', 'customers.id')->where('customer_deposits.scheme_number', '=', Auth::user()->scheme_number)->sum('deposit_amount');

        $csv_url = URL::to('create_csv/deposit_report_by_search/'.$search_key);

        $this->layout->page = View::make('report/deposit_reports_view', ['customers' => $customers, 'deposite_count'=> $deposite_count, 'total_amount' => $total_amount, 'csv_url' => $csv_url]);
    }

    public function iou_usage_display()
    {
        $customers = Customer::select(DB::raw('first_name,surname,time_date,charge'))
        ->join('iou_storage', 'iou_storage.customer_id', '=', 'customers.id')
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->orderby('time_date', 'desc')
        ->get();

        $csv_url = URL::to('create_csv/iou_usage_display');

        $this->layout->page = View::make('report/iou_usage_view', ['customers' => $customers, 'csv_url' => $csv_url]);
    }

    public function iou_usage_display_by_search()
    {
        $search_key = Input::get('search_box');

        $customers = Customer::select(DB::raw('first_name,surname,time_date,charge'))
        ->join('iou_storage', 'iou_storage.customer_id', '=', 'customers.id')
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->where(function ($query) {
            $query->orwhere('username', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('first_name', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('surname', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street1', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street2', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('email_address', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('mobile_number', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('town', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('county', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('nominated_telephone', 'like', '%'.Input::get('search_box').'%');
        })
        ->orderby('time_date', 'desc')
        ->get();

        $csv_url = URL::to('create_csv/iou_usage_display_by_search/'.$search_key);

        $this->layout->page = View::make('report/iou_usage_view', ['customers' => $customers, 'csv_url' => $csv_url]);
    }

    public function iou_extra_usage_display()
    {
        $customers = Customer::select(DB::raw('first_name,surname,date_time,charge'))
        ->join('iou_extra_storage', 'iou_extra_storage.customer_id', '=', 'customers.id')
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->orderby('date_time', 'desc')
        ->get();

        $csv_url = URL::to('create_csv/iou_extra_usage_display');

        $this->layout->page = View::make('report/iou_extra_usage_view', ['customers' => $customers, 'csv_url' => $csv_url]);
    }

    public function iou_extra_usage_display_by_search()
    {
        $search_key = Input::get('search_box');

        $customers = Customer::select(DB::raw('first_name,surname,date_time,charge'))
        ->join('iou_extra_storage', 'iou_extra_storage.customer_id', '=', 'customers.id')
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->where(function ($query) {
            $query->orwhere('username', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('first_name', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('surname', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street1', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street2', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('email_address', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('mobile_number', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('town', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('county', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('nominated_telephone', 'like', '%'.Input::get('search_box').'%');
        })
        ->orderby('date_time', 'desc')
        ->get();

        $csv_url = URL::to('create_csv/iou_extra_usage_display_by_search/'.$search_key);

        $this->layout->page = View::make('report/iou_extra_usage_view', ['customers' => $customers, 'csv_url' => $csv_url]);
    }

    public function adminIssuedCredit()
    {
        $customers = Customer::join('admin_issued_credit', 'admin_issued_credit.customer_id', '=', 'customers.id')
                    ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
                    ->orderby('date_time', 'desc')
                    ->get();

        $csv_url = URL::to('create_csv/admin_issued_credit');

        $this->layout->page = View::make('report/admin_issued_credit_view', ['customers' => $customers, 'csv_url' => $csv_url]);
    }

    public function deletedCustomers()
    {
        $from = Input::get('from');
        $to = Input::get('to');

        if (empty($from)) {
            $from = date('Y-m-d', strtotime('-1 month'));
        }

        if (empty($to)) {
            $to = date('Y-m-d');
        }

        $role = strpos(Route::getCurrentRoute()->getPath(), 'system_reports/inactive_landlords') !== false ? 'landlord' : 'normal';
        $customers = Customer::onlyTrashed()->where('scheme_number', '=', Auth::user()->scheme_number)->where('role', '=', $role)
        ->orderBy('deleted_at', 'DESC')
        ->whereRaw("(deleted_at >= '$from 00:00:00' AND deleted_at <= '$to 23:59:59')")
        ->get();
        $csv_url = $role === 'normal' ? URL::to('create_csv/deleted_customers') : URL::to('create_csv/inactive_landlords');

        $this->layout->page =
        View::make('report/deleted_customers_view',
        [
        'customers' => $customers,
        'csv_url' => $csv_url,
        'role' => $role,
        'from' => $from,
        'to' => $to,
        ]);
    }

    public function bill_reports()
    {
        $to = null;
        $from = null;
        $query = Customer::
                    //select(DB::raw('customers.id, CONCAT (first_name, " ", surname) as customer_name, username, email_address, house_number_name, street1, street2, town, county, country, barcode, sum(total_usage) as total_usage'))
                    join('district_heating_usage', 'customers.id', '=', 'district_heating_usage.customer_id')
                    ->where('customers.deleted_at', '=', null)
                    ->where('customers.scheme_number', '=', Auth::user()->scheme_number);

        if (Request::isMethod('post')) {
            $to = date('Y-m-d', strtotime(Input::get('to')));
            $from = date('Y-m-d', strtotime(Input::get('from')));

            $query = $query->where('district_heating_usage.date', '>=', $from)->where('district_heating_usage.date', '<=', $to);
        }

        $customers = $query->groupby('district_heating_usage.customer_id')->get();
        $billCustomers = new \Illuminate\Support\Collection();
        $regularCustomers = new \Illuminate\Support\Collection();

        foreach ($customers as $customer) {
            if ($to && $from) {
                $paymentsCollection = PaymentStorage::where('customer_id', $customer->customer_id)->whereBetween('time_date', [$from, $to])->get(['time_date', 'amount', 'currency_code']);
            } else {
                $paymentsCollection = PaymentStorage::where('customer_id', $customer->customer_id)->get(['time_date', 'amount', 'currency_code']);
            }

            $customer->payments = $paymentsCollection;
            $customer->paymentsTotal = $paymentsCollection->sum('amount');
            $customer->payments->sortByDesc('time_date');

            if ($to && $from) {
                $customer->total_usage = $customer->totalUsage($from, $to);
            } else {
                $customer->total_usage = $customer->totalUsageNoRange();
            }

            if ($customer->districtHeatingMeter && $customer->districtHeatingMeter->permanentMeterData && $customer->districtHeatingMeter->permanentMeterData->is_bill_paid_customer == 1) {
                $billCustomers->push($customer);
            } else {
                $regularCustomers->push($customer);
            }
        }

        $csv_url = URL::to('create_csv/bill_reports');
        if (Request::isMethod('post')) {
            $csv_url .= '/'.$from.'/'.$to;
        }

        $this->layout->page = View::make('report/bill_report', [
            'customers' => $regularCustomers,
            'blue_customers' => $billCustomers,
            'csv_url' => $csv_url,
        ]);
    }

    public function notReadMeters()
    {
        $datetime2DaysAgo = \Carbon\Carbon::now()->subDays(2);
        $meters = DistrictHeatingMeter::where('latest_reading_time', '<', $datetime2DaysAgo)->where('scheme_number', Auth::user()->scheme_number)->orderBy('latest_reading_time', 'DESC')->get();

        $csv_url = URL::to('create_csv/not_read_meters');

        $this->layout->page = View::make('report/not_read_meters', [
            'meters'  => $meters,
            'csv_url' => $csv_url,
        ]);
    }

    public function paypalPayouts()
    {
        try {
            ini_set('memory_limit', '500M');

            $csv_url = URL::to('create_csv/paypal_payout_reports');

            $topupTo = new DateTime(date('Y-m-d'));
            $topupFrom = (new DateTime(date('Y-m-d')))->modify('-12 months');

            if (isset($_GET['from']) && isset($_GET['to'])) {
                $from = $_GET['from'].' 00:00:00';
                $to = $_GET['to'].' 23:59:59';

                $from = new DateTime($from);
                $to = new DateTime($to);

                $from = $from->format('Y-m-d H:i:s');
                $to = $to->format('Y-m-d H:i:s');

                $no_months = PaymentStorage::no_months($from, $to);
            }

            if (! isset($from)) {
                $from = $topupFrom->format('Y-m-d H:i:s');
            }

            if (! isset($to)) {
                $to = $topupTo->format('Y-m-d').' 23:59:59';
            }

            $no_months = PaymentStorage::no_months($from, $to);

            $totals = [
            'Monday' => ['amount' => 0, 'total_no' => 0],
            'Tuesday' => ['amount' => 0, 'total_no' => 0],
            'Wednesday' => ['amount' => 0, 'total_no' => 0],
            'Thursday' => ['amount' => 0, 'total_no' => 0],
            'Friday' => ['amount' => 0, 'total_no' => 0],
            'Saturday' => ['amount' => 0, 'total_no' => 0],
            'Sunday' => ['amount' => 0, 'total_no' => 0],
        ];

            $paypalTopups = PaymentStorage::whereBetween('time_date', [$from, $to])->where('ref_number', 'like', '%PAY%')->get();
            $mostPopularDay = 'Sunday';
            $total_topups = 0;
            $total_amount = 0;
            foreach ($paypalTopups as $topup) {
                $date = $topup->time_date;
                $datetime = new DateTime($date);
                $day_of_week = $datetime->format('l');

                $totals[$day_of_week]['amount'] += $topup->amount;
                $totals[$day_of_week]['total_no']++;

                if ($totals[$day_of_week]['total_no'] > $totals[$mostPopularDay]['total_no']) {
                    $mostPopularDay = $day_of_week;
                }

                $total_amount += $topup->amount;
                $total_topups++;
            }

            $this->layout->page = View::make('report/paypal_payout_reports', [
            //'paypalTopups' => $paypalTopups,
            'mostPopularDay' => $mostPopularDay,
            'day_of_weeks' => $totals,
            'default_to' => $topupTo->format('d-m-Y'),
            'default_from' => $topupFrom->format('d-m-Y'),
            'to_dt' => new DateTime($to),
            'from_dt' => new DateTime($from),
            'no_months' => $no_months,
            'total_topups' => $total_topups,
            'total_amount' => $total_amount,
            'csvURL' => $csv_url,
        ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function payzonePayouts()
    {
        try {
            ini_set('memory_limit', '500M');

            $csv_url = URL::to('create_csv/payzone_payout_reports');

            $topupTo = new DateTime(date('Y-m-d'));
            $topupFrom = (new DateTime(date('Y-m-d')))->modify('-12 months');

            if (isset($_GET['from']) && isset($_GET['to'])) {
                $from = $_GET['from'].' 00:00:00';
                $to = $_GET['to'].' 23:59:59';

                $from = new DateTime($from);
                $to = new DateTime($to);

                $from = $from->format('Y-m-d H:i:s');
                $to = $to->format('Y-m-d H:i:s');

                $no_months = PaymentStorage::no_months($from, $to);
            }

            if (! isset($from)) {
                $from = $topupFrom->format('Y-m-d H:i:s');
            }

            if (! isset($to)) {
                $to = $topupTo->format('Y-m-d').' 23:59:59';
            }

            $no_months = PaymentStorage::no_months($from, $to);

            $totals = [
            'Monday' => ['amount' => 0, 'total_no' => 0],
            'Tuesday' => ['amount' => 0, 'total_no' => 0],
            'Wednesday' => ['amount' => 0, 'total_no' => 0],
            'Thursday' => ['amount' => 0, 'total_no' => 0],
            'Friday' => ['amount' => 0, 'total_no' => 0],
            'Saturday' => ['amount' => 0, 'total_no' => 0],
            'Sunday' => ['amount' => 0, 'total_no' => 0],
        ];

            $paypalTopups = PaymentStorage::whereBetween('time_date', [$from, $to])->where('ref_number', 'like', '%PZ-%')->get();

            $mostPopularDay = 'Sunday';
            $total_topups = 0;
            $total_amount = 0;
            foreach ($paypalTopups as $topup) {
                $date = $topup->time_date;
                $datetime = new DateTime($date);
                $day_of_week = $datetime->format('l');

                $totals[$day_of_week]['amount'] += $topup->amount;
                $totals[$day_of_week]['total_no']++;

                if ($totals[$day_of_week]['total_no'] > $totals[$mostPopularDay]['total_no']) {
                    $mostPopularDay = $day_of_week;
                }

                $total_amount += $topup->amount;
                $total_topups++;
            }

            $this->layout->page = View::make('report/payzone_payout_reports', [
            'mostPopularDay' => $mostPopularDay,
            'day_of_weeks' => $totals,
            'default_to' => $topupTo->format('d-m-Y'),
            'default_from' => $topupFrom->format('d-m-Y'),
            'to_dt' => new DateTime($to),
            'from_dt' => new DateTime($from),
            'no_months' => $no_months,
            'total_topups' => $total_topups,
            'total_amount' => $total_amount,
            'csvURL' => $csv_url,
        ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function missing_standing()
    {
        $scheme = Scheme::find(Auth::user()->scheme_number);

        /*
        if(Auth::user()->scheme_number <= 0 || is_null(Auth::user()->scheme_number) || DB::table('schemes')->where('id', Auth::user()->scheme_number)->first()->archived == 1) {
            Session::put('last_link', Route::getCurrentRoute()->getPath());
            return Redirect::to('welcome-schemes')->with(['errorMessage' => "<b>Error occured with last action:</b> Please re-select a valid, active scheme in order to continue with this action."
            ]);
        }*/

        $all = (! empty(Input::get('option')) && Input::get('option') == 'All Schemes') ? true : false;
        $date = (! empty(Input::get('date'))) ? Input::get('date') : date('Y-m-d');

        $scheme_customers = ($all) ? Customer::where('status', 1)->where('ev_role', null)->get() : Customer::where('scheme_number', $scheme->id)->where('ev_role', null)->get();

        foreach ($scheme_customers as $key => $c) {
            $c_scheme = Scheme::find($c->scheme_number);
            if ($c_scheme['archived']) {
                $scheme_customers->forget($key);
                continue;
            }

            if ($c->scheme_number < 1) {
                $scheme_customers->forget($key);
                continue;
            }

            if ($c_scheme['status_debug']) {
                $scheme_customers->forget($key);
                continue;
            }

            if (! $c->permanentMeter) {
                $scheme_customers->forget($key);
                continue;
            }

            if (! $c->districtMeter) {
                $scheme_customers->forget($key);
                continue;
            }

            $dhu_count = DistrictHeatingUsage::where('customer_id', $c->id)->where('date', $date)->count();
            $dhu = DistrictHeatingUsage::where('customer_id', $c->id)->where('date', $date)->first();

            $c->dhu_count = $dhu_count;
            $c->missing_standing = true;
            $c->missing_dhu = true;

            if ($dhu) {
                $c->missing_dhu = false;

                if ($dhu->standing_charge == $c_scheme->tariff->tariff_2) {
                    $c->missing_standing = false;
                    $scheme_customers->forget($key);
                    continue;
                }
            }

            $c->dhu = $dhu;
        }

        $this->layout->page = View::make('report/missing_standing', [
            'all' => $all,
            'scheme' => $scheme,
            'customers' => $scheme_customers,
            'date' => $date,
        ]);
    }

    public function missing_standing_rectify()
    {
        try {
            $date = Input::get('date');

            $customers = json_decode(Input::get('customers'));

            $num = 0;

            foreach ($customers as $c) {
                $customer = Customer::find($c);

                $scheme = $customer->scheme;

                $standing_charge = $scheme->tariff->tariff_2;

                $dhu = $customer->todaysDhu;

                if ($dhu->standing_charge != $standing_charge) {
                    if ($date == date('Y-m-d')) {
                        $customer->used_today += $standing_charge;
                    }

                    $customer->balance -= $standing_charge;
                    $customer->save();

                    $dhu->standing_charge = $standing_charge;
                    $dhu->cost_of_day += $standing_charge;
                    $dhu->save();

                    $num++;
                }
            }

            return Redirect::back()->with([
                'successMessage' => "Successfully fixed missing standing_charge for $num customers",
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => 'Unable to fix inconsistent usage: '.$e->getMessage(),
            ]);
        }
    }

    public function missing_dhu()
    {
        $scheme = Scheme::find(Auth::user()->scheme_number);

        if (Auth::user()->scheme_number <= 0 || is_null(Auth::user()->scheme_number) || DB::table('schemes')->where('id', Auth::user()->scheme_number)->first()->archived == 1) {
            Session::put('last_link', Route::getCurrentRoute()->getPath());

            return Redirect::to('welcome-schemes')->with(['errorMessage' => '<b>Error occured with last action:</b> Please re-select a valid, active scheme in order to continue with this action.',
            ]);
        }

        $all = (! empty(Input::get('option')) && Input::get('option') == 'All Schemes') ? true : false;
        $start_date = (! empty(Input::get('start_date'))) ? Input::get('start_date') : '2018-12-06';

        $scheme_customers = ($all) ? Customer::where('status', 1)->where('ev_role', null)->get() : Customer::where('scheme_number', $scheme->id)->where('ev_role', null)->get();

        foreach ($scheme_customers as $key => $c) {
            $c_scheme = Scheme::find($c->scheme_number);
            if ($c_scheme['archived']) {
                $scheme_customers->forget($key);
                continue;
            }

            if (! $c->permanentMeter) {
                $scheme_customers->forget($key);
                continue;
            }

            if (! $c->districtMeter) {
                $scheme_customers->forget($key);
                continue;
            }

            $dhu_count = DistrictHeatingUsage::where('customer_id', $c->id)->count();
            $dhu_count_post_nov = DistrictHeatingUsage::where('customer_id', $c->id)->where('date', '>', $start_date)->count();

            if ($dhu_count > 0 && $dhu_count_post_nov > 0) {
                $scheme_customers->forget($key);
            } else {
                $c->dhu_count = $dhu_count;
                $c->dhu_count_post_nov = $dhu_count_post_nov;
            }
        }

        $this->layout->page = View::make('report/missing_dhu', [
            'all' => $all,
            'scheme' => $scheme,
            'customers' => $scheme_customers,
        ]);
    }

    public function inconsistent_usage()
    {
        if (Input::get('date')) {
            $date = Input::get('date');
        } else {
            $date = date('Y-m-d');
        }

        $inconsistentDHU = Customer::inconsistentDHU($date);
        $num = $inconsistentDHU['inconsistent_usage'];
        $customers = $inconsistentDHU['inconsistent_usage_customers'];

        $this->layout->page = View::make('report/inconsistent_dhu', [
            'num' => $num,
            'customers' => $customers,
            'date' => $date,
        ]);
    }

    public function fix_inconsistent_usage()
    {
        try {
            $date = Input::get('date');

            $inconsistentDHU = Customer::inconsistentDHU($date);
            $num = $inconsistentDHU['inconsistent_usage'];
            $customers = $inconsistentDHU['inconsistent_usage_customers'];

            foreach ($customers as $c) {
                $e = $c->entry;
                $p = $c->prev_entry;

                $computed_usage = $e->end_day_reading - $e->start_day_reading;
                $actual_usage = $e->total_usage;

                // Exceptional case
                if (($e->end_day_reading < $e->start_day_reading) && $e->total_usage == 0) {
                    $e->start_day_reading = $e->end_day_reading;
                    $e->save();

                    continue;
                }

                if ($actual_usage > $computed_usage) {
                    $excess = $actual_usage - $computed_usage;
                    $e->start_day_reading -= $excess;
                    $e->save();
                    $p->end_day_reading = $e->start_day_reading;
                    $p->save();
                } elseif ($actual_usage < $computed_usage) {
                    $excess = abs($actual_usage - $computed_usage);
                    $e->start_day_reading += $excess;
                    $e->save();
                    $p->end_day_reading = $e->start_day_reading;
                    $p->save();
                }
            }

            return Redirect::back()->with([
                'successMessage' => 'Successfully fixed inconsistent usage',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => 'Unable to fix inconsistent usage: '.$e->getMessage(),
            ]);
        }
    }

    public function duplicate_dhu()
    {
        $duplicateDHU = Customer::duplicateDHU();
        $num = $duplicateDHU['duplicate_dhu'];
        $customers = $duplicateDHU['duplicate_dhu_customers'];

        $this->layout->page = View::make('report/duplicate_dhu', [
            'num' => $num,
            'customers' => $customers,
        ]);
    }

    public function remove_singular_duplicate_dhu()
    {
        try {
            $id = Input::get('id');

            $dhu = DistrictHeatingUsage::where('id', $id)->first();

            if (! $dhu) {
                throw new Exception("District_heating_usage id $id does not exist!");
            }
            $dhu->delete();

            return Redirect::back()->with([
                'successMessage' => "Successfully removed duplicate district_heating_usage ID #$id!",
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    public function remove_duplicate_dhu()
    {
        try {
            $customers = Customer::duplicateDHU()['duplicate_dhu_customers'];

            foreach ($customers as $c) {
                $entries = $c->duplicate_entries;
                $real = $entries[0];

                if ($real->standing_charge != 0 && $real->cost_of_day != 0) {
                    foreach ($entries as $key => $e) {
                        if ($key != 0) {
                            $e->delete();
                        }
                    }
                }
            }

            return Redirect::back()->with([
                'successMessage' => 'Successfully removed all duplicate district_heating_usage!',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    public function duplicate_dhm()
    {
        $scheme = Scheme::find(Auth::user()->scheme_number);

        if (Auth::user()->scheme_number <= 0 || is_null(Auth::user()->scheme_number) || DB::table('schemes')->where('id', Auth::user()->scheme_number)->first()->archived == 1) {
            Session::put('last_link', Route::getCurrentRoute()->getPath());

            return Redirect::to('welcome-schemes')->with(['errorMessage' => '<b>Error occured with last action:</b> Please re-select a <b>valid</b>, <b>active</b> scheme in order to continue with this action.',
            ]);
        }

        $all = (! empty(Input::get('option')) && Input::get('option') == 'All Schemes') ? true : false;

        $scheme_customers = ($all) ? Customer::where('status', 1)->where('ev_role', null)->get() : Customer::where('scheme_number', $scheme->id)->where('ev_role', null)->get();

        foreach ($scheme_customers as $key => $c) {
            $c_scheme = Scheme::find($c->scheme_number);
            if ($c_scheme['archived']) {
                $scheme_customers->forget($key);
                continue;
            }

            if (! $c->permanentMeter) {
                $scheme_customers->forget($key);
                continue;
            }

            if (! $c->districtMeter) {
                $scheme_customers->forget($key);
                continue;
            }

            if (count($c->duplicateMeters()) <= 0) {
                $scheme_customers->forget($key);
                continue;
            }

            $c->duplicates = $c->duplicateMeters();
        }

        $this->layout->page = View::make('report/duplicate_dhm', [
            'all' => $all,
            'scheme' => $scheme,
            'customers' => $scheme_customers,
        ]);
    }

    public function remove_duplicate_dhm()
    {
        try {
            $customer_id = Input::get('customer_id');
            $customer = Customer::find($customer_id);

            if (! $customer) {
                throw new Exception("Customer $customer_id does not exist!");
            }
            $duplicates = $customer->duplicateMeters();

            foreach ($duplicates as $d) {
                $d->delete();
            }

            return Redirect::back()->with([
                'successMessage' => 'Successfully deleted '.count($duplicates)." duplicate district_heating_meters associated with customer $customer_id (".$customer->username.')',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => 'Exception: '.$e->getMessage(),
            ]);
        }
    }

    public function report1($customer_v)
    {
        $customer = Customer::where('username', $customer_v)->first();
        if (! $customer) {
            $customer = Customer::find($customer_v);
        }

        $usage365 = $customer->districtHeatingUsage(['from' => '2018-02-28', 'to' => '2019-02-28']);

        $usage365 = HomeController::insertMissingDistrictUsage($customer->id, $usage365);
        $tariff = Tariff::where('scheme_number', $customer->scheme_number)->first();
        $kwh_charge = $tariff->tariff_1;
        $standing_charge = $tariff->tariff_2;

        $residual_kwh = 0;
        $unexpected_start_readings = 0;
        $abnormal_days = 0;
        $total_usage = 0;
        $total_cost = 0;

        if (count($usage365) > 0) {
            $peak_day = $usage365[0];
            $month_totals = [];

            foreach ($usage365 as $key => $u) {
                if ($u->start_day_reading <= 0) {
                    $u->start_day_reading = $u->end_day_reading;
                }

                if ($u->end_day_reading <= 0) {
                    $u->end_day_reading = $u->start_day_reading;
                }

                if ($u->cost_of_day >= 40 || $u->cost_of_day < 0) {
                    $u->cost_of_day = 0;
                    $u->start_day_reading = $u->end_day_reading;
                }

                $computed_usage = $u->end_day_reading - $u->start_day_reading;
                $computed_cod = ($computed_usage * $kwh_charge) + ($u->arrears_repayment) + ($standing_charge);

                $u->expected_cod = $computed_cod;
                $u->normal = true;
                if (abs($u->expected_cod - $u->cost_of_day) > 0.01) {
                    $u->normal = false;
                    $abnormal_days++;
                }

                $total_cost += $u->cost_of_day;

                //echo $u->date . '|' . $total_usage . '<br/>';
                $u->date = (new DateTime($u->date))->format('d-m-Y');

                if ($u->cost_of_day > $peak_day->cost_of_day) {
                    $peak_day = $u;
                }

                $month = (new DateTime($u->date))->format('m');

                $total_usage += $computed_usage;
                if (isset($usage365[$key - 1])) {
                    if ($usage365[$key - 1]->end_day_reading != $u->start_day_reading) {
                        $unexpected_start_readings++;
                        $u->e_start = $usage365[$key - 1]->end_day_reading;
                        if ($u->e_start > 0 && ! empty($u->e_start)) {
                            if ($u->e_start < $u->start_day_reading) {
                                $residual_kwh += ($u->start_day_reading - $u->e_start);
                            }
                        }
                    }
                }

                if (! isset($month_totals[$month])) {
                    $month_totals[$month] = [
                    'usage' => $computed_usage,
                    'cost' => $u->cost_of_day,
                    'days' => 1,
                    'month' => (new DateTime($u->date))->format('F'),
                ];
                } else {
                    $month_totals[$month]['usage'] += $computed_usage;
                    $month_totals[$month]['cost'] += $u->cost_of_day;
                    $month_totals[$month]['days']++;
                }
            }
        } else {
            $peak_day = null;
            $month_totals = [];
        }

        /*
        $first_reading = 0;
        $i = 0;
        while($first_reading == 0) {
            $first_reading = $usage365[$i]->start_day_reading;
            $i++;
        }
        $last_reading = 0;
        $i2 = count($usage365)-1;
        while($last_reading == 0) {
            $last_reading = $usage365[$i2]->end_day_reading;
            $i2--;
        }
        $total_usage = $last_reading - $first_reading;*/

        $avg_daily_usage = $total_usage / 365;
        $avg_daily_cost = $total_cost / 365;

        $payments = PaymentStorage::where('customer_id', $customer->id)
        ->whereRaw("settlement_date >= '2018-02-28' AND settlement_date <= '2019-02-28'")
        ->orderBy('settlement_date', 'ASC')
        ->get();

        $this->layout->page = View::make('report.customer.report1', [

            'customer' => $customer,
            'usage365' => $usage365,
            'abnormal_days' => $abnormal_days,
            'total_usage' => $total_usage,
            'total_cost' => $total_cost,
            'avg_daily_usage' => $avg_daily_usage,
            'avg_daily_cost' => $avg_daily_cost,
            'peak_day' => $peak_day,
            'unexpected_start_readings' => $unexpected_start_readings,
            'month_totals' => $month_totals,
            'payments' => $payments,

        ]);
    }
}
