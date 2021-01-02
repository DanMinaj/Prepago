<?php

namespace App\Models;

class Paypal
{
    // accounts (Prepago)
    public static $client_acc = 'AWBKcBCt4HDGo18nUKMQnns6_ZLSELeFeiYvfcgmFjYF-XZUPNNkvnAK75HC';
    public static $secret_acc = 'EHbLZBD8pScI88G7J4BvXljixlmZmro22v_BQftfg6kiAOCFtzGL2PSxyOFZ';
    public static $user_acc = 'accounts_api1.prepago.ie';
    public static $pw_acc = 'KTY99PGG25NQWA9N';
    public static $sig_acc = 'AGdIvmiretX9CTCEUmAsj84ln3MaAnoCVZSHutg4O.dPQzWL9a1B7UdV';

    // noreply (Snugzone)
    public static $client_noreply = 'AdaLyhgyaCNHU1ePx0J-nKXF5sa4yjsvM5TJmUtinNlejuE6sU6Xcnbjx4HwL3MUo1Ji6xkchMYQ6V0E';
    public static $secret_noreply = 'EIp5CMugq88c9FPxdUtrBz6V2ggbYiC2EUrvglBpZqduSZUqshikYoDIpKLRowRe7gbgACxGrGcoZKwP';
    public static $user_noreply = 'noreply_api1.snugzone.biz';
    public static $pw_noreply = 'HA4Q5KQJFR6U8CXH';
    public static $sig_noreply = 'Aw1CiyeJFVe1pRMrQLajmk7mFiVYAf-N51yM5w5Bf9L.x7ph4Q88UqId';

    /**
     * API Version.
     */
    const VERSION = 51.0;

    /**
     * List of valid API environments.
     * @var array
     */
    private $allowedEnvs = [
        'beta-sandbox',
        'live',
        'sandbox',
    ];

    /**
     * Config storage from constructor.
     * @var array
     */
    private $config = [];

    /**
     * URL storage based on environment.
     * @var string
     */
    private $url;

    public $account;

    /**
     * Build PayPal API request.
     *
     * @param string $username
     * @param string $password
     * @param string $signature
     * @param string $environment
     */
    public function __construct($account = 'accounts', $environment = 'live')
    {
        $username = self::$user_acc;
        $password = self::$pw_acc;
        $signature = self::$sig_acc;
        $client = self::$client_acc;
        $secret = self::$secret_acc;
        $this->account = $account;

        if ($account == 'noreply') {
            $username = self::$user_noreply;
            $password = self::$pw_noreply;
            $signature = self::$sig_noreply;
            $client = self::$client_noreply;
            $secret = self::$secret_noreply;
        }

        if (! in_array($environment, $this->allowedEnvs)) {
            throw new Exception('Specified environment is not allowed.');
        }
        $this->config = [
            'account'	  => $account,
            'username'    => $username,
            'password'    => $password,
            'signature'   => $signature,
            'environment' => $environment,
            'client'	  => $client,
            'secret'	  => $secret,
        ];
    }

    /**
     * Make a request to the PayPal API.
     *
     * @param  string $method API method (e.g. GetBalance)
     * @param  array  $params Additional fields to send in the request (e.g. array('RETURNALLCURRENCIES' => 1))
     * @return array
     */
    public function call($method, array $params = [], $account = 'noreply')
    {
        ini_set('max_input_vars', 6000);

        if ($account == 'noreply') {
            $fields = array_merge(
                [
                    'METHOD'    => $method,
                    'VERSION'   => self::VERSION,
                    'USER'      => self::$user_noreply,
                    'PWD'       => self::$pw_noreply,
                    'SIGNATURE' => self::$sig_noreply,
                ],
                $params
            );
        } else {
            $fields = array_merge(
                [
                    'METHOD'    => $method,
                    'VERSION'   => self::VERSION,
                    'USER'      => self::$user_acc,
                    'PWD'       => self::$pw_acc,
                    'SIGNATURE' => self::$sig_acc,
                ],
                $params
            );
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getUrl());
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        if (! $response) {
            throw new Exception('Failed to contact PayPal API: '.curl_error($ch).' (Error No. '.curl_errno($ch).')');
        }

        curl_close($ch);
        parse_str($response, $result);

        return $this->decodeFields($result);
    }

