<?php

namespace App\Console\Commands\Manual;

use App\Models\Customer;
use App\Models\DistrictHeatingUsage;
use App\Models\Tariff;
use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;



class FixStandingCharges extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'fix:standing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        ob_start();
        ini_set('memory_limit', '-1');

        try {
            $start_date = '2019-01-01';
            $end_date = date('Y-m-d');

            $customers = Customer::where('status', 1)->whereRaw('deleted_at IS NULL')->get();

            foreach ($customers as $customer) {
                $tariffs = Tariff::where('scheme_number', $customer->scheme_number)->first();
                $kwh_usage = $tariffs->tariff_1;
                $standing = $tariffs->tariff_2;

                $this->info("\n---------------------------------------");
                $this->info('Old CUSTOMER balance: '.$customer->balance);
                $dhu = DistrictHeatingUsage::where('customer_id', $customer->id)
                        ->whereRaw("date >= '$start_date' AND date <= '$end_date'")
                        ->orderBy('id', 'ASC')->get();

                foreach ($dhu as $d) {
                    if ($d->standing_charge == 0 || 1 == 1) {
                        $this->info('Found a customer with a 0 standing charge');
                        $this->info('Customer: '.$customer->id.' ('.$customer->username.')');
                        $this->info('DHU Date: '.$d->date.' ('.$d->id.')');
                        $this->info('Old standing: '.$d->standing_charge);
                        $this->info('Old C.O.D: '.$d->cost_of_day);
                        $this->info('Old Unit: '.$d->unit_charge);

                        $d->standing_charge = $standing;
                        $d->unit_charge = $d->total_usage * $kwh_usage;
                        $d->cost_of_day = $d->standing_charge + $d->unit_charge + $d->arrears_repayment;
                        $d->manual = 1;
                        $d->save();

                        //$customer->balance = $customer->balance - $d->cost_of_day;

                        $this->info('New C.O.D: '.$d->cost_of_day);
                        $this->info('New Unit: '.$d->unit_charge);
                        $this->info("\n\n");

                        //$customer->save();
                    }
                }

                $this->info("\n---------------------------------------\n\n");
            }
        } catch (Exception $e) {
            $this->info('Error: '.'Line '.$e->getLine().': '.$e->getMessage());
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    public function usageFromBillingEngineLogs($customer, $date, $scheme_id)
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

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }
}
