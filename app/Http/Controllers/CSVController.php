<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DistrictHeatingMeter;
use App\Models\PaymentStorage;
use App\Models\PermanentMeterDataReadings;
use App\Models\PermanentMeterDataReadingsAll;
use App\Models\Scheme;
use App\Models\TemporaryPayments;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Route;

class CSVController extends Controller
{
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

        $totalKWhUsage = DistrictHeatingUsage::sum('total_usage');*/

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
        $totalCustomers = $customers->count();

        $data = '';
        $data .= "Total KWh Usage:, $totalKWhUsage\n";
        $data .= "Total Customers:, $totalCustomers\n";
        $data .= "Range:, $from - $to\n\n";
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'barcode,';
        $data .= 'total_usage,';
        $data .= 'meter_id_number,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->barcode.',';
            $data .= $customer->total_usage.',';
            $data .= $customer->permanent_meter_number.',';

            if (count($customer->readings)) {
                foreach ($customer->dhu as $customerReading) {
                    $data .= 'Reading on '.$customerReading['date'].',';
                    $data .= 'First reading: '.$customerReading['start_day_reading'].'; Last reading: '.$customerReading['end_day_reading'].',';
                }
            }

            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=supply_report_units.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function search_supply_report_units($search_key)
    {
        /*$customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,barcode,sum(total_usage) as  total_usage'))
        ->join('district_heating_usage', 'customers.id', '=', 'district_heating_usage.customer_id')
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->where(function($query)
            {
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
        ->groupby('district_heating_usage.customer_id')
        ->get();*/

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
        }

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'barcode,';
        $data .= 'total_usage,';
        $data .= 'meter_id_number,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->barcode.',';
            $data .= $customer->total_usage.',';
            $data .= $customer->permanent_meter_number.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=search_supply_report_units.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function search_supply_report_units_by_date($to, $from)
    {
        ini_set('max_execution_time', 1800);
        ini_set('memory_limit', '1024M');

        $to = date('Y-m-d', strtotime($to));
        $from = date('Y-m-d', strtotime($from));

        /*$customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,barcode,sum(total_usage) as  total_usage'))
        ->join('district_heating_usage', 'customers.id', '=', 'district_heating_usage.customer_id')
        ->where('district_heating_usage.date', '>=', $from)
        ->where('district_heating_usage.date', '<=', $to)
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->groupby('district_heating_usage.customer_id')
        ->get();*/

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
        $totalCustomers = $customers->count();

        $data = '';
        $data .= "Total KWh Usage:, $totalKWhUsage\n";
        $data .= "Total Customers:, $totalCustomers\n";
        $data .= "Range:, $from - $to\n\n";
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'barcode,';
        $data .= 'total_usage,';
        $data .= 'meter_id_number,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->barcode.',';
            $data .= $customer->total_usage.',';
            $data .= $customer->permanent_meter_number.',';

            if (count($customer->readings)) {
                foreach ($customer->dhu as $customerReading) {
                    $data .= 'Reading on '.$customerReading['date'].',';
                    $data .= 'First reading: '.$customerReading['start_day_reading'].'; Last reading: '.$customerReading['end_day_reading'].',';
                }
            }

            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=search_supply_report_units_by_date.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function pending_topups()
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name, surname, time_date, amount'))
        ->join('temporary_payments', 'temporary_payments.customer_id', '=', 'customers.id')
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->orderby('time_date', 'desc')
        ->get();

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'time_date,';
        $data .= 'amount,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->time_date.',';
            $data .= $customer->amount.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=pending_topups.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function pending_topups_by_search($search_key)
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name, surname, time_date, amount'))
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

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'time_date,';
        $data .= 'amount,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->time_date.',';
            $data .= $customer->amount.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=pending_topups.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function pending_topups_search_by_date($to, $from)
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name, surname, time_date, amount'))
        ->join('temporary_payments', 'temporary_payments.customer_id', '=', 'customers.id')
        ->where('time_date', '>=', $from)
        ->where('time_date', '<=', $to)
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->orderby('time_date', 'desc')
        ->get();

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'time_date,';
        $data .= 'amount,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->time_date.',';
            $data .= $customer->amount.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=pending_topups.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    // unused?
    public function system_topup_history()
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name, surname, time_date, amount'))
        ->join('payments_storage', 'payments_storage.customer_id', '=', 'customers.id')
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->orderby('time_date', 'desc')
        ->get();

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'time_date,';
        $data .= 'amount,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->time_date.',';
            $data .= $customer->amount.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=system_topup_history.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    // Fixed by Daniel 23/03/2018
    public function customer_topup_history()
    {
        $payments = PaymentStorage::where('scheme_number', Auth::user()->scheme_number)->orderBy('time_date', 'DESC')->get(['ref_number', 'customer_id', 'time_date', 'amount', 'currency_code', 'acceptor_name_location', 'acceptor_name_location_']);

        $total_amount = $payments->sum('amount');
        $total = $payments->count();
        $pp_payments = 0;
        $ppo_payments = 0;
        $pz_payments = 0;
        $s_payments = 0;

        foreach ($payments as $k => $v) {
            if (substr($v->ref_number, 0, 6) == 'PAYID-' || substr($v->ref_number, 0, 4) == 'PAY-' || $v->acceptor_name_location == 'paypal' || $v->acceptor_name_location_ == 'paypal') {
                $pp_payments++;
                continue;
            }
            if ($v->acceptor_name_location_ == 'paypoint' || $v->acceptor_name_location == 'paypoint' || substr($v->ref_number, 0, 3) == 'PPR') {
                $ppo_payments++;
                continue;
            }
            if ($v->acceptor_name_location_ == 'payzone' || $v->acceptor_name_location == 'payzone' || substr($v->ref_number, 0, 3) == 'PZ-') {
                $pz_payments++;
                continue;
            }
            if ($v->acceptor_name_location_ == 'stripe' || $v->acceptor_name_location == 'stripe' || substr($v->ref_number, 0, 3) == 'ch_') {
                $s_payments++;
                continue;
            }
        }

        $data = '';
        $data .= 'Total amount:,'.$total_amount."\n";
        $data .= 'Total topups:,'.$total."\n";
        $data .= 'Paypal:,'.$pp_payments."\n";
        $data .= 'Stripe:,'.$s_payments."\n";
        $data .= 'Payzone:,'.$pz_payments."\n";
        $data .= 'PayPoint:,'.$ppo_payments."\n";
        $data .= "\n";

        $data .= 'id,';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'time_date,';
        $data .= 'amount,';
        $data .= 'method,';
        $data .= "\n";

        $inserted_data = false;

        foreach ($payments as $payment) {
            $customer = Customer::where('id', $payment->customer_id)->first();

            if (! $customer) {
                $customer = new stdClass;
                $customer->id = $payment->customer_id;
                $customer->first_name = 'not found/deleted';
                $customer->surname = 'not found/delete';
            }

            $data .= $customer->id.',';
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $payment->time_date.',';
            $data .= $payment->amount.',';
            $data .= (strlen($payment->acceptor_name_location) > 2) ? $payment->acceptor_name_location : $payment->acceptor_name_location_.',';
            $data .= "\n";

            $inserted_data = true;
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=customer_topup_history.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    // Fixed by Daniel 23/03/2018
    public function customer_topup_history_by_search($search_key)
    {
        $customers = Customer::where('scheme_number', Auth::user()->scheme_number)->where(function ($query) {
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
        ->get(['first_name', 'surname', 'time_date', 'amount']);

        $paymentsCollection = new \Illuminate\Support\Collection();
        $customersCollection = new \Illuminate\Support\Collection();
        foreach ($customers as $customer) {
            $p1 = PaymentStorage::where('customer_id', $customer->id)->whereBetween('time_date', [$from, $to])->get(['customer_id', 'time_date', 'amount', 'currency_code']);
            foreach ($p1 as $p1_entry) {
                $paymentsCollection->push($p1_entry);
            }

            $p2 = TemporaryPayments::where('customer_id', $customer->id)->whereBetween('time_date', [$from, $to])->get(['customer_id', 'time_date', 'amount', 'currency_code']);
            foreach ($p2 as $p2_entry) {
                $paymentsCollection->push($p2_entry);
            }
        }

        $paymentsCollection->sortByDesc('time_date');

        foreach ($paymentsCollection as $key=>$pc) {
            $cust_id = $pc->customer_id;
            $customer = Customer::where('id', $cust_id)->first(['id', 'first_name', 'surname', 'scheme_number']);
            $customer['topup'] = $pc;

            $customersCollection->push($customer);
        }

        $total_amount = $paymentsCollection->sum('amount');

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'time_date,';
        $data .= 'amount,';
        $data .= "\n";
        foreach ($customersCollection as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->topup->time_date.',';
            $data .= $customer->topup->amount.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=customer_topup_history_by_search.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    // Fixed by Daniel 23/03/2018
    public function customer_topup_history_search_by_date($to, $from)
    {
        $payments = PaymentStorage::where('scheme_number', Auth::user()->scheme_number)->whereBetween('time_date', [$from, $to])->orderBy('time_date', 'DESC')->get(['ref_number', 'customer_id', 'time_date', 'amount', 'currency_code', 'acceptor_name_location',
        'acceptor_name_location_', ]);

        $total_amount = $payments->sum('amount');
        $total = $payments->count();
        $pp_payments = 0;
        $ppo_payments = 0;
        $pz_payments = 0;
        $s_payments = 0;

        foreach ($payments as $k => $v) {
            if (substr($v->ref_number, 0, 6) == 'PAYID-' || substr($v->ref_number, 0, 4) == 'PAY-' || $v->acceptor_name_location == 'paypal' || $v->acceptor_name_location_ == 'paypal') {
                $pp_payments++;
                continue;
            }
            if ($v->acceptor_name_location_ == 'paypoint' || $v->acceptor_name_location == 'paypoint' || substr($v->ref_number, 0, 3) == 'PPR') {
                $ppo_payments++;
                continue;
            }
            if ($v->acceptor_name_location_ == 'payzone' || $v->acceptor_name_location == 'payzone' || substr($v->ref_number, 0, 3) == 'PZ-') {
                $pz_payments++;
                continue;
            }
            if ($v->acceptor_name_location_ == 'stripe' || $v->acceptor_name_location == 'stripe' || substr($v->ref_number, 0, 3) == 'ch_') {
                $s_payments++;
                continue;
            }
        }

        $data = '';
        $data .= 'From:,'.$from."\n";
        $data .= 'To:,'.$to."\n";
        $data .= 'Total amount:,'.$total_amount."\n";
        $data .= 'Total topups:,'.$total."\n";
        $data .= 'Paypal:,'.$pp_payments."\n";
        $data .= 'Stripe:,'.$s_payments."\n";
        $data .= 'Payzone:,'.$pz_payments."\n";
        $data .= 'PayPoint:,'.$ppo_payments."\n";
        $data .= "\n";

        $data .= 'id,';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'time_date,';
        $data .= 'amount,';
        $data .= 'method,';
        $data .= "\n";

        $inserted_data = false;

        foreach ($payments as $payment) {
            $customer = Customer::where('id', $payment->customer_id)->first();

            if (! $customer) {
                $customer = new stdClass;
                $customer->id = $payment->customer_id;
                $customer->first_name = 'not found/deleted';
                $customer->surname = 'not found/delete';
            }

            $data .= $customer->id.',';
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $payment->time_date.',';
            $data .= $payment->amount.',';
            $data .= (strlen($payment->acceptor_name_location) > 2) ? $payment->acceptor_name_location : $payment->acceptor_name_location_.',';
            $data .= "\n";

            $inserted_data = true;
        }

        if (! $inserted_data) {
            $data .= 'No data found.';
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=customer_topup_history_search_by_date.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function barcode_reports()
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,house_number_name,street1,street2,town,county,barcode'))
        ->where('status', '=', 1)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->get();

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'house_number_name,';
        $data .= 'street1,';
        $data .= 'street2,';
        $data .= 'town,';
        $data .= 'county,';
        $data .= 'barcode,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->house_number_name.',';
            $data .= $customer->street1.',';
            $data .= $customer->street2.',';
            $data .= $customer->town.',';
            $data .= $customer->county.',';
            $data .= $customer->barcode.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=barcode_reports.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function search_barcode_reports($search_key)
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,house_number_name,street1,street2,town,county,barcode'))
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

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'house_number_name,';
        $data .= 'street1,';
        $data .= 'street2,';
        $data .= 'town,';
        $data .= 'county,';
        $data .= 'barcode,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->house_number_name.',';
            $data .= $customer->street1.',';
            $data .= $customer->street2.',';
            $data .= $customer->town.',';
            $data .= $customer->county.',';
            $data .= $customer->barcode.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=search_barcode_reports.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function meter_readings_reports($to = null, $from = null)
    {
        ini_set('memory_limit', '-1');

        $meterReadingsQuery = PermanentMeterDataReadings::with('permanentMeter')->where('scheme_number', '=', Auth::user()->scheme_number);
        $csvFilename = 'meter_readings_reports.csv';

        if ($to && $from) {
            $meterReadingsQuery = $meterReadingsQuery->where('time_date', '>=', $from)->where('time_date', '<=', $to);
            $csvFilename = 'search_meter_readings_reports.csv';
        }

        $meterReadings = $meterReadingsQuery->orderBy('time_date', 'desc')->get();

        $data = '';
        $data .= 'meter_number,';
        $data .= 'date,';
        $data .= 'reading,';
        $data .= "\n";
        foreach ($meterReadings as $meterReading) {
            $data .= ($meterReading->permanentMeter ? $meterReading->permanentMeter->meter_number : '').',';
            $data .= $meterReading->time_date.',';
            $data .= $meterReading->reading1.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename='.$csvFilename);
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function sms_messages()
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,customers.mobile_number,message,date_time,charge,customers.scheme_number'))
        ->join('sms_messages', 'customers.id', '=', 'sms_messages.customer_id')
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->orderby('date_time', 'desc')
        ->get();

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'mobile_number,';
        $data .= 'message,';
        $data .= 'date_time,';
        $data .= 'charge,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->mobile_number.',';
            $data .= $customer->message.',';
            $data .= $customer->date_time.',';
            $data .= $customer->charge.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=sms_messages.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function search_sms_messages($search_key)
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,customers.mobile_number,message,date_time,charge,customers.scheme_number'))
        ->join('sms_messages', 'customers.id', '=', 'sms_messages.customer_id')
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

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'mobile_number,';
        $data .= 'message,';
        $data .= 'date_time,';
        $data .= 'charge,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->mobile_number.',';
            $data .= $customer->message.',';
            $data .= $customer->date_time.',';
            $data .= $customer->charge.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=search_sms_messages.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function search_sms_messages_by_date($to, $from)
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,customers.mobile_number,message,date_time,charge,customers.scheme_number'))
        ->join('sms_messages', 'customers.id', '=', 'sms_messages.customer_id')
        ->where('date_time', '>=', $from)
        ->where('date_time', '<=', $to)
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->get();

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'mobile_number,';
        $data .= 'message,';
        $data .= 'date_time,';
        $data .= 'charge,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->mobile_number.',';
            $data .= $customer->message.',';
            $data .= $customer->date_time.',';
            $data .= $customer->charge.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=search_sms_messages_by_date.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function in_app_messages()
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,customers.smart_phone_id,message,date_time,charge,customers.scheme_number'))
        ->join('in_app_messages', 'customers.id', '=', 'in_app_messages.customer_id')
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->orderby('date_time', 'desc')
        ->get();

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'smart_phone_id,';
        $data .= 'message,';
        $data .= 'date_time,';
        $data .= 'charge,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->smart_phone_id.',';
            $data .= $customer->message.',';
            $data .= $customer->date_time.',';
            $data .= $customer->charge.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=in_app_messages.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function search_in_app_message($search_key)
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,customers.smart_phone_id,message,date_time,charge,customers.scheme_number'))
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

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'smart_phone_id,';
        $data .= 'message,';
        $data .= 'date_time,';
        $data .= 'charge,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->smart_phone_id.',';
            $data .= $customer->message.',';
            $data .= $customer->date_time.',';
            $data .= $customer->charge.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=search_in_app_message.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function search_in_app_message_by_date($to, $from)
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,customers.smart_phone_id,message,date_time,charge,customers.scheme_number'))
        ->join('in_app_messages', 'customers.id', '=', 'in_app_messages.customer_id')
        ->where('date_time', '>=', $from)
        ->where('date_time', '<=', $to)
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->get();

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'smart_phone_id,';
        $data .= 'message,';
        $data .= 'date_time,';
        $data .= 'charge,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->smart_phone_id.',';
            $data .= $customer->message.',';
            $data .= $customer->date_time.',';
            $data .= $customer->charge.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=search_in_app_message_by_date.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function names_notes()
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,utility_notes'))
        ->where('status', '=', 1)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->get();

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'utility_notes,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->utility_notes.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=names_notes.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function names_notes_by_search($search_key)
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,utility_notes'))
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

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'utility_notes,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->utility_notes.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=names_notes_by_search.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function names_mobile_numbers()
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,mobile_number,nominated_telephone'))
        ->where('status', '=', 1)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->get();

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'mobile_number,';
        $data .= 'nominated_telephone,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->mobile_number.',';
            $data .= $customer->nominated_telephone.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=names_mobile_numbers.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function names_mobile_numbers_by_search($search_key)
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,mobile_number,nominated_telephone'))
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

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'mobile_number,';
        $data .= 'nominated_telephone,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->mobile_number.',';
            $data .= $customer->nominated_telephone.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=names_mobile_numbers_by_search.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function name_address()
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,house_number_name,street1,street2,town,county'))
        ->where('status', '=', 1)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->get();

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'house_number_name,';
        $data .= 'street1,';
        $data .= 'street2,';
        $data .= 'town,';
        $data .= 'county,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->house_number_name.',';
            $data .= $customer->street1.',';
            $data .= $customer->street2.',';
            $data .= $customer->town.',';
            $data .= $customer->county.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=name_address.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function name_address_by_search($search_key)
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,house_number_name,street1,street2,town,county'))
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

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'house_number_name,';
        $data .= 'street1,';
        $data .= 'street2,';
        $data .= 'town,';
        $data .= 'county,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->house_number_name.',';
            $data .= $customer->street1.',';
            $data .= $customer->street2.',';
            $data .= $customer->town.',';
            $data .= $customer->county.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=name_address_by_search.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function names()
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname'))
        ->where('status', '=', 1)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->get();

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=names.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function names_by_search($search_key)
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname'))
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

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=names_by_search.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function total_balance()
    {
        $balance = Customer::where('status', '=', 1)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->sum('balance');

        $data = '';
        $data .= 'balance,';
        $data .= "\n";
        $data .= $balance.',';
        $data .= "\n";

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=total_balance.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function list_of_credit_user()
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,barcode,balance,house_number_name,street1,street2,town,county'))
        ->where('status', '=', 1)
        ->where('balance', '>', 0)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->get();

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'barcode,';
        $data .= 'balance,';
        $data .= 'house_number_name,';
        $data .= 'street1,';
        $data .= 'street2,';
        $data .= 'town,';
        $data .= 'county,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->barcode.',';
            $data .= $customer->balance.',';
            $data .= $customer->house_number_name.',';
            $data .= $customer->street1.',';
            $data .= $customer->street2.',';
            $data .= $customer->town.',';
            $data .= $customer->county.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=list_of_credit_user.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function total_credit_users_by_search($search_key)
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,barcode,balance,house_number_name,street1,street2,town,county'))
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

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'barcode,';
        $data .= 'balance,';
        $data .= 'house_number_name,';
        $data .= 'street1,';
        $data .= 'street2,';
        $data .= 'town,';
        $data .= 'county,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->barcode.',';
            $data .= $customer->balance.',';
            $data .= $customer->house_number_name.',';
            $data .= $customer->street1.',';
            $data .= $customer->street2.',';
            $data .= $customer->town.',';
            $data .= $customer->county.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=total_credit_users_by_search.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function list_of_debit_user()
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,barcode,balance,house_number_name,street1,street2,town,county'))
        ->where('status', '=', 1)
        ->where('balance', '<', 0)
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->get();

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'barcode,';
        $data .= 'balance,';
        $data .= 'house_number_name,';
        $data .= 'street1,';
        $data .= 'street2,';
        $data .= 'town,';
        $data .= 'county,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->barcode.',';
            $data .= $customer->balance.',';
            $data .= $customer->house_number_name.',';
            $data .= $customer->street1.',';
            $data .= $customer->street2.',';
            $data .= $customer->town.',';
            $data .= $customer->county.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=list_of_debit_user.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function total_debit_users_by_search($search_key)
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,barcode,balance,house_number_name,street1,street2,town,county'))
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

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'barcode,';
        $data .= 'balance,';
        $data .= 'house_number_name,';
        $data .= 'street1,';
        $data .= 'street2,';
        $data .= 'town,';
        $data .= 'county,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->barcode.',';
            $data .= $customer->balance.',';
            $data .= $customer->house_number_name.',';
            $data .= $customer->street1.',';
            $data .= $customer->street2.',';
            $data .= $customer->town.',';
            $data .= $customer->county.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=total_debit_users_by_search.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function deposit_reports()
    {
        $customers = DB::table('customers')
        ->select(DB::raw('surname,first_name,deposit_amount,date'))
        ->join('customer_deposits', 'customer_deposits.customer_id', '=', 'customers.id')
        ->where('customer_deposits.scheme_number', '=', Auth::user()->scheme_number)
        ->orderby('date', 'desc')
        ->get();

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'deposit_amount,';
        $data .= 'date,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->deposit_amount.',';
            $data .= $customer->date.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=deposit_reports.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function deposit_report_by_search($search_key)
    {
        $customers = DB::table('customers')
        ->select(DB::raw('surname,first_name,deposit_amount,date'))
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

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'deposit_amount,';
        $data .= 'date,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->deposit_amount.',';
            $data .= $customer->date.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=deposit_report_by_search.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function iou_usage_display()
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,time_date,charge'))
        ->join('iou_storage', 'iou_storage.customer_id', '=', 'customers.id')
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->orderby('time_date', 'desc')
        ->get();

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'time_date,';
        $data .= 'charge,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->time_date.',';
            $data .= $customer->charge.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=iou_usage_display.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function iou_usage_display_by_search($search_key)
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,time_date,charge'))
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

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'time_date,';
        $data .= 'charge,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->time_date.',';
            $data .= $customer->charge.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=iou_usage_display_by_search.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function iou_extra_usage_display()
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,date_time,charge'))
        ->join('iou_extra_storage', 'iou_extra_storage.customer_id', '=', 'customers.id')
        ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
        ->orderby('date_time', 'desc')
        ->get();

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'date_time,';
        $data .= 'charge,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->date_time.',';
            $data .= $customer->charge.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=iou_extra_usage_display.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function iou_extra_usage_display_by_search($search_key)
    {
        $customers = DB::table('customers')
        ->select(DB::raw('first_name,surname,date_time,charge'))
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

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'date_time,';
        $data .= 'charge,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->date_time.',';
            $data .= $customer->charge.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=iou_extra_usage_display_by_search.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function adminIssuedCredit()
    {
        $customers = DB::table('customers')
                    ->join('admin_issued_credit', 'admin_issued_credit.customer_id', '=', 'customers.id')
                    ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
                    ->orderby('date_time', 'desc')
                    ->get();

        $data = '';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'date_time,';
        $data .= 'admin_name,';
        $data .= 'amount,';
        $data .= 'reason,';
        $data .= "\n";
        foreach ($customers as $customer) {
            $data .= $customer->first_name.',';
            $data .= $customer->surname.',';
            $data .= $customer->date_time.',';
            $data .= $customer->admin_name.',';
            $data .= $customer->amount.',';
            $data .= $customer->reason.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=admin_issued_credit_display.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function listAllCustomers()
    {
        $green_data = Customer::where('status', '=', 1)->where('shut_off', '=', 0)->where('balance', '>=', 5)->where('scheme_number', '=', Auth::user()->scheme_number)->orderBy('balance', 'asc')->get();
        $yellow_data = Customer::where('status', '=', 1)->where('shut_off', '=', 0)->where('balance', '<', 5)->where('scheme_number', '=', Auth::user()->scheme_number)->orderBy('balance', 'asc')->get();
        $red_data = Customer::where('status', '=', 1)->where('shut_off', '=', 1)->where('scheme_number', '=', Auth::user()->scheme_number)->orderBy('balance', 'desc')->get();

        $data = '';
        $data .= 'commencement_date,';
        //$data .= 'barcode,';
        $data .= 'username,';
        $data .= 'email,';
        $data .= 'address,';
        $data .= 'mobile_number,';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'balance,';
        $data .= "\n";

        foreach ($red_data as $customer) {
            $data .= $this->composeCustomerCSVLine($customer);
        }

        foreach ($yellow_data as $customer) {
            $data .= $this->composeCustomerCSVLine($customer);
        }

        foreach ($green_data as $customer) {
            $data .= $this->composeCustomerCSVLine($customer);
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=list_all_customers.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function deletedCustomers()
    {
        $from = Input::get('from');
        $to = Input::get('to');

        $role = strpos(Route::getCurrentRoute()->getPath(), 'create_csv/inactive_landlords') !== false ? 'landlord' : 'normal';
        $customers = Customer::onlyTrashed()->where('scheme_number', '=', Auth::user()->scheme_number)->where('role', '=', $role)
        ->orderBy('deleted_at', 'DESC')
        ->whereRaw("(deleted_at >= '$from 00:00:00' AND deleted_at <= '$to 23:59:59')")
        ->get();

        $data = '';
        $data .= 'commencement_date,';
        //$data .= 'barcode,';
        $data .= 'username,';
        $data .= 'email,';
        $data .= 'address,';
        $data .= 'mobile_number,';
        $data .= 'first_name,';
        $data .= 'surname,';
        $data .= 'balance,';
        $data .= 'meter_reading,';
        $data .= 'deleted_at,';
        $data .= "\n";

        foreach ($customers as $customer) {
            $dhm = DistrictHeatingMeter::find($customer->meter_ID);
            $data .= $this->composeCustomerCSVLine($customer, $dhm);
        }

        $csvFilename = $role === 'landlord' ? 'inactive_landlords' : 'deleted_customers_'.$from.'_'.$to.'';

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename='.$csvFilename.'.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function bill_reports($from = null, $to = null)
    {
        //$currencySign = User::find(Auth::user()->id)->scheme()->first()->currency_sign;
        $currencySign = Scheme::where('scheme_number', Auth::user()->scheme_number)->first()->currency_sign;
        $currencyCode = $currencySign == '$' ? 'USD' : $currencySign == '£' ? 'GBP' : 'EUR';

        $withDates = ! is_null($from) && ! is_null($to);
        if ($withDates) {
            $to = date('Y-m-d', strtotime($to));
            $from = date('Y-m-d', strtotime($from));
        }

        $query = Customer::
                    join('district_heating_usage', 'customers.id', '=', 'district_heating_usage.customer_id')
                    ->where('customers.scheme_number', '=', Auth::user()->scheme_number);

        if ($withDates) {
            $query = $query->where('date', '>=', $from)->where('date', '<=', $to);
        }

        $customers = $query->groupby('customers.id')->get();
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

        $data = '';
        $data .= 'Customer Name,';
        $data .= 'Username,';
        $data .= 'Email,';
        $data .= 'Address,';
        $data .= 'Units,';
        $data .= 'Barcode,';
        $data .= 'Payment Total,';
        $data .= "\n";

        foreach ($regularCustomers as $customer) {
            $data .= $this->displayBillReportRow($customer, $withDates, $from, $to, $currencyCode);
        }

        $data .= "\nBLUE METERS\n";

        foreach ($billCustomers as $customer) {
            $data .= $this->displayBillReportRow($customer, $withDates, $from, $to, $currencyCode);
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=bill_report.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function missing_readings_reports()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 300);

        $customerMissingReadings = new \Illuminate\Support\Collection();

        foreach (Customer::inScheme(3)->get() as $customer) {
            $readingToCompareTo = [
                'date' => null,
                'start_day_reading' => null,
            ];

            $customerInfo = $this->extractRelevantCustomerInfo($customer);

            foreach ($customer->validDistrictHeatingUsage as $reading) {
                if (! $reading->end_day_reading >= $reading->start_day_reading) {
                    $readingToCompareTo['date'] = $reading->date;
                    $readingToCompareTo['start_day_reading'] = $reading->start_day_reading;
                    continue;
                }

                if (! is_null($readingToCompareTo['start_day_reading']) && $reading->start_day_reading > $readingToCompareTo['start_day_reading']) {
                    $customerInfo->missing_readings[] = [
                        'missing_reading_start_date' => $readingToCompareTo['date'],
                        'missing_reading_start_value' => $readingToCompareTo['start_day_reading'],
                        'missing_reading_end_date' => $reading->date,
                        'missing_reading_end_value' => $reading->start_day_reading,
                    ];

                    $readingToCompareTo['date'] = null;
                    $readingToCompareTo['start_day_reading'] = null;
                }
            }

            $customerMissingReadings->push($customerInfo);
        }

        $customerMissingReadings = $customerMissingReadings->filter(function ($item) {
            return count($item->missing_readings);
        });

        $data = '';
        $data .= 'Customer Name,';
        $data .= 'Username,';
        $data .= 'Email,';
        $data .= 'Address,';
        $data .= 'Barcode,';
        $data .= "\n";

        foreach ($customerMissingReadings as $customer) {
            $data .= $this->displayMissingReadingsReportRow($customer);
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=missing_readings_report.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function notReadMeters()
    {
        $datetime2DaysAgo = \Carbon\Carbon::now()->subDays(2);
        $meters = DistrictHeatingMeter::where('latest_reading_time', '<', $datetime2DaysAgo)->where('scheme_number', Auth::user()->scheme_number)->orderBy('latest_reading_time', 'DESC')->get();

        $data = '';
        $data .= 'Meter Number,';
        $data .= 'Latest Reading,';
        $data .= 'Latest Reading Date/Time,';
        $data .= 'Scheme Number,';
        $data .= "\n";

        foreach ($meters as $meter) {
            $data .= $meter->meter_number.',';
            $data .= $meter->latest_reading.',';
            $data .= $meter->latest_reading_time.',';
            $data .= $meter->scheme_number.',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=not_read_meters.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function paypalPayouts()
    {
        ini_set('memory_limit', '500M');

        $topupTo = new DateTime(date('Y-m-d'));
        $topupFrom = $topupTo->modify('-12 months');

        if (isset($_GET['from']) && isset($_GET['to'])) {
            $from = $_GET['from'];
            $to = $_GET['to'];

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
            $to = $topupTo->format('Y-m-d H:i:s');
        }

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

        $from_dt = new DateTime($from);
        $to_dt = new DateTime($to);

        $data = '';
        $data .= "Most popular day:,$mostPopularDay\n";
        $data .= 'Range:,'.$from_dt->format('d-m-Y').'-'.$to_dt->format('d-m-Y')."\n";
        $data .= "No. of months:,$no_months\n";
        $data .= "Total no. of topups:,$total_topups\n";
        $data .= "Total amount €:,$total_amount\n\n";

        $data .= "Day,No. Topups,€ Total\n";
        foreach ($totals as $key=>$day) {
            $data .= $key.',';
            $data .= $day['total_no'].',';
            $data .= $day['amount'].',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=paypal_payouts_report.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function payzonePayouts()
    {
        ini_set('memory_limit', '500M');

        $topupTo = new DateTime(date('Y-m-d'));
        $topupFrom = (new DateTime(date('Y-m-d')))->modify('-12 months');

        if (isset($_GET['from']) && isset($_GET['to'])) {
            $from = $_GET['from'];
            $to = $_GET['to'];

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
            $to = $topupTo->format('Y-m-d H:i:s').' 23:59:59';
        }

        $totals = [
            'Monday' => ['amount' => 0, 'total_no' => 0],
            'Tuesday' => ['amount' => 0, 'total_no' => 0],
            'Wednesday' => ['amount' => 0, 'total_no' => 0],
            'Thursday' => ['amount' => 0, 'total_no' => 0],
            'Friday' => ['amount' => 0, 'total_no' => 0],
            'Saturday' => ['amount' => 0, 'total_no' => 0],
            'Sunday' => ['amount' => 0, 'total_no' => 0],
        ];

        $payzoneTopups = PaymentStorage::whereBetween('time_date', [$from, $to])->where('ref_number', 'like', '%PZ-%')->get();
        $mostPopularDay = 'Sunday';
        $total_topups = 0;
        $total_amount = 0;
        foreach ($payzoneTopups as $topup) {
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

        $from_dt = new DateTime($from);
        $to_dt = new DateTime($to);

        $data = '';
        $data .= "Most popular day:,$mostPopularDay\n";
        $data .= 'Range:,'.$from_dt->format('d-m-Y').'-'.$to_dt->format('d-m-Y')."\n";
        $data .= "No. of months:,$no_months\n";
        $data .= "Total no. of topups:,$total_topups\n";
        $data .= "Total amount €:,$total_amount\n\n";

        $data .= "Day,No. Topups,€ Total\n";
        foreach ($totals as $key=>$day) {
            $data .= $key.',';
            $data .= $day['total_no'].',';
            $data .= $day['amount'].',';
            $data .= "\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=payzone_payouts_report.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    public function customerReadings($customer_id, $from = null, $to = null)
    {
        $customer = Customer::find($customer_id);

        if (! $customer) {
            return "Cannot find customer $customer_id";
        }

        $pmd = $customer->permanentMeter;

        if (! $pmd) {
            return "No permanent_meter_data entry for customer $customer_id.";
        }

        if ($from == null) {
            $from = $customer->commencement_date.' 00:00:00';
        }

        if ($to == null) {
            $to = date('Y-m-d H:i:s');
        }

        $data = 'Meter number:,'.$pmd->meter_number."\n";
        $data .= 'Customer ID:,'.$customer->id."\n";
        $data .= 'Username:,'.$customer->username."\n";
        $data .= 'Commencement date:,'.$customer->commencement_date."\n";
        $data .= 'From:,'.$from."\n";
        $data .= 'To:,'.$to."\n";
        $data .= "\n\n";
        $data .= "DateTime,Reading\n";

        $readings = PermanentMeterDataReadingsAll::where('permanent_meter_id', $pmd->ID)
        ->where('reading1', '>', 0)
        ->whereRaw("(time_date >= '$from' AND time_date <= '$to')")
        ->orderBy('ID', 'DESC')
        ->get();

        foreach ($readings as $k => $r) {
            $data .= $r->time_date.','.$r->reading1."\n";
        }

        $f = new DateTime($from);
        $t = new DateTime($to);
        $f = $f->format('Y-m-d');
        $t = $t->format('Y-m-d');
        $f = str_replace('-', '', $f);
        $t = str_replace('-', '', $t);

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename='.$customer_id.'_'.$customer->username.'_'.$f.'_'.$t.'_readings.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;
    }

    private function composeCustomerCSVLine($customer, $dhm = null)
    {
        $data = '';
        $address = $this->getCustomerAddressAsStr($customer);

        $data .= $customer->commencement_date.',';
        //$data .= $customer->barcode.',';
        $data .= $customer->username.',';
        $data .= $customer->email_address.',';
        $data .= '"'.$address.'",';
        $data .= $customer->mobile_number.',';
        $data .= $customer->first_name.',';
        $data .= $customer->surname.',';
        $data .= $customer->balance.',';
        if ($dhm) {
            $data .= $dhm->sudo_reading.',';
        }
        $data .= $customer->deleted_at.',';
        $data .= "\n";

        return $data;
    }

    private function getCustomerAddressAsStr($customer)
    {
        $address = '';
        $address .= $customer['house_number_name'] ? $customer['house_number_name'].', ' : '';
        $address .= $customer['street1'] ? $customer['street1'].', ' : '';
        $address .= $customer['street2'] ? $customer['street2'].', ' : '';
        $address .= $customer['town'] ? $customer['town'].', ' : '';
        $address .= $customer['county'] ? $customer['county'].($customer['country'] ? ', ' : '') : '';
        $address .= $customer['country'] ? $customer['country'] : '';

        return $address;
    }

    private function displayBillReportRow($customer, $withDates, $from, $to, $currencyCode)
    {
        $address = '';
        $address .= $customer->house_number_name ? $customer->house_number_name.', ' : '';
        $address .= $customer->street1 ? $customer->street1.', ' : '';
        $address .= $customer->street2 ? $customer->street2.', ' : '';
        $address .= $customer->town ? $customer->town.', ' : '';
        $address .= $customer->county ? $customer->county.($customer->country ? ', ' : '') : '';
        $address .= $customer->country ? $customer->country : '';

        $data = '';
        $data .= $customer->first_name.' '.$customer->surname.',';
        $data .= $customer->username.',';
        $data .= $customer->email_address.',';
        $data .= '"'.$address.'",';
        $data .= $customer->total_usage.',';
        $data .= '"'.$customer->barcode.'",';

        $paymentsQuery = DB::table('customers')
        ->select(db::raw('currency_code, time_date, amount'))
        ->join('payments_storage', 'payments_storage.customer_id', '=', 'customers.id')
        ->where('customer_id', $customer->customer_id);

        if ($withDates) {
            $paymentsQuery = $paymentsQuery->where('time_date', '>=', $from)->where('time_date', '<=', $to);
        }

        $customer->payments = $paymentsQuery
            ->orderby('time_date', 'desc')
            ->get();

        $customer->paymentsTotal = 0;
        foreach ($customer->payments as $payment) {
            $customer->paymentsTotal += $payment->amount;
        }

        $data .= $customer->paymentsTotal.' '.$currencyCode.',';

        if (count($customer->payments)) {
            foreach ($customer->payments as $customerPayment) {
                $data .= 'Payment on '.$customerPayment->time_date.',';
                $data .= $customerPayment->amount.' '.$currencyCode.',';
            }
        }
        $data .= "\n";

        return $data;
    }

    public function displayMissingReadingsReportRow($customer)
    {
        $address = '';
        $address .= $customer->house_number_name ? $customer->house_number_name.', ' : '';
        $address .= $customer->street1 ? $customer->street1.', ' : '';
        $address .= $customer->street2 ? $customer->street2.', ' : '';
        $address .= $customer->town ? $customer->town.', ' : '';
        $address .= $customer->county ? $customer->county.($customer->country ? ', ' : '') : '';
        $address .= $customer->country ? $customer->country : '';

        $data = '';
        $data .= $customer->first_name.' '.$customer->surname.',';
        $data .= $customer->username.',';
        $data .= $customer->email_address.',';
        $data .= '"'.$address.'",';
        $data .= '"'.$customer->barcode.'",';

        if (count($customer->missing_readings)) {
            foreach ($customer->missing_readings as $missingReading) {
                $data .= 'Missing Reading from '.
                            $missingReading['missing_reading_start_date'].' ('.$missingReading['missing_reading_start_value'].') '.
                            ' until '.$missingReading['missing_reading_end_date'].' ('.$missingReading['missing_reading_end_value'].') '.
                            ' is '.($missingReading['missing_reading_end_value'] - $missingReading['missing_reading_start_value']).',';
            }
        }
        $data .= "\n";

        return $data;
    }

    protected function extractRelevantCustomerInfo($customer)
    {
        $customerInfo = new stdClass();

        $customerInfo->id = $customer->id;
        $customerInfo->first_name = $customer->first_name;
        $customerInfo->surname = $customer->surname;
        $customerInfo->username = $customer->username;
        $customerInfo->email_address = $customer->email_address;

        $customerInfo->house_number_name = $customer->house_number_name;
        $customerInfo->street1 = $customer->street1;
        $customerInfo->street2 = $customer->street2;
        $customerInfo->town = $customer->town;
        $customerInfo->county = $customer->county;
        $customerInfo->country = $customer->country;

        $customerInfo->barcode = $customer->barcode;

        $customerInfo->missing_readings = [];

        return $customerInfo;
    }
}
