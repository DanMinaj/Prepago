<?php

namespace App\Models;


class Emnify
{
    public static function getSIM($IP, $auth_token = null)
    {
        try {
            $client = new GuzzleHttp\Client([
                    'base_uri' => 'https://cdn.emnify.net/api/v1/',
                    'cookies' => true,
            ]);

            if ($auth_token == null) {
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
            }

            $sim_data = $client->request('GET', 'sim', ['headers' => ['Authorization' 	=> 'Bearer '.$auth_token, 'Content-Type'     	=> 'application/json', 'Accept'     		=> 'application/json']]);
            $sim_data_parsed = json_decode($sim_data->getBody());
            $sim = null;

            foreach ($sim_data_parsed as $k => $s) {
                if (! isset($s->endpoint)) {
                    continue;
                }
                if ($s->endpoint->ip_address == $IP) {
                    $sim = $s;
                }
            }

            return $sim;
        } catch (Exception $e) {
        }

        return null;
    }

    public static function getSIM_ICCID($ICCID, $auth_token = null)
    {
        try {
            $client = new GuzzleHttp\Client([
                    'base_uri' => 'https://cdn.emnify.net/api/v1/',
                    'cookies' => true,
            ]);

            if ($auth_token == null) {
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
            }

            $sim_data = $client->request('GET', 'sim', ['headers' => ['Authorization' 	=> 'Bearer '.$auth_token, 'Content-Type'     	=> 'application/json', 'Accept'     		=> 'application/json']]);
            $sim_data_parsed = json_decode($sim_data->getBody());
            $sims = [];

            foreach ($sim_data_parsed as $k => $s) {
                $icci = $s->iccid;
                if (strpos($icci, $ICCID) !== false) {
                    array_push($sims, $s);
                }
            }

            return $sims;
        } catch (Exception $e) {
        }

        return [];
    }

    public static function activateSIM_ICCID($ICCID, $name, $auth_token = null)
    {
        try {
            $client = new GuzzleHttp\Client([
                    'base_uri' => 'https://cdn.emnify.net/api/v1/',
                    'cookies' => true,
            ]);

            if ($auth_token == null) {
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
            }

            $sims = self::getSIM_ICCID($ICCID, $auth_token);
            if (count($sims) > 1) {
                //die('Cannot find SIM ' . $ICCID);
                return null;
            }

            $sim = $sims[0];

            if (empty($sim->endpoint)) {
                $activate_sim_res = $client->request('POST', 'endpoint', ['body' => '{
						"name": "'.$name.'",
						"status": {
							"id": 0
						},
						"sim": {
							"id": '.$sim->id.',
							"activate": 1
						},
						"service_profile": {
							"id": 403356
						},
						"tariff_profile": {
							"id": 392014
						}
					}',
                    'headers' => [
                    'Authorization' 	=> 'Bearer '.$auth_token,
                    'Content-Type'=> 'application/json', 'Accept' => 'application/json',
                    ],
                ]);

                $status_code = $activate_sim_res->getStatusCode();

                if ($status_code == 201) {
                    return self::getSIM_ICCID($ICCID);
                }
            } else {
                $res = self::modifyEndpoint($sim->endpoint->id, [
                    'name' => '"'.$name.'"',
                ], $auth_token);
                if ($res) {
                    return self::getSIM_ICCID($ICCID);
                }
            }

            return null;
        } catch (Exception $e) {
            echo $e->getMessage().' 	('.$e->getLine().')';
        }

