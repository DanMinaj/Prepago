<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class TestIPNController extends Controller
{
    private $log;

    public function __construct()
    {
        $this->log = new Logger('View Paypal IPN Logs');
        $this->log->pushHandler(new StreamHandler(storage_path('logs/pp_IPN_'.date('Y-m-d').'.log'), Logger::INFO));
    }

    public function index()
    {
        //log paypal data
        //$view_log = new Logger('View Paypal IPN Logs');
        //$view_log->pushHandler(new StreamHandler(storage_path('logs/pp_IPN' . date('Y-m-d') . '.log'), Logger::INFO));
        $this->log->addInfo('Paypal Preapproval IPN', Input::all());

        $paypalData = Input::all();

        //$preapproval_key = Input::get('preapproval_key');
        $preapproval_status = Input::get('status');

        if ($preapproval_status === 'CANCELED') {
            $this->cancelPreapproval($paypalData);
        } elseif ($preapproval_status === 'ACTIVE') {
            $this->confirmPreapproval($paypalData);
        }
    }

    private function cancelPreapproval($data)
    {
        $this->log->addInfo('Paypal Preapproval IPN CANCEL PREAPPROVAL', ['preapproval_key' => $data['preapproval_key']]);
    }

    private function confirmPreapproval($data)
    {
        $this->log->addInfo('Paypal Preapproval IPN CONFIRM PREAPPROVAL', ['preapproval_key' => $data['preapproval_key']]);
    }
}
