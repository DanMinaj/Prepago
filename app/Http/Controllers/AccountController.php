<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Whoops\Example\Exception;

class AccountController extends Controller
{
    protected $layout = 'layouts.admin_website';
    private $validator;
    private $mail;
    private $log;

    public function __construct(BaseValidator $validator, MailRepository $mail)
    {
        $this->validator = $validator;
        $this->mail = $mail;
        $this->log = new Logger('View Customer Set Up Logs');
        $this->log->pushHandler(new StreamHandler(storage_path('logs/customer_setup_'.date('Y-m-d').'.log'), Logger::INFO));
    }

    public function open_account($customer_id = null)
    {
        $usernames = [];
        $customerToSwap = '';

        //if we've been redirected to the customer setup page after trying to delete a customer
        if ($customer_id) {
            $customerToSwap = Customer::find($customer_id);
            if (! $customerToSwap) {
                return redirect('close_account')->with('errorMsg', 'Customer Not Found');
            }

            //get customer to swap's username from permanent_meter_data table
            /*$meterInfo = DistrictHeatingMeter::join('permanent_meter_data', 'permanent_meter_ID', '=', 'ID')
                        ->where('meter_ID', '=', $customerToSwap->meter_ID)
                        ->first();
            $customerToSwap->pm_username = $meterInfo ? $meterInfo->username : '';*/
        } else {
            $usernames = PermanentMeterData::join('sim_cards', 'sim_cards.ID', '=', 'permanent_meter_data.sim_ID')
                ->select('permanent_meter_data.in_use', 'username', 'meter_number', 'house_name_number', 'street1')
                ->where('permanent_meter_data.meter_type', '!=', 'EV')
                ->where('permanent_meter_data.in_use', '=', 0)
                //->where('is_boiler_room_meter', '=', 0)
                //->where('is_boiler_room_meter', '!=', 1)
                ->where('permanent_meter_data.installation_confirmed', '=', 1)
                ->where('permanent_meter_data.scheme_number', '=', Auth::user()->scheme_number)
                ->orderBy('permanent_meter_data.ID', 'ASC')
                ->groupby('username')
                ->get();
        }

        $this->layout->page = view('home/customer_setup_view')->with('usernames', $usernames)->with('customerToSwap', $customerToSwap);

        //Saving this code for a change made by David Byrne on the 12/09/2014, the fix is to stop other schemes meters showing up in the add new customer page
        //$usernames = PermanentMeterData::join('sim_cards', 'sim_cards.ID', '=', 'permanent_meter_data.sim_ID')->where('in_use', '=', 0)->where('is_boiler_room_meter', '=', 0)->where('is_boiler_room_meter', '!=', 1)->where('permanent_meter_data.installation_confirmed', '=', 1)->groupby('username')->get();
        //$this->layout->page = view('home/customer_setup_view', array('usernames' => $usernames));
    }

    public function open_account_queue()
    {
        $in_queue = CustomerQueue::orderBy('id', 'DESC')->whereRaw('(completed = 0)')->get();
        $finished_queue = CustomerQueue::orderBy('id', 'DESC')->whereRaw('(completed = 1 AND failed = 0)')->get();
        $failed_queue = CustomerQueue::orderBy('id', 'DESC')->whereRaw('(completed = 1 AND failed = 1)')->get();

        $this->layout->page = view('home/open_account_queue')
        ->with('in_queue', $in_queue)
        ->with('finished_queue', $finished_queue)
        ->with('failed_queue', $failed_queue);
    }

    public function open_account_queue_action()
    {
        try {
            $action = Input::get('action');
            $queue_id = Input::get('q');

            $queue = CustomerQueue::where('id', $queue_id)->first();

            if (! $queue) {
                throw new \Exception("Queue $queue_id does not exist");
            }
            switch ($action) {
                case 'run':
                    $queue->execute();

                    return redirect('open_account/queue')->with(['successMessage' => "Successfully ran queue #$queue_id"]);
                break;
                case 'cancel':
                    $queue->cancel();

                    return redirect('open_account/queue')->with(['successMessage' => "Successfully cancelled queue #$queue_id"]);
                break;
                case 'undo':
                    $queue->undo();

                    return redirect('open_account/queue')->with(['successMessage' => "Successfully un-did queue #$queue_id"]);
                break;
                case 'redo':
                    $queue->redo();

                    return redirect('open_account/queue')->with(['successMessage' => "Successfully restarted queue #$queue_id"]);
                break;
            }
        } catch (\Exception $e) {
            return redirect('open_account/queue')
            ->with([
                'errorMessage' => $e->getMessage().' ('.$e->getLine().')',
            ]);
        }
    }

