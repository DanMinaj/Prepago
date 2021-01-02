<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class SystemController extends BaseController {

	protected $layout = 'layouts.admin_website';
	
	
	public function system_monitor($date = null)
	{
		
		ini_set('memory_limit','-1');
		
		
		
		if($date == null)
			$date = date('Y-m-d');
		
		
		$us = DistrictHeatingUsage::where('date', $date)->where('standing_charge', '<=', 0)->get();
		$uninserted_s = [];
		$uninserted_s_customers = [];
		
		
		foreach($us as $key => $u) {
			
			ob_start();
			
			$scheme_number = $u->scheme_number;
			$tariff = Tariff::where('scheme_number', $scheme_number)->first();
			if(!$tariff) {
				continue;
				unset($us[$key]);
			}
			
			if($tariff->tariff_2 == 0) {
				continue;
				unset($us[$key]);
			}
			
			$customer = Customer::find($u->customer_id);
			if(!$customer) {
				continue;
				unset($us[$key]);
			}
			
			if($customer->commencement_date >= $date)
			{
				continue;
				unset($us[$key]);
			}
			
			$uninserted_s[] = $u;
			$uninserted_s_customers[] = $customer;
			
			ob_end_flush();
			
		}
		
		/*
		foreach($uninserted_s_customers as $c) {
			
			echo $c->username . ':' . $c->id . '<br/>';
			
		}*/
		
		$customers = Customer::where('status', 1)->get();
		
		
		$other_charges = 0;
		$other_charges_customers = [];
		
		$inconsistent_usage = 0;
		$inconsistent_usage_customers = [];
		
		$duplicate_dhu = 0;
		$duplicate_dhu_customers = [];
		
		$problems_found = 0;
		
		foreach($customers as $c) {
			
			ob_start();
			
			$dhu = DistrictHeatingUsage::where('customer_id', $c->id)->where('date', $date)->first();
			if(!$dhu)
				continue;
			
			$other_charge = ($dhu->cost_of_day) - ($dhu->unit_charge + $dhu->standing_charge + $dhu->arrears_repayment);
		
			if($other_charge > 0.001) {
				$other_charges += $other_charge;
				$other_charges_customers[] = $c;
			}
			
			if( ($dhu->end_day_reading - $dhu->start_day_reading) != $dhu->total_usage ) {
				$inconsistent_usage++;
				$inconsistent_usage_customers[] = $c;
			}
			
			if(DistrictHeatingUsage::where('customer_id', $c->id)->where('date', $date)->count() > 1) {
				$duplicate_dhu++;
				$duplicate_dhu_customers[] = $c;
			}
			
			ob_end_flush();
		}
		
		$schemes = Scheme::where('archived', 0)->where('status_debug', 0)->whereRaw('(id != 23 && id != 24 && id != 15)')->get();
		$non_readings = 0;
		$non_readings_schemes = [];
		
		/*
		foreach($schemes as $s) {
			
			ob_start();
			
			if(DB::table('permanent_meter_data_readings_all')->whereRaw("scheme_number = '" . $s->id . "'")->count() == 0)
			{
				$non_readings++;
			}
			
			ob_end_flush();
			
		}*/
		
		if(count($uninserted_s) > 0) {
			$problems_found++;
		}
		if($other_charges > 0.001) {
			$problems_found++;
		}
		if($inconsistent_usage > 0) {
			$problems_found++;
		}
		if($duplicate_dhu > 0) {
			$problems_found++;
		}
		if($non_readings > 0) {
			$problems_found++;
		}
			
		/*
		foreach($duplicate_dhu_customers as $c) {
			
			echo "<a href='https://www.prepago-admin.biz/customer_tabview_controller/show/" . $c->id . "'>" . $c->username . '</a>:' . $c->id . '<br/>';
			
		}
		*/
		
		$this->layout->page = View::make('home.system_monitor', [
			
			'uninserted_s' => $uninserted_s,
			'other_charges' => $other_charges,
			'inconsistent_usage' => $inconsistent_usage,
			'duplicate_dhu' => $duplicate_dhu,
			'problems_found' => $problems_found,
			'non_readings' => $non_readings,
			'non_readings_schemes' => $non_readings_schemes,
			'date' => $date,
			
		]);
	}
}