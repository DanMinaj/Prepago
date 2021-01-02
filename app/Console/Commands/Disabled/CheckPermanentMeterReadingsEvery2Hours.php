<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/*
The Use case - Run a server & scheme modem connection check
This is automatically checks the scheme log 15 minutes if any meters in the scheme have successfully been read in the last 1 hour/ 2 hours or appropriate reading cycle.
If no readings have been made, it randomly selects a meter. It reads it (and adds the reading if there is one to the manual read PHP table)
The connection check shows as a green/red “status” word after each scheme name on the welcome schemes screen on log in for the User Test.

So let me walk you through the steps of the new task to make sure I understood it correctly
1) Every 2 hours there will be a script going through the records in the permanent_meter_data_readings_all table and checking for each scheme whether any of its meters have been read within the last 2 hours.
2) If no meters were read within the last 2 hours, I will randomly select a meter from the scheme and perform a manual reading.
3) I will display the "status" word in red or green (depending on the manual reading result) on the  welcome schemes screen. And this scheme status will be visible only for the User Test.
 */
class CheckPermanentMeterReadingsEvery2Hours extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'pmd:2hourcheck';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('Permanent Meter Readings Check');
        $this->log->pushHandler(new StreamHandler(storage_path('logs/pmd_2_hour_check_' . date('Y-m-d') . '.log'), Logger::INFO));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
		
		// Disabled on 09/07/2018 - Refer to ProcessPMReadings2HoursCheck code.
		
		return;
		
        $schemesRequiringManualRead = $this->schemesRequiringManualRead();
        $this->log->addInfo('Schemes requiring manual read', $schemesRequiringManualRead);
		$this->info('Schemes requiring manual read', $schemesRequiringManualRead);
		
        // randomly select a permanent meter from each scheme and perform a manual reading
        foreach ($schemesRequiringManualRead as $schemeNumber => $schemePrefix) {
            $this->log->addInfo('Selecting random permanent meter for scheme with number ' . $schemeNumber);

            $pm = PermanentMeterData::where('scheme_number', $schemeNumber)
                        ->where("installation_confirmed", 1)
                        ->orderByRaw("RAND()")
                        ->limit(1)
                        ->first();
            $this->log->addInfo('Scheme\'s random meter number ID is ' . ($pm ? $pm->ID : 'scheme has no meters'));

            if ($pm) {
                $pmReading = $pm->performManualReading($schemeNumber, $schemePrefix);
                $pmReading->for_scheme_status_check = 1;
                $pmReading->save();

                $this->log->addInfo('Manual reading performed');
            }
        }
		
		// set remaining schemes status back to active
        if ($schemesRequiringManualRead) {
            $schemesRequiringStatusReset = Scheme::whereNotIn('scheme_number', array_keys($schemesRequiringManualRead))->lists('scheme_number');
            $this->log->addInfo('Schemes requiring status reset', $schemesRequiringStatusReset);
            foreach ($schemesRequiringStatusReset as $schemeNumber) {
                Scheme::where('scheme_number', $schemeNumber)->update(['status_ok' => 1]);
                $this->log->addInfo('The status of scheme with number ' . $schemeNumber . ' was reset (set to active).');
            }
        }
    }

    protected function schemesRequiringManualRead()
    {
//        $twoHoursAgo = "2018-03-27 18:44:12";
        $twoHoursAgo = \Carbon\Carbon::now()->subHours(2)->toDateTimeString();
        $schemesNumbersWithMeterReadsInTheLast2Hours = PermanentMeterDataReadingsAll::select('*')
                                                            ->from(\DB::raw(
                                                                '(SELECT scheme_number FROM permanent_meter_data_readings_all 
                                                                    WHERE time_date > "' . $twoHoursAgo . '" 
                                                                    ORDER BY time_date DESC
                                                                ) AS pm_readings')
                                                            )
                                                            ->groupBy('pm_readings.scheme_number')
                                                            ->lists('scheme_number');

        $query = Scheme::withoutArchived()->select('scheme_number', 'prefix');
        if ($schemesNumbersWithMeterReadsInTheLast2Hours) {
            $query->whereNotIn('scheme_number', $schemesNumbersWithMeterReadsInTheLast2Hours);
        }

        return $query->lists('prefix', 'scheme_number');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array();
    }

}
