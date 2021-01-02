<?php

use Dompdf\Dompdf;

/*
|--------------------------------------------------
| Testing Routes
|--------------------------------------------------
|
| These routes are not actively used, they are for testing purposes only
| as they are used as a shortcut to execute certain tasks
|
|
*/

/* SMS Control */
Route::any('sms/response', 'SMSController@parseCustomerReply');

use LaravelDaily\LaravelCharts\Classes\LaravelChart;

Route::get('pdf', function () {
    $dompdf = new Dompdf();
    $scheme = Scheme::where('scheme_number', 17)->first();
    $dompdf->loadHtml(View::make('report.aidan.advice_notes_pdf', [
                    'company_name'	=> 'test',
                    's'				=> $scheme,
                    'fullscreen' 	=> false,
                ]));
    $dompdf->set_option('isRemoteEnabled', true);
    $dompdf->set_option('debugKeepTemp', true);
    $dompdf->set_option('isHtml5ParserEnabled', true);
    $dompdf->setPaper('A4', 'portrait');
    // (Optional) Setup the paper size and orientation
    //$dompdf->setPaper('A4', 'landscape');

    // Render the HTML as PDF
    $dompdf->render();

    // Output the generated PDF to Browser
    $output = $dompdf->output();

    // $pdf = PDF::loadView('report.aidan.advice_notes_pdf', [
                    // 'company_name'	=> $scheme->company_name,
                    // 's'				=> $scheme,
                    // 'fullscreen' 	=> false,
                // ]);

                // $pdf->getDomPDF()->get_option('enable_html5_parser');
                // $pdf->save();
            // }
});
Route::get('lol', function () {
    $schemes = Scheme::active(false);

    foreach ($schemes as $j => $s) {
        $tariff = $s->tariff;
        if (! $tariff) {
            continue;
        }
        $standing_charge = $tariff->tariff_2;
        $kwh_charge = $tariff->tariff_1;

        $customers = $s->customers;
        foreach ($customers as $k => $c) {
            $first_day_ran = false;
            //echo "Customer #" . $c->id . "<br/>";
            $days = ['2020-12-22', '2020-12-23', '2020-12-24'];

            $prev = null;
            $next = null;

            foreach ($days as $k1 => $d) {
                $has_day = DistrictHeatingUsage::where('customer_id',
            $c->id)->where('date', $d)->first();

                if (Carbon\Carbon::parse($c->commencement_date) > Carbon\Carbon::parse('2020-12-15')) {
                    continue;
                }

                if (! $has_day) {
                    if (! $first_day_ran) {
                        $prev_day = Carbon\Carbon::parse($d)->subDays(1)->format('Y-m-d');
                        $next_day = Carbon\Carbon::parse($d)->addDays(1)->format('Y-m-d');
                        $prev = DistrictHeatingUsage::where('customer_id',
                    $c->id)->whereRaw("(date = '$prev_day')")->first();
                        $next = DistrictHeatingUsage::where('customer_id',
                    $c->id)->whereRaw("(date = '$next_day')")->first();
                        echo 'Missing day '.$d.' - Referencing from '.$prev_day.'';
                        $first_day_ran = true;

                        $new_day = new DistrictHeatingUsage();
                        $new_day->customer_id = $c->id;
                        $new_day->permanent_meter_id = $prev->permanent_meter_id;
                        $new_day->ev_meter_ID = $prev->ev_meter_ID;
                        $new_day->ev_timestamp = $d.' 00:00:00';
                        $new_day->scheme_number = $s->scheme_number;
                        $new_day->date = $d;
                        $new_day->standing_charge = $standing_charge;

                        if ($next) {
                            echo ' and '.$next_day;
                            $used = $next->start_day_reading - $prev->end_day_reading;
                            $used_eur = $used * $kwh_charge;
                            echo " :: <b> Used $used kWh </b>";
                            $new_day->cost_of_day = $standing_charge + $used_eur;
                            $new_day->start_day_reading = $prev->end_day_reading;
                            $new_day->end_day_reading = $next->start_day_reading;
                            $new_day->total_usage = $used;
                            $new_day->unit_charge = $used_eur;
                        } else {
                            $new_day->cost_of_day = $standing_charge;
                            $new_day->start_day_reading = $prev->end_day_reading;
                            $new_day->end_day_reading = $new_day->start_day_reading;
                            $new_day->total_usage = 0;
                            $new_day->unit_charge = 0;
                        }

                        echo '<br/>';

                        $c->balance -= $standing_charge;
                        $c->save();

                        $new_day->arrears_repayment = 0;
                        $new_day->manual = 1;
                        $new_day->save();
                        echo $new_day;
                        echo $c->id;
                    }

                    echo 'Missing <b>'.$d.'</b><br/>';
                }
            }

            echo '<br/><br/>';
        }
    }
});

Route::get('first_of_mont', function () {
    $aff = DistrictHeatingUsage::whereRaw("(date >= '2020-09-01' AND ( ((end_day_reading-start_day_reading) != total_usage)) )")->groupBy('customer_id')->get();

    foreach ($aff as $k => $a) {
        $customer = Customer::find($a->customer_id);

        if (! $customer) {
            continue;
        }

        $dhu = DistrictHeatingUsage::whereRaw("(customer_id = '".$customer->id."' AND date >= '2020-09-01' AND ( ((end_day_reading-start_day_reading) != total_usage)) )")->get();

        echo $customer->id;
        echo '<br/>';
        echo $customer->username;
        echo '<br/>';
        foreach ($dhu as $k => $d) {
            $correct_total_usage = $d->end_day_reading - $d->start_day_reading;
            if ($correct_total_usage <= 0) {
                continue;
            }

            $scheme = $customer->scheme;
            if (! $scheme) {
                continue;
            }

            $tariff = $scheme->tariff;
            if (! $tariff) {
                continue;
            }

            $per_kwh = $tariff->tariff_1;

            $correct_unit_charge = $per_kwh * $correct_total_usage;

            $reimbursement = $d->unit_charge - $correct_unit_charge;

            $bal_now = $customer->balance;
            // echo "WRONG unit cost: " . $d->unit_charge  ;
            // echo "<br/>";
            // echo "Correct unit cost: " . $correct_unit_charge  ;
            // echo "<br/>";

            $customer->balance += $reimbursement;
            $customer->save();

            $d->total_usage = $correct_total_usage;
            $d->unit_charge = $correct_unit_charge;
            $d->cost_of_day = $d->arrears_repayment + $d->unit_charge + $d->standing_charge;
            $d->save();

            echo "Owed $reimbursement";
            echo '<br/>';
            echo "Need to charge for $correct_total_usage instead of ".$d->total_usage;
            echo 'Balance change: '.$bal_now.' -> '.$customer->balance;
            echo '<br/>';
            echo '<br/>';
        }

        echo '<br/><br/>';
    }
});

Route::get('fix_fairways', function () {
    $affected = Customer::where('commencement_date', '>=', '2020-01-01')->get();

    foreach ($affected as $k => $c) {
        $entries = DistrictHeatingUsage::where('date', '<', $c->commencement_date)
        ->where('customer_id', $c->id)
        ->where('cost_of_day', '>', 0.00)
        ->get();

        if (count($entries) > 0) {
            foreach ($entries as $k => $v) {
            }

            echo "$c->username <br/>";
        }
    }
});

Route::get('sms', function () {
    $sms_messages = SMSMessage::whereRaw("(message NOT LIKE '%You can avail of a €5 IOU.%')")
    ->whereRaw("(message NOT LIKE '%The balance of your account is%')")
    ->whereRaw("(message NOT LIKE '%IOU successful.%')")
    ->whereRaw("(message NOT LIKE '%Daily arrears repayment%')")
    ->whereRaw("(message NOT LIKE '%You currently have no IOU\'s%')")
    ->whereRaw("(message NOT LIKE '%Your service has been shut off.%')")
    ->whereRaw("(message NOT LIKE '%You have been scheduled to shut off%')")
    ->whereRaw("(message NOT LIKE '%Your credit is running low%')")
    ->whereRaw("(message NOT LIKE '%Your barcode is%')")
    ->whereRaw("(message NOT LIKE '%You have successfully topped up%')")
    ->whereRaw("(message NOT LIKE '%Here are your SnugZone login credentials:%')")
    ->whereRaw("(message NOT LIKE '%Here are your SnugZone details%')")
    ->whereRaw("(message NOT LIKE '%Mariana Testing%')")
    ->whereRaw("(message NOT LIKE '%test%')")
    ->whereRaw('NOT (LENGTH(message) <= 20)')
    ->whereRaw("(message NOT LIKE '%Your Snugzone access is Account Number/User%')")
    ->whereRaw("(message NOT LIKE '%Your Snugzone access %')")
    ->whereRaw("(message NOT LIKE '%You requested a password reset.%')")
    ->whereRaw("(message NOT LIKE '%982600440000000%')")
    ->whereRaw("(message NOT LIKE '%You recently requested a password reset%')")
    ->whereRaw("(message NOT LIKE '%Your password reset code is%')")
    ->whereRaw("(message NOT LIKE '%Your requested SnugZone account statement has been%')")
    ->whereRaw("(message NOT LIKE '%To log in to your Snugzone%')")
    ->whereRaw("(message NOT LIKE '%To login to your app%')")
    ->whereRaw("(message NOT LIKE '%To access your app;%')")
    ->whereRaw("(message NOT LIKE '%To access your account%')")
    ->whereRaw("(message NOT LIKE '%USER:%')")
    ->whereRaw("(message NOT LIKE '%please top up immediately to avoid disconnectio%')")
    ->whereRaw("(message NOT LIKE '%Your statement has been sent %')")
    ->whereRaw("(message NOT LIKE '%Your Snugzone login details are%')")
    ->whereRaw("(message NOT LIKE '%You can avail of a 25eur IOU%')")
    ->whereRaw("(message NOT LIKE '%Your Snugzone details are Account%')")
    ->whereRaw("(LOWER(message) NOT LIKE '%password%')")
    ->whereRaw("(message NOT LIKE '%Your recharge fee is%')")
    ->whereRaw("(message NOT LIKE '%You are now registered with SnugZone.%')")
    ->whereRaw("(message NOT LIKE '%Visit this link to reset your password.%')")
    ->whereRaw("NOT (message LIKE '% Your Snugzone %' AND message NOT LIKE '%service%') ")
    ->whereRaw("NOT (LENGTH(message) = 19 AND message LIKE '%98260044000%')")
    ->groupBy('message')
    ->orderBy('date_time', 'DESC')
    ->get();

    foreach ($sms_messages as $k => $v) {
        echo "<b>Message:</b> $v->message<br/>";
    }
});

