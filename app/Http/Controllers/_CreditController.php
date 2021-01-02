<?php

class CreditController extends BaseController {

	protected $layout = 'layouts.admin_website';

	public function issue_credit()
    {
        $customers = DB::table('customers')
        ->select(DB::raw('id,first_name,surname,username,barcode,email_address,mobile_number'))
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->get();

        $this->layout->page = View::make('home/issue_credit', array('customers' => $customers));
    }

    public function ic_search_customers()
    {
    	$search_key = Input::get('search_box');

    	$customers = DB::table('customers')
        ->select(DB::raw('id,first_name,surname,username,barcode,email_address,mobile_number'))
        ->where('scheme_number', '=', Auth::user()->scheme_number)
		->where('username', 'like', '%'.$search_key.'%')
		->orWhere('first_name', 'like', '%'.$search_key.'%')
		->orWhere('barcode', 'like', '%'.$search_key.'%')
		->orWhere('surname', 'like', '%'.$search_key.'%')
		->orWhere('street1', 'like', '%'.$search_key.'%')
		->orWhere('street2', 'like', '%'.$search_key.'%')
		->orWhere('email_address', 'like', '%'.$search_key.'%')
		->orWhere('mobile_number', 'like', '%'.$search_key.'%')
		->orWhere('town', 'like', '%'.$search_key.'%')
		->orWhere('county', 'like', '%'.$search_key.'%')
		->orWhere('nominated_telephone', 'like', '%'.$search_key.'%')
		->get();

		$this->layout->page = View::make('home/issue_credit', array('customers' => $customers));
    }

    public function add_creditlist($customer_id, $customer_email)
    {
        if(!Session::has('credit_list')){
            $credit_list[0]['id'] = $customer_id;
            $credit_list[0]['email'] = $customer_email;
            Session::put('credit_list', $credit_list);
            return Redirect::to('issue_credit');
        }else{
            $credit_list = Session::get('credit_list');
            $keytracker = 0;
            foreach ($credit_list as $k => $v){
                $new_credit_list[$keytracker]['id'] = $v['id'];
                $new_credit_list[$keytracker]['email'] = $v['email'];

                $keytracker++;
            }
            $new_credit_list[$keytracker]['id'] = $customer_id;
            $new_credit_list[$keytracker]['email'] = $customer_email;
            Session::put('credit_list', $new_credit_list);
            return Redirect::to('issue_credit');
        }
    }

    public function rem_creditlist($customer_id)
    {
        $credit_list = Session::get('credit_list');
        $keytracker = 0;
        foreach ($credit_list as $k => $v){
            if($v['id'] != $customer_id){
                $new_credit_list[$keytracker]['id'] = $v['id'];
                $new_credit_list[$keytracker]['email'] = $v['email'];
                $keytracker++;
            }
        }
        if(empty($new_credit_list)){
        	Session::forget('credit_list');
        }else{
        	Session::put('credit_list', $new_credit_list);
        }
        return Redirect::to('issue_credit');
    }

    public function check_login($password)
    {
        if(Auth::validate(array('username' => Auth::user()->username, 'password' => $password))){
            return 'valid';
        }else{
            return 'invalid';
        }
    }

