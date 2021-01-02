<?php

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon as Carbon;

class Scheme extends Model
{
    //protected $appends = ['permissions'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'schemes';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = ['id'];

    public static $un_available_schemes = [/*24, */23, 15];

    public static $rules = [
        'country'							=> 'required|in:Ireland,UK',
        'scheme_number'						=> 'required|integer',
        'scheme_nickname'					=> 'required|max:255|unique:schemes',
        'company_name'  					=> 'required|max:30',
        'company_address'					=> 'required|max:100',
        'sms_password'						=> 'required_if:action,create|max:16',
        'accounts_email'					=> 'required|email',
        'vat_rate'							=> 'required|numeric',
        'currency_code'						=> 'required|integer',
        'currency_sign'						=> 'required|max:3',
        'street2'							=> 'max:50',
        'town'								=> 'max:100',
        'county'							=> 'max:20',
        'post_code'							=> 'max:12',
        //'start_date'						=> 'required|date|date_format:Y-m-d',
        'service_type'						=> 'required|integer',
        'daily_customer_charge'	 			=> 'required|numeric',
        'commission_charge'					=> 'required|numeric',
        'prepago_registered_apps_charge'	=> 'required|numeric',
        'IOU_chargeable'					=> 'integer',
        'IOU_amount'						=> 'required|numeric',
        'IOU_charge'						=> 'required|numeric',
        'IOU_text'							=> 'required|max:160',
        'IOU_extra_amount'					=> 'required|numeric',
        'IOU_extra_charge'					=> 'required|numeric',
        'IOU_extra_text'					=> 'required|max:160',
        'prepage_SMS_charge'				=> 'required|numeric',
        'prepago_new_admin_charge'			=> 'required|numeric',
        'prepago_in_app_message_charge'		=> 'required|numeric',
        'prefix'							=> 'required|max:100',
        'unit_abbreviation'					=> 'required|max:5',
        'scu_type'							=> 'required_if:action,create|in:n,a,d',
        'ICCID'								=> 'required_if:scu_type,a|max:20',
        'MSISDN'							=> 'required_if:scu_type,a|max:14',
        'IP_Address'						=> 'required_if:scu_type,a|max:15',
        'Name'								=> 'required_if:scu_type,a|max:100',
        'software_version'					=> 'required_if:scu_type,a|numeric',
        'in_use'							=> 'required_if:scu_type,a|in:0,1',
    ];

    public static function rules($schemeID = null)
    {
        $rules = self::$rules;
        if ($schemeID) {
            $rules['scheme_nickname'] = 'required|max:255|unique:schemes,scheme_nickname,'.$schemeID;
        }

        return $rules;
    }

    public function getDataLoggerAttribute()
    {
        return DataLogger::where('scheme_number', $this->scheme_number)->first();
    }

    public function users()
    {
        return $this->belongsToMany('User', 'users_schemes');
    }

    public function getTariffAttribute()
    {
        return $this->hasOne('Tariff', 'scheme_number', 'id')->first();
    }

    public function refreshFAQ()
    {
        $faqs = $this->FAQ;
        $faqs = json_decode($faqs);
        $start_scheme = self::orderBy('scheme_number', 'DESC')->first()->scheme_number;

        while (empty($faqs) || ! is_array($faqs)) {
            $other_scheme = self::find($start_scheme);
            $faqs = $other_scheme->FAQ;
            $faqs = json_decode($faqs);
            $start_scheme--;
        }

        foreach ($faqs as $f) {
            if (strpos($f->question, 'What is the Daily Delivery Charge?') !== false) {
                $f->answer = 'This is a fixed daily charge to ensure the availability of heating and hot water, customer service and support  24/7/365. It is currently '.($this->tariff->tariff_2 * 100).' cent per day. Rounding will occur in charges.';
            }

            if (strpos($f->question, 'What is the unit cost of heat?') !== false) {
                $f->answer = 'Heat is charged '.($this->tariff->tariff_1 * 100).' cents per kWh. Rounding will occur in charges.';
            }
        }

        $faqs = json_encode($faqs);

        $this->FAQ = $faqs;
        $this->save();
    }

    public function totalUsage($from = null, $to = null)
    {
        $from = $from ?: Carbon::now()->startOfMonth();
        $to = $to ?: Carbon::now()->endOfDay();

        $totalUsage = 0;

        foreach ($customers = $this->customers as $customer) {
            $customerTotalUsage = $customer->totalUsage($from, $to);
            $totalUsage += $customerTotalUsage;
        }

        if (! $customers->count()) {
            return 0;
        }

        return $totalUsage;
    }