Route::get('cronjobs', function () {
    if ($fh = fopen('/var/www/app/commands/Management/cron_job_manager.log', 'r')) {
        while (! feof($fh)) {
            $line = fgets($fh);
            echo nl2br($line);
        }
        fclose($fh);
    }
});

Route::get('quick_sms', function () {
    $username = 'aidan62';
    $message = 'Test Message';

    $customer = Customer::where('username', $username)->first();
    $res = $customer->sms($message);

    var_dump($res);
});

Route::any('prepago/index', 'PrepagoIEController@index');

Route::get('customer_info/{username}', 'TestRouteController@getCustomerInfo');

Route::get('test-watchdog', 'WatchDogController@testWatchDog');

Route::get('test_encrypt/{toencrypt}', 'TestRouteController@testEncrypt');

Route::get('test_decrypt/{encrypted}', 'TestRouteController@testDecrypt');

Route::get('hash/{password}/{other?}', 'TestRouteController@make_hash');

//Route::match(['get', 'post'], 'rest', 'TestRouteController@elvaco_rest');

Route::get('topups', 'TestRouteController@paypoint_totals');

Route::match(['get', 'post'], 'mbus', 'TestRouteController@insert_mbus_address_translations');

Route::get('logs/{type}/{date?}/{customer?}', 'TestRouteController@prepago_logs');
Route::get('logs_new/{type?}/{date?}/{customer?}', 'TestRouteController@prepago_logs_new');

Route::get('mysql_usage', 'TestRouteController@mysql_usage_info');

Route::get('running_services', 'PrepagoServiceController@prepago_services');

Route::get('running_services_stop/{id}/{name}', 'PrepagoServiceController@prepago_stop_service');

Route::get('running_services_start/{name}', 'PrepagoServiceController@prepago_start_service');

Route::post('test-ipn', 'TestIPNController@index');

Route::post('/gateway/register_interest', 'CorporateCitizenshipController@register_interest');
Route::post('/gateway/slots', 'CorporateCitizenshipController@slots');
Route::post('/gateway/active', 'CorporateCitizenshipController@activeCampaign');

/*
 Error handling
*/
Route::get('404', 'ErrorController@page_not_found');

/*
|--------------------------------------------------
| Application Routes
|--------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', 'UserController@showLogin');
Route::get('logout', 'HomeController@logout');
Route::get('prepago_admin/sms', 'HomeController@showWelcome');

/**
 * PREPAYGO API.
 */
Route::get('prepago_app/recharge_on/{customer_id}/{email}/{username}/{password}/{rs_code}', [
    'as' => 'ws_ev_recharge_on',
    'uses' => 'EVWebServiceController@evRechargeOn',
]);

Route::get('prepago_app/initiate_manual_recharge_stop/{customer_id}/{email}/{username}/{password}/{rs_code}', [
    'as' => 'ws_ev_recharge_manual_stop',
    'uses' => 'EVWebServiceController@initiateManualRechargeStop',
]);

Route::get('prepago_app/recharge_stop/{customer_id}/{email}/{username}/{password}/{rs_code}/{manually?}', [
    'as' => 'ws_ev_recharge_stop',
    'uses' => 'EVWebServiceController@finalizeRechargeStopProcedure',
]);

Route::get('prepago_app/get_meter_recharge_status/{customer_id}/{email}/{username}/{password}/{rs_code}', [
    'as' => 'ws_ev_meter_recharge_status',
    'uses' => 'EVWebServiceController@getMeterRechargeStatus',
]);

Route::get('prepago_app/ev_login/{username}/{password}', [
    'as' => 'ws_ev_login',
    'uses' => 'EVWebServiceController@evLogin',
]);

/**
 * PREPAGO ADMIN.
 *
 * Previously http://162.13.37.69/website/
 */
Route::get('login/login_action', function () {
    return Redirect::to('/');
});

Route::group(['middleware' => 'csrf'], function () {
    Route::post('login/login_action', 'UserController@login_action');
});

