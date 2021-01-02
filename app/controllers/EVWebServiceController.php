<?php
use \Illuminate\Support\Facades\Response as Response;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class EVWebServiceController extends BaseController {
	
	private $ws;
	private $headers;
	
	// ----------------------------------------------------------------------------------------------------------
	public function __construct(WebServiceRepositoryInterface $ws) {
		$this->ws = $ws;
		$this->headers = ['Content-type'=> 'application/json; charset=utf-8'];
	}
	
	public function loginVerification($username, $password, $email)
	{
		
			$isSha1 = (bool) preg_match('/^[0-9a-f]{40}$/i', $password);
			if(!$isSha1)
				$password = sha1($password);
			
			$ev_customer = Customer::where('username', $username)->first();
			
			if(!$ev_customer) {
				
				$customers = Customer::where('status', 1)
				->get();
				
				foreach($customers as $c) {
					if(strtolower($c->email_address) == strtolower($username))
						$ev_customer = $c;
				}
			}
		
			if(!$ev_customer)
				return Response::json(['username' => 'This username/email does not exist', 'error' => true]);
			
			//return Response::json(['password' => $password, 'error' => true]);

			if($ev_customer->password == '') {

				DB::table('customers')->where('id', $ev_customer->id)
				->update([
					'password' => $password,
				]);
				return Response::json([
					'id'		  => $ev_customer->id,
					'success' => true,
					'error' => false,
				]);
			}
			
			if($ev_customer->password != $password)
				return Response::json(['password' => 'This password does not belong to this user', 'error' => true]);
			
			return Response::json([
				'id'		  => $ev_customer->id,
				'success' => true,
				'error' => false,
			]);
	}

	public function evLogin($username, $password)
	{
		$password = sha1($password);
		
		return $this->loginVerification($username, $password, null);
	}
	
	public function evRechargeOn($customer_id, $email, $username, $password, $rsCode)
	{
		if ($this->loginVerification($username, $password, $email)->getData()->error)
		{
			$response = [
				'ev_recharge_status' 	=> '',
				'flag_message' 			=> 1,
				'error'  => $this->loginVerification($username, $password, $email)->getData()->error
			];
			
			return Response::json($response, 200, $this->headers, JSON_UNESCAPED_UNICODE);
		}

		$evRechargeOn = \Illuminate\Support\Facades\App::make('EVRechargeOn', [$rsCode, $customer_id]);
		$response = $evRechargeOn->handle();

		return Response::json($response, 200, $this->headers, JSON_UNESCAPED_UNICODE);
	}
	
	public function initiateManualRechargeStop($customer_id, $email, $username, $password, $rsCode)
	{
		if ($this->loginVerification($username, $password, $email)->getData()->error)
		{
			$response = [
				'ev_recharge_status' 	=> '',
				'flag_message' 			=> 1,
				'error'  => $this->loginVerification($username, $password, $email)->getData()->error
			];
			return Response::json($response, 200, $this->headers, JSON_UNESCAPED_UNICODE);
		}

		$initiateManualRechargeStop = \Illuminate\Support\Facades\App::make('InitiateManualRechargeStop', [$rsCode, $customer_id]);
		$response = $initiateManualRechargeStop->handle();

		return Response::json($response, 200, $this->headers, JSON_UNESCAPED_UNICODE);
	}
	
	// ----------------------------------------------------------------------------------------------------------
	public function finalizeRechargeStopProcedure($customer_id, $email, $username, $password, $rsCode, $manually = true)
	{
		if ($this->loginVerification($username, $password, $email)->getData()->error)
		{
			$response = [
				'ev_recharge_status' 	=> '',
				'flag_message' 			=> 1,
				'error'  => $this->loginVerification($username, $password, $email)->getData()->error
			];
			return Response::json($response, 200, $this->headers, JSON_UNESCAPED_UNICODE);
		}

		$finalizeRechargeStopProcedure = \Illuminate\Support\Facades\App::make('FinalizeRechargeStopProcedure', [$rsCode, $customer_id]);
		$response = $finalizeRechargeStopProcedure->handle($manually);

		return Response::json($response, 200, $this->headers, JSON_UNESCAPED_UNICODE);
	}
	
	// ----------------------------------------------------------------------------------------------------------
	public function getMeterRechargeStatus($customer_id, $email, $username, $password, $rsCode)
	{
		$customer_id = (int)$customer_id;
		$response = $this->ws->getMeterRechargeStatusRequest($customer_id, $email, $username, $password, $rsCode);

		return Response::json($response, 200, $this->headers, JSON_UNESCAPED_UNICODE);
	}
	
	/* EV Web Services implemented by Daniel 25/07/17 */
	
	// ----------------------------------------------------------------------------------------------------------
	public function getStation($ev_rs_code)
	{
		$stations = PermanentMeterData::where('meter_type', 'EV')->where('ev_rs_code', $ev_rs_code)->get();
		return Response::json($stations, 200, $this->headers, JSON_UNESCAPED_UNICODE);
	}
	
	// ----------------------------------------------------------------------------------------------------------
	public function getStations()
	{
		$stations = PermanentMeterData::where('meter_type', 'EV')->get();
		return Response::json($stations, 200, $this->headers, JSON_UNESCAPED_UNICODE);
	}
	
	// ----------------------------------------------------------------------------------------------------------
	public function getLastRecharge($customer_id, $email, $username, $password, $rsCode)
	{
		$customer_id = (int)$customer_id;
		
		$evusage = EVUsage::where('customer_id', $customer_id)->orderBy('ev_timestamp', 'DESC')->first();
		
		return Response::json($evusage, 200, $this->headers, JSON_UNESCAPED_UNICODE);
	}
	
	public function getStationUser($station)
	{
		
		

		
	}
	
	public function resetEVPassword($customer)
	{
		$resetCode = rand(1000, 5000);
		
		$message = "You recently requested a password reset. Your code is " . $resetCode;
		
		$this->sendEVSMS($customer, $message, "There was an error sending reset password");
	}
	
	public function getEVCustomer($email)
	{
		return Response::json(Customer::where('email_address', $email)->first());
	}
	
	public function sendEVSms($customer_id, $message, $errorMessage)
	{	
	
		$schemeNumber = 18;
		$scheme = Scheme::find($schemeNumber);
		
		$customer = 323;
		$message = $message;
		
		
        $smsRes = \Illuminate\Support\Facades\App::make('SMSController')->user_specific_message(323, $schemeNumber, $scheme->sms_password, $message);
        $dataJson = json_decode($smsRes);
        if (isset($dataJson->success) && $dataJson->success == 0)
        {
            echo 'fiucl';
        }
		else
		{
			echo Response::json($dataJson);
		}
	}
	
}