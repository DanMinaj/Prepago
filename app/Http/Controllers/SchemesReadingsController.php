<?php

class SchemesReadingsController extends BaseController {

    public function index()
    {
        $schemes = Scheme::where('schemes.archived', 0)->get();

        $layout = View::make('layouts.admin_website');
        $layout->page = View::make('home/schemes-readings', [
            'schemes' => $schemes
        ]);

        return $layout;
    }
	
	public function carlinn()
    {
        ini_set('max_execution_time', 1800);

        $readings = PermanentMeterDataReadingsAll
                            ::leftJoin('permanent_meter_data', 'permanent_meter_data_readings_all.permanent_meter_id', '=', 'permanent_meter_data.ID')
                            ->where('permanent_meter_data_readings_all.scheme_number', 3)
                            ->where('time_date', 'LIKE', '2016-09-30%')
                            ->get();

        $layout = View::make('layouts.admin_website');
        $layout->page = View::make('carlinn-readings-2016-09-30', [
            'readings' => $readings
        ]);

        return $layout;
    }

    public function export($schemeNumber)
    {
		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', 0);		
		
		$data = '';
        $data .= 'permanent_meter_id,';
        $data .= 'meter_number,';
        $data .= 'customer_id,';
        $data .= 'customer_username,';
        $data .= 'scheme_name,';
        $data .= "\n";
		
		$readings = PermanentMeterDataReadingsAll::select('scheme_number', 'permanent_meter_id')->where('scheme_number', $schemeNumber)->get();
		foreach ($readings as $reading) {
			var_dump($reading->permanent_meter_id);
			/*if (!$permanentMeter = $reading->permanentMeterData) {
				$customer = null;
			}
			else {
				$dhm = $permanentMeter->districtHeatingMeters;
				$customer = $dhm && $dhm->first() ? $dhm->first()->customers : null;
			}
			
			$data .= $reading->permanent_meter_id.',';
            $data .= $permanentMeter ? $permanentMeter->permanent_meter_number : '' .',';
            $data .= $customer ? $customer->id : '' .',';
            $data .= $customer ? $customer->username : '' .',';
            $data .= $reading->scheme ? $reading->scheme->scheme_name : '' .',';
            $data .= "\n";*/
		}
		dd();
		
        $readings = PermanentMeterDataReadingsAll
                                            ::leftJoin('permanent_meter_data', 'permanent_meter_data_readings_all.permanent_meter_id', '=', 'permanent_meter_data.ID')
                                            ->leftJoin('district_heating_meters', 'district_heating_meters.permanent_meter_ID', '=', 'permanent_meter_data.ID')
                                            ->leftJoin('customers', 'customers.meter_ID', '=', 'district_heating_meters.meter_ID')
                                            ->leftjoin('schemes', 'schemes.scheme_number', '=', 'permanent_meter_data.scheme_number')
                                            ->select(
                                                'permanent_meter_data.ID as permanent_meter_id',
                                                'permanent_meter_data.meter_number as permanent_meter_number',
                                                'customers.id as customer_id',
                                                'customers.username as customer_username',
                                                'schemes.company_name as scheme_name'
                                            )
                                            ->where('schemes.scheme_number', $schemeNumber)
                                            ->get();
											dd('asdasdasd');

        

        foreach($readings as $reading){
            
        }

        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=scheme_readings.csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        print $data;

    }

}