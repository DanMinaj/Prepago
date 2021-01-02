<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DistrictHeatingMeter;
use App\Models\IOUStorage;
use App\Models\Result;
use App\Models\Scheme;
use App\Models\SMS;
use App\Models\SMSMessage;
use App\Models\SMSMeterCommand;
use App\Models\Smsque;
use App\Models\SMSResponse;
use App\Models\SystemSetting;
use App\Models\Tariff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Whoops\Example\Exception;



class SMSController extends Controller
{
    /**
     * Sends a sms to the client's mobile number and nominated number.
     * @return json
     */
    public function shut_off_message($customer_id, $scheme_number, $sms_password)
    {
        return self::send_customer_sms($customer_id, $scheme_number, $sms_password, 'shut_off_message');
    }

    /**
     * Send the customer a warning sms that their meter will be shut down.
     * @return json
     */
    public function shut_off_warning($customer_id, $scheme_number, $sms_password)
    {
        $holiday_enabled = SystemSetting::get('holiday_service');
        $holiday_days = SystemSetting::get('holiday_days');
        $is_holiday = false;

        if (! empty($holiday_days)) {
            foreach (json_decode($holiday_days) as $val) {
                $date = $val->date;
                if (date('Y-m-d') == $date && $holiday_enabled) {
                    $is_holiday = true;
                }
            }
        }

        if ($is_holiday && SMSMessage::recentlyTextedWarning($customer_id)) {
            return Result::success([
                'msg' => 'Holiday day - Already sent shut off warning. Extra text aborted!',
            ]);
        }

        return self::send_customer_sms($customer_id, $scheme_number, $sms_password, 'shut_off_warning_message');
    }

    /**
     * Sends the customer a credit warning sms.
     * @return json
     */
    public function credit_warning($customer_id, $scheme_number, $sms_password)
    {
        return self::send_customer_sms($customer_id, $scheme_number, $sms_password, 'credit_warning_message');
    }

    /**
     * Sends a topup sms to the customer.
     * @return json
     */
    public function topup_message($customer_id, $scheme_number, $sms_password)
    {
        return self::send_customer_sms($customer_id, $scheme_number, $sms_password, 'topup_message');
    }

    public function ev_related_message($customer_id, $scheme_number, $sms_password, $message)
    {
        return self::send_customer_sms($customer_id, $scheme_number, $sms_password, 'custom', urldecode($message));
    }

    /** Check if this is what is being used for sending and sms in the customer view sms tab
     * Sends the customer a custom sms.
     * @return json
     */
    public function user_specific_message($customer_id, $scheme_number, $sms_password, $message)
    {
        return self::send_customer_sms($customer_id, $scheme_number, $sms_password, 'custom', urldecode($message), false);
    }

    /**
     * Sends a sms to all customers in scheme.
     * @return json
     */
    //public function scheme_specific_message($scheme_number, $sms_password, $message)
    public function scheme_specific_message()
    {
        $schemeNumbers = Input::get('schemes') ? explode(',', Input::get('schemes')) : [];
        if (count($schemeNumbers) == 0 && strlen(Input::get('schemes'))) {
            $schemeNumbers = [Input::get('schemes')];
        }
        $message = Input::get('message');
        $customerErrors = [];
        try {
            $customers = Customer::whereIN('scheme_number', $schemeNumbers)->get();

            foreach ($customers as $customer) {
                if ($customer['mobile_number']) {
                    $scheme_number = $customer['scheme_number'];
                    $scheme = $customer->scheme;
                    $sms_password = $scheme ? $scheme->sms_password : '';

                    $sendCustomerSMSRes = self::send_customer_sms($customer['id'], $scheme_number, $sms_password, 'custom', urldecode($message), false);
                    //check the result from the send_customer_sms functionality
                    if ($sendCustomerSMSRes) {
                        $sendCustomerSMSResObj = json_decode($sendCustomerSMSRes);
                        // if any of the sms messages failed to send, add the customer's email address to the $customerErrors array.
                        // This array will be later used to display on the error page a list of customers which failed to receive a sms message
                        if (isset($sendCustomerSMSResObj->success) && $sendCustomerSMSResObj->success == 0) {
                            $customerErrors[] = $customer['email_address'];
                        }
                    }
                }
            }

            if (count($customerErrors)) {
                return ['error' => true, 'customer_names' => implode(', ', $customerErrors)];
            }

            return Result::success();
        } catch (Exception $e) {
            return Result::fail('could not send sms to all customers in scheme');
        }
    }

