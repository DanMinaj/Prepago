<?php

namespace App\Http\Controllers;

use App\Models\DataLogger;
use App\Models\Scheme;
use App\Models\Simcard;
use App\Models\Tariff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;

class SchemeSetUpController extends Controller
{
    protected $layout = 'layouts.admin_website';
    private $validator;

    public function __construct(BaseValidator $validator)
    {
        $this->validator = $validator;
    }

    public function index()
    {
        $newSchemeId = Scheme::orderBy('id', 'DESC')->first();
        if ($newSchemeId) {
            $newSchemeId = $newSchemeId->id + 1;
        } else {
            $newSchemeId = 1;
        }

        $this->layout->page = view('home/scheme_setup/index', [

            'newSchemeId' => $newSchemeId,

          ]);
    }

    /**
        Create the scheme

     **/
    public function step_1_submit()
    {
        try {
            $step_1_vars = Input::get('step_1_vars');

            $scheme = new Scheme();

            foreach ($step_1_vars as $key => $value) {
                if (strlen($value) <= 0) {
                    if ($key != 'street2') {
                        throw new Exception("$key is empty. Please fill in <b>all</b> the fields");
                    }
                }

                $scheme->$key = $value;
            }

            $this->prepareSchemeObject($scheme);

            $scheme->save();

            $res = "<b>Please note:</b> You are creating an account for the scheme '".$scheme->company_name."'";

            return $res;
        } catch (Exception $e) {
            return '<b>Error in step_1_submit():</b> '.$e->getMessage();
        }
    }

    /**
        Create the accounts

     **/
    public function step_2_submit()
    {
        try {
            $step_2_vars = Input::get('step_2_vars');

            $scheme = Scheme::orderBy('id', 'DESC')->first();

            if ($scheme) {
                $installer_username = $step_2_vars['installer_username'];
                $installer_password = $step_2_vars['installer_password'];
                $installer_group_id = $step_2_vars['installer_group'];
                $installer_isInstaller = $step_2_vars['installer_isInstaller'];

                if (strlen($installer_password) <= 0) {
                    throw new Exception('Installer password is empty.');
                }
                if (strlen($installer_username) <= 0) {
                    throw new Exception('Installer username is empty.');
                }
                // Create installer account
                $installer = new User();
                $installer->employee_name = $scheme->company_name.' Installer';
                $installer->username = $installer_username;
                $installer->password = Hash::make($installer_password);
                $installer->account_type = 1;
                $installer->group_id = $installer_group_id;
                $installer->paid = $installer_group_id == 5 ? 1 : 0;
                $installer->charge = 0;
                $installer->isInstaller = $installer_isInstaller;
                if ($installer->save()) {
                    $scheme->users()->attach($installer->id);
                    $scheme->users()->attach(Auth::user()->id);
                } else {
                    throw new Exception('Error: Failed to create new Operator for '.$scheme->company_name);
                }

                $op_employee_name = $step_2_vars['op_employee_name'];
                $op_username = $step_2_vars['op_username'];

                if (! empty($op_username)) {
                    $op_password = $step_2_vars['op_password'];
                    $op_email_address = $step_2_vars['op_email_address'];
                    $op_group = $step_2_vars['op_group'];
                    $op_isInstaller = $step_2_vars['op_isInstaller'];

                    if (strlen($op_username) <= 0) {
                        throw new Exception('Operator username is empty.');
                    }
                    if (strlen($op_password) <= 0) {
                        throw new Exception('Operator password is empty.');
                    }
                    // Create operator account
                    $operator = new User();
                    $operator->employee_name = $op_employee_name;
                    $operator->username = $op_username;
                    $operator->password = Hash::make($op_password);
                    $operator->email_address = $op_email_address;
                    $operator->account_type = 1;
                    $operator->group_id = $op_group;
                    $operator->paid = $op_group == 5 ? 1 : 0;
                    $operator->charge = 0;
                    $operator->isInstaller = 0;
                    if ($operator->save()) {
                        $scheme->users()->attach($operator->id);
                    } else {
                        throw new Exception('Error: Failed to create new Operator for '.$scheme->company_name);
                    }
                }

                $scheme = Scheme::orderBy('id', 'DESC')->first();

                $res = "<center><b>Please note:</b> You are creating a datalogger for the scheme '".$scheme->company_name."'</center>";

                return $res;
            } else {
                throw new Exception("Scheme doesn't exist");
            }
        } catch (Exception $e) {
            return '<b>Error in step_2_submit():</b> '.$e->getMessage();
        }
    }

