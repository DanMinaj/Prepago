<?php

class PaypalAdminController extends BaseController {

	protected $layout = 'layouts.admin_website';
	
	public function payments()
	{
		
		
		$from = date('Y-m-d');
		$to = date('Y-m-d');
		
		
		$this->layout->page = View::make('report.paypal.payments', [
			'from' => $from,
			'to' => $to,	
		]);
		
	}
	
	public function getbalances_ajax()
	{
	
		$paypal = new Paypal('accounts');
		$response1 = $paypal->call('GetBalance');
		
		$paypal = new Paypal('noreply');
		$response2 = $paypal->call('GetBalance');

		
		return Response::json([
			'bal1' => '&euro;' . $response1['L_AMT0'],
			'bal2' => '&euro;' . $response2['L_AMT0'],
		]);
		
	}
	
	public function payments_ajax()
	{
		
		$from = Input::get('from') . ' 00:00:00';
		$to = Input::get('to') . ' 23:59:59';
		$count = Input::get('results');

		$response =  Paypal::getPaymentsNew($from, $to);

		return Response::json($response);
		
	}
	
	public function generateTopup()
	{
			
		
		$this->layout->page = View::make("", [
		
		
		
		]);
		
	}
	
	public function disputes()
	{
		
	}
	
	public function settings()
	{
		
		$settings = SystemSetting::where('type', 'paypal')->get();
		
		
		$this->layout->page = View::make('settings/paypal', ['settings' => $settings]);
		
		
	}
	
	public function settings_add()
	{
		
		$setting = SystemSetting::where('type', 'paypal')->where('name', Input::get('name'))->first();
		
		if($setting)
			return Redirect::back()->with('errorMessage', "That setting does already exists.");
		
		$new_setting = new SystemSetting();
		$new_setting->type = 'paypal';
		$new_setting->name = Input::get('name');
		$new_setting->value = Input::get('value');
		$new_setting->save();
		
		return Redirect::back()->with('successMessage', "Successfully created new Paypal setting '" . $new_setting->name . "'");
		
		
	}
	
	
	public function settings_remove($id)
	{
		
		$setting = SystemSetting::where('id', $id)->first();
		$setting_copy_name = $setting->name;
		
		if($setting)
			$setting->delete();
		
		
		
		return Redirect::back()->with('successMessage', "Successfully removed Paypal setting '$setting_copy_name'");
		
		
	}
	
	
	public function settings_save($id)
	{
	
		$setting = SystemSetting::where('id', $id)->first();
		$setting_copy_name = $setting->name;
		
		if(!$setting)
			return Redirect::back()->with('errorMessage', "That Paypal setting does not exist.");
		
		$new_name = Input::get('name');
		$new_value = Input::get('value');
		
		$setting->name = $new_name;
		$setting->value = $new_value;
		$setting->save();
		
		return Redirect::back()->with('successMessage', "Successfully saved settings.");
		
		
	}
	
	
}