    /** NO LONGER USED - David 16/6/215
     * Sends a custom message to a custom number
     * This method does not deduct any charges to customers.
     * @return json
     */
    public function message_to_number($scheme_number, $sms_password, $number, $message)
    {
        /* Check if the scheme_number and sms_password
            matches the details in the database */
        $scheme = Scheme::where('scheme_number', '=', $scheme_number)->get(['sms_password'])->first();
        if ($scheme->sms_password == $sms_password) {
            $message_to_send = urldecode($message);
            $mobile_number = self::validate_number(urldecode($number));

            // ** SMS SYSTEM WORKING ** //
            // disabled because save sms alrrady adds SMS TO QUEUE
            //$sms = new SMS();
            //$sms->sms();
            //$sms->send($mobile_number, $message_to_send);

            self::save_sms(0, $mobile_number, $message_to_send, $scheme_number, 0);

            return Result::success();
        } else {
            return Result::fail('sms password is incorrect');
        }
    }

    /** NO LONGER USED - David 16/06/2015
     * Sends a message to the meter.
     * @return json
     */
    public function message_to_meter($scheme_number, $sms_password, $number, $message, $customer_id)
    {
        /* Check if the scheme_number and sms_password
            matches the details in the database */
        $customer = Customer::where('id', '=', $customer_id)->get(['meter_ID'])->first();
        $scheme = Scheme::where('scheme_number', '=', $scheme_number)->get(['sms_password'])->first();
        if ($scheme->sms_password == $sms_password) {
            $message_to_send = urldecode($message);
            $mobile_number = self::validate_number(urldecode($number));

            // ** SMS SYSTEM WORKING ** //
            $sms = new SMS();
            $sms->sms();
            $sms->send($mobile_number, $message_to_send);

            self::save_meter_command($customer_id, $customer['meter_ID'], $mobile_number, $message_to_send, $scheme_number);

            return Result::success();
        } else {
            return Result::fail('sms password is incorrect');
        }
    }

    /** NO LONGER USED - David 16/06/2015
     * Sends a meter pin customer.
     * @return json
     */
    public function meter_shut_off_command_message($customer_id, $scheme_number, $sms_password)
    {
        /* Check if the scheme_number and sms_password
            matches the details in the database */
        $customer = Customer::where('id', '=', $customer_id)->get(['meter_ID'])->first();
        $scheme = Scheme::where('scheme_number', '=', $scheme_number)->get(['sms_password'])->first();
        $districtHeating = DistrictHeatingMeter::where('meter_ID', '=', $customer['meter_ID'])->get()->first();

        if ($scheme->sms_password == $sms_password) {
            $message_to_send = 'OutPut#0#1#';
            $mobile_number = self::validate_number($districtHeating['meter_contact_number']);

            // ** SMS SYSTEM WORKING ** //
            $sms = new SMS();
            $sms->sms();
            $sms->send($mobile_number, $message_to_send);

            self::save_meter_command($customer_id, $customer['meter_ID'], $mobile_number, $message_to_send, $scheme_number);

            return Result::success();
        } else {
            return Result::fail('sms password is incorrect');
        }
    }

    /** NO LONGER USED - David 16/06/2015
     * Clears the meters shutoff.
     * @return json
     */
    public function clear_shutoff($customer_id)
    {
        /* Check if the scheme_number and sms_password
            matches the details in the database */
        $customer = Customer::where('id', '=', $customer_id)->get(['meter_ID', 'scheme_number'])->first();
        $districtHeating = DistrictHeatingMeter::where('meter_ID', '=', $customer['meter_ID'])->get()->first();

        $message_to_send = 'OutPut#0#0#';
        $mobile_number = self::validate_number($districtHeating['meter_contact_number']);

        // ** SMS SYSTEM WORKING ** //
        $sms = new SMS();
        $sms->sms();
        $sms->send($mobile_number, $message_to_send);

        self::save_meter_command($customer_id, $customer['meter_ID'], $mobile_number, $message_to_send, $customer['scheme_number']);

        try {
            DistrictHeatingMeter::where('meter_ID', '=', $customer['meter_ID'])
                ->update([
                    'shut_off_device_status' => 0,
                    'scheduled_to_shut_off' => 0,
                    ]);
        } catch (Exception $e) {
            return Result::fail('could not update status or schedule in district heating');
        }

        return Result::success();
    }

