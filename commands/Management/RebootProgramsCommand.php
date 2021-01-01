<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Process\Process;

class RebootProgramsCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'reboot:programs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reboots prepago services';

	
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('RebootPrograms Command');
        $this->log->pushHandler(new StreamHandler(__DIR__ . '/RebootProgramsCommand/' . date('Y-m-d') . '.log'), Logger::INFO);
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
			
			$command = "sudo /var/www/app/commands/Management/r.sh";
			exec($command, $output);
			var_dump( $output);
			
		} catch(Exception $e) {		
			echo $e->getMessage();
		}
		
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