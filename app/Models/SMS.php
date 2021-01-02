<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;


class SMS
{
    /**
     * Clickatell API-ID.
     * @link http://sourceforge.net/forum/forum.php?thread_id=1005106&forum_id=344522 How to get CLICKATELL API ID?
     * @var int
     */
    public $api_id = '';

    /**
     * Clickatell username.
     * @var mixed
     */
    public $user = '';

    /**
     * Clickatell password.
     * @var mixed
     */
    public $password = '';

    /**
     * Send SMS from, mobile number.
     * @var string
     */
    public $from = '';

    /**
     * Use SSL (HTTPS) protocol.
     * @var bool
     */
    public $use_ssl = false;

    /**
     * Define SMS balance limit below class will not work.
     * @var int
     */
    public $balace_limit = 0;

    /**
     * Gateway command sending method (curl,fopen).
     * @var mixed
     */
    public $sending_method = 'curl';

    /**
     * Optional CURL Proxy.
     * @var bool
     */
    public $curl_use_proxy = false;

    /**
     * Proxy URL and PORT.
     * @var mixed
     */
    public $curl_proxy = 'http://127.0.0.1:8080';

    /**
     * Proxy username and password.
     * @var mixed
     */
    public $curl_proxyuserpwd = 'login:secretpass';

    /**
     * Callback
     * 0 - Off
     * 1 - Returns only intermediate statuses
     * 2 - Returns only final statuses
     * 3 - Returns both intermediate and final statuses.
     * @var int
     */
    public $callback = 0;

    /**
     * Session variable.
     * @var mixed
     */
    public $session;

    public function sms()
    {
        self::__construct();
    }

    public static function createAndSend($mobile_number, $message, $charge, $customer = null)
    {
        try {
            if ($customer != null) {
                $customer_id = $customer->id;
                $scheme_number = $customer->scheme_number;
            }

            try {
                $mySMS = SMSMessage::where('mobile_number', $mobile_number)
                ->whereRaw('(DATEDIFF(NOW(), date_time) <= 1)')
                ->count();

                if ($mySMS >= 20) {
                    DB::table('sms_messages_exceeded')->insert([
                        'customer_id' => $customer_id,
                        'time_date' => date('Y-m-d H:i:s'),
                        'count' => $mySMS,
                    ]);

                    return false;
                }
            } catch (Exception $b) {
            }

            // Add SMS to queue
            $sms_messages = new SMSMessage();
            $sms_messages->customer_id = $customer_id;
            $sms_messages->mobile_number = $mobile_number;
            $sms_messages->message = $message;
            $sms_messages->date_time = date('Y-m-d H:i:s');
            $sms_messages->scheme_number = $scheme_number;
            $sms_messages->charge = $charge;
            $sms_messages->balance_before = $customer->balance;
            $sms_messages->balance_after = $customer->balance - $charge;
            $sms_messages->paid = 1;
            $sms_messages->save();

            // Charge the customer
            $customer->balance -= $charge;
            $customer->save();

            return $sms_messages;
        } catch (Exception $e) {
            return Result::fail('could not save sms');
        }
    }

    /**
     * Class constructor
     * Create SMS object and authenticate SMS gateway.
     * @return object New SMS object.
     */
    public function __construct()
    {
        $clickatell_details = Clickatell::all()->first();

        $this->api_id = $clickatell_details['api_id'];
        $this->user = $clickatell_details['user'];
        $this->from = $clickatell_details['sender_id'];
        $this->password = $clickatell_details['password'];
        if ($this->use_ssl) {
            $this->base = 'http://api.clickatell.com/http';
            $this->base_s = 'https://api.clickatell.com/http';
        } else {
            $this->base = $clickatell_details['base_url'];
            $this->base_s = $clickatell_details['base_url'];
        }

        $this->_auth();
    }

    /**
     * Authenticate SMS gateway.
     * @return mixed  "OK" or script die
     */
    public function _auth()
    {
        $comm = sprintf('%s/auth?api_id=%s&user=%s&password=%s', $this->base_s, $this->api_id, $this->user, $this->password);
        $this->session = $this->_parse_auth($this->_execgw($comm));
    }