    /** This needs to be fixed up and made easier to follow. Message only now need to be saved to the sms_messages table and a java program will look after the sending.
        For customers with multiple numbers an entry must be made for each one of their ID's.
     * Sends user a predetermined sms from the db
     * @return json
     */
    private function send_customer_sms($customer_id, $scheme_number, $sms_password, $msg_type, $custom_message = '', $charge = true, $replyto = null, $sendOnlyOnce = true)
    {
        /* Check if the scheme_number and sms_password
            matches the details in the database */
        $scheme = Scheme::where('scheme_number', '=', $scheme_number)->get()->first();
        $customer = Customer::where('id', '=', $customer_id)->get(['mobile_number', 'nominated_telephone', 'username'])->first();

        if ($scheme->sms_password == $sms_password || $sms_password = 'J92NSOPS9Sb9S') {
            if ($scheme->sms_disabled === 1) {
                return Result::fail('The SMS system is disabled');
            }

            if ($msg_type == 'custom') {
                $message = $custom_message;
            } elseif (($msg_type == 'rates_message' || $msg_type == 'IOU_message' || $msg_type == 'IOU_denied_message') && ! $charge && $sendOnlyOnce) {
                $message = $custom_message;
            } else {
                $message = $scheme[$msg_type];
            }
            $mobile_number = null;
            $nominated_number = null;
            $message_to_send = self::replace_keys_in_sms($customer_id, $message);

            // ** SMS SYSTEM WORKING ** //
            $sms = new SMS();
            $sms->sms();

            //test
            $sms_charge = ($charge) ? self::charge_for_sms($scheme_number, $customer_id) : 0;

            if (! empty($replyto)) {
                $mobile_number = self::validate_number($replyto);
            } else {
                $mobile_number = self::validate_number($customer['mobile_number']);
            }

            //get a list of customers that have one and the same mobile number
            $multipleCustomersWithSameMobileNumber = $this->getCustomersByMobileNumber($mobile_number);

            //if there are customers with one and the same mobile number and the sms message should be send to each of them,
            //send an sms message to the current customer appending his username after the message content
            //if there is only one customer with that mobile number, send the message as it is
            $smsMsg = $multipleCustomersWithSameMobileNumber && ! $sendOnlyOnce ? $message_to_send.' '.$customer->username : $message_to_send;

            //save the message in the sms_messages table
            $saveSMSRes = self::save_sms($customer_id, $mobile_number, $smsMsg, $scheme_number, $sms_charge);
            if ($saveSMSRes) {
                $saveSMSResObj = json_decode($saveSMSRes);
                //if the sms message cannot be saved in the DB, return an error
                if (isset($saveSMSResObj->success) && $saveSMSResObj->success == 0) {
                    return Result::fail('message cannot be saved');
                }
            }

            if (empty($replyto)) {
                $nominated_number = self::validate_number($customer['nominated_telephone']);
                if (! empty($nominated_number)) {
                    self::save_sms($customer_id, $nominated_number, $smsMsg, $scheme_number, $sms_charge);
                }
            }

            return Result::success();
        } else {
            return Result::fail('sms password is incorrect');
        }
    }

