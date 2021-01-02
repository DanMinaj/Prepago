<?php

use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DailyChecklistCommand extends Command
{
    protected $name = 'daily:checklist';
    protected $description = 'Run Daily Checklist Tasks';
    protected $shutOffCustomers = null;
    protected $shutOffMeters = null;
    protected $readingShutOffMeters = null;
    protected $nonReadingMeters = null;
    protected $remoteControlErrors = null;

    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('Temporary Payments Logs');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/DailyChecklistCommand/'.date('Y-m-d').'.log'), Logger::INFO);
    }

    public function fire()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        //List Shut Off Customers
        $this->listShutOffCustomers();

        //Check Reading Shut Off Meters
        $this->checkReadingShutOffMeters();

        //Check Non Reading Meters
        $this->checkNonReadingMeters();

        //Check Remote Control Errors
        $this->checkRemoteControlErrors();

        //send daily checklist email
        $this->sendDailyChecklistEmail();
    }

    public function listShutOffCustomers()
    {
        $this->shutOffCustomers = Customer
                                    ::leftJoin('district_heating_meters', 'customers.meter_ID', '=', 'district_heating_meters.meter_ID')
                                    ->leftJoin('permanent_meter_data', 'permanent_meter_ID', '=', 'permanent_meter_data.ID')
                                    ->leftjoin('schemes', 'schemes.scheme_number', '=', 'customers.scheme_number')
                                    ->select(
                                        'permanent_meter_data.ID as permanent_meter_ID',
                                        'district_heating_meters.meter_id as district_meter_ID',
                                        'permanent_meter_data.meter_number as permanent_meter_number',
                                        'customers.id as customer_id',
                                        'customers.username as customer_username',
                                        'schemes.company_name as scheme_name',
                                        'district_heating_meters.shut_off_reading',
                                        'district_heating_meters.latest_reading as latest_reading',
                                        'district_heating_meters.last_shut_off_time'
                                    )
                                    ->where('district_heating_meters.shut_off_device_status', 1)
                                    ->whereNull('customers.deleted_at')
                                    ->where('schemes.archived', 0)
                                    ->where('schemes.scheme_number', '!=', 3)
                                    ->where('customers.ev_owner', '!=', 1)
                                    ->whereRaw('(customers.simulator = 0)')
                                    ->orderBy('customers.scheme_number')
                                    ->get();

        $this->log->addInfo('Shut Off Customers', $this->shutOffCustomers->toArray());
    }

    public function checkReadingShutOffMeters()
    {
        $permanentMeters = PermanentMeterData
            ::leftJoin('district_heating_meters', 'permanent_meter_ID', '=', 'permanent_meter_data.ID')
            ->leftJoin('customers', 'customers.meter_ID', '=', 'district_heating_meters.meter_ID')
            ->leftjoin('schemes', 'schemes.scheme_number', '=', 'permanent_meter_data.scheme_number')
            ->select(
                'permanent_meter_data.ID as permanent_meter_ID',
                'district_heating_meters.meter_id as district_meter_ID',
                'permanent_meter_data.meter_number as permanent_meter_number',
                'customers.id as customer_id',
                'customers.username as customer_username',
                'schemes.company_name as scheme_name',
                'district_heating_meters.shut_off_reading',
                'district_heating_meters.last_shut_off_time',
                'district_heating_meters.shut_off_device_status'
            )
            ->where('district_heating_meters.shut_off_device_status', 1)
            ->where('schemes.archived', 0)
            ->whereRaw('(customers.deleted_at IS NULL AND customers.simulator = 0)')
            ->where('schemes.scheme_number', '!=', 3)
            ->where('customers.ev_owner', '!=', 1)
            ->get()->toArray();

        //attach end_day_reading
        foreach ($permanentMeters as $key => $permanentMeter) {
            $dhmEndDayReading = DistrictHeatingUsage::where('customer_id', $permanentMeter['customer_id'])
                ->where('end_day_reading', '!=', -1)
                ->where('date', '=', \Carbon\Carbon::now()->subDay()->toDateString())
                ->orderBy('date', 'DESC')
                ->pluck('end_day_reading');
            if ($dhmEndDayReading !== $permanentMeter['shut_off_reading']) {
                $permanentMeters[$key]['end_day_reading'] = $dhmEndDayReading;
                $permanentMeters[$key]['usage'] = abs($dhmEndDayReading - $permanentMeter['shut_off_reading']);
            } else {
                unset($permanentMeters[$key]);
            }
        }

        $permanentMeters = \Illuminate\Support\Collection::make($permanentMeters);
        $this->readingShutOffMeters = $permanentMeters->map(function ($permanentMeter) {
            return (object) $permanentMeter;
        });

        $this->log->addInfo('Reading Shut Off Meters', $this->readingShutOffMeters->toArray());
    }

    public function checkNonReadingMeters()
    {
        /*$this->nonReadingMeters = PermanentMeterDataUnsuccessfulReadings::whereRaw("TIME(`date_time`) > '00:00:01' AND TIME(`date_time`) < '00:30:00' AND DATE(`date_time`) = CURDATE()")->get();*/

        $this->nonReadingMeters = DistrictHeatingMeter::nonReadingMeters();

        /*
            $this->nonReadingMeters = PermanentMeterDataUnsuccessfulReadings
                                        ::leftjoin('district_heating_meters', 'district_heating_meters.permanent_meter_ID', '=', 'permanent_meter_data_unsccessfull_readings.permanent_meter_id')
                                        ->leftjoin('customers', 'district_heating_meters.meter_ID', '=', 'customers.meter_ID')
                                        ->leftjoin('schemes', 'schemes.scheme_number', '=', 'customers.scheme_number')
                                        ->select(
                                            'permanent_meter_data_unsccessfull_readings.permanent_meter_id as permanent_meter_id',
                                            'district_heating_meters.meter_ID as dhm_id',
                                            'customers.id as customer_id',
                                            'customers.username as customer_username',
                                            'schemes.company_name as scheme_name'
                                        )
                                        ->whereRaw("TIME(`date_time`) > '00:00:01' AND TIME(`date_time`) < '00:30:00' AND DATE(`date_time`) = CURDATE()")
                                        ->where('schemes.archived', 0)
                                        ->where('schemes.scheme_number', '!=', 3)
                                        ->orderBy(
                                        'schemes.scheme_number', 'DESC')
                                        ->get();
                                        */

        $this->log->addInfo('Non Reading Meters', $this->nonReadingMeters);
        //SELECT a.permanent_meter_id, b.meter_ID, c.id, c.username  FROM `permanent_meter_data_unsccessfull_readings` as a, district_heating_meters as b, customers as c WHERE a.permanent_meter_id = b.permanent_meter_ID and b.meter_ID = c.meter_ID and TIME(`date_time`) > '00:00:01' AND TIME(`date_time`) < '00:30:00' AND DATE(`date_time`) = CURDATE() limit 30
    }

    public function checkRTUCommandErrors()
    {
        $this->rtuCommandErrors = RTUCommandQue
                                    ::leftjoin('schemes', 'schemes.scheme_number', '=', 'rtu_command_que.scheme_number')
                                    ->select(
                                        'customer_ID',
                                        'meter_id',
                                        'permanent_meter_id',
                                        'time_date',
                                        'rtu_command_que.scheme_number',
                                        'retries'
                                    )
                                    ->where('failed', 1)
                                    ->whereRaw('DATEDIFF(NOW(), `time_date`) < 7')
                                    ->where('schemes.archived', 0)
                                    ->where('schemes.scheme_number', '!=', 3)
                                    ->get();
        $this->log->addInfo('RTU Command Errors', $this->rtuCommandErrors->toArray());
    }

    public function checkRemoteControlErrors()
    {
        $this->remoteControlErrors = RemoteControlLogging
                                        ::leftJoin('permanent_meter_data', 'permanent_meter_id', '=', 'permanent_meter_data.ID')
                                        ->leftjoin('schemes', 'schemes.scheme_number', '=', 'permanent_meter_data.scheme_number')
                                        ->select(
                                            'permanent_meter_id',
                                            'date_time',
                                            'action',
                                            'error'
                                        )
                                        ->where('error', '!=', '')
                                        ->whereRaw('`date_time` > DATE_SUB(now(), INTERVAL 7 DAY)')
                                        ->where('schemes.archived', 0)
                                        ->where('schemes.scheme_number', '!=', 3)
                                        ->get();
        $this->log->addInfo('Remote Control Errors', $this->remoteControlErrors->toArray());
    }

    public function sendDailyChecklistEmail()
    {
        $testOnly = false;

        $emailInfo = [];

        $recipients = preg_split('/\r\n|\r|\n/', SystemSetting::get('daily_checklist_email_recipients'));

        if ($testOnly) {
            $emailInfo['email_addresses'] = ['daniel@prepago.ie'];
        } else {
            $emailInfo['email_addresses'] = $recipients;
        }
        //$emailInfo['email_addresses'] = ['mariana.bozduganova@gmail.com'];

        $data = [];
        $data['shutOffCustomers'] = $this->shutOffCustomers;
        $data['readingShutOffMeters'] = $this->readingShutOffMeters;
        $data['nonReadingMeters'] = $this->nonReadingMeters;
        $data['remoteControlErrors'] = $this->remoteControlErrors;

        //return Mail::send('emails.customer_set_up', [], function($message) use ($emailInfo) {
        return Mail::send('emails.daily_checklist', $data, function ($message) use ($emailInfo) {
            $message->from('mariana.bozduganova@gmail.com')->subject('Daily Checklist - '.date('Y-m-d', strtotime('yesterday')));
            $message->to($emailInfo['email_addresses']);
        });
    }

    protected function getArguments()
    {
        return [];
    }

    protected function getOptions()
    {
        return [];
    }
}
