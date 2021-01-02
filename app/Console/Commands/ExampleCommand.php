<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;



class ExampleCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'example';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Example command';

    /**
     * Set the log file & log file name.
     */
    private function setLog($name = 'default', $file_path = '/Default Logs/examplecommand.log')
    {
        $this->log = new Logger($name);
        $this->log->pushHandler(new StreamHandler(__DIR__.$file_path, Logger::INFO));
    }

    /**
     * Create a log entry in the log file.
     */
    private function createLog($msg)
    {
        $this->log->info($msg);
    }

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
        $this->setLog();
        $this->createLog('Just ran ExampleCommand');
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
