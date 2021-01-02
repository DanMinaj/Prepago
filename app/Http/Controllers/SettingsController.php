<?php

use \Illuminate\Support\Facades\Redirect;
use \Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class SettingsController extends BaseController {

	protected $layout = 'layouts.admin_website';
	
	public function __construct()
    {
        $this->beforeFilter('canAccessAdminSettings', ['except' => ['barcode_reports', 'add_account_action'] ]);
    }
	
	public function settings()
	{
		
		$settings = SystemSetting::all();
	
		$this->layout->page = View::make('settings/system_settings', ['settings' => $settings]);
		
		
	}
	
	public function settings_add()
	{
		
		$setting = SystemSetting::where('name', Input::get('name'))->first();
		
		if($setting)
			return Redirect::back()->with('errorMessage', "That setting already exists.");
		
		$new_setting = new SystemSetting();
		$new_setting->type = Input::get('type');
		$new_setting->name = Input::get('name');
		$new_setting->value = Input::get('value');
		$new_setting->save();
		
		return Redirect::back()->with('successMessage', "Successfully created new setting '" . $new_setting->name . "'");
		
		
	}
	
	
	public function settings_remove($id)
	{
		
		$setting = SystemSetting::where('id', $id)->first();
		$setting_copy_name = $setting->name;
		
		if($setting)
			$setting->delete();
		
		
		
		return Redirect::back()->with('successMessage', "Successfully removed setting '$setting_copy_name'");
		
		
	}
	
	
	public function settings_save($id)
	{
	
		$setting = SystemSetting::where('id', $id)->first();
		$setting_copy_name = $setting->name;
		
		if(!$setting)
			return Redirect::back()->with('errorMessage', "That setting does not exist.");
		
		$new_type = Input::get('type');
		
		if(!empty($new_type))
			$setting->type = $new_type;
		
		$new_name = Input::get('name');
		$new_value = Input::get('value');

		
		
		$setting->name = $new_name;
		$setting->value = $new_value;
		$setting->save();
		
		return Redirect::back()->with('successMessage', "Successfully saved settings.");
		
		
	}
	
	public function sms_settings()
	{

		$schemes = Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->get()->first();
		$sms_password = $schemes['sms_password'];

		$this->layout->page = View::make('settings/sms_settings_view', array('sms_password' => $sms_password, 'messages' => $schemes));
	}

	public function change_sms_password($password)
	{
		Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->update(array('sms_password' => $password));

		return Redirect::to('settings/sms_settings');
	}

	public function save_sms_message()
	{
		$text = Input::get('smsmessage');
        $number = Input::get('formid');

        if($number==1)
        {
            Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->update(array('balance_message' => $text));
        }
        
        if($number==2)
        {
            Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->update(array('IOU_message' => $text));
        }
        
        if($number==3)
        {
            Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->update(array('IOU_extra_message' => $text));
        }
        
        if($number==4)
        {
            Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->update(array('IOU_denied_message' => $text));
        }
        
        if($number==5)
        {
            Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->update(array('rates_message' => $text));
        }
        
        if($number==6)
        {
            Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->update(array('shut_off_message' => $text));
        }
        
        if($number==7)
        {
            Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->update(array('shut_off_warning_message' => $text)); 
        }
        
        if($number==8)
        {
            Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->update(array('credit_warning_message' => $text));
        }
        
        if($number==9)
        {
            Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->update(array('barcode_message' => $text));
        }
        
        if($number==10)
        {
            Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->update(array('topup_message' => $text));
        }

        return Redirect::to('settings/sms_settings');
        
	}

	public function faq($scheme_id = null)
	{	
		
		$schemes = Auth::user()->mySchemes();
		
		if($scheme_id != null) {
			$schemes = Scheme::where('scheme_number', $scheme_id)->get();
		}
		
		$this->layout->page = View::make('settings/faq', [
			'scheme_id' => $scheme_id,
			'schemes' => $schemes,
		]);
	}

	public function add_mass_faq()
	{
		try {
			
			$schemes = Input::get('schemes');
			$faq_question = Input::get('faq_question');
			$faq_answer = Input::get('faq_answer');
			
			if(empty($schemes) || count($schemes) <= 0) {
				throw new Exception("You did not select any schemes!");
			}
			
			
			if(strlen($faq_question) <= 1 || strlen($faq_answer) <= 1) {
				throw new Exception("Please fill in all the fields!");
			}
			
			$res = false;
			
			foreach($schemes as $k => $v) {
				$scheme = Scheme::find($v);
				if($scheme) {
					$res = $scheme->addFAQ($faq_question, $faq_answer);
				}
			}
		
			
			if($res) {
				return Response::json([
					"success" => "Successfully added new FAQ to " . count($schemes) . " schemes!",
				]);
			}
			
			throw new Exception("Failed to add FAQ!"); 	
			
		} catch(Exception $e) {
			return Response::json([
				"error" => $e->getMessage()
			]);
		}
	}
	
	public function save_faq($scheme_id = null)
	{
		
		try {
			
				$scheme_number = Input::get('scheme_number');
			$scheme = Scheme::find($scheme_number);
			
			$faqcounter = Input::get('faqcounter');
			for($i = 0; $i < $faqcounter; $i++){
				$q = Input::get('q'.$i);
				$a = Input::get('a'.$i);

				if($q != '' && $a != ''){
					$faq[$i]['question'] = $q;
					$faq[$i]['answer'] = $a;
				}
			}
			$faqs = json_encode($faq);


		
			if(!$scheme)
				throw new Exception("Scheme with ID " . $scheme_number . " does not exist!");
			
			Scheme::where('scheme_number', '=', $scheme->scheme_number)->update(array('FAQ' => $faqs));
			
			if($scheme_id != null) {
				return Redirect::to('settings/faq/' . $scheme_id)->with([
					'successMessage' => 'Successfully saved changes to <b>' . $scheme->scheme_nickname . "'s</b> FAQ",
				]);
			} else {
				return Redirect::to('settings/faq')->with([
					'successMessage' => 'Successfully saved changes to <b>' . $scheme->scheme_nickname . "'s</b> FAQ",
				]);
			}
			
		} catch(Exception $e) {
			return Redirect::to('settings/faq')->with([
				'errorMessage' => $e->getMessage() . " on Line: " . $e->getLine(),
			]); 
		}
	}

	public function tariff()
	{
	
		$viewInfo = [];
        $currentScheme = Auth::user()->schemes()->where('scheme_number', Auth::user()->scheme_number)->first();
        $schemeNumbers = stripos(Route::getCurrentRoute()->getPath(), 'all') ? Auth::user()->activeSchemes()->lists('scheme_number') : [(int)Auth::user()->scheme_number];

		$tarrifs = Tariff::with('scheme')->whereIN('scheme_number', $schemeNumbers)->get();
        $new_tarrifs = TariffChanges::with('scheme', 'tarrif')->whereIN('scheme_number', $schemeNumbers)->where('cancelled', '=', 0)->where('change_date', '>', date('Y-m-d'))->get();
        $past_tarrifs = TariffChanges::with('scheme', 'tarrif')->whereIN('scheme_number', $schemeNumbers)->where('cancelled', '=', 0)->where('change_date', '<=', date('Y-m-d'))->get();

		$viewInfo['all']            = stripos(Route::getCurrentRoute()->getPath(), 'all') ? true : false;
        $viewInfo['currentScheme']  = $currentScheme;
        $viewInfo['tarrifs']        = $tarrifs;
        $viewInfo['new_tarrifs']    = $new_tarrifs;
        $viewInfo['past_tarrifs']   = $past_tarrifs;

		$this->layout->page = View::make('settings/tariff_view', $viewInfo);
	}

	public function tariffadd()
	{
		$tariffType = Input::get('tarriftype');
        preg_match('/scheme_([0-9]+)_tariff_([0-9])/', $tariffType, $matches);
        $schemeNumber = $matches[1];
        $tariffToChange = 'tariff_' . $matches[2];
	
		$tariff = new TariffChanges();
        $tariff->scheme_number      = $schemeNumber;
        $tariff->change_date        = Input::get('fromDate');
        $tariff->tariff_to_change   = $tariffToChange;
        $tariff->new_value          = Input::get('newValue');
        $tariff->admin_name         = Auth::user()->username;
        $tariff->complete           = 0;
        $tariff->cancelled          = 0;
        $tariff->save();

        \Session::flash('tarrif-added', true);
        return Redirect::to('settings/tariff' . (Input::get('tariff-all') ? '/all' : ''));
	}

	public function tariffcancel($tarrif_id)
	{
		TariffChanges::where('id', '=', $tarrif_id)->update(array('cancelled' => 1));

		return Redirect::to('settings/tariff');
	}

	public function credit_setting()
	{

		$shutoff = Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->get()->first();

		$this->layout->page = View::make('settings/credit_setting', array('shutoff' => $shutoff));
	}

	public function credit_setting_change()
	{
		$json = '{"Days":[{"Day":"Monday","Shut_Off_Start":"';

        $json .= Input::get('MondayStartHour').':';
        $json .= Input::get('MondayStartMin').'","Shut_Off_End":"';
        $json .= Input::get('MondayEndHour').':';
        $json .= Input::get('MondayEndMin').'","Active":';
        $json .= (Input::get('MondayActive') == "on")?'1':'0';
        $json .= '},';

        $json .= '{"Day":"Tuesday","Shut_Off_Start":"';

        $json .= Input::get('TuesdayStartHour').':';
        $json .= Input::get('TuesdayStartMin').'","Shut_Off_End":"';
        $json .= Input::get('TuesdayEndHour').':';
        $json .= Input::get('TuesdayEndMin').'","Active":';
        $json .= (Input::get('TuesdayActive') == "on")?'1':'0';
        $json .= '},';

        $json .= '{"Day":"Wednesday","Shut_Off_Start":"';

        $json .= Input::get('WednesdayStartHour').':';
        $json .= Input::get('WednesdayStartMin').'","Shut_Off_End":"';
        $json .= Input::get('WednesdayEndHour').':';
        $json .= Input::get('WednesdayEndMin').'","Active":';
        $json .= (Input::get('WednesdayActive') == "on")?'1':'0';
        $json .= '},';

        $json .= '{"Day":"Thursday","Shut_Off_Start":"';

        $json .= Input::get('ThursdayStartHour').':';
        $json .= Input::get('ThursdayStartMin').'","Shut_Off_End":"';
        $json .= Input::get('ThursdayEndHour').':';
        $json .= Input::get('ThursdayEndMin').'","Active":';
        $json .= (Input::get('ThursdayActive') == "on")?'1':'0';
        $json .= '},';

        $json .= '{"Day":"Friday","Shut_Off_Start":"';

        $json .= Input::get('FridayStartHour').':';
        $json .= Input::get('FridayStartMin').'","Shut_Off_End":"';
        $json .= Input::get('FridayEndHour').':';
        $json .= Input::get('FridayEndMin').'","Active":';
        $json .= (Input::get('FridayActive') == "on")?'1':'0';
        $json .= '},';

        $json .= '{"Day":"Saturday","Shut_Off_Start":"';

        $json .= Input::get('SaturdayStartHour').':';
        $json .= Input::get('SaturdayStartMin').'","Shut_Off_End":"';
        $json .= Input::get('SaturdayEndHour').':';
        $json .= Input::get('SaturdayEndMin').'","Active":';
        $json .= (Input::get('SaturdayActive') == "on")?'1':'0';
        $json .= '},';

        $json .= '{"Day":"Sunday","Shut_Off_Start":"';

        $json .= Input::get('SundayStartHour').':';
        $json .= Input::get('SundayStartMin').'","Shut_Off_End":"';
        $json .= Input::get('SundayEndHour').':';
        $json .= Input::get('SundayEndMin').'","Active":';
        $json .= (Input::get('SundayActive') == "on")?'1':'0';
        $json .= '}]}';

        Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->update(array('shut_off_periods' => $json));

        return Redirect::to('settings/credit_setting');
	}
	
	public function unassignedUsers()
    {
        $users = User::where('parent_id', 0)->excludeAdmin()->get();
        $baseURL = URL::to('settings/unassigned_users');

        $this->layout->page = View::make('settings/unassigned_users', array(
            'customers' => $users,
            'baseURL' => $baseURL
        ));
    }

	//show all users assigned to the currently logged one (within BOSS)
	public function access_control()
	{
		//$customers = User::where('scheme_number', '=', Auth::user()->scheme_number)->get();
        $schemeIDs = getSchemes(Auth::user());
        $schemesCollection = Scheme::whereIn('id', $schemeIDs)->withoutArchived()->get();
        $schemes = [];
        foreach ($schemesCollection as $scheme)
        {
            $schemes[$scheme->id] = $scheme->nickname ? : $scheme->company_name;
        }

        $userChildren = getChildren(Auth::user()->id);
		
		$customers = [];
        if ($userChildren)
        {
			$customers = User::whereIn('id', $userChildren)->get();
		}

        $baseURL = URL::to('settings/access_control');

		$this->layout->page = View::make('settings/access_control', array(
            'customers' => $customers,
            'schemes' => $schemes,
            'baseURL' => $baseURL
        ));
	}
	
	public function accessControlSchemes($userID)
    {
        $user = User::findOrFail($userID);
        $schemeIDs = getSchemes(Auth::user());
        $schemes = Scheme::whereIn('id', $schemeIDs)->withoutArchived()->get();
        $userSchemes = getUserSchemes($user);

        $breadcrumbsURL = URL::to('settings/access_control');
        $breadcrumbsLinkText = 'Access Control';
        $formURL = URL::to('settings/access_control/' . $user->id . '/schemes');
        if (stripos(Request::url(), 'unassigned_users') !== false)
        {
            $breadcrumbsURL = URL::to('settings/unassigned_users');
            $breadcrumbsLinkText = 'Unassigned Users';
            $formURL = URL::to('settings/unassigned_users/' . $user->id . '/schemes');
        }

        $this->layout->page = View::make('settings/access_control_schemes', [
            'user' => $user,
            'schemes' => $schemes,
            'userSchemes' => $userSchemes,
            'breadcrumbsURL' => $breadcrumbsURL,
            'breadcrumbsLinkText' => $breadcrumbsLinkText,
            'formURL' => $formURL
        ]);
    }

    public function accessControlSchemesUpdate($userID)
    {
        $user = User::findOrFail($userID);
        $schemes = Input::get('schemes') ? : [];
        $user->schemes()->sync($schemes);

        Session::flash('successMessage', 'The user schemes were successfully updated');
        if (stripos(Request::url(), 'unassigned_users') !== false)
        {
            return Redirect::to('settings/unassigned_users');
        }

        return Redirect::to('settings/access_control');
    }

	public function editAccount($id)
    {
        $user = User::findOrFail($id);
        $user->employee_name = Input::get('employee_name');
        $user->username = Input::get('username');
        $user->group_id = (int)Input::get('group');
        $user->save();

        Session::flash('successMessage', 'The account information was updated successfully');
        if (stripos(Request::url(), 'unassigned_users') !== false)
        {
            return Redirect::to('settings/unassigned_users');
        }

        return Redirect::to('settings/access_control');
    }
	
	public function close_account_action($id)
	{
		$user = User::findOrFail($id);
        $user->schemes()->detach();
		$user->delete();

        if (stripos(Request::url(), 'unassigned_users') !== false)
        {
            return Redirect::to('settings/unassigned_users');
        }

		return Redirect::to('settings/access_control');
	}

	public function add_account_action()
	{
        //get scheme by scheme_number
        $scheme = Scheme::where('scheme_number', Auth::user()->scheme_number)->first();

		$postedGroupID = (int)Input::get('group');
		
		$newUserSchemes = Input::get('schemes');
		
		$fromInstallerWebsite = stripos(Request::url(), 'prepago_installer/access_control') !== false;
		
        $user = new User();
        //$user->scheme_number = Auth::user()->scheme_number;
        $user->employee_name = Input::get('employee_name');
        $user->username = Input::get('username');
        $user->password = Hash::make(Input::get('password'));
        //$user->password_unsecure = Input::get('password');
        $user->account_type = 1;
		$user->group_id = $postedGroupID;
        $user->paid = $postedGroupID == 5 ? 1 : 0;
        $user->charge = $postedGroupID == 5 ? 0 : $scheme['prepago_new_admin_charge'];
        $user->isInstaller = Input::get('isInstaller');
		
		if ($fromInstallerWebsite)
        {
            $user->parent_id = Auth::user()->id;
        }
		
        $user->save();

		if ($newUserSchemes)
        {
            $user->schemes()->attach($newUserSchemes);
        }
		
		if ($fromInstallerWebsite)
        {
            return Redirect::to('prepago_installer/access_control');
        }
		
        return Redirect::to('settings/access_control');
	}

	
	public function utilityUserSetup()
	{

	    $this->layout->page = View::make('settings/utility_user_setup', [
           
        ]);
	}
	
	
	public function utilityUserSetupSubmit()
	{
		
		try {

	
			$user = new User();
			$user->username = Input::get('username');
			$user->password = Hash::make(Input::get('password'));
			$user->account_type = 1;
			$user->group_id = Input::get('group_id');
			$user->employee_name = Input::get('employee_name');
			$user->email_address = Input::get('email_address');
			$user->paid = $user->group_id == 5 ? 1 : 0;
			$user->charge = 100;
			$user->isInstaller = (Input::get('isInstaller') == 'on') ? 1 : 0;
			if($user->isInstaller)
				$user->group_id = 5;
			
			$scheme = Scheme::find(Input::get('scheme'));
			
			
			if(empty($user->username) || empty(Input::get('password'))) {
				throw new Exception("The username or password cannot not be empty!");
			}
			
			if(User::where('username', $user->username)->first()) {
				throw new Exception("This username '" . $user->username . "' is already taken!");
			}
			
			if(User::where('email_address', $user->email_address)->first()) {
				throw new Exception("This email address '" . $user->email_address . "' is already taken!");
			}
			
			if(!$scheme)
				throw new Exception("Selected scheme doesn't exist!");
			
			if($user->save()) {
				
				$scheme->users()->attach($user->id);
					
				$email = new Email();
				$email->to = $user->email_address;
				$email->title = "Successfully registered a new Prepago operator account";
				$email->body = "Dear " . $user->employee_name . ",<br/>";
				$email->body .= "A utility operator account has been created on your behalf.";
				$email->body .= "<br/><br/>";
				$email->body .= "<b>These are the credentials:</b><br/>";
				$email->body .= "<b>Username:</b> " . $user->username . "<br/>";
				$email->body .= "<b>Password:</b> " . Input::get('password') . "<br/>";
				$email->body .= "* Please <b>delete</b> this email after taking note of the credentials *";
				$email->body .= "<br/><br/><hr>";
				$email->body .= "<a href='" . URL::to('/') . "'>Login now &gt;&gt;</a>";
				$email->send();
				
			} else {
				throw new Exception('Error: Failed to create new Operator for ' . $scheme->company_name);	
			}
		
			
		} catch(Exception $e) {
			
			
			return Redirect::back()->with([
				'errorMessage' => '<b>Error: </b> ' . $e->getMessage(),
				'error' => $e->getMessage(),
				'success' => 0
			]);
			
		}
		
		
		return Redirect::back()->with([
			'successMessage' => 'Successfully created new utility user "' . $user->username . '"!',
		]);

	}
	
	
	public function testScan()
	{
		//
		$scans = TestScan::where('completed', 0)->orderBy('id', 'ASC')->get();
		$schemes = Scheme::where('archived', 0)->orderBy('id', 'DESC')->get();
		
		$this->layout->page = View::make("home.test_scan", [
			'scans' => $scans,
			'schemes' => $schemes,
		]);
	}
	
	public function testScanAjax()
	{
		
		$action = Input::get('action');
		$list = Input::get('list');
		$scheme = Input::get('scheme');
		$parsed_list = [];
		$errors = [];
		$duplicates = [];
		$already_running = [];
		
		try {
			
			// Check the format of the list
			
			if(!is_array($list) || count($list) == 0 || !isset($list[0]) || !isset($list[0]['scu']) || !isset($list[0]['meter']) || empty(str_replace(' ', '', $list)) ) {
				throw new Exception("Please enter a valid list of SCU's & Meters");
			}
			
			
			
			if($action == 'check') {

				foreach($list as $key => $l) {
					
					if(!isset($l['scu']) || empty($l['scu'])) {
						array_push($errors, (object)[
							"msg" => "Missing SCU in Line " . ($key+1) . ".",
							"line" => "Line " . ($key+1)
						]);
						continue;
					}
					if(!isset($l['meter']) || empty($l['meter'])) {
						array_push($errors, (object)[
							"msg" => "Missing Meter in Line " . ($key+1) . ".",
							"line" => "Line " . ($key+1)
						]);
						continue;
					}
					
					$scu = $l['scu'];
					$meter = $l['meter'];
					
					if(strlen($scu) != 8) {
						array_push($errors, (object)[
							"msg" => "Invalid SCU '$scu', Length must be 8.",
							"line" => "Line " . ($key+1) . ": $scu $meter",
						]);
						continue;
					}
					if(strlen($meter) != 8) {
						array_push($errors, (object)[
							"msg" => "Invalid Meter '$meter', Length must be 8.",
							"line" => "Line " . ($key+1) . ": $scu $meter",
						]);
						continue;
					}
					if(strcmp($scu, $meter) === 0) {
						array_push($errors, (object)[
							"msg" => "SCU & Meter cannot be the same!",
							"line" => "Line " . ($key+1) . ": $scu $meter",
						]);
						continue;
					}
					
					$check1 = MBusAddressTranslation::where('8digit', $scu)->first();
					$check2 = MBusAddressTranslation::where('8digit', $meter)->first();
					
					if(!$check1) {
						array_push($errors, (object)[
							"msg" => "Cannot find SCU '$scu' in the database. Please insert it first.",
							"line" => "Line " . ($key+1) . ": $scu $meter",
						]);
					}
		
					if(!$check2) {
						array_push($errors, (object)[
							"msg" => "Cannot find Meter '$meter' in the database. Please insert it first.",
							"line" => "Line " . ($key+1) . ": $scu $meter",
						]);
					}

				}
				
			} else if($action == 'start') {
				
			
				$refresh_rate =  null;
				$expected_change = null;
				
				if(Input::get('refresh_rate')) {				
					try {	
						$refresh_rate = abs((int)preg_replace('/\s/', '', Input::get('refresh_rate')));
						if(empty($refresh_rate)) 
						$refresh_rate = null;
					} catch(Exception $e) {
						$refresh_rate = null;
					}		
				}
				
				if(Input::get('expected_change')) {				
					try {	
						$expected_change = abs((int)preg_replace('/\s/', '', Input::get('expected_change')));
						if(empty($expected_change)) 
						$expected_change = null;
					} catch(Exception $e) {
						$expected_change = null;
					}		
				}
				
				foreach($list as $k => $l) {
					
					$scu = $l['scu'];
					$meter = $l['meter'];
					$key = $scu ."-". $meter;
					
					if(isset($parsed_list[$key])) {
						$duplicates[] = (object)[
							"line" => ($k+1),
							"scu" => $scu,
							"meter" => $meter,
						];
						continue;
					}
					
					
					$instance = TestScan::whereRaw("( (scu = '$scu' OR meter = '$meter') AND completed = '0' )")->first();
					if($instance) {
						$already_running[] = (object)[
							"line" => ($k+1),
							"scu" => $scu,
							"meter" => $meter,
						];
						continue;
					}

					$pmd = PermanentMeterData::whereRaw("(meter_number LIKE '%" . $meter . "%' AND scu_number LIKE '%" . $scu . "%')")
					->first();
					
					
					$scan = new TestScan();
					
					$scan->scheme_number = $scheme;
					if($pmd) {
						$scan->username = $pmd->username;
						$scan->scheme_number = $pmd->scheme_number;
						$scan->IP = $pmd->IP;
					}
					$scan->scu = MBusAddressTranslation::where('8digit', $scu)->first()['16digit'];
					$scan->meter = MBusAddressTranslation::where('8digit', $meter)->first()['16digit'];;
					$scan->started = false;
					$scan->completed = false;
					$scan->progress = 0;
					if($refresh_rate != null)
						$scan->refresh_every_mins = $refresh_rate;
					if($expected_change != null)
						$scan->expected_temp_change = $expected_change;
					$scan->refresh_times = 0;
					
					$dl = DataLogger::where('scheme_number', $scan->scheme_number)->first();
					$SIM = Simcard::where('ID', $dl->sim_id)->first();
					if($SIM){
						$scan->IP = $SIM->IP_Address;
					}
					
					$scan->save();

					$parsed_list[$key] = $scan;
				
				}
				
			}	
		} catch(Exception $e) {
			array_push($errors, (object)[
				"msg" => "Fatal exception: " . $e->getMessage()
			]);
		}
		
		return Response::json([
			'action' => $action,
			'parsed_list' => $parsed_list,
			'errors' => $errors,
			'duplicates' => $duplicates,
			'already_running' => $already_running,
		]);
	}
	
	
	public function testScanReport($id)
	{
			
		$scan = TestScan::find($id);
		
		if(!$scan) {
			return Response::json([
				'error' => "Test scan $id does not exist!",
			]);
		}
		
		$val = SystemSetting::get('email_test_scan');
		$emails = preg_split( '/\r\n|\r|\n/', $val);
		foreach($emails as $email) {
			
		}
		
			$body = "";
			$body .= "hi";
			
			Email::quick_send($body, "Prepago Java Scheduler: Test Scan #$id", $emails, 'info@prepago.ie', 'Prepago Monitor');
				
				
		return Response::json([
			'success' => "Successfully sent test scan report to " . count($emails) . " emails",
		]);
		
	}
	
	
	public function smsTargetSubmit()
	{
		try {
			
			
			$charge = 0.08;
			$charge_for_sms = true;
			
			$targets = $this->getTargets();
			$old_app_customers = $targets['old_app_customers'];
			$paypal_customers = $targets['paypal_customers'];
		
			//$charge = Input::get('charge');
			$message = Input::get('message');
			$type = Input::get('type');
			$sent = 0;
			$sent_to = [];
			
			if($type == 'old_app_customers') {
				foreach($old_app_customers as $k => $c) {	

					if($charge_for_sms) {
						$customer = Customer::find($c->id);
						if($customer) {
							$customer->update([
								'balance' => ($c->balance - $charge)
							]);
						}
					}
					
					$sms_messages = new SMSMessage();
					$sms_messages->customer_id = $c->id;
					$sms_messages->mobile_number = $c->mobile_number;
					$sms_messages->message = $message;
					$sms_messages->date_time = date('Y-m-d H:i:s');
					$sms_messages->scheme_number = $c->scheme_number;
					$sms_messages->charge = $charge;
					$sms_messages->paid = $charge_for_sms;
					$sms_messages->save();
					$sent++;
					$sent_to[] = $c->id;
				}
			}
			
			if($type == 'paypal_customers') {
				foreach($paypal_customers as $k => $c) {	
				
					if($charge_for_sms) {
						$customer = Customer::find($c->id);
						if($customer) {
							$customer->update([
								'balance' => ($c->balance - $charge)
							]);
						}
					}
					
					$sms_messages = new SMSMessage();
					$sms_messages->customer_id = $c->id;
					$sms_messages->mobile_number = $c->mobile_number;
					$sms_messages->message = $message;
					$sms_messages->date_time = date('Y-m-d H:i:s');
					$sms_messages->scheme_number = $c->scheme_number;
					$sms_messages->charge = $charge;
					$sms_messages->paid = $charge_for_sms;
					$sms_messages->save();
					$sent++;
					$sent_to[] = $c->id;
				}
			}
				
			return Redirect::back()->with([
				'successMessage' => "Successfully sent $type SMS: '$message' to $sent customers",
				'sent_to' => $sent_to,
			]);
			
		} catch(Exception $e) {
			return Redirect::back()->with([
				'errorMessage' => $e->getMessage(),
			]);
		}
	}
	
	public function smsTarget()
	{
		$targets = $this->getTargets();
		$old_app_customers = $targets['old_app_customers'];
		$paypal_customers = $targets['paypal_customers'];
		
		
		$this->layout->page = View::make('settings.sms_target', [
			'old_app_customers' => $old_app_customers,
			'paypal_customers' => $paypal_customers,
		]);
		
	}
	
	
	private function getTargets()
	{
		ini_set('max_execution_time', '0');
		
		$old_app_customers = [];
		$paypal_customers = [];
		
		$old_logins = TrackingCustomerActivity::whereRaw("(date_time >= '2019-11-01')")
		->orderBy('id', 'DESC')->get();
		
		$paypal = PaymentStorage::whereRaw("(settlement_date >= '2019-11-01' AND ref_number LIKE '%PAYID-%' )")
		->orderBy('time_date', 'DESC')->get();
			
		foreach($old_logins as $k => $v) {
			$customer = Customer::find($v->customer_id);
			if($customer && !in_array($customer->id, $old_app_customers)) {
				$old_app_customers[] = $customer->id;
			}
		}
		
		foreach($paypal as $k => $v) {
			$customer = Customer::find($v->customer_id);
			if($customer && !in_array($customer->id, $paypal_customers)) {
				$paypal_customers[] = $customer->id;
			}
		}
		
		foreach($old_app_customers as $k => $c) {
			$last_login = TrackingCustomerActivity::where('customer_id', $c)->orderBy('id', 'DESC')->first();
			$c = Customer::find($c);
			$c->last_login = $last_login->date_time;
			$old_app_customers[$k] = $c;
			continue;
		}
		
		foreach($paypal_customers as $k => $c) {
			$last_topup = PaymentStorage::where('customer_id', $c)
			->whereRaw("(ref_number LIKE '%PAYID-%')")
			->orderBy('time_date', 'DESC')->first();
			$c = Customer::find($c);
			if($last_topup)
				$c->last_topup = $last_topup->time_date;
			$paypal_customers[$k] = $c;
			continue;
		}
		
		
		return [
			'old_app_customers' => $old_app_customers,
			'paypal_customers' => $paypal_customers,
		];
	}
	
	
		
	public function sms_presets()
	{
			
		$presets = SMSMessagePreset::all();
		$categories = SMSMessagePreset::groupBy('category')->orderBy('id', 'ASC')->get();
	
		$this->layout->page = View::make('settings/sms_presets', ['presets' => $presets, 'categories' => $categories]);
		
		
		
	}
	
	public function sms_presets_add()
	{
		
		$preset = SMSMessagePreset::where('category', Input::get('get'))->where('body', Input::get('body'))->first();
		
		if($preset)
			return Redirect::back()->with('errorMessage', "That preset already exists.");
		
		$new_preset = new SMSMessagePreset();
		$new_preset->category = Input::get('category');
		$new_preset->name = Input::get('name');
		$new_preset->body = Input::get('body');
		$new_preset->save();
		
		return Redirect::back()->with('successMessage', "Successfully created new preset under '$new_preset->category' Category.");
		
		
	}
	
	public function sms_presets_remove($id)
	{
		
		$preset = SMSMessagePreset::where('id', $id)->first();
		
		$preset_name = '';
		
		if($preset) {
			
			$preset_name = $preset->name;
		
			$preset->delete();
		}
		
		
		
		return Redirect::back()->with('successMessage', "Successfully removed preset '$preset_name'");
		
	}
	
	public function sms_presets_save($id)
	{
		
		$preset = SMSMessagePreset::where('id', $id)->first();
		$preset_copy_name = $preset->name;
		
		if(!$preset)
			return Redirect::back()->with('errorMessage', "That preset does not exist.");
		
		$new_category = Input::get('category');
		
		if(!empty($new_category))
			$preset->category = $new_category;
		
		$new_name = Input::get('name');
		$new_body = Input::get('body');

		
		
		$preset->name = $new_name;
		$preset->body = $new_body;
		$preset->save();
		
		return Redirect::back()->with('successMessage', "Successfully saved presets.");
		
		
	}
	

}