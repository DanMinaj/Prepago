<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class TrackingReportController extends ReportsBaseController
{
    protected $layout = 'layouts.admin_website';

    public function index()
    {
        $this->layout->page = View::make('report/tracking/index_tracking_reports');
    }

    public function admin_tracking()
    {
        $this->layout->page = View::make('report/tracking/admin_tracking');
    }

    public function admin_tracking_page_visit_data()
    {
        $unique_pages = AdminActivity::groupBy('page')->get(['page']);

        $data = [];

        foreach ($unique_pages as $up) {
            $count = AdminActivity::where('page', $up->page)->count();
            $data[$up->page] = $count;
        }

        arsort($data);

        $data = array_slice($data, 0, 5, true);

        return $data;
    }

    public function admin_tracking_page_duration_data()
    {
        $unique_pages = AdminActivity::groupBy('page')->get(['page']);

        $data = [];

        foreach ($unique_pages as $up) {
            $total_duration = number_format(AdminActivity::where('page', $up->page)->avg('duration') / 60);
            $data[$up->page] = $total_duration;
        }

        arsort($data);

        $data = array_slice($data, 0, 5, true);

        return $data;
    }

    public function customer_registered_data()
    {
        $data = [];

        $registered_app = RegisteredPhonesWithApps::all();

        $no_web = 0;
        $no_ios = 0;
        $no_android = 0;

        foreach ($registered_app as $reg) {
            $device_uid = $reg->phone_UID;
            $platform = 'web'; // default platform is web

            if (strpos($device_uid, '-') != false) {
                $no_ios++;
                $platform = 'ios';
                continue;
            }

            if (strlen($device_uid) == 16) {
                $no_android++;
                $platform = 'android';
                continue;
            }

            $no_web++;
            continue;
        }

        $data['Web App'] = $no_web;
        $data['iOS'] = $no_ios;
        $data['Android'] = $no_android;

        return $data;
    }

    public function customer_login_tracking_data()
    {
        $data = [];

        $web_logins = CustomerActivity::where('action', 'login')->where('platform', 'web')->get();
        $ios_logins = CustomerActivity::where('action', 'login')->where('platform', 'ios')->get();
        $android_logins = CustomerActivity::where('action', 'login')->where('platform', 'android')->get();

        $data['Web App'] = $web_logins->count();
        $data['iOS'] = $ios_logins->count();
        $data['Android'] = $android_logins->count();

        return $data;
    }

    public function customer_topup_tracking_data()
    {
        $data = [];

        $web_logins = CustomerActivity::where('action', 'topup')->where('platform', 'web')->get();
        $ios_logins = CustomerActivity::where('action', 'topup')->where('platform', 'ios')->get();
        $android_logins = CustomerActivity::where('action', 'topup')->where('platform', 'android')->get();

        $data['Web App'] = $web_logins->count();
        $data['iOS'] = $ios_logins->count();
        $data['Android'] = $android_logins->count();

        return $data;
    }

    public function customer_day_activity_tracking_data()
    {
        $data = [];

        $logins = CustomerActivity::where('action', 'login')->groupBy(DB::raw("date_format(date_time, '%H')"))->get(['date_time']);

        foreach ($logins as $key=>$login) {
            $hour = new DateTime($login->date_time);
            $hour = $hour->format('H');
            $count = CustomerActivity::where('action', 'login')->whereRaw("date_format(date_time, '%H') = '".$hour."'")->count();
            $data[''.$hour] = $count;
        }

        return $data;
    }

    public function customer_day_activity_tracking_search_day()
    {
        $data = [];

        $day = Input::get('day');

        $logins_on_day = CustomerActivity::where('action', 'login')->whereRaw("DAYNAME(date_time) = '".$day."'")->get(['date_time']);

        foreach ($logins_on_day as $key=>$login) {
            $date_time = new DateTime($login->date_time);
            $hour = $date_time->format('H');

            if (! (array_key_exists($hour, $data))) {
                $data[$hour] = 1;
            } else {
                $data[$hour]++;
            }
        }

        return $data;
    }
}