    public function avgDailyUsage($from = null, $to = null)
    {
        $from = $from ?: Carbon::now()->startOfMonth();
        $to = $to ?: Carbon::now()->endOfDay();

        $avgDailyUsage = 0;

        foreach ($customers = $this->customers as $customer) {
            $customerAvgDailyUsage = $customer->avgDailyUsage($from, $to);
            $avgDailyUsage += $customerAvgDailyUsage;
        }

        if (! $customers->count()) {
            return 0;
        }

        return $avgDailyUsage / $customers->count();
    }

    public function avgDailyCost($from = null, $to = null)
    {
        $from = $from ?: Carbon::now()->startOfMonth();
        $to = $to ?: Carbon::now()->endOfDay();

        $avgDailyCost = 0;

        foreach ($customers = $this->customers as $customer) {
            $customerAvgDailyCost = $customer->avgDailyCost($from, $to);
            $avgDailyCost += $customerAvgDailyCost;
        }

        if (! $customers->count()) {
            return 0;
        }

        return $avgDailyCost / $customers->count();
    }

    public function scopeWithoutArchived($query)
    {
        return $query->where('archived', 0);
    }

    public function scopeWithSchemeNumber($query, $schemeNumber)
    {
        return $query->where('scheme_number', $schemeNumber);
    }

    public function getSchemeDisplayName()
    {
        return $this->scheme_nickname ?: $this->company_name;
    }

    public function customers()
    {
        return $this->hasMany('Customer', 'scheme_number', 'scheme_number');
    }

    public function getLookupAttribute()
    {
        return MeterLookup::whereRaw("(applied_schemes LIKE '%".$this->scheme_number."%')")->first();
    }

    public function getLastReadingAttribute()
    {
        $pmdr_all = PermanentMeterDataReadingsAll::where('scheme_number', $this->scheme_number)
        ->where('reading1', '>', 0)
        ->orderBy('ID', 'DESC')->first();

        if (! $pmdr_all) {
            $pmdr_all = PermanentMeterDataReadings::where('scheme_number', $this->scheme_number)
            ->where('reading1', '>', 0)
            ->orderBy('ID', 'DESC')->first();
        }

        if ($pmdr_all) {
            $pmdr_all->mins = Carbon::parse($pmdr_all->time_date)->diffInMinutes();
            $pmdr_all->secs = Carbon::parse($pmdr_all->time_date)->diffInSeconds();
            $pmdr_all->hrs = Carbon::parse($pmdr_all->time_date)->diffInHours();
        }

        return $pmdr_all;
    }

    public function getCustomersAttribute()
    {
        if ($this->simulator > 0) {
            return Customer::where('scheme_number', $this->scheme_number)
            ->whereRaw('(ev_owner = 0)')
            ->get();
        }

        return Customer::where('scheme_number', $this->scheme_number)
        ->whereRaw('(status = 1 AND ev_owner = 0)')->get();
    }

    public function getReadingAttribute()
    {
        $lastReading = $this->lastReading;

        if ($this->customers->count() <= 0) {
            return true;
        }

        if (! $lastReading) {
            return true;
        }

        $mins = $lastReading->mins;
        $secs = $lastReading->secs;
        $hrs = $lastReading->hrs;

        if ($hrs >= 4.5) {
            return false;
        }

        return true;
    }

    public function getStatusAttribute()
    {
        if ($this->status_debug) {
            return 'inactive ('.$this->status_ok.')';
        }

        if ($this->status_ok == 1) {
            return 'SIM Online';
        }

        if ($this->status_ok == 0) {
            return 'SIM Offline';
        }

        if ($this->status_ok == 2) {
            return 'SIM Rebooting';
        }
    }

    public function getStatusCodeAttribute()
    {
        if ($this->status_debug) {
            return '0';
        }

        if ($this->status_ok == 1) {
            if (! $this->reading) {
                return $status = '11';
            }

            return '1';
        }

        if ($this->status_ok == 0) {
            if (! $this->reading) {
                return $status = '21';
            }

            return '2';
        }

        if ($this->status_ok == 2) {
            return '3';
        }
    }

    public function getStatusCSSAttribute()
    {
        if (strpos(strtolower($this->status), 'inactive') !== false) {
            return 'color: #ddd;';
        }

        if (strpos(strtolower($this->status), 'online') !== false) {
            if ($this->reading) {
                return 'color: #66CD00;font-weight: bold;';
            } else {
                return 'color: #f89406;font-weight:bold;';
            }
        }

        if (strpos(strtolower($this->status), 'offline') !== false) {
            if ($this->reading) {
                return 'color: #FF0000;font-weight:bold;';
            } else {
                return 'color: #FF0000;font-weight:bold;';
            }
        }

        if (strpos(strtolower($this->status), 'rebooting') !== false) {
            return 'color: #f89406;font-weight:bold;';
        }
    }

