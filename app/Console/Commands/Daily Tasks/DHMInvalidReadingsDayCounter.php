<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class DHMInvalidReadingsDayCounter extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'dhm:invalid-readings-day-counter';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Dealing with the DHM 200 kWh abandon read and stop updating trap used in the charging calculator functionality.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
        $this->log = new Logger('DHM Invalid readings day counter log');
		$this->log->pushHandler(new StreamHandler(__DIR__ . '/DHMInvalidReadingsDayCounter/' . date('Y-m-d') . '.log'), Logger::INFO);
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
        $dhm = DistrictHeatingMeter::join('schemes', 'schemes.scheme_number', '=', 'district_heating_meters.scheme_number')
                                    ->where('schemes.archived', 0)
                                    ->whereRaw('sudo_reading - latest_reading > 200')
                                    ->get();

        foreach ($dhm as $dhmReading) {
            $this->log->addInfo('Setting the invalid reading days counter for meter with number ' . $dhmReading->meter_number);
            if ($dhmReading->invalid_reading_days_counter >= 4) {
                $this->log->addInfo('The invalid reading days counter for meter with number ' . $dhmReading->meter_number . ' is >= 4');
                $dhmReading->latest_reading = $dhmReading->sudo_reading;
                $dhmReading->invalid_reading_days_counter = 0;
            }
            else {
                $this->log->addInfo('The invalid reading days counter for meter with number ' . $dhmReading->meter_number . ' is < 4');
                $dhmReading->invalid_reading_days_counter++;
            }

            $this->log->addInfo('The DHM reading after the changes is: ', $dhmReading->toArray());

            $dhmReading->save();
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
