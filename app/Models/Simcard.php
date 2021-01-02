<?php

class Simcard extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sim_cards';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    public static function online($host, $time = 6, $update_status = true, $scheme_id = -1)
    {
        $waitTimeoutInSeconds = 25;

        $scheme = Scheme::where('scheme_number', $scheme_id)->first();
        if (! $scheme) {
            $scheme = self::getScheme($host);
        }

        try {

            // if(!$scheme || $scheme == null) {
            // return false;
            // }

            if ($scheme) {
                $scheme->status_checked = date('Y-m-d H:i:s');
            }

            try {
                if ($fp = fsockopen($host, 2221, $errCode, $errStr, $waitTimeoutInSeconds)) {
                    if ($scheme) {
                        $scheme->status_ok = 1;
                        $scheme->status_offline_times = 0;
                        $scheme->checkWatch();
                    }

                    return true;
                    fclose($fp);
                }
            } catch (Exception $e) {
            }

            $ping = exec('ping -c 1 '.$host, $output, $result);

            if ($result == 1) {
                if ($scheme) {
                    $scheme->status_ok = 0;
                    $scheme->status_offline_times++;
                    $scheme->checkWatch();
                }

                return false;
            } else {
                if ($scheme) {
                    $scheme->status_ok = 1;
                    $scheme->status_offline_times = 0;
                    $scheme->checkWatch();
                }

                return true;
            }

            //var_dump($ping);
        } catch (Exception $e) {
            if ($scheme) {
                $scheme->status_ok = 0;
                $scheme->status_offline_times++;
                $scheme->checkWatch();
            }

            return false;
        } finally {
            if ($scheme) {
                $scheme->save();
            }
        }

        return false;
    }

    public static function reboot($host, $type = 'emnify', $update_status = true, $scheme_id = -1)
    {
        $scheme = Scheme::where('scheme_number', $scheme_id)->first();
        if (! $scheme) {
            $scheme = self::getScheme($host);
        }

        try {
            $SIM = self::where('IP_Address', $host)->first();

            if (! $SIM) {
                return false;
            }

            $iccid = $SIM->ICCID;

            if ($type == 'emnify') {
                return self::rebootEmnify($host);
            }

            if ($type == 'oliviawireless') {
                return self::rebootOlivia($iccid);
            }

            if ($type == 'eseye') {
                return self::rebootEseye($host, $iccid);
            }

            throw new Exception('Cannot reboot this SIM type!');
        } catch (Exception $e) {
            echo $e->getMessage();

            return false;
        } finally {
        }

        return false;
    }

    public static function msg($host, $sms, $wait = false, $waitTimeout = 7, $getResponse = false)
    {
        //$response_number = '353867267392';
        $response_number = '353874109020';
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
        $start_time = microtime(true);
        $res = [
            'sms' => $sms,
            'delivered' => false,
            'timed_out' => false,
            'elapsed' => 0,
            'res' => null,
        ];
        try {

            // Get SIM datalogger
            $sim = self::where('IP_Address', $host)->first();
            $dl = null;
            if ($sim) {
                $dl = $sim->dataLogger;
            }

            $now = Carbon\Carbon::parse(date('Y-m-d H:i:s'));
            $return_address = ($getResponse ? $response_number : null);
            Emnify::sendSMS($host, $sms, $return_address);

            $sim->last_sms = $sms;
            $sim->last_sms_timestamp = date('Y-m-d H:i:s');
            $sim->last_sms_status = 'unknown';
            $sim->save();

            if ($wait) {
                $start_time = microtime(true);
                $delivered = false;
                while (! $delivered) {
                    $last_sms = Emnify::getLastSMS($host);
                    if ($last_sms) {
                        $sim->last_sms = $last_sms->payload;
                        $sim->last_sms_timestamp = $last_sms->submit_date;
                        $sim->last_sms_status = $last_sms->status->description;
                        $sim->save();
                        if ($last_sms->payload == $sms && $last_sms->status->description == 'DELIVERED'
                        && (Carbon\Carbon::parse($last_sms->submit_date) >= $now)) {
                            $res['delivered'] = true;
                            if ($getResponse) {
                                $response = Emnify::getLastSMS($host, null, $type = 'MO');
                                if ($response) {
                                    if ((Carbon\Carbon::parse($response->submit_date) > $now)) {
                                        $res['response'] = $response->payload;
                                        $delivered = true;
                                        break;
                                    }
                                }
                            } else {
                                $delivered = true;
                                break;
                            }
                        }
                    }

                    $end_time = microtime(true);
                    $elapsed = ($end_time - $start_time);
                    if ($elapsed >= $waitTimeout) {
                        $res['timed_out'] = true;
                        $res['timed_out'] = $elapsed;
                        break;
                    }
                }
            }

            $res['elapsed'] = number_format(microtime(true) - $start_time, 2);

            return $res;
        } catch (Exception $e) {
            echo $e->getMessage().' ('.$e->getLine().')';
        }

        return $res;
    }

    public function getLastCommandDeliveredAttribute()
    {
        return  empty($this->last_sms_status) || ($this->last_sms_status == 'delivered');
    }

    public function getDataLoggerAttribute()
    {
        return DataLogger::where('sim_id', $this->ID)->first();
    }

    public function getSchemeAttribute()
    {
        if (! $this->dataLogger) {
            return 1;
        }

        return Scheme::find($this->dataLogger->scheme_number);
    }

    public static function rebootEmnify($host)
    {
        try {
            $scheme = self::getScheme($host);

            $res = self::msg($host, 'reboot', true, 10, true);
            $rebooted = false;
            $msg_res = '';

            if ($res['timed_out'] == false) {
                $rebooted = true;
                $msg_res = 'Reboot sent.';
                if ($scheme) {
                    $msg_res = 'Reboot sent to '.$scheme->scheme_nickname.'.';
                }
            }

            if ($res['delivered'] == true) {
                $rebooted = true;
                $msg_res = 'Reboot sent. Successfully delivered.';
                if ($scheme) {
                    $msg_res = 'Reboot sent to '.$scheme->scheme_nickname.'. Successfully delivered.';
                }
            }

            return (object) [
                'rebooted' => $rebooted,
                'msg_res' => $msg_res,
            ];
        } catch (Exception $e) {

            //die();
            //echo $e->getMessage();
            return (object) [
                'rebooted' => false,
                'res' => null,
            ];
        }

        return (object) [
            'rebooted' => false,
            'res' => null,
        ];
    }

    public static function refreshEmnify($host)
    {
        try {

            // Get SIM datalogger
            $sim = self::where('IP_Address', $host)->first();
            $dl = null;
            if ($sim) {
                $dl = $sim->dataLogger;
            }

            // Setup guzzle client
            $client = new GuzzleHttp\Client([
                'base_uri' => 'https://cdn.emnify.net/api/v1/',
                'cookies' => true,
            ]);

            // Get auth token
            $res = $client->request('POST', 'authenticate', [
                'body' => '{
					"application_token": "eyJhbGciOiJIUzUxMiJ9.eyJlc2MuYXBwc2VjcmV0IjoiOGUyYzg4ZjctNzcxZS00NTE4LThkNjQtYTQ2N2Q0NjQyNDM0Iiwic3ViIjoiYWNjb3VudHNAcHJlcGFnby5pZSIsImF1ZCI6IlwvYXBpXC92MVwvYXBwbGljYXRpb25fdG9rZW4iLCJlc2MuYXBwIjo2MDA4LCJhcGlfa2V5IjpudWxsLCJlc2MudXNlciI6MjA4Mjc0LCJlc2Mub3JnIjoxMTEzNSwiZXNjLm9yZ05hbWUiOiJQcmVwYWdvIFBsYXRmb3JtIEx0ZCIsImlzcyI6InNwYy1mcm9udGVuZDAwMUBzcGMtZnJvbnRlbmQiLCJpYXQiOjE1OTk0NzQ5OTF9.HN63jLWIS4baDg1pGsUfT2wTkDgaWDZSPLKH0MciM00T3TZED2v_SxNVsdJ_B3JHqxyf2WIPmWyk_sLvEQ4O9g"
				}',
                'headers' => [
                    //'api_key' 		=> 'Vnik2y80D6BSzdrwviZyEaEBCbGKP83aJWO4zkBm9YJrs4z5E5l4bHQH22o9',
                    'Content-Type'     	=> 'application/json',
                ],
            ]);
            $res = json_decode($res->getBody());
            $auth_token = $res->auth_token;

            // Get SIM Object from Emnify API
            $sim = Emnify::getSIM($host, $auth_token);
            if ($sim == null) {
                return (object) [
                    'rebooted' => false,
                    'res' => 'Could not find SIM using API',
                ];
            }

            // Get SIM's IP & Endpoint ID from Emnify API
            $sim_id = $sim->id;
            $endpoint_id = $sim->endpoint->id;

            // Disable the endpoint from Emnify API, then check if disable worked by checking the endpoint status
            $disable_endpoint = $client->request('PATCH', 'endpoint/'.$endpoint_id, ['body' => '{ "status": { "id": 1 } }', 'headers' => ['Authorization' 	=> 'Bearer '.$auth_token, 'Content-Type'     	=> 'application/json', 'Accept'     		=> 'application/json']]);
            $endpoint_status = Emnify::getEndpointStatus($endpoint_id, $auth_token);
            $dl->dl_status = $endpoint_status;
            $dl->save();
            if ($endpoint_status != 'Disabled') {
                return (object) [
                    'rebooted' => false,
                    'res' => 'Failed to disable endpoint',
                ];
            }

            sleep(2);

            // Enable the endpoint from Emnify API, then check if enable worked by checking the endpoint status
            $enable_endpoint = $client->request('PATCH', 'endpoint/'.$endpoint_id, ['body' => '{ "status": { "id": 0 } }', 'headers' => ['Authorization' 	=> 'Bearer '.$auth_token, 'Content-Type'     	=> 'application/json', 'Accept'     		=> 'application/json']]);
            $endpoint_status = Emnify::getEndpointStatus($endpoint_id, $auth_token);
            $dl->dl_status = $endpoint_status;
            $dl->save();
            if ($endpoint_status != 'Enabled') {
                return (object) [
                    'rebooted' => false,
                    'res' => 'Failed to enable endpoint',
                ];
            }

            sleep(2);

            // Suspend the SIM from Emnify API, then check if suspension worked by checking the SIM status
            $suspend_SIM = $client->request('PATCH', 'sim/'.$sim_id, ['body' => '{ "status": { "id": 2 } }', 'headers' => ['Authorization' 	=> 'Bearer '.$auth_token, 'Content-Type'     	=> 'application/json', 'Accept'     		=> 'application/json']]);
            $sim_status = Emnify::getSIMStatus($sim_id, $auth_token);
            $dl->dl_status = $sim_status;
            $dl->save();

            sleep(1);

            // Activate the SIM from Emnify API, then check if activation worked by checking the SIM status
            $activate_SIM = $client->request('PATCH', 'sim/'.$sim_id, ['body' => '{ "status": { "id": 1 } }', 'headers' => ['Authorization' 	=> 'Bearer '.$auth_token, 'Content-Type'     	=> 'application/json', 'Accept'     		=> 'application/json']]);
            // $sim_status = Emnify::getSIMStatus($sim_id, $auth_token);
            // $dl->dl_status = $sim_status; $dl->save();

            return (object) [
                'rebooted' => true,
                'res' => $res,
            ];

            // sleep(3);

            // $res = $client->request('PUT', '/editsim', [
                // 'body' => '{
                    // "iccid": ' . $iccid . ',
                    // "sim_state": "Active",
                    // "group": "Prepago"
                // }',
                // 'headers' => [
                    // 'api_key' 			=> 'Vnik2y80D6BSzdrwviZyEaEBCbGKP83aJWO4zkBm9YJrs4z5E5l4bHQH22o9',
                    // 'Content-Type'     	=> 'application/json',
                // ]
            // ]);

            // return ($res->getStatusCode() == 200);
        } catch (Exception $e) {
            echo $e->getMessage();

            return false;
        }

        return false;
    }

    public static function rebootEseye($host, $iccid)
    {
        try {
            $scheme = self::getScheme($host);

            if (! $scheme) {
                throw new Exception('Cannot find scheme for IP '.$host);
            }
            $EseyeConnection = EseyeConnection::establish(3);

            if (! $EseyeConnection->isLoggedIn()) {
                throw new Exception('Eseye Login: FAILED');

                return false;
            }

            $EseyeConnection->setScheme($scheme->scheme_number);

            $res = $scheme->reboot($EseyeConnection);
            $res = $res->getData();

            if (! isset($res->success)) {
                return false;
            }

            return $res->success;
        } catch (Exception $e) {
            echo $e->getMessage().' ('.$e->getLine().')';

            return false;
        }

        return false;
    }

    public static function rebootOlivia($iccid)
    {
        try {
            $client = new GuzzleHttp\Client([
                'base_uri' => 'https://api.oliviawireless.io',
                'cookies' => true,
            ]);

            $res = $client->request('PUT', '/editsim', [
                'body' => '{
					"iccid": '.$iccid.',
					"sim_state": "Paused",
					"group": "Prepago"
				}',
                'headers' => [
                    'api_key' 			=> 'Vnik2y80D6BSzdrwviZyEaEBCbGKP83aJWO4zkBm9YJrs4z5E5l4bHQH22o9',
                    'Content-Type'     	=> 'application/json',
                ],
            ]);

            sleep(3);

            $res = $client->request('PUT', '/editsim', [
                'body' => '{
					"iccid": '.$iccid.',
					"sim_state": "Active",
					"group": "Prepago"
				}',
                'headers' => [
                    'api_key' 			=> 'Vnik2y80D6BSzdrwviZyEaEBCbGKP83aJWO4zkBm9YJrs4z5E5l4bHQH22o9',
                    'Content-Type'     	=> 'application/json',
                ],
            ]);

            return $res->getStatusCode() == 200;
        } catch (Exception $e) {
            echo $e->getMessage();

            return false;
        }

        return false;
    }

    public static function getScheme($host)
    {
        $simcard = self::where('IP_Address', $host)->first();
        if ($simcard) {
            $datalogger = DataLogger::where('sim_id', $simcard->ID)->first();
            if ($datalogger) {
                $scheme = Scheme::where('scheme_number', $datalogger->scheme_number)->first();

                return $scheme;
            }
        }

        return null;
    }
}