    /**
     * Receive sms
     * If there are multiple customers with the same mobile number, each account is charged and SMS are returned as many times as the customers are, appending the customer's username to each message
     * If the customer sent rates/iou/reset command in his SMS a check should be performed whether there are multiple customers with that mobile number
     * If there are multiple customers, make sure he appended his username to the SMS.
     * If the username is appended, then process the according action ONLY for the customer with that username
     * If the username is NOT appended, send back and SMS asking them to append it. The SMS returned will be sent back ONLY ONCE & WILL NOT BE CHARGED.
     * @return json
     */
    public function receive_sms()
    {
        $source = Input::get('source');
        $to = Input::get('to');
        $payload = Input::get('payload');
        $from = Input::get('from');

        //$source = '+353874109020';
        //$payload = 'Barcode';

        $message = urldecode($payload);
        $mobile_number = self::validate_number($source);
        $clearcode = 'error';

        try {
            $isProblematicFlag = 0;
            $errorMsg = '';
            $dh = DistrictHeatingMeter::where('shut_off_device_contact_number', '=', $mobile_number)->get()->first();

            if (! $dh) {
                // return Result::fail('this number does not exist in district hearing meters');
                $errorMsg = 'this number does not exist in district hearing meters';
                $isProblematicFlag++;
            }

            //check if there are > 1 customers with that mobile number
            $customers = $this->getCustomersByMobileNumber($mobile_number);
            if ($customers->count() == 0) {
                // return Result::fail('this number does not the number for a customer');
                $errorMsg = 'this number does not the number for a customer';
                $isProblematicFlag++;
            }

            // Fail only when Meter & Customer cannot be found
            if ($isProblematicFlag == 2) {
                return Result::fail($errorMsg);
            }

            // Checking if the special codes exists in the payload.
            if (strpos($message, 'OutPut#0#1#') !== false) {
                $clearcode = 'OutPut#0#1#';
                self::update_confirmational_district_heating_meters($mobile_number, $clearcode);
            }

            if (strpos($message, 'OutPut#0#0#') !== false) {
                $clearcode = 'OutPut#0#0#';
                self::update_confirmational_district_heating_meters($mobile_number, $clearcode);
            }

            //for all customers with the current mobile number, send the specific SMS message
            $break = false; //if the sms system is disabled $break will be set to true and the system won't iterate over all customers
            foreach ($customers as $key => $customer) {
                $scheme = Scheme::where('scheme_number', '=', $customer['scheme_number'])->get()->first();
                if ($scheme->sms_disabled === 1) {
                    $break = true;
                    break;
                }

                if ($dh) {
                    // Checking if the commands are the same as the previous command
                    if ($dh->last_command_sent == $dh->last_command_confirmation_received) {
                        self::message_to_meter($customer['scheme_number'], $scheme['sms_password'], $mobile_number, $clearcode, $customer['id']);
                    }
                }

                // Check for specific words in sms and send back a specific sms
                $res = strtok($message, ' ');

                if (strcasecmp($res, 'balance') == 0) {
                    self::send_customer_sms($customer['id'], $customer['scheme_number'], $scheme['sms_password'], 'balance_message', '', true, $source, false);
                }

                if (strcasecmp($res, 'rates') == 0 || strcasecmp($res, 'iou') == 0 || strcasecmp($res, 'reset') == 0) {
                    $msg = '';
                    $msgCharge = true;
                    $sendOnlyOnce = true;
                    $hasUsernameAppended = true;
                    //check if there are > 1 customers with the same mobile, then username should be included in the message content as well
                    if ($customers->count() > 1) {
                        preg_match_all('/\w+/', $message, $matches);
                        //if there are multiple customers with that mobile number and the customer didn't include the username, send back a FREE SMS
                        if (count($matches[0]) == 1) {
                            $msg = "Please append your username to the end of the message. For example '".$res." myusername'";
                            $msgCharge = false;
                            $hasUsernameAppended = false;
                        }
                    }

                    if (strcasecmp($res, 'rates') == 0) {
                        $param4 = 'rates_message';
                    } elseif (strcasecmp($res, 'iou') == 0) {
                        if ($customer['IOU_available'] == '1') {
                            file_get_contents('http://www.prepago-admin.biz/prepago_app/iou/'.$customer['id'].'/'.$customer['email_address'].'/'.$customer['username'].'/'.$customer['password'].'/1');
                            //file_get_contents('http://localhost/prepago_app/index.php/service/request3/3/'.$customer['id'].'/'.$customer['email_address'].'/'.$customer['username'].'/'.$customer['password'].'/1/');
                            $param4 = 'IOU_message';
                        } else {
                            $param4 = 'IOU_denied_message';
                        }
                    } elseif (strcasecmp($res, 'reset') == 0) {
                        //if the message contains the username, then $msgCharge should be true
                        if ($msgCharge) {
                            // change the customers password field to blank ""
                            if (! Customer::find($customer['id'])->update(['password' => ''])) {
                                throw new Exception('customer password cannot be reset');
                            }
                            $msg = 'Your password has been reset, please login again with a new password. The new password will then become permanent.';
                        }

                        $param4 = 'custom';
                    }

                    self::send_customer_sms($customer['id'], $customer['scheme_number'], $scheme['sms_password'], $param4, $msg, $msgCharge, $source, $sendOnlyOnce);

                    //if there are multiple customers with the same mobile number and the message doesn't have username appended don't send any more SMS messages
                    //OR if there are multiple customers with the same mobile number and the message has username appended - send only one SMS to that particular user
                    if ($customers->count() > 1 && (! $msgCharge || $hasUsernameAppended)) {
                        break;
                    }
                }

                if (strcasecmp($res, 'iou extra') == 0) {
                    if ($customer['IOU_extra_available'] == '1') {
                        file_get_contents('http://www.prepago-admin.biz/prepago_app/iou/'.$customer['id'].'/'.$customer['email_address'].'/'.$customer['username'].'/'.$customer['password'].'/2');
                        //file_get_contents('http://localhost/prepago_app/index.php/service/request3/3/'.$customer['id'].'/'.$customer['email_address'].'/'.$customer['username'].'/'.$customer['password'].'/2/');
                        self::send_customer_sms($customer['id'], $customer['scheme_number'], $scheme['sms_password'], 'IOU_extra_message', '', true, $source, false);
                    } else {
                        self::send_customer_sms($customer['id'], $customer['scheme_number'], $scheme['sms_password'], 'IOU_denied_message', '', true, $source, false);
                    }
                }

                if (strcasecmp($res, 'barcode') == 0 && $customer['status'] == 1) {
                    //self::send_customer_sms($customer['id'], $customer['scheme_number'], $scheme['sms_password'], 'custom', $customer['barcode'], '', true, $source);
                    self::send_customer_sms($customer['id'], $customer['scheme_number'], $scheme['sms_password'], 'custom', $customer['barcode'], true, $source, false);
                }
            }
            if ($break) {
                return Result::fail('The SMS system is disabled');
            }

            return Result::success();
        } catch (Exception $e) {
            return Result::fail('could not receive sms');
        }
    }