Route::group(['middleware' => 'auth'], function () {
    //Route::resource('customerduplicates', 'SchemeController');
    Route::resource('customerduplicates', 'CustomerDuplicatesController');

    Route::get('m-test', function () {
        exec('/usr/bin/java -jar -Djava.library.path="/var/www/tmp/java_code/M-Bus:/var/www/tmp/boost_1_56_0/stage/lib/" /var/www/tmp/java_code/M-Bus/RelayCheck.jar "checkvalve" "10.241.216.149" "0200182096152800" 2>&1', $res, $retVal);
        var_dump($res);
        var_dump($retVal);
    });

    Route::get('m-test-temp', function () {
        Artisan::call('billing:monthstart');
    });

    if (Auth::user()) {
        Auth::user()->logPage();
    }

    Route::get('welcome', 'HomeController@showWelcome');

    Route::get('groups', 'GroupController@index');
    Route::put('groups', 'GroupController@update');

    Route::get('schemes-readings', 'SchemesReadingsController@index');
    Route::get('schemes-readings/export/{schemeNumber}', 'SchemesReadingsController@export');

    /* MISC */
    Route::get('advanced_search', 'AccountController@advanced_search');
    Route::post('advanced_search', 'AccountController@advanced_search_submit');
    Route::get('missing_customers', 'AccountController@missing_customers');
    Route::get('away_modes', 'AccountController@away_modes');
    Route::match(['get', 'post'], 'shut_offs', 'AccountController@shut_offs');
    Route::get('ious', 'AccountController@ious');
    Route::get('exceptions', 'ErrorController@exceptions');
    Route::get('system_monitor/{date?}', 'SystemController@system_monitor');

    /* PAYPAL MANAGER */
    Route::post('pp/getbalances', 'PaypalAdminController@getbalances_ajax');
    Route::get('pp/payments', 'PaypalAdminController@payments');
    Route::get('pp/generate', 'PaypalAdminController@generateTopup');
    Route::post('pp/payments', 'PaypalAdminController@payments_ajax');
    Route::get('pp/disputes', 'PaypalAdminController@disputes');

    /* REMOTE CONTROL MGR */
    Route::get('temperature_control', 'TemperatureControlController@index');
    Route::post('temperature_control/edit_schedule', 'TemperatureControlController@edit_schedule');

    /* VALVE TEST PROGRAM */
    Route::get('valve_control', 'ValveTestController@index');

    /* DATALOGGER */
    Route::get('datalogger', 'DataLoggerController@index');
    Route::post('datalogger/get_dataloggers', 'DataLoggerController@get_dataloggers');
    Route::post('datalogger/get_meters/{data_logger_id}', 'DataLoggerController@get_meters');

    /* APP BUG REPORTS */
    Route::get('bug/reports', 'SupportController@report_a_bug');
    Route::post('bug/reports/reply/{id}', 'SupportController@report_a_bug_reply');
    Route::get('bug/reports/view/{id}/{platform?}', 'SupportController@report_a_bug_view');
    Route::get('bug/reports/view/{id}/{mark}/{platform?}', 'SupportController@report_a_bug_view_mark');
    Route::any('bug/reports/get_presets', 'SupportController@report_a_bug_get_presets');

    /* CHANGELOG */
    Route::get('changelog', 'ChangelogController@index');
    Route::get('changelog/view/{id}', 'ChangelogController@viewChange');
    Route::post('changelog/view/{id}', 'ChangelogController@viewChangeAction');
    Route::post('changelog/add', 'ChangelogController@addChange');
    Route::post('changelog/remove', 'ChangelogController@removeChange');
    Route::post('changelog/edit', 'ChangelogController@editChange');
    Route::post('changelog/mark_completed', 'ChangelogController@markCompleted');
    Route::post('changelog/mark_uncompleted', 'ChangelogController@markUnCompleted');
    Route::post('changelog/send_reminder', 'ChangelogController@sendReminder');
    Route::post('changelog/increment_change', 'ChangelogController@incrementChange');

    /* SUPPORT */
    Route::post('submit_issue', 'SupportController@submit_issue');
    Route::get('support', 'SupportController@index');
    Route::get('support/view/{id}', 'SupportController@view_ticket');
    Route::post('support/reply/{id}', 'SupportController@submit_reply');
    Route::get('support/mark_solved/{id}', 'SupportController@mark_solved');
    Route::get('support/mark_reopened/{id}', 'SupportController@mark_reopened');
    Route::get('support/tickets', 'SupportController@view_my_tickets');

    /* WATCHDOG */
    Route::get('watchdogs/{customer_id?}', 'WatchDogController@watchdogs');
    Route::get('view_watchdog/{id}', 'WatchDogController@view_watchdog');
    Route::get('view_csv_watchdog/{id}', 'WatchDogController@view_csv_watchdog');
    Route::post('start_watchdog', 'WatchDogController@startWatchDog');
    Route::post('stop_watchdog', 'WatchDogController@stopWatchDog');
    Route::post('email_watchdog', 'WatchDogController@emailWatchDog');

    /* GUARDDOG */
    Route::get('guarddog', 'GuardDogController@index');
    Route::any('guarddog/start', 'GuardDogController@startGuardDog');
    Route::any('guarddog/stop', 'GuardDogController@stopGuardDog');
    Route::any('guarddog/get', 'GuardDogController@getGuardDog');

    /* BOSS */
    Route::get('welcome-schemes', 'BossSchemesController@welcome');
    Route::post('welcome-schemes', 'BossSchemesController@welcome_scheme_info');
    Route::put('schemes/set', 'BossSchemesController@setScheme');
    Route::get('schemes/{scheme_number}/payout-report', 'BossSchemesController@setSchemeAndLoadPayoutReport');

    Route::get('schemes/status/{status_id?}', 'BossSchemesController@statusCodeInfo');

    Route::get('boss/{userID?}', 'BOSSController@index');
    Route::get('boss-hierarchy', 'BOSSController@displayHierarchyPage');

    Route::get('boss/{userID?}/assign', 'BossUsersController@displayAssignUsersPage');
    Route::post('boss/{userID?}/assign', 'BossUsersController@assign');
    Route::post('boss/{userID}/unassign', 'BossUsersController@unassign');

    Route::get('boss/{userID}/reassign', 'BossUsersController@displayReassignPage');
    Route::post('boss/{userID}/reassign', 'BossUsersController@reassign');

    Route::get('boss/{userID}/schemes', 'BossSchemesController@displayEditSchemesPage');
    Route::get('boss/{userID}/schemes/{schemeNumber}/rs-codes', 'BossSchemesController@displaySchemeRSCodesPage');
    Route::post('boss/{userID}/schemes', 'BossSchemesController@updateSchemes');

    /* TRACKING */
    Route::any('track/admin_page_visit', 'TrackingController@logAdminPageVisit');
    Route::any('track/admin_page_duration', 'TrackingController@logAdminPageDuration');

    /* TRACKING CHARTS */
    Route::get('data/admin_page_visit', 'TrackingReportController@admin_tracking_page_visit_data');
    Route::get('data/admin_page_duration', 'TrackingReportController@admin_tracking_page_duration_data');
    Route::get('data/customer_login_tracking', 'TrackingReportController@customer_login_tracking_data');
    Route::get('data/customer_topup_tracking', 'TrackingReportController@customer_topup_tracking_data');
    Route::get('data/customer_registered_platforms', 'TrackingReportController@customer_registered_data');
    Route::get('data/customer_day_activity', 'TrackingReportController@customer_day_activity_tracking_data');
    Route::match(['post', 'get'], 'data/customer_day_activity/day', 'TrackingReportController@customer_day_activity_tracking_search_day');

    /*Route::get('scheme-setup', function()
    {
        return Redirect::to('scheme-setup/user-setup');
    });
    Route::match(['get', 'post'], 'scheme-setup/user-setup', 'SchemeSetUpController@userSetup');
    Route::match(['get', 'post'], 'scheme-setup/scheme-setup', 'SchemeSetUpController@schemeSetup');
    Route::match(['get', 'post'], 'scheme-setup/tariff-setup', 'SchemeSetUpController@tariffSetup');
    Route::match(['get', 'post'], 'scheme-setup/sim-setup', 'SchemeSetUpController@simSetup');
    Route::get('scheme-setup/success', 'SchemeSetUpController@success');
    */

    /* SCHEME SET UP */
    Route::get('scheme-setup', 'SchemeSetUpController@index');
    Route::post('scheme-setup/step1', 'SchemeSetUpController@step_1_submit');
    Route::post('scheme-setup/step2', 'SchemeSetUpController@step_2_submit');
    Route::post('scheme-setup/step3', 'SchemeSetUpController@step_3_submit');
    Route::post('scheme-setup/complete', 'SchemeSetUpController@step_complete');

    /* SCHEME SET UP V2 */
    Route::get('setup/choose', 'SetupController@setupChoose');
    Route::post('setup/choose', 'SetupController@setupChooseSubmit');
    Route::get('setup/scheme', 'SetupController@schemeSetup');
    Route::post('setup/scheme', 'SetupController@schemeSetupSubmit');

    /* CUSTOMER SET UP */
    Route::get('customer-setup', 'CustomerSetupController@index');

    Route::resource('schemes', 'SchemeController');

    Route::get('customer/{customer_id}', 'HomeController@customer_view_shortcut');
    Route::post('customer_tabview_controller/send_notification/{customer_id}', 'HomeController@send_notification');
    Route::post('customer_tabview_controller/save_meter_info/{customer_id}', 'HomeController@save_meter_info');
    Route::post('customer_tabview_controller/replace_meter/{customer_id}', 'HomeController@replace_meter');
    Route::post('customer_tabview_controller/poll_replace_meter/{customer_id}', 'HomeController@poll_replace_meter');
    Route::post('customer_tabview_controller/test_replace_meter/{customer_id}', 'HomeController@test_replace_meter');
    Route::get('customer_tabview_controller/show/{customer_id}', 'HomeController@customer_view');
    Route::get('customer_tabview_controller/show/meter/{meter_id}', 'HomeController@meterView');
    Route::get('customer_tabview_controller/password-reset/{customer_id}', 'HomeController@passwordReset');
    Route::get('customer_tabview_controller/activate_away_mode/{customer_id}', 'HomeController@activate_away_mode');
    Route::post('customer_tabview_controller/refund_payment/{customer_id}', 'HomeController@refund_payment');
    Route::get('customer_tabview_controller/sync_paypal/{customer_id}/{date}', 'HomeController@sync_paypal');
    Route::get('customer_tabview_controller/clear_away_mode/{customer_id}', 'HomeController@clear_away_mode');
    Route::any('customer_tabview_controller/start_at/{customer_id}', 'HomeController@start_at');
    Route::any('customer_tabview_controller/stop_at/{customer_id}', 'HomeController@stop_at');
    Route::get('customer_tabview_controller/force_iou/{customer_id}', 'HomeController@force_iou');
    Route::get('customer_tabview_controller/remove_pm/{customer_id}', 'HomeController@remove_pm');
    Route::get('customer_tabview_controller/sync_pm/{customer_id}', 'HomeController@sync_pm');
    Route::get('customer_tabview_controller/send_statement/{customer_id}', 'HomeController@send_statement');
    Route::get('customer_tabview_controller/export', 'HomeController@export');

    Route::get('red_to_green/{customer_id}', 'HomeController@redToGreen');
    Route::get('customer_search', ['before' => 'canAccessCRMFunction', 'uses' => 'HomeController@customer_search']);
    Route::post('search', 'HomeController@search');

    Route::get('missing_readings', 'MissingReadingsController@index');

    // CRM Function

    Route::get('dashboard', 'DashboardController@index');
    Route::get('dashboard/get_data_ajax/{data}/{params?}', 'DashboardController@get_data_ajax');

    Route::get('view/sub_view/{name}', 'TestRouteController@loadSubView');

    Route::get('specialist', 'HomeController@adminSpecialist');

    Route::get('whos-online', 'TrackingController@whosOnline');

    Route::get('issue_arrears', 'HomeController@issue_arrears');
    Route::get('issue_top_up', 'HomeController@issue_top_up');

    Route::any('authorise_topup/{customer_id}', 'CreditController@authorise_topup');

    Route::get('issue_top_up/add_creditlist/{customer_id}/{customer_email}/{type}', 'CreditController@add_creditlist');
    Route::get('issue_top_up/rem_creditlist/{customer_id}/{type}', 'CreditController@rem_creditlist');

    Route::get('issue_credit', 'CreditController@issue_credit');
    //Route::get('issue_credit/add_creditlist/{customer_id}/{customer_email}', 'CreditController@add_creditlist');
    //Route::get('issue_credit/rem_creditlist/{customer_id}', 'CreditController@rem_creditlist');
    Route::get('issue_credit/check_login/{password?}', 'CreditController@check_login');
    Route::get('issue_credit/add_amount/{amount}/{reason}', 'CreditController@ic_add_amount');
    //Route::post('issue_credit/search_customers', 'CreditController@ic_search_customers');
    Route::post('issue_credit/search_customers', 'CreditController@search_customers');

    Route::get('issue_admin_iou', 'CreditController@issue_admin_iou');
    //Route::post('issue_admin_iou/search_customers', 'CreditController@iai_search_customers');
    Route::post('issue_admin_iou/search_customers', 'CreditController@search_customers');
    Route::get('issue_admin_iou/issue_admin_iou_amount/{customer_id}', 'CreditController@issue_admin_iou_amount');
    Route::get('issue_admin_iou/issue/{customer_id}', 'CreditController@issue_admin_iou_quick');
    //Route::get('issue_admin_iou/add_amount/{customer_id}/{amount}/{reason}', 'CreditController@iai_add_amount');
    Route::get('issue_admin_iou/add_amount/{amount}/{reason}', 'CreditController@iai_add_amount');

    Route::get('issue_topup_arrears', 'CreditController@issue_topup_arrears');
    //Route::post('issue_topup_arrears/search_customers', 'CreditController@ita_search_customers');
    Route::post('issue_topup_arrears/search_customers', 'CreditController@search_customers');
    Route::get('issue_topup_arrears/issue_topup_arrears_amount/{customer_id}', 'CreditController@issue_topup_arrears_amount');
    //Route::get('issue_topup_arrears/add_amount/{customer_id}/{amount}/{reason}', 'CreditController@ita_add_amount');
    Route::post('issue_topup_arrears/add_amount', 'CreditController@ita_add_amount');

    Route::post('issue_credit', 'CreditController@add_amount');
    Route::post('deduct_credit', 'CreditController@remove_amount');

    Route::get('campaigns', 'CampaignController@index');
    Route::get('campaigns/view/{id}', 'CampaignController@view');
    Route::get('campaigns/edit/{id}', 'CampaignController@edit');
    Route::post('campaigns/edit/{id}', 'CampaignController@edit_submit');
    Route::get('campaigns/create', 'CampaignController@create');
    Route::post('campaigns/create', 'CampaignController@create_submit');

    Route::get('announcements', 'AnnouncementsController@create_announcement');
    Route::post('announcements', 'AnnouncementsController@create_announcement_submit');
    Route::get('announcements/all', 'AnnouncementsController@view_announcements');
    Route::get('announcements/view/{id}', 'AnnouncementsController@view_announcement');
    Route::get('announcements/edit/{id}', 'AnnouncementsController@edit_announcement');
    Route::post('announcements/edit/{id}', 'AnnouncementsController@edit_announcement_submit');

    Route::get('notifications', 'NotificationsController@create_notifications');
    Route::post('notifications', 'NotificationsController@create_notifications_submit');
    Route::get('notifications/all', 'NotificationsController@all_notifications');

    Route::get('customer_messaging/single_customer', 'MessageController@single_customer');
    Route::post('customer_messaging/search_single_customer', 'MessageController@search_single_customer');
    Route::get('customer_messaging/rem_smslist/{customer_id}', 'MessageController@rem_smslist');
    Route::get('customer_messaging/add_smslist/{customer_id}/{username}', 'MessageController@add_smslist');
    Route::get('customer_messaging/check_sms_login/{password}', 'MessageController@check_sms_login');
    Route::get('customer_messaging/send_single_sms/{message}', 'MessageController@send_single_sms');
    Route::post('customer_messaging/send_single_sms', 'MessageController@send_single_sms_post');

    Route::get('customer_messaging/scheme', ['before' => 'canAccessCRMFunction', 'uses' => 'MessageController@scheme']);
    Route::get('customer_messaging/scheme/all', ['before' => 'canAccessCRMFunction', 'uses' => 'MessageController@scheme']);
    //Route::get('customer_messaging/send_scheme_sms/{message}', 'MessageController@send_scheme_sms');
    Route::post('customer_messaging/send_scheme_sms', 'MessageController@send_scheme_sms');
    Route::post('customer_messaging/send_scheme_sms/all', 'MessageController@send_scheme_sms');
    Route::get('customer_messaging/send_scheme_sms_result', 'MessageController@send_scheme_sms_result');

    Route::get('edit_customer_details', 'HomeController@edit_customer_details');

    // CRM Function Programs
    Route::get('system_programs', 'ProgramController@index_programs');
    Route::get('system_programs/remote_execution', 'ProgramController@index_remote_execution');
    Route::post('system_programs/remote_execution', 'ProgramController@create_remote_execution');
    Route::post('system_programs/remote_execution/stop', 'ProgramController@stop_remote_execution');
    Route::get('system_programs/manage_schedule', 'ProgramController@index_manage_schedule');
    Route::post('system_programs/manage_schedule', 'ProgramController@manage_schedule');
    Route::post('system_programs/switch_schedule', 'ProgramController@switch_schedule');

    // Installed Meters
    Route::match(['get', 'post'], 'installed_meters', 'MeterController@installed_meters');
    Route::post('search_installed_meters', 'MeterController@search_installed_meters');
    Route::get('installed_meters/meter_data/{meter_id}', 'MeterController@meter_data');
    Route::post('installed_meters/meter_data_search', 'MeterController@meter_data_search');

    // Customer setup (** New 25/11/2018 **)

    // Account Controller
    Route::get('reinstate_account', 'AccountController@deleted_accounts');
    Route::get('reinstate_account/confirm/{id}', 'AccountController@reinstate_account');
    Route::post('reinstate_account/confirm/{id}', 'AccountController@reinstate_account_submit');

    Route::get('open_account', 'AccountController@open_account');
    Route::get('open_account/swap/{customer_id}', 'AccountController@open_account');
    Route::any('open_account/action', 'AccountController@open_account_action');
    Route::get('open_account/customer_setup_error', 'AccountController@customer_setup_error');
    Route::get('open_account/get_prepopulation/{meter_number}', 'AccountController@get_prepopulation');
    Route::post('open_account/search_apt', 'AccountController@search_apt');
    Route::post('open_account/rectify_apt', 'AccountController@rectify_apt');
    Route::get('open_account/queue', 'AccountController@open_account_queue');
    Route::get('open_account/queue/action', 'AccountController@open_account_queue_action');
    Route::get('close_account', 'AccountController@close_account');
    Route::post('close_account', 'AccountController@close_account');
    Route::get('close_account_alt', 'AccountController@close_account_alt');
    Route::get('close_account_alt/confirm/download/{id}', 'AccountController@close_account_alt_download');
    Route::get('close_account_alt/confirm/{id}', 'AccountController@close_account_alt_confirm');
    Route::post('close_account_alt/confirm/{id}', 'AccountController@close_account_alt_confirm');
    Route::post('close_account/close_account_search', 'AccountController@close_account_search');
    Route::get('close_account/close_account_action/{customer_number}', 'AccountController@close_account_action');

    Route::any('fm', 'PaymentController@fm');
    Route::any('fm_email', 'PaymentController@fm_email');

    //close account procedure
    Route::match(['get', 'post'], 'close_account/{customer_id}/step1', 'AccountController@closeAccountStep1');
    Route::get('close_account/{customer_id}/step2', 'AccountController@closeAccountStep2');
    Route::match(['get', 'post'], 'close_account/{customer_id}/step3', 'AccountController@closeAccountStep3');
    Route::get('close_account/{customer_id}/step4', 'AccountController@closeAccountStep4');

    // Customer View Update
    //Route::post('customer_tabview_controller/edit_utility_action/{customer_id}', 'HomeController@edit_utility_action');
    Route::post('customer_tabview_controller/add_utility_note/{customer_id}', 'HomeController@addUtilityNote');
    Route::delete('customer_tabview_controller/utility_note/{customer_id}', 'HomeController@deleteUtilityNote');
    Route::post('customer_tabview_controller/edit_common_action/{customer_id}', 'HomeController@edit_common_action');
    Route::post('customer_tabview_controller/edit_max_recharge_fee/{customer_id}', 'HomeController@editMaxRechargeFee');
    Route::post('customer_tabview_controller/edit_nominated_phone_action/{customer_id}', 'HomeController@edit_nominated_phone_action');
    //Route::post('customer_tabview_controller/date_search_action/{customer_id}', 'HomeController@date_search_action');
    Route::post('customer_tabview_controller/date_search_action/{customer_id}', 'HomeController@customer_view');
    Route::post('customer_tabview_controller/daily_charges_search/{customer_id}', 'HomeController@dailyChargesSearch');
    Route::post('customer_tabview_controller/topups/date_search_action/{customer_id}', 'HomeController@topupsDateSearch');
    Route::post('customer_tabview_controller/readings/date_search_action/{customer_id}', 'HomeController@readingsDateSearch');
    Route::any('customer_tabview_controller/get_readings/{customer_id}/{from?}/{to?}', 'HomeController@getReadings');
    Route::post('customer_tabview_controller/addarrears', 'HomeController@addarrears');
    Route::post('customer_tabview_controller/toggle_ev_owner', 'HomeController@toggleEVOwner');

    // Meter stats
    Route::get('meter_stats/{customer_id}', 'MeterStatisticsController@index');

    // District Heating Usage
    Route::get('edit_dhu/{id}', 'CustomerController@edit_dhu');
    Route::post('edit_dhu/{id}', 'CustomerController@edit_dhu_save');
    Route::post('edit_dhu/spread/{id}', 'CustomerController@edit_dhu_spread_cost');

    // Readings export
    Route::get('export/readings/{customer_id}/{from?}/{to?}', 'CSVController@customerReadings');
    // Billing
    Route::get('billing/flags', 'BillingController@view_flags');
    Route::get('billing/{customer_id}', 'BillingController@get_billing');
    Route::get('billing/{customer_id}/download', 'BillingController@download_billing');
    Route::post('billing/{customer_id}/refund_all', 'BillingController@refund_date');
    Route::post('billing/{customer_id}/refund', 'BillingController@refund_charge');
    Route::post('billing/{customer_id}/charge', 'BillingController@issue_charge');
    Route::post('billing/{customer_id}/approve_flag', 'BillingController@approve_flag');
    Route::post('billing/{customer_id}/decline_flag', 'BillingController@decline_flag');
    Route::post('billing/{customer_id}/auto_spread', 'BillingController@auto_spread');

    // System Reports
    Route::get('system_reports/paypoint_reports', 'PaypointController@index');

    Route::get('system_reports/prepaygo', 'PrepayGoReportController@index');

    Route::get('system_reports/sim_reports', 'ReportController@uptime_report');
    Route::get('system_reports/sim_reports/{scheme}/{pdf?}', 'ReportController@get_uptime_report');
    Route::post('system_reports/sim_reports', 'ReportController@uptime_report');
    Route::post('system_reports/sim_reports/get', 'ReportController@get_uptime_report_data');

    Route::post('system_reports/sim/track_scheme', 'TrackingController@trackScheme');
    Route::post('system_reports/sim/track_scheme/settings', 'TrackingController@trackSchemeSettings');

    Route::get('system_reports/tracking_reports', 'TrackingReportController@index');
    Route::get('system_reports/admin_tracking', 'TrackingReportController@admin_tracking');

    Route::get('system_reports/inconsistent_usage', 'ReportController@inconsistent_usage');
    Route::post('system_reports/inconsistent_usage', 'ReportController@fix_inconsistent_usage');
    Route::get('system_reports/missing_standing', 'ReportController@missing_standing');
    Route::post('system_reports/missing_standing', 'ReportController@missing_standing_rectify');
    Route::get('system_reports/missing_dhu', 'ReportController@missing_dhu');
    Route::get('system_reports/duplicate_dhu', 'ReportController@duplicate_dhu');
    Route::post('system_reports/duplicate_dhu/singular', 'ReportController@remove_singular_duplicate_dhu');
    Route::post('system_reports/duplicate_dhu', 'ReportController@remove_duplicate_dhu');
    Route::get('system_reports/duplicate_dhm', 'ReportController@duplicate_dhm');
    Route::post('system_reports/duplicate_dhm', 'ReportController@remove_duplicate_dhm');

    Route::get('customer_reports/report1/{customer_id}', 'ReportController@report1');

    Route::get('system_reports', 'ReportController@index');
    Route::get('system_reports/topup_reports', 'ReportController@index_topup_reports');
    Route::get('system_reports/messaging_reports', 'ReportController@index_messaging_reports');
    Route::get('system_reports/customer_supply_status', 'ReportController@index_customer_supply_status');
    Route::get('system_reports/credit_issue_reports', 'ReportController@index_credit_issue_reports');
    Route::get('system_reports/weather_reports', 'ReportController@index_weather_reports');

    Route::get('system_reports/supply_report_units', 'ReportController@supply_report_units');
    Route::post('system_reports/search_supply_report_units', 'ReportController@search_supply_report_units');
    Route::post('system_reports/search_supply_report_units_by_date', 'ReportController@search_supply_report_units_by_date');

    Route::match(['get', 'post'], 'system_reports/boiler_report', 'BoilerReportController@index');

    //Route::get('system_reports/topup_reports/pending_topups', 'ReportController@pending_topups');
    Route::get('system_reports/topup_reports/customer_topup_history', 'ReportController@customer_topup_history');
    Route::get('system_reports/topup_reports/customer_topup_history_by_ajax', 'ReportController@customer_topup_history_by_ajax');
    //Route::post('system_reports/topup_reports/pending_topups_by_search', 'ReportController@pending_topups_by_search');
    //Route::post('system_reports/topup_reports/pending_topups_search_by_date', 'ReportController@pending_topups_search_by_date');

    Route::get('system_reports/topup_reports/system_topup_history', 'ReportController@system_topup_history');
    Route::post('system_reports/topup_reports/customer_topup_history_by_search', 'ReportController@customer_topup_history_by_search');
    Route::post('system_reports/topup_reports/customer_topup_history_search_by_date', 'ReportController@customer_topup_history_search_by_date');

    Route::get('system_reports/topup_reports/tarrif_history', 'ReportController@tarrif_history');
    Route::post('system_reports/topup_reports/tarrif_history_by_date', 'ReportController@tarrif_history_by_date');

    Route::get('system_reports/barcode_reports', ['before' => 'canAccessCRMFunction', 'uses' => 'ReportController@barcode_reports']);
    Route::post('system_reports/search_barcode_reports', 'ReportController@search_barcode_reports');

    Route::get('system_reports/sms_messages', 'ReportController@sms_messages');
    Route::post('system_reports/search_sms_messages', 'ReportController@search_sms_messages');
    Route::post('system_reports/search_sms_messages_by_date', 'ReportController@search_sms_messages_by_date');

    Route::get('system_reports/in_app_messages', 'ReportController@in_app_messages');
    Route::post('system_reports/search_in_app_message', 'ReportController@search_in_app_message');
    Route::post('system_reports/search_in_app_message_by_date', 'ReportController@search_in_app_message_by_date');

    Route::get('system_reports/not_read_meters', 'ReportController@notReadMeters');
    Route::get('system_reports/paypal_payout_reports', 'ReportController@paypalPayouts');
    Route::get('system_reports/payzone_payout_reports', 'ReportController@payzonePayouts');

    Route::get('system_reports/advice_notes', 'AidanReportController@adviceNotes');
    Route::any('system_reports/schedule/getInfo', 'AidanReportController@scheduleGetInfo');

    Route::get('system_reports/bill_reports', 'ReportController@bill_reports');
    Route::post('system_reports/bill_reports', 'ReportController@bill_reports');

    Route::get('system_reports/carlinn_report', 'CarlinnReportController@index');

    Route::get('customer_supply_status/names_notes', 'ReportController@names_notes');
    Route::post('customer_supply_status/names_notes_by_search', 'ReportController@names_notes_by_search');

    Route::get('customer_supply_status/names_mobile_numbers', 'ReportController@names_mobile_numbers');
    Route::post('customer_supply_status/names_mobile_numbers_by_search', 'ReportController@names_mobile_numbers_by_search');

    Route::get('customer_supply_status/name_address', 'ReportController@name_address');
    Route::post('customer_supply_status/name_address_by_search', 'ReportController@name_address_by_search');

    Route::get('customer_supply_status/names', 'ReportController@names');
    Route::post('customer_supply_status/names_by_search', 'ReportController@names_by_search');

    Route::get('customer_supply_status/total_balance', 'ReportController@total_balance');

    Route::get('customer_supply_status/list_of_credit_user', 'ReportController@list_of_credit_user');
    Route::post('customer_supply_status/total_credit_users_by_search', 'ReportController@total_credit_users_by_search');

    Route::get('customer_supply_status/list_of_debit_user', 'ReportController@list_of_debit_user');
    Route::post('customer_supply_status/total_debit_users_by_search', 'ReportController@total_debit_users_by_search');

    Route::get('customer_supply_status/deposit_reports', 'ReportController@deposit_reports');
    Route::post('customer_supply_status/deposit_report_by_search', 'ReportController@deposit_report_by_search');

    Route::get('system_reports/iou_usage_display', 'ReportController@iou_usage_display');
    Route::post('system_reports/iou_usage_display_by_search', 'ReportController@iou_usage_display_by_search');

    Route::get('system_reports/iou_extra_usage_display', 'ReportController@iou_extra_usage_display');
    Route::post('system_reports/iou_extra_usage_display_by_search', 'ReportController@iou_extra_usage_display_by_search');

    Route::get('system_reports/list_all_customers', 'HomeController@showWelcome');
    Route::get('system_reports/deleted_customers', 'ReportController@deletedCustomers');
    Route::get('system_reports/inactive_landlords', 'ReportController@deletedCustomers');

    Route::get('system_reports/admin_issued_credit', 'ReportController@adminIssuedCredit');

    Route::match(['get', 'post'], 'system_reports/payout_reports/{scheme_number?}', 'PayoutReportController@index');

    // Create CSV
    Route::get('create_csv/supply_report_units', 'CSVController@supply_report_units');
    Route::get('create_csv/search_supply_report_units/{search_key}', 'CSVController@search_supply_report_units');
    Route::get('create_csv/search_supply_report_units_by_date/{to}/{from}', 'CSVController@search_supply_report_units_by_date');

    Route::get('create_csv/boiler_report/{from?}/{to?}', 'CSVBoilerReportController@index');

    Route::get('create_csv/pending_topups', 'CSVController@pending_topups');
    Route::get('create_csv/pending_topups_by_search/{search_key}', 'CSVController@pending_topups_by_search');
    Route::get('create_csv/pending_topups_search_by_date/{to}/{from}', 'CSVController@pending_topups_search_by_date');

    Route::get('create_csv/system_topup_history', 'CSVController@system_topup_history');
    Route::get('create_csv/customer_topup_history', 'CSVController@customer_topup_history');
    Route::get('create_csv/customer_topup_history_by_search/{search_key}', 'CSVController@customer_topup_history_by_search');
    Route::get('create_csv/customer_topup_history_search_by_date/{to}/{from}', 'CSVController@customer_topup_history_search_by_date');

    Route::get('create_csv/barcode_reports', 'CSVController@barcode_reports');
    Route::get('create_csv/search_barcode_reports/{search_key}', 'CSVController@search_barcode_reports');

    Route::get('create_csv/meter_readings/{to?}/{from?}', 'CSVController@meter_readings_reports');

    Route::get('create_csv/sms_messages', 'CSVController@sms_messages');
    Route::get('create_csv/search_sms_messages/{search_key}', 'CSVController@search_sms_messages');
    Route::get('create_csv/search_sms_messages_by_date/{to}/{from}', 'CSVController@search_sms_messages_by_date');

    Route::get('create_csv/in_app_messages', 'CSVController@in_app_messages');
    Route::get('create_csv/search_in_app_message/{search_key}', 'CSVController@search_in_app_message');
    Route::get('create_csv/search_in_app_message_by_date/{to}/{from}', 'CSVController@search_in_app_message_by_date');

    Route::get('create_csv/names_notes', 'CSVController@names_notes');
    Route::get('create_csv/names_notes_by_search/{search_key}', 'CSVController@names_notes_by_search');

    Route::get('create_csv/names_mobile_numbers', 'CSVController@names_mobile_numbers');
    Route::get('create_csv/names_mobile_numbers_by_search/{search_key}', 'CSVController@names_mobile_numbers_by_search');

    Route::get('create_csv/name_address', 'CSVController@name_address');
    Route::get('create_csv/name_address_by_search/{search_key}', 'CSVController@name_address_by_search');

    Route::get('create_csv/names', 'CSVController@names');
    Route::get('create_csv/names_by_search/{search_key}', 'CSVController@names_by_search');

    Route::get('create_csv/total_balance', 'CSVController@total_balance');

    Route::get('create_csv/list_of_credit_user', 'CSVController@list_of_credit_user');
    Route::get('create_csv/total_credit_users_by_search/{search_key}', 'CSVController@total_credit_users_by_search');

    Route::get('create_csv/list_of_debit_user', 'CSVController@list_of_debit_user');
    Route::get('create_csv/total_debit_users_by_search/{search_key}', 'CSVController@total_debit_users_by_search');

    Route::get('create_csv/deposit_reports', 'CSVController@deposit_reports');
    Route::get('create_csv/deposit_report_by_search/{search_key}', 'CSVController@deposit_report_by_search');

    Route::get('create_csv/iou_usage_display', 'CSVController@iou_usage_display');
    Route::get('create_csv/iou_usage_display_by_search/{search_key}', 'CSVController@iou_usage_display_by_search');

    Route::get('create_csv/iou_extra_usage_display', 'CSVController@iou_extra_usage_display');
    Route::get('create_csv/iou_extra_usage_display_by_search/{search_key}', 'CSVController@iou_extra_usage_display_by_search');

    Route::get('create_csv/admin_issued_credit', 'CSVController@adminIssuedCredit');

    Route::get('create_csv/list_all_customers', 'CSVController@listAllCustomers');
    Route::get('create_csv/deleted_customers', 'CSVController@deletedCustomers');
    Route::get('create_csv/inactive_landlords', 'CSVController@deletedCustomers');

    Route::get('create_csv/not_read_meters', 'CSVController@notReadMeters');

    Route::get('create_csv/paypal_payout_reports', 'CSVController@paypalPayouts');
    Route::get('create_csv/payzone_payout_reports', 'CSVController@payzonePayouts');

    Route::get('create_csv/bill_reports/{from?}/{to?}', 'CSVController@bill_reports');

    Route::get('create_csv/missing_readings_reports', 'CSVController@missing_readings_reports');

    Route::post('create_csv/payout_reports/{from}/{to}/{scheme_number?}', 'CSVPayoutController@index');

    /**
     * WEATHER REPORTS.
     *
     * Weather data specification.
     * As Prepago is currently targeting District Heating Utility Companies (DUHC)
     * it makes sense for the DUHC to be able to map out how much heat is being used
     * in the system compared to the external weather conditions.
     */
    Route::get('weather_reports/topups', 'WeatherController@topups');
    Route::post('weather_reports/topups/get', 'WeatherController@get_topups');
    Route::post('weather_reports/topups/get2', 'WeatherController@get_topups2');
    Route::post('weather_reports/topups/csv', 'WeatherController@csv_topups');
    Route::post('weather_reports/topups/extra', 'WeatherController@extra_topups');

    Route::get('weather_reports/heat_usage', 'WeatherController@heatUsage');
    Route::any('weather_reports/heat_usage/get', 'WeatherController@get_heatUsage');
    Route::any('weather_reports/heat_usage/get2', 'WeatherController@get_heatUsage2');
    Route::post('weather_reports/heat_usage/csv', 'WeatherController@csv_heatUsage');
    Route::post('weather_reports/heat_usage/extra', 'WeatherController@extra_heatUsage');

    // Settings
    Route::get('backup/database', 'BackupController@database');
    Route::post('backup/database/submit', 'BackupController@databaseSubmit');
    Route::post('backup/database/submitTable', 'BackupController@databaseSubmitTable');
    Route::post('backup/database/restore', 'BackupController@databaseRestore');
    Route::post('backup/database/remove', 'BackupController@databaseRemoveFile');
    Route::post('backup/database/restoreTable', 'BackupController@databaseRestoreTable');

    Route::get('settings/sms_target', 'SettingsController@smsTarget');
    Route::post('settings/sms_target', 'SettingsController@smsTargetSubmit');

    Route::get('settings/simulator', 'SimulatorController@simulate');
    Route::post('settings/simulator', 'SimulatorController@simulateSubmit');

    Route::get('settings/test', 'SettingsController@testScan');
    Route::post('settings/test', 'SettingsController@testScanAjax');

    Route::get('settings/utility_user_setup', 'SettingsController@utilityUserSetup');
    Route::post('settings/utility_user_setup', 'SettingsController@utilityUserSetupSubmit');

    Route::get('settings/meter_setup', 'MeterController@bulkMeterSetup');
    Route::post('settings/meter_setup', 'MeterController@bulkMeterSetupSubmit');

    Route::post('settings/meter_setup2', 'MeterController@bulkMeterSetupSubmit2');

    Route::get('settings/scan_setup', 'MeterController@scanSetup');
    Route::post('settings/scan_setup', 'MeterController@scanSetupSubmit');

    Route::get('settings/tariff_setup/{scheme_id}', 'SchemeController@tariffSetup');
    Route::post('settings/tariff_setup/{scheme_id}', 'SchemeController@tariffSetupSubmit');

    Route::get('settings/meter_lookup', 'MeterController@meterLookup');
    Route::post('settings/meter_lookup', 'MeterController@meterLookupSubmit');
    Route::get('settings/meter_lookup/{id}', 'MeterController@meterLookupEdit');
    Route::post('settings/meter_lookup/{id}', 'MeterController@meterLookupEditSubmit');

    Route::get('settings/autotopup', 'AutoTopupController@autotopup_settings');
    Route::post('settings/autotopup/edit_signup', 'AutoTopupController@autotopup_settings_edit_signup');
    Route::post('settings/autotopup/edit_terms', 'AutoTopupController@autotopup_settings_edit_terms');
    Route::post('settings/autotopup/edit_vars', 'AutoTopupController@autotopup_settings_edit_vars');
    Route::post('settings/autotopup/create', 'AutoTopupController@autotopup_settings_create');

    Route::get('settings/system_settings', 'SettingsController@settings');
    Route::post('settings/system_settings/add', 'SettingsController@settings_add');
    Route::get('settings/system_settings/remove/{id}', 'SettingsController@settings_remove');
    Route::any('settings/system_settings/save/{id}', 'SettingsController@settings_save');

    Route::get('settings/paypal', 'PaypalAdminController@settings');
    Route::post('settings/paypal/add', 'PaypalAdminController@settings_add');
    Route::get('settings/paypal/remove/{id}', 'PaypalAdminController@settings_remove');
    Route::any('settings/paypal/save/{id}', 'PaypalAdminController@settings_save');

    Route::get('settings/sms_presets', 'SettingsController@sms_presets');
    Route::any('settings/sms_presets/add', 'SettingsController@sms_presets_add');
    Route::get('settings/sms_presets/remove/{id}', 'SettingsController@sms_presets_remove');
    Route::any('settings/sms_presets/save/{id}', 'SettingsController@sms_presets_save');

    Route::get('settings/email_settings', 'EmailController@settings');
    Route::post('settings/email_settings/add', 'EmailController@settings_add');
    Route::get('settings/email_settings/remove/{id}', 'EmailController@settings_remove');
    Route::any('settings/email_settings/save/{id}', 'EmailController@settings_save');

    Route::get('settings/scheme_settings', 'SchemeController@scheme_settings');
    Route::post('settings/scheme_settings', 'SchemeController@scheme_settings_save');
    Route::post('settings/scheme_settings/shut_off/{scheme_number?}', 'SchemeController@scheme_settings_shut_off_save');
    Route::post('settings/scheme_settings/add_operator', 'SchemeController@scheme_settings_add_operator');
    Route::post('settings/scheme_settings/remove_operator', 'SchemeController@scheme_settings_remove_operator');
    Route::get('settings/scheme_settings/manage_operator/{operator_id}', 'SchemeController@scheme_settings_manage_operator');
    Route::post('settings/scheme_settings/manage_operator/{operator_id}', 'SchemeController@scheme_settings_manage_operator_save');
    Route::get('settings/scheme_settings/manage_operator/lock/{operator_id}', 'SchemeController@scheme_settings_manage_operator_lock');
    Route::get('settings/scheme_settings/manage_meter/{pmd_id}', 'SchemeController@scheme_settings_manage_meter');
    Route::post('settings/scheme_settings/manage_meter/{pmd_id}', 'SchemeController@scheme_settings_manage_meter_save');
    Route::post('settings/scheme_settings/manage_extra', 'SchemeController@scheme_settings_manage_extra_save');

    Route::get('settings/sms_settings', 'SettingsController@sms_settings');
    Route::get('sms_settings/change_sms_password/{password}', 'SettingsController@change_sms_password');
    Route::post('settings/sms_settings/save_sms_message', 'SettingsController@save_sms_message');

    Route::get('settings/faq/{scheme_id?}', 'SettingsController@faq');
    Route::post('settings/faq/save_faq/{scheme_id?}', 'SettingsController@save_faq');
    Route::post('faq/add_mass_faq', 'SettingsController@add_mass_faq');

    Route::get('settings/tariff', 'SettingsController@tariff');
    Route::get('settings/tariff/all', 'SettingsController@tariff');
    Route::post('settings/tarrif/add', 'SettingsController@tariffadd');
    Route::get('settings/tarrif/cancel/{tarrif_id}', 'SettingsController@tariffcancel');
    Route::get('settings/tarrif/cancel/{tarrif_id}/all', 'SettingsController@tariffcancel');

    Route::get('settings/credit_setting', 'SettingsController@credit_setting');
    Route::post('settings/credit_setting/change', 'SettingsController@credit_setting_change');

    Route::get('settings/unassigned_users', 'SettingsController@unassignedUsers');
    Route::get('settings/unassigned_users/{user_id}/schemes', 'SettingsController@accessControlSchemes');
    Route::post('settings/unassigned_users/{user_id}/schemes', 'SettingsController@accessControlSchemesUpdate');
    Route::put('settings/unassigned_users/{user_id}', 'SettingsController@editAccount');
    Route::get('settings/unassigned_users/close_account_action/{user_id}', 'SettingsController@close_account_action');

    Route::get('settings/access_control', 'SettingsController@access_control');
    Route::get('settings/access_control/{user_id}/schemes', 'SettingsController@accessControlSchemes');
    Route::post('settings/access_control/{user_id}/schemes', 'SettingsController@accessControlSchemesUpdate');
    Route::put('settings/access_control/{user_id}', 'SettingsController@editAccount');
    Route::get('settings/access_control/close_account_action/{id}', 'SettingsController@close_account_action');
    Route::post('settings/access_control/add_account_action', 'SettingsController@add_account_action');

    Route::get('settings/multiple_close', 'AccountController@multiple_close');
    Route::post('settings/multiple_close/close_account_action', 'AccountController@multiple_close_account_action');
    Route::post('settings/multiple_close/multiple_close_account_search', 'AccountController@multiple_close_account_search');

    // User Settings
    Route::get('user_settings/signins', 'UserSettingsController@signins');
    Route::get('user_settings/change_username', 'UserSettingsController@change_username');
    Route::get('user_settings/change_admin_username/{username}', 'UserSettingsController@change_admin_username');

    Route::get('user_settings/change_password', 'UserSettingsController@change_password');
    Route::get('user_settings/change_admin_password/{password}', 'UserSettingsController@change_admin_password');

    Route::get('settings/boss_restrictions', 'BOSSController@displayBossRestrictionsSettings');
    Route::post('settings/boss_restrictions', 'BOSSController@saveBossRestrictionsSettings');

    // Program Settings
    Route::get('settings/system_programs/run_cronjob/{name}', 'ProgramController@run_cronjob');
    Route::get('settings/system_programs/cronjobs', 'ProgramController@manage_cronjobs');
    Route::post('settings/system_programs/cronjobs', 'ProgramController@save_manage_cronjobs');

    Route::get('settings/customers/spread', 'CustomerController@mass_spread_cost');
    Route::post('settings/customers/spread/check', 'CustomerController@mass_spread_cost_check');
    Route::post('settings/customers/spread', 'CustomerController@mass_spread_cost_submit');

    Route::get('settings/payments', 'PaymentController@payment_settings');

    Route::get('settings/system_programs/billing_engine', 'ProgramController@index_billing_engine_settings');
    Route::post('settings/system_programs/billing_engine/save_programs', 'ProgramController@save_programs_billing_engine_settings');
    Route::post('settings/system_programs/billing_engine/save', 'ProgramController@save_billing_engine_settings');
    Route::post('settings/system_programs/billing_engine/delete', 'ProgramController@delete_billing_engine_settings');
    Route::post('settings/system_programs/billing_engine/add', 'ProgramController@add_billing_engine_settings');

    Route::get('settings/system_programs/shut_off_engine', 'ProgramController@index_shut_off_engine_settings');
    Route::post('settings/system_programs/shut_off_engine/save_programs', 'ProgramController@save_programs_shut_off_engine_settings');
    Route::post('settings/system_programs/shut_off_engine/save', 'ProgramController@save_shut_off_engine_settings');
    Route::post('settings/system_programs/shut_off_engine/delete', 'ProgramController@delete_shut_off_engine_settings');
    Route::post('settings/system_programs/shut_off_engine/add', 'ProgramController@add_shut_off_engine_settings');

    Route::get('settings/system_programs/update', 'ProgramController@update');

    // Global System Settings
    Route::get('settings/system_programs/shut_off', 'ProgramController@shut_off_engine_manager');
    Route::post('settings/system_programs/shut_off', 'ProgramController@shut_off_engine_manager_save');

    /* Eseye/SIM Management */
    Route::post('settings/create_eseye_ticket', 'SIMController@create_eseye_ticket');

    /* SIM Control */
    Route::get('sim/ping', 'SIMController@ping_SIM');
    Route::get('sim/reboot', 'SIMController@reboot_SIM');
    Route::get('sim/msg', 'SIMController@msg_SIM');
    Route::any('sim/grab_setup', 'SIMController@grab_setup_SIM');
    Route::any('sim/assign_setup', 'SIMController@assign_setup_SIM');

    /* Scheme / SIM Tests */
    Route::get('prepago_installer/test-unit/sim-test/{simIP}', 'InstallerController@test_sim');

    Route::post('settings/reboot_scheme/{track?}', 'SchemeController@reboot_scheme_sim');
    Route::get('settings/{iccid}/sms/toggle/block', 'SchemeController@toggle_schemes_sms_block');

    Route::get('settings/{iccid}/sms', 'SIMController@get_schemes_sms');
    Route::any('settings/log', 'SIMController@get_sims_log');
    Route::get('settings/ping', 'SIMController@ping_sims_index');
    Route::post('settings/ping/{id}/{update_status?}', 'SIMController@sim_status_html');
    Route::post('settings/customcmd', 'SIMController@send_custom_cmd');

    /* New generation Unit Tests; Not currently rolled out 04/03/2020 */
    Route::match(['GET', 'POST'], 'prepago_installer/test-unit/quick-telegram', 'InstallerController@quick_telegram');

    /* Unit Tests */
    Route::get('prepago_installer/cancel-shutoff/{unitID}', 'InstallerController@cancelShutOffSchedule'); // Using
    Route::get('prepago_installer/test-unit/{unitID}', 'InstallerController@testUnit'); // Using
    Route::get('automate_meter_readings', 'InstallerController@readAllCustomersMeters');
    Route::get('prepago_installer/test-unit/meter-read-test/{unitID}/{port?}/{attempts?}', 'InstallerController@meter_read_test'); // Using
    Route::get('prepago_installer/test-unit/meter-read-test-confirm/{unitID}', 'InstallerController@meter_read_test_confirm'); // Using
    Route::get('prepago_installer/test-unit/meter-read-test-confirm-alt/{unitID}', 'InstallerController@meter_read_test_confirm_alt'); // Using
    Route::get('prepago_installer/test-unit/service-control-test/{unitID}/{port?}/{attempts?}', 'InstallerController@service_control_test'); // Using
    Route::get('prepago_installer/test-unit/service-control-test-confirm/{unitID}', 'InstallerController@service_control_test_confirm'); // Using
    Route::get('prepago_installer/test-unit/service-control-test-switch/{unitID}/{action}/{port?}/{attempts?}', 'InstallerController@service_control_test_switch'); // Using
    Route::get('prepago_installer/test-unit/service-control-test-switch-confirm/{unitID}/{action}', 'InstallerController@service_control_test_switch_confirm'); // Using
    Route::get('prepago_installer/test-unit/heat-control-test/{unitID}', 'InstallerController@heat_control_test'); // Using
    Route::get('prepago_installer/test-unit/heat-control-test-confirm/{unitID}', 'InstallerController@heat_control_test_confirm'); // Using
    Route::get('prepago_installer/test-unit/heat-control-test-switch/{unitID}/{action}', 'InstallerController@heat_control_test_switch'); // Using

    Route::get('prepago_installer/test-unit/meter-telegram-test/{unitID}', 'DiagnosticsButtonsController@meterTelegramTest'); // Using
    Route::get('prepago_installer/test-unit/meter-telegram-test-confirm/{unitID}', 'DiagnosticsButtonsController@meterTelegramTestConfirm'); // Using
    Route::get('prepago_installer/test-unit/relay-telegram-test/{unitID}', 'DiagnosticsButtonsController@relayTelegramTest'); // Using
    Route::get('prepago_installer/test-unit/relay-telegram-test-confirm/{unitID}', 'DiagnosticsButtonsController@relayTelegramTestConfirm'); // Using
    Route::get('prepago_installer/test-unit/check-valve-test/{unitID}', 'DiagnosticsButtonsController@checkValveTest'); // Using
    Route::get('prepago_installer/test-unit/check-valve-test-confirm/{unitID}', 'DiagnosticsButtonsController@checkValveTestConfirm'); // Using
    Route::get('prepago_installer/test-dataloggers/{scheme_number}', 'DiagnosticsButtonsController@dataLoggersTest'); // Using
    Route::get('prepago_installer/test-dataloggers-confirm/{scheme_number}', 'DiagnosticsButtonsController@dataLoggersTestConfirm'); // Using
});

 /**
  * PAYPOINT WEBSERVICE.
  *
  *
  * Soap requests will be processed using these
  */
 Route::post('prepago_admin/pp/auth', 'SOAPController@auth');
 Route::post('prepago_admin/pp/process_payment', 'SOAPController@processPayment');
 Route::post('prepago_admin/pp/check_connection', 'SOAPController@checkConnection');
 Route::post('prepago_admin/pp/void_payment', 'SOAPController@voidPayment');
 Route::post('prepago_admin/pp/reverse_payment', 'SOAPController@reversePayment');
 Route::post('prepago_admin/pp/calc_fee', 'SOAPController@calcFee');

