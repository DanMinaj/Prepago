<?php

use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CopyManualMeterReadingsToMeterReadingsAllTable extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'meters:copymanualreadings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('Automated Meters Readings');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/CopyManualReadings/'.date('Y-m-d').'.log'), Logger::INFO);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $readingsToEmail = new \Illuminate\Support\Collection();

        foreach (PermanentMeterDataMeterReadWebsite::unprocessed()->orderBy('scheme_number')->orderBy('ID')->get() as $reading) {
            if ($reading->isReadyForProcessing()) {
                if ($reading->belongsToUserWithReadingsAutomationPermissions()) {
                    $readingsToEmail->push($reading);
                }

                $this->log->addInfo('Reading processing initiated', ['reading_id' => $reading->ID]);
                $reading->process();
                $this->log->addInfo('Reading processed successfully', ['reading_id' => $reading->ID]);
            }
        }

        if ($readingsToEmail->count()) {
            $this->log->addInfo('Sending Automated Readings Emails initiated');
            $this->sendAutomatedReadingsEmails($readingsToEmail->groupBy('automated_by_user_ID'));
            $this->log->addInfo('Automated Readings Emails sent successfully');
        }
    }

    private function sendAutomatedReadingsEmails($usersReadings)
    {
        foreach ($usersReadings as $userID => $readings) {
            $user = User::find($userID);

            $emailInfo = [];
            $emailInfo['email_addresses'] = ['mariana.bozduganova@gmail.com', 'aidan@prepago.ie'];
            if ($user->email_address) {
                array_unshift($emailInfo['email_addresses'], $user->email_address);
            }
            $this->log->addInfo('Sending Automated Readings Emails to', $emailInfo);

            try {
                $data = $this->prepareEmailInformation($user, $readings);
            } catch (Exception $e) {
                $this->log->addInfo('Error when preparing Email information - '.$e->getMessage());
            }

            Mail::send('emails.automated_readings', $data, function ($message) use ($emailInfo) {
                $message->from('aidan@prepago.ie')->subject('Automated Meters Readings');
                $message->to($emailInfo['email_addresses']);
            });
        }
    }

    private function prepareEmailInformation($user, $readings)
    {
        $data = [];

        $data['user_name'] = $user->employee_name;
        $data['readings'] = new \Illuminate\Support\Collection();

        foreach ($readings as $reading) {
            $this->log->addInfo('Prepare Email Information - reading id '.$reading->ID);
            $pm = PermanentMeterData::find($reading->permanent_meter_id);
            $scheme = Scheme::where('scheme_number', $reading->scheme_number)->first();
            $customer = DistrictHeatingMeter::where('permanent_meter_ID', $reading->permanent_meter_id)->first()->customers;

            $this->log->addInfo('Prepare Email Information - customer first and last name - '.($customer ? $customer->first_name.' '.$customer->surname : ''));
            $this->log->addInfo('Prepare Email Information - customer email - '.($customer ? $customer->email_address : ''));
            $this->log->addInfo('Prepare Email Information - pm meter number - '.($pm ? $pm->meter_number : ''));
            $this->log->addInfo('Prepare Email Information - reading  - '.($reading ? $reading->reading : ''));
            $this->log->addInfo('Prepare Email Information - scheme company name  - '.($scheme && $scheme->company_name ?: $scheme->scheme_nickname));
            $this->log->addInfo('Prepare Email Information - reading_status  - '.($reading && $reading->complete == 1 ? 'successful' : 'unsuccessful'));

            $data['readings']->push([
                'customer_name' => $customer ? $customer->first_name.' '.$customer->surname : '',
                'customer_email' => $customer ? $customer->email_address : '',
                'meter_number' => $pm ? $pm->meter_number : '',
                'reading' => $reading ? $reading->reading : '',
                'scheme' => $scheme && $scheme->company_name ?: $scheme->scheme_nickname,
                'reading_status' => $reading && $reading->complete == 1 ? 'successful' : 'unsuccessful',
            ]);
        }

        $data['readings'] = $data['readings']->sortBy('reading_status');

        return $data;
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