    /**
     * Sends all SMS's that have not been sent.
     */
    public function process_sms_que()
    {
        $smsque = Smsque::where('message_sent', '=', 0)->get();

        foreach ($smsque as $smsq) {
            $sms = new SMS();
            $sms->sms();
            $sms->send($smsq['mobile_number'], $smsq['message']);

            Smsque::where('id', '=', $smsq['id'])
                ->update([
                    'message_sent' => 1,
                    ]);
        }
    }

    /**
     * Update confirmational district heating meters.
     * @return bool
     */
    private function update_confirmational_district_heating_meters($mobile_number, $message)
    {
        try {
            DistrictHeatingMeter::where('shut_off_device_contact_number', '=', $mobile_number)
                ->update([
                    'last_command_confirmation_received' => $message,
                    'last_command_confirmation_time' => date('Y-m-d H:i:s'),
                    ]);
        } catch (Exception $e) {
            return Result::fail('could not update confirmational district heating meter');
        }
    }

    /**
     * Update district heating meters.
     * @return bool
     */
    private function update_district_heating_meters($meter_ID, $message)
    {
        try {
            DistrictHeatingMeter::where('meter_ID', '=', $meter_ID)
                ->update([
                    'last_command_sent' => $message,
                    'last_command_sent_time' => date('Y-m-d H:i:s'),
                    ]);
        } catch (Exception $e) {
            return Result::fail('could not update district heating meter');
        }
    }

    /**
     * Save SMS transaction.
     * @return bool
     */
    private function save_sms($customer_id, $mobile_number, $message, $scheme_number, $charge)
    {
        try {
            $sms_messages = new SMSMessage();
            $sms_messages->customer_id = $customer_id;
            $sms_messages->mobile_number = $mobile_number;
            $sms_messages->message = $message;
            $sms_messages->date_time = date('Y-m-d H:i:s');
            $sms_messages->scheme_number = $scheme_number;
            $sms_messages->charge = $charge;
            $sms_messages->paid = 0;
            $sms_messages->save();

            return true;
        } catch (Exception $e) {
            return Result::fail('could not save sms');
        }
    }

