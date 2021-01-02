<?php

use Whoops\Example\Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class PrepagoServiceController extends BaseController {

	private $internal_link = "http://prepago-admin.biz/services/list/internal";
	public function service_list($type = 'external')
	{
		
		if($type == "external") {
			
			
			// Send a cURL request to the internal link.	
			$ch = curl_init();
			$headers = array("Host: www.prepago-admin.biz");
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
		    curl_setopt($ch,CURLOPT_URL, $this->internal_link);
			curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,2);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			$res = curl_exec($ch);
			curl_close($ch);
			 
			return $res;
			
		} else {
			
			$services = $this->getServicesFromSSH();
			
			return $services;
			
		}
				
	}

	
	
	private function getServicesFromSSH()
	{
		
		$services = [];
		
		exec("ps aux | grep java", $output);
		foreach($output as $o)
		{
			if(strpos($o, '-jar ') == false)
			{
				continue;
			}
			
			{
				$parts = preg_split('/ +/',  $o);
				$p_id = $parts[1];
				$p_usage = $parts[2];
				$p_mem = $parts[3];
				$p_status = $parts[7];
				$p_last = substr($o, strrpos($o, "/"), -1);
				$p_start = substr($o, strrpos($o, "SCR"), -1);
				$p_since = $parts[8] . ' ' . $parts[9];
			}
			
			
			if(strpos($p_last, 'MBus') !== false)
				continue;
				
			// Handle java conditions
			{
			if(strpos($p_last, '-Djava.library.path=. ') !== false)
				$p_last = explode('-Djava.library.path=. ', $p_last)[1];
			if(strpos($p_last, '/') !== false)
				$p_last = str_replace('/', '', $p_last);
			if(strpos($p_last, '.jar') !== false)
				$p_last = str_replace('.jar', '', $p_last);
			if(strpos($p_last, '.ja') !== false)
				$p_last = str_replace('.ja', '', $p_last);
			if(strpos($p_start, 'pts/') !== false)
				$p_start = null;
			}
			
			// Handle inserting into array
			{
				if($p_start != null)
				{
					$name = preg_split('/ +/', $p_start)[2];
					
					$services[$p_last] = [
						'id' => $p_id,
						'name' => $name,
						'memory' => 0,
						'cpu'	 => 0,
						'running_since' => $p_since,
						'status' => $p_status,
						'start_cmd' => $p_start,
						'kill_cmd' => "kill $p_id",
						'others' => [],
					];
				}
				else
				{
					
						$services[$p_last]['memory'] += $p_mem;
						$services[$p_last]['cpu'] += $p_usage;
						$otherEntry = [
							'id'	 => $p_id,
							'status' => $p_status,
							'memory' => $p_mem,
							'cpu'	 => $p_usage,
						];
					
					array_push($services[$p_last]['others'], $otherEntry);
					
				}
			}
			
			/*
			echo 'Process ID: ' . $p_id;
			echo '<br/>';
			echo 'Process Usage: ' . $p_usage;
			echo '<br/>';
			echo 'Memory Usage: ' . $p_mem;
			echo '<br/>';
			echo 'Status: ' . $p_status;
			echo '<br/>';
			echo 'Last: ' . $p_last;
			echo '<br/>';
			echo 'Running since: ' . $p_since;
			echo '<br/>';
			echo 'Start: ' . $p_start;
			echo '<br/>';
			
			echo '<br/>';
			echo '<br/>';
			*/
		
		}
		
		$services['prepagoCPPScheduler'] = [
			'id' => '',
			'name' => '',
			'memory' => 0,
			'cpu'	 => 0,
			'running_since' => '',
			'status' => '',
			'start_cmd' => '',
			'kill_cmd' => '',
			'others' => [],
		];
		exec("ps aux | grep roslyn1234", $output2);
		foreach($output2 as $o)
		{
			if(strpos($o, 'apache') !== false)
				continue;
			$parts = preg_split('/ +/',  $o);
				$p_id = $parts[1];
				$p_usage = $parts[2];
				$p_mem = $parts[3];
				$p_status = $parts[7];
			if(strpos($o, " -S") !== false)
			{
				$p_start = substr($o, strrpos($o, "SCR"), -1);
				$p_name = preg_split('/ +/',  $p_start)[2];
				$p_since = $parts[8] . ' ' . $parts[9];

				$services['prepagoCPPScheduler'] = [
					'id' => $p_id,
					'name' => $p_name,
					'memory' => 0,
					'cpu'	 => 0,
					'running_since' => $p_since,
					'status' => $p_status,
					'start_cmd' => $p_start,
					'kill_cmd' => "kill $p_id",
					'others' => [],
				];
				
			}
			else
			{
				$services['prepagoCPPScheduler']['memory'] += $p_mem;
				$services['prepagoCPPScheduler']['cpu'] += $p_usage;
				$otherEntry = [
					'id'	 => $p_id,
					'status' => $p_status,
					'memory' => $p_mem,
					'cpu'	 => $p_usage,
				];
				array_push($services['prepagoCPPScheduler']['others'], $otherEntry);
			}
		}
		/*
		exec("ps aux | grep paypoint", $output3);
		foreach($output3 as $o)
		{
			if(strpos($o, 'apache') !== false)
				continue;
			$parts = preg_split('/ +/',  $o);
				$p_id = $parts[1];
				$p_usage = $parts[2];
				$p_mem = $parts[3];
				$p_status = $parts[7];
			if(strpos($o, " -S") !== false)
			{
				$p_start = substr($o, strrpos($o, "SCR"), -1);
				$p_name = preg_split('/ +/',  $p_start)[2];
				$p_since = $parts[8] . ' ' . $parts[9];
				
				$services['paypointServer'] = [
					'id' => $p_id,
					'name' => $p_name,
					'memory' => 0,
					'cpu'	 => 0,
					'running_since' => $p_since,
					'status' => $p_status,
					'start_cmd' => $p_start,
					'kill_cmd' => "kill $p_id",
					'others' => [],
				];
				
			}
			else
			{
				$services['paypointServer']['memory'] += $p_mem;
				$services['paypointServer']['cpu'] += $p_usage;
				$otherEntry = [
					'id'	 => $p_id,
					'status' => $p_status,
					'memory' => $p_mem,
					'cpu'	 => $p_usage,
				];
				array_push($services['paypointServer']['others'], $otherEntry);
			}
		}*/

		return Response::json($services);
	}
	
	public function cron_list()
	{
		$myFile = "/home/crontab.txt";
		exec("crontab -l > $myFile", $output);
		
		foreach($output as $o)
		{
			
		}
		
	}
	
	public function prepago_services()
	{
		try
		{
			
			$services = PrepagoService::where('enabled', 1)->get();
			$processes = [];
			foreach($services as $service)
			{
				$processes[$service->name] = [
					'process_id' => $service->process_id,
					'process_name' => $service->name,
					'process_started' => $service->running_since,
					'process_start_cmd' => $service->start_command,
					'process_memory'	=> 0,
					'process_cpu'		=> 0,
					'process_status'	=> '',
				];
			}
			
			$retrieved_services = PrepagoService::getServicesFromSSH();
			foreach($retrieved_services as $s)
			{
				$s = (object)$s;
				
				$service = PrepagoService::where('name', $s->name)->first();
				if($service) {
					$service->process_id = $s->id;
					$service->save();
				}
				
				$processes[$s->name] = [
					'process_id' => $s->id,
					'process_name' => $s->name,
					'process_started' => $s->running_since,
					'process_start_cmd' => $s->start_cmd,
					'process_memory'	=> $s->memory,
					'process_cpu'		=> $s->cpu,
					'process_status'		=> $s->status,
				];
				
			}
			
			
			
			return json_encode($processes);
		}
		catch(Exception $e)
		{
			return json_encode(['error' => "<b>An error occured:</b> " . $e->getMessage()]);
		}
		
			
	}
	
	public function prepago_stop_service($id, $name)
	{
		
		$commands = [
			'kill ' . $id,
		];
		
		\phpseclib\Net\SSH2::run($commands, function($line)
		{
			echo "Successfully killed service: " . $name;
		});

	}
	
	public function prepago_start_service($name)
	{
		
		$service = PrepagoService::where('name', $name)->first();
		$command_start = $service->start_command;
		
		$commands = [
			$command_start
		];
		
		$out = shell_exec($command_start);
		echo $out;
		

	}
	
}