    public function getStatusAltAttribute()
    {
        if ($this->status_debug) {
            return 'inactive ('.$this->status_ok.')';
        }

        if ($this->status_ok == 1) {
            return 'SIM Online | '.($this->reading ? ' Reading' : ' Not Reading');
        }

        if ($this->status_ok == 0) {
            return 'SIM Offline | '.($this->reading ? ' Reading' : ' Not Reading');
        }

        if ($this->status_ok == 2) {
            return 'SIM Rebooting';
        }
    }

    public function getEmptyAddressesAttribute()
    {
        return PermanentMeterData::where('in_use', 0)
        ->where('scheme_number', $this->scheme_number)->get();
    }

    public function getStatusAltCSSAttribute()
    {
        if (strpos(strtolower($this->status), 'inactive') !== false) {
            return 'background: #ddd;';
        }

        if (strpos(strtolower($this->status), 'online') !== false) {
            if ($this->reading) {
                return 'background: #62c462;';
            } else {
                return 'background: #f89406;';
            }
        }

        if (strpos(strtolower($this->status), 'offline') !== false) {
            return 'background: #ee5f5b;';
        }

        if (strpos(strtolower($this->status), 'rebooting') !== false) {
            return 'background: #f89406;';
        }
    }

    public function getSIMAttribute()
    {
        $data_logger = DataLogger::where('scheme_number', $this->scheme_number)->first();

        if ($data_logger) {
            $sim = Simcard::where('ID', $data_logger->sim_id)->first();

            return $sim;
        }

        return null;
    }

    public function getTrackingAttribute()
    {
        $tracking = null;

        $tracking = TrackingScheme::where('scheme_number', $this->scheme_number)
        ->orderBy('id', 'DESC');

        return $tracking;
    }

    public function getTrackLog($day = null)
    {
        $log = null;

        if ($day == null) {
            $log = TrackingScheme::where('scheme_number', $this->scheme_number)
            ->orderBy('id', 'DESC')->first();
        } else {
            $log = TrackingScheme::where('scheme_number', $this->scheme_number)
            ->orderBy('id', 'DESC')->where('date', $day)->first();
        }

        if ($log != null) {
            $log = $log->status_log;
        }

        return $log;
    }

    public function getIsBlueSchemeAttribute()
    {
        return PermanentMeterData::where('is_bill_paid_customer', 1)->where('scheme_number', $this->scheme_number)
->count() == PermanentMeterData::where('scheme_number', $this->scheme_number)->count();
    }

    public function reboot($EseyeConnection = null)
    {
        $sim = $this->SIM;

        if (! $sim) {
            return [
            'rebooted' => false,
        ];
        }

        $res = Simcard::reboot($sim->IP_Address, 'emnify');

        return $res;
    }

    public function getLastReboot()
    {
        try {
            if (! empty($this->status_last_reboot) && strpos($this->status_last_reboot, '0000') === false && Carbon::parse($this->status_last_reboot)) {
                return Carbon::parse($this->status_last_reboot)->diffInSeconds();
            } else {
                return -1;
            }
        } catch (Exception $e) {
            return 0;
        }

        return 0;
    }

    public function getFaqsAttribute()
    {
        return json_decode($this->FAQ);
    }

    public function addFAQ($question, $answer)
    {
        try {
            if (strlen($question) < 1 || strlen($answer) < 1) {
                return false;
            }

            if (strlen($this->FAQ) <= 0) {
                return false;
            }

            $faqs = json_decode($this->FAQ);
            $faqs_arr = [];

            if (! is_object($faqs) && ! is_array($faqs)) {
                return false;
            }

            // echo count($faqs_arr) . "\n\n";

            $exists = false;

            foreach ($faqs as $k => $v) {
                $q = $v->question;
                $a = $v->answer;

                if ($q == $question) {
                    $a = $answer;
                    $exists = true;
                }

                array_push($faqs_arr, (object) [
                    'question' => $q,
                    'answer' => $a,
                ]);
            }

            if (! $exists) {
                array_push($faqs_arr, (object) [
                    'question' => $question,
                    'answer' => $answer,
                ]);
            }

            // echo count($faqs_arr) . "\n\n";

            $faqs = json_encode($faqs_arr);

            $this->FAQ = $faqs;
            $this->save();

            return true;
        } catch (Exception $e) {
            echo $e->getMessage();

            return false;
        }
    }

    public function removeFAQ($identifier)
    {
    }

