<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class TempControlTaskCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'tempcontrol:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run temp control tasks';

	
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('Temp Control Commandd');
        $this->log->pushHandler(new StreamHandler(__DIR__ . '/TempControlTaskCommand/' . date('Y-m-d') . '.log'), Logger::INFO);
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
		
		$this->purgeOld();
		
		$tcp_customers = System::getTcpCustomers();
		
		$to_restore = $tcp_customers['require_restoration'];
		$to_shut_off = $tcp_customers['require_shut_off'];
		$to_shut_off_awaymode = $tcp_customers['require_away_mode'];
		
		echo "\n=========Grabbing customers requiring restoration=========\n\n";
		foreach($to_restore as $k => $dhm) {
			
			$pmd_ID = $dhm->permanent_meter_ID;
			$pmd = PermanentMeterData::where('ID', $pmd_ID)->first();
			if(!$pmd) continue;
			
			$entry = TempControlTask::where('username', $pmd->username)
			->whereRaw("(completed_at IS NULL)")->get();
			
			if(!$entry) {
				$entry = new TempControlTask();
				$entry->scheme_number = $dhm->scheme_number;
				$entry->permanent_meter_ID = $dhm->permanent_meter_ID;
				$entry->username = $pmd->username;
				$entry->balance_start = ($pmd->customer) ? $pmd->customer->balance : null;
				$entry->temp_start = $dhm->last_flow_temp;
				$entry->temp_cur = $dhm->last_flow_temp;
				$entry->log = '';
				$entry->completed_at = NULL;
			}
			
			
			$entry->temp_cur = $dhm->last_flow_temp;
			$entry->expected_to = 'open';
			$entry->last_command = $dhm->last_valve_status;
			$entry->last_command_at = $dhm->last_valve_status_time;
			$entry->save();
			
		}
		
		echo "\n=========Grabbing customers requiring shut off=========\n\n";
		foreach($to_shut_off as $k => $dhm) {
			
			$pmd_ID = $dhm->permanent_meter_ID;
			$pmd = PermanentMeterData::where('ID', $pmd_ID)->first();
			if(!$pmd) continue;
			
			$entry = TempControlTask::where('username', $pmd->username)
			->whereRaw("(completed_at IS NULL)")->get();
			
			if(!$entry) {
				$entry = new TempControlTask();
				$entry->scheme_number = $dhm->scheme_number;
				$entry->permanent_meter_ID = $dhm->permanent_meter_ID;
				$entry->username = $pmd->username;
				$entry->balance_start = ($pmd->customer) ? $pmd->customer->balance : null;
				$entry->temp_start = $dhm->last_flow_temp;
				$entry->temp_cur = $dhm->last_flow_temp;
				$entry->log = '';
				$entry->completed_at = NULL;
			}
			
			
			$entry->temp_cur = $dhm->last_flow_temp;
			$entry->expected_to = 'closed';
			$entry->last_command = $dhm->last_valve_status;
			$entry->last_command_at = $dhm->last_valve_status_time;
			$entry->save();
			
		}
		
		echo "\n=========Grabbing customers requiring away mode shut off=========\n\n";
		foreach($to_shut_off_awaymode as $k => $dhm) {
			
			$pmd_ID = $dhm->permanent_meter_ID;
			$pmd = PermanentMeterData::where('ID', $pmd_ID)->first();
			if(!$pmd) continue;
			
			$entry = TempControlTask::where('username', $pmd->username)
			->whereRaw("(completed_at IS NULL)")->get();
			
			if(!$entry) {
				$entry = new TempControlTask();
				$entry->scheme_number = $dhm->scheme_number;
				$entry->permanent_meter_ID = $dhm->permanent_meter_ID;
				$entry->username = $pmd->username;
				$entry->balance_start = ($pmd->customer) ? $pmd->customer->balance : null;
				$entry->temp_start = $dhm->last_flow_temp;
				$entry->temp_cur = $dhm->last_flow_temp;
				$entry->log = '';
				$entry->completed_at = NULL;
			}
			
			
			$entry->temp_cur = $dhm->last_flow_temp;
			$entry->expected_to = 'closed';
			$entry->last_command = $dhm->last_valve_status;
			$entry->last_command_at = $dhm->last_valve_status_time;
			$entry->save();
			
		}
			
	}
	
	public function purgeOld()
	{
		
	}
	
	public function is_on($dhm, $temp = null) {
		
		$temp = $dhm->last_flow_temp;
		
		$is_on = ($temp >= SystemSetting::get('service_on_min_temp'));

		return $is_on;
	}


	public function add_log($i, $entry) {
		try {
			
			if(empty($i->log))
				$log = [];
			else
				$log = unserialize($i->log);
			
			$entry = date('Y-m-d H:i:s') . "| " . $entry;
			array_push($log, $entry);
			
			$log = serialize($log);
			
			$i->log = $log;
			$i->save();
			
			
		} catch(Exception $e) {
			$this->print_err($i, $e, "add_log()");
		}
	}
	
	public function clear_log($i) {
		try {
			$i->log = "";
			$i->save();
		} catch(Exception $e) {
			$this->print_err($i, $e, "clear_log()");
		}
	}
	
	public function print_err($i, $e, $func) {
		echo "[" . $func . "] Error: " . $e->getMessage() . " (" . $e->getLine() . ")\n";
		$this->add_log($i, $func . ": " . $e->getMessage() . "(" . $e->getLine() . ")");
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