/**
 * SMS SYSTEM.
 *
 * All SMS web services will go through the SMS Controller
 */
Route::get('prepago_admin/sms/shut_off_message/{customer_id}/{scheme_number}/{sms_password}', 'SMSController@shut_off_message');
Route::get('prepago_admin/sms/shut_off_warning/{customer_id}/{scheme_number}/{sms_password}', 'SMSController@shut_off_warning');
Route::get('prepago_admin/sms/credit_warning/{customer_id}/{scheme_number}/{sms_password}', 'SMSController@credit_warning');
Route::get('prepago_admin/sms/topup_message/{customer_id}/{scheme_number}/{sms_password}', 'SMSController@topup_message');
Route::get('prepago_admin/sms/user_specific_message/{customer_id}/{scheme_number}/{sms_password}/{message}', 'SMSController@user_specific_message');
//Route::get('prepago_admin/sms/scheme_specific_message/{scheme_number}/{sms_password}/{message}', 'SMSController@scheme_specific_message');
Route::post('prepago_admin/sms/scheme_specific_message', 'SMSController@scheme_specific_message');
Route::get('prepago_admin/sms/message_to_number/{scheme_number}/{sms_password}/{number}/{message}', 'SMSController@message_to_number');
Route::get('prepago_admin/sms/message_to_meter/{scheme_number}/{sms_password}/{number}/{message}/{customer_id}', 'SMSController@message_to_meter');
Route::get('prepago_admin/sms/meter_shut_off_command_message/{customer_id}/{scheme_number}/{sms_password}', 'SMSController@meter_shut_off_command_message');
Route::get('prepago_admin/sms/receive_sms', 'SMSController@receive_sms');
Route::get('prepago_admin/sms/clear_shutoff/{customer_id}', 'SMSController@clear_shutoff');
Route::get('prepago_admin/sms/process_sms_que', 'SMSController@process_sms_que');