    /**
        Create the datalogger

     **/
    public function step_3_submit()
    {
        try {
            $step_3_vars = Input::get('step_3_vars');

            $scheme = Scheme::orderBy('id', 'DESC')->first();

            $sim_exists = Simcard::where('IP_Address', $step_3_vars['IP_Address'])->first();

            if (! $sim_exists) {
                $new_sim = new Simcard();
                $new_sim->ICCID = $step_3_vars['ICCID'];
                $new_sim->MSISDN = $step_3_vars['MSISDN'];
                $new_sim->IP_Address = $step_3_vars['IP_Address'];
                $new_sim->Name = $scheme->company_name.' Official';
                $new_sim->software_version = '1.13';
                $new_sim->in_use = '0';
                $new_sim->in_use_datetime = date('Y-m-d H:i:s');

                if (! $new_sim->save()) {
                    throw new Exception('Error: Failed to create new SIM for '.$scheme->company_name);
                }
            } else {
            }

            // Creating a new scheme controller sim is OPTIONAL
            if (! empty($step_3_vars['c_IP_Address'])) {
                if (empty($step_3_vars['c_ICCID'])) {
                    throw new Exception('Cannot leave ICCID empty. It must also be unique!');
                }
                if (empty($step_3_vars['c_MSISDN'])) {
                    throw new Exception('Cannot leave MSISDN empty. It must also be unique!');
                }
                if (empty($step_3_vars['c_IP_Address'])) {
                    throw new Exception('Cannot leave IP Address empty. It must also be unique!');
                }
                $c_sim_exists = Simcard::where('IP_Address', $step_3_vars['c_IP_Address'])->first();

                if (! $c_sim_exists) {
                    $c_new_sim = new Simcard();
                    $c_new_sim->ICCID = $step_3_vars['c_ICCID'];
                    $c_new_sim->MSISDN = $step_3_vars['c_MSISDN'];
                    $c_new_sim->IP_Address = $step_3_vars['c_IP_Address'];
                    $c_new_sim->Name = $scheme->company_name.' Remote Controller';
                    $c_new_sim->software_version = '1.13';
                    $c_new_sim->in_use = '0';
                    $c_new_sim->in_use_datetime = date('Y-m-d H:i:s');

                    if (! $c_new_sim->save()) {
                        throw new Exception('Error: Failed to create new Remote Controller SIM for '.$scheme->company_name);
                    }
                }
            }

            $new_sim = Simcard::where('IP_Address', $step_3_vars['IP_Address'])->first();

            if ($new_sim) {
                $dlexists = DataLogger::where('scheme_number', $scheme->id)->first();

                if (! $dlexists) {
                    $dl = new DataLogger();
                    $dl->scheme_number = $scheme->id;
                    $dl->sim_id = $new_sim->ID;
                    $dl->password = '';
                    $dl->relay_data_logger = 0;
                    $dl->name = $scheme->company_name;
                    $dl->datalogger_active = 0;
                    $dl->scu_last8 = $step_3_vars['scu_last8'];
                    $dl->meter_last8 = $step_3_vars['meter_last8'];

                    if (! $dl->save()) {
                        throw new Exception('Error: Failed to create new DataLogger for '.$scheme->company_name);
                    }
                }
            } else {
                throw new Exception('Error: Failed to create new DataLogger for '.$scheme->company_name);
            }

            return 'success';
        } catch (Exception $e) {
            return '<b>Error in step_3_submit():</b> '.$e->getMessage();
        }
    }