    public function add_amount($amount, $reason)
    {

    	$sms_list = Session::get('credit_list');
    	foreach ($sms_list as $k => $v){

    		$aic = new AdminIssuedCredit();
    		$aic->customer_id = $v['id']; 
    		$aic->scheme_number = Auth::user()->scheme_number; 
    		$aic->date_time = date('Y-m-d'); 
    		$aic->admin_name = Auth::user()->username; 
    		$aic->amount = $amount; 
    		$aic->reason = $reason; 
    		$aic->save();

    		$customer = Customer::where('id', '=', $v['id'])->get()->first();
    		$customer->balance = $customer->balance + $amount;
	        
	        if( ( $customer->balance > 0 ) && ( $customer->shut_off == 1 ) ){
	            
	            $meter_id = $customer->meter_ID;

	            $customer->shut_off = 0;
	            $customer->shut_off_command_sent = 0;
	            $customer->credit_warning_sent = 0;
	            $customer->IOU_available = 0;
	            $customer->IOU_used = 0;
	            $customer->IOU_extra_used = 0;
	            
	            DistricHeatingMeters::where('meter_ID', '=', $meter_id)->update(array('scheduled_to_shut_off' => 0, 'shut_off_device_status' => 0));
	        }

	        $customer->save();

	        $rtu = Customer::join('district_heating_meters', 'customers.meter_ID', '=', 'district_heating_meters.meter_ID')
	        					->where('customers.id', '=', $v['id'])
	        					->get()->first();
			
			$scu_type = $rtu['scu_type'];

        	//check which type of RTU is being used
    		if($scu_type == "a" || $scu_type == "d"){

    			$rtuCommandQue = new RTUCommandQue();
    			$rtuCommandQue->customer_ID = $v['id'];
    			$rtuCommandQue->meter_id = $scu_type['meter_ID'];
    			$rtuCommandQue->turn_service_on = 1;
    			$rtuCommandQue->shut_off_device_contact_number = $scu_type['shut_off_device_contact_number'];
    			$rtuCommandQue->port = $scu_type['port'];
    			$rtuCommandQue->save();

    		}else if($scu_type == "b"){
            //code to do
    		}else if($scu_type == "c"){
            //code to do
    		}else{
				/* DEACTIVATE SMS BY COMMENTING OUT LINES 125 - 144 */
				$schemes = Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->get()->first();

				$password= $schemes['sms_password'];
				$details_url ='http://localhost/prepago_sms/index.php/prepago_system/meter_pin_message/' . $v['id'] . '/' . Auth::user()->scheme_number . '/' . $password;

				$options = Array(
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
			}

		}

		return Redirect::to('issue_credit');
	}


	public function issue_admin_iou()
	{
		$this->layout->page = View::make('home/issue_admin_iou');
	}

	public function iai_search_customers()
	{

		$search_key = Input::get('search_box');

    	$customers = DB::table('customers')
        ->select(DB::raw('id,first_name,surname,username,barcode,email_address,mobile_number'))
        ->where('scheme_number', '=', Auth::user()->scheme_number)
		->where('username', 'like', '%'.$search_key.'%')
		->orWhere('first_name', 'like', '%'.$search_key.'%')
		->orWhere('barcode', 'like', '%'.$search_key.'%')
		->orWhere('surname', 'like', '%'.$search_key.'%')
		->orWhere('street1', 'like', '%'.$search_key.'%')
		->orWhere('street2', 'like', '%'.$search_key.'%')
		->orWhere('email_address', 'like', '%'.$search_key.'%')
		->orWhere('mobile_number', 'like', '%'.$search_key.'%')
		->orWhere('town', 'like', '%'.$search_key.'%')
		->orWhere('county', 'like', '%'.$search_key.'%')
		->orWhere('nominated_telephone', 'like', '%'.$search_key.'%')
		->get();

		$this->layout->page = View::make('home/issue_admin_iou_search_view', array('customers' => $customers));
	}

	public function issue_admin_iou_amount($customer_id)
	{
		$this->layout->page = View::make('home/issue_admin_iou_amount_view', array('customer_id' => $customer_id));
	}

	public function iai_add_amount($customer_id, $amount, $reason)
	{

		$customer = Customer::where('id', '=', $customer_id)->get()->first();
    	$customer->admin_IOU_amount = $customer->admin_IOU_amount + $amount;
    	$customer->admin_IOU_in_use = 1;
       
        if( ( $customer->balance > ( 0 - $customer->admin_IOU_amount ) ) && ( $customer->shut_off == 1) ){
            
            $meter_id = $customer->meter_ID;

            $customer->shut_off = 0;
            $customer->shut_off_command_sent = 0;
            $customer->IOU_available = 0;
            $customer->IOU_used = 0;
            $customer->IOU_extra_used = 0;

            DistricHeatingMeters::where('meter_ID', '=', $meter_id)->update(array('scheduled_to_shut_off' => 0, 'shut_off_device_status' => 0));
        }

        $customer->save();

        $rtu = Customer::join('district_heating_meters', 'customers.meter_ID', '=', 'district_heating_meters.meter_ID')
	        					->where('customers.id', '=', $customer_id)
	        					->get()->first();
			
		$scu_type = $rtu['scu_type'];

        	//check which type of RTU is being used
		if($scu_type == "a" || $scu_type == "d"){

			$rtuCommandQue = new RTUCommandQue();
			$rtuCommandQue->customer_ID = $customer_id;
			$rtuCommandQue->meter_id = $scu_type['meter_ID'];
			$rtuCommandQue->turn_service_on = 1;
			$rtuCommandQue->shut_off_device_contact_number = $scu_type['shut_off_device_contact_number'];
			$rtuCommandQue->port = $scu_type['port'];
			$rtuCommandQue->save();

		}else if($scu_type == "b"){
        //code to do
		}else if($scu_type == "c"){
        //code to do
		}else{

            /* DEACTIVATE SMS BY COMMENTING OUT LINES 82 - 103 */
            $password = $this->issue_admin_iou_model->get_sms_password($this->session->userdata('scheme_number'));
            $details_url = 'http://localhost/prepago_sms/index.php/prepago_system/meter_pin_message/' . $customer['id'] . '/' . $this->session->userdata('scheme_number') . '/' . $password;

            $options = Array(
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
	    }

	    return Redirect::to('issue_admin_iou');
	}

////////////////////////////////////////////////

	public function issue_topup_arrears()
	{
		$this->layout->page = View::make('home/issue_topup_arrears');
	}

	public function ita_search_customers()
	{
		$search_key = Input::get('search_box');

    	$customers = DB::table('customers')
        ->select(DB::raw('id,first_name,surname,barcode,email_address,mobile_number'))
        ->where('scheme_number', '=', Auth::user()->scheme_number)
		->where('username', 'like', '%'.$search_key.'%')
		->orWhere('first_name', 'like', '%'.$search_key.'%')
		->orWhere('barcode', 'like', '%'.$search_key.'%')
		->orWhere('surname', 'like', '%'.$search_key.'%')
		->orWhere('street1', 'like', '%'.$search_key.'%')
		->orWhere('street2', 'like', '%'.$search_key.'%')
		->orWhere('email_address', 'like', '%'.$search_key.'%')
		->orWhere('mobile_number', 'like', '%'.$search_key.'%')
		->orWhere('town', 'like', '%'.$search_key.'%')
		->orWhere('county', 'like', '%'.$search_key.'%')
		->orWhere('nominated_telephone', 'like', '%'.$search_key.'%')
		->get();

		$this->layout->page = View::make('home/issue_topup_arrears_search_view', array('customers' => $customers));
	}

	public function issue_topup_arrears_amount($customer_id)
	{
		$this->layout->page = View::make('home/issue_topup_arrears_amount_view', array('customer_id' => $customer_id));
	}

	public function ita_add_amount($customer_id, $amount, $reason)
	{

        $customer = Customer::where('id', '=', $customer_id)->get()->first();
    	$customer->balance = $customer->balance + $amount;
    	$customer->arrears = $customer->arrears + $amount;
    	$customer->arrears_daily_repayment = $reason;
        
    	$customerArrears = new CustomerArrears();
        $customerArrears->customer_id =  $customer['id'];
        $customerArrears->scheme_number =  $this->session->userdata('scheme_number');
        $customerArrears->amount =  $customer['amount'];
        $customerArrears->repayment_amount =  $customer['reason'];
        $customerArrears->date =  date('Y-m-d');
        $customerArrears->save();
        
        if( ( $customer->balance ) && ( $customer->shut_off == 1 )){
            
            $meter_id = $customer->meter_ID;

            $customer->shut_off = 0;
            $customer->shut_off_command_sent = 0;
            $customer->credit_warning_sent = 0;
            $customer->IOU_available = 0;
            $customer->IOU_used = 0;
            $customer->IOU_extra_used = 0;

            DistricHeatingMeters::where('meter_ID', '=', $meter_id)->update(array('scheduled_to_shut_off' => 0, 'shut_off_device_status' => 0));
        }

        $customer->save();

	    return Redirect::to('issue_topup_arrears');
	}

}