/**
 * DATA LOGGER.
 *
 * Meter, Meter Data & Shut Off List
 */
Route::get('prepago_admin/data_logger/test', 'DataLoggerController@test');
Route::post('prepago_admin/data_logger/meter_information_upload', 'DataLoggerController@meter_information_upload');

/**
 * INSTALLATION WEBSITE.
 */
Route::group(['before' => 'isInstaller'], function () {
    Route::get('prepago_installer/access_control', 'InstallerController@access_control'); // Using
    Route::post('prepago_installer/access_control/add_account_action', 'SettingsController@add_account_action');

    /* PAGES */
    Route::get('prepago_installer', 'InstallerController@dashboard'); // Using
    Route::get('prepago_installer/address_translations', 'InstallerController@addressTranslations'); // Using
    Route::post('prepago_installer/address_translations/search', 'InstallerController@searchTranslations'); // Using
    Route::post('prepago_installer/address_translations/add-new', 'InstallerController@addAddressTranslation'); // Using
    Route::delete('prepago_installer/delete-address-translation/{digit}', 'InstallerController@deleteAddressTranslation'); // Using
    Route::get('prepago_installer/edit-address-translation/{digit}', 'InstallerController@editAddressTranslation'); // Using
    Route::post('prepago_installer/edit-address-translation/{digit}', 'InstallerController@editSubmitAddressTranslation'); // Using
    Route::get('prepago_installer/settings', 'InstallerController@settings');
    Route::get('prepago_installer/add-units', 'InstallerController@addUnits');  // Using
    Route::get('prepago_installer/add-units/{type}', 'InstallerController@addUnits');
    Route::get('prepago_installer/fetch_eight', 'InstallerController@fetchEight');
    Route::get('prepago_installer/tools', 'InstallerController@tools');  // Using
    Route::get('prepago_installer/help', 'InstallerController@help');  // Using
    Route::get('prepago_installer/search', 'InstallerController@search'); // Using
    Route::get('prepago_installer/edit-unit/{unitID}', 'InstallerController@editUnit'); // Using
    Route::post('prepago_installer/edit-unit/{unitID}', 'InstallerController@saveEditedUnitInformation');
    Route::delete('prepago_installer/delete-unit/{unitID}', 'InstallerController@deleteUnit'); // Using
    Route::post('prepago_installer/save-unit/{unitID}', 'InstallerController@saveUnit'); // Using
    Route::get('prepago_installer/relay-control/ethernet', 'InstallerController@control_ethernet');
    Route::get('prepago_installer/relay-control/mbus', 'InstallerController@control_mbus');
    Route::get('prepago_installer/relay-control/sim', 'InstallerController@control_sim');

    /* FORM ACTIONS */
    Route::post('prepago_installer/search', 'InstallerController@dashboard');  // Using

    /* -- Add unit */
    Route::post('prepago_installer/add-unit', 'InstallerController@add_unit');
    Route::post('prepago_installer/complete-install', 'InstallerController@complete_install');
    Route::post('prepago_installer/incomplete-install', 'InstallerController@incomplete_install');

    /* -- Settings */
    Route::get('prepago_installer/retrieve-secondary-meter-number/{pmd}/{datalogger}', 'InstallerController@try_grab_secondary');
    Route::post('prepago_installer/change-scheme-prefix', 'InstallerController@change_scheme_prefix');
    Route::post('prepago_installer/change-max-meter-count', 'InstallerController@change_max_meter_count');
    Route::post('prepago_installer/change-baud-rate', 'InstallerController@change_baud_rate');
});

