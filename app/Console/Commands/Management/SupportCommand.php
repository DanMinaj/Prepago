<?php

use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class SupportCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'support';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle support tasks such as follow up emails, sms etc';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('Support Commandd');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/SupportCommand/'.date('Y-m-d').'.log'), Logger::INFO);
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
            $this->handleSupportEmails();
            $this->handleSMS();
        } catch (Exception $e) {
        }
    }

    public function handleSupportEmails()
    {
        try {
            $queries = ReportABug::where('resolved', 1)
            ->whereRaw('(follow_up_sent = 0 OR follow_up_sent_2 = 0)')
            ->whereRaw("(created_at >= '2020-07-26 00:00:00')")
            ->get();

            echo "\n";
            foreach ($queries as $k => $q) {
                if (! $q->customer) {
                    $q->follow_up_sent = 1;
                    $q->follow_up_sent_2 = 1;
                    $q->follow_up_sent_at = date('Y-m-d H:i:s');
                    $q->save();

                    return;
                }

                $age_hrs = Carbon\Carbon::parse($q->completed_at)->diffInHours();
                $age_hrs_2 = Carbon\Carbon::parse($q->follow_up_sent_at)->diffInHours();

                echo 'Query #:'.$q->id."\n";
                echo 'Created: '.Carbon\Carbon::parse($q->created_at)->diffForHumans()."\n";
                if ($q->customer) {
                    echo 'Customer: '.$q->customer->username.' ('.$q->customer->id.")\n";
                }

                echo 'Follow ups: '.($q->follow_up_sent + $q->follow_up_sent_2)."\n";
                echo 'Age (hrs): '.$age_hrs."hrs\n";
                echo 'Age2 (hrs): '.$age_hrs_2."hrs\n";

                if ($q->customer) {
                    if ($q->follow_up_sent == 0 && $age_hrs >= 0) {
                        $q->sendFollowUpEmail($q->customer, $q);

                        $q->follow_up_sent = 1;
                        $q->follow_up_sent_at = date('Y-m-d H:i:s');
                        $q->save();
                        echo "**Sent follow up email 1!**\n";

                        continue;
                    }

                    if ($q->follow_up_sent_2 == 0 && $age_hrs_2 >= 10) {
                        $q->sendFollowUpEmail($q->customer, $q, true);

                        $q->follow_up_sent_2 = 1;
                        $q->follow_up_sent_at = date('Y-m-d H:i:s');
                        $q->save();
                        echo "**Sent follow up email 2!**\n";

                        continue;
                    }

                    echo "**No action.........**\n";

                    echo '';
                    echo "\n\n";
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function handleSMS()
    {
        try {
            $bugs = ReportABug::where('sms_sent', 0)->orderBy('id', 'ASC')->get();

            foreach ($bugs as $k => $v) {
            }
        } catch (Exception $e) {
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