    public function open_account_action()
    {
        $queue = (Input::get('submit') == 'queue');
        $customerQueue = CustomerQueue::where('username', Input::get('username'))->where('completed', 0)->first();

        if ($queue) {
            $scheme_number = Auth::user()->scheme_number;
            $meter_number = Input::get('select_units');
            $username = Input::get('username');
            $first_name = Input::get('first_name');
            $surname = Input::get('surname');
            $email_address = Input::get('email_address');
            $mobile_number = Input::get('mobile_number');
            $nominated_telephone = Input::get('nominated_telephone');
            $balance = Input::get('balance');
            $starting_balance = Input::get('balance');
            $arrears = Input::get('arrears');
            $arrears_daily_repayment = Input::get('arrears_daily_repayment');
            $commencement_date = Input::get('commencement_date');

            if (! $customerQueue) {
                $customer_queue = new CustomerQueue();
                $customer_queue->scheme_number = $scheme_number;
                $customer_queue->type = 'open_account';
                $customer_queue->meter_number = $meter_number;
                $customer_queue->username = $username;
                $customer_queue->first_name = $first_name;
                $customer_queue->surname = $surname;
                $customer_queue->email_address = $email_address;
                $customer_queue->mobile_number = $mobile_number;
                $customer_queue->nominated_telephone = $nominated_telephone;
                $customer_queue->balance = $balance;
                $customer_queue->starting_balance = $starting_balance;
                $customer_queue->arrears = $arrears;
                $customer_queue->arrears_daily_repayment = $arrears_daily_repayment;
                $customer_queue->commencement_date = $commencement_date;
                $customer_queue->save();

                return redirect('open_account')->with('successMessage', "Successfully queue customer setup of $username for $commencement_date. (Queue #".$customer_queue->id.')')->withInput();
            } else {
                $customerQueue->type = 'open_account';
                $customerQueue->scheme_number = $scheme_number;
                $customerQueue->meter_number = $meter_number;
                $customerQueue->username = $username;
                $customerQueue->first_name = $first_name;
                $customerQueue->surname = $surname;
                $customerQueue->email_address = $email_address;
                $customerQueue->mobile_number = $mobile_number;
                $customerQueue->nominated_telephone = $nominated_telephone;
                $customerQueue->balance = $balance;
                $customerQueue->starting_balance = $starting_balance;
                $customerQueue->arrears = $arrears;
                $customerQueue->arrears_daily_repayment = $arrears_daily_repayment;
                $customerQueue->commencement_date = $commencement_date;
                $customerQueue->failed = 0;
                $customerQueue->failed_id = 0;
                $customerQueue->failed_msg = '';
                $customerQueue->save();

                return redirect('open_account')->with('successMessage', "Updated queue of $username for $commencement_date. (Queue #".$customerQueue->id.')')->withInput();
            }

            //$this->log->addInfo('The customer was set up successfully');

            return redirect('open_account')->with('errorMessage', 'Disabled')->withInput();
        } else {

            //validate user's data
            $errors = [];

            if (! $this->validator->isValid(PermanentMeterData::$rules, PermanentMeterData::$customAttributeNames, PermanentMeterData::$customErrorMessages)) {
                $errors = $this->validator->getErrors();
            }

            if (! $this->validator->isValid(Customer::$rules, Customer::$customAttributeNames, Customer::$customErrorMessages)) {
                $customerErrors = $this->validator->getErrors();
                //merge validation errors from the Customer model into the the validation errors from the PermanentMeterData model
                if (count($errors)) {
                    $errors->merge($customerErrors);
                } else {
                    $errors = $customerErrors;
                }
            }

            $action = Input::get('swap_from_id') ? 'swap' : 'create';

            if (count($errors)) {
                return redirect($action === 'swap' ? 'open_account/swap/'.(int) Input::get('swap_from_id') : 'open_account')->withInput()->withErrors($errors);
            }

            $this->log->addInfo('Validation passed. Starting the transaction');

            $trans = DB::transaction(function () use ($action) {
                try {

                    //if we are adding new customer and not swapping on delete
                    if ($action === 'swap') {
                        $this->log->addInfo('Action is SWAP');
                        $meterID = (int) Input::get('selected_unit');
                        //get permanent meter information
                        $permanentMeterID = DistrictHeatingMeter::find($meterID) ? DistrictHeatingMeter::find($meterID)->permanent_meter_ID : '';
                        if ($permanentMeterID) {
                            $permanentMeterNumber = PermanentMeterData::find($permanentMeterID) ? PermanentMeterData::find($permanentMeterID)->meter_number : '';
                            if ($permanentMeterNumber) {
                                $permanentMeterData = $this->get_prepopulation($permanentMeterNumber);
                            } else {
                                //return redirect('open_account')->with('errorMessage', '<b> An error occured: </b> No entry in the permanent meters for this customer was found');
                                throw new \Exception('No entry in the permanent meters for this customer was found');
                            }
                        } else {
                            //return redirect('open_account')->with('errorMessage', '<b> An error occured: </b> No entry in the district heating meters for this customer was found');
                            throw new \Exception('No entry in the district heating meters for this customer was found');
                        }
                    } else {
                        $this->log->addInfo('Get permanent_meter_data joined with sim_cards');
                        //permanent_meter_data joined with sim_cards
                        $permanentMeterData = $this->get_prepopulation(Input::get('select_units'));
                        if (! $permanentMeterData->count()) {
                            //return redirect('open_account')->with('errorMessage', '<b> An error occured: </b> No information about the selected permanent meter was found.');
                            $this->log->addInfo('No information about the selected permanent meter was found');
                            throw new \Exception('No information about the selected permanent meter was found.');
                        }

                        /* INSERT ENTRY IN THE DISTRICT_HEATING_METERS TABLE */
                        $this->log->addInfo('Insert entry in the DISTRICT_HEATING_METERS table');
                        $dhmData = [];
                        $dhmData['meter_number'] = Input::get('select_units');
                        $dhmData['meter_contact_number'] = $permanentMeterData['IP_Address'];
                        $dhmData['shut_off_device_contact_number'] = $permanentMeterData['IP_Address'];
                        $dhmData['port'] = $permanentMeterData['scu_port'];
                        $dhmData['scu_type'] = $permanentMeterData['scu_type'];
                        $dhmData['permanent_meter_ID'] = $permanentMeterData['permanent_meter_data_ID'];
                        $dhmData['scheme_number'] = $permanentMeterData['scheme_number'];
                        $this->log->addInfo('$dhmData', $dhmData);

                        $dhmExists = DistrictHeatingMeter::where('meter_number', $dhmData['meter_number'])
                        ->first();
                        if ($dhmExists && $dhmExists->customers()->first()) {
                            $error_res = "An error occured: A customer already has that meter_number '".$dhmData['meter_number']."' <br/>";
                            $error_res .= 'Please modify the meter number of either customer via the installer website.<br/>';
                            if ($dhmExists->customers()->first()) {
                                $error_res .= " <a href='".URL::to('customer_tab_view', ['id' => $dhmExists->customers()->first()->id])."'>(This customer is currently utilizing that meter_number)</a>";
                                $this->log->addInfo('A district heating meter with this meter number "'.$dhmData['meter_number'].'" already exists!');
                                throw new \Exception('A district heating meter with this meter number "'.$dhmData['meter_number'].'" already exists! It is in use by customer '.$dhmExists->customers()->first()->id);
                            }

                            //return redirect('open_account')->with('errorMessage', $error_res);
                            $this->log->addInfo('A district heating meter with this meter number "'.$dhmData['meter_number'].'" already exists!');
                            throw new \Exception('A district heating meter with this meter number "'.$dhmData['meter_number'].'" already exists!');
                        }

                        if (! $dhmInfo = DistrictHeatingMeter::create($dhmData)) {
                            //return redirect('open_account')->with('errorMessage', '<b> An error occured: </b> The district heating meter data cannot be saved.');
                            $this->log->addInfo('The district heating meter data cannot be saved');
                            throw new \Exception('The district heating meter data cannot be saved.');
                        }

                        $meterID = $dhmInfo->meter_ID;
                        $this->log->addInfo('$meterID', [$meterID]);
                    }

                    /* UPDATE THE CUSTOMERS TABLE */
                    $this->log->addInfo('UPDATE THE CUSTOMERS TABLE');
                    //find next available id that has status set to 0
                    $this->log->addInfo('find next available id that has status set to 0');
                    $customerInfo = Customer::where('status', '=', 0)->first();
                    $customer_id = isset($customerInfo['id']) ? $customerInfo['id'] : '';
                    $this->log->addInfo('$customer_id', [$customer_id]);
                    if ($customer_id) {
                        $customerData = [];
                        $customerData['role'] = Input::get('role');
                        $customerData['balance'] = Input::get('balance');
                        $customerData['starting_balance'] = Input::get('balance');
                        $customerData['meter_ID'] = $meterID;
                        $customerData['first_name'] = Input::get('first_name');
                        $customerData['surname'] = Input::get('surname');
                        $customerData['arrears'] = Input::get('arrears');
                        $customerData['arrears_daily_repayment'] = Input::get('arrears_daily_repayment');
                        $customerData['username'] = Input::get('username');
                        $customerData['email_address'] = Input::get('email_address');
                        $customerData['mobile_number'] = Input::get('mobile_number');
                        $customerData['nominated_telephone'] = Input::get('nominated_telephone');
                        $customerData['commencement_date'] = Input::get('commencement_date');
                        $customerData['scheme_number'] = isset($permanentMeterData['scheme_number']) ? $permanentMeterData['scheme_number'] : '';
                        $customerData['house_number_name'] = isset($permanentMeterData['house_name_number']) ? $permanentMeterData['house_name_number'] : '';
                        $customerData['street1'] = isset($permanentMeterData['street1']) ? $permanentMeterData['street1'] : '';
                        $customerData['street2'] = isset($permanentMeterData['street2']) ? $permanentMeterData['street2'] : '';
                        $customerData['town'] = isset($permanentMeterData['town']) ? $permanentMeterData['town'] : '';
                        $customerData['county'] = isset($permanentMeterData['county']) ? $permanentMeterData['county'] : '';
                        $customerData['country'] = isset($permanentMeterData['country']) ? $permanentMeterData['country'] : '';
                        $customerData['postcode'] = isset($permanentMeterData['postcode']) ? $permanentMeterData['postcode'] : '';
                        $customerData['status'] = 1;
                        $this->log->addInfo('$customerData', $customerData);
                        if (! $customer = $customerInfo->update($customerData)) {
                            $this->log->addInfo('The customer data cannot be updated');
                            throw new \Exception('The customer data cannot be updated.');
                        }
                    }

                    //if there have been values entered for arrears and arrears daily repayment, add an entry in the customer_arrears table
                    $this->log->addInfo('if there have been values entered for arrears and arrears daily repayment, add an entry in the customer_arrears table');
                    if ($customer_id && Input::get('arrears') && Input::get('arrears') > 0 && Input::get('arrears_daily_repayment') && Input::get('arrears_daily_repayment') > 0) {
                        $customerArrearsData = [];
                        $customerArrearsData['customer_id'] = $customer_id;
                        $customerArrearsData['scheme_number'] = Auth::user()->scheme_number;
                        $customerArrearsData['amount'] = Input::get('arrears');
                        $customerArrearsData['repayment_amount'] = Input::get('arrears_daily_repayment');
                        $customerArrearsData['date'] = date('Y-m-d');
                        $this->log->addInfo('$customerArrearsData', $customerArrearsData);

                        if (! $customerArrears = CustomerArrears::create($customerArrearsData)) {
                            $this->log->addInfo('The data cannot be saved in the customer_arrears table');
                            throw new \Exception('The data cannot be saved in the customer_arrears table.');
                        }
                    }

                    if ($action === 'create') {
                        //if the insert and update operations above were successful -> set permanent_meter_data.in_use to 1
                        $this->log->addInfo('if the insert and update operations above were successful -> set permanent_meter_data.in_use to 1');
                        if (! PermanentMeterData::find($permanentMeterData['permanent_meter_data_ID'])->update(['in_use' => 1])) {
                            $this->log->addInfo('The permanent meter "in_use" field cannot be set to 1');
                            throw new \Exception('The permanent meter "in_use" field cannot be set to 1.');
                        }
                    }

                    if ($action === 'swap') {
                        $this->log->addInfo('Swapping customer');
                        $swapFromCustomer = Customer::find((int) Input::get('swap_from_id'));
                        if (! $swapFromCustomer) {
                            throw new \Exception('The customer we\'re trying to delete doesn\'t exist');
                        }

                        //When the new customer is created and they are in positive credit or on 0 credit and the old customer was customers.shut_off add an entry to the rtu_command_que
                        if ($customer_id && $customerData['balance'] >= 0 && $swapFromCustomer->shut_off == '1') {
                            //get the information about the DHM
                            $dhm = DistrictHeatingMeter::find($meterID);
                            $data = [
                                'customer_ID' => $customer_id,
                                'meter_id' => $meterID,
                                'turn_service_on' => 1,
                                'shut_off_device_contact_number' => $dhm->shut_off_device_contact_number,
                                'permanent_meter_id' => $dhm->permanent_meter_ID,
                                'scheme_number' => $customerData['scheme_number'],
                                'port' => $dhm->port,
                            ];
                            RTUCommandQue::create($data);
                        }

                        //if we're swapping customers, delete the customer we're swapping FROM
                        if (! $swapFromCustomer->delete()) {
                            throw new \Exception('Cannot delete the customer we\'re swapping from');
                        }
                    }

                    return ['customer_id' => $customer_id];
                } catch (Exception $e) {
                    return ['error' => $e->getMessage()];
                }
            });

            if (isset($trans['error']) || ! $trans['customer_id']) {
                $this->log->addInfo('There was an error with the transaction');

                return redirect('open_account/customer_setup_error');
            }

            $customerID = $trans['customer_id'];
            $this->log->addInfo('$customerID AFTER the transaction', [$customerID]);

            //send email to the customer
            $this->log->addInfo('Send email to the customer');
            $currencySign = '';
            $customerInfo = '';
            $schemeInfo = null;

            if ($customerID) {
                $customerInfo = Customer::where('id', '=', $customerID)->first();
                $schemeNumber = isset($customerInfo->scheme_number) ? $customerInfo->scheme_number : '';
                $this->log->addInfo('$schemeNumber', [$schemeNumber]);
                if ($schemeNumber !== '') {
                    $schemeInfo = Scheme::where('scheme_number', '=', $schemeNumber)->first();
                    $currencySign = $schemeInfo->currency_sign;
                    $this->log->addInfo('$currencySign', [$currencySign]);
                }
            }

            if ($schemeInfo != null && $schemeInfo->isBlueScheme) {
            } else {
                $mailData = [];
                $mailData['first_name'] = Input::get('first_name');
                $mailData['email_address'] = Input::get('email_address');
                $mailData['username'] = Input::get('username');
                $mailData['currency_sign'] = $currencySign;
                $mailData['starting_balance'] = Input::get('balance');
                $this->log->addInfo('$mailData', $mailData);
                $this->mail->sendCustomerSetUpEmail($mailData);

                //send SMS to the customer
                $this->log->addInfo('send SMS to the customer');
                $smsSent = $this->sendSMSAfterCustomerSetUp($customerID);
                if (! $smsSent) {
                    $this->log->addInfo('SMS cannot be sent to the customer');

                    return redirect('open_account')->with('errorMessage', 'SMS cannot be sent to the customer.');
                }
            }

            if ($customerQueue) {
                $customerQueue->completed = 1;
                $customerQueue->completed_at = date('Y-m-d H:i:s');
                $customerQueue->save();
            }

            if ($action === 'create') {
                $this->log->addInfo('The customer was set up successfully');

                if ($schemeInfo != null && $schemeInfo->isBlueScheme && $customerID) {
                    return redirect('open_account')->with('successMessage', "The customer was set up successfully. <a href='/customer_tabview_controller/show/".$customerID."'>Visit the customer.</a>");
                } else {
                    return redirect('open_account')->with('successMessage', 'The customer was set up successfully.');
                }
            } else {
                return redirect('close_account')->with('successMessage', 'The customer was set up successfully in place of the deleted one.');
            }
        }
    }