/**
 * SECURE ROUTES.
 */
Route::get('services/list/{type?}', [
    'as' => 'services_list',
    'uses' => 'PrepagoServiceController@service_list',
]);
Route::get('services/crons', [
    'as' => 'services_list',
    'uses' => 'PrepagoServiceController@cron_list',
]);

/**
 * WEB SERVICE ROUTES.
 */
Route::get('prepago_app/login/{email}/{username}/{password}/{phone_id}/{model?}', [
    'as' => 'ws_request_1',
    'uses' => 'WebServiceController@customerLoginRequest',
]);
Route::get('prepago_app/get_prepay_information/{customer_id}/{email}/{username}/{password}', [
    'as' => 'ws_request_2',
    'uses' => 'WebServiceController@informationRequest',
]);
Route::get('prepago_app/iou/{customer_id}/{email}/{username}/{password}/{iouType}', [
    'as' => 'ws_request_3',
    'uses' => 'WebServiceController@IOURequest',
]);
Route::get('prepago_app/get_recent_topups/{customer_id}/{email}/{username}/{password}', [
    'as' => 'ws_request_4',
    'uses' => 'WebServiceController@recentTopUpsRequest',
]);
Route::get('prepago_app/get_barcode/{customer_id}/{email}/{username}/{password}', [
    'as' => 'ws_request_5',
    'uses' => 'WebServiceController@getBarcodeRequest',
]);
Route::get('prepago_app/get_faq/{customer_id}/{email}/{username}/{password}', [
    'as' => 'ws_request_6',
    'uses' => 'WebServiceController@getFAQRequest',
]);
Route::get('prepago_app/get_topup_locations/{customer_id}/{email}/{username}/{password}/{lat}/{lon}', [
    'as' => 'ws_request_7',
    'uses' => 'WebServiceController@getTopupLocationsRequest',
]);
Route::get('prepago_app/get_barcode_webapp/{username}', [
    'as' => 'ws_request_8',
    'uses' => 'WebServiceController@getBarcodeWebappRequest',
]);
Route::get('prepago_app/s', function () {
    die();
});
Route::get('prepago_app/get_barcode_webapp_rotate/{username}', [
    'as' => 'ws_request_8',
    'uses' => 'WebServiceController@getBarcodeRotateRequest',
]);
Route::get('prepago_app/get_rc_information/{customer_id}/{email}/{username}/{password}', [
    'as' => 'ws_request_9',
    'uses' => 'WebServiceController@getRemoteControlInformationRequest',
]);
Route::get('prepago_app/set_rc_information/{customer_id}/{email}/{username}/{password}/{json}', [
    'as' => 'ws_request_10',
    'uses' => 'WebServiceController@setRemoteControlInformationRequest',
]);
Route::get('prepago_app/toggle_away/{customer_id}/{email}/{username}/{password}', [
    'as' => 'ws_request_10',
    'uses' => 'WebServiceController@toggleRemoteControl',
]);
Route::get('prepago_app/app_payment/{customer_id}/{email}/{username}/{password}/{json}/{debug?}', [
    'as' => 'ws_request_11',
    'uses' => 'WebServiceController@addPaypalPaymentRequest',
]);
Route::get('prepago_app/meter_control/clear_shutoff?customer={customer_id}', [
    'as' => 'ws_request_12',
    'uses' => 'WebServiceController@paypointPaymentsTurnMeterOnOff',
]);
Route::get('prepago_app/meter_control/clear_shutoff/{customer_id}', [
    'as' => 'ws_request_12',
    'uses' => 'WebServiceController@paypointPaymentsTurnMeterOnOff',
]);
Route::get('prepago_app/meter_control/away_mode/{pmd_id}/{status}', [
    'as' => 'handle_away_mode',
    'uses' => 'WebServiceController@handleAwayModeValveSwitch',
]);
Route::get('prepago_app/customer_graph_data/{customer_id}/{email}/{username}/{password}/{date_from}/{date_to}', [
    'as' => 'ws_request_13',
    'uses' => 'WebServiceController@customerGraphData',
]);
Route::get('prepago_app/payments/{key}/{from?}/{to?}', [
    'as' => 'direct.payments',
    'uses' => 'PaymentController@getPaymentsFromPaypal',
]);
Route::get('prepago_app/captures/{id}', [
    'as' => 'direct.captures',
    'uses' => 'PaymentController@getCapturesFromPaypal',
]);
Route::any('prepago_api/incoming/{key}', [
    'as' => 'payment.incoming',
    'uses' => 'PaymentController@incoming',
]);
Route::any('prepago_api/view/{key}/{date?}', [
    'as' => 'payment.incoming',
    'uses' => 'PaymentController@view_incoming',
]);
Route::any('prepago_api/test/report/{id}', [
    'as' => 'settings.test.report',
    'uses' => 'SettingsController@testScanReport',
]);

