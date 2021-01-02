<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\Paypal;
use App\Models\Stripe\StripeCustomer;
use App\Models\Stripe\StripeCustomerFailedPayment;
use App\Models\Stripe\StripeCustomerPayment;
use App\Models\Stripe\StripeErrorLog;
use App\Models\Stripe\StripeLog;
use App\Models\Stripe\StripePaymentSource;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;


class PaymentController extends Controller
{
    protected $layout = 'layouts.admin_website';

    // For other API
    private $prepago_secret_key = '123abc';
    private $incoming_secret_key = 'd1b20be1-c97d-4d7e-bf61-cdf32eb779dd';

    public function fm()
    {
        $this->layout->page = view('fm', [

        ]);
    }

    public function fm_email()
    {
        try {
            $song = Input::get('song');
            $duration = Input::get('duration');

            Email::quick_send("<font style='font-size:1.4rem'>Hi Daniel,<br/><br/>Your song '<b>$song</b>' has just played.
			<br/><br/>It has a duration of:<br/>$duration.<br/><br/>Get your phone ready to text 98fm <b>now!!</b>.</font>",
            "98FM - Competition: Your Song just played! ($song) ".date('Y-m-d H:i:s').'',
            'itsdanieln@gmail.com', 'info@prepago.ie', 'Daniel Monitors');

            return Response::json([
                'sent' => true,
            ]);
        } catch (Exception $e) {
        }
    }

