<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ZeroStartDayReadings extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'dhu:zero-start-day';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Take a manual meter reading if the start_day_reading in district_heating_usage was "0".';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->log = new Logger('Automated Meters Readings');
		$this->log->pushHandler(new StreamHandler(__DIR__ . '/ZeroStartDayReadings/' . date('Y-m-d') . '.log'), Logger::INFO);
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$zeroStartDayReadings = DistrictHeatingUsage::where('start_day_reading', 0)
								->where('date', '>', \Carbon\Carbon::now()->subDay())
								->get();

		foreach ($zeroStartDayReadings as $zeroStartDayReading) {
			$this->log->addInfo('Perform manual reading for dhu entry', ['dhu_entry' => $zeroStartDayReading->id]);
			$customerWithZeroStartDayReading = Customer::find($zeroStartDayReading->customer_id);
			if ( ! $customerWithZeroStartDayReading->districtHeatingMeter || ! $customerWithZeroStartDayReading->permanentMeter()) {
				$this->log->addInfo('Customer does not have a district heating meter or a permanent meter', ['customer_id' => $customerWithZeroStartDayReading->id]);
				continue;
			}
			$this->log->addInfo('Manual Reading started for customer', ['customer_id' => $customerWithZeroStartDayReading->id]);
			$permanentMeter = $customerWithZeroStartDayReading->permanentMeter();
			$scheme = Scheme::where('scheme_number', '=', $zeroStartDayReading->scheme_number)->first();
			$permanentMeter->performManualReading($zeroStartDayReading->scheme_number, $scheme->prefix);
			$this->log->addInfo('Manual Reading performed for permanent meter', ['pm_id' => $permanentMeter->ID]);
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [];
	}

}
