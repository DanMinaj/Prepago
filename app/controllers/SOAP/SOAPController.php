<?php

class SOAPController extends BaseController {

	protected $layout = 'layouts.admin_website';
		
	private $testMode = false;
	
	private $SenderId = 7331;
	private $Password = "TREBUCHETfx=*S2D:h#j6B)?L";

	
	/**
	 * ---------- IMPORTANT ----------
	 * Reminder: In live implementation, make sure `rtu_command_queue`.`ID` column is 'AUTO_INCREMENT' for meter on/off.
	 *
	**/
	public function __construct() 
	{
			
		// If testmode is enabled, used default Payzone SenderId & Password
		if($this->testMode || 1==1) {
			$this->SenderId = "payzone";
			$this->Password = "testTest";
		}
		
		$this->AuthenticationTableExists(false);
		$this->SetAuthenticationCredentials();
		$this->ArchivedTableExists(false);
	}
	
	/**
	 *
	 * Process a LoadCard request
	 *
	 */
	public function processPayment()
	{
		
		$paymentInfo = (object)Input::get('params');
		
		try {
			
			// Check Authentication
			$checkAuth = $this->checkAuth(Input::get('authentication'));
			
			if($checkAuth != "Success") {
				throw new Exception($checkAuth);
			}
			
			// Convert cardValue to an object
			$paymentInfo->cardValue = (object)$paymentInfo->cardValue;
			
			// Make sure customer exists if not, return an exception
			$customer = Customer::where('barcode', $paymentInfo->cardNumber)->first();
			if(!$customer) {
				throw new Exception("Barcode doesn't exist: " . $paymentInfo->cardNumber);
			}
			
			$existingPayment = PaymentStorage::where('ref_number', 'PZ-' . $paymentInfo->requestId)->first();
			if($existingPayment) {
				throw new Exception("RequestId exists: " . $paymentInfo->requestId);
			}
			
			
			// Create a new payment entry in payments_storage
			$paymentStorage  = null;
			
			if($this->testMode)
				$paymentStorage = new PaymentStorageTest();
			else
				$paymentStorage = new PaymentStorage();
			
			$paymentStorage->ref_number = 'PZ-' . $paymentInfo->requestId;
			$paymentStorage->customer_id = $customer->id;
			$paymentStorage->scheme_number = $customer->scheme_number;
			$paymentStorage->barcode = $customer->barcode;
			$paymentStorage->time_date = date("Y-m-d H:i:s");
			$paymentStorage->currency_code = $paymentInfo->cardValue->CurrencyCode;
			$paymentStorage->amount = $paymentInfo->cardValue->Amount / 100;
			$paymentStorage->transaction_fee = 0;
			$paymentStorage->acceptor_name_location_ = "payzone " . $paymentInfo->storeChainId;
			$paymentStorage->payment_received = 1;
			$paymentStorage->settlement_date = date("Y-m-d");
			$paymentStorage->merchant_type = $paymentInfo->merchantId;
			$paymentStorage->POS_entry_mode = 10;
			$paymentStorage->save();

			if($this->testMode) {
				
				$log = new PaymentStorageTestLog();
				$log->message = "Customer " . $customer->id . " topped up by " . $paymentStorage->amount . "eur. New Bal: " . ($customer->balance + $paymentStorage->amount);
				$log->save();
				
			} else {
				
				// Update the customers balance
				
				$customer->topup($paymentStorage);
				
			}
			
			// Response
			$response = 'Success';
			
			
		}
		catch(Exception $e) {
			
			$response = 'Failed|' . $e->getMessage();
			$this->LogPaymentError(0, $response);
			
		}
		
		return $response;
		
	}
	
	/*
	 *
	 * Process a CheckConnection request
	 *
	 */
	public function checkConnection()
	{
		
		try {
			
			// Check Authentication
			$checkAuth = $this->checkAuth(Input::get('authentication'));
			if($checkAuth != "Success") {
				throw new Exception($checkAuth);
			}
			
			$response = "Success";
			
		} catch(Exception $e) {
			
			$response = 'Failed|' . $e->getMessage();
			
		}
	
		return $response;
		
	}
	
