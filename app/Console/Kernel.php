<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\Inspire',
        'App\Console\Commands\Backup Tasks\BackupCustomers',
        'App\Console\Commands\Backup Tasks\BackupDatabase',
        'App\Console\Commands\Backup Tasks\MarkCustomerBalance',
        'App\Console\Commands\Daily Tasks\DHMInvalidReadingsDayCounter',
        'App\Console\Commands\Daily Tasks\DailyChecklistCommand',
        'App\Console\Commands\Daily Tasks\FixCommencementDate',
        'App\Console\Commands\Daily Tasks\ZeroStartDayReadings',
        'App\Console\Commands\Disabled\CarlinnDailyReport',
        'App\Console\Commands\Disabled\CheckPermanentMeterReadingsEvery2Hours',
        'App\Console\Commands\Disabled\MoveTempPaymentsCommand',
        'App\Console\Commands\Disabled\RemoveCarlinnDuplicateUsageRecords',
        'App\Console\Commands\End of day Tasks\BillingForecastCommand',
        'App\Console\Commands\End of day Tasks\CacheUsage',
        'App\Console\Commands\End of day Tasks\PaypalProcessCommand',
        'App\Console\Commands\End of day Tasks\StatementScheduleCommand',
        'App\Console\Commands\End of day Tasks\SystemGraphDataLoggerCommand',
        'App\Console\Commands\ExampleCommand',
        'App\Console\Commands\Hourly Tasks\CheckSchemesCommand',
        'App\Console\Commands\Hourly Tasks\CopyManualMeterReadingsToMeterReadingsAllTable',
        'App\Console\Commands\Hourly Tasks\HandleCampaignsCommand',
        'App\Console\Commands\Hourly Tasks\QueuedCustomersCommand',
        'App\Console\Commands\Hourly Tasks\SMSReplyControlCommand',
        'App\Console\Commands\Management\CheckForOfflineServices',
        'App\Console\Commands\Management\ManageCommandSchedule',
        'App\Console\Commands\Management\ReadinessTaskCommand',
        'App\Console\Commands\Management\RebootProgramsCommand',
        'App\Console\Commands\Management\ReportScheduleCommand',
        'App\Console\Commands\Management\SupportCommand',
        'App\Console\Commands\Management\TempControlTaskCommand',
        'App\Console\Commands\Manual\FixCharlotte',
        'App\Console\Commands\Manual\FixOverCharge',
        'App\Console\Commands\Manual\FixStandingCharges',
        'App\Console\Commands\Manual\GenerateCustomersUsage',
        'App\Console\Commands\Manual\InsertCharlotteUsage',
        'App\Console\Commands\Manual\OverChargedCharlotte',
        'App\Console\Commands\Monthly Tasks\MonthStartBillingIssue',
        'App\Console\Commands\QuickCommand',
        'App\Console\Commands\Reminders\CreditWarningSMSForYellowZoneCustomers',
        'App\Console\Commands\Valve Tasks\ResendCloseValveCommand',
        'App\Console\Commands\Valve Tasks\ResendOpenValveCommand',
        'App\Console\Commands\Valve Tasks\ResendShutOffCommand',
        'App\Console\Commands\Valve Tasks\ResetShutOffCommand',
        'App\Console\Commands\WatchDogManager',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('inspire')
                 ->hourly();
    }
}