    public function getFaqAttribute()
    {
        $tariff = $this->tariff;

        if ($tariff) {
            $this->attributes['FAQ'] = str_replace('%TARIFF_1%', $tariff->tariff_1 * 100, $this->attributes['FAQ']);
            $this->attributes['FAQ'] = str_replace('%TARIFF_2%', $tariff->tariff_2 * 100, $this->attributes['FAQ']);
            $this->attributes['FAQ'] = str_replace('%TARIFF_3%', $tariff->tariff_3 * 100, $this->attributes['FAQ']);
        }

        return $this->attributes['FAQ'];
    }

    public function getActiveAttribute()
    {
        return $this->status_debug == 0 && $this->archived == 0 && in_array($this->id, self::$un_available_schemes) == false;
    }

    public static function active($allow_simulators = true)
    {
        $un_available_schemes = implode(',', self::$un_available_schemes);

        if ($allow_simulators == false) {
            return self::where('status_debug', 0)->where('archived', 0)
            ->orderBy('scheme_number', 'DESC')
            ->whereRaw("(scheme_number NOT IN ($un_available_schemes) AND simulator = 0)")->get();
        }

        return self::where('status_debug', 0)->where('archived', 0)
        ->orderBy('scheme_number', 'DESC')
        ->whereRaw("(scheme_number NOT IN ($un_available_schemes))")->get();
    }

    public function getOnlinePercentageAttribute()
    {
        $tracking_yesterday = TrackingScheme::whereRaw("(date = '".date('Y-m-d', strtotime('yesterday'))."')")
        ->where('scheme_number', $this->scheme_number)->first();
        $log_yday = unserialize($tracking_yesterday->status_log);

        $tracking_today = TrackingScheme::whereRaw("(date = '".date('Y-m-d')."')")
        ->where('scheme_number', $this->scheme_number)->first();
        $log_today = unserialize($tracking_today->status_log);

        $status_logs_24 = [];
        $status_logs_24_pairs = [];

        foreach ($log_yday as $k => $t) {
            $online = '';
            $time = '';
            foreach ($t as $l => $v) {
                $online = $l;
                $time = $v;
            }
            $datetime = (new DateTime($time))->format('H:i:s');
            if ($datetime < date('H:i:s')) {
                continue;
            }
            array_push($status_logs_24, $online);
            array_push($status_logs_24_pairs, [
                0 => $online,
                1 => $time,
            ]);
            //echo "Online: " . $online . "<br/>";
            //echo "Time: " . $time . "<br/>";
            //echo "<hr/>";
        }

        foreach ($log_today as $k => $t) {
            $online = '';
            $time = '';
            foreach ($t as $l => $v) {
                $online = $l;
                $time = $v;
            }
            $datetime = (new DateTime($time))->format('H:i:s');
            if ($datetime > date('H:i:s')) {
                continue;
            }
            array_push($status_logs_24, $online);
            array_push($status_logs_24_pairs, [
                0 => $online,
                1 => $time,
            ]);
            //echo "Online: " . $online . "<br/>";
            //echo "Time: " . $time . "<br/>";
            //echo "<hr/>";
        }

        $total = count($status_logs_24);
        $online_times = 0;

        foreach ($status_logs_24 as $k => $v) {
            $online_times += $v;
        }

        return [
            'percent' => number_format(($online_times / $total) * 100, 0),
            'logs' => json_encode($status_logs_24_pairs),
        ];
    }

    public function updateSimulator()
    {
        $simulatingScheme = self::find($this->simulator);

        if (! $simulatingScheme) {
            die('Cannot find scheme '.$this->simulator);
        }

        // Simulate tariffs
        $tariff_simulating = $simulatingScheme->tariff;
        if ($tariff_simulating) {
            $tariff = $this->tariff;
            if (! $tariff) {
                $tariff = new Tariff();
            }

            $tariff->scheme_number = $this->scheme_number;
            foreach ($tariff_simulating->getAttributes() as $k => $v) {
                if ($k == 'scheme_number') {
                    continue;
                }
                $tariff->$k = $v;
            }
            $tariff->save();
        }

        $dataLogger_simulating = $simulatingScheme->dataLogger;
        if ($dataLogger_simulating) {
            $dataLogger = $this->dataLogger;
            if (! $dataLogger) {
                $dataLogger = new DataLogger();
            }

            $dataLogger->scheme_number = $this->scheme_number;
            foreach ($dataLogger_simulating->getAttributes() as $k => $v) {
                if ($k == 'scheme_number' || $k == 'id') {
                    continue;
                }
                $dataLogger->$k = $v;
            }
            $dataLogger->name = $this->scheme_nickname.' (simulator)';
            $dataLogger->save();
        }

        DB::statement('DELETE FROM customers WHERE simulator > 0');
        $lastValidCustomer = Customer::where('status', 1)
        ->where('simulator', '=', 0)->orderBy('id', 'DESC')->first();
        DB::statement('ALTER TABLE customers AUTO_INCREMENT = '.$lastValidCustomer->id);
        $customers_to_simulate = Customer::where('scheme_number', $this->simulator)->get();
        foreach ($customers_to_simulate as $k => $v) {
            $customer = Customer::where('scheme_number', $this->scheme_number)
            ->where('simulator', $this->simulator)
            ->whereRaw("(username LIKE '%".$v->username."%')")
            ->first();

            if (! $customer) {
                $customer = new Customer();
            }
            foreach ($v->getAttributes() as $a => $b) {
                if ($a == 'id' || $a == 'simulator' || $a == 'scheme_number' || $a == 'username') {
                    continue;
                }
                $customer->$a = $b;
            }
            $customer->username = $v->username.'_test';
            $customer->mobile_number = '';
            $customer->email_address = $v->username.'@test.com';
            $customer->scheme_number = $this->scheme_number;
            $customer->simulator = $this->simulator;
            $customer->status = 0;
            $customer->save();
            DB::table('customers')->where('id', $customer->id)->update(['password' => '']);
        }

        $this->simulator_updated_at = date('Y-m-d H:i:s');
        $this->save();
    }