    /**
     * Query SMS credis balance.
     * @return int  number of SMS credits
     */
    public function getbalance()
    {
        $comm = sprintf('%s/getbalance?session_id=%s', $this->base, $this->session);

        return $this->_parse_getbalance($this->_execgw($comm));
    }

    /**
     * Send SMS message.
     * @param to mixed  The destination address.
     * @param text mixed  The text content of the message
     * @return mixed  "OK" or script die
     */
    public function send($to = null, $text = null)
    {

        /* Check SMS credits balance */
        if (self::getbalance() < $this->balace_limit) {
            return Result::fail('You have reach the SMS credit limit!');
        }

        /* Check SMS $text length */
        if (strlen($text) > 465) {
            return Result::fail('Your message is to long! (Current lenght=".strlen ($text).")');
        }

        /* Does message need to be concatenate */
        if (strlen($text) > 160) {
            $concat = '&concat=3';
        } else {
            $concat = '';
        }

        /* Check $to and $from is not empty */
        if (empty($to)) {
            return Result::fail('You not specify destination address (TO)!');
        }
        if (empty($this->from)) {
            return Result::fail('You not specify source address (FROM)!');
        }

        /* Reformat $to number */
        $cleanup_chr = ['+', ' ', '(', ')', "\r", "\n", "\r\n"];
        $to = str_replace($cleanup_chr, '', $to);

        /* Send SMS now */
        $comm = sprintf('%s/sendmsg?session_id=%s&to=%s&from=%s&text=%s&callback=%s%s',
            $this->base,
            $this->session,
            rawurlencode($to),
            rawurlencode($this->from),
            rawurlencode($text),
            $this->callback,
            $concat
        );

        return $this->_parse_send($this->_execgw($comm));
    }

    /**
     * Execute gateway commands.
     */
    public function _execgw($command)
    {
        if ($this->sending_method == 'curl') {
            return $this->_curl($command);
        }
        if ($this->sending_method == 'fopen') {
            return $this->_fopen($command);
        }
        die('Unsupported sending method!');
    }

    /**
     * CURL sending method.
     */
    public function _curl($command)
    {
        $this->_chk_curl();
        $ch = curl_init($command);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        if ($this->curl_use_proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->curl_proxy);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->curl_proxyuserpwd);
        }
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * fopen sending method.
     */
    public function _fopen($command)
    {
        $result = '';
        $handler = @fopen($command, 'r');
        if ($handler) {
            while ($line = @fgets($handler, 1024)) {
                $result .= $line;
            }
            fclose($handler);

            return $result;
        } else {
            die('Error while executing fopen sending method!<br>Please check does PHP have OpenSSL support and check does PHP version is greater than 4.3.0.');
        }
    }

    /**
     * Parse authentication command response text.
     */
    public function _parse_auth($result)
    {
        $session = substr($result, 4);
        $code = substr($result, 0, 2);
        if ($code != 'OK') {
            die("Error in SMS authorization! ($result)");
        }

        return $session;
    }

    /**
     * Parse send command response text.
     */
    public function _parse_send($result)
    {
        $code = substr($result, 0, 2);
        if ($code != 'ID') {
            die("Error sending SMS! ($result)");
        } else {
            $code = 'OK';
        }

        return $code;
    }

    /**
     * Parse getbalance command response text.
     */
    public function _parse_getbalance($result)
    {
        $result = substr($result, 8);

        return (int) $result;
    }

    /**
     * Check for CURL PHP module.
     */
    public function _set_par($u_name, $pass, $apiid, $my_type)
    {
        $this->user = $u_name;
        $this->password = $pass;
        $this->password = $apiid;
        $this->sending_method = $my_type;
    }

    public function _chk_curl()
    {
        if (! extension_loaded('curl')) {
            die('This SMS API class can not work without CURL PHP module! Try using fopen sending method.');
        }
    }
}
