<?php

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class SIMController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function ping_sims_index()
    {
        $schemes = Scheme::where('archived', 0)->where('status_debug', 0)->orderBy('scheme_number', 'DESC')->get();

        foreach ($schemes as $key => $val) {
            if (! $val->SIM || $val->SIM == null) {
                $schemes->forget($key);
            }
            if ($val->scheme_number == 15 || $val->scheme_number == 23 || $val->scheme_number == 25) {
                $schemes->forget($key);
            }
        }

        $this->layout->page = view('settings.scheme_ping_new', [
            'schemes' => $schemes,
        ]);
    }

    public function sim_status_html($scheme_id, $update_status = true)
    {
        $scheme = Scheme::find($scheme_id);

        if (! $scheme) {
            return Response::json([
                'error' => 'Scheme '.$scheme_id.' not found!',
            ]);
        }

        $IP = $scheme->SIM->IP_Address;

        $res = null;

        $online = Simcard::online($IP, 4, $update_status, $scheme_id);

        if ($online) {
            $res = "<font color='#66CD00'>Sim Online!</font>";
        } else {
            $res = "<font color='#FF0000'>Sim Offline!</font>";
        }

        return $res;
    }

    public function send_custom_cmd()
    {
        try {
            $command = Input::get('command');
            $scheme = Scheme::find(Input::get('scheme'));

            $eseye = new EseyeConnection();
            $eseye->setScheme($scheme->scheme_number);
            $res = $eseye->sendCustomCommand($command);

            $res = $eseye->getSMSs();
            $last_sms = $res['last_sms'];

            if (! empty($last_sms)) {
                return Response::json($last_sms);
            } else {
                return 'last_sms_unset';
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function get_schemes_sms($iccid)
    {
        $sim = Simcard::where('ICCID', $iccid)->first();
        $ip = $sim->IP_Address;

        $eseye = new EseyeConnection();
        $eseye->setIP($ip);

        $sms = $eseye->getSMSs();

        $scheme = $sim->scheme;

        $this->layout->page = view('settings.scheme_sms', [
            'sms' => $sms,
            'iccid' => $iccid,
            'scheme' => $scheme,
        ]);
    }

    public function create_eseye_ticket()
    {

        /*
            'subject' => $subject,
                    'comment' => $comment, // use your actual password
                    'type' => $type, // problem, incident, question, task
                    'priority' => $priority, // low, normal, high, critical
                    'submit' => "Create New Ticket",
        */

        $res = [];
        $body = null;

        try {
            $subject = Input::get('subject');
            $comment = Input::get('comment');
            $type = Input::get('type');
            $priority = Input::get('priority');

            if (empty($subject)) {
                throw new Exception('Please fill in a subject!');
            }

            if (empty($comment)) {
                throw new Exception('Please fill in a comment!');
            }

            if (empty($type)) {
                throw new Exception('Please fill in a type!');
            }

            if (empty($priority)) {
                throw new Exception('Please fill in a priority!');
            }

            //throw new Exception("Temporarily disabled!");

            $eseye = new EseyeConnection();
            $res = $eseye->createTicket($subject, $comment, $type, $priority);

            if (! empty($res)) {
                $body = $res->getBody();
            }

            return Response::json([
                'success' => 'Successfully created Eseye Ticket',
                'res' => $res,
                'body' => $body,
            ]);
        } catch (Exception $e) {
            return Response::json([
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function ping_SIM()
    {
        try {
            ini_set('max_execution_time', '300');

            $IP = Input::get('IP');

            $SIM = Simcard::where('IP_Address', $IP)->first();

            if (empty($IP)) {
                throw new Exception("Please specify the SIM's IP!");
            }
            if (! $SIM) {
                $SIM = new Simcard();
                $SIM->IP_Address = $IP;
                //throw new Exception("SIM with IP $IP not found!");
            }

            $scheme = $SIM->scheme;
            $scheme_number = 1;

            // if(!$scheme) {
            // throw new Exception("Cannot find a scheme associated with this SIM!");
            // }

            if (! $scheme || empty($scheme->scheme_number)) {
                $scheme_number = 1;
            } else {
                $scheme_number = $scheme->scheme_number;
            }

            $isOnline = Simcard::online($SIM->IP_Address, 6, true, $scheme_number);

            return Response::json([
                'online' => (($isOnline) ? 1 : 0),
            ]);
        } catch (Exception $e) {
            return Response::json([
                'error' => $e->getMessage().' ('.$e->getLine().')',
            ]);
        }
    }

    public function reboot_SIM()
    {
        try {
            ini_set('max_execution_time', '300');

            $IP = Input::get('IP');
            $type = Input::get('type');
            $endpoint_id = Input::get('endpoint_id');
            $sim_id = Input::get('sim_id');

            if (empty($type)) {
                $type = 'emnify';
            }

            $SIM = Simcard::where('IP_Address', $IP)->first();

            if (empty($IP)) {
                throw new Exception("Please specify the SIM's IP!");
            }
            if (! $SIM) {
                $SIM = new Simcard();
                $SIM->IP_Address = $IP;
                //throw new Exception("SIM with IP $IP not found!");
            }

            $scheme = $SIM->scheme;
            $scheme_number = 1;

            // if(!$scheme) {
            // throw new Exception("Cannot find a scheme associated with this SIM!");
            // }

            if (! $scheme || empty($scheme->scheme_number)) {
                $scheme_number = 1;
            } else {
                $scheme_number = $scheme->scheme_number;
            }

            $SIM = Simcard::where('IP_Address', $IP)->first();

            if (! $SIM) {
                return false;
            }

            $iccid = $SIM->ICCID;

            $res = null;
            $rebooted = false;
            $success_rebooted_msg = 'Successfully rebooted SIM!';
            $failed_rebooted_msg = 'Failed to reboot SIM';
            $reboot_msg = '';

            switch ($type) {
                case 'emnify':
                    $res = Simcard::rebootEmnify($IP);
                    $rebooted = ($res->rebooted == true);
                    $success_rebooted_msg = $res->msg_res;
                break;
                case 'eseye':
                    $res = Simcard::rebootEseye($IP, $iccid);
                    $rebooted = $res;
                break;
                case 'oliviawireless':
                    $res = Simcard::rebootOlivia($iccid);
                    $rebooted = $res;
                break;
            }

            $reboot_msg = ($rebooted) ? $success_rebooted_msg : $failed_rebooted_msg;

            if ($rebooted && $scheme) {
                $scheme->status_last_reboot = date('Y-m-d H:i:s');
                $scheme->save();
            }

            return Response::json([
                'rebooted' => ($rebooted) ? 1 : 0,
                'rebooted_msg' => $reboot_msg,
                'full_res' => $res,
            ]);
        } catch (Exception $e) {
            return Response::json([
                'error' => $e->getMessage().' ('.$e->getLine().')',
            ]);
        }
    }

    public function msg_SIM()
    {
        try {
            $IP = Input::get('IP');
            $sms = Input::get('sms');
            $wait = (Input::get('wait') == 'true' || Input::get('getResponse') == 'true');
            $waitTimeout = Input::get('waitTimeout');
            $getResponse = (Input::get('getResponse') == 'true');

            $SIM = Simcard::where('IP_Address', $IP)->first();

            if (empty($IP)) {
                throw new Exception("Please specify the SIM's IP!");
            }
            if (! $SIM) {
                $SIM = new Simcard();
                $SIM->IP_Address = $IP;
                throw new Exception("SIM with IP $IP not found!");
            }

            $res = Simcard::msg($IP, $sms, $wait, $waitTimeout, $getResponse);

            return Response::json($res);
        } catch (Exception $e) {
            return Response::json([
                'error' => $e->getMessage().' ('.$e->getLine().')',
            ]);
        }
    }

    public function get_sims_log()
    {
        try {
            $scheme_number = Input::get('scheme_number');
            $ip = Input::get('ip');

            $scheme = Scheme::find($scheme_number);

            if (! $scheme) {
                $sim = Simcard::where('ip_address', $ip)->first();
                if (! $sim) {
                    throw new Exception('There is no SIM');
                }
                $scheme = $sim->scheme;
            }

            if (! $scheme) {
                throw new Exception('Cannot find scheme for this SIM');
            }
            $logs = $scheme->statusLogs;

            return Response::json([
                'logs' => $logs,
            ]);
        } catch (Exception $e) {
            return Response::json([
                'error' => $e->getMessage().' ('.$e->getLine().')',
            ]);
        }
    }

    public function grab_setup_SIM()
    {
        try {
            $iccid = Input::get('iccid');

            $sims = Emnify::getSIM_ICCID($iccid);

            if (count($sims) > 1) {
                throw new Exception("Please enter a more indepth ICCID as there are multiple SIMS with an ICCID containing $iccid");
            }
            if (count($sims) == 0) {
                throw new Exception("Could not find any SIMs with ICCID '$iccid'");
            }
            $sim = $sims[0];

            return Response::json([
                'success' => 'Grabbed SIM #'.$sim->id.'. Ready to be assigned to a scheme.',
                'sim' => $sim,
            ]);
        } catch (Exception $e) {
            return Response::json([
                'error' => $e->getMessage().' ('.$e->getLine().')',
            ]);
        }
    }

    public function assign_setup_SIM()
    {
        try {
            $iccid = Input::get('iccid');
            $scheme_number = Input::get('scheme_number');

            $scheme = Scheme::find($scheme_number);

            if (! $scheme) {
                throw new Exception("Cannot find scheme #$scheme_number");
            }
            $sim = Emnify::activateSIM_ICCID($iccid, ucfirst($scheme->scheme_nickname));

            if (! $sim || ! $sim[0]) {
                throw new Exception("Could not activate SIM ICCID $iccid");
            }
            $sim = $sim[0];

            if (empty($sim->endpoint)) {
                throw new Exception('Could not create SIM ICCID endpoint');
            }
            $ip_address = $sim->endpoint->ip_address;

            if (strlen($ip_address) <= 2) {
                throw new Exception('Could not retrieve SIM IP Address');
            }
            $SIM = $scheme->SIM;

            if (! $SIM) {
                $SIM = new Simcard();
                $SIM->Name = ucfirst($scheme->scheme_nickname).' Official';
            } else {
                try {
                    $old_SIM = Emnify::deactivateSIM_IP($SIM->IP_Address);
                } catch (Exception $d) {
                }
            }

            $SIM->ICCID = $sim->iccid;
            $SIM->MSISDN = $sim->msisdn;
            $SIM->IP_Address = $ip_address;
            $SIM->save();

            return Response::json([
                'success' => "Successfully assigned SIM with IP address '$ip_address' to ".ucfirst($scheme->scheme_nickname),
                'assigned' => 1,
            ]);
        } catch (Exception $e) {
            return Response::json([
                'error' => $e->getMessage().' ('.$e->getLine().')',
            ]);
        }
    }
}
