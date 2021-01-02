<?php
use Illuminate\Database\Eloquent\Model;

class DistrictHeatingUsage extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'district_heating_usage';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $primaryKey = 'id';

    public function scopeWithEVMeterID($query, $meterID)
    {
        return $query->where('ev_meter_ID', $meterID);
    }

    public function getDefaultStandingChargeAttribute()
    {
        return Tariff::where('scheme_number', $this->scheme_number)->first()->tariff_2;
    }

    public function getkWhUsageTariffAttribute()
    {
        return Tariff::where('scheme_number', $this->scheme_number)->first()->tariff_1;
    }

    public function getOtherCharges()
    {
        return abs($this->cost_of_day - (($this->total_usage * $this->kWh_usage_tariff) + ($this->standing_charge) + ($this->arrears_repayment)));
    }

    public static function getUsage($customer, $date)
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
        $usage = 0;

        if (file_exists($filename)) {
            foreach (file($filename) as $line) {
                $c1 = 'Customer '.$customer.' Old Balance';

                if (strpos($line, $c1) !== false) {
                    $parts = explode(' ', $line);
                    $usage += floatval($parts[14]);
                }
            }
        }

        return $usage;
    }

    public static function getEndReading($customer, $date)
    {
        $today = new DateTime($date);
        $tommorow = new DateTime($today->format('Y-m-d').' + 1 day');
    }

    public static function usageFromBillingEngineLogs($customer, $date)
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
        $tariff_1 = Tariff::where('scheme_number', $scheme_id)->first()->tariff_1;

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

    public function getCustomerAttribute()
    {
        return Customer::find($this->customer_id);
    }

    public function backup()
    {
    }

    public function spreadCharges($days)
    {
        $todays_date = new DateTime(date('Y-m-d'));
        $todays_date = $todays_date->format('Y-m-d');
        $yesterdays_date = new DateTime(date('Y-m-d'));
        $yesterdays_date = $yesterdays_date->modify('- 1 days');
        $yesterdays_date = $yesterdays_date->format('Y-m-d');

        $missing_days = false;
        $kwh_per = floor($this->total_usage / $days);
        $total_processed = 0;

        $affected_dhu = [];

        for ($i = $days - 1; $i > -1; $i--) {
            $date = new DateTime($this->date);
            $date = $date->modify("- $i days");
            $date = $date->format('Y-m-d');

            $dhu = self::where('customer_id', $this->customer_id)
            ->where('date', $date)->first();

            if (! $dhu) {
                $missing_days = true;
            }
        }

        if ($missing_days) {
            return;
        }

        for ($i = $days - 1; $i > -1; $i--) {
            $date = new DateTime($this->date);
            $date = $date->modify("- $i days");
            $date = $date->format('Y-m-d');

            $dhu = self::where('customer_id', $this->customer_id)
            ->where('date', $date)->first();

            array_push($affected_dhu, $dhu->id);

            if ($i == 0) {
                $dhu->start_day_reading = $dhu->prevDay->end_day_reading;
                $dhu->save();
                $dhu->recalculate();
            } else {
                if ($dhu) {
                    $dhu->spread_from = $this->id;
                    $dhu->end_day_reading += $kwh_per;
                    $dhu->recalculate();

                    $dhuNext = $dhu->nextDay;
                    if ($dhuNext && $dhuNext->date != $this->date) {
                        $dhuNext->start_day_reading = $dhu->end_day_reading;
                        $dhuNext->end_day_reading = $dhuNext->start_day_reading + $dhuNext->total_usage;
                        $dhuNext->save();
                        $dhuNext->recalculate();
                    }

                    $total_processed++;
                }
            }

            if ($dhu->date == $yesterdays_date) {
                $customer = $dhu->customer;
                if ($customer) {
                    $customer->used_yesterday = $dhu->cost_of_day;
                    $customer->save();
                }
            }

            if ($dhu->date == $todays_date) {
                $customer = $dhu->customer;
                if ($customer) {
                    $customer->used_today = $dhu->cost_of_day;
                    $customer->save();
                }
            }
        }

        $customer = $this->customer;
        $customer_id = 0;
        if ($customer) {
            $customer_id = $customer->id;
        }

        $entry = new EngineBillingLog();
        $entry->operator_id = Auth::user()->id;
        $entry->customer_id = $customer_id;
        $entry->type = 'billing_spread';
        $entry->message = 'Spread charge #'.$this->id." over $days days, in chunks of $kwh_per kWh";
        $entry->save();

        return $affected_dhu;
    }

    public function recalculate()
    {
        $tariff = Tariff::where('scheme_number', $this->scheme_number)->first();

        if (! $tariff) {
            return;
        }

        $per_kwh = $tariff->tariff_1;
        $standing = $tariff->tariff_2;

        $this->total_usage = abs($this->end_day_reading - $this->start_day_reading);
        $this->unit_charge = ($this->total_usage * $per_kwh);
        $this->cost_of_day = $this->unit_charge + $this->standing_charge + $this->arrears_repayment;
        $this->save();
    }

    public function getNextDayAttribute()
    {
        $date = new DateTime($this->date);
        $date = $date->modify('+ 1 day');
        $date = $date->format('Y-m-d');

        $next = self::where('customer_id', $this->customer_id)
        ->where('date', $date)->orderBy('id', 'ASC')->first();

        return $next;
    }

    public function getPrevDayAttribute()
    {
        $date = new DateTime($this->date);
        $date = $date->modify('- 1 day');
        $date = $date->format('Y-m-d');

        $prev = self::where('customer_id', $this->customer_id)
        ->where('date', $date)->orderBy('id', 'ASC')->first();

        return $prev;
    }

    public function getPrevAttribute()
    {
        return $this->prevDay;
    }
}