	/*
	 *
	 * Process a VoidCard request
	 *
	 */
	public function voidPayment()
	{
		
		$paymentInfo = (object)Input::get('params');
		
		try {
			
			// Check Authentication
			$checkAuth = $this->checkAuth(Input::get('authentication'));
			if($checkAuth != "Success") {
				throw new Exception($checkAuth);
			}
			
			// Convert cardValue to an object
			$paymentInfo->cardValue = (object)$paymentInfo->cardValue;
			
			// Make sure customer exists if not, return an exception
			$customer = Customer::where('barcode', $paymentInfo->cardNumber)->first();
			if(!$customer) {
				throw new Exception("Barcode doesn't exist: " . $paymentInfo->cardNumber);
			}
			
			$existingPayment = null;
			
			if($this->testMode) 
				$existingPayment = PaymentStorageTest::where('ref_number', 'PZ-' . $paymentInfo->requestId)->first();
			else
				$existingPayment = PaymentStorage::where('ref_number', 'PZ-' . $paymentInfo->requestId)->first();
			
			
			if(!$existingPayment) {
				throw new Exception("RequestId doesn't exist: " . $paymentInfo->requestId);
			}
			
			// Archive existing payment (Insert into payments_storage_archived)
			DB::table('payments_storage_archived')->insert([
				'archived_reason' => 'Cancelled by clerk',
				'ref_number' => $existingPayment->ref_number,
				'customer_id' => $existingPayment->customer_id,
				'barcode' => $existingPayment->barcode,
				'time_date' => $existingPayment->time_date,
				'currency_code' => $existingPayment->currency_code,
				'amount' => $existingPayment->amount,
				'acceptor_name_location_' => $existingPayment->acceptor_name_location_,
				'payment_received' => $existingPayment->payment_received,
				'settlement_date' => $existingPayment->settlement_date,
				'merchant_type' => $existingPayment->merchant_type,
				'POS_entry_mode' => $existingPayment->POS_entry_mode,
				'time_date_archived' => date('Y-m-d H:i:s'),
				'test' => $this->testMode,
			]);

			// Update the customers balance (Remove the balance of the existing payment)
			
				
			// Delete existing payment from payments_storage
			if($this->testMode) {
				DB::table('test_payments_storage')->where('ref_number', $existingPayment->ref_number)->delete();
				$log = new PaymentStorageTestLog();
				$log->message = "Customer " . $customer->id . " cancelled a payment ref " . $existingPayment->ref_number  . ". New Bal: " . ($customer->balance - $existingPayment->amount);
				$log->save();
			}
			else {
				
				
				$customer->balance -= $existingPayment->amount;
				$customer->save();
				
				DB::table('payments_storage')->where('ref_number', $existingPayment->ref_number)->delete();
			}
					
			// Response
			$response = 'Success';
			
			
		}
		catch(Exception $e) {
			
			$response = 'Failed|' . $e->getMessage();
			
		}
		
		return $response;
	
		
	}
	
	/*
	 *
	 * Process a ReverseCard request
	 *
	 */
	public function reversePayment()
	{
		$paymentInfo = (object)Input::get('params');
		
		try {
			
			// Check Authentication
			$checkAuth = $this->checkAuth(Input::get('authentication'));
			if($checkAuth != "Success") {
				throw new Exception($checkAuth);
			}
			
			// Convert cardValue to an object
			$paymentInfo->OriginalRequest = (object)$paymentInfo->OriginalRequest;
		
			$existingPayment = null;
			
			if($this->testMode)
				$existingPayment = PaymentStorageTest::where('ref_number', 'PZ-' . $paymentInfo->OriginalRequest->RequestId)->first();
			else
				$existingPayment = PaymentStorage::where('ref_number', 'PZ-' . $paymentInfo->OriginalRequest->RequestId)->first();
			
			if(!$existingPayment) {
				throw new Exception("RequestId doesn't exist: " . $paymentInfo->OriginalRequest->RequestId);
			}
			
			// Archive existing payment (Insert into payments_storage_archived)
			DB::table('payments_storage_archived')->insert([
				'archived_reason' => 'Reversed',
				'ref_number' => $existingPayment->ref_number,
				'customer_id' => $existingPayment->customer_id,
				'barcode' => $existingPayment->barcode,
				'time_date' => $existingPayment->time_date,
				'currency_code' => $existingPayment->currency_code,
				'amount' => $existingPayment->amount,
				'acceptor_name_location_' => $existingPayment->acceptor_name_location_,
				'payment_received' => $existingPayment->payment_received,
				'settlement_date' => $existingPayment->settlement_date,
				'merchant_type' => $existingPayment->merchant_type,
				'POS_entry_mode' => $existingPayment->POS_entry_mode,
				'time_date_archived' => date('Y-m-d H:i:s'),
				'test' => $this->testMode,
			]);

			
			// Update the customers balance (Remove the balance of the existing payment)
			$customer = Customer::find($existingPayment->customer_id);
			if($customer && !$this->testMode) {
				$customer->balance -= $existingPayment->amount;
				$customer->save();
				
				// Delete existing payment from payments_storage
				DB::table('payments_storage')->where('ref_number', $existingPayment->ref_number)->delete();
						
			}
			
			if($this->testMode) {
				
				// Delete existing payment from payments_storage
				DB::table('test_payments_storage')->where('ref_number', $existingPayment->ref_number)->delete();
					

				$log = new PaymentStorageTestLog();
				$log->message = "Customer " . $customer->id . " reversed a payment ref " . $existingPayment->ref_number  . ". New Bal: " . ($customer->balance - $existingPayment->amount);
				$log->save();
			}
				
			
			// Response
			$response = 'Success';
		}
		catch(Exception $e) {
			
			$response = 'Failed|' . $e->getMessage();
			
		}
		
		return $response;
	
	}
	