    public static function getReportInformation($s, $start_date = null, $end_date = null,
    $vat = 13.5, $payments_charge = 7.00, $app_charge = 3.00,
    $meter_charge = 0.04, $iou_charge = 0, $statements_charge = 0.25,
    $app_support = 0.25, $vat_number = 'IE9850930S', $company_name = '', $autotopup_charge = 1.95,
    $sms_cost = -1, $premium_sms_cost = 0.50, $deleted_customers_charge = 2.00, $blue_accounts_charge = 0.06)
    {
        $start_date = date('Y-m-d', strtotime($start_date));
        $end_date = date('Y-m-d', strtotime($end_date));
        ///	$stripe_invoices = StripeCustomerSubscription::APIInvoiceTotal($start_date, $end_date);

        if ($sms_cost == -1) {
            $sms_cost = $this->prepage_SMS_charge;
        }
        $s->company_name = $company_name;
        $s->start_date = $start_date;
        $s->end_date = $end_date;
        $parsed_start = Carbon::parse($start_date);
        $parsed_end = Carbon::parse($end_date);
        $days = ($parsed_end->diffInDays($parsed_start)) + 1;
        $s->vat = $vat;
        $s->vat_number = $vat_number;
        $s->remove_vat = (($s->vat / 100) + 1);
        $s->ref_pa = date('ym', strtotime($end_date));
        $s->month = date('M-y', strtotime($end_date));
        $s->date = date('d/m/Y');
        $s->days = $days;
        $s->payments_charge = ($payments_charge / 100);
        $s->app_charge = $app_charge;
        $s->meter_charge = $meter_charge;
        $s->autotopup_charge = $autotopup_charge;
        $s->closed_accounts_charge = $deleted_customers_charge;
        $s->iou_charge = $iou_charge;
        $s->statements_charge = $statements_charge;
        $s->amount_of_payments = self::getPaymentAmount($s->scheme_number, $start_date, $end_date);
        $s->value_of_payments = self::getPaymentValue($s->scheme_number, $start_date, $end_date);
        $s->cost_of_topups_inc_vat = $s->value_of_payments * $s->payments_charge;
        $s->cost_of_topups_ex_vat = ($s->cost_of_topups_inc_vat / $s->remove_vat);
        $s->sms_msgs = ((self::getSMSMessages($s->scheme_number, $start_date, $end_date)));
        $s->sms_cost = $sms_cost;
        $s->premium_sms_msgs = ((self::getPremiumSMSMessages($s->scheme_number, $start_date, $end_date)));
        $s->premium_sms_cost = $premium_sms_cost;
        $s->sms_total_inc_vat = $sms_cost * self::getSMSCost($s->scheme_number, $start_date, $end_date);
        $s->sms_total_ex_vat = ($s->sms_total_inc_vat / $s->remove_vat);
        $s->premium_sms_total_ex_vat = $premium_sms_cost * self::getPremiumSMSCost($s->scheme_number, $start_date, $end_date);
        $s->premium_sms_total_inc_vat = ($s->premium_sms_total_ex_vat * $s->remove_vat);
        $s->apps_installed = RegisteredPhonesWithApps::getNewApps($s->scheme_number, $start_date, $end_date);
        $s->app_charge = $s->app_charge;
        $s->app_total_ex_vat = $s->apps_installed * $s->app_charge;
        $s->app_total_inc_vat = $s->app_total_ex_vat * $s->remove_vat;
        $s->autotopup_active = StripeCustomerSubscription::join('customers', 'customers.id', '=', 'customers_stripe_subs.customer_id')->where('customers.scheme_number', '=', $s->scheme_number)->where('active', 1)->count();
        $s->autotopup_ex_vat = (($s->autotopup_charge / 113.5) * 100) * $s->autotopup_active;
        $s->autotopup_inc_vat = $s->autotopup_active * $s->autotopup_charge;
        $s->iou_chargeable = ($s->iou_charge > 0.00) ? 'yes' : 'no';
        $s->ious = self::getIOUs($s->scheme_number, $start_date, $end_date);
        $s->iou_charge_inc_vat = ($s->iou_chargeable == 'yes') ? ($s->ious * $s->iou_charge) : 0;
        $s->iou_charge_ex_vat = ($s->iou_charge_inc_vat / $s->remove_vat);
        $s->closed_accounts = self::getClosedAccounts($s->scheme_number, $start_date, $end_date);
        $s->closed_accounts_charge_ex_vat = $s->closed_accounts * $s->closed_accounts_charge;
        $s->closed_accounts_charge_inc_vat = $s->closed_accounts_charge_ex_vat * $s->remove_vat;
        $s->statements_issued = self::getStatements($s->scheme_number, $start_date, $end_date);
        $s->statements_total_inc_vat = $s->statements_issued * $s->statements_charge;
        $s->statements_total_ex_vat = $s->statements_total_inc_vat / $s->remove_vat;
        $s->no_of_meters = self::getMeters($s->scheme_number, $start_date, $end_date);
        $s->meter_days = ($s->no_of_meters * $s->days);
        $s->meter_total_ex_vat = $s->meter_days * $s->meter_charge;
        $s->meter_total_inc_vat = $s->meter_total_ex_vat * $s->remove_vat;
        $s->app_support = $app_support;
        $s->app_support_ex_vat = $app_support * $s->no_of_meters;
        $s->app_support_inc_vat = $s->app_support_ex_vat * $s->remove_vat;
        $s->blue_accounts_charge = $blue_accounts_charge;
        $s->blue_accounts = self::getBlueAccounts($s->scheme_number, $start_date, $end_date);
        $s->blue_accounts_ex_vat = $s->blue_accounts_charge * $s->blue_accounts * $s->days;
        $s->blue_accounts_inc_vat = $s->blue_accounts_ex_vat * $s->remove_vat;
        $s->invoiced_amount = $s->cost_of_topups_ex_vat + $s->sms_total_ex_vat + $s->app_total_ex_vat + $s->app_support_ex_vat + $s->statements_total_ex_vat + $s->meter_total_ex_vat
        + $s->blue_accounts_ex_vat + $s->premium_sms_total_ex_vat + $s->closed_accounts_charge_ex_vat;

        $s->vat_payment = ($s->cost_of_topups_inc_vat - $s->cost_of_topups_ex_vat) + ($s->sms_total_inc_vat - $s->sms_total_ex_vat) + ($s->app_total_inc_vat - $s->app_total_ex_vat) + ($s->app_support_inc_vat - $s->app_support_ex_vat) + ($s->statements_total_inc_vat - $s->statements_total_ex_vat) +
        ($s->meter_total_inc_vat - $s->meter_total_ex_vat) +
        ($s->blue_accounts_inc_vat - $s->blue_accounts_ex_vat) + ($s->premium_sms_total_inc_vat - $s->premium_sms_total_ex_vat) + ($s->closed_accounts_charge_inc_vat - $s->closed_accounts_charge_ex_vat);

        $s->scheme_payment = $s->value_of_payments - ($s->cost_of_topups_inc_vat + $s->sms_total_inc_vat + $s->app_total_inc_vat + $s->app_support_inc_vat + $s->statements_total_inc_vat
        + $s->meter_total_inc_vat + $s->blue_accounts_inc_vat + $s->premium_sms_total_inc_vat + $s->closed_accounts_charge_inc_vat);

        $s->total_payment = $s->invoiced_amount + $s->vat_payment;
        $s->avg_daily_kwh = self::getAvgs($s->scheme_number, 'avg_daily_kwh', ['from' => $start_date, 'to' => $end_date]);
        $s->avg_daily_cost = self::getAvgs($s->scheme_number, 'avg_daily_cost', ['from' => $start_date, 'to' => $end_date]);
        $s->annual_avg_kwh_day = self::getAvgs($s->scheme_number, 'annual_avg_kwh_day', ['from' => $start_date, 'to' => $end_date]);
        $s->annual_avg_cost_day = self::getAvgs($s->scheme_number, 'annual_avg_cost_day', ['from' => $start_date, 'to' => $end_date]);

        return $s;
    }

