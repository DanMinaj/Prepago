<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DistrictHeatingUsage;
use App\Models\MBusAddressTranslation;
use App\Models\Tariff;
use App\Models\TemporaryPayments;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Whoops\Example\Exception;



class TestRouteController extends Controller
{
    public function getCustomerInfo($username)
    {
        if (Auth::user()->username != 'test') {
            return;
        }

        $customer = Customer::where('username', $username)->first();

        if ($customer) {
            $permanentMeter = $customer->permanentMeter()->get16Digit();

            echo $permanentMeter;

            return;

            Response::json($customer);
        }
    }

    public function encryptAllUsers()
    {
        $customers = Customer::get(['username', 'id', 'first_name', 'surname', 'email_address', 'mobile_number']);
        $encrypted_records = 0;

        foreach ($customers as $c) {
            if (strlen($c->first_name) > 1) {
                $encrypted_fname = Crypt::encrypt($c->first_name);
                $encrypted_records++;
            } else {
                $encrypted_fname = $c->first_name;
            }

            if (strlen($c->surname) > 1) {
                $encrypted_lname = Crypt::encrypt($c->surname);
                $encrypted_records++;
            } else {
                $encrypted_lname = $c->surname;
            }

            if (strlen($c->email_address) > 1) {
                $encrypted_email = Crypt::encrypt($c->email_address);
                $encrypted_records++;
            } else {
                $encrypted_email = $c->email_address;
            }

            if (strlen($c->mobile_number) > 1) {
                $encrypted_mobile = Crypt::encrypt($c->mobile_number);
                $encrypted_records++;
            } else {
                $encrypted_mobile = $c->mobile_number;
            }

            echo 'Customer: '.$c->id.'<br/>';
            echo 'Encrypted firstname: '.$encrypted_fname.'<br/>';
            echo 'Encrypted surname: '.$encrypted_lname.'<br/>';
            echo 'Encrypted email: '.$encrypted_email.'<br/><br/>';
            echo 'Encrypted mobile: '.$encrypted_mobile.'<br/><br/>';

            $c->first_name = $encrypted_fname;
            $c->surname = $encrypted_lname;
            $c->email_address = $encrypted_email;
            $c->mobile_number = $encrypted_mobile;
            $c->save();
        }

        echo "Total encrypted records: $encrypted_records";
    }

    public function decryptAllUsers()
    {
        $customers = Customer::get(['username', 'id', 'first_name', 'surname', 'email_address', 'mobile_number']);

        $decrypted_records = 0;

        foreach ($customers as $c) {
            try {
                if (strlen($c->first_name) > 45) {
                    $decrypted_fname = Crypt::decrypt($c->first_name);
                    $decrypted_records++;
                } else {
                    $decrypted_fname = $c->first_name;
                }

                if (strlen($c->surname) > 45) {
                    $decrypted_lname = Crypt::decrypt($c->surname);
                    $decrypted_records++;
                } else {
                    $decrypted_lname = $c->surname;
                }

                if (strlen($c->email_address) > 45) {
                    $decrypted_email = Crypt::decrypt($c->email_address);
                    $decrypted_records++;
                } else {
                    $decrypted_email = $c->email_address;
                }

                if (strlen($c->mobile_number) > 45) {
                    $decrypted_mobile = Crypt::decrypt($c->mobile_number);
                    $decrypted_records++;
                } else {
                    $decrypted_mobile = $c->mobile_number;
                }
            } catch (Exception $e) {
            }
            $c->first_name = $decrypted_fname;
            $c->surname = $decrypted_lname;
            $c->email_address = $decrypted_email;
            $c->mobile_number = $decrypted_mobile;
            $c->save();
        }

        echo "Total encrypted records: $decrypted_records";
    }

    public function testEncrypt($toencrypt)
    {
        $encrypted = Crypt::encrypt($toencrypt);
        echo $encrypted;
    }

