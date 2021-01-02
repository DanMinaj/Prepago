<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use LaravelDaily\LaravelCharts\Classes\LaravelChart;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class DashboardController extends Controller
{
    protected $layout = 'layouts.admin_website';

    private $startLastWeek;
    private $startThisWeek;
    private $days_into_week;
    private $endThisWeek;
    private $endLastWeek;

    private $topupPercent;
    private $topupWeekPercent;
    private $awayModePercent;
    private $IOUPercent;
    private $shutOffPercent;
    private $statementsPercent;
    private $customerPercent;
    private $engagementPercent;
    private $schemeDropsPercent;

    private $topupsThisWeek = 0;
    private $topupsAmountThisWeek = 0;
    private $awayModesThisWeek = 0;
    private $iousThisWeek = 0;
    private $shutOffsThisWeek = 0;
    private $statementsThisWeek = 0;
    private $creditThisWeek = 0;
    private $creditLastWeek = 0;

    public function __construct()
    {

        ////Stripe::start();
    }

    public function index()
    {
        try {
            $this->setWeekVariables();

            $clickData = $this->get_app_click_data();
            $appLoginData = $this->get_app_login_data();

            $todaysGraphData = SystemGraphData::where('date', date('Y-m-d'))->first();
            $customer_statuses = System::getCachedCustomersStatus();

            $topupTypeChart = Chart::apexMultipleYAxis('topups_type', '', $this->get_topups_type_data(), [
                'theme' => ['palette' => "'palette2'"],
                'yaxis' => ['title' => ['text' => "'Topups'"]],
            ]);
            $ticketResponseChart = Chart::apexMultipleYAxis('ticket_responses', '', $this->get_ticket_response_data(), [
                'theme' => ['palette' => "'palette3'"],
                'yaxis' => ['title' => ['text' => "'Tickets'"]],
                'colors' => ["'#40ad09'", "'#ff0000'", "'#c2c0c0'"],
                'chart' => ['stacked' => false],
            ]);
            $appEngagementChart = Chart::apexMultipleYAxis('app_engagement', '', $this->get_engagement_data(), [
                'theme' => ['palette' => "'palette2'"],
                'yaxis' => ['title' => ['text' => "'Unique customer logins'"]],
            ]);
            $customerTrendChart = Chart::apexMultipleYAxis('trends_chart', '', $this->get_trends_data(), [
                'theme' => ['palette' => "'palette2'"],
                'yaxis' => ['title' => ['text' => "''"]],
                'xaxis' => [
                    'categories' => $this->get_trends_data()['categories'],
                    'labels' => [
                        'show' => false,
                    ],
                ],
            ]);
            $appPlatformsChart = Chart::apexDonutChart('app_platforms', $this->get_platforms_data());

            $weekTopupData = $this->get_topups_week_data();
            $weekTopupDataAmount = $this->get_topups_week_data('amount');
            $weekTopupChart = Chart::apexTimeSeries('topups_week', 'Topups ('.$this->topupsThisWeek.' - €'.number_format($this->topupsAmountThisWeek, 2).')', 'Topups', '2019-06-05', $weekTopupData, [
                'theme' => ['palette' => "'palette3'"],
                'tooltip' => ['x' => ['format' => "'MMM dd yyyy'"]],
                'chart' => ['toolbar' => ['show'=>false]],
            ], [
                [
                    'data' => $weekTopupDataAmount,
                    'name' => "'Amount (&euro;)'",
                ],
            ]);
            $weekAwayModeData = $this->get_away_mode_week_data();
            $awayModeChart = Chart::apexTimeSeries('awaymodes', 'Away modes ('.$this->awayModesThisWeek.')', 'Away modes', '2019-06-05', $weekAwayModeData, [
                'theme' => ['palette' => "'palette3'"],
                'tooltip' => ['x' => ['format' => "'MMM dd yyyy'"]],
                'chart' => ['toolbar' => ['show'=>false]],
            ]);
            $weekiouData = $this->get_iou_week_data();
            $iouChart = Chart::apexTimeSeries('ious', 'IOUs ('.$this->iousThisWeek.')', 'IOUs', '2019-06-05', $weekiouData, [
                'theme' => ['palette' => "'palette4'"],
                'tooltip' => ['x' => ['format' => "'MMM dd yyyy'"]],
                'chart' => ['toolbar' => ['show'=>false]],
            ]);
            $weekshutOffData = $this->get_shut_off_week_data();
            $shutOffChart = Chart::apexTimeSeries('shutoffs', 'Shutoffs ('.$this->shutOffsThisWeek.')', 'Shutoffs', '2019-06-05', $weekshutOffData, [
                'theme' => ['palette' => "'palette5'"],
                'tooltip' => ['x' => ['format' => "'MMM dd yyyy'"]],
                'chart' => ['toolbar' => ['show'=>false]],
            ]);
            $weekStatementData = $this->get_statement_week_data();
            $statementChart = Chart::apexTimeSeries('statements', 'Statements ('.$this->statementsThisWeek.')', 'Statements', '2019-06-05', $weekStatementData, [
                'theme' => ['palette' => "'palette5'"],
                'tooltip' => ['x' => ['format' => "'MMM dd yyyy'"]],
                'chart' => ['toolbar' => ['show'=>false]],
            ]);
            $customerEngagementChart = Chart::apexTimeSeries('customer_engagement', 'App Engagement', 'Unique Logins', '2019-06-05', $this->get_customer_engagement_data(), [
                'theme' => ['palette' => "'palette6'"],
                'tooltip' => ['x' => ['format' => "'MMM yyyy'"]],
                'yaxis' => ['title' => ['text' => "'Unique logins'"]],
            ]);
            $weekSMSData = $this->get_week_sms_data();
            $smsChart = Chart::apexTimeSeries('sms_credit', 'SMS Credit Used in past 7 days ('.($this->creditThisWeek + $this->creditLastWeek).')', 'SMS Credit', '2019-06-05', $weekSMSData, [
                'theme' => ['palette' => "'palette3'"],
                'tooltip' => ['x' => ['format' => "'MMM dd yyyy'"]],
                'chart' => ['toolbar' => ['show'=>false]],
                'yaxis' => [
                    'title' => ['text' => "''"],
                    'labels' => [
                        'show' => false,
                    ],
                    'lines' => [
                        'show' => false,
                    ],
                ],
                'xaxis' => [
                    'categories' => $this->get_trends_data()['categories'],
                    'labels' => [
                        'show' => false,
                    ],
                    'lines' => [
                        'show' => false,
                    ],
                ],
            ]);

            /*


            $schemeUptimeChart = Chart::apexMultipleYAxis("scheme_uptime", "Scheme service drops", $this->get_scheme_uptime_data(), [
                'theme' => ['palette' => "'palette1'"]
            ]);
            */

            $top5Faqs = $this->top5Faqs();
            $last5BugReports = $this->last5BugReports();
            $last5OnlineUsers = $this->getLast5Online();
            $last5OfflineSchemes = $this->last5OfflineSchemes();
            $tcpCustomers = System::getTcpCustomers();
            $stripe = System::getStripe(7);
            $misc_data = $this->get_misc_data();
            $reconnection_data = $this->get_reconnection_data();
            $support_data = $this->get_support_data();
            $sms_data = $this->get_sms_data();
            $announcements = Announcement::orderBy('id', 'DESC')->limit(5)->get();
        } catch (Exception $e) {
            echo $e->getMessage().' ('.$e->getLine().') ';

            die();
        }

        $this->layout->page = View::make('dashboardv2.index', [
                'todaysGraphData'				=> $todaysGraphData,
                'customer_statuses' 			=> $customer_statuses,
                'awayModeChart'					=> $awayModeChart,
                'iouChart'						=> $iouChart,
                'shutOffChart'					=> $shutOffChart,
                'statementChart'				=> $statementChart,
                'smsChart'						=> $smsChart,
                'topupTypeChart'				=> $topupTypeChart,
                'ticketResponseChart'			=> $ticketResponseChart,
                'appEngagementChart'			=> $appEngagementChart,
                'appPlatformsChart'				=> $appPlatformsChart,
                'weekTopupChart'				=> $weekTopupChart,
                'stripe'						=> $stripe,
                //'schemeUptimeChart'				=> $schemeUptimeChart,
                'awayModePercent'				=> $this->awayModePercent,
                'IOUPercent'					=> $this->IOUPercent,
                'shutOffPercent'				=> $this->shutOffPercent,
                'statementsPercent'				=> $this->statementsPercent,
                'topupPercent' 					=> $this->topupPercent,
                'topupWeekPercent'				=> $this->topupWeekPercent,
                'customerPercent' 				=> $this->customerPercent,
                'schemeDropsPercent' 			=> $this->schemeDropsPercent,
                'customerEngagementChart'		=> $customerEngagementChart,
                'last5OnlineUsers'				=> $last5OnlineUsers,
                'last5OfflineSchemes'			=> $last5OfflineSchemes,
                'last5BugReports'				=> $last5BugReports,
                'top5Faqs'						=> $top5Faqs,
                'customerTrendChart'			=> $customerTrendChart,
                'tcpCustomers'					=> $tcpCustomers,
                'clickData'						=> $clickData,
                'appLoginData'					=> $appLoginData,
                'announcements'					=> $announcements,
                'misc_data'						=> $misc_data,
                'reconnection_data'				=> $reconnection_data,
                'support_data'					=> $support_data,
                'sms_data'						=> $sms_data,
        ]);
    }

    private function get_support_data()
    {
        $sys_graph_data = SystemGraphData::orderBy('id', 'DESC')->first();

        if (! ($sys_graph_data->contains('support'))) {
            return;
        }

        $support_data = $sys_graph_data->get('support');

        return $support_data;
    }

    private function get_sms_data()
    {
        $sys_graph_data = SystemGraphData::orderBy('id', 'DESC')->first();

        if (! ($sys_graph_data->contains('sms_api'))) {
            return;
        }

        $sms_data = $sys_graph_data->get('sms_api');

        return $sms_data;
    }

    private function get_reconnection_data()
    {
        ini_set('memory_limit', '-1');

        $sys_graph_data = SystemGraphData::orderBy('id', 'DESC')->first();

        if (! ($sys_graph_data->contains('shutoffs_restored_list'))) {
            return;
        }

        $shutoffs_restored_list = $sys_graph_data->get('shutoffs_restored_list');
        $shutoffs_unrestored_list = $sys_graph_data->get('shutoffs_unrestored_list');
        $total_list = $sys_graph_data->get('shutoffs');

        return [
            'unrestored' => $shutoffs_unrestored_list,
            'restored' => $shutoffs_restored_list,
            'total' => $total_list,
        ];
    }

    private function get_misc_data()
    {
        ini_set('memory_limit', '-1');

        $sys_graph_data = SystemGraphData::orderBy('id', 'DESC')->first();

        if (! ($sys_graph_data->contains('misc_data'))) {
            return;
        }

        $misc_data = $sys_graph_data->get('misc_data');

        return $misc_data;
    }

    private function get_app_click_data()
    {
        $yday_a = TrackingAppClick::where('date', date('Y-m-d', strtotime('1 day ago')))->where('clicked_on', 'normal');
        $today_a = TrackingAppClick::where('date', date('Y-m-d'))->where('clicked_on', 'normal');

        $yday_b = TrackingAppClick::where('date', date('Y-m-d', strtotime('1 day ago')))->where('clicked_on', 'beta');
        $today_b = TrackingAppClick::where('date', date('Y-m-d'))->where('clicked_on', 'beta');

        //echo $today_a->count();

        if ($yday_a->count() == 0) {
            $percent_a = 100;
        } else {
            $percent_a = (($today_a->count() - $yday_a->count()) / $yday_a->count()) * 100;
        }

        if ($yday_b->count() == 0) {
            $percent_b = 100;
        } else {
            $percent_b = (($today_b->count() - $yday_b->count()) / $yday_b->count()) * 100;
        }

        $total_a_clicks = TrackingAppClick::where('clicked_on', 'normal')->get()->sum('times');
        $total_b_clicks = TrackingAppClick::where('clicked_on', 'beta')->get()->sum('times');
        $total_b_clicks_u = TrackingAppClick::where('clicked_on', 'beta')->count();
        $total_a_clicks_u = TrackingAppClick::where('clicked_on', 'normal')->count();

        $data = [
            'yday_b' => $yday_b->count(),
            'today_b' => $today_b->count(),
            'percent_a' => $percent_a,
            'percent_b' => $percent_b,
            'total_a_clicks' => $total_a_clicks,
            'total_b_clicks' => $total_b_clicks,
            'total_b_clicks_u' => $total_b_clicks_u,
            'total_a_clicks_u' => $total_a_clicks_u,
        ];

        return $data;
    }

    private function get_app_login_data()
    {
        $app_data = TrackingAppData::all();

        $data = [

        ];

        return $data;
    }

    private function get_topups_type_data($params = null)
    {
        ini_set('memory_limit', '-1');

        $sys_graph_data = SystemGraphData::orderBy('id', 'DESC')->first();

        if (! ($sys_graph_data->contains('topup_12_labels'))) {
            die();

            return;
        }

        $data = [
            'series' => [],
            'seriesyaxis' => [],
            'categories' => $sys_graph_data->get('topup_12_labels'),
        ];

        $month_data = $sys_graph_data->get('topup_12_data');

        $lastYearY = date('Y', strtotime('-1 year'));
        $curYearY = date('Y');

        // $paypal =
        // [
        // 'name' => "'Paypal topups'",
        // 'type' => "'bar'",
        // 'data' => [],
        // ];

        $stripe =
        [
            'name' => "'Stripe'",
            'type' => "'bar'",
            'data' => [],
        ];

        $payzone =
        [
            'name' => "'Payzone topups'",
            'type' => "'bar'",
            'data' => [],
        ];

        foreach ($month_data as $k => $v) {

            //array_push($paypal['data'], $v['paypal']);
            array_push($stripe['data'], $v['stripe']);
            array_push($payzone['data'], $v['payzone']);
        }

        //$paypal['data'] = array_reverse($paypal['data']);
        $stripe['data'] = array_reverse($stripe['data']);
        $payzone['data'] = array_reverse($payzone['data']);

        // $week_year_topup_methods = $sys_graph_data->get('week_year_topup_methods');

        // $paypal['data'] = $week_year_topup_methods["paypal"];
        // $stripe['data'] = $week_year_topup_methods["stripe"];
        // $payzone['data'] = $week_year_topup_methods["payzone"];

        //array_push($data['series'], $paypal);
        array_push($data['series'], $stripe);
        array_push($data['series'], $payzone);

        $topups_revenue = $sys_graph_data->get('topups_revenue');

        $this->topupPercent = $topups_revenue['topupPercent'];
        $this->customerPercent = $topups_revenue['customersPercent'];

        return $data;
    }

    private function get_ticket_response_data($params = null)
    {
        ini_set('memory_limit', '-1');

        $sys_graph_data = SystemGraphData::orderBy('id', 'DESC')->first();

        if (! ($sys_graph_data->contains('support'))) {
            die();

            return;
        }

        $data = [
            'series' => [],
            'seriesyaxis' => [],
            'categories' => ["'13'", "'12'",  "'11'",  "'10'",  "'9'",  "'8'",  "'7'",  "'6'",  "'5'",  "'4'",  "'3'",  "'2'",  "'1'"],
        ];

        $each_week_data = $sys_graph_data->get('support')['last_13_wks'];

        $happy =
        [
            'name' => "'Happy'",
            'type' => "'bar'",
            'data' => [],
        ];

        $unhappy =
        [
            'name' => "'Unhappy'",
            'type' => "'bar'",
            'data' => [],
        ];

        $noresponse =
        [
            'name' => "'No Response'",
            'type' => "'bar'",
            'data' => [],
        ];

        foreach ($each_week_data as $k => $week) {
            $total = $week['total'];
            $total_replies = $week['total_replies'];
            $happy_count = $week['happy'];
            $unhappy_count = $week['unhappy'];
            $noresponse_count = $week['unknown'];

            array_push($happy['data'], $happy_count);
            array_push($unhappy['data'], $unhappy_count);
            array_push($noresponse['data'], $noresponse_count);
        }

        array_push($data['series'], $happy);
        array_push($data['series'], $unhappy);
        array_push($data['series'], $noresponse);

        return $data;
    }

    private function get_engagement_data()
    {
        ini_set('memory_limit', '-1');

        $sys_graph_data = SystemGraphData::orderBy('id', 'DESC')->first();

        if (! ($sys_graph_data->contains('app_engagement'))) {
            return;
        }

        $app_engagement_data = $sys_graph_data->get('app_engagement');

        $data = [
            'series' => [],
            'seriesyaxis' => [],
            'categories' => $app_engagement_data['months'],
        ];

        $new_app =
        [
            'name' => "'New app unique logins'",
            'type' => "'line'",
            'data' => [],
        ];

        $old_app =
        [
            'name' => "'Old app unique logins'",
            'type' => "'line'",
            'data' => [],
        ];

        $new_app['data'] = $app_engagement_data['new_app_growth'];
        $old_app['data'] = $app_engagement_data['old_app_growth'];

        array_push($data['series'], $new_app);
        array_push($data['series'], $old_app);

        return $data;
    }

    private function get_trends_data()
    {
        ini_set('memory_limit', '-1');

        $sys_graph_data = SystemGraphData::orderBy('id', 'DESC')->first();

        if (! ($sys_graph_data->contains('topup_trends_data'))) {
            return;
        }

        $trends_data = $sys_graph_data->get('topup_trends_data');

        $data = [
            'series' => [],
            'seriesyaxis' => [],
            'categories' => $trends_data['months'],
        ];

        $customers =
        [
            'name' => "'Customers'",
            'type' => "'line'",
            'data' => [],
        ];

        $topups =
        [
            'name' => "'100euro topups'",
            'type' => "'line'",
            'data' => [],
        ];

        $customers['data'] = $trends_data['customer_increase'];
        $topups['data'] = $trends_data['100_topup_increase'];

        array_push($data['series'], $customers);
        array_push($data['series'], $topups);

        return $data;
    }

    private function get_platforms_data()
    {
        $sys_graph_data = SystemGraphData::orderBy('id', 'DESC')->first();

        if (! ($sys_graph_data->contains('app_platforms'))) {
            return;
        }

        $app_platforms_data = $sys_graph_data->get('app_platforms');

        return [
            $app_platforms_data['ios'],
            $app_platforms_data['android'],
            $app_platforms_data['browser'],
        ];
    }

    private function get_week_sms_data($params = null)
    {
        $data = [];

        if ($params != null) {
        } else {
            $monday = date('Y-m-d', strtotime('-7 days'));
            $sys_graph_data = SystemGraphData::where('date', '>=', $monday)->get();

            $lastWeek = 0;
            $thisWeek = 0;
            foreach ($sys_graph_data as $k => $v) {
                $date = Carbon\Carbon::parse($v->date)->format('d M Y');

                if (! $v->contains('sms_api')) {
                    $data[] = ["new Date('".$date."').getTime()", 0];
                    continue;
                }

                $sms_api_data = $v->get('sms_api');

                if (! isset($sms_api_data['used_today'])) {
                    $data[] = ["new Date('".$date."').getTime()", 0];
                    continue;
                }

                $used_credit = $sms_api_data['used_today'];

                $data[] = ["new Date('".$date."').getTime()", $used_credit];

                if (DataSet::isLastWeek(Carbon\Carbon::parse($v->date)->format('Y-m-d'))) {
                    $lastWeek += $used_credit;
                }

                if (DataSet::isThisWeek(Carbon\Carbon::parse($v->date)->format('Y-m-d'))) {
                    $thisWeek += $used_credit;
                }
            }

            $this->creditThisWeek = $thisWeek;
            $this->creditLastWeek = $lastWeek;
        }

        return $data;
    }

    private function get_topups_week_data($params = null)
    {
        $data = [];

        if ($params != null) {
            if ($params == 'amount') {
                $monday = date('Y-m-d', strtotime('monday last week'));
                $sys_graph_data = SystemGraphData::where('date', '>=', $monday)->get();

                $lastWeek = 0;
                $thisWeek = 0;

                foreach ($sys_graph_data as $k => $v) {
                    $date = Carbon\Carbon::parse($v->date)->format('d M Y');

                    if (! $v->contains('totaltopupsamount')) {
                        $data[] = ["new Date('".$date."').getTime()", 0];
                        continue;
                    }

                    $topups = $v->get('totaltopupsamount');

                    if (DataSet::isLastWeek(Carbon\Carbon::parse($v->date)->format('Y-m-d'))) {
                        $lastWeek += $topups;
                    }

                    $diff = (Carbon\Carbon::parse($v->date))->diffInDays(Carbon\Carbon::now());
                    if ($diff < 7) {
                        $data[] = ["new Date('".$date."').getTime()", $topups];
                    }

                    if (DataSet::isThisWeek(Carbon\Carbon::parse($v->date)->format('Y-m-d'))) {
                        $thisWeek += $topups;
                        $data[] = ["new Date('".$date."').getTime()", $topups];
                    }
                }

                $this->topupsAmountThisWeek = $thisWeek;

                $startLastWeek = date('Y-m-d', strtotime('last monday - 7 days'));
                $startThisWeek = date('Y-m-d', strtotime('this week'));
                $days_into_week = date('N', strtotime(date('Y-m-d')));
                $endThisWeek = date('Y-m-d', strtotime($startThisWeek.' + '.($days_into_week - 1).' days'));
                $endLastWeek = date('Y-m-d', strtotime($startLastWeek.' + '.($days_into_week - 1).' days'));

                $lastWeek = PaymentStorage::whereRaw("(settlement_date >= '$startLastWeek' AND settlement_date <= '$endLastWeek')")->count();
                $thisWeek = PaymentStorage::whereRaw("(settlement_date >= '$startThisWeek' AND settlement_date <= '$endThisWeek')")->count();

                $this->topupWeekPercent = (($thisWeek - $lastWeek) / $lastWeek) * 100;
            }
        } else {
            $monday = date('Y-m-d', strtotime('monday last week'));
            $sys_graph_data = SystemGraphData::where('date', '>=', $monday)->get();

            $lastWeek = 0;
            $thisWeek = 0;

            foreach ($sys_graph_data as $k => $v) {
                $date = Carbon\Carbon::parse($v->date)->format('d M Y');

                if (! $v->contains('totaltopupstoday')) {
                    $data[] = ["new Date('".$date."').getTime()", 0];
                    continue;
                }

                $topups = $v->get('totaltopupstoday');

                $diff = (Carbon\Carbon::parse($v->date))->diffInDays(Carbon\Carbon::now());
                if ($diff < 7) {
                    $data[] = ["new Date('".$date."').getTime()", $topups];
                }

                if (DataSet::isLastWeek(Carbon\Carbon::parse($v->date)->format('Y-m-d'))) {
                    $lastWeek += $topups;
                }

                if (DataSet::isThisWeek(Carbon\Carbon::parse($v->date)->format('Y-m-d'))) {
                    $thisWeek += $topups;
                    $data[] = ["new Date('".$date."').getTime()", $topups];
                }
            }

            $this->topupsThisWeek = $thisWeek;

            $lastWeek = PaymentStorage::whereRaw("(settlement_date >= '".$this->startLastWeek."' AND settlement_date <= '".$this->endLastWeek."')")->count();
            $thisWeek = PaymentStorage::whereRaw("(settlement_date >= '".$this->startThisWeek."' AND settlement_date <= '".$this->endThisWeek."')")->count();

            $this->topupWeekPercent = (($thisWeek - $lastWeek) / $lastWeek) * 100;
        }

        return $data;
    }

    private function get_away_mode_week_data($params = null)
    {
        $data = [];

        if ($params != null) {
        } else {
            $monday = date('Y-m-d', strtotime('monday last week'));
            $sys_graph_data = SystemGraphData::where('date', '>=', $monday)->get();

            $lastWeek = 0;
            $thisWeek = 0;

            foreach ($sys_graph_data as $k => $v) {
                $date = Carbon\Carbon::parse($v->date)->format('d M Y');

                if (! $v->contains('awaymodes')) {
                    $data[] = ["new Date('".$date."').getTime()", 0];
                    continue;
                }

                $awaymodes = $v->get('awaymodes');

                $diff = (Carbon\Carbon::parse($v->date))->diffInDays(Carbon\Carbon::now());
                if ($diff < 7) {
                    $data[] = ["new Date('".$date."').getTime()", $awaymodes];
                }

                if (DataSet::isLastWeek(Carbon\Carbon::parse($v->date)->format('Y-m-d'))) {
                    $lastWeek += $awaymodes;
                }

                if (DataSet::isThisWeek(Carbon\Carbon::parse($v->date)->format('Y-m-d'))) {
                    $thisWeek += $awaymodes;
                    $data[] = ["new Date('".$date."').getTime()", $awaymodes];
                }
            }

            $lastWeek = count(RemoteControlLogging::whereRaw("(date_time >= '".$this->startLastWeek."' AND date_time <= '".$this->endLastWeek."')")->whereRaw("(action LIKE '%Away Mode Starting%')")->get());
            $thisWeek = count(RemoteControlLogging::whereRaw("(date_time >= '".$this->startThisWeek."' AND date_time <= '".$this->endThisWeek."')")->whereRaw("(action LIKE '%Away Mode Starting%')")->get());

            $this->awayModesThisWeek = $thisWeek;

            if ($lastWeek == 0) {
                $this->awayModePercent = 0;
            } else {
                $this->awayModePercent = (($thisWeek - $lastWeek) / $lastWeek) * 100;
            }
        }

        return $data;
    }

    private function get_shut_off_week_data($params = null)
    {
        $data = [];

        if ($params != null) {
        } else {
            $monday = date('Y-m-d', strtotime('monday last week'));
            $sys_graph_data = SystemGraphData::where('date', '>=', $monday)->get();

            $lastWeek = 0;
            $thisWeek = 0;
            foreach ($sys_graph_data as $k => $v) {
                $date = Carbon\Carbon::parse($v->date)->format('d M Y');

                if (! $v->contains('shutoffs')) {
                    $data[] = ["new Date('".$date."').getTime()", 0];
                    continue;
                }

                $shutoffs = $v->get('shutoffs');

                $diff = (Carbon\Carbon::parse($v->date))->diffInDays(Carbon\Carbon::now());
                if ($diff < 7) {
                    $data[] = ["new Date('".$date."').getTime()", $shutoffs];
                }

                if (DataSet::isLastWeek(Carbon\Carbon::parse($v->date)->format('Y-m-d'))) {
                    $lastWeek += $shutoffs;
                }

                if (DataSet::isThisWeek(Carbon\Carbon::parse($v->date)->format('Y-m-d'))) {
                    $thisWeek += $shutoffs;
                    $data[] = ["new Date('".$date."').getTime()", $shutoffs];
                }
            }

            $lastWeek = count(DistrictHeatingMeter::whereRaw("(last_shut_off_time >= '".$this->startLastWeek."' AND last_shut_off_time <= '".$this->endLastWeek."')")->get());
            $thisWeek = count(DistrictHeatingMeter::whereRaw("(last_shut_off_time >= '".$this->startThisWeek."' AND last_shut_off_time <= '".$this->endThisWeek."')")->get());

            $this->shutOffsThisWeek = $thisWeek;
            $this->shutOffPercent = (($thisWeek - $lastWeek) / $lastWeek) * 100;
        }

        return $data;
    }

    private function get_statement_week_data($params = null)
    {
        $data = [];

        if ($params != null) {
        } else {
            $monday = date('Y-m-d', strtotime('monday last week'));
            $sys_graph_data = SystemGraphData::where('date', '>=', $monday)->get();

            $lastWeek = 0;
            $thisWeek = 0;
            foreach ($sys_graph_data as $k => $v) {
                $date = Carbon\Carbon::parse($v->date)->format('d M Y');

                if (! $v->contains('statements')) {
                    $data[] = ["new Date('".$date."').getTime()", 0];
                    continue;
                }

                $statements = $v->get('statements');
                $data[] = ["new Date('".$date."').getTime()", $statements];
                if (DataSet::isLastWeek(Carbon\Carbon::parse($v->date)->format('Y-m-d'))) {
                    $lastWeek += $statements;
                }

                if (DataSet::isThisWeek(Carbon\Carbon::parse($v->date)->format('Y-m-d'))) {
                    $thisWeek += $statements;
                }
            }

            $this->statementsThisWeek = $thisWeek;
            $this->statementsPercent = ($lastWeek > 0) ? ((($thisWeek - $lastWeek) / $lastWeek) * 100) : 0;
        }

        return $data;
    }

    private function get_iou_week_data($params = null)
    {
        $data = [];

        if ($params != null) {
        } else {
            $monday = date('Y-m-d', strtotime('monday last week'));
            $sys_graph_data = SystemGraphData::where('date', '>=', $monday)->get();

            $lastWeek = 0;
            $thisWeek = 0;
            foreach ($sys_graph_data as $k => $v) {
                $date = Carbon\Carbon::parse($v->date)->format('d M Y');

                if (! $v->contains('ious')) {
                    $data[] = ["new Date('".$date."').getTime()", 0];
                    continue;
                }

                $ious = $v->get('ious');

                $diff = (Carbon\Carbon::parse($v->date))->diffInDays(Carbon\Carbon::now());
                if ($diff < 7) {
                    $data[] = ["new Date('".$date."').getTime()", $ious];
                }

                if (DataSet::isLastWeek(Carbon\Carbon::parse($v->date)->format('Y-m-d'))) {
                    $lastWeek += $ious;
                }

                if (DataSet::isThisWeek(Carbon\Carbon::parse($v->date)->format('Y-m-d'))) {
                    $thisWeek += $ious;
                    $data[] = ["new Date('".$date."').getTime()", $ious];
                }
            }

            $lastWeek = count(IOUStorage::whereRaw("(time_date >= '".$this->startLastWeek."' AND time_date <= '".$this->endLastWeek."')")->get());
            $thisWeek = count(IOUStorage::whereRaw("(time_date >= '".$this->startThisWeek."' AND time_date <= '".$this->endThisWeek."')")->get());

            $this->iousThisWeek = $thisWeek;

            $this->IOUPercent = (($thisWeek - $lastWeek) / $lastWeek) * 100;
        }

        return $data;
    }

    private function get_customer_engagement_data($params = null)
    {
        $data = [];

        if ($params != null) {
        } else {
            $sys_graph_data = SystemGraphData::orderBy('id', 'DESC')->first();

            if (! ($sys_graph_data->contains('engagement'))) {
                return;
            }

            $engagement = $sys_graph_data->get('engagement');

            foreach ($engagement as $k => $v) {
                $data[] = ["new Date('".$v[0]."').getTime()", $v[1]];
            }
        }

        return $data;
    }

    public function get_scheme_uptime_data($params = null)
    {
        ini_set('memory_limit', '-1');
        $data = [
            'series' => [],
            'seriesyaxis' => [],
            'categories' => ['"Mon"', '"Tue"', '"Wed"', '"Thurs"', '"Fri"', '"Sat"', '"Sun"'],
        ];

        $sys_graph_data = SystemGraphData::orderBy('id', 'DESC')->first();
        if (! ($sys_graph_data->contains('scheme_week_uptime'))) {
            return;
        }

        $uptime_data = $sys_graph_data->get('scheme_week_uptime');

        foreach ($uptime_data as $k => $v) {
            array_push($data['series'], [
                'name' => "'$k'",
                'type' => "'column'",
                'data' => $v,
            ]);
        }//

        return $data;
    }

    public function last5BugReports()
    {
        return ReportABug::orderBy('id', 'DESC')->limit(5)->get();
    }

    public function top5Faqs()
    {
        $sys_graph_data = SystemGraphData::orderBy('id', 'DESC')->first();
        $top_5_faqs = [];

        if (! ($sys_graph_data->contains('faq_data'))) {
            return [];
        }

        $top_faqs = $sys_graph_data->get('faq_data');
        for ($i = 0; $i < 5; $i++) {
            $top_5_faqs[] = $top_faqs['popular_faqs'][$i];
        }

        return $top_5_faqs;
    }

    public function getLast5Online()
    {
        return User::orderBy('is_online_time', 'DESC')->limit(5)->get();
//
    }

    public function last5OfflineSchemes()
    {
        return TrackingScheme::where('date', date('Y-m-d'))->where('offline_times', '>', 0)->orderBy('last_offline', 'DESC')->limit(5)
        ->whereRaw('scheme_number NOT IN(15,24,23,18)')->get();
    }

    public function setWeekVariables()
    {
        $this->startLastWeek = date('Y-m-d', strtotime('last monday - 7 days')).' 00:00:00';
        $this->startThisWeek = date('Y-m-d', strtotime('this week')).' 00:00:00';
        $this->days_into_week = date('N', strtotime(date('Y-m-d')));
        $this->endThisWeek = date('Y-m-d', strtotime($this->startThisWeek.' + '.($this->days_into_week - 1).' days')).' 23:59:59';
        $this->endLastWeek = date('Y-m-d', strtotime($this->startLastWeek.' + '.($this->days_into_week - 1).' days')).' 23:59:59';
    }

    /*




$data = [
    'labels' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
    'datasets' => [[
        'data' =>[8, 7, 8, 9, 6],
        'backgroundColor' => '#f2b21a',
        'borderColor' => '#e5801d',
        'label' => 'Legend'
    ]]
];
$options = [];
$attributes = ['id' => 'example', 'width' => 500, 'height' => 500];
$Line = new ChartJs\ChartJS('line', $data, $options, $attributes);

// Echo your line
echo $Line;
echo 'done';
    */
}
