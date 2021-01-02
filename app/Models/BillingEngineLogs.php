<?php

namespace App\Models;


class logPackage
{
    public $customer;
    public $customer_obj;
    public $date;
    public $parts;
    public $year;
    public $month;
    public $day;
    public $log_file;

    public function __construct($customer, $date)
    {
        try {
            $this->customer = $customer;
            $this->customer_obj = Customer::find($this->customer);
            $this->date = str_replace('-', '_', $date);
            $this->parts = explode('_', $this->date);
            $this->year = $this->parts[0];
            $this->month = $this->parts[1];
            $this->day = $this->parts[2];
            $this->log_file = $this->date.'.txt';
        } catch (Exception $e) {
        }
    }

    public function logExists()
    {
        $filename = '/opt/prepago_engine/prepago_engine/logs/'.$this->year.'/'.$this->month.'/billing_engine/'.$this->log_file;

        return file_exists($filename);
    }

    public function kwhUsage()
    {
        $filename = '/opt/prepago_engine/prepago_engine/logs/'.$this->year.'/'.$this->month.'/billing_engine/'.$this->log_file;
        $total = 0;

        foreach (file($filename) as $line) {
            if (strpos($line, '***********') !== false) {
                if (strpos($line, 'Customer '.$this->customer.' Old') !== false) {
                    $total += intval(explode(', usage = ', $line)[1]);
                }
            }
        }

        return $total;
    }

    public function arrearsCharge()
    {
        $filename = '/opt/prepago_engine/prepago_engine/logs/'.$this->year.'/'.$this->month.'/billing_engine/'.$this->log_file;
        $total = 0;

        foreach (file($filename) as $line) {
            if (strpos($line, 'Customer '.$this->customer.' has arrears repayment ') !== false) {
                $p1 = explode(' repayment ', $line)[1];
                $p2 = explode(' for ', $p1)[0];
                $total = $p2;
            }
        }

        return $total;
    }

    public function standingCharge()
    {
        $tariff = Tariff::where('scheme_number', $this->customer_obj->scheme_number)->first();

        if ($tariff) {
            return $tariff->tariff_2;
        } else {
            return 0;
        }
    }

    public function unitCharge()
    {
        $tariff = Tariff::where('scheme_number', $this->customer_obj->scheme_number)->first();

        if ($tariff) {
            return $tariff->tariff_1 * ($this->kwhUsage());
        } else {
            return 0;
        }
    }

    public function costOfDay()
    {
        $kwhCharge = $this->unitCharge();
        $standingCharge = $this->standingCharge();
        $arrearsCharge = $this->arrearsCharge();

        return $kwhCharge + $standingCharge + $arrearsCharge;
    }
}

class BillingEngineLogs
{
    public static function logExists($customer, $date)
    {
        $logPackage = new logPackage($customer, $date);

        return $logPackage->logExists();
    }

    public static function getPackage($customer, $date)
    {
        return new logPackage($customer, $date);
    }

    public static function getKwh($customer, $date)
    {
        $logPackage = new logPackage($customer, $date);

        return $logPackage->kwhUsage();
    }

    public static function getStanding($customer, $date)
    {
        $logPackage = new logPackage($customer, $date);

        return $logPackage->standingCharge();
    }

    public static function getUnitCharge($customer, $date)
    {
        $logPackage = new logPackage($customer, $date);

        return $logPackage->unitCharge();
    }

    public static function getArrearsCharge($customer, $date)
    {
        $logPackage = new logPackage($customer, $date);

        return $logPackage->arrearsCharge();
    }

    public static function getCostOfDay($customer, $date)
    {
        $logPackage = new logPackage($customer, $date);

        return $logPackage->costOfDay();
    }

    public static function getStartDayReading($customer, $date)
    {
        $date = str_replace('_', '-', $date);

        $customer_obj = Customer::find($customer);

        if (! $customer) {
            return 0;
        }

        $pmd = $customer_obj->permanentMeter;

        if (! $pmd) {
            return 0;
        }

        $yesterday_date = (new DateTime($date));
        $yesterday_date->modify('-1 day');
        $yesterday_date = $yesterday_date->format('Y-m-d');

        $dhu_yesterday = DistrictHeatingUsage::where('customer_id', $customer)->where('date', $yesterday_date)->first();

        $generated_end_day = self::getEndDayReading($customer, $yesterday_date);

        if ($dhu_yesterday) {
            if ($generated_end_day < $dhu_yesterday->end_day_reading) {
                return $dhu_yesterday->end_day_reading;
            }
        }

        return $generated_end_day;
    }

    public static function getEndDayReading($customer, $date)
    {
        $date = str_replace('_', '-', $date);

        $customer_obj = Customer::find($customer);

        if (! $customer) {
            return 0;
        }

        $pmd = $customer_obj->permanentMeter;

        if (! $pmd) {
            return 0;
        }

        $pmdall = PermanentMeterDataReadingsAll::where('permanent_meter_id', $pmd->ID)
        ->where('time_date', 'like', '%'.$date.'%')
        ->orderBy('ID', 'DESC')
        ->first();

        if (! $pmdall) {
            return 0;
        }

        if ($pmdall->reading1 <= 0) {
            return 0;
        }

        return $pmdall->reading1;
    }
}
