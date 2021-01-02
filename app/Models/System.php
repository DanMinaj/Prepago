<?php

namespace App\Models;

class System
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    public static function getCachedCustomersStatus($date = null)
    {
        if ($date == null) {
            $date = date('Y-m-d');
        }

        $entry = SystemGraphData::where('date', $date)->first();
        $value = $entry->get('customer_status');

        try {
            return (object) [
            'greenCustomers' 			=> $value['greenCustomers'],
            'yellowCustomers' 			=> $value['yellowCustomers'],
            'redCustomers' 				=> $value['redCustomers'],
            'blueCustomers' 			=> $value['blueCustomers'],
            'whiteCustomers' 			=> $value['whiteCustomers'],
            'greenCustomers_yesterday' 	=> $value['greenCustomers_yesterday'],
            'yellowCustomers_yesterday' => $value['yellowCustomers_yesterday'],
            'redCustomers_yesterday' 	=> $value['redCustomers_yesterday'],
            'blueCustomers_yesterday' 	=> $value['blueCustomers_yesterday'],
            'whiteCustomers_yesterday' 	=> $value['whiteCustomers_yesterday'],
            'greenCustomers_pc'			=> $value['greenCustomers_pc'],
            'redCustomers_pc'			=> $value['redCustomers_pc'],
            'yellowCustomers_pc'		=> $value['yellowCustomers_pc'],
            'blueCustomers_pc'			=> $value['blueCustomers_pc'],
            'whiteCustomers_pc'			=> $value['whiteCustomers_pc'],
        ];
        } catch (Exception $e) {
            echo $e->getMessage();
            die();
        }
    }

    public static function getCustomersStatus()
    {
        $schemes = Scheme::where('status_debug', 0)->where('archived', 0)->get();
        $customers = self::getCustomers();
        $green = count($customers->green);
        $yellow = count($customers->yellow);
        $red = count($customers->red);
        $blue = count($customers->blue);
        $white = count($customers->white);

        return (object) [
            'green' => $green,
            'yellow' => $yellow,
            'red' => $red,
            'blue' => $blue,
            'white' => $white,
        ];
    }

    public static function getCustomersDate($date = null)
    {
        $customers = Customer::active2()
        ->where('customers.status', '=', 1)
        ->where('commencement_date', '<=', $date)
        ->orderBy('balance', 'asc')
        ->get();

        return $customers;
    }

    public static function getCustomers($date = null)
    {
        $schemes = Scheme::where('status_debug', 0)->where('archived', 0)->where('simulator', 0)->get();
        $all = [];
        $green = [];
        $yellow = [];
        $red = [];
        $blue = [];
        $white = [];

        foreach ($schemes as $s) {
            foreach (Customer::getNormalCustomers($s->scheme_number) as $k => $c) {
                if ($c->permanentMeter) {
                    if ($c->permanentMeter->is_bill_paid_customer) {
                        continue;
                    }
                }
                $green[] = $c;

                if ($date != null && $c->commencement_date <= $date) {
                    $all[] = $c;
                }
            }

            foreach (Customer::getPendingCustomers($s->scheme_number) as $k => $c) {
                if ($c->permanentMeter) {
                    if ($c->permanentMeter->is_bill_paid_customer) {
                        continue;
                    }
                }
                $yellow[] = $c;

                if ($date != null && $c->commencement_date <= $date) {
                    $all[] = $c;
                }
            }
            foreach (Customer::getShutOffCustomers($s->scheme_number) as $k => $c) {
                if ($c->permanentMeter) {
                    if ($c->permanentMeter->is_bill_paid_customer) {
                        continue;
                    }
                }
                $red[] = $c;

                if ($date != null && $c->commencement_date <= $date) {
                    $all[] = $c;
                }
            }

            foreach (Customer::getBillPaidCustomers($s->scheme_number) as $k => $c) {
                $blue[] = $c;

                if ($date != null && $c->commencement_date <= $date) {
                    $all[] = $c;
                }
            }

            foreach (Customer::getEmptyCustomers($s->scheme_number) as $k => $c) {
                $white[] = $c;
            }
        }

        //	$red -= $blue;

        return (object) [
            'green' => $green,
            'yellow' => $yellow,
            'red' => $red,
            'blue' => $blue,
            'white' => $white,
        ];
    }

    public static function whosOnline()
    {
        return User::where('is_online', 1)->whereRaw('is_online_time  >= NOW() - INTERVAL 15 SECOND')->get();
    }

    public static function whosOffline()
    {
        return User::whereRaw('is_online_time  < NOW() - INTERVAL 15 SECOND')->orderBy('is_online_time', 'DESC')->get();
    }

    public static function getTcpCustomers()
    {
        try {
            $require_shut_off = DistrictHeatingMeter::requireShutoff()->get();
            $require_restoration = DistrictHeatingMeter::requireRestoration()->get();
            $require_away_mode = DistrictHeatingMeter::requireAwayMode()->get();

            foreach ($require_restoration as $key => $m) {
                $require_restoration[$key]['customer'] = Customer::where('meter_ID', $m->meter_ID)->first();
                if (! $require_restoration[$key]['customer']) {
                    $require_restoration->forget($key);
                }
            }

            return [
            'require_shut_off'				=> $require_shut_off,
            'require_restoration'			=> $require_restoration,
            'require_away_mode'				=> $require_away_mode,
        ];
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public static function getColouredCustomers()
    {
        $schemes = Scheme::where('status_debug', 0)->where('archived', 0)->get();
        $green = [];
        $yellow = [];
        $red = [];
        $blue = [];

        foreach ($schemes as $s) {
            foreach (Customer::getNormalCustomers($s->scheme_number) as $g) {
                $green[] = $g;
            }

            foreach (Customer::getPendingCustomers($s->scheme_number) as $g) {
                $yellow[] = $g;
            }

            foreach (Customer::getBillPaidCustomers($s->scheme_number) as $g) {
                $blue[] = $g;
            }

            foreach (Customer::getShutOffCustomers($s->scheme_number) as $g) {
                if ($g->permanentMeter->is_bill_paid_customer) {
                    continue;
                }

                $red[] = $g;
            }
        }

        return (object) [
            'green' => $green,
            'yellow' => $yellow,
            'red' => $red,
            'blue' => $blue,
        ];
    }

    public static function getGraphData($date)
    {
        $graphData = SystemGraphData::where('date', $date)->first();

        return $graphData;
    }

    public static function set($stat, $value)
    {
        SystemStat::set($stat, $value);
    }

    public static function get($stat)
    {
        return SystemStat::get($stat);
    }

    public static function log($msg, $type = 'general')
    {
        $newLog = new SystemLog();
        $newLog->type = $type;
        $newLog->message = $msg;
        $newLog->save();
    }

    public static function clearLogs()
    {
        SystemLog::truncate();
    }

    public static function getStripe($num = 7)
    {
        $sys_graph_data = SystemGraphData::orderBy('id', 'DESC')->first();

        if (! ($sys_graph_data->contains('stripe'))) {
            return;
        }

        $stripe = $sys_graph_data->get('stripe');

        try {
            if ($stripe != null) {
                if (isset($stripe['payouts'])) {
                    $payouts = [];
                    foreach ($stripe['payouts'] as $k => $v) {
                        if ($num == 0) {
                            continue;
                        }
                        array_push($payouts, $v);
                        //var_dump($v);

                        $num--;
                    }

                    $stripe['payouts'] = $payouts;
                }
            }
        } catch (Exception $e) {
        }

        return $stripe;
    }
}