    public static function getAvgs($scheme_number, $type, $params = [])
    {
        $scheme = self::where('scheme_number', $scheme_number)->first();

        switch ($type) {
            case 'avg_daily_kwh':
                return self::where('scheme_number', $scheme_number)->first()->avgDailyUsage($params['from'], $params['to']);
            break;
            case 'avg_daily_cost':
                return self::where('scheme_number', $scheme_number)->first()->avgDailyCost($params['from'], $params['to']);
            break;
            case 'annual_avg_kwh_day':
                $year = date('Y', strtotime($params['from']));
                $year_end = date('Y', strtotime($params['to']));
                $start_of_year = $year.'-01-01';
                $end_of_year = $year_end.'-12-31';

                return $scheme->avgDailyUsage($start_of_year, $end_of_year);
            break;
            case 'annual_avg_cost_day':
                $year = date('Y', strtotime($params['from']));
                $year_end = date('Y', strtotime($params['to']));
                $start_of_year = $year.'-01-01';
                $end_of_year = $year_end.'-12-31';

                return $scheme->avgDailyCost($start_of_year, $end_of_year);
            break;
        }
    }

    public static function getPaymentAmount($scheme_number, $from, $to)
    {
        return PaymentStorage::where('scheme_number', $scheme_number)->whereRaw("(time_date >= '$from 00:00:00' AND time_date <= '$to 23:59:59')")->count();
    }

