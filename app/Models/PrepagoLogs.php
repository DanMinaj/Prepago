<?php

class pLog
{
    public $date;
    public $type;
    public $data;
    public $customer;

    public function __construct($date, $type, $data, $customer)
    {
        $this->date = $date;
        $this->type = $type;
        $this->data = $data;
        $this->customer = $customer;
    }
}

class pLogPackage
{
    public $type;
    public $date;
    public $customer;
    public $logs;

    public function __construct($type, $date, $customer)
    {
        $this->type = $type;
        $this->date = $date;
        $this->customer = $customer;
        $this->logs = [];
    }

    public function addLog($the_data, $the_customer)
    {
        $pLog = new pLog($this->date, $this->type, $the_data, $the_customer);
        array_push($this->logs, $pLog);
    }

    public function totalCharge()
    {
        $date = $this->date;
        $parts = explode('-', $date);
        $year = $parts[0];
        $month = $parts[1];
        $day = $parts[2];
        $type = 'billing_engine';
        $date_ = str_replace('-', '_', $date);

        $total = 0;

        $filename = "/opt/prepago_engine/prepago_engine/logs/$year/$month/$type/$date_.txt";

        $c1 = 'Testing Customer '.$this->customer;
        $c2 = 'Customer ID: '.$this->customer;
        $c3 = 'Customer '.$this->customer;

        foreach (file($filename) as $line) {
            if (strpos(strtolower($line), 'error')) {
                $line = "<font color='red'>$line</font>";
            }

            $c1 = 'Customer '.$this->customer.' Old Balance';
            $c2 = 'Customer '.$this->customer.' billed';

            if (strpos($line, $c1) === false) {
                if (strpos($line, $c2) === false || strpos($line, 'daily tariff') === false) {
                    continue;
                } else {
                    $parts_1 = explode(' ', $line);
                    $standing = floatval($parts_1[7]);
                    $residual_yesterday_charge = floatval($parts_1[3]);
                    $total += $standing;
                    $total += $residual_yesterday_charge;
                }

                continue;
            }

            $parts = explode(' ', $line);
            $old_balance = floatval($parts[7]);
            $new_balance = floatval($parts[11]);
            $the_usage = floatval($parts[14]);
            $billed = $old_balance - $new_balance;
            $total += $billed;
        }

        return $total;
    }

    public function missedStandingCharge()
    {
        return $this->totalCharge() == 0;
    }

    public function entries_num()
    {
        return count($this->logs);
    }

    public function missed_standing_num()
    {
        $hit_end = false;
        $count = 0;

        foreach ($this->logs as $log) {
            if (strpos($log->data, 'run type 2') !== false) {
                break;
            } elseif (strpos($log->data, 'MySQL server has gone away.') !== false) {
                $count++;
            }
        }

        return "$count customers missed the standing charge on ".$this->date;
    }
}

class PrepagoLogs
{
    public static function billingLogs($date, $customer_id = null)
    {
        $log_package = new pLogPackage('billing_engine', $date, $customer_id);

        $parts = explode('-', $date);
        $year = $parts[0];
        $month = $parts[1];
        $day = $parts[2];
        $type = 'billing_engine';
        $date_ = str_replace('-', '_', $date);

        $filename = "/opt/prepago_engine/prepago_engine/logs/$year/$month/$type/$date_.txt";

        $c1 = 'Testing Customer '.$customer_id;
        $c2 = 'Customer ID: '.$customer_id;
        $c3 = 'Customer '.$customer_id;

        foreach (file($filename) as $line) {
            if ($customer_id != null) {
                if (strpos($line, $c1) === false && strpos($line, $c2) === false && strpos($line, $c3) === false) {
                    continue;
                }
            }

            $log_package->addLog($line, $customer_id);
        }

        return $log_package;
    }

    public static function billingErrors($date, $customer_id = null)
    {
        $log_package = new pLogPackage('billing_engine', $date, $customer_id);

        $parts = explode('-', $date);
        $year = $parts[0];
        $month = $parts[1];
        $day = $parts[2];
        $type = 'billing_engine';
        $date_ = str_replace('-', '_', $date);

        $filename = "/opt/prepago_engine/prepago_engine/logs/$year/$month/$type/$date_.txt";

        $c1 = 'error';
        $c2 = 'Customer ID: '.$customer_id;

        foreach (file($filename) as $line) {
            if ($customer_id != null) {
                if (strpos($line, $c1) === false || strpos($line, $c2) === false) {
                    continue;
                }
            } else {
                if (strpos($line, $c1) === false) {
                    continue;
                }
            }

            $log_package->addLog($line, $customer_id);
        }

        return $log_package;
    }
}
