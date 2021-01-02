<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class BillingController extends BaseController
{
    protected $layout = 'layouts.admin_website';

    public function get_billing($customer_id)
    {
        $customer = Customer::find($customer_id);

        if (! Auth::user()->isUserTest() && $customer->scheme_number != Auth::user()->scheme_number) {
            return 'You do not have the authorization to view this page.';
        }

        try {
            if (! Input::get('from')) {
                $from = explode(' ', Carbon\Carbon::now()->startOfMonth())[0];
            } else {
                $from = Input::get('from');
            }

            if (! Input::get('to')) {
                $to = date('Y-m-d');
            } else {
                $to = Input::get('to');
            }
        } catch (Exception $e) {
        }

        $flags = BillingEngineFlag::where('customer_ID', $customer->id)
        ->whereRaw("(created_at >= '$from 00:00:00' AND created_at <= '$to 23:59:59')")
        ->get();

        $logs = BillingEngineLogsNew::getLogRange($customer->id, $from, $to);

        $log = BillingEngineLogsNew::getLogs($customer_id, $to);

        $db_logs = EngineBillingLog::where('customer_id', $customer->id)
        ->whereRaw("(created_at >= '$from 00:00:00' AND created_at <= '$to 23:59:59')")
        ->orderBy('id', 'DESC')->get();

        $this->layout->page = View::make('home.customer_billing', [

            'customer' => $customer,
            'logs' => $logs,
            'from' => $from,
            'to' => $to,
            'flags' => $flags,
            'db_logs' => $db_logs,

        ]);
    }

    public function download_billing($customer_id)
    {
        try {
            $log = BillingEngineLogsNew::getLogs($customer_id, date('Y-m-d'));

            return Redirect::to($log->download());
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => 'Error occurred: '.$e->getMessage(),
            ]);
        }
    }

    public function refund_date($customer_id)
    {
        $log_date = Input::get('log_date');

        try {
            return Redirect::back()->with([
                'successMessage' => "Successfully refunded billing's from $log_date",
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => 'Error occurred: '.$e->getMessage(),
            ]);
        }
    }

    public function refund_charge($customer_id)
    {
        $date = Input::get('date');
        $charge_id = Input::get('charge_id');

        try {
            $customer = Customer::find($customer_id);

            if (! $customer) {
                throw new Exception("Customer $customer_id does not exist!");
            }
            $log = BillingEngineLogsNew::getLogs($customer_id, $date);
            $response = $log->refundCharge($charge_id);

            if (! $response['found']) {
                throw new Exception('Charge #'.($charge_id + 1).' not found: '.$response['error']);
            }
            $amount = $response['amount'];
            $entry = new EngineBillingLog();
            $entry->operator_id = Auth::user()->id;
            $entry->customer_id = $customer_id;
            $entry->type = 'billing_refund';
            $entry->message = 'Refunded €'.$amount.' for Charge #'.($charge_id + 1).' from '.$response['date'];
            $entry->save();

            return Redirect::back()->with([
                'successMessage' => 'Successfully refunded Charge #'.($charge_id + 1).". Customer's balance change: &euro;".$response['balance_before'].' -> &euro;'.$response['balance_after'].'',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => 'Error occurred: '.$e->getMessage(),
            ]);
        }
    }

    public function issue_charge($customer_id)
    {
        $date = Input::get('date');
        $charge_id = Input::get('charge_id');

        try {
            $customer = Customer::find($customer_id);

            if (! $customer) {
                throw new Exception("Customer $customer_id does not exist!");
            }
            $log = BillingEngineLogsNew::getLogs($customer_id, $date);
            $response = $log->reissueCharge($charge_id);

            if (! $response['found']) {
                throw new Exception('Charge #'.($charge_id + 1).' not found: '.$response['error']);
            }
            $amount = $response['amount'];
            $entry = new EngineBillingLog();
            $entry->operator_id = Auth::user()->id;
            $entry->customer_id = $customer_id;
            $entry->type = 'billing_reissue';
            $entry->message = 'Reissued €'.$amount.' for Charge #'.($charge_id + 1).' from '.$response['date'];
            $entry->save();

            return Redirect::back()->with([
                'successMessage' => 'Successfully re-issued Charge #'.($charge_id + 1).". Customer's balance change: &euro;".$response['balance_before'].' -> &euro;'.$response['balance_after'].'',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => 'Error occurred: '.$e->getMessage(),
            ]);
        }
    }

    public function view_flags()
    {
        try {
            $flags = BillingEngineFlag::pending()->get();

            $this->layout->page = View::make('home.flags', [
                'flags' => $flags,
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => 'Error occurred: '.$e->getMessage(),
            ]);
        }
    }

    public function approve_flag($customer_id)
    {
        $flag_id = Input::get('flag_id');

        try {
            $flag = BillingEngineFlag::where('id', $flag_id)->where('customer_ID', $customer_id)->first();

            if (! $flag) {
                throw new Exception("Flag #$flag_id for Customer $customer_id does not exist!");
            }
            $amount = $flag->amount;
            $kwh = $flag->kwh_usage;

            $customer = Customer::find($customer_id);

            if (! $customer) {
                throw new Exception("Customer $customer_id not found!");
            }
            $dhm = $customer->districtMeter;

            if (! $dhm) {
                throw new Exception("Customer $customer_id's meter not found!");
            }
            $dhu = DistrictHeatingUsage::where('customer_id', $customer_id)->where('date', date('Y-m-d'))->first();

            if (! $dhu) {
                throw new Exception("Cannot find today's district_heating_usage to apply the charge to!");
            }
            $before = [
                        'dhu_id' => $dhu->id,
                        'balance' => $customer->balance,
                        'used_today' => $customer->used_today,
                        'sudo_reading' => $dhm->sudo_reading,
                        'latest_reading' => $dhm->latest_reading,
                        'cost_of_day' => $dhu->cost_of_day,
                        'total_usage' => $dhu->total_usage,
                        'unit_charge' => $dhu->unit_charge,
                        'end_day_reading' => $dhu->end_day_reading,
            ];

            $customer->balance -= $amount;
            $customer->used_today += $amount;
            $customer->save();

            if ($dhm->sudo_reading > $dhm->latest_reading) {
                $dhm->latest_reading = $dhm->sudo_reading;
            } else {
                $dhm->sudo_reading = $dhm->latest_reading;
            }

            $dhm->latest_reading_time = date('Y-m-d H:i:s');
            $dhm->save();

            $dhu->cost_of_day += $amount;
            $dhu->total_usage += $kwh;
            $dhu->unit_charge += $amount;
            $dhu->end_day_reading = $dhm->latest_reading;
            $dhu->save();

            $flag->approved = true;
            $flag->applied_to = $dhu->id;
            $flag->save();

            $entry = new EngineBillingLog();
            $entry->operator_id = Auth::user()->id;
            $entry->customer_id = $customer_id;
            $entry->type = 'flag_approve';
            $entry->message = "Approved Flag #$flag_id for Customer $customer_id";
            $entry->save();

            $after = [
                        'dhu_id' => $dhu->id,
                        'balance' => $customer->balance,
                        'used_today' => $customer->used_today,
                        'sudo_reading' => $dhm->sudo_reading,
                        'latest_reading' => $dhm->latest_reading,
                        'cost_of_day' => $dhu->cost_of_day,
                        'total_usage' => $dhu->total_usage,
                        'unit_charge' => $dhu->unit_charge,
                        'end_day_reading' => $dhu->end_day_reading,
                  ];

            return Redirect::back()->with([
                'successMessage' => "Successfully approved Flag #$flag_id for Customer $customer_id",
                'before' => $before,
                'after' => $after,
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => 'Error occurred: '.$e->getMessage(),
            ]);
        }
    }

    public function decline_flag($customer_id)
    {
        $flag_id = Input::get('flag_id');

        try {
            $flag = BillingEngineFlag::where('id', $flag_id)->where('customer_ID', $customer_id)->first();

            if (! $flag) {
                throw new Exception("Flag ID $flag_id for Customer $customer_id does not exist!");
            }
            $flag->declined = true;
            $flag->save();

            $entry = new EngineBillingLog();
            $entry->operator_id = Auth::user()->id;
            $entry->customer_id = $customer_id;
            $entry->type = 'flag_decline';
            $entry->message = "Declined Flag #$flag_id for Customer $customer_id";
            $entry->save();

            // Change the customers latest reading to sudo reading
            $customer = $flag->customer;
            if ($customer) {
                $dhm = $customer->districtMeter;
                if ($dhm) {
                    $dhm->latest_reading = $dhm->sudo_reading;
                    $dhm->save();

                    return Redirect::back()->with([
                        'warning' => "Ignoring charges for flag #$flag_id for Customer $customer_id. ",
                    ]);
                }
            }

            return Redirect::back()->with([
                'warning' => "Successfully declined Flag #$flag_id for Customer $customer_id",
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => 'Error occurred: '.$e->getMessage(),
            ]);
        }
    }

    public function auto_spread($customer_id)
    {
        $flag_id = Input::get('flag_id');
        $spread_days = Input::get('spread_days_'.$flag_id);

        try {
            $flag = BillingEngineFlag::where('id', $flag_id)->where('customer_ID', $customer_id)->first();

            if (! $flag) {
                throw new Exception("Flag #$flag_id for Customer $customer_id does not exist!");
            }
            $amount = $flag->amount;
            $kwh = $flag->kwh_usage;

            $customer = Customer::find($customer_id);

            if (! $customer) {
                throw new Exception("Customer $customer_id not found!");
            }
            $dhm = $customer->districtMeter;

            if (! $dhm) {
                throw new Exception("Customer $customer_id's meter not found!");
            }
            $dhu = DistrictHeatingUsage::where('customer_id', $customer_id)->where('date', date('Y-m-d'))->first();

            if (! $dhu) {
                throw new Exception("Cannot find today's district_heating_usage to apply the charge to!");
            }
            $before = [
                        'dhu_id' => $dhu->id,
                        'balance' => $customer->balance,
                        'used_today' => $customer->used_today,
                        'sudo_reading' => $dhm->sudo_reading,
                        'latest_reading' => $dhm->latest_reading,
                        'cost_of_day' => $dhu->cost_of_day,
                        'total_usage' => $dhu->total_usage,
                        'unit_charge' => $dhu->unit_charge,
                        'end_day_reading' => $dhu->end_day_reading,
            ];

            $customer->balance -= $amount;
            $customer->used_today += $amount;
            $customer->save();

            if ($dhm->sudo_reading > $dhm->latest_reading) {
                $dhm->latest_reading = $dhm->sudo_reading;
            } else {
                $dhm->sudo_reading = $dhm->latest_reading;
            }

            $dhm->latest_reading_time = date('Y-m-d H:i:s');
            $dhm->save();

            $dhu->cost_of_day += $amount;
            $dhu->total_usage += $kwh;
            $dhu->unit_charge += $amount;
            $dhu->end_day_reading = $dhm->latest_reading;
            $dhu->save();

            $flag->approved = true;
            $flag->applied_to = $dhu->id;
            $flag->save();

            $entry = new EngineBillingLog();
            $entry->operator_id = Auth::user()->id;
            $entry->customer_id = $customer_id;
            $entry->type = 'flag_approve';
            $entry->message = "Approved Flag #$flag_id for Customer $customer_id. And spread over $spread_days days.";
            $entry->save();

            $after = [
                'dhu_id' => $dhu->id,
                'balance' => $customer->balance,
                'used_today' => $customer->used_today,
                'sudo_reading' => $dhm->sudo_reading,
                'latest_reading' => $dhm->latest_reading,
                'cost_of_day' => $dhu->cost_of_day,
                'total_usage' => $dhu->total_usage,
                'unit_charge' => $dhu->unit_charge,
                'end_day_reading' => $dhu->end_day_reading,
            ];

            $dhu->spreadCharges($spread_days);

            return Redirect::back()->with([
                'successMessage' => 'Successfully approved & spread over '.$spread_days." on Flag #$flag_id for Customer $customer_id",
                'before' => $before,
                'after' => $after,
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => 'Error occurred: '.$e->getMessage(),
            ]);
        }
    }
}