    public function testDecrypt($encrypted)
    {
        $decrypted = Crypt::decrypt($encrypted);
        echo $decrypted;
    }

    // Used to generate passwords for utility company login details
    public function make_hash($password, $other = false)
    {
        if ($other) {
            echo sha1($password);
        } else {
            echo Hash::make($password);
        }
    }

    public function mysql_usage_info()
    {
        exec('ps aux | grep mysql', $output);
        foreach ($output as $o) {
            $parts = preg_split('/ +/', $o);
            $name = $parts[0];
            if ($name != 'mysql') {
                continue;
            }

            $pid = $parts[1];
            $cpu = $parts[2];
            $mem = $parts[3];

            echo 'MySQL is currently utilizing: <br/><br/>';
            echo "CPU Usage: $cpu/100 % <br/>";
            echo "Memory Usage: $mem/100 % <br/>";

            echo '<hr><br/>'.$o.'<br/><br/>';
        }
    }

    public function elvaco_rest()
    {
    }

    public function paypoint_totals()
    {
        $topupFrom = '2017-05-18';
        $topupTo = '2018-05-18';

        $paypointTopups = TemporaryPayments::whereBetween('time_date', [$topupFrom.' 00:00:00', $topupTo.' 23:59:59'])->get();

        $total_amount = 0;
        $total = 0;

        $mar_total_amount = 0;
        $mar_total = 0;

        $apr_total_amount = 0;
        $apr_total = 0;

        foreach ($paypointTopups as $topup) {
            if (strpos($topup->time_date, '2018-03') !== false) {
                $mar_total_amount += $topup->amount;
                $mar_total++;
            }

            if (strpos($topup->time_date, '2018-04') !== false) {
                $apr_total_amount += $topup->amount;
                $apr_total++;
            }

            $total_amount += $topup->amount;
            $total++;
        }

        echo "
		
		<table style='border-collapse:collapse;' border=1 width='20%'>
			
			<tr>
				<td colspan='2'><b><center>($topupFrom - $topupTo) <br/> (12 months)</center></b></td>
			</tr>
			<tr>
				<td> <b>Total topups:</b> </td> <td> $total </td>
			</tr>
			<tr>
				<td> <b>Total amount &euro;</b> </td> <td> &euro;$total_amount </td>
			</tr>
		
		</table>
		
		<br/><br/>
		
		<table border=1 width='20%'>
			
			<tr>
				<td colspan='2'><b><center>March 2018</center></b></td>
			</tr>
			<tr>
				<td> <b>Total topups:</b> </td> <td> $mar_total </td>
			</tr>
			<tr>
				<td> <b>Total amount &euro;</b> </td> <td> &euro;$mar_total_amount </td>
			</tr>
		
		</table>
	
		<br/><br/>
		
		<table border=1 width='20%'>
				
				<tr>
					<td colspan='2'><b><center>April 2018</center></b></td>
				</tr>
				<tr>
					<td> <b>Total topups:</b> </td> <td> $apr_total </td>
				</tr>
				<tr>
					<td> <b>Total amount &euro;</b> </td> <td> &euro;$apr_total_amount </td>
				</tr>
			
			</table>
		";
    }

    public function insert_mbus_address_translations()
    {
        if (isset($_POST['list']) && ! empty($_POST['list'])) {
            $list = $_POST['list'];
            $rows = explode("\r", $list);
            $count = count($rows);
            echo "There are <b>$count</b> mbus_addresses to be inserted!<br/><br/>";
            foreach ($rows as $row) {
                $parts = explode(' ', $row);
                $eight = $parts[0];
                $sixteen = $parts[1];

                $check = MBusAddressTranslation::where('8digit', $eight)->orWhere('16digit', $sixteen);
                if ($check) {
                    echo '<font color="red">Already inserted'.$eight.'|'.$sixteen.'</font><br/>';
                } else {
                    echo '<font color="green">Inserted'.$eight.'|'.$sixteen.'</font><br/>';
                }
            }
        } else {
            echo "
			<form action='' method='POST'>
				<textarea name='list' cols='100' rows='30'></textarea>
				<br/>
				<input type='submit' value='Insert'>
			</form>
			";
        }
    }