    public function getCapturesFromPaypal($id)
    {
        $token = $this->getToken();
        $req_link = "https://api.paypal.com/v2/payments/captures/$id";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $req_link);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '.$token,
            'Accept: application/json',
            'Content-Type: application/json',
        ]);

        $result = json_decode(curl_exec($ch));

        var_dump($result);

        die();

        $payments = [];

        foreach ($result->payments as $p) {
            $id = $p->id;
            $time = $p->create_time;
            $state = $p->state;
            $transactions = (array) $p->transactions;
            $amount_obj = (object) $transactions[0]->amount;
            $total = $amount_obj->total;
            $serialized = serialize($p);
            $time_formatted = explode('T', $time)[0].' '.str_replace('Z', '', explode('T', $time)[1]);

            $payer = (object) $p->payer;
            $payer_info = (object) $payer->payer_info;
            $email = $payer_info->email;
            $first_name = $payer_info->first_name;
            $last_name = $payer_info->last_name;
            $name = $first_name.' '.$last_name;

            array_push($payments, [

                'id' => $id,
                'time' => $time,
                'time_formatted' => $time_formatted,
                'total' => $total,
                'state' => $state,
                'email' => $email,
                'name' => $name,

            ]);
        }

        return Response::json($payments);
    }

    public function getPaymentsFromPaypal($key, $from = null, $to = null)
    {
        if ($key != $this->prepago_secret_key && 1 != 1) {
            return Response::json([
                'error' => 'Invalid secret key',
            ]);
        }

        if (strpos($from, 'T') === false || strpos($to, 'T') === false || strpos($from, 'Z') === false || strpos($to, 'Z') === false) {
            $datetime_from = new DateTime($from);
            $datetime_to = new DateTime($to);

            $from = $datetime_from->format('Y-m-d').'T00:00:00Z';
            $to = $datetime_to->format('Y-m-d').'T23:59:59Z';
        }

        echo 'From: '.$from."\n";
        echo 'To: '.$to."\n\n";

        $token1 = Paypal::getToken('noreply');

        $req_link = "https://api.paypal.com/v1/payments/payment?count=200&sort_by=create_time&start_time=$from&end_time=$to";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $req_link);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '.$token1,
            'Accept: application/json',
            'Content-Type: application/json',
        ]);

        $result = json_decode(curl_exec($ch));

        echo "noreply@snugzone.biz\n";
        if (! isset($result->payments) || empty($result->payments)) {
            return Response::json([
                'error' => "No payments for the range $from -> $to",
            ]);
        }

        $payments = [];

        foreach ($result->payments as $p) {
            $id = $p->id;
            $time = $p->create_time;
            $state = $p->state;
            $transactions = (array) $p->transactions;
            $amount_obj = (object) $transactions[0]->amount;
            $total = $amount_obj->total;
            $serialized = serialize($p);
            $time_formatted = explode('T', $time)[0].' '.str_replace('Z', '', explode('T', $time)[1]);

            $payer = (object) $p->payer;
            $payer_info = (object) $payer->payer_info;
            $email = $payer_info->email;
            $first_name = $payer_info->first_name;
            $last_name = $payer_info->last_name;
            $name = $first_name.' '.$last_name;

            array_push($payments, [

                'id' => $id,
                'time' => $time,
                'time_formatted' => $time_formatted,
                'total' => $total,
                'state' => $state,
                'email' => $email,
                'name' => $name,

            ]);
        }

        echo Response::json($payments);

        echo "\n\n===========================================\n\n";

        /////

        $token1 = Paypal::getToken('accounts');

        $req_link = "https://api.paypal.com/v1/payments/payment?count=200&sort_by=create_time&start_time=$from&end_time=$to";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $req_link);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '.$token1,
            'Accept: application/json',
            'Content-Type: application/json',
        ]);

        $result = json_decode(curl_exec($ch));

        if (! isset($result->payments) || empty($result->payments)) {
            return Response::json([
                'error' => "No payments for the range $from -> $to",
            ]);
        }

        $payments = [];

        foreach ($result->payments as $p) {
            $id = $p->id;
            $time = $p->create_time;
            $state = $p->state;
            $transactions = (array) $p->transactions;
            $amount_obj = (object) $transactions[0]->amount;
            $total = $amount_obj->total;
            $serialized = serialize($p);
            $time_formatted = explode('T', $time)[0].' '.str_replace('Z', '', explode('T', $time)[1]);

            $payer = (object) $p->payer;
            $payer_info = (object) $payer->payer_info;
            $email = $payer_info->email;
            $first_name = $payer_info->first_name;
            $last_name = $payer_info->last_name;
            $name = $first_name.' '.$last_name;

            array_push($payments, [

                'id' => $id,
                'time' => $time,
                'time_formatted' => $time_formatted,
                'total' => $total,
                'state' => $state,
                'email' => $email,
                'name' => $name,

            ]);
        }

        echo "noreply@snugzone.biz\n";
        echo Response::json($payments);

        return 'done';
    }

    public function view_incoming($key, $date = null)
    {
        if ($date == null) {
            $date = date('Y-m-d');
        }

        $date = str_replace(' ', '-', $date);
        $date = str_replace('_', '-', $date);

        $filename = '/var/www/app/storage/logs/payment_api_new/'.$date.'.txt';

        //header('Content-Type: text/plain');

        $myfile = fopen($filename, 'r') or die('Unable to open file!');
        $data = fread($myfile, filesize($filename));
        fclose($myfile);

        return nl2br($data);
    }

    public function incoming($key)
    {
        try {
            if ($key != $this->incoming_secret_key) {
                return Response::json([
                'success' => '',
                'error' => 'Invalid API Key.',
            ]);
            }

            $todays_date = date('Y-m-d');

            $input = 'Server time: '.$todays_date.' '.date('H:i:s')."\n";
            $input .= 'Raw Data: '.file_get_contents('php://input')."\n";

            if (! empty($_POST)) {
                $input .= 'POST Data: '.serialize($_POST)."\n";

                foreach ($_POST as $key=>$value) {
                    $input .= "$key: $value\n";
                }
            }

            $input .= "\n\n";

            $this->logEntry($input, '/var/www/app/storage/logs/payment_api_new/'.$todays_date.'.txt', false);

            Email::quick_send("Received new API Request: <br/> <a href='http://prepago-admin.biz/prepago_api/view/d1b20be1-c97d-4d7e-bf61-cdf32eb779dd'>Click here to view it</a>", 'Title', 'daniel@prepago.ie', 'support@prepago.ie', 'Prepago Support');

            return Response::json([
                'success' => 'success',
                'error' => '',
            ]);
        } catch (Exception $e) {
            return Response::json([
                'success' => '',
                'error' => 'Exception in code: '.$e->getMessage(),
            ]);
        }
    }

    private function logEntry($data, $directory, $error = false)
    {
        if ($directory == null || $directory == '') {
            return;
        }

        if (! file_exists(dirname($directory))) {
            mkdir(dirname($directory), 0777, true);
        }

        $myfile = fopen($directory, 'a') or die('Unable to open file!');

        $txt = $data;

        fwrite($myfile, $txt);

        fclose($myfile);
    }

    // New

    public function payment_settings()
    {
        $stripeCustomers = StripeCustomer::orderBy('id', 'DESC')->get();
        $stripeSources = StripePaymentSource::orderBy('id', 'DESC')->get();
        $stripeLogs = StripeLog::orderBy('id', 'DESC')->get();
        $stripeErrorLogs = StripeErrorLog::orderBy('id', 'DESC')->get();
        $stripePayments = StripeCustomerPayment::orderBy('id', 'DESC')->get();
        $stripeFailedPayments = StripeCustomerFailedPayment::orderBy('id', 'DESC')->get();

        $this->layout->page = view('settings.payments.payment_settings', [
            'stripeCustomers' 	=> $stripeCustomers,
            'stripeSources' 	=> $stripeSources,
            'stripeLogs' 		=> $stripeLogs,
            'stripeErrorLogs' 	=> $stripeErrorLogs,
            'stripePayments' 	=> $stripePayments,
            'stripeFailedPayments' 	=> $stripeFailedPayments,
        ]);
    }
}