    public static function getPaymentValue($scheme_number, $from, $to)
    {
        return PaymentStorage::where('scheme_number', $scheme_number)->whereRaw("(time_date >= '$from 00:00:00' AND time_date <= '$to 23:59:59')")->sum('amount');
    }

    public static function getSMSMessages($scheme_number, $from, $to)
    {
        return SMSMessage::charged()->where('scheme_number', $scheme_number)->whereRaw("(date_time >= '$from 00:00:00' AND date_time <= '$to 23:59:59')")->count();
    }

    public static function getPremiumSMSMessages($scheme_number, $from, $to)
    {
        return SMSMessage::premiumCharged()->where('scheme_number', $scheme_number)->whereRaw("(date_time >= '$from 00:00:00' AND date_time <= '$to 23:59:59')")->count();
    }

    public static function getSMSCost($scheme_number, $from, $to)
    {
        return SMSMessage::charged()->where('scheme_number', $scheme_number)->whereRaw("(date_time >= '$from 00:00:00' AND date_time <= '$to 23:59:59')")->count();
    }

    public static function getPremiumSMSCost($scheme_number, $from, $to)
    {

        //return SMSMessage::premiumCharged()->where('scheme_number', $scheme_number)->whereRaw("(date_time >= '$from 00:00:00' AND date_time <= '$to 23:59:59')")->sum('charge');
        return SMSMessage::premiumCharged()->where('scheme_number', $scheme_number)->whereRaw("(date_time >= '$from 00:00:00' AND date_time <= '$to 23:59:59')")->count();
    }

    public static function getIOUs($scheme_number, $from, $to)
    {
        return IOUStorage::inScheme($scheme_number)
                ->whereRaw("(time_date >= '$from 00:00:00' AND time_date <= '$to 23:59:59')")->count();
    }

    public static function getBlueAccounts($scheme_number, $from, $to)
    {
        $from = Carbon::parse($from);
        $to = Carbon::parse($to);
        $pmds = PermanentMeterData::where('scheme_number', $scheme_number)
        ->whereRaw('(is_bill_paid_customer = 1 OR is_boiler_room_meter = 1)')
        ->get();

        $customers = Customer::where('id', -2)->get();

        foreach ($pmds as $k => $p) {
            $customer = $p->customer;
            if ($customer) {
                $commencement = Carbon::parse($customer->commencement_date);
                if ($commencement <= $from) {
                    $customers->push($customer);
                }
            }
        }

        return count($customers);
    }

    public static function getStatements($scheme_number, $from, $to)
    {
        $scheme_customers = Customer::where('scheme_number', $scheme_number)->get();
        $statements = 0;
        foreach ($scheme_customers as $k => $c) {
            $statements += count($c->getStatements($from, $to));
        }

        return $statements;
    }

    public static function getClosedAccounts($scheme_number, $from, $to)
    {
        $closed_accounts = DB::table('customers')
        ->where('scheme_number', $scheme_number)
        ->whereRaw("(deleted_at >= '$from 00:00:00' AND deleted_at <= '$to 23:59:59')")
        ->get();

        return count($closed_accounts);
    }

