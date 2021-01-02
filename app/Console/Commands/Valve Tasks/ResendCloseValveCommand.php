<?php

use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ResendCloseValveCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'close-valve:resend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resend close valve command for "m" type meters.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('Close valve resend log');
        $this->log->pushHandler(new StreamHandler(storage_path('logs/resend_close_valve_command.log'), Logger::INFO));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        /*
        $closeValveCommandsForThePast10Minutes = RTUCommandQue::where('turn_service_off', 1)
                                                    ->where('time_date', '>', \Carbon\Carbon::now()->subMinutes(30))
                                                    ->where('resent', 0)
                                                    ->get();

        foreach ($closeValveCommandsForThePast10Minutes as $closeValveCommand) {
            $permanentMeter = PermanentMeterData::find($closeValveCommand->permanent_meter_id);
            if ( ! $permanentMeter || $permanentMeter->scu_type !== 'm') {
                continue;
            }

            $this->log->addInfo('Resending close valve command', ['customer_id' => $closeValveCommand->customer_ID, 'meter_id' => $closeValveCommand->meter_id, 'permanent_meter_id' => $closeValveCommand->permanent_meter_id, 'scheme_number' => $closeValveCommand->scheme_number]);

            $closeValveDuplicate = $closeValveCommand->toArray();
            unset($closeValveDuplicate['ID']);
            $closeValveDuplicate['time_date'] = \Carbon\Carbon::now()->toDateTimeString();
            $closeValveDuplicate['resent'] = 1;
            RTUCommandQue::resendCloseValve($closeValveDuplicate);

            $this->log->addInfo('Close valve command re-sent');
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