Route::any('api/logerror', [
    'as' => 'settings.log.error',
    'uses' => 'SnugzoneAPIController@logAppError',
]);

Route::any('api/login', [
    'as' => 'settings.test.report',
    'uses' => 'SnugzoneAPIController@login',
]);

Route::any('api/login', [
    'as' => 'settings.test.report',
    'uses' => 'SnugzoneAPIController@login',
]);

Route::any('api/refreshdata', [
    'as' => 'settings.test.refresh.data',
    'uses' => 'SnugzoneAPIController@refreshData',
]);

Route::any('api/data', [
    'as' => 'settings.test.report',
    'uses' => 'SnugzoneAPIController@getData',
]);

Route::any('api/query_device', [
    'as' => 'settings.test.query.device',
    'uses' => 'SnugzoneAPIController@queryDevice',
]);

Route::any('api/query_faq/click', [
    'as' => 'settings.test.query.faq',
    'uses' => 'SnugzoneAPIController@queryFaqClick',
]);

Route::any('api/get_schemes', [
    'as' => 'settings.test.get.schemes',
    'uses' => 'SnugzoneAPIController@getSchemes',
]);

Route::any('api/auth/validate', [
    'as' => 'settings.test.valid.session',
    'uses' => 'SnugzoneAPIController@validSession',
]);

