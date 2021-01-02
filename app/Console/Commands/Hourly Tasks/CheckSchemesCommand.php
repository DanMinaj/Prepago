<?php

use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Processes the entries inserted by the 'pmd:2hourcheck' command and updates the scheme status field
 * based on whether the reading was completed successfully or not.
 *
 * Class CheckSchemesCommand
 */
class CheckSchemesCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'check-schemes';

    private $testMode = false;
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
        $this->log = new Logger('Processing Check Schemes');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/CheckSchemesCommand/'.date('Y-m-d').'.log'), Logger::INFO);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $attempts = 4;
        $time_start = microtime(true);
        $EseyeConnection = EseyeConnection::establish($attempts);
        $schemes = Scheme::where('archived', 0)->where('status_debug', 0)->whereNotIn('id', [2, 15, 23, 25])->orderBy('status_offline_times', 'DESC')->get();

        if (! $EseyeConnection->isLoggedIn()) {
            $this->info('Eseye Login: FAILED');

            return;
        }

        $this->info('Eseye Login: Successful!');
        $this->info('Running scheme status checker ['.date('Y-m-d H:i:s')."]\n");
        $this->log->addInfo('Running scheme status checker ['.date('Y-m-d H:i:s')."]\n");

        if ($this->testMode) {
            $schemes = Scheme::where('id', 5)->get();
        }

        foreach ($schemes as $scheme) {
            try {
                $SIM = $scheme->SIM;
                if (! $SIM) {
                    continue;
                }

                $this->info("Checking Scheme #$scheme->id");
                $this->info("Name: $scheme->scheme_nickname");
                $this->info('IP Address: '.$SIM->IP_Address);
                if (strpos($scheme->status_checked, '0000') === false) {
                    $this->info('Last status: '.(($scheme->status_ok == '1') ? 'Online' : 'Offline :('));
                    $this->info('Last checked: '.(Carbon\Carbon::parse($scheme->status_checked)->diffForHumans()).'.');
                }
                if ($scheme->lastReading && ! empty($scheme->lastReading->hrs)) {
                    $this->info('Last reading: '.$scheme->lastReading->hrs.' hrs ago.');
                }

                //echo $scheme->tracking->first()->reboot_times;
                $EseyeConnection->setScheme($scheme->scheme_number);
                $isOnline = Simcard::online($SIM->IP_Address, 6, true, $scheme->scheme_number);
                $this->info('Current Status: '.(($isOnline) ? 'Online' : 'Offline :('));
                $this->log->addInfo('Current Status: '.(($isOnline) ? 'Online' : 'Offline :('));

                if ($isOnline) {
                    TrackingScheme::stamp($scheme->scheme_number, 1);
                    $scheme->status_reboot_times = 0;
                    $scheme->status_last_online = date('Y-m-d H:i:s');
                    $scheme->save();

                    if ($scheme->lastReading && ! empty($scheme->lastReading->hrs)) {

                        // If the scheme hasn't been read in 8 hrs, reboot it
                        if ($scheme->lastReading->hrs >= 8 && count($scheme->customers) > 1) {
                            $scheme->status_read_warning_times++;
                            $scheme->save();
                            if ($scheme->status_read_warning_times >= 2) {
                                $this->info('Non-reading hours: '.$scheme->lastReading->hrs);
                                $this->log->addInfo('Non-reading hours: '.$scheme->lastReading->hrs);

                                if ($scheme->block_reboots == 1) {
                                    $this->info('Reboots blocked! Will not proceed.');
                                    continue;
                                }

                                if ($scheme->getLastReboot() >= 2400 || $scheme->getLastReboot() == -1) {
                                    $rebootSuccessful = $scheme->reboot($EseyeConnection);
                                    if (! $rebootSuccessful) {
                                        $this->info('Reboot failed!');
                                        $this->log->addInfo('Reboot failed!');
                                        continue;
                                    }

                                    $this->info("Reboot #$scheme->status_reboot_times successful!");
                                    $this->log->addInfo("Reboot #$scheme->status_reboot_times successful!");
                                } else {
                                    $this->info('Rebooted recently..will try again later. ('.$scheme->getLastReboot().')');
                                    $this->log->addInfo('Rebooted recently..will try again later. ('.$scheme->getLastReboot().')');
                                }
                            }
                        } else {
                            $scheme->status_read_warning_times = 0;
                            $scheme->save();
                        }
                    }
                } else {
                    TrackingScheme::stamp($scheme->scheme_number, 0);
                    $scheme->status_last_offline = date('Y-m-d H:i:s');
                    $scheme->save();

                    $this->info("Offline times consecutively: $scheme->status_offline_times");
                    $this->log->addInfo("Offline times consecutively: $scheme->status_offline_times");

                    if ($scheme->status_offline_times >= 5) {
                        $IP = $scheme->IP;
                        $res = Simcard::rebootEmnify($IP);
                        $rebooted = ($res->rebooted == true);
                        $success_rebooted_msg = $res->msg_res;
                        $this->info("Sent reboot command. $success_rebooted_msg");
                        $this->log->addInfo("Sent reboot command. $success_rebooted_msg");
                        $scheme->status_offline_times = 0;
                        $scheme->save();
                    }
                }
            } catch (Exception $e) {
                $this->info('Error: '.$e->getMessage());
                $this->log->addInfo('Error: '.$e->getMessage());
            }
            $this->info("\n\n");
        }

        $time_end = microtime(true);

        $execution_time = ($time_end - $time_start);

        $this->info('FINISHED! Took a total of '.$execution_time.' seconds to complete!');

        $this->info('-----------------------------------------');
        $this->log->addInfo('-----------------------------------------');

        $this->info("\n\n");
        $this->log->addInfo("\n\n");
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
