<?php

class FinalizeRechargeStopProcedure extends EVRechargeManager
{
    public function handle($manually)
    {
        if (! $this->performChecksAndInit()) {
            return $this->errorResponse();
        }

        if (! $manually) {
            if (! $this->meter->rechargeInProgress()) {
                $this->errorMsg = 'The EV Meter with RS Code '.$this->rsCode.' is currently not in use.';

                return $this->errorResponse();
            }

            $this->rechargeManualStopProcedure();
        }

        $this->meter->unblock();

        $this->disassociateDistrictHeatingMeterFromCustomer();

        $customer_starting_balance = $this->customer->balance;

        $chargeFee = $this->getChargeFee();
        $this->chargeCustomer($chargeFee);

        if ($manually) {
            $this->sendRechargeFeeSMS($this->customer->scheme_number, $chargeFee);
        } else {
            $this->sendStopRechargeSMS($this->customer->scheme_number);
        }

        $insertedReport = false;
        $insertedReportError = '';

        try {
            $this->insertRechargeReport($customer_starting_balance);
            $insertedReport = true;
        } catch (Exception $e) {
            $insertedReport = false;
            $insertedReportError = $e->getMessage();
        }

        return [
            'ev_recharge_status' => 'off',
            'flag_message' => 0,
            'error' => '',
            'inserted_report' => $insertedReport,
            'inserted_report_error' => $insertedReportError,
        ];
    }

    protected function insertRechargeReport($starting_balance)
    {
        $recharge_report = new EVRechargeReport();
        $ev_usage = $this->findEVUsage();

        $stopped = date('Y-m-d H:i:s');
        $to_time = strtotime($stopped);
        $from_time = strtotime($ev_usage->ev_timestamp);
        $seconds = $to_time - $from_time;

        $recharge_report->ev_meter_ID = $ev_usage->ev_meter_ID;
        $recharge_report->ev_stop_timestamp = $stopped;
        $recharge_report->ev_usage_id = $ev_usage->id;
        $recharge_report->ev_total_kwh = ($ev_usage->end_day_reading - $ev_usage->start_day_reading);
        $recharge_report->ev_length_of_charge = $seconds;
        $recharge_report->ev_cost_of_charge = $ev_usage->unit_charge;
        $recharge_report->ev_rs_code = $this->rsCode;
        $recharge_report->ev_recharge_location = $this->meter->ev_rs_address;
        $recharge_report->customer_id = $this->customer->id;
        $recharge_report->customer_fullname = $this->customer->first_name.' '.$this->customer->surname;
        $recharge_report->customer_start_bal = $starting_balance;
        $recharge_report->customer_end_bal = $this->customer->balance;
        $recharge_report->save();

        $this->emailRechargeReport($recharge_report->id);
    }

    protected function emailRechargeReport($report_id)
    {
        try {
            $json = json_decode(file_get_contents('http://sys.prepaygo.com/send_mail/'.$this->customer->id.'/recharge_customer_report/'.$report_id), true);
        } catch (Exception $e) {
        }
    }

    protected function chargeCustomer($chargeFee)
    {
        if ($chargeFee === false) {
            return $this->errorResponse();
        }

        $this->customer->charge($chargeFee);
    }

    protected function sendRechargeFeeSMS($schemeNumber, $chargeFee)
    {
        $cur_balance = $this->customer->balance;
        $message = 'Your recharge fee is '.number_format($chargeFee, 2).' euro. Your current balance is '.number_format($cur_balance, 2).' euro.';
        $errorMessage = 'There was an error while sending the Recharge Fee SMS';
        $this->sendEVRechargeRelatedSMS($schemeNumber, $message, $errorMessage);
    }

    protected function sendStopRechargeSMS($schemeNumber)
    {
        $message = 'Your recharge stopped. Maximum recharge fee reached.';
        $errorMessage = 'There was an error while sending the Recharge Stop SMS';
        $this->sendEVRechargeRelatedSMS($schemeNumber, $message, $errorMessage);
    }

    private function sendEVRechargeRelatedSMS($schemeNumber, $message, $errorMessage)
    {
        $scheme = Scheme::where('scheme_number', '=', $schemeNumber)->first();
        //$details_url ='http://prepago/prepago_admin/sms/user_specific_message/' . $this->customer->id . '/' . $schemeNumber . '/' . $scheme->sms_password .'/'. urlencode($message) .'/';

        $smsRes = \Illuminate\Support\Facades\App::make('SMSController')->ev_related_message($this->customer->id, $schemeNumber, $scheme->sms_password, $message);
        $dataJson = json_decode($smsRes);
        if (isset($dataJson->success) && $dataJson->success == 0) {
            throw new Exception($errorMessage);
        }

        /*$options = Array(
            CURLOPT_RETURNTRANSFER => TRUE, // Setting cURL's option to return the webpage data
            CURLOPT_FOLLOWLOCATION => TRUE, // Setting cURL to follow 'location' HTTP headers
            CURLOPT_AUTOREFERER => TRUE, // Automatically set the referer where following 'location' HTTP headers
            CURLOPT_CONNECTTIMEOUT => 120, // Setting the amount of time (in seconds) before the request times out
            CURLOPT_TIMEOUT => 120, // Setting the maximum amount of time for cURL to execute queries
            CURLOPT_MAXREDIRS => 10, // Setting the maximum number of redirections to follow
            CURLOPT_USERAGENT => "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1a2pre) Gecko/2008073000 Shredder/3.0a2pre ThunderBrowse/3.2.1.8", // Setting the useragent
            CURLOPT_URL => $details_url // Setting cURL's URL option with the $url variable passed into the function
        );
        $ch = curl_init(); // Initialising cURL
        curl_setopt_array($ch, $options); // Setting cURL's options using the previously assigned array data in $options

        $data = curl_exec($ch); // Executing the cURL request and assigning the returned data to the $data variable

        curl_close($ch); // Closing cURL

        $dataJson = json_decode($data);
        if (isset($dataJson->success) && $dataJson->success == 0)
        {
            throw new Exception($errorMessage);
        }*/
    }
}