    public function customer_setup_error()
    {
        $this->layout->page = view('home/customer_setup_error');
    }

    public function get_prepopulation($meter_number)
    {
        $meter = PermanentMeterData::join('sim_cards', 'sim_cards.ID', '=', 'permanent_meter_data.sim_ID')
                    ->select('permanent_meter_data.ID as permanent_meter_data_ID', 'permanent_meter_data.*', 'sim_cards.*')
                    ->where('meter_number', '=', $meter_number)->get()->first();

        return $meter;
    }

    public function search_apt()
    {
        $apartment = Input::get('apartment');

        try {
            $pmd = PermanentMeterData::where('username', $apartment)->first();
            if ($pmd) {
                $has_customer = Customer::where('username', $apartment)->first();

                if ($has_customer) {
                    return 'This apartment has already been setup & associated with a customer!';
                }

                $scheme = Scheme::find($pmd->scheme_number);
                $scheme_name = $scheme->scheme_nickname;

                if ($pmd->in_use == 1) {
                    $pmd->in_use = 0;
                }

                if ($pmd->installation_confirmed == 0) {
                    $pmd->installation_confirmed = 1;
                }

                $pmd->save();

                return $pmd->username.'|'.$pmd->meter_number.'|'.$pmd->house_name_number." $scheme_name|success";
            } else {
                return 'This apartment has no data associated with it in the database, please create a ticket!';
            }
        } catch (Exception $e) {
            echo 'Error: '.$e->getMessage();
            die();
        }
    }

    public function rectify_apt()
    {
    }

