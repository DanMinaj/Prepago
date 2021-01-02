<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CarlinnDailyReport extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'carlinn:daily-report';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send daily report to Carlinn.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$data = [];

		$data['warningResults'] = DistrictHeatingMeter::with('customers')
									->whereRaw("sudo_reading - latest_reading > 50 and sudo_reading - latest_reading <= 100")
									->where('scheme_number', 3)
									->get();

		$data['errorResults'] = DistrictHeatingMeter::with('customers')
									->whereRaw("sudo_reading - latest_reading > 100 and sudo_reading - latest_reading <= 200")
									->where('scheme_number', 3)
									->get();

		$data['criticalResults'] = DistrictHeatingMeter::with('customers')
									->whereRaw("sudo_reading - latest_reading > 200")
									->where('scheme_number', 3)
									->get();

		if ( ! $data['warningResults']->count() && ! $data['errorResults']->count() && ! $data['criticalResults']->count())
		{
			return;
		}

		return Mail::send('emails.carlinn_daily_report', $data, function($message) {
			$message->from('aidan@prepago.ie')
					->subject('Critical: Carlinn Hard Reset Alert')
					->to(['kcranny@deniswilliams.ie', 'aidan@prepago.ie', 'mariana.bozduganova@gmail.com']);
		});
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