    public function step_complete()
    {
        try {
            $newScheme = Scheme::orderBy('id', 'DESC')->first();
            $newSchemeDL = DataLogger::orderBy('id', 'DESC')->first();
            $newSchemeSim = Simcard::where('Name', $newScheme->company_name.' Official')->first();

            $res = '';
            $res .= "<div class='alert alert-success alert-block'><i class='fa fa-check'></i> <b>Scheme successfully setup!</b></div>";
            $res .= "<font style='font-size:15px'>";
            $res .= '<b>Scheme ID:</b> '.$newScheme->id.'<br/><br/>';
            $res .= '<b>Scheme name:</b> '.$newScheme->company_name.'<br/><br/>';
            $res .= '<b>DataLogger ID:</b> '.$newSchemeDL->id.'<br/><br/>';
            $res .= '<b>Scheme IP:</b> '.$newSchemeSim->IP_Address.'<br/></br>';

            $res .= '</font>';

            return $res;
        } catch (Exception $e) {
            return '<b>Error:</b> '.$e->getMessage();
        }
    }

    public function userSetup()
    {
        if (Request::isMethod('post')) {
            $postedGroupID = (int) Input::get('group');

            $user = new User();
            $user->employee_name = Input::get('employee_name');
            $user->username = Input::get('username');
            $user->password = Hash::make(Input::get('password'));
            $user->account_type = 1;
            $user->group_id = $postedGroupID;
            $user->paid = $postedGroupID == 5 ? 1 : 0;
            $user->charge = 0;
            $user->isInstaller = Input::get('isInstaller');

            if (! $user->save()) {
                return redirect('scheme-setup/user-setup')->with('errorMessage', 'There was an error setting up a new user');
            }

            return redirect('scheme-setup/scheme-setup?user_id='.$user->id);
        }

        $this->layout->page = view('home/scheme_setup/user_setup');
    }

    public function schemeSetup()
    {
        $fieldsVersions = getSchemeSetupCountryVersions();

        $userID = Input::get('user_id');

        if (Request::isMethod('post')) {
            if (! $this->validator->isValid(Scheme::$rules)) {
                $errors = $this->validator->getErrors();

                return redirect('scheme-setup/scheme-setup?user_id='.$userID)->withInput()->withErrors($errors);
            }

            $data = $this->prepareData(Input::all(), $fieldsVersions);

            //insert data in the schemes table
            if (! $scheme = Scheme::create($data)) {
                return redirect('scheme-setup/scheme-setup?user_id='.$userID)->with('errorMessage', 'There was an error adding the new scheme');
            }

            //insert data in the users_schemes table
            $scheme->users()->attach($userID);

            //update the charge information in the utility_company_login_details table
            $user = User::findOrFail($userID);
            if ($user->group_id !== 5) {
                if (! $user->update(['charge' =>  (int) $scheme['prepago_new_admin_charge']])) {
                    return redirect('scheme-setup/scheme-setup?user_id='.$userID)->with('errorMessage', 'There was an error updating the user\'s charge information');
                }
            }

            //insert data in the sim_cards table
            if ($data['scu_type'] == 'a') {
                $simData = [];
                $simData['ICCID'] = Input::get('ICCID');
                $simData['MSISDN'] = Input::get('MSISDN');
                //$simData['IP_Address']        = Request::getClientIp();
                $simData['IP_Address'] = Input::get('IP_Address');
                $simData['Name'] = Input::get('Name');
                $simData['software_version'] = Input::get('software_version');
                $simData['in_use'] = Input::get('in_use');
                $simData['in_use_datetime'] = Carbon::now()->toDateTimeString();

                if (! $simcard = Simcard::create($simData)) {
                    return redirect('scheme-setup/scheme-setup?user_id='.$userID)->with('errorMessage', 'There was an error adding the sim card information');
                }

                // insert data in data_loggers table
                $dataLoggerData = [];
                $dataLoggerData['sim_id'] = $simcard->id;
                $dataLoggerData['scheme_number'] = $scheme->scheme_number;
                $dataLoggerData['name'] = $scheme->company_name;
                if (! DataLogger::insert($dataLoggerData)) {
                    return redirect('scheme-setup/scheme-setup?user_id='.$userID)->with('errorMessage', 'There was an error adding the data logger information');
                }
            }

            return redirect('scheme-setup/tariff-setup?scheme_id='.$scheme->id);
        }

        //get latest scheme info
        $scheme = Scheme::orderBy('start_date', 'desc')->orderBy('scheme_number', 'DESC')->first();

        $this->layout->page = view('home/scheme_setup/scheme_setup')
                                ->with('scheme', $scheme)
                                ->with('fieldsVersions', $fieldsVersions)
                                ->with('user_id', $userID);
    }