    public function close_account()
    {
        if (Request::isMethod('post')) {
            //if we want only to delete a customer without swapping or activating a deleted landlord
            if (! Input::get('swap_to_id')) {
                $res = $this->close_account_action((int) Input::get('swap_from_id'));

                if ($res['errors']) {
                    redirect('close_account')->with('errorMessage', $res['errors']);
                }
            } else {
                $trans = DB::transaction(function () {
                    try {
                        //delete the customer we're swapping from
                        if (! Customer::find((int) Input::get('swap_from_id'))->delete()) {
                            throw new \Exception('The customer swapping from cannot be deleted');
                        }

                        //set the deleted_at timestamp to null for the customer we're swapping to
                        if (! Customer::onlyTrashed()->find((int) Input::get('swap_to_id'))->update(['deleted_at' => null])) {
                            throw new \Exception('Cannot make the selected customer active again');
                        }
                    } catch (Exception $e) {
                        return ['error' => $e->getMessage()];
                    }
                });

                if (isset($trans['error'])) {
                    return redirect('close_account')->with('errorMessage', 'Customer cannot be deleted');
                }
            }

            return redirect('close_account')->with('successMessage', 'Customer was deleted successfully');
        } else {
            $customers = Customer::where('status', '=', 1)->where('scheme_number', '=', Auth::user()->scheme_number)->get();
            $deletedLandlordsList = new \Illuminate\Database\Eloquent\Collection();
            foreach ($customers as $customer) {
                //get deleted landlords list (needed if we're deleting a normal customer)
                $deletedLandlordsList[$customer->id] = Customer::onlyTrashed()->where('role', '=', 'landlord')->where('meter_ID', '=', $customer->meter_ID)->get(['id', 'username']);
            }
            $this->layout->page = view('home/close_account_view')->withCustomers($customers)->withLandlords($deletedLandlordsList);
        }
    }

    public function close_account_alt()
    {
        $customers = Customer::where('status', '=', 1)->where('scheme_number', '=', Auth::user()->scheme_number)->get();

        $this->layout->page = view('home/close_account_alternative_view')->withCustomers($customers);
    }