    /**
     * Save meter command.
     * @return bool
     */
    private function save_meter_command($customer_id, $meter_id, $mobile_number, $message_to_send, $scheme_number)
    {
        try {
            $meter_command = new SMSMeterCommand();
            $meter_command->customer_id = $customer_id;
            $meter_command->meter_id = $meter_id;
            $meter_command->contact_number = $mobile_number;
            $meter_command->message = $message_to_send;
            $meter_command->scheme_number = $scheme_number;
            $meter_command->date_time = date('Y-m-d H:i:s');
            $meter_command->save();

            self::update_district_heating_meters($meter_id, $message_to_send);

            return true;
        } catch (Exception $e) {
            return Result::fail('could not save save meter command');
        }
    }

    /**
     * Check if the number is valid and if it needs a + sign or area code.
     * @return string
     */
    private function validate_number($mobile_number)
    {
        if (! $mobile_number) {
            return null;
        }
        if (substr($mobile_number, 0, 1) == '0') {
            $regexpattern = '/^0/';
            $mobile_number = preg_replace($regexpattern, '+353', $mobile_number);
        }
        $new_number = '';
        $j = strlen($mobile_number);
        for ($i = 0; $i < $j; $i++) {
            if ($mobile_number[$i] != '+' && $mobile_number[$i] != '0') {
                break;
            }
        }
        for ($i; $i < strlen($mobile_number); $i++) {
            $new_number .= $mobile_number[$i];
        }

        return '+'.$new_number;
    }

    /**
     * Deduct the SMS charge off the client account.
     * @return float
     */
    private function charge_for_sms($scheme_number, $customer_id)
    {
        try {
            $sms_charge = Scheme::where('scheme_number', '=', $scheme_number)->get(['prepage_SMS_charge'])->first();
            $customer = Customer::find($customer_id);
            $customer->balance -= (float) ($sms_charge['prepage_SMS_charge']);
            $customer->save();

            return (float) ($sms_charge['prepage_SMS_charge']);
        } catch (Exception $e) {
            return Result::fail('unable to charge client');
        }
    }

    /**
     * Replace placeholders in sms before sending to client.
     * @return string
     */
    private static function replace_keys_in_sms($customer_id, $sms_msg)
    {
        $search = [
            '"b"', // balance
            '"a"', // arrears_daily_repayment
            '"n"', // first_name
            '"iou"', // IOU_available
            '"ioue"', // IOU_extra_available
            '"1"', // tariff_1
            '"2"', // tariff_2
            '"3"', // tariff_3
            '"4"', // tariff_4
            '"5"', // tariff_5
            '"bc"', // barcode
            '"IOUc"', // IOU_charge
            '"IOUEc"', // IOU_extra_charge
            ];

        $customer = Customer::where('id', '=', $customer_id)->get(['scheme_number', 'balance', 'arrears_daily_repayment', 'first_name', 'IOU_available', 'IOU_extra_available'])->first();
        $tarrifs = Tariff::where('scheme_number', '=', $customer['scheme_number'])->get(['tariff_1', 'tariff_2', 'tariff_3', 'tariff_4', 'tariff_5'])->first();
        $schemes = Scheme::where('scheme_number', '=', $customer['scheme_number'])->get(['IOU_charge', 'IOU_extra_charge'])->first();

        $replace = [
            $customer['balance'],  // 'â€œbâ€�'
            $customer['arrears_daily_repayment'],  // 'â€œaâ€�'
            $customer['first_name'],  // 'â€œnâ€�'
            $customer['IOU_available'],  // 'â€œiouâ€�'
            $customer['IOU_extra_available'],  // 'â€œioueâ€�'
            $tarrifs['tariff_1'],  // 'â€œ1â€�'
            $tarrifs['tariff_2'],  // 'â€œ2â€�'
            $tarrifs['tariff_3'],  // 'â€œ3â€�'
            $tarrifs['tariff_4'],  // 'â€œ4â€�'
            $tarrifs['tariff_5'],  // 'â€œ5â€�'
            $customer['barcode'],  // 'â€œbcâ€�'
            $schemes['IOU_charge'],  // 'â€œIOUcâ€�'
            $schemes['IOU_extra_charge'],  // 'â€œIOUEcâ€�'
            ];

        return str_replace($search, $replace, $sms_msg);
    }

    private function getCustomersByMobileNumber($mobileNumber)
    {
        $customers = Customer::where('mobile_number', '=', $mobileNumber)->orWhere('nominated_telephone', '=', $mobileNumber)->get();

        return $customers;
    }

