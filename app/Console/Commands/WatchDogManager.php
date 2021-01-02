<?php

namespace App\Console\Commands;

use App\Models\PermanentMeterData;
use App\Models\WatchDog;
use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;



class WatchDogManager extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'watchdog:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run watchdog handler';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('Watchdog Log');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/Watch Dog Logs/'.date('Y-m-d').'.log', Logger::INFO));
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

        try {
            ini_set('memory_limit', '-1');

            $watchdogs = WatchDog::where('completed', 0)
            ->get();

            if (count($watchdogs) == 0) {
                $this->info('Found no watchdogs');
                $this->info('Trying again later..');
                $this->log->addInfo('Found no watchdogs');
                $this->log->addInfo('Trying again later..');
            }

            foreach ($watchdogs as $watchdog) {
                $this->info("\nHandling Watchdog #".$watchdog->id.' for customer #'.$watchdog->customer_id);
                $this->log->addInfo("\nHandling Watchdog #".$watchdog->id.' for customer #'.$watchdog->customer_id);

                //$dueToRun = ($watchdog->updated_at

                $lastRan = Carbon\Carbon::parse($watchdog->updated_at);
                $now = Carbon\Carbon::parse(date('Y-m-d H:i:s'));
                $minsPassed = $now->diffInMinutes($lastRan);
                $minsToBePassed = $watchdog->run_every * 60;
                $this->info("Minutes passed: $minsPassed/$minsToBePassed");
                $this->log->addInfo("Minutes passed: $minsPassed/$minsToBePassed\n");

                if (($minsPassed < $minsToBePassed) && ($watchdog->ran_times > 0 || $watchdog->failed_attempts > 0)) {
                    $this->info("$minsToBePassed minutes have not passed yet! ".($minsToBePassed - $minsPassed)." minutes left!\n");
                    $this->log->addInfo("$minsToBePassed minutes have not passed yet! ".($minsToBePassed - $minsPassed)." minutes left!\n");

                    return;
                }

                if ($watchdog->reading) {
                    $this->info($watchdog->getInfo());
                    $this->info("Skipping.. a read is already in progress..\n\n");
                    $this->log->addInfo($watchdog->getInfo());
                    $this->log->addInfo("Skipping.. a read is currently in progress..\n\n");
                    continue;
                }

                $watchdog->reading = true;
                $watchdog->save();

                try {
                    $pmd = PermanentMeterData::where('ID', $watchdog->permanent_meter_id)->first();

                    if ($pmd) {
                        $this->info('Reading PMD#'.$pmd->ID);
                        $this->log->addInfo('Reading PMD#'.$pmd->ID);
                        $res = $pmd->read('never', 3, 'meter_number', true);
                        if (! $res->success) {
                            $this->info('Previous relay failed.. incrementing failed attempts');
                            $this->log->addInfo('Previous relay failed.. incrementing failed attempts');
                            $watchdog->failed_attempts++;
                            $watchdog->telegram_returned .= '';
                            $watchdog->telegram_returned .= 'Failed attempt at '.date('Y-m-d H:i:s');
                            $watchdog->telegram_returned .= "\n--------------------\n\n";
                        } else {
                            $this->info('Previous relay successful.');
                            $watchdog->failed_attempts = 0;
                            $watchdog->telegram_returned .= $res->telegram."\n";
                            $watchdog->telegram_returned .= 'Successful attempt at '.date('Y-m-d H:i:s');
                            $watchdog->telegram_returned .= "\n--------------------\n\n";
                            $watchdog->ran_times++;
                        }
                    }

                    if ($watchdog->failed_attempts >= $watchog->max_failed_attempts) {
                        $watchdog->completed = 1;
                        $watchdog->completed_at = date('Y-m-d H:i:s');
                        $watchdog->save();
                    }
                } catch (Exception $e) {
                } finally {
                    $watchdog->reading = false;
                    $watchdog->save();

                    if ($watchdog->ran_times >= $watchdog->run_times) {
                        $watchdog->completed = 1;
                        $watchdog->completed_at = date('Y-m-d H:i:s');
                        $watchdog->save();
                    }

                    $this->info("\n\n");
                    $this->log->addInfo("\n\n");
                }

                // if($watchdog->awaitingAcknowledgement)
                // {

                    // $watchdog->run_next = Carbon\Carbon::parse($watchdog->updated_at)->addHours($watchdog->run_every);
                    // $watchdog->ran_times++;

                    // $this->info($watchdog->getInfo());
                    // $this->info("Acknowledging watchdog");

                    // $this->log->addInfo($watchdog->getInfo());
                    // $this->log->addInfo("Acknowledging watchdog");

                    // if($watchdog->lastRelay->fail)
                    // {
                        // $this->info("Previous relay failed.. incrementing failed attempts");
                        // $this->log->addInfo("Previous relay failed.. incrementing failed attempts");
                        // $watchdog->failed_attempts++;
                        // $watchdog->telegram_returned .= $watchdog->lastRelay->telegram;
                        // $watchdog->telegram_returned .= "Failed attempt at " . date('Y-m-d H:i:s');
                        // $watchdog->telegram_returned .= "\n--------------------\n\n";
                    // }
                    // else {

                        // $this->info("Previous relay successful.");
                        // $watchdog->failed_attempts = 0;
                        // $watchdog->telegram_returned .= $watchdog->lastRelay->telegram;
                        // $watchdog->telegram_returned .= "Successful attempt at " . date('Y-m-d H:i:s');
                        // $watchdog->telegram_returned .= "\n--------------------\n\n";

                    // }

                    // $watchdog->acknowledgeLastRelay();

                    // $watchdog->save();

                    // continue;
                // }

                // if($watchdog->run_times == $watchdog->ran_times) {

                    // $this->info($watchdog->getInfo());
                    // $this->info("Watchdog has reached max run time\n\n");
                    // $this->log->addInfo($watchdog->getInfo());
                    // $this->log->addInfo("Watchdog has reached max run time\n\n");

                    // $watchdog->completed = true;
                    // $watchdog->completed_at = date('Y-m-d H:i:s');
                    // $watchdog->save();
                    // continue;
                // }

                // if($watchdog->failed_attempts == $watchdog->max_failed_attempts) {

                    // $this->info($watchdog->getInfo());
                    // $this->info("Watchdog has reached max fail time\n\n");
                    // $this->log->addInfo($watchdog->getInfo());
                    // $this->log->addInfo("Watchdog has reached max fail time\n\n");

                    // $watchdog->completed = true;
                    // $watchdog->completed_at = date('Y-m-d H:i:s');
                    // $watchdog->save();
                    // continue;
                // }
            }
        } catch (Exception $e) {

            //$this->log->addInfo("Error occured while backing up customers at  " . date("d-m-Y H:i:s") . " : " . $e->getMessage());
            $this->info('Error occured  '.$e->getMessage());
            $this->log->addInfo('Error occured  '.$e->getMessage());
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