    public function close_account_alt_download($id)
    {
        $customer = Customer::find($id);

        if (! $customer) {
            return Redirect::back()->with('errorMessage', 'Customer Not Found');
        }

        $data = '';

        $data .= 'Generated at: '.date('Y-m-d H:i:s')."\n";
        $data .= 'Operator: '.Auth::user()->username."\n\n";

        if ($customer->scheme()->first()) {
            $scheme = $customer->scheme()->first();
            $data .= 'Scheme: '.$scheme->company_name."\n\n";
        }

        $data .= "Customer information\n";
        $data .= 'ID: ,'.$customer->id."\n";
        $data .= 'Barcode: ,'.$customer->barcode."\n";
        $data .= 'Fullname: ,'.$customer->first_name.' '.$customer->surname."\n";
        $data .= 'Username: ,'.$customer->username."\n";
        $data .= 'Email address: ,'.$customer->email_address."\n";
        $data .= 'Mobile number: ,'.$customer->mobile_number."\n";

        if ($customer->districtMeter && $customer->permanentMeter) {
            $data .= "\n\nMeter Data\n";
            $data .= 'District meter ID: ,'.$customer->districtMeter->meter_ID."\n";
            $data .= 'Meter number: ,'.$customer->districtMeter->meter_number."\n";
            $data .= 'Meter number 2: ,'.$customer->permanentMeter->meter_number2."\n";
            $data .= 'Latest reading: ,'.$customer->districtMeter->latest_reading.' kWh ('.$customer->districtMeter->latest_reading_time.")\n";
            $data .= 'Sudo reading: ,'.$customer->districtMeter->sudo_reading.' kWh ('.$customer->districtMeter->sudo_reading_time.")\n";
            $data .= 'Last return temp: ,'.$customer->districtMeter->last_return_temp." degrees\n";

            $data .= "\nPermanent Meter Data\n";
            $data .= 'Permanent meter ID: ,'.$customer->permanentMeter->ID."\n";
            $data .= 'Type: ,'.$customer->permanentMeter->scu_type."\n";
            $data .= 'In use: ,'.$customer->permanentMeter->in_use."\n";
            $data .= 'SCU number: ,'.$customer->permanentMeter->scu_number."\n";
            $data .= 'Shut off: ,'.$customer->permanentMeter->shut_off."\n";
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=del_customer_'.$id.'_info.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $data;

        die();
    }

    public function close_account_alt_confirm($id)
    {
        $customer = Customer::find($id);

        if (! $customer) {
            return Redirect::back()->with('errorMessage', 'Customer Not Found');
        }

        if (Request::isMethod('post')) {
            $res = $this->close_account_action($id);

            if ($res['errors']) {
                redirect('close_account_alt')->with('errorMessage', $res['errors']);
            }

            return redirect('close_account_alt')->with('successMessage', 'Customer was deleted successfully');
        } else {
            $this->layout->page = view('home/close_account_alternative_view_confirm')->with(['customer' => $customer]);
        }
    }

    public function close_account_search()
    {
        $search_term = Input::get('search_box');

        $customers = $this->search_customers($search_term);

        $this->layout->page = view('home/close_account_view', ['customers' => $customers]);
    }

    public function close_account_action($customer_number)
    {

        //delete customer
        $customer = Customer::find($customer_number);
        if (! $customer) {
            return redirect('close_account')->with('errorMsg', 'Customer Not Found');
        }

        $trans = DB::transaction(function () use ($customer) {
            try {
                //delete the customer (soft delete)
                if (! $customer->delete()) {
                    throw new \Exception('Customer cannot be deleted');
                }

                //set meter_number and permanent_meter_ID attributes to null in DHM
                $dhm = DistrictHeatingMeter::find($customer->meter_ID);
                if (! $dhm) {
                    throw new \Exception('District heating meter not found');
                }

                $permanentMeterID = $dhm->permanent_meter_ID;
                if (! $dhm->update(['meter_number' => null, 'permanent_meter_ID' => null])) {
                    throw new \Exception('District heating meter data cannot be updated');
                }

                //set permanent_meter_data.in_use to 0
                $pmd = PermanentMeterData::find($permanentMeterID);
                if (! $pmd) {
                    throw new \Exception('Permanent meter data not found');
                }
                if (! $pmd->update(['in_use' => 0])) {
                    throw new \Exception('Permanent meter data cannot be updated');
                }

                $customerDeletion = new CustomerDeletion();
                $customerDeletion->customer_id = $customer->id;
                $customerDeletion->operator_id = Auth::user()->id;
                $customerDeletion->reason = 'n/a';
                $customerDeletion->save();
            } catch (Exception $e) {
                return ['error' => $e->getMessage()];
            }
        });

        if (isset($trans['error'])) {
            return ['errors' => $trans['error']];
        }

        return ['errors' => ''];

        //$this->layout->page = view('home/close_account_actions')->with('customer', $customer)->with('deletedLandlordsList', $deletedLandlordsList);

        /*Customer::where('id', '=', $customer_number)->update(array('status' => 2, 'username' => $username.'_d'));*/
        /*$customerInfo = Customer::where('id', '=', $customer_number)->get()->first();
        if ($customerInfo)
        {
            Customer::where('id', '=', $customer_number)->update(['status' => 2, 'username' => $customerInfo->username . '_d']);
            $meter = DistrictHeatingMeter::where('meter_ID', '=', $customerInfo->meter_ID)->get()->first();
            //delete the district heating meter
            DistrictHeatingMeter::where('meter_ID', '=', $customerInfo->meter_ID)->delete();
            //DistrictHeatingMeter::where('meter_ID', '=', $customerInfo->meter_ID)->update(['meter_number' => null, 'shut_off_device_contact_number' => null, 'meter_contact_number' => null]);
            if ($meter)
            {
                PermanentMeterData::where('meter_number', '=', $meter->meter_number)->update(['in_use' => 0]);
            }
        }

        return redirect('close_account')->with('successMsg', 'The customer was deleted successfully');*/
    }

    public function multiple_close()
    {
        $customers = Customer::where('status', '=', 1)->where('scheme_number', '=', Auth::user()->scheme_number)->get();
        $this->layout->page = view('home/multiple_close_view', ['customers' => $customers]);
    }

    public function multiple_close_account_action()
    {
        $checkboxes = $_POST['checkbox'];
        $rows = $_POST['row'];

        foreach ($checkboxes as $key => $value) {

            /*Customer::where('Id', '=', $rows[$key])->update(array('status' => 2, 'username' => $username.'_d'));
            $customer = Customer::where('Id', '=', $rows[$key])->get()->first();
            $meter = DistrictHeatingMeter::where('meter_ID', '=', $customer['meter_ID'])->get()->first();
            //delete the district heating meter
            DistrictHeatingMeter::where('meter_ID', '=', $customerInfo->meter_ID)->delete();
            if ($meter)
            {
                PermanentMeterData::where('meter_number', '=', $meter->meter_number)->update(['in_use' => 0]);
            }*/
            $customerInfo = Customer::where('id', '=', $rows[$key])->get()->first();
            if ($customerInfo) {
                Customer::where('id', '=', $rows[$key])->update(['status' => 2, 'username' => $customerInfo->username.'_d']);
                $meter = DistrictHeatingMeter::where('meter_ID', '=', $customerInfo->meter_ID)->get()->first();
                //delete the district heating meter
                DistrictHeatingMeter::where('meter_ID', '=', $customerInfo->meter_ID)->delete();
                //DistrictHeatingMeter::where('meter_ID', '=', $customerInfo->meter_ID)->update(['meter_number' => null, 'shut_off_device_contact_number' => null, 'meter_contact_number' => null]);
                if ($meter) {
                    PermanentMeterData::where('meter_number', '=', $meter->meter_number)->update(['in_use' => 0]);
                }
            }
        }

        return redirect('settings/multiple_close')->with('successMsg', 'The customer was deleted successfully');
    }

    public function multiple_close_account_search()
    {
        $search_term = Input::get('search_box');

        $customers = $this->search_customers($search_term);

        $this->layout->page = view('home/multiple_close_view', ['customers' => $customers]);
    }

    /* Close Account Procedure */

    public function closeAccountStep1($customerID)
    {
        if (Request::isMethod('post')) {
            //inserting the last meter reading
            $unitID = Input::get('unit_id');
            $latestMeterReading = PermanentMeterDataMeterReadWebsite::where('permanent_meter_id', '=', $unitID)->where('complete', 1)->orderBy('time_date', 'desc')->first();
            $reading = $latestMeterReading ? $latestMeterReading->reading : null;
            $customer = Customer::findOrFail($customerID);
            $meterID = $customer->meter_ID;
            //$permanentMeter = PermanentMeterData::where('ID', $unitID)->first();

            if (is_null($reading)) {
                return \Illuminate\Support\Facades\Response::json(['error' => 'No successful reading detected'], 200);
            }

            if (! PermanentMeterDataReadingsAll::insert([
                    'time_date' 		 => date('Y-m-d H:i:s'),
                    'scheme_number' 	 => Auth::user()->scheme_number,
                    'permanent_meter_id' => $unitID,
                    'reading1' 			 => $reading,
                ])) {
                return \Illuminate\Support\Facades\Response::json(['error' => 'Cannot insert entry in the meters table'], 200);
            }

            if (! DistrictHeatingMeter::where('meter_ID', $meterID)->update([
                'sudo_reading' 			=> $reading,
                'sudo_reading_time' 	=> date('Y-m-d H:i:s'),
            ])) {
                return \Illuminate\Support\Facades\Response::json(['error' => 'Cannot update the district heating meter table'], 200);
            }

            if (! $dhu = DistrictHeatingUsage::create([
                'customer_id'		=> $customerID,
                'scheme_number' 	=> Auth::user()->scheme_number,
                'date' 				=> date('Y-m-d'),
                'end_day_reading' 	=> $reading,
            ])) {
                return \Illuminate\Support\Facades\Response::json(['error' => 'Cannot insert entry in the district heating usage table'], 200);
            }

            $totalUsage = $dhu->end_day_reading - $dhu->start_day_reading;

            if (! $dhu->update(['total_usage' => $totalUsage])) {
                return \Illuminate\Support\Facades\Response::json(['error' => 'Cannot update the total usage'], 200);
            }

            return \Illuminate\Support\Facades\Response::json(['success' => 1], 200);
        }

        //delete customer
        $customer = Customer::find($customerID);
        if (! $customer) {
            return redirect('close_account')->with('errorMessage', 'Customer Not Found');
        }

        $data = [];
        $data['meter_id'] = $customer->districtHeatingMeter ? $customer->districtHeatingMeter->permanentMeterData->ID : null;
        $data['customer_id'] = $customerID;

        if (is_null($data['meter_id'])) {
            return redirect('close_account')->with('errorMessage', 'The customer doesn\'t have a permanent meter assigned');
        }

        $this->layout->page = view('home/close_account_step1')->with('data', $data);
    }

    public function closeAccountStep2($customerID)
    {
        $customer = Customer::findOrFail($customerID);
        $dhm = $customer->districtHeatingMeter;
        if (! $dhm) {
            return redirect('close_account')->with('errorMessage', 'Customer doesn\'t have a district heating meter');
        }

        $totalUsage = $dhm->latest_reading - $dhm->sudo_reading;
        $landlords = getLandlords(Auth::user()->scheme_number);

        $data = [];
        $data['dhm'] = $dhm;
        $data['total_usage'] = $totalUsage;
        $data['customer'] = $customer;
        $data['landlords'] = $landlords;

        $this->layout->page = view('home/close_account_step2')->with('data', $data);
    }

    public function closeAccountStep3($customerID)
    {
        if (Request::isMethod('post')) {
            $customer = Customer::findOrFail($customerID);
            $dhm = $customer->districtHeatingMeter;
            if (! $dhm) {
                return redirect('close_account')->with('errorMessage', 'Customer doesn\'t have a district heating meter');
            }

            $tariff = Tariff::where('scheme_number', '=', Auth::user()->scheme_number)->first();
            if (! $tariff) {
                return redirect('close_account/'.$customerID.'/step3')->with('errorMessage', 'No tariff assigned for the current scheme');
            }
            $tariffType = \Input::get('tariff_type');

            $charge = 0;
            if ($dhm->latest_reading <= 0 || $dhm->sudo_reading <= 0) {
                $charge = 0;
            } else {
                $charge = ($dhm->latest_reading - $dhm->sudo_reading) * $tariff->$tariffType;
            }

            if ($charge) {
                if ($charge > 0) {
                    if (! Customer::where('id', $customerID)->update([
                        'balance' => $customer->balance - $charge,
                        'total_unit_charge' => $customer->total_unit_charge + $charge,
                    ])) {
                        return redirect('close_account/'.$customerID.'/step3')->with('errorMessage', 'Cannot update customer\'s balance');
                    }

                    if (! DistrictHeatingMeter::where('meter_ID', $dhm->meter_ID)->update([
                        'latest_reading' => $dhm->sudo_reading,
                    ])) {
                        return redirect('close_account/'.$customerID.'/step3')->with('errorMessage', 'Could not update the latest meter reading');
                    }

                    DistrictHeatingUsage::where('customer_id', $customerID)->where('date', date('Y-m-d'))->orderBy('id', 'desc')->first()->update([
                        'cost_of_day' => $charge,
                    ]);
                }
            }

            return redirect('close_account/'.$customerID.'/step4');
        }

        $tariffs = Tariff::where('scheme_number', '=', Auth::user()->scheme_number)->first();

        $data = [];
        $data['tariffs'] = $tariffs;
        $data['customer_id'] = $customerID;

        $this->layout->page = view('home/close_account_step3')->with('data', $data);
    }

    public function closeAccountStep4($customerID)
    {
        $customer = Customer::findOrFail($customerID);
        $landlords = getLandlords(Auth::user()->scheme_number);

        echo $customer;

        $data = [];
        $data['customer'] = $customer;
        $data['landlords'] = $landlords;

        $this->layout->page = view('home/close_account_step4')->with('data', $data);
    }

    /* Close Account Procedure */

    public function convert_number($mobile_number)
    {
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

        return $new_number;
    }

    public static function opensms($customerID)
    {
        try {
            //get customer info
            $customer = Customer::where('id', '=', $customerID)->first();

            if (! $customer) {
                return false;
            }

            $msg = 'Hi '.$customer['first_name']."\n";
            $msg .= "You are now registered with SnugZone.\n";
            $msg .= "To download the app and login please visit www.snugzone.biz.\n\n";
            $msg .= "Login Credentials:\n";
            $msg .= 'Email: '.$customer['email_address']."\n";
            $msg .= "Username\Account Number: ".$customer['username']."\n";
            $msg .= "Password: The password that you enter on your first login attempt will become your password.\n";
            $msg .= 'Starting balance: '.$customer['starting_balance']."\n";
            $msg .= "\nSnugZone.";

            $customer->sms($msg, 0);

            $this->log->addInfo('Successfully sent opensms() SMS to '.$customerID);

            return true;
        } catch (Exception $e) {
            $this->log->addInfo('Failed to send opensms() SMS to '.$customerID.': '.$e->getMessage().' ('.$e->getLine().')');

            return false;
        }
    }

    private function sendSMSAfterCustomerSetUp($customerID)
    {
        try {
            //get customer info
            $customer = Customer::where('id', '=', $customerID)->first();

            if (! $customer) {
                return false;
            }

            $msg = '';
            $msg .= "Dear customer,\n\n";
            $msg .= "Here are your SnugZone login credentials:\n\n";
            $msg .= "Username/Account Number:\n".$customer['username']."\n\n";
            $msg .= "Email:\n".$customer['email_address']."\n\n";
            $msg .= "Password:\n- The password that you enter on your first login attempt will become your password. -\n\n";
            $msg .= 'Current balance: '.$customer['starting_balance']."\n\n";
            $msg .= "Please download our app from your app store.\n\n";
            $msg .= 'SnugZone';

            // $msg = "Hi " . $customer['first_name'] . "\n";
            // $msg .= "You are now registered with SnugZone.\n";
            // $msg .= "To download the app and login please visit www.snugzone.biz.\n\n";
            // $msg .= "Login Credentials:\n";
            // $msg .= "Email: " . $customer['email_address'] . "\n";
            // $msg .= "Username\Account Number: " . $customer['username'] . "\n";
            // $msg .= "Password: The password that you enter on your first login attempt will become your password.\n";
            // $msg .= "Starting balance: " . $customer['starting_balance'] . "\n";
            // $msg .= "\nSnugZone.";

            $customer->sms($msg, 0);

            $this->log->addInfo('Successfully sent sendSMSAfterCustomerSetUp() SMS to '.$customerID);

            return true;
        } catch (Exception $e) {
            $this->log->addInfo('Failed to send sendSMSAfterCustomerSetUp() SMS to '.$customerID.': '.$e->getMessage().' ('.$e->getLine().')');

            return false;
        }
    }

    private function search_customers($search_term)
    {
        $customers = Customer::where('status', '=', 1)
                        ->where('scheme_number', '=', Auth::user()->scheme_number)
                        ->where(function ($query) use ($search_term) {
                            $query->where('first_name', 'like', '%'.$search_term.'%')
                            ->orWhere('barcode', 'like', '%'.$search_term.'%')
                            ->orWhere('surname', 'like', '%'.$search_term.'%')
                            ->orWhere('street1', 'like', '%'.$search_term.'%')
                            ->orWhere('street2', 'like', '%'.$search_term.'%')
                            ->orWhere('email_address', 'like', '%'.$search_term.'%')
                            ->orWhere('mobile_number', 'like', '%'.$search_term.'%')
                            ->orWhere('town', 'like', '%'.$search_term.'%')
                            ->orWhere('county', 'like', '%'.$search_term.'%')
                            ->orWhere('nominated_telephone', 'like', '%'.$search_term.'%');
                        })
                        ->get();

        return $customers;
    }

    public function advanced_search()
    {
        $results = [];

        if (Session::has('results')) {
            $results = Session::get('results');
        }

        if (Session::has('searched_by')) {
            $searched_by = Session::get('searched_by');
        } else {
            $searched_by = '';
        }

        $this->layout->page = view('home/advanced_search')
        ->with(
        [

            'results' => $results,
            'searched_by' => $searched_by,

        ]);
    }

    private function clean($s)
    {
        if (substr($s, -1) == ',') {
            $s = substr($s, 0, strlen($s) - 1);
        }

        return str_replace(' ', '', $s);
    }

    public function advanced_search_submit()
    {
        $meter_ID = $this->clean(Input::get('meter_ID'));
        $pmd_ID = $this->clean(Input::get('pmd_ID'));
        $username = $this->clean(Input::get('username'));
        $barcode = $this->clean(Input::get('barcode'));
        $mobile_number = $this->clean(Input::get('mobile_number'));
        $email = $this->clean(Input::get('email'));
        $first_name = $this->clean(Input::get('first_name'));
        $surname = $this->clean(Input::get('surname'));
        $custom = Input::get('custom');

        $searched_by = '';

        if (! empty($meter_ID)) {
            if (strpos($meter_ID, ',') !== false) {
                $arr = explode(',', str_replace(' ', '', $meter_ID));
                $results = Customer::whereIn('meter_ID', $arr)->get();
            } else {
                $results = Customer::where('meter_ID', $meter_ID)->get();
            }

            $searched_by = "customer.meter_ID = ($meter_ID)";
        } elseif (! empty($pmd_ID)) {
            if (strpos($pmd_ID, ',') !== false) {
                $arr = explode(',', str_replace(' ', '', $pmd_ID));
                $meter = DistrictHeatingMeter::whereIn('permanent_meter_ID', $arr)->get();
            } else {
                $meter = DistrictHeatingMeter::where('permanent_meter_ID', $pmd_ID)->first();
            }

            if ($meter) {
                if (strpos($pmd_ID, ',') !== false) {
                    $results = Customer::whereIn('meter_ID', $meter->lists('meter_ID'))->get();
                } else {
                    $results = Customer::where('meter_ID', $meter->meter_ID)->get();
                }
            } else {
                $results = [];
            }

            $searched_by = "dhm.permanent_meter_ID = ($pmd_ID)";
        } elseif (! empty($username)) {
            if (strpos($username, ',') !== false) {
                $arr = explode(',', str_replace(' ', '', $username));

                $results = Customer::whereIn('username', $arr)->get();
            } else {
                $results = Customer::where('username', $username)->get();
            }

            $searched_by = "customer.username = ($username)";
        } elseif (! empty($barcode)) {
            if (strpos($barcode, ',') !== false) {
                $arr = explode(',', str_replace(' ', '', $barcode));

                $results = Customer::whereIn('barcode', $arr)->get();
            } else {
                $results = Customer::where('barcode', $barcode)->get();
            }

            $searched_by = "customer.barcode = ($barcode)";
        } elseif (! empty($mobile_number)) {
            $searched_by = "customer.mobile_number LIKE ($mobile_number)";

            if (substr($mobile_number, 0, 1) == 0) {
                $mobile_number = ltrim($mobile_number, '0');
            }

            if (strpos($mobile_number, ',') !== false) {
                $arr = explode(',', str_replace(' ', '', $mobile_number));

                $customers = Customer::where('status', 1)->get();
                $results = Customer::where('id', 0)->get();

                foreach ($customers as $key => $c) {
                    for ($i = 0; $i < count($arr); $i++) {
                        if (substr($arr[$i], 0, 1) == 0) {
                            $arr[$i] = ltrim($arr[$i], '0');
                        }

                        if (strpos($c->mobile_number, $arr[$i]) !== false || strpos($c->nominated_telephone, $arr[$i]) !== false) {
                            $results->add($c);
                        }
                    }
                }

                //whereIn('mobile_number', $arr)->get();
            } else {
                $customers = Customer::where('status', 1)->get();
                $results = Customer::where('id', 0)->get();

                foreach ($customers as $key => $c) {
                    if (strpos($c->mobile_number, $mobile_number) !== false || strpos($c->nominated_telephone, $mobile_number) !== false) {
                        $results->add($c);
                    }
                }
            }
        } elseif (! empty($email)) {

            //ini_set('memory_limit', -1);

            $customers = Customer::where('status', 1)->get();
            $results = Customer::where('id', 0)->get();
            $email = strtolower($email);

            foreach ($customers as $key => $c) {
                if (strpos(strtolower($c->email_address), $email) !== false) {
                    $results->add($c);
                }
            }

            $searched_by = "customer.email_address LIKE ($email)";
        } elseif (! empty($first_name)) {

            //ini_set('memory_limit', -1);

            $customers = Customer::where('status', 1)->get();
            $results = Customer::where('id', 0)->get();
            $first_name = strtolower($first_name);

            foreach ($customers as $key => $c) {
                if (strpos(strtolower($c->first_name), $first_name) !== false) {
                    $results->add($c);
                }
            }

            $searched_by = "customer.first_name LIKE ($first_name)";
        } elseif (! empty($surname)) {
            $customers = Customer::where('status', 1)->get();
            $results = Customer::where('id', 0)->get();
            $surname = strtolower($surname);

            foreach ($customers as $key => $c) {
                if (strpos(strtolower($c->surname), $surname) !== false) {
                    $results->add($c);
                }
            }

            $searched_by = "customer.surname LIKE ($surname)";
        } elseif (! empty($custom)) {
            $custom_parts = explode(' ', $custom);

            $column = $custom_parts[0];
            $wildcard = $custom_parts[1];
            $value = $custom_parts[2];

            $customers = Customer::where('status', 1)->get();
            $results = Customer::where('id', 0)->get();

            if ($wildcard == '=') {
                foreach ($customers as $key => $c) {
                    if (strtolower($c->$column) == strtolower($value)) {
                        $results->add($c);
                    }
                }
                $searched_by = "customer.$column = ($value)";
            }

            if (strtolower($wildcard) == 'like') {
                foreach ($customers as $key => $c) {
                    if (strpos(strtolower($c->$column), strtolower($value)) !== false) {
                        $results->add($c);
                    }
                }
                $searched_by = "customer.$column LIKE ($value)";
            }
        } else {
            $results = [];
        }

        return redirect('advanced_search')->with([
            'results' => $results,
            'searched_by' => $searched_by,
        ]);
    }

    public function missing_customers()
    {
        $schemes = Scheme::where('archived', 0)->orderBy('id', 'DESC')->get();
        foreach ($schemes as $key => $s) {
            $all_customers = Customer::where('scheme_number', $s->id)->get();

            if ($all_customers->count() == 0) {
                $schemes->forget($key);
                continue;
            }

            $shut_customers = Customer::getShutOffCustomers($s->id);
            $pending_customers = Customer::getPendingCustomers($s->id);
            $normal_customers = Customer::getNormalCustomers($s->id);

            $customers = Customer::where('id', 0)->get();

            foreach ($shut_customers as $sc) {
                $sc->color = '#facccb';
                $customers->add($sc);
            }

            foreach ($pending_customers as $sc) {
                $sc->color = '#faf4cb';
                $customers->add($sc);
            }

            foreach ($normal_customers as $sc) {
                $sc->color = '#cbfadb';
                $customers->add($sc);
            }

            $missing_customers = Customer::where('scheme_number', $s->id)->whereNotIn('id', $customers->lists('id'))->get();

            $s->customers = $missing_customers;
            $s->viewable_count = $customers->count();
            $s->actual_count = $all_customers->count();
            $s->red_count = $shut_customers->count();
            $s->yellow_count = $pending_customers->count();
            $s->green_count = $normal_customers->count();
        }

        $this->layout->page = view('home/missing_customers', [
            'schemes' => $schemes,
        ]);
    }

    public function away_modes()
    {
        $away_modes = RemoteControlStatus::where('away_mode_on', 1)->orderBy('away_mode_end_datetime', 'ASC')->get();
        foreach ($away_modes as $key => $a) {
            $pmd = PermanentMeterData::where('ID', $a->permanent_meter_id)->first();

            if (! $pmd) {
                $away_modes->forget($key);
                continue;
            }

            $customer = Customer::where('username', $pmd->username)->first();

            if (! $customer) {
                //$away_modes->forget($key);
                //continue;
            }

            $a->customer = $customer;
            $a->pmd = $pmd;
        }

        $this->layout->page = view('home/away_modes', [
            'away_modes' => $away_modes,
        ]);
    }

    public function shut_offs()
    {
        $date = (Input::get('date')) ? new DateTime(Input::get('date')) : new DateTime(date('Y-m-d'));

        // Use the old mechanism to retrieve billing engine log files if date is before April 5th
        if (date('Y-m-d') <= '2019-04-05') {
            $shut_off_file = '/opt/prepago_engine/prepago_engine/logs/'.$date->format('Y').'/'.$date->format('m').'/shut_off_engine/'.$date->format('Y').'_'.$date->format('m').'_'.$date->format('d').'.txt';

            if ($date->format('Y-m-d') == date('Y-m-d') && ! file_exists($shut_off_file)) {
                while (! file_exists($shut_off_file)) {
                    $date = new DateTime($date->format('Y-m-d').' -1 day');
                    $shut_off_file = '/opt/prepago_engine/prepago_engine/logs/'.$date->format('Y').'/'.$date->format('m').'/shut_off_engine/'.$date->format('Y').'_'.$date->format('m').'_'.$date->format('d').'.txt';
                }
            }

            if (! file_exists($shut_off_file)) {
                $shut_offs = [];
            } else {
                $lines = file($shut_off_file);
                $shut_offs = [];
                $current_time = '';

                foreach ($lines as $l) {
                    if (strpos($l, 'Started the shut off engine on ') !== false) {
                        $parts = explode(' ', explode('Started the shut off engine on ', $l)[1]);
                        $day = str_replace('.', '-', $parts[0]);
                        $time = str_replace('.', '', $parts[1]);
                        $datetime = new DateTime($day);
                        $current_time = $datetime->format('Y-m-d').' '.$time;
                    }

                    if (strpos($l, 'has been shut down') === false) {
                        continue;
                    }

                    $meter_id = explode(' ', $l)[1];

                    $dhm = DistrictHeatingMeter::where('meter_ID', $meter_id)->first();

                    $customer = Customer::where('meter_ID', $dhm->meter_ID)->first();

                    if (! $customer) {
                        continue;
                    }

                    $dhm->customer = $customer;

                    if (! isset($shut_offs[$dhm->meter_ID])) {
                        $dhm->times = 1;
                        $shut_offs[$dhm->meter_ID] = $dhm;
                        $shut_offs[$dhm->meter_ID]->last = $current_time;
                    } else {
                        $shut_offs[$dhm->meter_ID]->last = $current_time;
                        $shut_offs[$dhm->meter_ID]->times++;
                    }

                    if ($customer) {
                        if ($customer->balance > 0.00) {
                            $shut_offs[$dhm->meter_ID]->style = 'background-color: #c9eec9;';
                        } else {
                            $shut_offs[$dhm->meter_ID]->style = 'background-color: #ffd0ce;';
                        }
                    } else {
                        $shut_offs[$dhm->meter_ID]->style = 'background-color: #ffd0ce;';
                    }
                }
            }
        } else {
            $shut_offs = [];

            $dhm = DistrictHeatingMeter::whereRaw("last_shut_off_time LIKE '%".$date->format('Y-m-d')."%'")->get();

            foreach ($dhm as $d) {
                $customer = Customer::where('meter_ID', $d->meter_ID)->first();
                if (! $customer) {
                    continue;
                }

                $d->customer = $customer;

                $scheme = Scheme::find($d->scheme_number);
                $d->scheme = $scheme;

                if ($customer && $scheme) {
                    if ($customer->balance < -$scheme->IOU_amount) {
                        $customer->exceeded = true;
                    }

                    $iou_storage = IOUStorage::where('customer_id', $customer->id)->orderBy('id', 'DESC')->get();
                    $d->info = $iou_storage;
                }

                if (! isset($shut_offs[$d->meter_ID])) {
                    $d->times = 1;
                    $shut_offs[$d->meter_ID] = $d;
                    $shut_offs[$d->meter_ID]->last = $d->last_shut_off_time;
                    $shut_offs[$d->meter_ID]->times = 1;
                }

                if ($customer) {
                    if ($customer->balance > 0.00) {
                        $shut_offs[$d->meter_ID]->style = 'background-color: #c9eec9;';
                    } else {
                        $shut_offs[$d->meter_ID]->style = 'background-color: #ffd0ce;';
                    }
                } else {
                    $shut_offs[$d->meter_ID]->style = 'background-color: #ffd0ce;';
                }
            }
        }

        $this->layout->page = view('home/shut_offs', [
            'date' => $date,
            'shut_offs' => $shut_offs,
        ]);
    }

    public function ious()
    {
        $ious = Customer::IOU()->get(['customers.*']);

        if (Input::get('date')) {
            $date = Input::get('date');
        } else {
            $date = date('Y-m-d');
        }

        foreach ($ious as $key => $iou) {
            $scheme = Scheme::find($iou->scheme_number);

            if ($scheme->simulator) {
                $ious->forget($key);
            }

            if ($scheme) {
                if ($iou->balance < -$scheme->IOU_amount) {
                    $iou->exceeded = true;
                }
            }

            $iou_storage = IOUStorage::where('customer_id', $iou->id)->orderBy('id', 'DESC')->get();
            $iou->info = $iou_storage;
        }

        $this->layout->page = view('home/ious', [
            'ious' => $ious,
            'date' => new DateTime($date),
        ]);
    }

    public function deleted_accounts()
    {
        $deletedCustomers = DB::table('customers')->whereRaw('(deleted_at IS NOT NULL)')
        ->orderBy('deleted_at', 'DESC')->get();

        foreach ($deletedCustomers as $k => $c) {
            $c->replaced = false;
            $c->replacement = null;

            $replaced = Customer::where('username', $c->username)->whereRaw('(deleted_at IS NULL)')
            ->first();
            if ($replaced) {
                $c->replaced = true;
                $c->replacement = $replaced;
            }
        }

        $this->layout->page = view('home/customers_deleted')
        ->with([
            'deletedCustomers' => $deletedCustomers,
        ]);
    }

    public function reinstate_account($customer_id)
    {
        $customer = DB::table('customers')->where('id', $customer_id)->first();
        $customer->replaced = false;
        $customer->replacement = null;
        $replaced = Customer::where('username', $customer->username)->whereRaw('(deleted_at IS NULL)')
            ->first();
        if ($replaced) {
            $customer->replaced = true;
            $customer->replacement = $replaced;
        }

        $this->layout->page = view('home/customers_reinstate')
        ->with([
            'customer' => $customer,
        ]);
    }

    public function reinstate_account_submit($customer_id)
    {
        try {
            $customer = DB::table('customers')->where('id', $customer_id)->first();
            $customer->replaced = false;
            $customer->replacement = null;
            $replaced = Customer::where('username', $customer->username)->whereRaw('(deleted_at IS NULL)')
                ->first();

            if ($replaced) {
                $customer->replaced = true;
                $customer->replacement = $replaced;

                return redirect('reinstate_account')
                ->with([
                    'customer' => $customer,
                    'warningMessage' => "Didn't reinstate <a href='/customer/$customer_id'>Customer #$customer_id</a>. This customer was already replaced..",
                ]);
            } else {
                $customer = Customer::withTrashed()->where('id', $customer->id)->first();

                if (! $customer->restore()) {
                    throw new \Exception('Customer cannot be reinstated');
                }

                //set permanent_meter_data.in_use to 0
                $pmd = PermanentMeterData::where('username', $customer->username)
                ->where('in_use', 0)->first();
                if (! $pmd) {
                    throw new \Exception('Permanent meter data not found');
                }
                if (! $pmd->update(['in_use' => 1])) {
                    throw new \Exception('Permanent meter data cannot be updated');
                }

                $dhm = DistrictHeatingMeter::where('meter_ID', $customer->meter_ID)->first();
                if (! $dhm) {
                    throw new \Exception('District heating meter not found');
                }
                $permanentMeterID = $pmd->ID;
                $meter_number = $pmd->meter_number;

                if (! $dhm->update(['meter_number' => $meter_number, 'permanent_meter_ID' => $permanentMeterID])) {
                    throw new \Exception('District heating meter data cannot be updated');
                }

                $customerReinstation = new CustomerReinstation();
                $customerReinstation->customer_id = $customer->id;
                $customerReinstation->operator_id = Auth::user()->id;
                $customerReinstation->reason = 'n/a';
                $customerReinstation->save();

                return redirect('reinstate_account')
                ->with([
                    'customer' => $customer,
                    'successMessage' => "Successfully reinstated <a href='/customer/$customer_id'>Customer #$customer_id</a>",
                ]);
            }
        } catch (Exception $e) {
            return redirect('reinstate_account')
            ->with([
                'customer' => $customer,
                'errorMessage' => "Failed to reinstate <a href='/customer/$customer_id'>Customer #$customer_id</a>: ".$e->getMessage().' ('.$e->getLine().')',
            ]);
        }
    }
}
