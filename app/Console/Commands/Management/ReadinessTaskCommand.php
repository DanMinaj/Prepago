<?php

namespace App\Console\Commands\Management;

use App\Models\ReadinessTask;
use App\Models\SystemSetting;
use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ReadinessTaskCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'readiness:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run scu readiness tasks';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('Readiness Task Commandd');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/ReadinessTaskCommand/'.date('Y-m-d').'.log'), Logger::INFO);
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

        $tasks = ReadinessTask::unCompleted();

        if (count($tasks) == 0) {
            echo "\nThere are no tasks to complete..\n";
        }

        echo "\n";
        foreach ($tasks as $k => $t) {
            echo '-----Task ID '.$t->task_id."-----\n";
            $t = $t;
            while ($t != null) {
                try {
                    echo "\nHandling Order#".$t->order_id."\n";

                    if ($t->schemeProcessing()) {
                        echo "A command in this scheme is currently processing! Please wait!\n";
                        $t = $t->getNext();
                        continue;
                    }

                    if ($t->last_execution_at != null) {
                        $carb = Carbon\Carbon::parse($t->last_execution_at);
                        $diff = $carb->diffInMinutes();
                        $t->next_execution -= $diff;
                        echo "-= $diff mins. (Last executed: ".$carb->diffInSeconds()."s ago.)\n";
                    }

                    $t->last_execution_at = date('Y-m-d H:i:s');
                    $t->save();

                    if (! $t->pmd) {
                        echo "This entry has no PMD! Skipping!\n";
                        $t = $t->getNext();
                        continue;
                    }

                    if ($t->next_execution > 0 && ($t->started_at != null)) {
                        echo 'Not time to execute. '.$t->next_execution." minutes left! Skipping!\n";
                        $t = $t->getNext();
                        continue;
                    }

                    if ($t->started_at == null) {
                        $t->started_at = date('Y-m-d H:i:s');
                        $t->save();
                    }

                    $test_successful = false;

                    $t->processing = 1;
                    $t->save();

                    // Start test
                    switch ($t->type) {
                        case 'test_valve':
                            $test_successful = $this->test_valve($t);
                        break;
                    }

                    $t->processing = 0;
                    $t->save();

                    try {
                        $extra_data = unserialize($t->extra_data);
                        if ($test_successful) {
                            $t->next_execution = (isset($extra_data['next_execution'])) ? $extra_data['next_execution'] : 60;
                        } else {
                            $t->next_execution = (isset($extra_data['next_fail_execution'])) ? $extra_data['next_fail_execution'] : 10;
                        }

                        $t->save();
                    } catch (Exception $e) {
                    }

                    $t = $t->getNext();

                    echo "\n\n";
                } catch (Exception $e) {
                    echo 'An error occured: '.$e->getMessage().' ('.$e->getLine().')';
                    $t->processing = 0;
                    $t->save();
                }
            }

            echo "\n======================\n";
        }
    }

    public function test_valve($i)
    {
        try {
            switch ($i->step) {

                case '1':
                    $temp = $this->get_temp($i);
                    $is_on = $this->is_on($i, $temp);

                    if ($is_on) {
                        echo "YES it is!\n";
                        $this->close_valve($i);
                        $this->add_log($i, 'Valve is open. Closing the valve.');
                        echo "Temp = $temp. Turning it off by closing the valve..";
                        echo 'success!';
                        $i->step = 2;
                        $i->expected_to = 'close';
                        $i->save();

                        return true;
                    } else {
                        echo "NO it isn't\n";
                        $this->open_valve($i);
                        $this->add_log($i, 'Valve is closed. Opening the valve.');
                        echo "Temp = $temp. Turning it on by opening the valve..";
                        echo 'success!';
                        $i->step = 2;
                        $i->expected_to = 'open';
                        $i->save();

                        return true;
                    }
                break;
                case '2':
                    $temp = $this->get_temp($i);
                    $is_on = $this->is_on($i, $temp);

                    if ($i->expected_to == 'close') {
                        if (! $is_on) {
                            $this->open_valve($i);
                            $this->add_log($i, 'Valve closure completed. Reversing. Opening the valve.');
                            echo "\nValve closure completed! Reversing. Opening the valve..success\n";
                            $i->step = 3;
                            $i->expected_to = 'open';
                            $i->save();
                        } else {
                            echo "\nValve is still open($temp)..should be off..checking again later.\n";
                        }
                    }

                    if ($i->expected_to == 'open') {
                        if ($is_on) {
                            $this->close_valve($i);
                            $this->add_log($i, 'Valve open completed. Reversing. Closing the valve.');
                            echo "\nValve open completed! Reversing. Closing the valve..success\n";
                            $i->step = 3;
                            $i->expected_to = 'close';
                            $i->save();
                        } else {
                            echo "\nValve is still closed($temp)..should be open..checking again later.\n";
                        }
                    }

                    return true;
                break;
                case '3':
                    $temp = $this->get_temp($i);
                    $is_on = $this->is_on($i, $temp);

                    if ($i->expected_to == 'open') {
                        if ($is_on) {
                            $this->add_log($i, 'Valve open completed. Test completed.');
                            echo "\nValve open completed! Test completed\n";
                            $i->step = 4;
                            $i->completed_at = date('Y-m-d H:i:s');
                            $i->expected_to = 'n/a';
                            $i->save();
                        } else {
                            echo "\nValve is still closed($temp)..should be open..checking again later.\n";
                        }
                    }

                    if ($i->expected_to == 'close') {
                        if (! $is_on) {
                            $this->add_log($i, 'Valve closure completed. Test completed.');
                            echo "\nValve closure completed! Test completed\n";
                            $i->step = 4;
                            $i->completed_at = date('Y-m-d H:i:s');
                            $i->expected_to = 'n/a';
                            $i->save();
                        } else {
                            echo "\nValve is still open($temp)..should be off..checking again later.\n";
                        }
                    }

                    return true;
                break;
            }
        } catch (Exception $e) {
            $this->print_err($i, $e, 'test_valve()');
        }
    }

    public function open_valve($i)
    {
        $pmd = $i->pmd;
        $request = $pmd->open('never', 3);

        if ($request->success == false) {
            throw new Exception('Open valve failed, trying again later!');
        }
    }

    public function close_valve($i)
    {
        $pmd = $i->pmd;
        $request = $pmd->close('never', 3);

        if ($request->success == false) {
            throw new Exception('Close valve failed, trying again later!');
        }
    }

    public function is_on($i, $temp = null)
    {
        echo 'Checking if temp is classified service on..';

        if ($temp == null) {
            $temp = $this->get_temp($i);
        }

        $is_on = ($temp >= SystemSetting::get('service_on_min_temp'));

        return $is_on;
    }

    public function get_temp($i)
    {
        echo "Getting temperature..\n";

        $pmd = $i->pmd;
        $meter_reading = $pmd->read('never', 3, 'meter');

        if ($meter_reading->success == false || ! isset($meter_reading->temp)) {
            //$last_temp = $pmd->last_temp;
            //$last_temp_time = $pmd->last_temp_time;
            throw new Exception('Meter reading failed, trying again later!');
        }

        $this->add_log($i, 'Temperature is '.$meter_reading->temp.'');

        return (float) $meter_reading->temp;
    }

    public function add_log($i, $entry)
    {
        try {
            if (empty($i->log)) {
                $log = [];
            } else {
                $log = unserialize($i->log);
            }

            $entry = date('Y-m-d H:i:s').'| '.$entry;
            array_push($log, $entry);

            $log = serialize($log);

            $i->log = $log;
            $i->save();
        } catch (Exception $e) {
            $this->print_err($i, $e, 'add_log()');
        }
    }

    public function clear_log($i)
    {
        try {
            $i->log = '';
            $i->save();
        } catch (Exception $e) {
            $this->print_err($i, $e, 'clear_log()');
        }
    }

    public function print_err($i, $e, $func)
    {
        echo '['.$func.'] Error: '.$e->getMessage().' ('.$e->getLine().")\n";
        $this->add_log($i, $func.': '.$e->getMessage().'('.$e->getLine().')');
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
