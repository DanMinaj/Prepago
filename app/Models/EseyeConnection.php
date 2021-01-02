<?php

namespace App\Models;


class EseyeConnection
{
    public $command_url;
    public $eseyeConnection;
    public $ICCID;
    public $IP;
    public $scheme_id;
    public $command;

    public $last_response;

    public function __construct()
    {
        $this->eseyeConnection = new GuzzleHttp\Client([
                'base_uri' => 'https://siam3.eseye.com',
                'cookies' => true,
        ]);

        $this->login();
    }

    public static function establish($attempts = 3)
    {
        $eseye = new self();

        while (! $eseye->isLoggedIn() && $attempts > 0) {
            $eseye = new self();
            $attempts--;
        }

        if ($eseye->isLoggedIn()) {
            return $eseye;
        }

        return false;
    }

    public function isLoggedIn()
    {
        $res = $this->eseyeConnection->get('https://siam3.eseye.com');

        if (empty($res) || empty($res->getBody())) {
            return false;
        }

        if (strpos($res->getBody(), 'Logout') !== false) {
            return true;
        }

        return false;
    }

    private function login()
    {
        try {
            $this->command_url = 'user/login';
            $this->command = 'login';

            $res = $this->eseyeConnection->post($this->command_url, [
                'form_params' => [
                    'username' => 'aidan62', // use your actual username
                    'password' => 'Cook1234', // use your actual password
                ],
            ]);

            $this->last_response = $res;

            return $res;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function getSMSs()
    {
        try {
            $this->command_url = 'ajax/simmessagingdetail';
            $res = $this->eseyeConnection->post($this->command_url, [
                'form_params' => [
                    'numRecs' => 5000, // use your actual username
                    'startRec' => 0, // use your actual password
                    'sdate' => '2019-01-01',
                    'edate' => date('Y-m-d'),
                    'sortOrder' => 'RD',
                    'iccid' => $this->ICCID,
                    'type' => 'TOLMA',
                ],
            ]);

            $res_body = json_decode($res->getBody());

            if (empty($res_body) || empty($res_body->recs)) {
                return [];
            }

            $recs = $res_body->recs;
            $commands = [];

            $last_entry = null;
            $last_entry_timestamp = null;

            $last_entry_sent = null;
            $last_entry_timestamp_sent = null;

            foreach ($recs as $k => $v) {
                $v->direction = trim((string) $v->direction);
                $v->status = trim($v->status);
                $v->destination = trim($v->destination);
                $v->source = trim($v->source);

                // outgoing sms
                if (($v->direction == 'MT SMS') && ($last_entry_timestamp == null || $v->timestamp > $last_entry_timestamp)) {
                    $last_entry_timestamp = $v->timestamp;
                    $last_entry = $v;
                    $last_entry->text = $v->Text;
                }

                if (($v->direction == 'MO SMS') && (! empty($v->Text)) && ($last_entry_timestamp_sent == null)) {
                    $last_entry_timestamp_sent = $v->timestamp;
                    $last_entry_sent = $v;
                    $last_entry_sent->text = $v->Text;
                }

                $v->timestamp = new DateTime($v->timestamp);
                $v->timestamp = ($v->timestamp)->format('Y-m-d H:i:s');
                //$v->timestamp = ($v->timestamp)->modify('+1 hour')->format('Y-m-d H:i:s');
                array_push($commands, (object) [
                    'timestamp' 	=> $v->timestamp,
                    'source'		=> $v->source,
                    'destination' 	=> $v->destination,
                    'MSISDN' 		=> $v->MSISDN,
                    'direction'		=> $v->direction,
                    'text'			=> $v->Text,
                    'status' 		=> $v->status,
                ]);
            }

            if (count($commands) > 0 && $last_entry != null) {
                $sim = Simcard::where('ICCID', $this->ICCID)->get();

                if ($last_entry_sent != null) {
                    $last_entry->last_sms_res = $last_entry_sent->text;
                } else {
                    $last_entry->last_sms_res = 'n/a';
                }

                foreach ($sim as $k => $s) {
                    $s->last_sms = $last_entry->text;
                    $s->last_sms_timestamp = $last_entry->timestamp;
                    $s->last_sms_res = $last_entry->last_sms_res;
                    $s->last_sms_status = $last_entry->status;
                    $s->save();
                }
            }

            $this->last_response = $res;

            $commands['last_sms'] = $last_entry;

            return $commands;
        } catch (Exception $e) {
            echo $e->getMessage().' ('.$e->getLine().')';
            die();

            return $e->getMessage();
        }
    }

    public function getEventSummary()
    {
        try {
            if (empty($this->ICCID)) {
                throw new Exception('ICCID must be set. It is currently blank!');
            }
            $this->command_url = 'sim/controlpanel/modal/1/iccid/'.$this->ICCID.'/controls/SU';
            $this->command_url = 'sim/eventsummary/modal/1/iccid/'.$this->ICCID;
            $this->command = 'event_summary';

            $res = $this->eseyeConnection->get($this->command_url);
            $res->extra = (object) [
                'ICCID' => $this->ICCID,
                'last_mcc_mnc' => '',
                'mcc_mnc' => '',
                'last_network' => '',
                'last_lai_ci' => '',
                'last_session_start' => '',
                'last_session_stop' => '',
                'last_session_total' => '',
                'last_mt_sms' => '',
                'last_mo_sms' => '',
            ];

            $res_lines = preg_split("/\r\n|\n|\r/", $res->getBody());
            $td_count = substr_count($res->getBody(), '<td class="tdfirst">');
            $cur_line = $res_lines[0];
            $prev_line = $res_lines[1];
            $network_found = false;

            foreach ($res_lines as $k => $v) {
                $cur_line = $res_lines[$k];
                if (isset($res_lines[$k - 1])) {
                    $prev_line = $res_lines[$k - 1];
                } else {
                    $prev_line = $res_lines[$k];
                }

                if (strpos($v, '<td class="tdfirst">') === false) {
                    continue;
                }
                if (strpos($v, '&nbsp;') !== false) {
                    continue;
                }

                $v = str_replace('<td class="tdfirst">', '', $v);
                $v = str_replace('</td>', '', $v);
                $v = str_replace('<br>', '', $v);

                if (strpos($prev_line, 'Last MCC / MNC') !== false) {
                    $res->extra->last_mcc_mnc = $v;
                    //continue;
                }
                if (! $network_found && strpos($v, '(') !== false) {
                    $v = str_replace('(', '', $v);
                    $v = str_replace(')', '', $v);

                    if (strpos($v, '<br/>') !== false) {
                        $parts = explode('<br/>', $v);
                        $res->extra->mcc_mnc = $parts[0];
                        $res->extra->last_network = $parts[1];
                    } else {
                        $res->extra->last_network = $v;
                    }
                    $network_found = true;
                    continue;
                }
                if (strpos($prev_line, 'Last LAI / CI') !== false) {
                    $res->extra->last_lai_ci = $v;
                    continue;
                }
                if (strpos($prev_line, 'Last Session Start') !== false) {
                    $res->extra->last_session_start = $v;
                    continue;
                }
                if (strpos($prev_line, 'Last Session Stop') !== false) {
                    $res->extra->last_session_stop = $v;
                    continue;
                }
                if (strpos($prev_line, 'Last Session Total') !== false) {
                    $res->extra->last_session_total = $v;
                    continue;
                }
                if (strpos($prev_line, 'Last MT SMS') !== false) {
                    $res->extra->last_mt_sms = $v;
                    continue;
                }
                if (strpos($prev_line, 'Last MO SMS') !== false) {
                    $res->extra->last_mo_sms = $v;
                    continue;
                }

                //echo $v . "<br/>";
            }

            $this->last_response = $res;

            return $res;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function sendCustomCommand($cmd)
    {
        try {
            if (empty($this->ICCID)) {
                throw new Exception('ICCID must be set. It is currently blank!');
            }
            $this->command = $cmd;

            $this->command_url = 'sim/controlpanel/modal/1/iccid/'.$this->ICCID.'/controls/SU';

            $res = $this->eseyeConnection->post($this->command_url, [
                'form_params' => [
                    'message' => $this->command, // use your actual username
                    'SMS' => 'Send via SMS', // use your actual password
                ],
            ]);

            $sent_time = date('Y-m-d H:i:s');
            $this->last_response = $res;

            return $res;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function sendRebootCommand()
    {
        try {
            if (empty($this->ICCID)) {
                throw new Exception('ICCID must be set. It is currently blank!');
            }
            $this->command_url = 'sim/controlpanel/modal/1/iccid/'.$this->ICCID.'/controls/SU';
            $this->command = 'reboot';

            $res = $this->eseyeConnection->post($this->command_url, [
                'form_params' => [
                    'message' => $this->command, // use your actual username
                    'SMS' => 'Send via SMS', // use your actual password
                ],
            ]);

            $sent_time = date('Y-m-d H:i:s');
            $this->last_response = $res;

            return $res;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function createTicket($subject, $comment, $type, $priority = 'high')
    {
        try {
            $this->command_url = 'support/create';
            $this->command = 'create_ticket';

            $res = $this->eseyeConnection->post($this->command_url, [
                'form_params' => [
                    'subject' => $subject,
                    'comment' => $comment, // use your actual password
                    'type' => $type, // problem, incident, question, task
                    'priority' => $priority, // low, normal, high, critical
                    'submit' => 'Create New Ticket',
                ],
            ]);

            $this->last_response = $res;

            return $res;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function getLastResponse()
    {
        $full_res = (object) [
            'scheme_id' => $this->scheme_id,
            'url' => $this->command_url,
            'command' => $this->command,
            'status' => $this->last_response->getStatusCode(),
            'header' => $this->last_response->getHeader('content-type')[0],
            'body' => $this->last_response->getBody(),
            'res' => $this->last_response,
        ];

        return $full_res;
    }

    public function setScheme($scheme_id)
    {
        try {
            $this->scheme_id = $scheme_id;

            $scheme = Scheme::find($scheme_id);
            if (! $scheme) {
                throw new Exception("Scheme id $scheme_id not found.");
            }
            $sim = $scheme->SIM;
            if (! $sim) {
                throw new Exception('Scheme SIM not found.');
            }
            $ICCID = $sim->ICCID;
            $IP = $sim->IP_Address;

            $this->ICCID = $ICCID;
            $this->IP = $IP;

            $this->getSMSs();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function setIP($IP)
    {
        try {
            $this->IP = $IP;

            $SIM = Simcard::where('IP_Address', $IP)->first();

            if (! $SIM) {
                throw new Exception("SIM with IP $IP not found.");
            }
            $this->ICCID = $SIM->ICCID;

            $sim_id = $SIM->ID;

            $dl = DataLogger::where('sim_id', $sim_id)->first();

            if (! $dl) {
                throw new Exception("No datalogger associated with the IP $IP.");
            }
            $dl_id = $dl->id;
            $scheme_id = $dl->scheme_number;
            $scheme = Scheme::find($scheme_id);

            $this->scheme_id = $scheme_id;

            if (! $scheme) {
                throw new Exception("No Scheme associated with the datalogger $dl_id");
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