    /**
     * Send POST request to a certain link (particularly Paypal related).
     *
     **/
    public function callAPI($API, $token, $type = 'GET')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $API);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($type == 'GET') {
            curl_setopt($ch, CURLOPT_POST, false);
        } else {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '.$token,
            'Accept: application/json',
            'Content-Type: application/json',
        ]);

        $result = json_decode(curl_exec($ch));

        return $result;
    }

    /**
     * Get disputes from a range.
     *
     **/
    public function getDisputes($from)
    {
        if (strpos($from, ':') === false) {
            $from = $from.' 00:00:00';
        }

        $from = new DateTime($from);
        $from = $from->format('Y-m-d').'T'.$from->format('H:i:s').'Z';

        $url = "https://api.paypal.com/v1/customer/disputes?page_size=2&start_time=$from";
        $token1 = self::getToken('noreply');
        $token2 = self::getToken('accounts');

        $res1 = $this->callAPI($url, $token1);
        $res2 = $this->callAPI($url, $token1);

        echo serialize($res2);
        $data = [];
    }

    /**
     * Get payments normally using a range.
     *
     **/
    public function getPayments($from, $to, $count)
    {
        if (strpos($from, ':') === false) {
            $from = $from.' 00:00:00';
        }
        if (strpos($to, ':') === false) {
            $to = $to.' 00:00:00';
        }

        $from = new DateTime($from);
        $to = new DateTime($to);

        $from = $from->format('Y-m-d').'T'.$from->format('H:i:s').'Z';
        $to = $to->format('Y-m-d').'T'.$to->format('H:i:s').'Z';

        if (strtolower($count) == 'all') {
            $count = 2000;
        }

        $url = "https://api.paypal.com/v1/payments/payment?count=$count&start_time=$from&end_time=$to&sort_by=create_time";
        $token1 = self::getToken('noreply');
        $token2 = self::getToken('accounts');

        $res1 = $this->callAPI($url, $token1);
        $res2 = $this->callAPI($url, $token2);

        $data = [];

        foreach ($res1->payments as $payment) {
            $obj = new PaypalObject();
            $obj->id = $payment->id;
            $obj->time = new DateTime($payment->create_time);
            $obj->time = $obj->time->format('Y-m-d H:i:s');
            $obj->amount = $payment->transactions[0]->amount->total;
            $obj->state = $payment->state;
            $obj->email = $payment->payer->payer_info->email;
            $obj->phone = $payment->payer->payer_info->phone;
            $obj->name = $payment->payer->payer_info->first_name.' '.$payment->payer->payer_info->last_name;
            $obj->data = $payment;
            $obj->from = 'noreply@snugzone.biz';
            $obj->db_entry = PaymentStorage::whereRaw("(ref_number = '".$obj->id."')")->first();

            try {
                if (isset($payment->transactions[0])) {
                    $obj->desc = $payment->transactions[0]->description;
                    $obj->payee = $payment->transactions[0]->payee;
                    $obj->address = $payment->transactions[0]->item_list->shipping_address->line1;
                    $obj->address_line2 = $payment->transactions[0]->item_list->shipping_address->line2;
                    $obj->postal_code = $payment->transactions[0]->item_list->shipping_address->postal_code;
                } else {
                    $obj->address = '';
                    $obj->desc = '';
                    $obj->payee = '';
                }
            } catch (Exception $e) {
            }

            array_push($data, $obj);
        }

        foreach ($res2->payments as $payment) {
            $obj = new PaypalObject();
            $obj->id = $payment->id;
            $obj->time = new DateTime($payment->create_time);
            $obj->time = $obj->time->format('Y-m-d H:i:s');
            $obj->amount = $payment->transactions[0]->amount->total;
            $obj->state = $payment->state;
            $obj->email = $payment->payer->payer_info->email;
            $obj->phone = $payment->payer->payer_info->phone;
            $obj->name = $payment->payer->payer_info->first_name.$payment->payer->payer_info->last_name;
            $obj->data = $payment;
            $obj->from = 'accounts@prepago.ie';
            $obj->db_entry = PaymentStorage::whereRaw("(ref_number = '".$obj->id."')")->first();

            try {
                if (isset($payment->transactions[0])) {
                    $obj->desc = $payment->transactions[0]->description;
                    $obj->payee = $payment->transactions[0]->payee;
                    $obj->address = $payment->transactions[0]->item_list->shipping_address->line1;
                    $obj->address_line2 = $payment->transactions[0]->item_list->shipping_address->line2;
                    $obj->postal_code = $payment->transactions[0]->item_list->shipping_address->postal_code;
                } else {
                    $obj->address = '';
                    $obj->desc = '';
                    $obj->payee = '';
                }
            } catch (Exception $e) {
            }

            array_push($data, $obj);
        }

        usort($data, function ($a, $b) {
            return $a->time < $b->time;
        });

        return $data;
    }

    /**
     * Get payments using NVP Route (un-used currently).
     **/
    public function getPaymentsNVP($from, $to, $count)
    {
        if (strpos($from, ':') === false) {
            $from = $from.' 00:00:00';
        }
        if (strpos($to, ':') === false) {
            $to = $to.' 00:00:00';
        }

        $from = new DateTime($from);
        $to = new DateTime($to);

        $from = $from->format('Y-m-d').'T'.$from->format('H:i:s').'Z';
        $to = $to->format('Y-m-d').'T'.$to->format('H:i:s').'Z';

        if (strtolower($count) == 'all') {
            $count = 2000;
        }

        $res1 = $this->call('TransactionSearch', ['STARTDATE' => $from, 'ENDDATE' => $to], 'noreply');
        $res2 = $this->call('TransactionSearch', ['STARTDATE' => $from, 'ENDDATE' => $to], 'accounts');
        $payments = [];

        //echo var_dump($res1);

        foreach ($res1 as $k => $v) {
            if (empty($k)) {
                continue;
            }

            $num = 0;

            if (strpos($k, 'L_') !== false) {
                $num = (int) filter_var((explode('L_', $k)[1]), FILTER_SANITIZE_NUMBER_INT);
            }
            $key = str_replace($num, '', $k);
            if (strpos($k, 'L_TRANSACTIONID') === false) {
                continue;
            }
            $transaction = $this->call('GetTransactionDetails', ['TransactionID' => $v], 'noreply');
            $obj = new PaypalObject();
            $obj->id = (isset($transaction['TRANSACTIONID'])) ? $transaction['TRANSACTIONID'] : '';
            if (strlen($obj->id) <= 4) {
                continue;
            }

            $obj->time = (isset($transaction['ORDERTIME'])) ? $transaction['ORDERTIME'] : '';
            $obj->amount = (isset($transaction['AMT'])) ? $transaction['AMT'] : '';
            $obj->state = (isset($transaction['PAYMENTSTATUS'])) ? $transaction['PAYMENTSTATUS'] : '';
            $obj->email = (isset($transaction['EMAIL'])) ? $transaction['EMAIL'] : '';
            $obj->name = ((isset($transaction['FIRSTNAME'])) ? $transaction['FIRSTNAME'] : '').' '.(isset($transaction['LASTNAME'])) ?: '';
            $obj->db_entry = PaymentStorage::whereRaw("(ref_number = 'PAYID-".$obj->id."')")->first();
            $obj->from = 'noreply@snugzone.biz';
            echo 'Transaction ID: '.$obj->id."\n";
            echo 'Address: '.((isset($transaction['SHIPTOSTREET'])) ? $transaction['SHIPTOSTREET'] : '')."\n";
            echo 'Name: '.$obj->name."\n";
            echo 'Time: '.$obj->time."\n";
            echo 'Amount: '.$obj->amount."\n";
            echo 'Email: '.$obj->email."\n";
            echo 'Entry: '.$obj->db_entry."\n";
            echo "\n\n";
            if (! isset($payments[$obj->id])) {
                $payments[$obj->id] = $obj;
            }
        }
        foreach ($res2 as $k => $v) {
            if (empty($k)) {
                continue;
            }

            $num = 0;

            if (strpos($k, 'L_') !== false) {
                $num = (int) filter_var((explode('L_', $k)[1]), FILTER_SANITIZE_NUMBER_INT);
            }
            $key = str_replace($num, '', $k);

            if (strpos($k, 'L_TRANSACTIONID') === false) {
                continue;
            }

            $transaction = $this->call('GetTransactionDetails', ['TransactionID' => $v], 'accounts');
            $obj = new PaypalObject();
            $obj->id = (isset($transaction['TRANSACTIONID'])) ? $transaction['TRANSACTIONID'] : '';
            if (strlen($obj->id) <= 4) {
                continue;
            }

            $obj->time = (isset($transaction['ORDERTIME'])) ? $transaction['ORDERTIME'] : '';
            $obj->amount = (isset($transaction['AMT'])) ? $transaction['AMT'] : '';
            $obj->state = (isset($transaction['PAYMENTSTATUS'])) ? $transaction['PAYMENTSTATUS'] : '';
            $obj->email = (isset($transaction['EMAIL'])) ? $transaction['EMAIL'] : '';
            $obj->name = ((isset($transaction['FIRSTNAME'])) ? $transaction['FIRSTNAME'] : '').' '.(isset($transaction['LASTNAME'])) ?: '';
            $obj->db_entry = PaymentStorage::whereRaw("(ref_number = 'PAYID-".$obj->id."')")->first();
            $obj->from = 'accounts@prepago.ie';
            echo 'Transaction ID: '.$obj->id."\n";
            echo 'Address: '.((isset($transaction['SHIPTOSTREET'])) ? $transaction['SHIPTOSTREET'] : '')."\n";
            echo 'Name: '.$obj->name."\n";
            echo 'Time: '.$obj->time."\n";
            echo 'Amount: '.$obj->amount."\n";
            echo 'Email: '.$obj->email."\n";
            echo 'Entry: '.$obj->db_entry."\n";
            echo "\n\n";
            if (! isset($payments[$obj->id])) {
                $payments[$obj->id] = $obj;
            }
        }

        return $payments;
    }

    /**
     * Prepare fields for API.
     *
     * @param  array  $fields
     * @return array
     */
    private function encodeFields(array $fields)
    {
        return array_map('urlencode', $fields);
    }

    /**
     * Make response readable.
     *
     * @param  array  $fields
     * @return array
     */
    private function decodeFields(array $fields)
    {
        return array_map('urldecode', $fields);
    }

    /**
     * Get API url based on environment (NVP).
     *
     * @return string
     */
    private function getUrl()
    {
        if (is_null($this->url)) {
            switch ($this->config['environment']) {
                case 'sandbox':
                case 'beta-sandbox':
                    $this->url = "https://api-3t.$environment.paypal.com/nvp";
                    break;
                default:
                    $this->url = 'https://api-3t.paypal.com/nvp';
            }
        }

        return $this->url;
    }

    /**
     * Get a token for Paypal v1 API requests.
     *
     **/
    public static function getToken($account = 'accounts')
    {
        if ($account == 'accounts') {
            $liveClientID = self::$client_acc;
            $liveSecret = self::$secret_acc;
        }

        if ($account == 'noreply') {
            $liveClientID = self::$client_noreply;
            $liveSecret = self::$secret_noreply;
        }

        //TMP CODE
        $url = 'https://api.paypal.com/v1/oauth2/token';
        $clientID = $liveClientID;
        $secret = $liveSecret;
        $token = '';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $clientID.':'.$secret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');

        $result = curl_exec($ch);

        if (! empty($result)) {
            $json = json_decode($result);
            $token = isset($json->access_token) ? $json->access_token : '';
            //$tokenExpire = isset($json->expires_in) ? $json->expires_in : ""; //in seconds
        }

        curl_close($ch);

        return $token;
    }

    public static function getBal()
    {
        $paypal = new self('accounts');
        $response1 = $paypal->call('GetBalance', [], 'accounts');

        $paypal = new self('noreply');
        $response2 = $paypal->call('GetBalance', [], 'noreply');

        return (object) [
            'accounts' => $response1['L_AMT0'],
            'noreply' => $response2['L_AMT0'],
        ];
    }

    public function refund($id)
    {
        $url = "https://api.paypal.com/v1/payments/sale/$id/refund";
        $token1 = self::getToken('noreply');
        $res1 = $this->callAPI($url, $token1, 'POST');

        echo var_dump($res1);
    }

    public static function accountsContext()
    {
        $accounts = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                self::$client_acc,     // ClientID
                self::$secret_acc      // ClientSecret
            )
        );

        $accounts->setConfig(
            [
            'mode' => 'live',
            'log.LogEnabled' => true,
            'log.FileName' => '/var/www/app/storage/logs/Paypal.log',
            'log.LogLevel' => 'FINE', // PLEASE USE FINE LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
            'cache.enabled' => false,
            // 'http.CURLOPT_CONNECTTIMEOUT' => 30
            // 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
            ]
        );

        return $accounts;
    }

    public static function noreplyContext()
    {
        $noreply = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                self::$client_noreply,     // ClientID
                self::$secret_noreply      // ClientSecret
            )
        );

        $noreply->setConfig(
            [
            'mode' => 'live',
            'log.LogEnabled' => true,
            'log.FileName' => '/var/www/app/storage/logs/Paypal.log',
            'log.LogLevel' => 'FINE', // PLEASE USE FINE LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
            'cache.enabled' => false,
            // 'http.CURLOPT_CONNECTTIMEOUT' => 30
            // 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
            ]
        );

        return $noreply;
    }

    public static function isGhostPayment($saleID)
    {
    }

    public static function extractPaymentData($p)
    {
        $state = null;
        $email = null;
        $name = null;
        $phone = null;
        $transactionid = $p->id;
        $amount = null;
        $saleid = null;
        $create_time = null;
        $entry = null;
        $db_entry = null;
        $refund_url = null;
        $desc = null;
        $username = false;

        if ($p->payer) {
            if ($p->payer->payer_info) {
                $email = $p->payer->payer_info->email;
                $name = ucfirst(strtolower($p->payer->payer_info->first_name)).' '.ucfirst(strtolower($p->payer->payer_info->last_name));
                $phone = $p->payer->payer_info->phone;
            }
        }

        foreach ($p->transactions as $a=>$t) {
            $amount = $t->amount->total;
            $desc = $t->description;
        }

        if ($t->related_resources) {
            if (isset($t->related_resources[0])) {
                if ($t->related_resources[0]->sale) {
                    $state = $t->related_resources[0]->sale->state;
                    $saleid = $t->related_resources[0]->sale->id;
                    $create_time = $t->related_resources[0]->sale->create_time;
                    $entry = PaymentStorage::where('ref_number', $transactionid)->count();
                    $db_entry = PaymentStorage::where('ref_number', $transactionid)->first();
                    $links = $t->related_resources[0]->sale->links;
                    $refund_url = $links[1]->href;
                }
            }
        }

        if (strpos($desc, '(') !== false && strpos($desc, ')') !== false) {
            $username = explode('(', $desc);
            if (count($username) > 1) {
                $username = $username[1];
                $username = explode(')', $username);
                if (count($username) > 1) {
                    $username = $username[0];
                }
            }
        }

        return (object) [
            'id' => $transactionid,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'create_time' => $create_time,
            'time' => $create_time,
            'transactionid' => $transactionid,
            'amount' => $amount,
            'saleid' => $saleid,
            'state' => $state,
            'entry' => $entry,
            'db_entry' => $db_entry,
            'refund_url' => $refund_url,
            'desc' => $desc,
            'username' => $username,
        ];
    }

    public static function getPaymentsNew($from, $to)
    {
        try {
            $return_payments = [];

            $from = new DateTime($from);
            $to = new DateTime($to);
            $start_time = $from->format('Y-m-d').'T'.$from->format('H:i:s').'Z';
            $end_time = $to->format('Y-m-d').'T'.$to->format('H:i:s').'Z';

            $payments = \PayPal\Api\Payment::all([
                'start_time' => $start_time,
                'end_time' => $end_time,
                'sort_by' => 'create_time',
                'count' => '20',
            ], self::accountsContext());

            $payments = $payments->getPayments();

            foreach ($payments as $k => $p) {
                $data = self::extractPaymentData($p);
                $data->from = 'accounts@prepago.ie';
                if ($data->saleid == null || $data->state == 'denied') {
                    continue;
                }
                array_push($return_payments, $data);
            }
        } catch (Exception $e) {
            return 'Failed to get range: '.$e->getMessage().' ('.$e->getLine().')';
        }

        /*
        $payments = \PayPal\Api\Payment::all([
            'start_time' => $start_time,
            'end_time' => $end_time,
            'sort_by' => 'create_time',
            'count' => '20',
        ], Paypal::noreplyContext());


        $payments = $payments->getPayments();

        foreach($payments as $k => $p) {
            $data = Paypal::extractPaymentData($p);
            $data->from = "noreply@snugzone.biz";
            if($data->saleid == null || $data->state == 'denied') continue;
            array_push($return_payments, $data);
        }
        */

        return $return_payments;
    }

    public static function getMissingPayments($from, $to, $customerID = null)
    {
        $from = $from.' 23:59:59';
        $to = $to.' 00:00:00';
        $missing_payments = [];

        $paypal = new self();
        $customer = Customer::find($customerID);
        $start = new DateTime($from);
        $end = new DateTime($to);
        $_start = $start;
        $_end = $end;

        while ($start > $end) {
            $start_minus_4 = new DateTime($start->format('Y-m-d H:i:s'));
            $start_minus_4->modify('-5 hours');

            $range_payments = self::getPaymentsNew($start_minus_4->format('Y-m-d H:i:s'), $start->format('Y-m-d H:i:s'));

            foreach ($range_payments as $p) {
                $entry_exists = PaymentStorage::where('ref_number', $p->id)->first();
                if (! $entry_exists) {
                    continue;
                }

                if ($customerID != null) {
                    if (
                        (strpos($p->email, $customer->email_address) === false) &&
                        (strpos(strtolower($p->desc), strtolower($customer->username)) === false) &&
                        (strpos(strtolower($p->name), strtolower($customer->first_name.$customer->surname)) === false)
                        ) {
                        continue;
                    }
                }

                array_push($missing_payments, $p);
            }

            $start = $start_minus_4;
        }

        return [
            'from' => $_start->format('Y-m-d H:i:s'),
            'to' => $_end->format('Y-m-d H:i:s'),
            'payments' => $missing_payments,
        ];
    }
}
