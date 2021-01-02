<?php

class BillingEngineLogsNew
{
    public $file;
    public $folder;
    public $customer_id;
    public $customer;
    public $date;
    public $entries;
    public $charges;
    public $entry_exists;
    public $charge_total = 0;
    public $total_usage = 0;

    public $unit_charge = 0;

    public $start_day_reading = 0;
    public $end_day_reading = 0;

    public function __construct($customer_id, $date)
    {
        try {
            $this->customer_id = $customer_id;
            $this->customer = Customer::find($customer_id);
            $this->date = $date;
            $this->file = "/var/www/app/storage/backups/billing_engine/Customer $customer_id/$date.txt";
            $this->folder = "/var/www/app/storage/backups/billing_engine/Customer $customer_id/";
            $this->entries = [];
            $this->charges = [];

            if (file_exists($this->file)) {
                $this->entry_exists = true;
            } else {
                $this->entry_exists = false;
            }

            $this->getEntries();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function getEntries()
    {
        try {
            $i = -1;

            if (! $this->entry_exists) {
                return;
            }

            foreach (file($this->file) as $line) {
                if (strpos($line, '[') !== false) {
                    $i++;
                    $time = explode(']', explode('[', $line)[1])[0];
                    $data = explode(']', $line)[1];
                    $this->entries[$i] = (object) [
                    'time' => $time,
                    'data' => $data,
                    'raw_data' => $line,
                ];
                } else {
                    $data = $line;
                    $this->entries[$i]->data .= $data;
                    $this->entries[$i]->raw_data .= $data;
                }
            }

            $this->parseEntries();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function parseEntries()
    {
        foreach ($this->entries as $e) {
            $data = $e->data;

            if (strpos($data, 'Usage: ') !== false) {
                $lines = explode(PHP_EOL, $data);

                $time = $e->time;
                $usage = str_replace(' kWh.', '', str_replace('Usage: ', '', $lines[1]));
                $billed = str_replace('', '', str_replace('Amount to bill: €', '', $lines[2]));
                $sudo_reading = str_replace(' kWh', '', str_replace('Sudo Reading: ', '', $lines[3]));
                $latest_reading = str_replace(' kWh', '', str_replace('Latest Reading: ', '', $lines[4]));
                $per_kwh = str_replace('', '', str_replace('Per kWh: €', '', $lines[5]));
                $total_used_today = str_replace('', '', str_replace('Total used today: ', '', $lines[6]));

                if ($this->start_day_reading == 0) {
                    $this->start_day_reading = $latest_reading;
                }

                $this->end_day_reading = $sudo_reading;

                if (strpos($data, 'Refunded') !== false) {
                    $balance_before = explode(' -> ', str_replace('Balance change: ', '', $lines[8]))[0];
                    $balance_after = explode(' -> ', str_replace('Balance change: ', '', $lines[8]))[1];
                } else {
                    $balance_before = explode(' -> ', str_replace('Balance change: ', '', $lines[7]))[0];
                    $balance_after = explode(' -> ', str_replace('Balance change: ', '', $lines[7]))[1];
                }

                $status = 'applied';
                $dhu = null;

                try {
                    $dhu = DistrictHeatingUsage::where('date', $this->date)->where('customer_id', $this->customer_id)->first();

                    $charge = ($balance_before - $balance_after);

                    $this->charge_total += ($balance_before - $balance_after);
                    $this->total_usage += $usage;
                    $this->unit_charge += ($balance_before - $balance_after);

                    if (strpos($data, 'Refunded') !== false) {
                        $status = 'refunded';
                    }
                } catch (Exception $e) {
                }

                $this->charges[] = (object) [
                    'type'	 => 'normal',
                    'time' => Carbon\Carbon::parse($time),
                    'usage' => $usage,
                    'sudo_reading' => $sudo_reading,
                    'latest_reading' => $latest_reading,
                    'billed' => $billed,
                    'balance_before' => $balance_before,
                    'balance_after' => $balance_after,
                    'dhu'			=> $dhu,
                    'status'		=> $status,
                    'charge'		=> $charge,
                    'data'			=> $e->raw_data,
                ];
            } elseif ((strpos($data, 'Applied standing charge') !== false && strpos($data, 'New Balance') !== false) || (strpos($data, 'Applied MISSED standing charge') !== false && strpos($data, 'New Balance') !== false)) {
                $time = $e->time;

                if (strpos($data, 'MISSED')) {
                    $billed = explode(' to Customer ', explode('Applied MISSED standing charge of €', $data)[1])[0];
                } else {
                    $billed = explode(' to Customer ', explode('Applied standing charge of €', $data)[1])[0];
                }

                $balance_after = explode('\n', explode('New Balance €', $data)[1])[0];
                $balance_before = $balance_after + $billed;
                $status = 'applied';
                $dhu = null;

                $dhu = DistrictHeatingUsage::where('date', $this->date)->where('customer_id', $this->customer_id)->first();

                $this->charge_total += ($balance_before - $balance_after);

                if (strpos($data, 'Refunded') !== false) {
                    $status = 'refunded';
                }

                $this->charges[] = (object) [
                    'type' => 'standing',
                    'time' => Carbon\Carbon::parse($time),
                    'balance_before' => $balance_before,
                    'balance_after' => $balance_after,
                    'billed' => $billed,
                    'charge' => $billed,
                    'status' => $status,
                    'dhu' => $dhu,
                    'data'	=> $e->raw_data,
                ];
            }
        }
    }

    public function refundCharge($charge_id)
    {
        $res = [
            'found' => false,
            'balance_before' => $this->customer->balance,
            'balance_after' => '0.00',
        ];

        foreach ($this->charges as $key => $c) {
            if ($key == $charge_id) {
                try {
                    $refunding_amount = $c->charge;

                    //read the entire string
                    $str = file_get_contents($this->file);

                    if ($c->type == 'standing') {
                        $replaced_section = str_replace('New Balance', 'Refunded. New Balance', $c->data);
                    } else {
                        $replaced_section = str_replace('Balance change:', "Refunded: true\nBalance change:", $c->data);
                    }

                    //replace something in the file string - this is a VERY simple example
                    $str = str_replace($c->data, $replaced_section, $str);

                    //write the entire string
                    file_put_contents($this->file, $str);

                    $res['found'] = true;
                    $res['amount'] = $refunding_amount;
                    $res['date'] = $c->time;
                } catch (Exception $e) {
                    $res['error'] = $e->getMessage();

                    return $res;
                }

                $this->customer->balance += abs($refunding_amount);
                $this->customer->save();

                $res['balance_after'] = $this->customer->balance;
            }
        }

        return $res;
    }

    public function reissueCharge($charge_id)
    {
        $res = [
            'found' => false,
            'balance_before' => $this->customer->balance,
            'balance_after' => '0.00',
        ];

        foreach ($this->charges as $key => $c) {
            if ($key == $charge_id) {
                try {
                    $charge_amount = $c->charge;

                    //read the entire string
                    $str = file_get_contents($this->file);

                    if ($c->type == 'standing') {
                        $replaced_section = str_replace(' Refunded. ', ' ', $c->data);
                    } else {
                        $replaced_section = str_replace("\nRefunded: true\n", "\n", $c->data);
                    }

                    //replace something in the file string - this is a VERY simple example
                    $str = str_replace($c->data, $replaced_section, $str);

                    //write the entire string
                    file_put_contents($this->file, $str);

                    $res['found'] = true;
                    $res['amount'] = $charge_amount;
                    $res['date'] = $c->time;
                } catch (Exception $e) {
                    $res['error'] = $e->getMessage();

                    return $res;
                }

                $this->customer->balance -= abs($charge_amount);
                $this->customer->save();
                $res['balance_after'] = $this->customer->balance;
            }
        }

        return $res;
    }

    public function download()
    {
        $zip_file_name = 'customer_'.$this->customer_id.'_'.$this->date.'_billing_logs.zip';
        $zip_file_directory = "/var/www/html/exports/customer_billing/$zip_file_name";
        $folder_to_zip = $this->folder;
        $zip_command = "zip -r -j '$zip_file_directory' '$folder_to_zip'";
        exec("cd /var/www/html/exports; mkdir -m 777 /var/www/html/exports/customer_billing; cd /customer_billing; $zip_command", $output, $result);

        return "https://prepago-admin.biz/exports/customer_billing/$zip_file_name";
    }

    public static function getMainLog($date)
    {
        $file = "/var/www/app/storage/backups/billing_engine/$date.txt";

        return file_get_contents($file);
    }

    public static function getLogRange($customer_id, $from, $to)
    {
        $logs = [];

        $to = (new DateTime($to));
        $to = $to->modify('+ 1 day');
        $to = $to->format('Y-m-d');

        while ($from != $to) {
            $logs[$from] = new self($customer_id, $from);

            $from = (new DateTime($from));
            $from = $from->modify('+ 1 day');
            $from = $from->format('Y-m-d');
        }

        return $logs;
    }

    public static function getLogs($customer_id, $from)
    {
        return new self($customer_id, $from);
    }

    public static function getLog($customer_id, $from)
    {
        return new self($customer_id, $from);
    }
}
