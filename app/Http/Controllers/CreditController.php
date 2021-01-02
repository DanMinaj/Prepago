<?php

class CreditController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function issue_credit($customersAfterSearch = false)
    {
        $data = [];
        $data['title'] = 'Issue Credit';
        if (! $customersAfterSearch) {
            $data['customers'] = $this->getCustomersList();
        } else {
            $data['customers'] = $customersAfterSearch;
        }
        $data['searchFormURL'] = 'issue_credit/search_customers';
        $data['addAmountURL'] = '/issue_credit/add_amount/';
        $data['type'] = 'issue_credit';

        //$this->layout->page = View::make('home/issue_credit')->with($data);
        $this->layout->page = View::make('home/issue_topup')->with($data);
    }

    public function ic_add_amount($amount, $reason)
    {
        $trans = DB::transaction(function () use ($amount, $reason) {
            try {
                $sms_list = Session::get('issue_credit_credit_list');
                foreach ($sms_list as $k => $v) {
                    $aic = new AdminIssuedCredit();
                    $aic->customer_id = $v['id'];
                    $aic->scheme_number = Auth::user()->scheme_number;
                    $aic->date_time = date('Y-m-d H:i:s');
                    $aic->admin_name = Auth::user()->username;
                    $aic->amount = $amount;
                    $aic->reason = $reason;
                    $aic->save();

                    $customer = Customer::where('id', '=', $v['id'])->get()->first();

                    $customer->balance = $customer->balance + $amount;

                    if (($customer->balance > 0) && ($customer->shut_off == 1)) {
                        $customer = $this->setAdditionalCustomerInfo($customer);
                    }

                    $customer->save();

                    //check scu type and add entry in RTUCommandQue
                    $this->checkSCUType($v['id']);
                }
            } catch (Exception $e) {
                return ['error' => $e->getMessage()];
            }
        });

        $selectedCustomerID = Session::get('issue_credit_credit_list')[0]['id'];

        if (isset($trans['error'])) {
            //return Redirect::to('issue_credit')->with('errorMsg', 'An error occured.');
            return Redirect::to('customer_tabview_controller/show/'.$selectedCustomerID)->with('errorMessage', 'An error occured.');
        }

        Session::forget('issue_credit_credit_list');
        //return Redirect::to('issue_credit')->with('successMsg', 'The credit is issued successfully.');
        return Redirect::to('customer_tabview_controller/show/'.$selectedCustomerID)->with('successMessage', 'The credit is issued successfully.');
    }

    public function issue_admin_iou_quick($customer_id)
    {
        try {
            $customer = Customer::find($customer_id);

            if (! $customer) {
                throw new Exception("Customer $customer_id does not exist!");
            }
            $success = $customer->useIOU();

            if (! $success) {
                throw new Exception('Could not issue IOU!');
            }

            return Redirect::to('customer_tabview_controller/show/'.$customer_id)->with('successMessage', 'The IOU was issued successfully.');
        } catch (Exception $e) {
            return Redirect::to('customer_tabview_controller/show/'.$customer_id)->with('errorMessage', 'Error issuing IOU: '.$e->getMessage());
        }
    }

    public function issue_admin_iou($customersAfterSearch = false)
    {
        $data = [];
        $data['title'] = 'Issue Admin IOU';
        if (! $customersAfterSearch) {
            $data['customers'] = $this->getCustomersList();
        } else {
            $data['customers'] = $customersAfterSearch;
        }
        $data['searchFormURL'] = 'issue_admin_iou/search_customers';
        $data['addAmountURL'] = '/issue_admin_iou/add_amount/';
        $data['type'] = 'issue_admin_iou';

        //$this->layout->page = View::make('home/issue_admin_iou');
        $this->layout->page = View::make('home/issue_topup')->with($data);
    }

    public function iai_add_amount($amount, $reason)
    {
        $trans = DB::transaction(function () use ($amount, $reason) {
            try {
                $sms_list = Session::get('issue_admin_iou_credit_list');

                foreach ($sms_list as $k => $v) {
                    $iouType = 0;
                    $iouAvailable = false;
                    $customer = Customer::where('id', '=', $v['id'])->get()->first();

                    if ($customer->IOU_available == 1 && $customer->IOU_used != 1) {
                        $iouAvailable = true;
                        $iouType = 1;
                    } elseif ($customer->IOU_used == 1 && $customer->IOU_extra_available == 1 && $customer->IOU_extra_used == 0) {
                        $iouAvailable = true;
                        $iouType = 2;
                    }

                    if (! $iouAvailable) {
                        throw new Exception('IOU Unavailable');
                    }

                    /*
                    //$customer->admin_IOU_amount = $customer->admin_IOU_amount + $amount;
                    $customer->admin_IOU_amount = $amount;
                    $customer->admin_IOU_in_use = 1;

                    if( ( $customer->balance > ( 0 - $customer->admin_IOU_amount ) ) && ( $customer->shut_off == 1) )
                    {
                        $customer = $this->setAdditionalCustomerInfo($customer, true);
                    }

                    $customer->save();

                    //check sku type and add entry in RTUCommandQue
                    $this->checkSCUType($v['id']);
                    */

                    if (! in_array($iouType, [0, 1, 2])) {
                        throw new Exception('Invalid IOU type');
                    }

                    if (! $customer->email_address || ! $customer->username || ! $customer->password) {
                        throw new Exception('Invalid customer email/username/password');
                    }

                    $requestURL = 'http://www.prepago-admin.biz/prepago_app/iou/'.$customer->id.'/'.$customer->email_address.'/'.
                                    $customer->username.'/'.$customer->password.'/'.$iouType;
                    $result = $this->getResultFromWebService($requestURL);
                    if ($result === false) {
                        throw new Exception('Error fetching the data from the web service');
                    }
                }
            } catch (Exception $e) {
                return ['error' => $e->getMessage()];
            }
        });

        $selectedCustomerID = Session::get('issue_admin_iou_credit_list')[0]['id'];
        if (isset($trans['error'])) {
            //return Redirect::to('issue_admin_iou')->with('errorMsg', 'An error occured.');
            return Redirect::to('customer_tabview_controller/show/'.$selectedCustomerID)->with('errorMessage', 'An error occured - '.$trans['error']);
        }

        Session::forget('issue_admin_iou_credit_list');
        //return Redirect::to('issue_admin_iou')->with('successMsg', 'The admin IOU is issued successfully.');
        return Redirect::to('customer_tabview_controller/show/'.$selectedCustomerID)->with('successMessage', 'The admin IOU is issued successfully.');
    }

    public function issue_topup_arrears($customersAfterSearch = false)
    {
        $data = [];
        $data['title'] = 'Issue Top-Up Arrears';
        if (! $customersAfterSearch) {
            $data['customers'] = $this->getCustomersList();
        } else {
            $data['customers'] = $customersAfterSearch;
        }
        $data['searchFormURL'] = 'issue_topup_arrears/search_customers';
        $data['addAmountURL'] = '/issue_topup_arrears/add_amount/';
        $data['type'] = 'issue_topup_arrears';

        //$this->layout->page = View::make('home/issue_topup_arrears');
        $this->layout->page = View::make('home/issue_topup')->with($data);
    }

    public function ita_add_amount()
    {
        /*
        echo "Amount: &euro;" . $amount . "<br/>";
        echo "Reason: " . $reason . "<br/>";
        echo "Arrears Daily Repayment: &euro;" . $arrears_daily_repayment . "<br/>";

        die();*/

        $amount = Input::get('amount');
        $reason = Input::get('reason');
        $arrears_daily_repayment = Input::get('arrears_daily_repayment');

        $trans = DB::transaction(function () use ($amount, $reason, $arrears_daily_repayment) {
            try {
                $sms_list = Session::get('issue_topup_arrears_credit_list');

                foreach ($sms_list as $k => $v) {
                    $customer = Customer::where('id', '=', $v['id'])->get()->first();
                    $customer->balance = $customer->balance + $amount;
                    $customer->arrears = $customer->arrears + $amount;
                    $customer->arrears_daily_repayment = $arrears_daily_repayment;

                    $customerArrears = new CustomerArrears();
                    $customerArrears->customer_id = $customer['id'];
                    //$customerArrears->scheme_number =  $this->session->userdata('scheme_number');
                    //$customerArrears->amount =  $customer['amount'];
                    //$customerArrears->repayment_amount =  $customer['reason'];
                    $customerArrears->reason = $reason;
                    $customerArrears->scheme_number = Auth::user()->scheme_number;
                    $customerArrears->amount = $amount;
                    $customerArrears->repayment_amount = $arrears_daily_repayment;
                    $customerArrears->date = date('Y-m-d');
                    $customerArrears->save();

                    if (($customer->balance) && ($customer->shut_off == 1)) {
                        $customer = $this->setAdditionalCustomerInfo($customer);
                    }

                    $customer->save();

                    //check sku type and add entry in RTUCommandQue
                    $this->checkSCUType($v['id']);
                }
            } catch (Exception $e) {
                return ['error' => $e->getMessage()];
            }
        });

        $selectedCustomerID = Session::get('issue_topup_arrears_credit_list')[0]['id'];

        if (isset($trans['error'])) {
            //return Redirect::to('issue_topup_arrears')->with('errorMsg', 'An error occured.');
            return Redirect::to('customer_tabview_controller/show/'.$selectedCustomerID)->with('errorMessage', 'An error occured.');
        }

        Session::forget('issue_topup_arrears_credit_list');
        //return Redirect::to('issue_topup_arrears')->with('successMsg', 'The topup is issued successfully.');
        return Redirect::to('customer_tabview_controller/show/'.$selectedCustomerID)->with('successMessage', 'The topup is issued successfully.');
    }

    public function add_creditlist($customer_id, $customer_email, $type = 'issue_credit')
    {
        if (! Session::has($type.'_credit_list')) {
            $credit_list[0]['id'] = $customer_id;
            $credit_list[0]['email'] = $customer_email;

            Session::put($type.'_credit_list', $credit_list);

            return Redirect::to($type);
        } else {
            $credit_list = Session::get($type.'_credit_list');
            $keytracker = 0;
            foreach ($credit_list as $k => $v) {
                $new_credit_list[$keytracker]['id'] = $v['id'];
                $new_credit_list[$keytracker]['email'] = $v['email'];

                $keytracker++;
            }
            $new_credit_list[$keytracker]['id'] = $customer_id;
            $new_credit_list[$keytracker]['email'] = $customer_email;

            Session::put($type.'_credit_list', $new_credit_list);

            return Redirect::to($type);
        }
    }

    public function rem_creditlist($customer_id, $type = 'issue_credit')
    {
        $credit_list = Session::get($type.'_credit_list');
        $keytracker = 0;
        foreach ($credit_list as $k => $v) {
            if ($v['id'] != $customer_id) {
                $new_credit_list[$keytracker]['id'] = $v['id'];
                $new_credit_list[$keytracker]['email'] = $v['email'];
                $keytracker++;
            }
        }
        if (empty($new_credit_list)) {
            Session::forget($type.'_credit_list');
        } else {
            Session::put($type.'_credit_list', $new_credit_list);
        }

        return Redirect::to($type);
    }

    public function check_login($password = '')
    {
        if (Auth::validate(['username' => Auth::user()->username, 'password' => $password]) || $password == 'disabled') {
            return 'valid';
        } else {
            return 'invalid';
        }
    }

    public function search_customers()
    {
        $search_key = Input::get('search_box');

        $customers = $this->getCustomersList($search_key);

        $callMethod = Input::get('type');
        if (method_exists('CreditController', $callMethod)) {
            $this->$callMethod($customers);
        } else {
            $this->issue_credit($customers);
        }
    }

    private function getCustomersList($search_key = '')
    {
        $customers = DB::table('customers')
                        ->select(DB::raw('id,first_name,surname,username,barcode,email_address,mobile_number'))
                        ->where('scheme_number', '=', Auth::user()->scheme_number)
                        ->where(function ($query) use ($search_key) {
                            $query->where('username', 'like', '%'.$search_key.'%')
                                    ->orWhere('first_name', 'like', '%'.$search_key.'%')
                                    ->orWhere('barcode', 'like', '%'.$search_key.'%')
                                    ->orWhere('surname', 'like', '%'.$search_key.'%')
                                    ->orWhere('street1', 'like', '%'.$search_key.'%')
                                    ->orWhere('street2', 'like', '%'.$search_key.'%')
                                    ->orWhere('email_address', 'like', '%'.$search_key.'%')
                                    ->orWhere('mobile_number', 'like', '%'.$search_key.'%')
                                    ->orWhere('town', 'like', '%'.$search_key.'%')
                                    ->orWhere('county', 'like', '%'.$search_key.'%')
                                    ->orWhere('nominated_telephone', 'like', '%'.$search_key.'%');
                        })
                        ->get();

        return $customers;
    }

    private function checkSCUType($customer_id)
    {
        $rtu = Customer::join('district_heating_meters', 'customers.meter_ID', '=', 'district_heating_meters.meter_ID')
                    ->where('customers.id', '=', $customer_id)
                    ->get()
                    ->first();

        $scu_type = $rtu['scu_type'];

        //check which type of RTU is being used
        if ($scu_type == 'a' || $scu_type == 'd' || $scu_type == 'm') {
            $rtuCommandQue = new RTUCommandQue();
            $rtuCommandQue->customer_ID = $customer_id;
            $rtuCommandQue->meter_id = $rtu->meter_ID;
            $rtuCommandQue->permanent_meter_id = $rtu->permanent_meter_ID;
            $rtuCommandQue->turn_service_on = 1;
            $rtuCommandQue->shut_off_device_contact_number = $rtu->shut_off_device_contact_number;
            $rtuCommandQue->port = $rtu->port;
            $rtuCommandQue->save();
        } elseif ($scu_type == 'b') {
            //code to do
        } elseif ($scu_type == 'c') {
            //code to d
        }
    }

    private function setAdditionalCustomerInfo($customerObj, $ignoreCreditWarningSentField = false)
    {
        $meter_id = $customerObj->meter_ID;

        //added when using issue topup arrears
        if ($ignoreCreditWarningSentField) {
            $customerObj->credit_warning_sent = 0;
        }
        //$customer->shut_off = 0; Removed as this is now performed by the RTU Command Que program
        $customerObj->shut_off_command_sent = 0;
        $customerObj->IOU_available = 0;
        $customerObj->IOU_used = 0;
        $customerObj->IOU_extra_used = 0;

        //JNS CODE
        //$customer->shut_off = 0;
        $customerObj->shut_off = 0;

        DistrictHeatingMeter::where('meter_ID', '=', $meter_id)->update(['scheduled_to_shut_off' => 0, 'shut_off_device_status' => 0]);

        return $customerObj;
    }

    protected function getResultFromWebService($requestURL)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $requestURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $jsonRes = json_decode($result);

        if (isset($jsonRes->IOUsuccess) && $jsonRes->error === 'Wrong choice') {
            return $jsonRes;
        }

        if (isset($jsonRes->error) && $jsonRes->error) {
            return false;
        }

        return $jsonRes;
    }

    public function add_amount()
    {
        try {
            $ref_num = Input::get('ref_number');
            if (empty($ref_num)) {
                $ref_num = 'ADMIN-'.time();
            }

            $customer_id = Input::get('customer_id');
            $amount = Input::get('amount');
            $reason = Input::get('reason');
            $time = Input::get('time');

            if (empty($time)) {
                $time = date('Y-m-d H:i:s');
            }

            if ($amount <= 0) {
                throw new Exception('Amount must be greater than 0.00!');
            }
            $customer = Customer::find($customer_id);
            if (! $customer) {
                throw new Exception('Customer '.$customer_id.' does not exist!');
            }
            $paymentData = [];
            $paymentData['ref_number'] = $ref_num;
            $paymentData['customer_id'] = $customer->id;
            $paymentData['scheme_number'] = $customer->scheme_number;
            $paymentData['barcode'] = isset($customer->barcode) ? $customer->barcode : ''; //rc customers don't have barcode
            $paymentData['time_date'] = $time;
            $paymentData['currency_code'] = $customer->scheme->currency_sign;
            $paymentData['amount'] = $amount;
            $paymentData['transaction_fee'] = 0.0;

            if (strpos($ref_num, 'PAYID') !== false) {
                $paymentData['acceptor_name_location_'] = 'paypal';
            } else {
                $paymentData['acceptor_name_location_'] = 'admin_issue_topup';
            }
            $paymentData['payment_received'] = 1;
            $paymentData['settlement_date'] = (new DateTime($time))->format('Y-m-d');
            $paymentData['merchant_type'] = 0;
            $paymentData['POS_entry_mode'] = 0;
            $paymentData['restored_payment'] = 1;

            if (! PaymentStorage::create($paymentData)) {
                throw new Exception('The payment cannot be added');
            }

            $customerData = [];
            $customerData['last_top_up'] = date('Y-m-d H:i:s');
            $customerData['IOU_available'] = 0;
            $customerData['IOU_used'] = 0;
            $customerData['IOU_extra_available'] = 0;
            $customerData['IOU_extra_used'] = 0;
            $customerData['admin_IOU_in_use'] = 0;
            if (! $customer->update($customerData)) {
                throw new Exception('The customer data cannot be updated');
            }

            $ps = PaymentStorage::where('ref_number', $paymentData['ref_number'])->first();
            $customer->topup($ps);

            $aic = new AdminIssuedCredit();
            $aic->customer_id = $customer_id;
            $aic->scheme_number = $customer->scheme_number;
            $aic->date_time = date('Y-m-d H:i:s');
            $aic->admin_name = Auth::user()->username;
            $aic->amount = $amount;
            $aic->reason = $reason;
            $aic->ps_id = $ps->ref_number;
            $aic->save();

            return Redirect::back()->with('successMessage', 'Successfully issued '.$amount." to the customer's balance!");
        } catch (Exception $e) {
            return Redirect::back()->with('errorMessage', 'An error occured: '.$e->getMessage());
        }
    }

    public function remove_amount()
    {
        try {
            $customer_id = Input::get('customer_id');
            $amount = Input::get('amount');
            $reason = Input::get('reason');

            if ($amount <= 0) {
                throw new Exception('Amount must be greater than 0.00!');
            }
            $customer = Customer::find($customer_id);
            if (! $customer) {
                throw new Exception('Customer '.$customer_id.' does not exist!');
            }
            $customer->balance -= $amount;
            $customer->save();

            $adc = new AdminDeductedCredit();
            $adc->customer_id = $customer_id;
            $adc->scheme_number = $customer->scheme_number;
            $adc->date_time = date('Y-m-d H:i:s');
            $adc->admin_name = Auth::user()->username;
            $adc->amount = $amount;
            $adc->reason = $reason;
            $adc->save();

            return Redirect::back()->with('successMessage', 'Successfully deducted '.$amount." from customer's balance!");
        } catch (Exception $e) {
            return Redirect::back()->with('errorMessage', 'An error occured: '.$e->getMessage());
        }
    }

    public function authorise_topup($customer_id)
    {
        try {
            $amount = Input::get('amount');
            $ref = Input::get('ref');
            $date = Input::get('date');
            $customer = Customer::find($customer_id);

            if (! $customer) {
                throw new Exception("Customer $customer_id does not exist");
            }
            $ps = PaymentStorage::where('ref_number', $ref)->first();

            if ($ps) {
                throw new Exception("This payment ref '$ref' was already inserted");
            }
            $ps = $customer->addPayment($ref, $amount, $date);
        } catch (Exception $e) {
            return Response::json([
                'error' => $e->getMessage(),
            ]);
        }
    }
}