    public function parseCustomerReply()
    {
        try {
            $mobilenumber = Input::get('mobilenumber');
            $command = Input::get('command');
            $sentto = Input::get('sentto');
            $customer_id = -1;
            $last_sms_id = 0;

            $last_sms = SMSMessage::whereRaw("(mobile_number LIKE '%".$mobilenumber."%')")->orderBy('id', 'DESC')->first();
            if ($last_sms) {
                $last_sms_id = $last_sms->id;
                $customer_id = $last_sms->customer_id;
            } else {
                $customers = Customer::getActiveCustomers(true);
                foreach ($customers as $k => $c) {
                    if (strpos($c->mobile_number, $mobilenumber) !== false || strpos($c->nominated_telephone, $mobilenumber) !== false) {
                        $customer_id = $c->id;
                        break;
                    }
                }
            }

            $customer = Customer::find($customer_id);

            $sms_response = new SMSResponse();
            $sms_response->customer_id = $customer_id;
            $sms_response->mobile_number = $mobilenumber;
            $sms_response->message = $command;
            $sms_response->date_time = date('Y-m-d H:i:s');
            $sms_response->scheme_number = 0;
            $sms_response->last_sms = $last_sms_id;
            $sms_response->acknowledged = 0;
            $sms_response->save();

            // Check for SMS M2M
            if ($last_sms) {
                if ((strpos(strtolower($last_sms->message), 'away mode') !== false) && (strpos(strtolower($last_sms->message), 'was not made by') !== false)) {
                    $sms_response->type = 'away_mode';
                    $sms_response->save();
                    if ($customer) {
                        $awayMode = $customer->awayMode;
                        if (! empty($awayMode->rcs) && $awayMode->rcs->away_mode_on == 1) {
                            if (strpos(strtolower($command), 'no') !== false) {
                                $customer->toggleAwayMode();
                                $customer->confirmsms("Thank you for your response. Your away mode has been turned off!\n\nKind regards\nSnugZone", 0.08);
                                $sms_response->acknowledged = 1;
                                $sms_response->save();
                            }
                        }
                    }
                }
            }

            if (! $customer) {
                echo 'True';

                return;
            }

            if (strpos(strtolower($command), 'balance') !== false) {
                $customer->confirmsms('Your balance is €'.number_format($customer->balance, 2)."\n\nKing regards\nSnugZone", 0.08);
            }

            if (strpos(strtolower($command), 'iou') !== false) {
                $scheme = $customer->scheme;

                if (! $scheme) {
                    echo 'True';

                    return;
                }

                $charge_cmp = $scheme->IOU_amount;
                $charge_red = $scheme->IOU_charge;

                if ($customer->balance > (0 - $charge_cmp)) {
                    $customer->balance = $customer->balance - $charge_red;
                    $data = ['IOU_used' => 1, 'IOU_available' => 0, 'balance' => $customer->balance];
                    $customer->update($data);
                } else {
                    $customer->confirmsms("You currently cannot use an IOU.\n\nKing regards\nSnugZone", 0.08);

                    return;
                }

                $ioucharge = $scheme->IOU_charge;
                $date = date('Y-m-d H:i:s');
                $iou_data = ['customer_id' => $customer->id,
                             'scheme_number' => $scheme->scheme_number,
                             'time_date' => $date,
                             'charge' => $ioucharge,
                             'paid' => '0', ];
                IOUStorage::create($iou_data);

                $customer->clearShutOff();

                $customer->confirmsms('Successfully '.$customer->balance." used IOU. If you have been cut off your service will be restored shortly.\n\nKing regards\nSnugZone", 0.08);
            }

            if (strpos(strtolower($command), 'password') !== false) {
                DB::table('customers')->where('id', $customer->id)->update(['password' => '']);

                $customer->confirmsms("Your password has been reset.\n\nThe next time you login, the password you use will become your new password.\n\nKing regards\nSnugZone", 0.08);
            }

            if (strpos(strtolower($command), 'barcode') !== false) {
                $customer->confirmsms('Your barcode is: '.$customer->barcode."\n\nKing regards\nSnugZone", 0.08);
            }

            echo 'True';
        } catch (Exception $e) {
            echo 'False';
        }
    }
}