	/*
	 *
	 * Process a CalcFee request
	 *
	 */
	public function calcFee()
	{
		try {
			
			// Check Authentication
			$checkAuth = $this->checkAuth(Input::get('authentication'));
			if($checkAuth != "Success") {
				throw new Exception($checkAuth);
			}
			
			$response = 'Success';
			
		} catch(Exception $e) {
			
			$response = 'Failed|' . $e->getMessage();
			
		}
		
		return $response;
	}
	
	
	/**
	 *
	 *	Check incoming authentication information
	 *
	 */
	private function checkAuth($authentication)
	{
		try {
		
			$input_SenderId = $authentication['SenderId'][0];
			$input_Password = $authentication['Password'][0];
			$input_Timestamp = $authentication['Timestamp'][0];
					
			if( strcmp($this->SenderId, $input_SenderId) == 0 && strcmp($this->Password, $input_Password) == 0 ) {
				
				return 'Success';
				
			} else {
				
				return 'Auth Failed: Invalid credentials [ SenderID: ' . $input_SenderId . ' Password: ' . $input_Password . ']';
				
			}
			
		} catch(Exception $e) {
			
			return 'Failed|' . $e->getMessage();
			
		}

	}

	
	/*
	 *
	 * Send POST Request
	 *
	 */
	private function POST($url, $data) {
	
		try {
			
			$ch = curl_init();
			
			$post_fields = "";
			
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);

			// In real life you should use something like:
			 curl_setopt($ch, CURLOPT_POSTFIELDS, 
					  http_build_query($data));

			// Receive server response ...
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$server_output = curl_exec($ch);

			curl_close ($ch);

			return $server_output;
			
			
		} catch(Exception $e) {
			
			
		}
	
	}
	
	/*
	 *
	 * Update Authentication Details In api_authentication Table
	 *
	 */
	private function SetAuthenticationCredentials() {
		if(!DB::table('api_authentication')->where('type', 'payzone')->where('senderId', $this->SenderId)->where('password', $this->Password)->first()) {
				DB::table('api_authentication')->truncate();
				DB::table('api_authentication')->insert(['type' => 'payzone', 'senderId' => $this->SenderId, 'password' => $this->Password]);
		}
		else
			echo '';
	}
	
	/*
	 *
	 * Create api_authentication Table if it does nto exist
	 *
	 */
	private function AuthenticationTableExists($drop = false) {
		
		if($drop) {
			Schema::dropIfExists('api_authentication');
		}
		
		if (Schema::hasTable('api_authentication')) {
			return true;
		}	
		
		Schema::create('api_authentication', function($table)
		{
			$table->increments('id');
			$table->string('type', 50);
			$table->integer('senderId')->unsigned();
			$table->string('password', 100);
			
		});
		
	}
	
	/*
	 *
	 * Create payments_storage_archived Table if it does not exist
	 *
	 */
	private function ArchivedTableExists($drop = false) {
		
		if($drop) {
			Schema::dropIfExists('payments_storage_archived');
		}
		
		if (Schema::hasTable('payments_storage_archived')) {
			return true;
		}	
		
		Schema::create('payments_storage_archived', function($table)
		{
			$table->increments('id');
			$table->string('archived_reason', 50);
			$table->string('ref_number', 50);
			$table->integer('customer_id')->unsigned();
			$table->string('barcode', 50);
			$table->dateTime('time_date');
			$table->string('currency_code', 20);
			$table->double('amount');
			$table->double('transaction_fee');
			$table->string('acceptor_name_location_', 100);
			$table->tinyInteger('payment_received')->unsigned();
			$table->date('settlement_date');
			$table->integer('merchant_type')->unsigned();
			$table->integer('POS_entry_mode')->unsigned();
			$table->boolean('test');
			$table->dateTime('time_date_archived');
		});
		
	}

	/*
	 *
	 * Log an error
	 *
	 *
	 */
	private function LogPaymentError($customer_id, $message) {
		
		
		DB::table('test_payments_storage_errors')->insert([
			
			'customer_id' => $customer_id,
			'message' => $message
		
		]);
		
	}
	
}