    public function prepago_logs_new($type = 'billing', $date = null, $customer = null)
    {
        if ($date == null) {
            $date = date('Y-m-d');
        }

        switch ($type) {

            case 'billing':
            default:

                $file = "/var/www/app/storage/backups/billing_engine/$date.txt";

                return nl2br(file_get_contents($file));

            break;

            case 'shutoff':

                $file = "/var/www/app/storage/backups/shut_off_engine/$date.txt";

                return nl2br(file_get_contents($file));

            break;

        }
    }

    public function prepago_logs($type, $date = null, $customer = null)
    {
        $filename = '';

        try {

            // set it as todays dates by default
            $year = date('Y');
            $month = date('m');
            $day = date('d');

            if ($date != null) {
                $date = str_replace('-', '_', $date);
                $parts = explode('_', $date);
                $year = $parts[0];
                $month = $parts[1];
                $day = $parts[2];
            }

            $date = $year.'_'.$month.'_'.$day;

            $filename = "/opt/prepago_engine/prepago_engine/logs/$year/$month/$type/$date.txt";

            $i = 1;

            echo "<h3>$type logs for ".date('F jS Y', strtotime("$day.$month.$year")).'</h3>';

            $total_usage = 0;
            $total_billed = 0;

            foreach (file($filename) as $line) {
                if (strpos(strtolower($line), 'error')) {
                    $line = "<font color='red'>$line</font>";
                }

                $c1 = 'Testing Customer '.$customer;
                $c2 = 'Customer ID: '.$customer;
                $c3 = 'Customer '.$customer;

                if ($customer != null) {
                    if (strpos($line, $c1) === false && strpos($line, $c2) === false && strpos($line, $c3) === false) {
                        continue;
                    }

                    if (strpos($line, 'usage = ') !== false) {
                        $usage = explode('kWh', explode(' ', $line)[14])[0];
                        $total_usage += $usage;
                    }

                    if (strpos($line, ' billed ') !== false) {
                        $billed = floatval(explode(' ', $line)[4]);
                        $total_billed += $billed;
                        echo " <font color=green>+&euro;$billed</font> ";
                    }

                    //	if(strpos($line, 'aily tariff'
                }

                echo $line.'<br/>';

                $i++;
            }

            $customer_model = Customer::find($customer);

            if ($customer_model) {
                $tariff = Tariff::where('scheme_number', $customer_model->scheme_number)->first();

                if ($tariff) {
                    $standing_charge = $tariff->tariff_2;
                    $unit_charge = $tariff->tariff_1;
                }

                $calc = ($total_usage * $unit_charge) + $standing_charge;
                $date_format = str_replace('_', '-', $date);
                $dh = DistrictHeatingUsage::where('date', $date_format)->where('customer_id', $customer)->first();
                $actual_cost_of_day = $dh->cost_of_day;
                $actual_standing = $dh->standing_charge;
                $actual_usage = $dh->total_usage;

                echo "<h3>Total usage: $total_usage kWh | Total billed: &euro;$total_billed | Standing charge: &euro;$standing_charge | Unit charge: &euro;$unit_charge | Calculated cost of day: &euro;$calc</h3>";

                echo "<h3>Actual total usage: $actual_usage kWh | Actual standing charge: &euro;$actual_standing | Actual cost of day: &euro;$actual_cost_of_day | </h3>";
            }
        } catch (Exception $e) {
            echo "The billing log file for this date doesn't exist.<br/>";
            echo '<b>File not found:</b> '.$filename;
        }
    }

    public function loadSubView($name)
    {
        return view("modals.ajax.$name", [

        ]);
    }
}
