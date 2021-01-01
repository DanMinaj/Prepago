<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class BackupDatabase extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'backup:database';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Backup database';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->log = new Logger('Database Backup Log');
		$this->log->pushHandler(new StreamHandler(__DIR__ . '/BackupDatabase/' . date('Y-m-d') . '.log'), Logger::INFO);
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
	
		try
		{
			ini_set('memory_limit','-1');
			
			$backupName = Data::getDbBackupName();
			
			$this->info("Backing up database with name: $backupName");
			Data::backupDB();
			
		}
		catch(Exception $e)
		{
			
			$this->log->addInfo("Error occured while backing up database at  " . date("d-m-Y H:i:s") . " : " . $e->getMessage());
			$this->info("Error occured  " . $e->getMessage());
	
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