    public static function getMeters($scheme_number, $from, $to)
    {
        return PermanentMeterData::inScheme($scheme_number)->count();
    }

    public static function uniqueUsernames()
    {
        $schemes = self::active(false);
        $names = [];

        foreach ($schemes as $k => $v) {
            try {
                $unique_usernames = Customer::where('scheme_number', $v->scheme_number)
                ->where('ev_owner', 0)
                ->where('username', 'NOT LIKE', '%bloom%')
                ->groupBy(DB::raw("
					(REPLACE
					(REPLACE
					(REPLACE
					(REPLACE
					(REPLACE
					(REPLACE
					(REPLACE
					(REPLACE
					(REPLACE
					(REPLACE (username, '0', ''),
					'1', ''),
					'2', ''),
					'3', ''),
					'4', ''),
					'5', ''),
					'6', ''),
					'7', ''),
					'8', ''),
					'9', ''))
				"))->get();

                foreach ($unique_usernames as $a => $u) {
                    $username = $u->username;
                    $username = strtolower($username);
                    $username = preg_replace('/[0-9]+/', '', $username);
                    $username = ucfirst($username);
                    $username = str_replace('Hfairways', 'Fairways Hall', $username);
                    array_push($names, $username);
                }
            } catch (Exception $e) {
            }
        }

        return $names;
    }

    public function getIPAttribute()
    {
        $IP = '0.0.0.0';

        $dataLogger = DataLogger::where('scheme_number', $this->scheme_number)->first();

        if (! $dataLogger) {
            return $IP;
        }

        $SIM = Simcard::where('ID', $dataLogger->sim_id)->first();

        if (! $SIM) {
            return $IP;
        }

        return $SIM->IP_Address;
    }

    public function getWatchAttribute()
    {
        return SchemeWatch::where('scheme_number', $this->scheme_number)
        ->where('active', 1)->orderBy('id', 'DESC')->first();
    }

    public function checkWatch()
    {
        try {
            $watch = $this->watch;

            if (! $watch) {
                return;
            }

            if (strpos($this->status, 'Online') !== false && $watch->watch_type == 'for_online') {
                $this->notifyWatch('for_online');
                $watch->log('Came back online');
            }

            if (strpos($this->status, 'Offline') !== false && $watch->watch_type == 'for_offline') {
                $this->notifyWatch('for_offline');
                $watch->log('Went online');
            }

            $watch->active = 0;
            $watch->save();
        } catch (Exception $e) {
            //echo $e->getMessage() . " (" . $e->getLine() . ")";
        }
    }

    public function notifyWatch($type)
    {
        $watch = $this->watch;

        try {
            if (! $watch) {
                return;
            }

            $from = 'info@prepago.ie';
            $who = 'Prepago Notifications';
            $subject = SystemSetting::get('var_scheme_watch_subject');
            $emails = SystemSetting::get('var_scheme_watch_emails');
            $emails = explode("\n", str_replace(["\r\n", "\n\r", "\r"], "\n", $emails));

            $data = [
                'scheme' => $this,
                'watch' => $watch,
            ];

            Mail::send('emails.watch_schemes', $data, function ($message) use ($from, $watch, $who, $subject, $emails) {
                $subject .= ' #'.$watch->id;
                $message->from($from, $who)->subject($subject);
                $message->to($emails);
            });

            $watch->log('Sent notification email');
        } catch (Exception $e) {
            $watch->log($e->getMessage().' ('.$e->getLine().')');
        }
    }

    public function getStatusLogsAttribute()
    {
        $logs = [];

        try {
            $track = TrackingScheme::where('scheme_number', $this->scheme_number)
            ->where('date', '>=', date('Y-m-d', strtotime('2 days ago')))
            ->get();

            $track_today = TrackingScheme::where('scheme_number', $this->scheme_number)
            ->orderBy('id', 'DESC')->first();

            $uptime_percent = 100;
            if ($track_today) {
                $uptime_percent = $track_today->uptime_percentage;
            }

            foreach ($track as $k => $t) {
                $l = unserialize($t->status_log);

                foreach ($l as $j => $log) {
                    //var_dump($log);
                    if (isset($log[0])) {
                        array_push($logs, (object) [
                            'status' => 'Offline',
                            'time' => $log[0],
                        ]);
                    }
                    if (isset($log[1])) {
                        array_push($logs, (object) [
                            'status' => 'Online',
                            'time' => $log[1],
                        ]);
                    }
                }
            }

            usort($logs, function ($a, $b) {
                return (new DateTime($a->time)) < (new DateTime($b->time));
            });
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage().' ('.$e->getLine().')',
            ];
        }

        return $logs;
    }
}
