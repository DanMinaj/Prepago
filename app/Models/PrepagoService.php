<?php

class PrepagoService extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'prepago_services';

    // List of services that SHOULD be running
    public static function allServices()
    {
        return self::all();
    }

    // List of services that are ACTUALLY running
    public static function getServicesFromSSH()
    {
        $services = [];
        exec('ps aux | grep java', $output);
        foreach ($output as $o) {
            if (strpos($o, '-jar ') == false) {
                continue;
            }

            {
                $parts = preg_split('/ +/', $o);
                $p_id = $parts[1];
                $p_usage = $parts[2];
                $p_mem = $parts[3];
                $p_status = $parts[7];
                $p_last = substr($o, strrpos($o, '/'), -1);
                $p_start = substr($o, strrpos($o, 'SCR'), -1);
                $p_since = $parts[8].' '.$parts[9];
            }

            if (strpos($p_last, 'MBus') !== false) {
                continue;
            }

            // Handle java conditions
            {
            if (strpos($p_last, '-Djava.library.path=. ') !== false) {
                $p_last = explode('-Djava.library.path=. ', $p_last)[1];
            }
            if (strpos($p_last, '/') !== false) {
                $p_last = str_replace('/', '', $p_last);
            }
            if (strpos($p_last, '.jar') !== false) {
                $p_last = str_replace('.jar', '', $p_last);
            }
            if (strpos($p_last, '.ja') !== false) {
                $p_last = str_replace('.ja', '', $p_last);
            }
            if (strpos($p_start, 'pts/') !== false) {
                $p_start = null;
            }
            }

            // Handle inserting into array
            {
                if ($p_start != null) {
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
                } else {
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
        exec('ps aux | grep roslyn1234', $output2);
        foreach ($output2 as $o) {
            if (strpos($o, 'apache') !== false) {
                continue;
            }
            $parts = preg_split('/ +/', $o);
            $p_id = $parts[1];
            $p_usage = $parts[2];
            $p_mem = $parts[3];
            $p_status = $parts[7];
            if (strpos($o, ' -S') !== false) {
                $p_start = substr($o, strrpos($o, 'SCR'), -1);
                $p_name = preg_split('/ +/', $p_start)[2];
                $p_since = $parts[8].' '.$parts[9];

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
            } else {
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
        }
        */

        return $services;
    }
}
