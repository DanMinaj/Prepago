	<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ResendOpenValveCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'open-valve:resend';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Resend open valve command for "m" type meters.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->log = new Logger('Shut off reset log');
		$this->log->pushHandler(new StreamHandler(storage_path('logs/resend_open_valve_command.log'), Logger::INFO));
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		/*
		$openValveCommandsForThePast10Minutes = RTUCommandQue::where('turn_service_on', 1)
												->where('time_date', '>', \Carbon\Carbon::now()->subMinutes(30))
												->where('resent', 0)
												->get();

		foreach ($openValveCommandsForThePast10Minutes as $openValveCommand) {
			$permanentMeter = PermanentMeterData::find($openValveCommand->permanent_meter_id);
			if ( ! $permanentMeter || $permanentMeter->scu_type !== 'm') {
				continue;
			}

			$this->log->addInfo('Resending open valve command', ['customer_id' => $openValveCommand->customer_ID, 'meter_id' => $openValveCommand->meter_id, 'permanent_meter_id' => $openValveCommand->permanent_meter_id, 'scheme_number' => $openValveCommand->scheme_number]);

			$openValveDuplicate = $openValveCommand->toArray();
			unset($openValveDuplicate['ID']);
			$openValveDuplicate['time_date'] = \Carbon\Carbon::now()->toDateTimeString();
			$openValveDuplicate['resent'] = 1;
			RTUCommandQue::resendOpenValve($openValveDuplicate);

			$this->log->addInfo('Open valve command re-sent');
		}
		*/
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
