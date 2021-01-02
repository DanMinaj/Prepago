<?php

namespace App\Http\Controllers;

use App\Models\AwayModeLog;
use App\Models\Customer;
use App\Models\CustomerArrears;
use App\Models\CustomerBalanceChange;
use App\Models\DataLogger;
use App\Models\DistrictHeatingMeter;
use App\Models\DistrictHeatingUsage;
use App\Models\DistrictHeatingUsageAdvanced;
use App\Models\InAppNotification;
use App\Models\IOUStorage;
use App\Models\MBusAddressTranslation;
use App\Models\MeterLookup;
use App\Models\PaymentStorage;
use App\Models\Paypal;
use App\Models\PermanentMeterData;
use App\Models\PermanentMeterDataReadingsAll;
use App\Models\RemoteControlStatus;
use App\Models\Scheme;
use App\Models\Simcard;
use App\Models\SMSMessage;
use App\Models\Tariff;
use App\Models\TrackingCustomerActivity;
use App\Models\UtilityNote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class HomeController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function __construct()
    {
        $this->log = new Logger('Shut off reset log');
        $this->log->pushHandler(new StreamHandler(storage_path('logs/shut_off_reset.log'), Logger::INFO));
    }

    public function showWelcome()
    {
        $last_topup = (object) [
            'amount' => '0.00',
            'time_date' => 'never',
            'customer' => (object) ['id' => '0', 'first_name' => 'null', 'surname' => 'null'],
            'topup_type' => 'none',
        ];

        $green_data = Customer::getNormalCustomers();
        $yellow_data = Customer::getPendingCustomers();
        $red_data = Customer::getShutOffCustomers();
        $white_data = Customer::getEmptyCustomers();

        $blue_data = Customer::leftjoin('district_heating_meters', 'customers.meter_ID', '=', 'district_heating_meters.meter_ID')
                            ->leftjoin('permanent_meter_data', 'district_heating_meters.permanent_meter_ID', '=', 'permanent_meter_data.ID')
                            ->select('customers.*')
                            ->where('permanent_meter_data.is_bill_paid_customer', 1)
                            ->where('status', '=', 1)
                            ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
                            ->orderBy('id', 'ASC')
                            ->get();

        $boilerMeters = PermanentMeterData::where('is_boiler_room_meter', 1)
                        ->where('scheme_number', '=', Auth::user()->scheme_number)->get();

        $red = $red_data;
        $yellow = $yellow_data;
        $green = $green_data;
        $blue = $blue_data;
        $white = $white_data;

        $fromSystemReports = strpos(Route::getCurrentRoute()->getPath(), 'system_reports/list_all_customers') !== false ? 1 : 0;
        View::share('fromSystemReports', $fromSystemReports);

        $showMeterReadingsAutomationButton = Auth::user()->isAutoMeterReadingAvailable(Auth::user()->scheme_number);

        $last_topup = PaymentStorage::where('scheme_number', Auth::user()->scheme_number)->orderBy('time_date', 'DESC')->first();
        if ($last_topup) {
            $last_topup['topup_type'] = ucfirst($last_topup->acceptor_name_location_);
            $last_topup['customer'] = (object) [
                'id' => 0,
                'first_name' => 'undefined',
                'surname' => 'undefined',
            ];
            if (empty(trim($last_topup->acceptor_name_location_))) {
                $last_topup->acceptor_name_location_ = 'Paypoint';
            }
            if ($last_topup->customer_id > 0) {
                $customer = Customer::find($last_topup->customer_id);
                if ($customer) {
                    $last_topup['customer'] = $customer;
                }
            }
        }

        $all_customers = $blue_data = Customer::leftjoin('district_heating_meters', 'customers.meter_ID', '=', 'district_heating_meters.meter_ID')
                            ->leftjoin('permanent_meter_data', 'district_heating_meters.permanent_meter_ID', '=', 'permanent_meter_data.ID')
                            ->select('customers.*')
                            ->where('permanent_meter_data.is_bill_paid_customer', 1)
                            ->where('status', '=', -1)
                            ->where('customers.scheme_number', '=', Auth::user()->scheme_number)
                            ->orderBy('id', 'ASC')
                            ->get();
        $all_customers = $all_customers->merge($blue);
        $all_customers = $all_customers->merge($green);
        $all_customers = $all_customers->merge($yellow);
        $all_customers = $all_customers->merge($red);

        $categories = [];

        foreach ($all_customers as $customer) {
            $username = strtolower(preg_replace('/[0-9]+/', '', $customer->username));
            if ($username == 'hfairways') {
                $username = 'Fairways Hall';
            }
            //echo $username . '<br/>';
            if (! isset($categories[$username])) {
                $categories[$username] = [];
                $categories[$username]['green'] = [];
                $categories[$username]['yellow'] = [];
                $categories[$username]['red'] = [];
                $categories[$username]['blue'] = [];
            }

            array_push($categories[$username][$customer->colour], $customer);
        }

        if (count($categories) > 1 && Auth::user()->scheme_number == 19) {
            $categories = array_reverse($categories);
        }

        $scheme_info = Scheme::find(Auth::user()->scheme_number);

        $this->layout->page = view('home/welcome', [
            'show_meter_readings_automation_button' => $showMeterReadingsAutomationButton,
            'white'         => $white,
            'red'           => $red,
            'yellow'        => $yellow,
            'green'         => $green,
            'blue'          => $blue,
            'boiler_meters' => $boilerMeters,
            'last_topup' => $last_topup,
            'scheme_info' => $scheme_info,
            'categories' => $categories,
        ]);
    }

    public function logout()
    {
        Auth::logout();
        Session::flush();

        return redirect('/');
    }

    public function customer_search()
    {
        $this->layout->page = view('home/customer_search');
    }

    public function edit_customer_details()
    {
        $this->layout->page = view('home/edit_customer_details');
    }

    public function search()
    {
        $search_term = strtolower(Input::get('search_box'));

        $customers = Customer::where('status', 1)->where('scheme_number', Auth::user()->scheme_number)->get();
        $results = Customer::where('id', -1)->get();
        foreach ($customers as $c) {
            if (strpos(strtolower($c->username), $search_term) !== false) {
                $results->push($c);
            } elseif (strpos(strtolower($c->first_name), $search_term) !== false) {
                $results->push($c);
            } elseif (strpos(strtolower($c->surname), $search_term) !== false) {
                $results->push($c);
            } elseif (strpos(strtolower($c->email_address), $search_term) !== false) {
                $results->push($c);
            } elseif (strpos(strtolower($c->mobile_number), $search_term) !== false) {
                $results->push($c);
            } elseif (strpos(strtolower($c->barcode), $search_term) !== false) {
                $results->push($c);
            } elseif (strpos(strtolower($c->street1), $search_term) !== false) {
                $results->push($c);
            } elseif (strpos(strtolower($c->street2), $search_term) !== false) {
                $results->push($c);
            } elseif (strpos(strtolower($c->town), $search_term) !== false) {
                $results->push($c);
            } elseif (strpos(strtolower($c->county), $search_term) !== false) {
                $results->push($c);
            } elseif (strpos(strtolower($c->nominated_telephone), $search_term) !== false) {
                $results->push($c);
            }

            //echo $results->first()->id . " | " . $search_term;
            //die();
        }

        /*
        $results = Customer::where('scheme_number', '=', Auth::user()->scheme_number)
            ->where(function($query)
            {
                $query->orwhere('username', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('first_name', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('barcode', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('surname', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street1', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('street2', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('town', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('county', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('nominated_telephone', 'like', '%'.Input::get('search_box').'%');
            })
            ->get();
        */

        /*

        ->orWhere('email_address', 'like', '%'.Input::get('search_box').'%')
                        ->orWhere('mobile_number', 'like', '%'.Input::get('search_box').'%')
                        */
        $this->layout->page = view('home/search', ['customers' => $results]);
    }

    public function usageFromBillingEngineLogs($customer, $date)
    {
        if (strpos($date, '_') !== false) {
            $date = str_replace('_', '-', $date);
        }

        $parts = explode('-', $date);
        $year = $parts[0];
        $month = $parts[1];
        $day = $parts[2];
        $date = $year.'_'.$month.'_'.$day;

        $filename = "/opt/prepago_engine/prepago_engine/logs/$year/$month/billing_engine/$date.txt";
        $entry = (object) [
            'billed' => 0,
            'usage' => 0,
            'date' => '',
            'standing_charge' => '',
            'residual_yesterday_charge' => 0,
            'charges' => [],
        ];

        $today_d = new DateTime($year.'-'.$month.'-'.$day);
        $tommorow_d = new DateTime($today_d->format('Y-m-d').' + 1 day');

        $year_t = $tommorow_d->format('Y');
        $month_t = $tommorow_d->format('m');
        $day_t = $tommorow_d->format('d');
        $date_t = $year_t.'_'.$month_t.'_'.$day_t;

        $filename_tommorow = "/opt/prepago_engine/prepago_engine/logs/$year_t/$month_t/billing_engine/$date_t.txt";

        $scheme_id = Customer::where('id', $customer)->first()->scheme_number;
        $tariff_1 = Tariff::where('scheme_number', $scheme_id)->first();

        if ($tariff_1) {
            $tariff_1 = $tariff_1->tariff_1;
        }

        if (file_exists($filename_tommorow)) {
            foreach (file($filename_tommorow) as $line) {
                $c1 = 'Customer '.$customer.' Old Balance';
                $c2 = 'Customer '.$customer.' billed';

                if (strpos($line, $c1) === false) {
                    if (strpos($line, $c2) === false || strpos($line, 'daily tariff') === false) {
                        continue;
                    } else {
                        $end_bill = floatval(explode(' ', $line)[3]);
                        //$entry->billed += $end_bill;
                        $entry->usage += ($end_bill / $tariff_1);
                        //echo $entry->usage;

                        $charge1 = (object) [
                            'type' => 'residual_prev_day',
                            'amount' => $end_bill,
                            'kwh' => ($end_bill / $tariff_1),
                            'old_balance' => '',
                            'new_balance' => '',
                        ];

                        array_push($entry->charges, $charge1);
                    }

                    continue;
                }
            }
        }

        $entry->date = $year.'-'.$month.'-'.$day;

        if (! file_exists($filename)) {
            return $entry;
        }

        foreach (file($filename) as $line) {
            if (strpos(strtolower($line), 'error')) {
                $line = "<font color='red'>$line</font>";
            }

            $c1 = 'Customer '.$customer.' Old Balance';
            $c2 = 'Customer '.$customer.' billed';

            if (strpos($line, $c1) === false) {
                if (strpos($line, $c2) === false || strpos($line, 'daily tariff') === false) {
                    continue;
                } else {
                    $parts_1 = explode(' ', $line);
                    $billed = 0;
                    $standing = floatval($parts_1[7]);
                    $entry->standing_charge = $standing;
                    $entry->residual_yesterday_charge = floatval($parts_1[3]);
                    $charge = (object) [
                        'type' => 'standing_charge',
                        'amount' => $standing,
                        'kwh' => '',
                        'old_balance' => '',
                        'new_balance' => '',
                    ];

                    array_push($entry->charges, $charge);

                    //$entry->billed += $parts_1[3];
                }

                continue;
            }

            $parts = explode(' ', $line);
            $old_balance = floatval($parts[7]);
            $new_balance = floatval($parts[11]);
            $the_usage = floatval($parts[14]);
            $billed = $old_balance - $new_balance;
            $entry->billed += $billed;
            $entry->usage += $the_usage;

            $charge = (object) [
                'type' => 'general_kwh',
                'amount' => $billed,
                'kwh' => $the_usage,
                'old_balance' => $old_balance,
                'new_balance' => $new_balance,
            ];

            array_push($entry->charges, $charge);
        }

        /*
        foreach($entry->charges as $e)
        {
            $e = (object)$e;
            echo $date . '|' . $e->type . '|' . $e->amount . '<br/>';
        }*/

        return $entry;
    }

    public function getAdvancedDistrictUsageResidualCost($d_usage)
    {
        $residual_charge = 0;
        $today_d = new DateTime($d_usage->date);
        $year_t = $today_d->format('Y');
        $month_t = $today_d->format('m');
        $day_t = $today_d->format('d');
        $date_t = $year_t.'_'.$month_t.'_'.$day_t;

        $filename = "/opt/prepago_engine/prepago_engine/logs/$year_t/$month_t/billing_engine/$date_t.txt";

        $customer_i = Customer::find($d_usage->customer_id);

        if (! $customer_i) {
            return (object) ['residual_cost' => $residual_charge, 'tariff_1' => '0'];
        }

        $customer = $customer_i->id;

        if (! file_exists($filename)) {
            return (object) ['residual_cost' => $residual_charge, 'tariff_1' => '0'];
        }

        $tariff_1 = Tariff::where('scheme_number', $customer_i->scheme_number)->first()->tariff_1;

        foreach (file($filename) as $line) {
            if (strpos(strtolower($line), 'error')) {
                continue;
            }

            $c1 = 'Customer '.$customer.' Old Balance';
            $c2 = 'Customer '.$customer.' billed';

            if (strpos($line, $c1) === false) {
                if (strpos($line, $c2) === false || strpos($line, 'daily tariff') === false) {
                    continue;
                } else {
                    $parts_1 = explode(' ', $line);
                    $residual_charge = floatval($parts_1[3]);

                    return (object) ['residual_cost' => $residual_charge, 'tariff_1' => $tariff_1];
                }
            }
        }

        return (object) ['residual_cost' => $residual_charge, 'tariff_1' => $tariff_1];
    }

    public function getAdvancedDistrictUsage($customer_id, $to, $from)
    {
        $customer = Customer::find($customer_id);
        $d_usages = DistrictHeatingUsageAdvanced::where('customer_id', '=', $customer_id)->where('date', '>=', $from)->where('date', '<=', $to)->orderby('date', 'asc')->where('first_reading', '>', 0)->where('last_reading', '>', 0)->get();

        foreach ($d_usages as $d_usage) {
            $d_usage->residual_cost = $this->getAdvancedDistrictUsageResidualCost($d_usage)->residual_cost;
            $cal_total_sms = $customer->allSMS($d_usage->date, true);
            $cal_total_topup = $customer->allTopups($d_usage->date, true);

            if ($d_usage->total_topup < $cal_total_topup || $d_usage->total_sms < $cal_total_sms) {
                $d_usage->total_topup = $cal_total_topup;
                $d_usage->total_sms = $cal_total_sms;
                $d_usage->total_balance_deducted += ($d_usage->total_topup + $d_usage->total_sms);
            }
            //$per_kwh = $this->getAdvancedDistrictUsageResidualCost($d_usage)->tariff_1;
            //$d_usage->total_kwh_used += ($residual_cost/$per_kwh);
        }

        return $d_usages;
    }

    public function usageTotals($customer_id, $to, $from)
    {
        $ret = [
            'start_date' 			=> $from,
            'end_date'				=> $to,
            'total_usage'			=> 0,
            'avg_daily_usage'		=> 0,
            'start_reading'			=> 0,
            'end_reading'			=> 0,
            'total_cost'			=> 0,
            'avg_daily_cost'		=> 0,
            'unit_charge'			=> 0,
            'standing_charge'		=> 0,
            'arrears_repayment'		=> 0,
            'other_charges'			=> 0,
        ];

        $customer = Customer::find($customer_id);
        if ($customer) {
            $usage = $customer->districtHeatingUsage(['from' => $from, 'to' => $to]);

            foreach ($usage as $key => $value) {
                $ret['total_usage'] += $value->total_usage;
                $ret['avg_daily_usage'] = number_format($ret['total_usage'] / count($usage), 2, '.', '');
                $ret['start_reading'] = ($ret['start_reading'] == 0) ? $value->start_day_reading : $ret['start_reading'];
                $ret['end_reading'] = ($value->end_day_reading == 0) ? $ret['end_reading'] : $value->end_day_reading;
                $ret['total_cost'] += $value->cost_of_day;
                $ret['avg_daily_cost'] = number_format($ret['total_cost'] / count($usage), 2, '.', '');
                $ret['unit_charge'] += $value->unit_charge;
                $ret['standing_charge'] += $value->standing_charge;
                $ret['arrears_repayment'] += $value->arrears_repayment;
                $ret['other_charges'] += (($value->cost_of_day) - ($value->unit_charge + $value->standing_charge + $value->arrears_repayment));
            }
        }

        return (object) $ret;
    }

    public function getStartReading($date, $customer_id)
    {
        try {
            $today_date_time = new DateTime($date);
            $yesterday_date_time = new DateTime($today_date_time->format('Y-m-d').' - 1 day');
            $date = $yesterday_date_time->format('Y-m-d');
            $yesterday_district = DistrictHeatingUsage::where('customer_id', $customer_id)->where('date', $date)->first();

            // this is a messed up day too
            if ($yesterday_district->end_day_reading == 0) {
                $yesterday_start_reading = $this->getStartReading($yesterday_date_time->format('Y-m-d'), $customer_id);
                $yesterday_usage = DistrictHeatingUsage::getUsage($customer_id, $yesterday_date_time->format('Y-m-d'));
                $projected_end_reading = $yesterday_start_reading + $yesterday_usage;

                return $projected_end_reading;
            } else {
                return $yesterday_district->end_day_reading;
            }
        } catch (Exception $e) {
            echo 'failed: '.$e->getMessage();
        }
    }

    public static function insertMissingDistrictUsage($customer_id, $district_heating_usage)
    {
        foreach ($district_heating_usage as $dhu) {
            $defaultStandingCharge = $dhu->defaultStandingCharge;
            $today = new DateTime($dhu->date);
            $tommorow = new DateTime($today->format('Y-m-d').' + 1 day');
            $start_day_tommorow = DistrictHeatingUsage::where('customer_id', $customer_id)->where('date', $tommorow->format('Y-m-d'))->first(['start_day_reading', 'end_day_reading']);
            if ($start_day_tommorow) {
                $start_day_tommorow_reading = $start_day_tommorow->start_day_reading;
            } else {
                continue;
            }

            $yesterday = new DateTime($today->format('Y-m-d').' - 1 day');
            $end_day_yesterday = DistrictHeatingUsage::where('customer_id', $customer_id)->where('date', $yesterday->format('Y-m-d'))->first(['end_day_reading', 'start_day_reading']);

            if ($dhu->end_day_reading <= 0) {
                $dhu->end_day_reading = $start_day_tommorow->start_day_reading;
                if ($dhu->end_day_reading <= 0) {
                    $dhu->end_day_reading = $start_day_tommorow->end_day_reading;
                }

                //echo $dhu->date . ": " . $dhu->end_day_reading . "\n";
                $dhu->total_usage = abs($dhu->end_day_reading - $dhu->start_day_reading);
            }

            if ($dhu->start_day_reading <= 0 && $end_day_yesterday) {
                $end_day_yesterday = $end_day_yesterday->end_day_reading;
                $dhu->start_day_reading = $end_day_yesterday;
                $dhu->total_usage = $dhu->end_day_reading - $dhu->start_day_reading;

                if ($dhu->start_day_reading <= 0) {
                    $dhu->start_day_reading = $dhu->end_day_reading;
                    $dhu->total_usage = $dhu->end_day_reading - $dhu->start_day_reading;
                    $dhu->unit_charge = 0;
                    if ($dhu->unit_charge <= 0 || $dhu->unit_charge > 70) {
                        $scheme = Scheme::where('scheme_number', $dhu->scheme_number)->first();
                        $tariff = $scheme->tariff;
                        if ($scheme && $tariff) {
                            $dhu->unit_charge = $dhu->total_usage * $tariff->tariff_1;
                        }
                    }
                    $dhu->cost_of_day = $dhu->standing_charge + $dhu->unit_charge + $dhu->arrears_repayment;
                }
            }

            if ($dhu->standing_charge == 0) {
                $dhu->cost_of_day -= $defaultStandingCharge;
                $dhu->standing_charge = 0;
            }

            if ($dhu->cost_of_day <= 0 || $dhu->total_usage <= 0) {
                if ($dhu->unit_charge <= 0 || $dhu->unit_charge > 70) {
                    $scheme = Scheme::where('scheme_number', $dhu->scheme_number)->first();
                    $tariff = $scheme->tariff;
                    if ($scheme && $tariff) {
                        $dhu->unit_charge = $dhu->total_usage * $tariff->tariff_1;
                    }
                }

                $dhu->cost_of_day = $dhu->standing_charge + $dhu->unit_charge + $dhu->arrears_repayment;
            }

            //$dhu->save();
        }

        return $district_heating_usage;
    }

    public function customer_view_shortcut($customer_id)
    {
        return redirect("customer_tabview_controller/show/$customer_id");
    }

    private function customerError($customer_id, $custom = null)
    {
        $customerInfo = null;
        $dhmInfo = null;

        if (preg_match('/[a-z]/i', strtolower($customer_id))) {
            $customerInfo = DB::table('customers')->where('username', $customer_id)->orderBy('deleted_at', 'DESC')->first();
        } else {
            $customerInfo = DB::table('customers')->where('id', $customer_id)->orderBy('deleted_at', 'DESC')->first();
        }

        if ($customerInfo) {
            $dhmInfo = DB::table('district_heating_meters')->where('meter_ID', $customerInfo->meter_ID)->first();
        } else {
            $customerInfo = null;
        }

        $this->layout->page = view('error/customer_404', [
            'customer_id' => $customer_id,
            'customerInfo' => $customerInfo,
            'dhmInfo' => $dhmInfo,
            'custom' => $custom,
        ]);
    }

    public function customer_view($customer_id)
    {
        $customer = Customer::join('district_heating_meters', 'customers.meter_ID', '=', 'district_heating_meters.meter_ID')
                    ->select('customers.*', 'customers.scheme_number as c_scheme_number', 'district_heating_meters.*')
                    ->where('id', '=', $customer_id)
                    ->first();

        if (preg_match('/[a-z]/i', strtolower($customer_id))) {
            $customer = Customer::join('district_heating_meters', 'customers.meter_ID', '=', 'district_heating_meters.meter_ID')
            ->select('customers.*', 'customers.scheme_number as c_scheme_number', 'district_heating_meters.*')
            ->where('username', '=', $customer_id)
            ->first();
            if ($customer) {
                $customer_id = $customer->id;
            }
        }

        if (! $customer) {
            return $this->customerError($customer_id);
        }

        try {
            if ($customer && $customer->simulator > 0) {
                $username = str_replace('_test', '', $customer->username);
                //echo $username;
                $original = Customer::where('username', $username)->first();
                if ($original) {
                    $customer_id = $original->id;
                }
            }

            if (isset($_GET['testing']) || Auth::user()->isUserTest()) {
                Session::put('scheme_number', $customer->c_scheme_number);
            } else {
                if ($customer['c_scheme_number'] != Auth::user()->scheme_number) {
                    return redirect('customer_search');
                }
            }

            $c_data = $customer;

            /*
            if ($c_data['shut_off'] == 0) {
                $c_data['shut_off'] = 'Device On';
            } else {
                $c_data['shut_off'] = 'Device Off';
            }*/

            if ($c_data['credit_warning_sent'] == 1) {
                $c_data['credit_warning_sent'] = 'Yes';
            } else {
                $c_data['credit_warning_sent'] = 'No';
            }

            if ($c_data['IOU_available'] == 1) {
                $c_data['iou_statas'] = 'Available';
            } else {
                $c_data['iou_statas'] = 'Unavailable';
            }
            if ($c_data['IOU_used'] == 1) {
                $c_data['iou_statas'] = $c_data['iou_statas'].' & '.'Used';
            } else {
                $c_data['iou_statas'] = $c_data['iou_statas'].' & '.'Not used';
            }
            //IOU extra
            if ($c_data['IOU_extra_available'] == 1) {
                $c_data['iou_extra_statas'] = 'Available';
            } else {
                $c_data['iou_extra_statas'] = 'Unavailable';
            }
            if ($c_data['IOU_extra_used'] == 1) {
                $c_data['iou_extra_statas'] = $c_data['iou_extra_statas'].' & '.'Used';
            } else {
                $c_data['iou_extra_statas'] = $c_data['iou_extra_statas'].' & '.'Not used';
            }

            //Admin IOU
            if ($c_data['admin_IOU_in_use'] == 1) {
                $c_data['admin_IOU_in_use'] = 'Used';
            } else {
                $c_data['admin_IOU_in_use'] = 'Not used';
            }

            if ($c_data['shut_off_device_status'] == 1) {
                $c_data['shut_off_device_status'] = 'Yes';
            } else {
                $c_data['shut_off_device_status'] = 'No';
            }

            if (Request::isMethod('post')) {
                $c_data['home'] = '';
                $c_data['message'] = 'active';
                $to = date('Y-m-d', strtotime(Input::get('to')));
                $from = date('Y-m-d', strtotime(Input::get('from')));
                $today = date('Y-m-d');
                if ($to >= $today) {
                    // $to = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
                }
            } else {
                $to = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 0, date('Y')));
                $from = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 31, date('Y')));
            }

            $tariff = Tariff::where('scheme_number', $c_data['scheme_number'])->first();
            if (! $tariff) {
                throw new Exception('There is not tariff set for this scheme');
            }
            $c_data['kWh_usage_tariff'] = $tariff->tariff_1;

            $district_heating_usage = DistrictHeatingUsage::where('customer_id', '=', $customer_id)->where('date', '>=', $from)->where('date', '<=', $to)
            ->orderby('date', 'asc')->groupBy('date')->get();

            $entries = [];
            $date = null;
            $year = date('Y');
            $month = date('m');
            $day = date('d');

            if ((Session::has('search_date'))) {
                //echo Session::get('search_date');
                //die();
                $date = Session::get('search_date');
            }

            if ($date != null) {
                $parts = explode('-', $date);
                $year = $parts[2];
                $month = $parts[1];
                $day = $parts[0];
            }

            $date = $year.'_'.$month.'_'.$day;

            $entries = $this->usageFromBillingEngineLogs($customer_id, $date)->charges;
            $c_data['sms'] = SMSMessage::where('customer_id', $customer_id);

            $today_sms = $c_data['sms']->where('date_time', 'like', '%'.str_replace('_', '-', $date).'%')->get();

            foreach ($today_sms as $t_sms) {
                $charge = (object) [
                    'type' => 'sms',
                    'amount' => $t_sms->charge,
                    'message' => $t_sms->message,
                ];
                array_push($entries, $charge);
            }

            $c_data['array'] = self::insertMissingDistrictUsage($customer_id, $district_heating_usage);

            if (Request::isMethod('post')) {
                $this->populate_c_data($c_data, 'date_search_action');
            } else {
                $this->populate_c_data($c_data);
            }

            $arrear_data = CustomerArrears::where('customer_id', $customer_id)->orderby('date', 'desc')->get();
            $current_arrear = Customer::where('id', '=', $customer_id)->get(['arrears', 'arrears_daily_repayment'])->first();

            $current_arrear_data['arrears'] = $current_arrear['arrears'];
            $current_arrear_data['arrears_daily_repayment'] = $current_arrear['arrears_daily_repayment'];

            $c_data['home'] = 'active';
            $c_data['message'] = '';

            $scheme = Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->get()->first();
            $currency = $scheme['currency_sign'];

            $c_data['sms_cost'] = $scheme['prepage_SMS_charge'];
            $abbreviation = $scheme['unit_abbreviation'];
            $schemeName = $scheme['scheme_nickname'] ?: $scheme['company_name'];
            $rsCodes = PermanentMeterData::isEV()->where('scheme_number', Auth::user()->scheme_number)->lists('ev_rs_code');

            $dates['to'] = $to;
            $dates['from'] = $from;

            //Top Up tab info
            $credit_list = [];
            $credit_list[0]['id'] = $customer->id;
            $credit_list[0]['email'] = $customer->email_address;
            Session::put('issue_credit_credit_list', $credit_list);
            Session::put('issue_admin_iou_credit_list', $credit_list);
            Session::put('issue_topup_arrears_credit_list', $credit_list);

            //Send Message tab info
            $sms_list = [];
            $sms_list[0]['id'] = $customer->id;
            $sms_list[0]['email'] = $customer->username;
            Session::put('sms_list', $sms_list);
            $smsList = DB::table('customers')
                        ->join('sms_messages', 'customers.id', '=', 'sms_messages.customer_id')
                        ->where('customers.scheme_number', '=', $customer->c_scheme_number)
                        ->where('customers.id', '=', $customer_id)
                        ->orderby('date_time', 'desc')
                        ->get();
            $notifications = InAppNotification::where('customer_id', $customer->id)
            ->orWhere('all_schemes', 1)->get();

            //Utility Notes tab info
            $utilityNotesList = UtilityNote::where('scheme_number', $customer->c_scheme_number)->where('customer_id', $customer_id)->get();

            //IOU Usage tab info
            $iouUsageList = IOUStorage::where('scheme_number', $customer->c_scheme_number)->where('customer_id', $customer_id)->get();

            //Top Ups tab info
            $customerModel = Customer::find($customer_id);
            if (Session::get('action') === 'topups_dates_search') {
                $topupTo = Session::get('dates')['to'];
                $topupFrom = Session::get('dates')['from'];

                $topupHistory = $customerModel->paymentsStorage()->where('time_date', '>=', $topupFrom)->where('time_date', '<=', $topupTo)->orderby('time_date', 'desc')->get();
                $pendingTopups = $customerModel->temporaryPayments()->where('time_date', '>=', $topupFrom)->where('time_date', '<=', $topupTo)->orderby('time_date', 'desc')->get();
                $adminTopups = $customerModel->adminIssuedCredit()->where('date_time', '>=', $topupFrom)->where('date_time', '<=', $topupTo)->orderby('date_time', 'desc')->get();
                $adminDeductions = $customerModel->adminDeductedCredit()->where('date_time', '>=', $topupFrom)->where('date_time', '<=', $topupTo)->orderby('date_time', 'desc')->get();

                $allTopups = array_merge($topupHistory->toArray(), $pendingTopups->toArray());
                usort($allTopups, function ($item1, $item2) {
                    return $item2['time_date'] >= $item1['time_date'];
                });
            } else {
                $topupHistory = $customerModel->paymentsStorage()->orderby('time_date', 'desc')->get();
                $pendingTopups = $customerModel->temporaryPayments()->orderby('time_date', 'desc')->get();
                $adminTopups = $customerModel->adminIssuedCredit()->orderby('date_time', 'desc')->get();
                $adminDeductions = $customerModel->adminDeductedCredit()->orderby('date_time', 'desc')->get();

                $allTopups = array_merge($topupHistory->toArray(), $pendingTopups->toArray());
                usort($allTopups, function ($item1, $item2) {
                    return $item2['time_date'] >= $item1['time_date'];
                });
            }

            $awayMode = RemoteControlStatus::where('permanent_meter_id', $customer->permanent_meter_ID)->pluck('away_mode_on');

            $iouAvailable = false;
            if ($customer->IOU_available == 1 && $customer->IOU_used != 1) {
                $iouAvailable = true;
            } elseif ($customer->IOU_used == 1 && $customer->IOU_extra_available == 1 && $customer->IOU_extra_used == 0) {
                $iouAvailable = true;
            }

            // diagnostics tab - "m" scu type buttons
            $permanentMeter = Customer::find($c_data['id'])->permanentMeter();
            $pm_scheme = null;
            if ($permanentMeter) {
                $c_data['permanent_meter'] = $permanentMeter;
                $c_data['permanent_meter_id'] = $permanentMeter->ID;
                $pm_scheme = Scheme::where('id', $customer->scheme_number)->first();

                if ($pm_scheme) {
                    $c_data['scheme_number'] = $pm_scheme->id;
                    $c_data['company_name'] = $pm_scheme->company_name;
                }

                $c_data['scu_number'] = $permanentMeter->scu_number;

                $pm_tariff = Tariff::where('scheme_number', $c_data['scheme_number'])->first();

                if ($pm_tariff) {
                    $c_data['tariff_1'] = $pm_tariff->tariff_1;
                    $c_data['tariff_2'] = $pm_tariff->tariff_2;
                }

                $c_data['scheme_ip'] = 'Unable to find.';
            }

            $meter_num = (strpos($c_data['meter_number'], '_') !== false) ? explode('_', $c_data['meter_number'])[1] : $c_data['meter_number'];
            $mbus_translations = MBusAddressTranslation::where('8digit', $c_data['scu_number'])->orWhere('16digit', 'like', '%'.$c_data['scu_number'].'%')->first();
            $meter_translations = MBusAddressTranslation::where('8digit', $meter_num)->orWhere('16digit', 'like', '%'.$meter_num.'%')->first();
            $meter_type = null;
            $meter_types = MeterLookup::orderBy('id', 'ASC')->get();

            if ($mbus_translations) {
                $c_data['scu_number'] = $mbus_translations['8digit'];
                $c_data['scu_number_sixteen'] = $mbus_translations['16digit'];
            }
            if ($meter_translations) {
                $c_data['meter_number'] = $meter_translations['8digit'];
                $c_data['meter_number_sixteen'] = $meter_translations['16digit'];

                if (strlen($c_data['meter_number_sixteen']) >= 16) {
                    $c_data['meter_last_8'] = substr($c_data['meter_number_sixteen'], 8, 16);
                } else {
                    $c_data['meter_last_8'] = 'n/a';
                }

                $meter_type = MeterLookup::where('last_eight', $c_data['meter_last_8'])->orderBy('id', 'ASC')->first();
            }

            $pm_datalog = [];
            if ($pm_scheme) {
                $pm_datalog = DataLogger::where('scheme_number', $pm_scheme->id)->first();
                if ($pm_datalog) {
                    $pm_sim = Simcard::where('ID', $pm_datalog->sim_id)->first();
                    if ($pm_sim) {
                        $c_data['scheme_ip'] = $pm_sim->IP_Address;
                    }
                }
            }

            $permanentMeterScuType = $permanentMeter ? $permanentMeter->scu_type : '';
            $districtHeatingMeter = null;

            if ($permanentMeter) {
                $c_data['customer_id'] = $customer->id;
                $c_data['pmd_id'] = $permanentMeter->ID;
                $districtHeatingMeter = DistrictHeatingMeter::where('permanent_meter_ID', $permanentMeter->ID)->first();
                $c_data['d_id'] = $districtHeatingMeter->meter_ID;
                $c_data['d_meter_number'] = $districtHeatingMeter->meter_number;
                $c_data['d_usage'] = DistrictHeatingUsage::where('customer_id', $customer->id)->count();
            }

            $evMeter = null;
            if ($c_data['ev_meter_ID']) {
                $evMeter = $customerModel->EVDistrictHeatingMeter->toArray();

                if ($evMeter['shut_off_device_status'] == 1) {
                    $evMeter['shut_off_device_status'] = 'Yes';
                } else {
                    $evMeter['shut_off_device_status'] = 'No';
                }
            }

            $usageTotals = $this->usageTotals($customer_id, $to, $from);
            $enhancedUsage = $customer->enhancedUsage(['from' => $from, 'to' => $to]);

            if (Session::get('action') == 'readings_dates_search') {
                $readingsDates = [];

                $readingsDates['to'] = (new DateTime(Session::get('readingsDates')['to']))->format('d-m-Y');
                $readingsDates['from'] = (new DateTime(Session::get('readingsDates')['from']))->format('d-m-Y');
            } else {
                $readingsDates = [];
                $readingsDates['to'] = date('d-m-Y');
                $readingsDates['from'] = (new DateTime($readingsDates['to']))->modify('-7 day')->format('d-m-Y');
            }

            if ($customer->permanentMeter) {
                $c_data['permanentMeter'] = $customer->permanentMeter;
            }

            if ($customer->districtMeter) {
                $c_data['districtMeter'] = $customer->districtMeter;
            }

            $onCommands = [];
            $offCommands = [];
            $lastCommand = null;
            if ($districtHeatingMeter) {
                $onCommands = $districtHeatingMeter->onCommands;
                $offCommands = $districtHeatingMeter->offCommands;
                $lastCommand = $districtHeatingMeter->lastCommand;
            }

            $c_data['address'] = $customer->address;
            $c_data['address_formatted'] = $customer->addressFormatted;

            if (! Session::get('dates')) {
                $topupDates['from'] = $dates['from'];
                $topupDates['to'] = $dates['to'];
                $balanceInfo['from_bal'] = $dates['from'];
                $balanceInfo['to_bal'] = $dates['to'];
            } else {
                $topupDates['from'] = Session::get('dates')['from'];
                $topupDates['to'] = Session::get('dates')['to'];
                $balanceInfo['from_bal'] = Session::get('dates')['from'];
                $balanceInfo['to_bal'] = Session::get('dates')['to'];
            }

            // $balanceInfo['balances'] =  CustomerBalanceChange::where('customer_id', $c_data['id'])
            // ->whereRaw("(date >= '" . ($balanceInfo['from_bal']) . "' AND date <= '" . ($balanceInfo['to_bal']) . "')")->get();
            $balanceInfo['balances'] = [];

            $lastLoginInfo = TrackingCustomerActivity::where('customer_id', $c_data['id'])->orderBy('id', 'DESC')
            ->first();
        } catch (Exception $e) {
            return $this->customerError($customer_id, $e->getMessage());
        }

        $this->layout->page = view('home/customer_tab_view', [
            'dates'             => $dates,
            'currency'          => $currency,
            'currencySign'      => $currency,
            'abbreviation'      => $abbreviation,
            'scheme_name'       => $schemeName,
            'rs_codes'          => $rsCodes,
            'data'              => $c_data,
            'arrears'           => $arrear_data,
            'current_arrears'   => $current_arrear_data,
            'smsList'           => $smsList,
            'notifications'     => $notifications,
            'utilityNotesList'  => $utilityNotesList,
            'iouUsageList'      => $iouUsageList,
            'iouAvailable'      => $iouAvailable,
            'topupHistory'      => $topupHistory,
            'pendingTopups'     => $pendingTopups,
            'adminTopups'       => $adminTopups,
            'adminDeductions'   => $adminDeductions,
            'allTopups'			=> $allTopups,
            'awayMode'         	=> $awayMode,
            'pm_scu_type'       => $permanentMeterScuType,
            'evMeter'           => $evMeter,
            'entries'			=> $entries,
            'date'				=> $date,
            'usageTotals'		=> $usageTotals,
            'dataLogger'		=> $pm_datalog,
            'onCommands'		=> $onCommands,
            'offCommands'		=> $offCommands,
            'lastCommand'		=> $lastCommand,
            'readingsDates'		=> $readingsDates,
            'enhancedUsage'		=> $enhancedUsage,
            'balanceInfo'		=> $balanceInfo,
            'topupDates'		=> $topupDates,
            'lastLoginInfo'		=> $lastLoginInfo,
            'meter_type'		=> $meter_type,
            'meter_types'		=> $meter_types,
        ]);
    }

    public function dailyChargesSearch($customer_id)
    {
        $date = Input::get('on');

        return redirect('customer_tabview_controller/show/'.$customer_id)->with('search_date', $date);
    }

    public function topupsDateSearch($customerID)
    {
        $dates = [];
        $dates['to'] = date('Y-m-d', strtotime(Input::get('to')));
        $dates['from'] = date('Y-m-d', strtotime(Input::get('from')));
        $today = date('Y-m-d');
        if ($dates['to'] >= $today) {
            $dates['to'] = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')));
        }

        $from_bal = $dates['from'];
        $to_bal = $dates['to'];

        $balanceChanges = CustomerBalanceChange::where('customer_id', $customerID)
        ->whereRaw("(start_time >= '".($from_bal.' 00:00:00')."' AND end_time <= '".($to_bal.' 23:59:59')."')")->get();

        $balanceInfo['from_bal'] = $from_bal;
        $balanceInfo['to_bal'] = $to_bal;
        $balanceInfo['search'] = true;
        // $balanceInfo['balances'] =  CustomerBalanceChange::where('customer_id', $customerID)
        // ->whereRaw("(start_time >= '" . ($balanceInfo['from_bal'] . " 00:00:00") . "' AND end_time <= '" . ($balanceInfo['to_bal'] . " 23:59:59") . "')")->get();
        $balanceInfo['search'] = [];

        return redirect('customer_tabview_controller/show/'.$customerID)->with('action', 'topups_dates_search')->with('dates', $dates)->with('balanceInfo', $balanceInfo);
    }

    public function readingsDateSearch($customerID)
    {
        $readingsDates = [];
        $readingsDates['to'] = date('Y-m-d', strtotime(Input::get('to')));
        $readingsDates['from'] = date('Y-m-d', strtotime(Input::get('from')));
        $today = date('Y-m-d');
        if ($readingsDates['to'] > $today) {
            $readingsDates['to'] = $today;
        }

        return redirect('customer_tabview_controller/show/'.$customerID)->with('action', 'readings_dates_search')->with('readingsDates', $readingsDates);
    }

    public function getReadings($customerID, $from = null, $to = null)
    {
        if ($from == null) {
            $from = (new DateTime(Input::get('from')))->format('Y-m-d');
        }

        if ($to == null) {
            $to = (new DateTime(Input::get('to')))->format('Y-m-d');
        }

        $customer = Customer::find($customerID);

        if (! $customer) {
            Redirect::back();
        }

        $meterID = $customer->meter_ID;

        $dhm = DistrictHeatingMeter::where('meter_ID', $meterID)->first();

        if (! $dhm) {
            Redirect::back();
        }

        $pmID = $dhm->permanent_meter_ID;

        $startingDate = $from;

        $readings = [];
        $i = 0;

        while ($startingDate != (new DateTime($to.' + 1 day'))->format('Y-m-d')) {
            $lastDaysReading = PermanentMeterDataReadingsAll::where('permanent_meter_id', $pmID)
            ->orderBy('ID', 'DESC')
            ->whereRaw("CAST(time_date AS DATE) = '".$startingDate."'")
            ->first();

            if ($lastDaysReading) {
                $readings["set_$i"]['startDayReadings'] = $lastDaysReading;
            }

            $startingDate = (new DateTime($startingDate.' + 1 day'))->format('Y-m-d');
            $i++;
        }

        return $readings;
    }

    public function meterView($meterID)
    {
        $meterData = DistrictHeatingMeter::where('permanent_meter_ID', $meterID)->first();
        if ($meterData) {
            if ($meterData->shut_off_device_status == 1) {
                $meterData->shut_off_device_status = 'Yes';
            } else {
                $meterData->shut_off_device_status = 'No';
            }
        }

        $this->layout->page = view('home/boiler_meter', ['meter_id' => $meterID, 'meter' => $meterData]);
    }

    /*public function edit_utility_action($customer_id)
    {
        $textarea = Input::get('t_area');

        Customer::where('id', '=', $customer_id)->update(array('utility_notes' => $textarea));

        return redirect('customer_tabview_controller/show/'.$customer_id);
    }*/

    public function addUtilityNote($customer_id)
    {
        $notes = Input::get('t_area');
        $customer = Customer::join('district_heating_meters', 'customers.meter_ID', '=', 'district_heating_meters.meter_ID')->where('id', '=', $customer_id)->first();
        $utilityNotesData = [
            'customer_id' => $customer_id,
            'scheme_number' => $customer['scheme_number'],
            'date_time' => date('Y-m-d H:i:s'),
            'notes' => $notes,
        ];

        if (! UtilityNote::create($utilityNotesData)) {
            return redirect('customer_tabview_controller/show/'.$customer_id)->with('errorMessage', 'The Utility Note information was not inserted successfully.');
        }

        return redirect('customer_tabview_controller/show/'.$customer_id)->with('successMessage', 'The Utility note information was inserted successfully.');
    }

    public function deleteUtilityNote($customerID)
    {
        $utilityNoteID = (int) Input::get('utility_note_id');

        if (! UtilityNote::find($utilityNoteID)->delete()) {
            return redirect('customer_tabview_controller/show/'.$customerID)->with('errorMessage', 'The Utility Note information cannot be deleted.');
        }

        return redirect('customer_tabview_controller/show/'.$customerID)->with('successMessage', 'The Utility note information was deleted successfully.');
    }

    public function edit_common_action($customer_id)
    {
        $customer = Customer::find($customer_id);

        if (! $customer) {
            return redirect('customer_tabview_controller/show/'.$customer_id);
        }

        $number = Input::get('formid');

        if ($number == 2) {
            $customer->username = Input::get('t_area');
        } elseif ($number == 3) {
            $customer->house_number_name = Input::get('t_area');
        } elseif ($number == 4) {
            $customer->street1 = Input::get('t_area');
            $pmd = $customer->permanentMeter;
            if ($pmd) {
                $pmd->street1 = Input::get('t_area');
                $pmd->save();
            }
        } elseif ($number == 5) {
            $customer->street2 = Input::get('t_area');
            $pmd = $customer->permanentMeter;
            if ($pmd) {
                $pmd->street2 = Input::get('t_area');
                $pmd->save();
            }
        } elseif ($number == 6) {
            $customer->county = Input::get('t_area');
            $pmd = $customer->permanentMeter;
            if ($pmd) {
                $pmd->county = Input::get('t_area');
                $pmd->save();
            }
        } elseif ($number == 7) {
            $customer->country = Input::get('t_area');
            $pmd = $customer->permanentMeter;
            if ($pmd) {
                $pmd->country = Input::get('t_area');
                $pmd->save();
            }
        } elseif ($number == 8) {
            $customer->email_address = Input::get('t_area');
        } elseif ($number == 9) {
            $customer->mobile_number = Input::get('t_area');
        } elseif ($number == 10) {
            $customer->postcode = Input::get('t_area');
            $pmd = $customer->permanentMeter;
            if ($pmd) {
                $pmd->postcode = Input::get('t_area');
                $pmd->save();
            }
        } elseif ($number == 11) {
            $customer->town = Input::get('t_area');
            $pmd = $customer->permanentMeter;
            if ($pmd) {
                $pmd->town = Input::get('t_area');
                $pmd->save();
            }
        } elseif ($number == 20) {
            $customer->first_name = Input::get('first_name');
            $customer->surname = Input::get('surname');
        // if ( ! Input::get('first_name') || ! Input::get('surname')) {
                // return redirect('customer_tabview_controller/show/'.$customer_id)->with('errorMessage', 'First Name and Surname are both required.');
            // }
            // }
        } elseif ($number == 21) {
            if (! Input::get('commencement_date')) {
                return redirect('customer_tabview_controller/show/'.$customer_id);
            }

            $customer->commencement_date = Input::get('commencement_date');
            // if ( Input::get('commencement_date') && \Carbon\Carbon::createFromFormat('Y-m-d', Input::get('commencement_date')) < \Carbon\Carbon::now() ) {
                // return redirect('customer_tabview_controller/show/'.$customer_id)->with('errorMessage', 'The Commencement date can only be set to a date in the future.');
            // }
        }

        $customer->save();

        return redirect('customer_tabview_controller/show/'.$customer_id);
    }

    public function editMaxRechargeFee($customerID)
    {
        if (! Input::get('maximum_recharge_fee')) {
            return redirect('customer_tabview_controller/show/'.$customerID)->with('errorMessage', 'The value for the Maximum Recharge Fee is not valid.');
        }

        Customer::where('id', '=', $customerID)->update([
            'maximum_recharge_fee' => Input::get('maximum_recharge_fee'),
        ]);

        return redirect('customer_tabview_controller/show/'.$customerID)->with('successMessage', 'The Maximum Recharge Fee was updated successfully.');
    }

    public function edit_nominated_phone_action($customer_id)
    {
        $textarea = Input::get('t_area');

        Customer::where('id', '=', $customer_id)->update(['nominated_telephone' => $textarea]);

        return redirect('customer_tabview_controller/show/'.$customer_id);
    }

    /*public function date_search_action($customer_id)
    {
        $customer = Customer::join('district_heating_meters', 'customers.meter_ID', '=', 'district_heating_meters.meter_ID')->where('id', '=', $customer_id)->get()->first();

        // Check if customer is users customer
        if($customer['scheme_number'] != Auth::user()->scheme_number)
        {
            return redirect('customer_search');
        }

        $c_data = $customer;
        if ($c_data['shut_off'] == 0) {
            $c_data['shut_off'] = 'Device On';
        } else {
            $c_data['shut_off'] = 'Device Off';
        }
        if ($c_data['credit_warning_sent'] == 1) {
            $c_data['credit_warning_sent'] = 'Yes';
        } else {
            $c_data['credit_warning_sent'] = "No";
        }

        if ($c_data['IOU_available'] == 1) {
            $c_data['iou_statas'] = 'Available';
        } else {
            $c_data['iou_statas'] = 'Unavailable';
        }
        if ($c_data['IOU_used'] == 1) {
            $c_data['iou_statas'] = $c_data['iou_statas'] . ' & ' . "Used";
        } else {
            $c_data['iou_statas'] = $c_data['iou_statas'] . ' & ' . "Not used";
        }
        //IOU extra
        if ($c_data['IOU_extra_available'] == 1) {
            $c_data['iou_extra_statas'] = 'Available';
        } else {
            $c_data['iou_extra_statas'] = 'Unavailable';
        }
        if ($c_data['IOU_extra_used'] == 1) {
            $c_data['iou_extra_statas'] = $c_data['iou_extra_statas'] . ' & ' . "Used";
        } else {
            $c_data['iou_extra_statas'] = $c_data['iou_extra_statas'] . ' & ' . "Not used";
        }
        //Admin IOU
        if ($c_data['admin_IOU_in_use'] == 1) {
            $c_data['admin_IOU_in_use'] = "Used";
        } else {
            $c_data['admin_IOU_in_use'] = "Not used";
        }



        if ($c_data['shut_off_device_status'] == 1) {
            $c_data['shut_off_device_status'] = "On";
        } else {
            $c_data['shut_off_device_status'] = "Off";
        }
        $c_data['home'] = '';
        $c_data['message'] = 'active';
        //  echo $to .' ';
        $to = date('Y-m-d', strtotime(Input::get('to')));
        $from = date('Y-m-d', strtotime(Input::get('from')));
        $today = date('Y-m-d');
        if ($to >= $today) {
            $to = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
        }

        $district_heating_usage = DistrictHeatingUsage::where('customer_id', '=', $customer_id)->where('date', '>=', $from)->where('date', '<=', $to)->where('start_day_reading', '>', 0)->where('end_day_reading', '>', 0)->orderby('date', 'asc')->get();

        $c_data['array'] = $district_heating_usage;
        $this->populate_c_data($c_data, 'date_search_action');

        $arrear_data = CustomerArrears::where('customer_id', $customer_id)->orderby('date', 'desc')->get();
        $current_arrear = Customer::where('id', '=', $customer_id)->get(array('arrears', 'arrears_daily_repayment'))->first();

        $current_arrear_data['arrears'] = $current_arrear['arrears'];
        $current_arrear_data['arrears_daily_repayment'] = $current_arrear['arrears_daily_repayment'];

        $c_data['home'] = 'active';
        $c_data['message'] = '';

        $scheme = Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->get()->first();
        $currency = $scheme['currency_sign'];
        $abbreviation = $scheme['unit_abbreviation'];

        $dates['to'] = $to;
        $dates['from'] = $from;

        //Top Up tab info
        $credit_list = [];
        $credit_list[0]['id'] 		= $customer->id;
        $credit_list[0]['email'] 	= $customer->email_address;
        Session::put('issue_credit_credit_list', $credit_list);
        Session::put('issue_admin_iou_credit_list', $credit_list);
        Session::put('issue_topup_arrears_credit_list', $credit_list);

        //Send Message tab info
        $sms_list = [];
        $sms_list[0]['id'] = $customer->id;
        $sms_list[0]['email'] = $customer->username;
        Session::put('sms_list', $sms_list);
        $smsList = DB::table('customers')
            ->join('sms_messages', 'customers.id', '=', 'sms_messages.customer_id')
            ->where('customers.scheme_number', '=', $customer->scheme_number)
            ->where('customers.id', '=', $customer_id)
            ->orderby('date_time', 'desc')
            ->get();

        //Utility Notes tab info
        $utilityNotesList = UtilityNote::all();

        $this->layout->page = view('home/customer_tab_view', array(
            'dates' => $dates,
            'currency'=>  $currency,
            'abbreviation' => $abbreviation,
            'data' => $c_data,
            'arrears' => $arrear_data,
            'current_arrears' => $current_arrear_data,
            'smsList'           => $smsList,
            'utilityNotesList'  => $utilityNotesList
        ));
    }*/

    public function addarrears()
    {
        $user_id = Input::get('user_id');
        $addArrears = Input::get('addArrears');
        $addDailyRep = Input::get('addDailyRep');

        $ca = new CustomerArrears;
        $ca->customer_id = $user_id;
        $ca->scheme_number = Auth::user()->scheme_number;
        $ca->amount = $addArrears;
        $ca->repayment_amount = $addDailyRep;
        $ca->date = date('Y-m-d');
        $ca->save();

        return redirect('customer_tabview_controller/show/'.$user_id);
    }

    public function issue_arrears()
    {
        $this->layout->page = view('home/issue_arrears');
    }

    public function issue_top_up()
    {
        $this->layout->page = view('home/issue_top_up');
    }

    public function populate_c_data(&$c_data, $function_name = 'customer_view')
    {
        if ($c_data['array'] == false || $c_data['array']->isEmpty()) {
            $c_data['total_cost'] = 0;
            $c_data['total_usage'] = 0;
            $c_data['avg_cost'] = 0;
            $c_data['avg_usage'] = 0;
            $c_data['start_reading'] = 0;
            $c_data['end_reading'] = 0;
            $c_data['total_unit_charge'] = 0;
            $c_data['total_standing_charge'] = 0;
            $c_data['total_arrears_repayment'] = 0;
            $c_data['total_other_charges'] = 0;
        } else {
            $array = $c_data['array'];
            $total_cost = 0;
            $total_usage = 0;
            $total_unit_charge = 0;
            $total_standing_charge = 0;
            $total_arrears_repayment = 0;
            $total_other_charges = 0;
            for ($i = 0; $i < count($array); $i++) {
                $total_cost = $total_cost + $array[$i]['cost_of_day'];
                $total_usage = $total_usage + $array[$i]['total_usage'];
                //$total_unit_charge = $total_unit_charge + $array[$i]['unit_charge'];
                $total_unit_charge = $total_unit_charge + $array[$i]['total_usage'] * $c_data['kWh_usage_tariff'];

                $total_standing_charge = $total_standing_charge + $array[$i]['standing_charge'];
                $total_arrears_repayment = $total_arrears_repayment + $array[$i]['arrears_repayment'];
                $total_other_charges += abs($array[$i]['cost_of_day'] - (($array[$i]['total_usage'] * $c_data['kWh_usage_tariff']) + ($array[$i]['standing_charge']) + ($array[$i]['arrears_repayment'])));
            }

            $avg_cost = $total_cost / count($array);
            $c_data['total_cost'] = $total_cost;
            $c_data['total_usage'] = $total_usage;
            $c_data['total_unit_charge'] = $total_unit_charge;
            $c_data['total_standing_charge'] = $total_standing_charge;
            $c_data['total_arrears_repayment'] = $total_arrears_repayment;
            $c_data['total_other_charges'] = $total_other_charges;

            $c_data['avg_cost'] = $avg_cost;
            $c_data['start_reading'] = $c_data['array'][0]['start_day_reading'];
            $c_data['end_reading'] = $c_data['array'][count($c_data['array']) - 1]['end_day_reading'];
            $c_data['avg_usage'] = (($c_data['end_reading'] - $c_data['start_reading']) / count($array));
            $c_data['unit_charge'] = $c_data['array'][0]['unit_charge'];
            $c_data['standing_charge'] = $c_data['array'][0]['standing_charge'];
            $c_data['arrears_repayment'] = $c_data['array'][0]['arrears_repayment'];

            // other charges is the arrears repayment + standing charge + unit charge
            if ($function_name == 'date_search_action') {
                $c_data['other_charges'] = ((float) $c_data['array'][0]['arrears_repayment'] + (float) $c_data['array'][0]['standing_charge'] + (float) $c_data['array'][0]['unit_charge']);
            }
        }
    }

    public function passwordReset($customerID)
    {
        $customer = Customer::where('id', '=', $customerID)->get()->first();

        if (! $customer) {
            return Redirect::back()->with('errorMessage', 'Customer '.$customerID.' does not exist!');
        }

        $customer->password = '';
        $customer->save();

        return redirect('customer_tabview_controller/show/'.$customerID)->with('successMessage', 'The customer\'s password was successfully reset.');
    }

    public function stop_at($customer_id)
    {
        try {
            $reason = Input::get('reason');

            $customer = Customer::find($customer_id);
            if (! $customer) {
                throw new Exception("Customer $customer_id not found");
            }
            $res = $customer->stopAutotopup($reason);

            return redirect('customer_tabview_controller/show/'.$customer_id)->with('successMessage', 'Successfully cancelled autotopup.');
        } catch (Exception $e) {
            return redirect('customer_tabview_controller/show/'.$customer_id)->with('errorMessage', 'Failed to cancel autotopup: '.$e->getMessage().' ('.$e->getLine().')');
        }
    }

    public function start_at($customer_id)
    {
        try {
            $customer = Customer::find($customer_id);
            if (! $customer) {
                throw new Exception("Customer $customer_id not found");
            }
            $res = $customer->startAutotopup();

            return redirect('customer_tabview_controller/show/'.$customer_id)->with('successMessage', 'Successfully started autotopup.');
        } catch (Exception $e) {
            return redirect('customer_tabview_controller/show/'.$customer_id)->with('errorMessage', 'Failed to start autotopup: '.$e->getMessage().' ('.$e->getLine().')');
        }
    }

    public function force_iou($customer_id)
    {
        try {
            $customer = Customer::find($customer_id);

            if (! $customer) {
                throw new Exception("Customer $customer_id not found");
            }
            $customer->useIOU();

            return redirect('customer_tabview_controller/show/'.$customer_id)->with('successMessage', 'Successfully applied IOU.');
        } catch (Exception $e) {
            return redirect('customer_tabview_controller/show/'.$customer_id)->with('errorMessage', 'Failed to apply IOU: '.$e->getMessage().' ('.$e->getLine().')');
        }
    }

    public function remove_pm($customer_id)
    {
        try {
            $customer = Customer::find($customer_id);
            if (! $customer) {
                throw new Exception("Customer $customer_id not found");
            }

            return redirect('customer_tabview_controller/show/'.$customer_id)->with('successMessage', 'Successfully removed payment method.');
        } catch (Exception $e) {
            return redirect('customer_tabview_controller/show/'.$customer_id)->with('errorMessage', 'Failed to remove payment method: '.$e->getMessage().' ('.$e->getLine().')');
        }
    }

    public function sync_pm($customer_id)
    {
        try {
            $customer = Customer::find($customer_id);
            if (! $customer) {
                throw new Exception("Customer $customer_id not found");
            }

            return redirect('customer_tabview_controller/show/'.$customer_id)->with('successMessage', 'Successfully synced payment methods.');
        } catch (Exception $e) {
            return redirect('customer_tabview_controller/show/'.$customer_id)->with('errorMessage', 'Failed to sync payment methods: '.$e->getMessage().' ('.$e->getLine().')');
        }
    }

    public function send_statement()
    {
        try {
            $from = Input::get('from');
            $to = Input::get('to');

            $customer = Customer::find($customer_id);
            if (! $customer) {
                throw new Exception("Customer $customer_id not found");
            }

            return redirect('customer_tabview_controller/show/'.$customer_id)->with('successMessage', 'Successfully sent account statement');
        } catch (Exception $e) {
            return redirect('customer_tabview_controller/show/'.$customer_id)->with('errorMessage', 'Failed to send account statement: '.$e->getMessage().' ('.$e->getLine().')');
        }
    }

    public function clear_away_mode($customerID)
    {
        $customer = Customer::find($customerID);
        if (! $customer) {
            return Redirect::back()->with(['errorMessage' => 'Customer '.$customerID.' does not exist!']);
        }

        if (! $customer->districtHeatingMeter) {
            return Redirect::back()->with(['errorMessage' => 'Customer '.$customerID.' does not have a district_heating_meter entry!']);
        }

        $permanent_meter_id = $customer->districtHeatingMeter->permanent_meter_ID;

        $rcs = RemoteControlStatus::where('permanent_meter_id', $permanent_meter_id)->first();
        if (! $rcs) {
            return Redirect::back()->with(['errorMessage' => 'Customer does not have a remote_control_status entry in database']);
        }

        $rcs->away_mode_retry_datetime = null;
        $rcs->away_mode_permanent = 0;
        $rcs->away_mode_end_datetime = '2015-01-01 00:00:00';
        $rcs->save();

        $log = new AwayModeLog();
        $log->permanent_meter_id = $permanent_meter_id;
        $log->message = Auth::user()->username.' force stopped away mode for customer '.$customerID;
        $log->save();

        return Redirect::back()->with(['successMessage' => 'Successfully removed away mode from customer']);
    }

    public function activate_away_mode($customerID)
    {
        $customer = Customer::find($customerID);
        if (! $customer) {
            return Redirect::back()->with(['errorMessage' => 'Customer '.$customerID.' does not exist!']);
        }

        if (! $customer->districtHeatingMeter) {
            return Redirect::back()->with(['errorMessage' => 'Customer '.$customerID.' does not have a district_heating_meter entry!']);
        }

        $permanent_meter_id = $customer->districtHeatingMeter->permanent_meter_ID;

        $rcs = RemoteControlStatus::where('permanent_meter_id', $permanent_meter_id)->first();
        if (! $rcs) {
            return Redirect::back()->with(['errorMessage' => 'Customer does not have a remote_control_status entry in database']);
        }

        $rcs->away_mode_retry_datetime = null;
        $rcs->away_mode_on = 1;
        $rcs->away_mode_cancelled = 0;
        $rcs->away_mode_permanent = 1;
        $rcs->away_mode_end_datetime = '2050-10-10 00:00:00';
        $rcs->away_mode_relay_status = 0;
        $rcs->save();

        $log = new AwayModeLog();
        $log->permanent_meter_id = $permanent_meter_id;
        $log->message = Auth::user()->username.' force activated away mode for customer '.$customerID;
        $log->save();

        return Redirect::back()->with(['successMessage' => 'Successfully activated away mode for customer']);
    }

    public function sync_paypal($customerID, $date)
    {
        $missing_payments = Paypal::getMissingPayments($date, $date, $customerID)['payments'];
        $inserted = 0;

        $customer = Customer::find($customerID);

        foreach ($missing_payments as $m) {
            $result = $customer->addPayment($m->id, $m->amount, $m->time);
            if ($result) {
                $inserted++;
            }
        }

        if ($inserted > 0) {
            return Redirect::back()->with(['successMessage' => "Successfully synched $inserted Paypal payment(s)!"]);
        } else {
            return Redirect::back()->with(['info' => 'There were no payments to sync.']);
        }
    }

    public function toggleEVOwner()
    {
        $customerID = Input::get('user_id');
        $isEVOwner = (int) Input::get('ev_owner');

        if (! $customerID) {
            return '0';
        }

        Customer::findOrFail($customerID)->update([
            'ev_owner' => (int) $isEVOwner,
            'maximum_recharge_fee' => $isEVOwner ? 10 : null,
        ]);

        return '1';
    }

    public function redToGreen($customerID)
    {
        $customer = Customer::findOrFail($customerID);

        $customer->resetShutOff();

        $this->log->addInfo('Shut off MANUAL reset', ['customer_id' => $customer->id, 'balance' => $customer->balance]);

        return redirect('customer_tabview_controller/show/'.$customerID)->with('successMessage', 'The customer is set back to green.');
    }

    public function save_meter_info($customerID)
    {
        try {
            $customer = Customer::find($customerID);

            if (! $customer) {
                throw new Exception('Customer with ID '.$customerID.' not found!');
            }
            $pmd = $customer->permanentMeter;

            if (! $pmd) {
                throw new Exception("Customer doesn't have a permanent_meter_data entry!");
            }
            $dhm = $customer->districtMeter;

            if (! $dhm) {
                throw new Exception("Customer doesn't have a district_heating_meter entry!");
            }
            //district_heating_meter.meter_ID
            $d_meter_ID = Input::get('d_meter_ID');

            //permanent_meter_data.ID
            $pmd_ID = Input::get('pmd_ID');

            //customers.meter_ID
            $c_meter_ID = Input::get('c_meter_ID');

            //district_heating_meter.meter_number
            $meter_number = Input::get('meter_number');

            //permanent_meter_data.meter_number
            $p_meter_number = Input::get('p_meter_number');

            //permanent_meter_data.meter_number2
            $p_meter_number2 = Input::get('p_meter_number2');

            //permanent_meter_data.scu_number
            $p_scu_number = Input::get('p_scu_number');

            //permanent_meter_data.m_bus_relay_id
            $p_m_bus_relay_id = Input::get('p_m_bus_relay_id');

            //permanent_meter_data.data_logger_id
            $data_logger_id = Input::get('data_logger_id');

            //permanent_meter_data.data_logger_id
            $readings_per_day = Input::get('readings_per_day');

            $pmd_meter_number_check = PermanentMeterData::where('meter_number', $p_meter_number)->where('ID', '!=', $pmd_ID)->first();
            if ($pmd_meter_number_check) {
                $username = $pmd_meter_number_check->username;
                $customer = Customer::where('username', $username)->first();

                if ($customer) {
                    $username = "<a href='/customer_tabview_controller/show/".$customer->id."'>$username</a>";
                }
                throw new Exception("$username is already using that meter_number ' ".$p_meter_number." '. Please either enter a new one or modify ".$username."'s meter_number in permanent_meter_data..");
            }

            $new_meter_8 = (strpos($meter_number, '_') !== false) ? explode('_', $meter_number)[1] : $meter_number;
            $meter_ending = '';

            // Cache old meter address translations
            $old_meter_8 = (strpos($dhm->meter_number, '_') !== false) ? explode('_', $dhm->meter_number)[1] : $dhm->meter_number;
            $old_meter_16 = MBusAddressTranslation::where('8digit', $old_meter_8)->first()['16digit'];
            if (! $old_meter_16) {
                $check_new_meter_8_inserted = MBusAddressTranslation::where('8digit', $new_meter_8)->first();
                if ($check_new_meter_8_inserted) {
                    $check_meter_8_16 = $check_new_meter_8_inserted['16digit'];
                    $meter_ending = substr($check_meter_8_16, 8, 15);
                } else {
                }
            } else {
                $meter_ending = explode($old_meter_8, $old_meter_16)[1];
            }

            // Cache old scu address translations
            $old_scu_8 = $pmd->scu_number;
            $old_scu_16 = MBusAddressTranslation::where('8digit', $old_scu_8)->first()['16digit'];
            $scu_ending = explode($old_scu_8, $old_scu_16)[1];

            $customer->meter_ID = $c_meter_ID;
            $customer->save();

            //$dhm->meter_ID = $d_meter_ID;
            $dhm->meter_number = $meter_number;
            $dhm->save();

            //$pmd->ID = $pmd_ID;
            $pmd->meter_number = $p_meter_number;
            $pmd->meter_number2 = $p_meter_number2;
            $pmd->scu_number = $p_scu_number;
            $pmd->m_bus_relay_id = $p_m_bus_relay_id;
            $pmd->data_logger_id = $data_logger_id;
            $pmd->readings_per_day = $readings_per_day;
            $pmd->save();

            // Set new address translations

            $new_meter_16 = $new_meter_8.$meter_ending;
            if (! MBusAddressTranslation::where('8digit', $new_meter_8)->first()) {
                DB::table('mbus_address_translations')->insert([
                '8digit' => $new_meter_8,
                '16digit' => $new_meter_16,
            ]);
            }
            /*
            DB::table('mbus_address_translations')->where('8digit', $old_meter_8)->update([
                '8digit' => $new_meter_8,
                '16digit' => $new_meter_16
            ]);
            */

            $new_scu_8 = $pmd->scu_number;
            $new_scu_16 = $new_scu_8.$scu_ending;
            if (! MBusAddressTranslation::where('8digit', $new_scu_8)->first()) {
                DB::table('mbus_address_translations')->insert([
                '8digit' => $new_scu_8,
                '16digit' => $new_scu_16,
            ]);
            }
            /*
            DB::table('mbus_address_translations')->where('8digit', $old_scu_8)->update([
                '8digit' => $new_scu_8,
                '16digit' => $new_scu_16
            ]);
            */

            return Redirect::back()->with('successMessage', 'Successfully saved changes to customers meter information.');
        } catch (Exception $e) {
            return Redirect::back()->with('errorMessage', $e->getMessage().' on Line '.$e->getLine());
        }
    }

    public function replace_meter($customerID)
    {
        try {
            $primary = Input::get('primary_meter_num');
            $secondary = Input::get('secondary_meter_num');

            if (strlen($primary) < 8) {
                throw new Exception('The replacement primary meter address must be 8 digits long!');
            }
            if (strlen($secondary) < 16) {
                throw new Exception('The replacement secondary meter address must be 16 digits long!');
            }
            $customer = Customer::find($customerID);

            if (! $customer) {
                throw new Exception("Customer $customerID not found!");
            }
            $scheme = Scheme::where('scheme_number', $customer->scheme_number)->first();

            if (! $scheme) {
                throw new Exception('Scheme '.$customer->scheme_number.' not found!');
            }
            $scheme_prefix = $scheme->prefix;

            $meter_number = $scheme_prefix.$primary;
            $meter_number_sixteen = $secondary;

            $dhm = $customer->districtMeter;

            if (! $dhm) {
                throw new Exception('Customer doesnt have a disitrict heating meter!');
            }
            $pmd = $customer->permanentMeter;

            if (! $pmd) {
                throw new Exception("Customer doesn't have a permanent meter!");
            }
            DB::table('district_heating_meters')->where('meter_ID', $dhm->meter_ID)
            ->update([
                'meter_number' => $meter_number,
            ]);

            DB::table('permanent_meter_data')->where('ID', $pmd->ID)
            ->update([
                'meter_number' => $meter_number,
            ]);

            return Redirect::back()->with([
                'successMessage' => "Successfully changed customers' meter number to '$secondary'",
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => '<b>Error: </b>'.$e->getMessage().' ('.$e->getLine().')',
            ]);
        }
    }

    public function poll_replace_meter($customerID)
    {
        try {
            $meter = Input::get('primary');

            $potential_meter = MeterLookup::where('typical_start', substr($meter, 0, 1))->first();

            if (! $potential_meter) {
                return Response::json([
                    'errorMessage' => 'none',
                ]);
            }

            $mbus = MBusAddressTranslation::where('8digit', $meter)->first();
            if ($mbus) {
                $mbus['16digit'] = $meter.$potential_meter->last_eight;
                $mbus->save();
            } else {
                $mbus = new MBusAddressTranslation();
                $mbus['8digit'] = $meter;
                $mbus['16digit'] = $meter.$potential_meter->last_eight;
                $mbus->save();
                $mbus = MBusAddressTranslation::where('8digit', $primary)->first();
            }

            return Response::json([
                'successMessage' => "<b>Potential meter detected:</b> '".$potential_meter->meter_make.' '.$potential_meter->meter_model."'. Ending for secondary address is '<b>".$potential_meter->last_eight."</b>'.<button onclick='useBtn(this)' digit='".$potential_meter->last_eight."' class='btn btn-primary pull-right'>Use</button>",
                'meter' => $potential_meter,
            ]);
        } catch (Exception $e) {
            return Response::json([
                'errorMessage' => '<b>Error: </b>'.$e->getMessage().' ('.$e->getLine().')',
            ]);
        }
    }

    public function test_replace_meter($customerID)
    {
        try {
            $primary = Input::get('primary');
            $secondary = Input::get('secondary');

            $mbus = MBusAddressTranslation::where('8digit', $primary)->first();
            if ($mbus) {
                $mbus['16digit'] = $secondary;
                $mbus->save();
            } else {
                $mbus = new MBusAddressTranslation();
                $mbus['8digit'] = $primary;
                $mbus['16digit'] = $secondary;
                $mbus->save();
                $mbus = MBusAddressTranslation::where('8digit', $primary)->first();
            }

            $customer = Customer::find($customerID);
            if (! $customer) {
                throw new Exception("Customer $customerID not found!");
            }
            $res = $mbus->read($customer->scheme_number);
            $reading = $res->val;
            $temp = $res->temp;

            if ($reading <= -1) {
                return Response::json([
                    'errorMessage' => 'Reading failed! '.$res->error,
                ]);
            }

            return Response::json([
                'successMessage' => "<b>Reading successful</b>: $reading kWh. ($temp&deg;C)",
                'res' => $res,
                'reading' => $reading,
                'temp' => $temp,
            ]);
        } catch (Exception $e) {
            return Response::json([
                'errorMessage' => '<b>Error: </b>'.$e->getMessage().' ('.$e->getLine().')',
            ]);
        }
    }

    public function adminSpecialist()
    {
        $this->layout->page = view('home/specialist');
    }

    public function requestTest()
    {
        try {
            $this->layout->page = view('home.request_test', [

            ]);
        } catch (Exception $e) {
            return Response::json([
                'error' => 'An error occured: '.$e->getMessage(),
            ]);
        }
    }

    public function export()
    {
        $customer_id = Input::get('customer_id');
        $show_amounts = (Input::get('show_amounts') == 'true') ? true : false;
        $show_gtotal = (Input::get('show_gtotal') == 'true') ? true : false;
        $show_usage = (Input::get('show_usage') == 'true') ? true : false;
        $show_readings = (Input::get('show_readings') == 'true') ? true : false;
        $show_tariffs = (Input::get('show_tariffs') == 'true') ? true : false;
        $from = (new DateTime(Input::get('from')))->format('Y-m-d');
        $to = (new DateTime(Input::get('to')))->format('Y-m-d');

        $usage_entries = DistrictHeatingUsage::whereRaw("(customer_id = $customer_id AND (date >= '$from' AND date <= '$to') )")->get();

        $customer = Customer::find($customer_id);

        $scheme = Scheme::find($customer->scheme_number);
        $tariff = $scheme->tariff;

        $csvData = '';
        $csvData .= "Customer #$customer_id:\nName: ".$customer->first_name.' '.$customer->surname."\nUsername: ".$customer->username."\n";
        $csvData .= "Start Date:, $from\nEnd Date, $to";
        if ($show_tariffs) {
            $csvData .= "\nkWh Usage Tariff:, ".$tariff->tariff_1."\n";
            $csvData .= 'Standing charge Tariff:, '.$tariff->tariff_2;
        }
        $csvData .= "\n\n";

        $csvData .= 'Date';
        if ($show_amounts) {
            $csvData .= ',Unit Charge, Standing Charge';
        }
        if ($show_usage) {
            $csvData .= ',Total Usage';
        }
        if ($show_readings) {
            $csvData .= ',Start Day Reading, End Day Reading';
        }
        if ($show_gtotal) {
            $csvData .= ',Cost of day';
        }

        $csvData .= "\n";

        foreach ($usage_entries as $k=> $v) {
            $csvData .= $v->date.'';
            if ($show_amounts) {
                $csvData .= ','.$v->unit_charge.','.$v->standing_charge;
            }
            if ($show_usage) {
                $csvData .= ','.$v->total_usage;
            }
            if ($show_readings) {
                $csvData .= ','.$v->start_day_reading.','.$v->end_day_reading;
            }
            if ($show_gtotal) {
                $csvData .= ','.$v->cost_of_day;
            }

            $csvData .= "\n";
        }

        $csvFilename = 'customer_'.$customer_id.'_export_'.date('Y-m-d Hms').'';

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename='.$csvFilename.'.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        // print $csvData;

        return $csvData;
    }

    public function send_notification($customer_id)
    {
        try {
            $title = Input::get('title');
            $body = Input::get('body');
            $dismiss_txt = Input::get('dismiss_txt');
            $dismiss_txt_url = Input::get('dismiss_txt_url');

            $customer = Customer::find($customer_id);

            $ian = new InAppNotification();
            $ian->customer_id = $customer_id;
            $ian->scheme_number = $customer->scheme_number;
            $ian->all_schemes = false;
            $ian->dismiss_txt = $dismiss_txt;
            $ian->dismiss_txt_url = $dismiss_txt_url;
            $ian->title = $title;
            $ian->body = $body;
            $ian->delivered = false;
            $ian->delivered_at = null;
            $ian->save();

            return Redirect::back()->with([
                'successMessage' => 'Successfully sent notification',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'error' => $e->getMessage(),
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    public function refund_payment($customer_id)
    {
        try {
            $customer = Customer::find($customer_id);
            if (! $customer) {
                throw new Exception("Customer $customer_id not found");
            }
            $refund_amount = Input::get('refund_amount');
            $partial_refund = Input::get('partial_refund');
            if ($partial_refund == 'on') {
                $partial_refund = true;
            } else {
                $partial_refund = false;
            }

            $ref_number = Input::get('ref_number');
            $refund_amount = Input::get('refund_amount');
            $refund_reason = Input::get('refund_reason');

            $res = $customer->refundPayment($ref_number, $refund_reason, $partial_refund, $refund_amount);

            return redirect('customer_tabview_controller/show/'.$customer_id)->with('successMessage', "Successfully refunded payment ' $ref_number '.");
        } catch (Exception $e) {
            return redirect('customer_tabview_controller/show/'.$customer_id)->with('errorMessage', "Failed to refund payment $ref_number: ".$e->getMessage().' ('.$e->getLine().')');
        }
    }
}