    public function simSetup()
    {
        if (Request::isMethod('post')) {
            $sim_ip = Input::get('sim_ip');
            $sim_name = Input::get('sim_name');
            $iccid = Input::get('iccid');
            $msisdn = Input::get('msisdn');

            if (empty($iccid)) {
                $iccid = str_replace('.', '', $sim_ip);
            }

            if (empty($msisdn)) {
                $msisdn = str_replace('.', '', $sim_ip);
            }

            $simcard = new Simcard();
            $simcard->ICCID = $iccid;
            $simcard->MSISDN = $msisdn;
            $simcard->IP_Address = $sim_ip;
            $simcard->Name = $sim_name;
            $simcard->save();

            return redirect('welcome-schemes')
             ->with('successMessage', "Successfully created the Sim ' <b> $sim_name ($sim_ip) </b>'");
        }
    }

    public function tariffSetup()
    {
        $schemeID = Input::get('scheme_id');

        if (Request::isMethod('post')) {
            $scheme = Scheme::findOrFail($schemeID);
            $data = [];

            $data['scheme_number'] = $scheme->scheme_number;
            $data['vat_rate'] = $scheme->vat_rate;
            $data['vat_rate_new'] = $scheme->vat_rate;
            $data['tariff_1'] = Input::get('tariff_1');
            $data['tariff_1_new'] = Input::get('tariff_1');
            $data['tariff_1_name'] = Input::get('tariff_1_name');
            $data['tariff_2'] = Input::get('tariff_2');
            $data['tariff_2_new'] = Input::get('tariff_2');
            $data['tariff_2_name'] = Input::get('tariff_2_name');
            $data['tariff_3'] = Input::get('tariff_3');
            $data['tariff_3_new'] = Input::get('tariff_3');
            $data['tariff_3_name'] = Input::get('tariff_3_name');
            $data['tariff_4'] = Input::get('tariff_4');
            $data['tariff_4_new'] = Input::get('tariff_4');
            $data['tariff_4_name'] = Input::get('tariff_4_name');
            $data['tariff_5'] = Input::get('tariff_5');
            $data['tariff_5_new'] = Input::get('tariff_5');
            $data['tariff_5_name'] = Input::get('tariff_5_name');

            if (! Tariff::create($data)) {
                return redirect('scheme-setup/tariff-setup?scheme_id='.$schemeID)->with('errorMessage', 'There was an error adding the tariff information');
            }

            return redirect('scheme-setup/success');
        }

        $this->layout->page = view('home/scheme_setup/tariff_setup')->with('scheme_id', $schemeID);
    }

    public function success()
    {
        $this->layout->page = view('home/scheme_setup/success');
    }

    public function prepareSchemeObject($scheme_obj)
    {
        $fieldsVersions = getSchemeSetupCountryVersions();
        $predefinedFields = $fieldsVersions[$scheme_obj->country];

        $scheme_obj->FAQ = $predefinedFields['FAQ'];
        $scheme_obj->balance_message = $predefinedFields['balance_message'];
        $scheme_obj->IOU_message = $predefinedFields['IOU_message'];
        $scheme_obj->IOU_extra_message = $predefinedFields['IOU_extra_message'];
        $scheme_obj->rates_message = $predefinedFields['rates_message'];
        $scheme_obj->IOU_denied_message = $fieldsVersions['IOU_denied_message'];
        $scheme_obj->shut_off_message = $fieldsVersions['shut_off_message'];
        $scheme_obj->shut_off_warning_message = $fieldsVersions['shut_off_warning_message'];
        $scheme_obj->credit_warning_message = $predefinedFields['credit_warning_message'];
        $scheme_obj->barcode_message = $fieldsVersions['barcode_message'];
        $scheme_obj->topup_message = $fieldsVersions['topup_message'];
        $scheme_obj->shut_off_periods = $fieldsVersions['shut_off_periods'];
        $scheme_obj->isLive = $fieldsVersions['isLive'];
        $scheme_obj->start_date = Carbon::now()->toDateString();
        $scheme_obj->end_date = Carbon::now()->addYears(100)->toDateString();
        $scheme_obj->sms_disabled = $fieldsVersions['sms_disabled'];
    }

