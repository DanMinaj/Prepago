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
class SMSReplyControlCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'sms:replies';

    private $testMode = false;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parses incoming customer SMS to execute events';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('Parsing SMS Replies');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/SMSReplyControlCommand/'.date('Y-m-d').'.log'), Logger::INFO);
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