        return null;
    }

    public static function deactivateSIM_ICCID($ICCID, $auth_token = null)
    {
        try {
            $client = new GuzzleHttp\Client([
                'base_uri' => 'https://cdn.emnify.net/api/v1/',
                'cookies' => true,
            ]);

            if ($auth_token == null) {
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
            }

            $sims = self::getSIM_ICCID($ICCID);
            if (count($sims) > 1) {
                return null;
            }

            $sim = $sims[0];

            $endpoint = $sim->endpoint;
            $endpoint_id = -1;

            if (empty($endpoint)) {
                return false;
            }

            $endpoint_id = $endpoint->id;

            // $res = Emnify::modifyEndpoint($sim->endpoint->id, [
            // 'status' => '{"id": 1, "description": "DISABLED"}'
            // ], $auth_token);
            $res = self::modifyEndpoint($sim->endpoint->id, [
            'sim' => '{"id": null}',
            ], $auth_token);

            $delete_endpoint_res = $client->request('DELETE', 'endpoint/'.$endpoint_id, [
                'headers' => [
                'Authorization' 	=> 'Bearer '.$auth_token,
                'Content-Type'=> 'application/json', 'Accept' => 'application/json',
                ],
            ]);

            $status_code = $delete_endpoint_res->getStatusCode();

            $deactivate_sim_res = $client->request('PATCH', 'sim/'.$sim->id, [
                'body' => '{
					"status": {
						"id": 2
					}
				}',
                'headers' => [
                'Authorization' 	=> 'Bearer '.$auth_token,
                'Content-Type'=> 'application/json', 'Accept' => 'application/json',
                ],
            ]);
            $status_code2 = $deactivate_sim_res->getStatusCode();

            if ($status_code == 204 && $status_code2 == 204) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return false;
    }

    public static function deactivateSIM_IP($IP, $auth_token = null)
    {
        try {
            $client = new GuzzleHttp\Client([
                'base_uri' => 'https://cdn.emnify.net/api/v1/',
                'cookies' => true,
            ]);

            if ($auth_token == null) {
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
            }

            $sim = self::getSIM($IP);
            if (! $sim) {
                return null;
            }

            $endpoint = $sim->endpoint;
            $endpoint_id = -1;

            if (empty($endpoint)) {
                return false;
            }

            $endpoint_id = $endpoint->id;

            // $res = Emnify::modifyEndpoint($sim->endpoint->id, [
            // 'status' => '{"id": 1, "description": "DISABLED"}'
            // ], $auth_token);
            $res = self::modifyEndpoint($sim->endpoint->id, [
            'sim' => '{"id": null}',
            ], $auth_token);

            $delete_endpoint_res = $client->request('DELETE', 'endpoint/'.$endpoint_id, [
                'headers' => [
                'Authorization' 	=> 'Bearer '.$auth_token,
                'Content-Type'=> 'application/json', 'Accept' => 'application/json',
                ],
            ]);

            $status_code = $delete_endpoint_res->getStatusCode();

            $deactivate_sim_res = $client->request('PATCH', 'sim/'.$sim->id, [
                'body' => '{
					"status": {
						"id": 2
					}
				}',
                'headers' => [
                'Authorization' 	=> 'Bearer '.$auth_token,
                'Content-Type'=> 'application/json', 'Accept' => 'application/json',
                ],
            ]);
            $status_code2 = $deactivate_sim_res->getStatusCode();

            if ($status_code == 204 && $status_code2 == 204) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return false;
    }

    public static function modifyEndpoint($endpoint_id, $vals = [], $auth_token = null)
    {
        try {
            $client = new GuzzleHttp\Client([
                    'base_uri' => 'https://cdn.emnify.net/api/v1/',
                    'cookies' => true,
            ]);

            if ($auth_token == null) {
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
            }

            $edit_array = '{';
            foreach ($vals as $k => $v) {
                $edit_array .= "\"$k\": $v,";
            }
            $edit_array = rtrim($edit_array, ',');
            $edit_array .= '}';

            //echo $edit_array; die();

            $edit_endpoint_res = $client->request('PATCH', 'endpoint/'.$endpoint_id, ['body' => "$edit_array",
                'headers' => [
                'Authorization' 	=> 'Bearer '.$auth_token,
                'Content-Type'=> 'application/json', 'Accept' => 'application/json',
                ],
            ]);

            $status_code = $edit_endpoint_res->getStatusCode();

            if ($status_code == 204) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            echo $e->getMessage().' 	('.$e->getLine().')';
        }

        return false;
    }

    public static function getEndpointStatus($endpoint_id, $auth_token = null)
    {
        try {
            $client = new GuzzleHttp\Client([
                    'base_uri' => 'https://cdn.emnify.net/api/v1/',
                    'cookies' => true,
            ]);

            if ($auth_token == null) {
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
            }

            $endpoint_status = $client->request('GET', 'endpoint/'.$endpoint_id, ['headers' => ['Authorization' 	=> 'Bearer '.$auth_token, 'Content-Type'     	=> 'application/json', 'Accept'     		=> 'application/json']]);
            $endpoint_status_parsed = json_decode($endpoint_status->getBody());
            $status = $endpoint_status_parsed->status->description;

            return $status;
        } catch (Exception $e) {
        }

        return null;
    }

    public static function getSIMStatus($sim_id, $auth_token = null)
    {
        try {
            $client = new GuzzleHttp\Client([
                    'base_uri' => 'https://cdn.emnify.net/api/v1/',
                    'cookies' => true,
            ]);

            if ($auth_token == null) {
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
            }

            $endpoint_status = $client->request('GET', 'sim/'.$sim_id, ['headers' => ['Authorization' 	=> 'Bearer '.$auth_token, 'Content-Type'     	=> 'application/json', 'Accept'     		=> 'application/json']]);
            $endpoint_status_parsed = json_decode($endpoint_status->getBody());
            $status = $endpoint_status_parsed->status->description;

            return $status;
        } catch (Exception $e) {
        }

        return null;
    }

    public static function sendSMS($IP, $sms, $return_address = null, $auth_token = null)
    {
        try {
            $client = new GuzzleHttp\Client([
                    'base_uri' => 'https://cdn.emnify.net/api/v1/',
                    'cookies' => true,
            ]);

            if ($auth_token == null) {
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
            }

            $sim = self::getSIM($IP, $auth_token);
            $sms_res = [];
            if ($sim == null) {
                return;
            }

            if ($return_address != null) {
                $sms_res = $client->request('POST', 'endpoint/'.$sim->endpoint->id.'/sms', ['body' => '{
					"payload": "'.$sms.'",
					"source_address": "'.$return_address.'"
				}',
                'headers' => ['Authorization' 	=> 'Bearer '.$auth_token, 'Content-Type'     	=> 'application/json', 'Accept'     		=> 'application/json'], ]);
            } else {
                $sms_res = $client->request('POST', 'endpoint/'.$sim->endpoint->id.'/sms', ['body' => '{
					"payload": "'.$sms.'"
				}',
                'headers' => ['Authorization' 	=> 'Bearer '.$auth_token, 'Content-Type'     	=> 'application/json', 'Accept'     		=> 'application/json'], ]);
            }

            $status_code = $sms_res->getStatusCode();

            if ($status_code == 201) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return false;
    }

    public static function getLastSMS($IP, $delim = null, $type = 'MT', $auth_token = null)
    {
        try {
            $client = new GuzzleHttp\Client([
                    'base_uri' => 'https://cdn.emnify.net/api/v1/',
                    'cookies' => true,
            ]);

            if ($auth_token == null) {
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
            }

            $sim = self::getSIM($IP, $auth_token);
            $sms_res = [];
            if ($sim == null) {
                return false;
            }

            $sms_res = $client->request('GET', 'endpoint/'.$sim->endpoint->id.'/sms', [
            'headers' => ['Authorization' 	=> 'Bearer '.$auth_token, 'Content-Type'     	=> 'application/json', 'Accept'     		=> 'application/json'], ]);

            $sms_res_parsed = json_decode($sms_res->getBody());

            $found = false;
            $found_sms = false;

            //$sms_res_parsed = array_reverse($sms_res_parsed);

            foreach ($sms_res_parsed as $k => $sms) {
                if ($sms->sms_type->description != $type) {
                    continue;
                }
                if ($delim) {
                    if ($sms->payload == $delim) {
                        $found = true;
                        $found_sms = $sms;
                        break;
                    }
                } else {
                    $found_sms = $sms;
                    $found = true;
                    break;
                }
            }

            $found_sms->submit_date = (Carbon\Carbon::parse($found_sms->submit_date)->addHour())->format('Y-m-d H:i:s');
            $found_sms->delivery_date = (Carbon\Carbon::parse($found_sms->delivery_date)->addHour())->format('Y-m-d H:i:s');
            $found_sms->expiry_date = (Carbon\Carbon::parse($found_sms->expiry_date)->addHour())->format('Y-m-d H:i:s');
            $found_sms->final_date = (Carbon\Carbon::parse($found_sms->final_date)->addHour())->format('Y-m-d H:i:s');

            //echo $found_sms->submit_date . "<br/>";
            //echo $found_sms->delivery_date . "<br/>";
            //echo $found_sms->expiry_date . "<br/>";
            //echo $found_sms->final_date . "<br/>";

            return $found_sms;
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return false;
    }

    public static function getBlacklist($IP, $auth_token = null)
    {
        try {
            $client = new GuzzleHttp\Client([
                    'base_uri' => 'https://cdn.emnify.net/api/v1/',
                    'cookies' => true,
            ]);

            if ($auth_token == null) {
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
            }

            $sim = self::getSIM($IP, $auth_token);
            $blacklist_res = [];
            if ($sim == null) {
                return [];
            }

            $blacklist_res = $client->request('GET', 'endpoint/'.$sim->endpoint->id.'/operator_blacklist', [
            'headers' => ['Authorization' 	=> 'Bearer '.$auth_token, 'Content-Type'     	=> 'application/json', 'Accept'     		=> 'application/json'], ]);

            $blacklist_res_parsed = json_decode($blacklist_res->getBody());
            //$sms_res_parsed = array_reverse($sms_res_parsed);

            return $blacklist_res_parsed;
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return [];
    }

    public static function editBlacklist($IP, $name = '', $type = 'add', $auth_token = null)
    {
        try {
            $client = new GuzzleHttp\Client([
                    'base_uri' => 'https://cdn.emnify.net/api/v1/',
                    'cookies' => true,
            ]);

            if ($auth_token == null) {
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
            }

            $sim = self::getSIM($IP, $auth_token);
            $command_res = [];
            if ($sim == null) {
                return false;
            }

            $operator = self::getOperators($name);
            $operator_id = $operator->id;

            // add operator
            if ($type == 'add') {
                $command_res = $client->request('PUT', 'endpoint/'.$sim->endpoint->id.'/operator_blacklist/'.$operator_id, [
                'headers' => ['Authorization' 	=> 'Bearer '.$auth_token, 'Content-Type'     	=> 'application/json',
                'Accept' => 'application/json', ], ]);
            } else {
                $command_res = $client->request('DELETE', 'endpoint/'.$sim->endpoint->id.'/operator_blacklist/'.$operator_id, [
                'headers' => ['Authorization' 	=> 'Bearer '.$auth_token, 'Content-Type'     	=> 'application/json',
                'Accept' => 'application/json', ], ]);
            }

            // 204 = good

            // echo $command_res->getStatusCode() . "<br/>";
            // $res = json_decode($command_res->getBody());
            // var_dump($res);

            return true;
        } catch (Exception $e) {
            //echo $e->getMessage();
        }

        return false;
    }

    public static function getOperators($name = null)
    {
        $operator_ids = [
            '1027' => (object) [
                'name' => 'Liffey Telecom',
                'id' => '1027',
            ],
            '1174' => (object) [
                'name' => 'LGI',
                'id' => '1174',
            ],
            '671' => (object) [
                'name' => '3',
                'id' => '671',
            ],
            '670' => (object) [
                'name' => 'eir',
                'id' => '670',
            ],
            '58' => (object) [
                'name' => 'Vodafone',
                'id' => '58',
            ],
        ];

        if ($name == null) {
            return $operator_ids;
        }

        if ($name != null) {
            foreach ($operator_ids as $k => $v) {
                if ($k == $name || strpos(strtolower($v->name), strtolower($name)) !== false) {
                    return $operator_ids[$k];
                }
            }
        }
    }

    public static function getSMSList($IP, $delim = null, $auth_token = null)
    {
        try {
            $client = new GuzzleHttp\Client([
                    'base_uri' => 'https://cdn.emnify.net/api/v1/',
                    'cookies' => true,
            ]);

            if ($auth_token == null) {
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
            }

            $sim = self::getSIM($IP, $auth_token);
            $sms_res = [];
            if ($sim == null) {
                return [];
            }

            $sms_res = $client->request('GET', 'endpoint/'.$sim->endpoint->id.'/sms', [
            'headers' => ['Authorization' 	=> 'Bearer '.$auth_token, 'Content-Type'     	=> 'application/json', 'Accept'     		=> 'application/json'], ]);

            $sms_res_parsed = json_decode($sms_res->getBody());
            //$sms_res_parsed = array_reverse($sms_res_parsed);

            foreach ($sms_res_parsed as $k => $sms) {
                $sms->submit_date = (Carbon\Carbon::parse($sms->submit_date)->addHour())->format('Y-m-d H:i:s');
                $sms->delivery_date = (Carbon\Carbon::parse($sms->delivery_date)->addHour())->format('Y-m-d H:i:s');
                $sms->expiry_date = (Carbon\Carbon::parse($sms->expiry_date)->addHour())->format('Y-m-d H:i:s');
                $sms->final_date = (Carbon\Carbon::parse($sms->final_date)->addHour())->format('Y-m-d H:i:s');
            }

            return $sms_res_parsed;
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return [];
    }
}
