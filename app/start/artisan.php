 <?php

/*
|--------------------------------------------------------------------------
| Register The Artisan Commands
|--------------------------------------------------------------------------
|
| Each available Artisan command must be registered with the console so
| that it is available to be called. We'll register every command so
| the console gets access to each of the command object instances.
|
*/

Artisan::add(new CheckSchemesCommand);
Artisan::add(new MoveTempPaymentsCommand);
Artisan::add(new DailyChecklistCommand);
Artisan::add(new ResetShutOffCommand);
Artisan::add(new ResendShutOffCommand);
Artisan::add(new CopyManualMeterReadingsToMeterReadingsAllTable);
Artisan::add(new ZeroStartDayReadings);
Artisan::add(new ResendOpenValveCommand);
Artisan::add(new ResendCloseValveCommand);
Artisan::add(new CarlinnDailyReport);
Artisan::add(new RemoveCarlinnDuplicateUsageRecords);
Artisan::add(new MonthStartBillingIssue);
Artisan::add(new CreditWarningSMSForYellowZoneCustomers);
Artisan::add(new DHMInvalidReadingsDayCounter);
Artisan::add(new CheckForOfflineServices);
Artisan::add(new MarkCustomerBalance);
Artisan::add(new BackupCustomers);
Artisan::add(new BackupDatabase);
Artisan::add(new CacheUsage);
Artisan::add(new WatchDogManager);
Artisan::add(new PaypalProcessCommand);
Artisan::add(new QueuedCustomersCommand);
Artisan::add(new SystemGraphDataLoggerCommand);
Artisan::add(new StatementScheduleCommand);
Artisan::add(new ManageCommandSchedule);
Artisan::add(new BillingForecastCommand);
Artisan::add(new ExampleCommand);
Artisan::add(new FixCommencementDate);
Artisan::add(new ReportScheduleCommand);
Artisan::add(new ReadinessTaskCommand);
Artisan::add(new TempControlTaskCommand);
Artisan::add(new SupportCommand);
Artisan::add(new HandleCampaignsCommand);
Artisan::add(new RebootProgramsCommand);
/* Debug commands */
Artisan::add(new QuickCommand);
Artisan::add(new FixStandingCharges);
Artisan::add(new FixCharlotte);
Artisan::add(new InsertCharlotteUsage);
Artisan::add(new OverChargedCharlotte);
Artisan::add(new GenerateCustomersUsage);
Artisan::add(new FixOverCharge);