Route::any('api/get_autotopup/{extra?}', [
    'as' => 'settings.test.get.autotopup',
    'uses' => 'SnugzoneAPIController@getAutotopup',
]);

Route::any('api/get_announcements', [
    'as' => 'settings.test.get.announcements',
    'uses' => 'SnugzoneAPIController@getAnnouncements',
]);

Route::any('api/get_usage_range', [
    'as' => 'settings.test.get.usage_range',
    'uses' => 'SnugzoneAPIController@getUsageRange',
]);

Route::any('api/start_statement_schedule', [
    'as' => 'settings.test.start.statement.schedule',
    'uses' => 'SnugzoneAPIController@startStatementSchedule',
]);

Route::any('api/cancel_statement_schedule', [
    'as' => 'settings.test.cancel.statement.schedule',
    'uses' => 'SnugzoneAPIController@cancelStatementSchedule',
]);

Route::any('api/view_notification', [
    'as' => 'settings.test.view.notification',
    'uses' => 'SnugzoneAPIController@viewNotification',
]);

Route::any('api/view_announcement/{id}', [
    'as' => 'settings.test.view.announcements',
    'uses' => 'SnugzoneAPIController@viewAnnouncement',
]);

Route::any('api/comment_announcement/{id}', [
    'as' => 'settings.test.comment.announcements',
    'uses' => 'SnugzoneAPIController@commentAnnouncement',
]);

Route::any('api/use_iou', [
    'as' => 'settings.test.use.iou',
    'uses' => 'SnugzoneAPIController@useIOU',
]);

Route::any('api/resetpassword/{confirm?}', [
    'as' => 'settings.test.resetpassword',
    'uses' => 'SnugzoneAPIController@resetPassword',
]);

Route::any('api/get_support_types', [
    'as' => 'settings.test.support_types',
    'uses' => 'SnugzoneAPIController@getSupportTypes',
]);

Route::any('api/get_support_replies', [
    'as' => 'settings.test.support_replies',
    'uses' => 'SnugzoneAPIController@getSupportReplies',
]);

Route::any('api/reportabug', [
    'as' => 'settings.test.reportabug',
    'uses' => 'SnugzoneAPIController@reportABug',
]);

Route::any('api/replyticket', [
    'as' => 'settings.test.replyticket',
    'uses' => 'SnugzoneAPIController@replyTicket',
]);

Route::any('api/solveticket', [
    'as' => 'settings.test.solveticket',
    'uses' => 'SnugzoneAPIController@solveTicket',
]);

Route::any('api/gettickets', [
    'as' => 'settings.test.gettickets',
    'uses' => 'SnugzoneAPIController@getTickets',
]);

Route::any('api/changeemail', [
    'as' => 'settings.test.changeemail',
    'uses' => 'SnugzoneAPIController@changeEmail',
]);

Route::any('api/changedetails', [
    'as' => 'settings.test.changedetails',
    'uses' => 'SnugzoneAPIController@changeDetails',
]);

Route::any('api/changemobile', [
    'as' => 'settings.test.changemobile',
    'uses' => 'SnugzoneAPIController@changeMobile',
]);

Route::any('api/changepassword', [
    'as' => 'settings.test.changepassword',
    'uses' => 'SnugzoneAPIController@changePassword',
]);

Route::any('api/agreeawaymode', [
    'as' => 'settings.test.agree_away_mode',
    'uses' => 'SnugzoneAPIController@agreeAwayMode',
]);

Route::any('api/getawaymodehistory', [
    'as' => 'settings.test.get_away_mode_history',
    'uses' => 'SnugzoneAPIController@getAwayModeHistory',
]);

Route::any('api/toggleawaymode', [
    'as' => 'settings.test.toggle_away_mode',
    'uses' => 'SnugzoneAPIController@toggleAwayMode',
]);

Route::any('api/away_mode/edit', [
    'as' => 'settings.test.edit_away_mode',
    'uses' => 'SnugzoneAPIController@editAwayMode',
]);

Route::any('api/notifications/seen', [
    'as' => 'settings.test.notifications.seen',
    'uses' => 'SnugzoneAPIController@markNotificationSeen',
]);

Route::any('api/account/statement', [
    'as' => 'settings.test.statement',
    'uses' => 'SnugzoneAPIController@generateStatement',
]);

Route::get('request', [
    'as' => 'request.test',
    'uses' => 'HomeController@requestTest',
]);

Route::get('prepago/cme3100/incoming/meter', [
    'as' => 'cme3100.incoming.meter',
    'uses' => 'MeterController@incomingCme3100Meter',
]);

Route::get('prepago/cme3100/incoming/report', [
    'as' => 'cme3100.incoming.report',
    'uses' => 'MeterController@incomingCme3100Report',
]);

Route::get('prepago/cme3100/outgoing/{action}', [
    'as' => 'cme3100.outgoing.action',
    'uses' => 'MeterController@outgoingCme3100Action',
]);
Route::any('site/search', function () {
    return Redirect::back();
});
Route::any('site/subscribe', function () {
    return Redirect::back();
});
Route::any('site/contact', function () {
    return Redirect::back();
});
