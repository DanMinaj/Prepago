<?php

class UserSettingsController extends BaseController {

	protected $layout = 'layouts.admin_website';

	public function signins()
	{	
		$signins = UserSignIn::where('operator_id', Auth::user()->id)->get();
		
		try {
			foreach($signins as $s) {
				$s->info = $this->ip_info($s->IP, "Address");
			}
		} catch(Exception $e) {}
		
		$this->layout->page = View::make('settings/signins', [
			'signins' => $signins,
		]);
	}
	
	public function change_username()
	{
		$this->layout->page = View::make('settings/change_username');
	}

	public function change_admin_username($username)
	{
		User::where('id', '=', Auth::user()->id)->update(array('username' => $username));

		Auth::logout();
		return Redirect::to('/');
	}

	public function change_password()
	{
		$this->layout->page = View::make('settings/change_password');
	}

	public function change_admin_password($password)
	{
		User::where('id', '=', Auth::user()->id)->update(array('password' => Hash::make($password)));

		Auth::logout();
		return Redirect::to('/');
	}
	
	private function ip_info($ip = NULL, $purpose = "location", $deep_detect = TRUE) {
		$output = NULL;
		try {
			if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
				$ip = $_SERVER["REMOTE_ADDR"];
				if ($deep_detect) {
					if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
						$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
					if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
						$ip = $_SERVER['HTTP_CLIENT_IP'];
				}
			}
			$purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
			$support    = array("country", "countrycode", "state", "region", "city", "location", "address");
			$continents = array(
				"AF" => "Africa",
				"AN" => "Antarctica",
				"AS" => "Asia",
				"EU" => "Europe",
				"OC" => "Australia (Oceania)",
				"NA" => "North America",
				"SA" => "South America"
			);
			if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
				$ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
				if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
					switch ($purpose) {
						case "location":
							$output = array(
								"city"           => @$ipdat->geoplugin_city,
								"state"          => @$ipdat->geoplugin_regionName,
								"country"        => @$ipdat->geoplugin_countryName,
								"country_code"   => @$ipdat->geoplugin_countryCode,
								"continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
								"continent_code" => @$ipdat->geoplugin_continentCode
							);
							break;
						case "address":
						
							$address = array($ipdat->geoplugin_countryName);
							if (@strlen($ipdat->geoplugin_regionName) >= 1)
								$address[] = $ipdat->geoplugin_regionName;
							if (@strlen($ipdat->geoplugin_city) >= 1)
								$address[] = $ipdat->geoplugin_city;
							$output = implode(", ", array_reverse($address));
							
							break;
						case "city":
							$output = @$ipdat->geoplugin_city;
							break;
						case "state":
							$output = @$ipdat->geoplugin_regionName;
							break;
						case "region":
							$output = @$ipdat->geoplugin_regionName;
							break;
						case "country":
							$output = @$ipdat->geoplugin_countryName;
							break;
						case "countrycode":
							$output = @$ipdat->geoplugin_countryCode;
							break;
					}
				}
			}
		} catch(Exception $e) {
			
		}
		
		return $output;
	}


}