<?php

class DataLoggerController extends BaseController {

	protected $layout = 'layouts.admin_website';

	/**
	 * Display a test UI
	 * Testing all of the data loggers
	 * @return View
	 */
	public function test()
	{
		return View::make('datalogger.testui');
	}

	
	public function index()
	{
		$scheme_ids = [];
		$type = 'read';
		$running = false;
		
		if(Input::has('s')) {
			$scheme_ids = explode(',', Input::get('s'));
			$running = true;
		}
		
		if(Input::has('type')) {
			$type = Input::get('type');
		}
		
		$dataloggers = DataLogger::
		whereIn('scheme_number', $scheme_ids)
		->get();
		
		$this->layout->page = View::make('home/datalogger',
		[
			'dataloggers' => $dataloggers,
			'scheme_ids' => $scheme_ids,
			'running' 	=> $running,
			'type' 		=> $type,
		]);
		
	}
	
	public function get_dataloggers()
	{
		$scheme_ids = Input::get('scheme_ids');
		
		$data_loggers = DataLogger::
		whereIn('scheme_number', $scheme_ids)
		/*->orWhere('scheme_number', 25)*/
		->get();
		
		return Response::json(['data_loggers' => $data_loggers]);
	}
	
	public function get_meters($data_logger_id)
	{
		
		$pmd = PermanentMeterData::where('data_logger_id', $data_logger_id)->get();
		
		foreach($pmd as $p) {
			
			$dhm =  DistrictHeatingMeter::where('meter_number', $p->meter_number)->first();
			
			if($dhm) 
				$p->dhm = $dhm;
			else
				$p->dhm = null;	

			
			$p->balance = ($p->customer) ? ("&euro;" . number_format($p->customer->balance, 2)) : null;
		}
		
		return $pmd;
	}

	public function meter_information_upload()
	{
		$json = file_get_contents('php://input');
		$obj = json_decode($json);
		
		try{
			$scheme = Scheme::where("pi_ID", "=", $obj->ID)->get()->first();

			$pmd = new PermanentMeterData;
			$pmd->scheme_number = ($scheme) ? $scheme->scheme_number : 0;
			$pmd->meter_type = $obj->Meters->meter_type;
			$pmd->meter_number = $obj->Meters->meter_number;
			$pmd->install_date = $obj->Meters->install_date;
			$pmd->scu_type = $obj->Meters->scu_type;
			$pmd->scu_number = $obj->Meters->scu_number;
			$pmd->scu_port = $obj->Meters->scu_port;
			$pmd->in_use = (isset($obj->Meters->in_use)) ? $obj->Meters->in_use : 0;
			$pmd->shut_off = (isset($obj->Meters->shut_off)) ? $obj->Meters->shut_off : 0;
			$pmd->is_boiler_room_meter = (isset($obj->Meters->is_boiler_room_meter)) ? $obj->Meters->is_boiler_room_meter : 0;
			$pmd->meter_make = $obj->Meters->meter_make;
			$pmd->meter_model = $obj->Meters->meter_model;
			$pmd->meter_manufacturer = $obj->Meters->meter_manufacturer;
			$pmd->meter_baud_rate = $obj->Meters->meter_baud_rate;
			$pmd->HIU_make = $obj->Meters->HIU_make;
			$pmd->HIU_model = $obj->Meters->HIU_model;
			$pmd->HIU_manufacturer = $obj->Meters->HIU_manufacturer;
			$pmd->valve_make = $obj->Meters->valve_make;
			$pmd->valve_model = $obj->Meters->valve_model;
			$pmd->valve_manufacturer = $obj->Meters->valve_manufacturer;
			$pmd->save();

			return "Successful";
		}catch(Exception $e){
			return "Unsuccessful";
		}
	
	}

}