    public function prepareData($input, $fieldsVersions, $forEdit = false)
    {
        $country = $input['country'];
        $predefinedFields = $fieldsVersions[$country];

        $data = [];

        $data['scheme_number'] = $input['scheme_number'];
        $data['scheme_nickname'] = $input['scheme_nickname'];
        $data['scheme_description'] = $input['scheme_description'];
        $data['company_name'] = $input['company_name'];
        $data['company_address'] = $input['company_address'];
        $data['sms_password'] = $input['sms_password'];
        $data['accounts_email'] = $input['accounts_email'];
        $data['vat_rate'] = $input['vat_rate'];
        $data['currency_code'] = $input['currency_code'];
        $data['currency_sign'] = $input['currency_sign'];
        $data['service_type'] = $input['service_type'];
        $data['daily_customer_charge'] = $input['daily_customer_charge'];
        $data['commission_charge'] = $input['commission_charge'];
        $data['prepago_registered_apps_charge'] = $input['prepago_registered_apps_charge'];
        $data['IOU_chargeable'] = $input['IOU_chargeable'];
        $data['IOU_amount'] = $input['IOU_amount'];
        $data['IOU_charge'] = $input['IOU_charge'];
        $data['IOU_text'] = $input['IOU_text'];
        $data['IOU_extra_amount'] = $input['IOU_extra_amount'];
        $data['IOU_extra_charge'] = $input['IOU_extra_charge'];
        $data['IOU_extra_text'] = $input['IOU_extra_text'];
        $data['prepage_SMS_charge'] = $input['prepage_SMS_charge'];
        $data['prepago_new_admin_charge'] = $input['prepago_new_admin_charge'];
        $data['prepago_in_app_message_charge'] = $input['prepago_in_app_message_charge'];
        $data['prefix'] = $input['prefix'];
        $data['street2'] = $input['street2'];
        $data['town'] = $input['town'];
        $data['county'] = $input['county'];
        $data['post_code'] = $input['post_code'];
        $data['country'] = $country;
        $data['unit_abbreviation'] = $input['unit_abbreviation'];

        if (! $forEdit) {
            $data['FAQ'] = $predefinedFields['FAQ'];
            $data['balance_message'] = $predefinedFields['balance_message'];
            $data['IOU_message'] = $predefinedFields['IOU_message'];
            $data['IOU_extra_message'] = $predefinedFields['IOU_extra_message'];
            $data['rates_message'] = $predefinedFields['rates_message'];
            $data['IOU_denied_message'] = $fieldsVersions['IOU_denied_message'];
            $data['shut_off_message'] = $fieldsVersions['shut_off_message'];
            $data['shut_off_warning_message'] = $fieldsVersions['shut_off_warning_message'];
            $data['credit_warning_message'] = $predefinedFields['credit_warning_message'];
            $data['barcode_message'] = $fieldsVersions['barcode_message'];
            $data['topup_message'] = $fieldsVersions['topup_message'];
            $data['shut_off_periods'] = $fieldsVersions['shut_off_periods'];
            $data['isLive'] = $fieldsVersions['isLive'];
            $data['start_date'] = Carbon::now()->toDateString();
            $data['end_date'] = Carbon::now()->addYears(100)->toDateString();
            $data['scu_type'] = $input['scu_type'];
            $data['sms_disabled'] = $fieldsVersions['sms_disabled'];
        }

        return $data;
    }
}
