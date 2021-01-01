<?php

class ReportSchedule extends Eloquent{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'scheduled_pdf_reports';
	
	public static $path = "/var/www/html/reports";
	
	public static function prepare($type, $schemes, $run_data = []) {
		
		
		$next_run_id = ReportSchedule::orderBy('run_id', 'DESC')->first();
		if(!$next_run_id) {
			$next_run_id = 1;
		} else {
			$next_run_id = $next_run_id->run_id + 1;
		}
		
		$start = null;
		
		foreach($schemes as $k => $v) {		
			$report_schedule = new ReportSchedule();
			$report_schedule->type = $type;
			$report_schedule->scheme_number = $v;
			$report_schedule->run_data = serialize($run_data);
			$report_schedule->is_first = ($k == 0);
			$report_schedule->is_last = ($k == (count($schemes)-1));
			$report_schedule->run_id = $next_run_id;
			$report_schedule->save();
			$report_schedule->next_id = $report_schedule->id + 1;
			$report_schedule->save();
			
			if($start == null)
				$start = $report_schedule;
		}
		
		return $start;
		
	}
	
	public function getProgress()
	{
		$run_data = unserialize($this->run_data);
		$iterated = false;
		$error = '';
		
		try {
			
			Artisan::call('report:schedule');
			$iterated = true;
			
		} catch(Exception $e) {
			$iterated = false;
			$error = $e->getMessage() . ': ' . $e->getLine();
		}
		
		return Response::json([
			'iterated' => $iterated,
			'completion' => ((count($this->iterations(true)) / count($this->iterations()))*100),
			'dl' => 'https://prepagoplatform.com/' . str_replace('/var/www/html/', '', ReportSchedule::$path)
			. '/' . $run_data->folder . '/' . $this->type . '.zip',
			'error' => $error,
		]);
	}

	public function iterations($completed = false) {
		if(!$completed) {
			return ReportSchedule::where('run_id', $this->run_id)->orderBy('id', 'ASC')->get();
		} else {
			return ReportSchedule::where('run_id', $this->run_id)
			->where('it_completed', 1)->orderBy('id', 'ASC')->get();
		}
	}
	
	public function nextIteration() {
		if(!$this->it_completed)
				return $this;
		return ReportSchedule::where('run_id', $this->run_id)
		->where('it_completed', 0)->orderBy('id', 'ASC')->first();
	}
	
	public function isComplete() {
		return ($this->nextIteration() == null);
	}
	
	public function markComplete() {
		foreach($this->iterations() as $k => $iteration) {
			$iteration->all_completed = 1;
			$iteration->save();
		}
		$run_data = unserialize($iteration->run_data);
		$path = ReportSchedule::$path . '/' . $run_data->folder;
		$out = $path . '/' . $this->type . '.zip';
		exec('zip -r -j ' . $out . ' ' . $path . '/*');
	}

}