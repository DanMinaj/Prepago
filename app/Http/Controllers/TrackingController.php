<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class TrackingController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function logAdminPageVisit()
    {
        if (! isset(Auth::user()->id) || empty(Auth::user()->id)) {
            return Response::json(['error' => true, 'error_msg' => 'Authentication not set']);
        }

        if (empty(Input::get('page'))) {
            return Response::json(['error' => true, 'error_msg' => 'No page set']);
        }

        $track_entry = new AdminActivity();
        $track_entry->page = Input::get('page');
        $track_entry->duration = 0;
        $track_entry->user_id = Auth::user()->id;
        $track_entry->user_name = Auth::user()->username;
        $track_entry->ip = $_SERVER['REMOTE_ADDR'];
        $track_entry->date_time_started = date('Y-m-d H:i:s');
        $track_entry->date_time_left = date('Y-m-d H:i:s');
        $track_entry->save();

        return Response::json([
            'success' => true,
            'trackID' => $track_entry->id,
        ]);
    }

    public function logAdminPageDuration()
    {
        if (! isset($_POST['trackID'])) {
            return Redirect::back();
        }

        if (Input::get('trackID') == 0) {
            return Response::json(['error' => true, 'error_msg' => 'TrackID cannot be 0']);
        }

        if (empty(Input::get('trackID'))) {
            return Response::json(['error' => true, 'error_msg' => 'TrackID not found']);
        }

        $trackID = Input::get('trackID');
        $track_entry = AdminActivity::find($trackID);

        if (! $track_entry) {
            return Response::json(['error' => true, 'error_msg' => "Track entry not found for $trackID"]);
        }

        $track_entry->duration += 1;
        $track_entry->save();

        Auth::user()->is_online = true;
        Auth::user()->is_online_time = date('Y-m-d H:i:s');
        if (strpos(Input::get('fullURL').' from '.$_SERVER['REMOTE_ADDR'], 'whos-online') === false) {
            Auth::user()->is_online_page = Input::get('fullURL').' from '.$_SERVER['REMOTE_ADDR'];
        }
        Auth::user()->save();

        return Response::json([
            'success' => true,
            'trackID' => $track_entry->id,
        ]);
    }

    public function whosOnline()
    {
        $this->layout->page = view('home/whos_online',
        [
            'whosonline' => System::whosOnline(),
            'whosoffline' => System::whosOffline(),
        ]);
    }

    public function trackScheme()
    {
        try {
            $scheme_number = Input::get('scheme_number');

            $scheme = Scheme::find($scheme_number);
            if (! $scheme) {
                throw new Exception('Cannot find scheme #'.$scheme_number);
            }
            $scheme_watch = SchemeWatch::where('scheme_number', $scheme_number)->where('active', 1)->first();
            if ($scheme_watch) {
                $scheme_watch->active = 0;
                $scheme_watch->save();

                return Response::json([
                    'success' => "<i class='fa fa-eye-slash'></i> Stopped scheme watch for ".ucfirst($scheme->scheme_nickname),
                    'btn_content' => '<i class="fa fa-eye"></i> Watch',
                ]);
            }

            $watch_type = 'for_online';

            if (strpos($scheme->status, 'Online') !== false) {
                $watch_type = 'for_offline';
            }

            if (strpos($scheme->status, 'Offline') !== false) {
                $watch_type = 'for_online';
            }

            $new_scheme_watch = new SchemeWatch();
            $new_scheme_watch->scheme_number = $scheme_number;
            $new_scheme_watch->operator_id = Auth::user()->id;
            $new_scheme_watch->watch_type = $watch_type;
            $new_scheme_watch->active = 1;
            $new_scheme_watch->run_til = null;
            $new_scheme_watch->logs = serialize([]);
            $new_scheme_watch->save();

            return Response::json([
                'success' => 'Started scheme watch for '.ucfirst($scheme->scheme_nickname),
                'btn_content' => '<i class="fa fa-eye-slash"></i> Stop Watch',
            ]);
        } catch (Exception $e) {
            return Response::json([
                'error' => $e->getMessage().' ('.$e->getLine().')',
            ]);
        }
    }

    public function trackSchemeSettings()
    {
        try {
            $var_scheme_watch_subject = Input::get('var_scheme_watch_subject');
            $var_scheme_watch_emails = Input::get('var_scheme_watch_emails');

            SystemSetting::modify('var_scheme_watch_subject', 'value', $var_scheme_watch_subject, 'scheme_watch');
            SystemSetting::modify('var_scheme_watch_emails', 'value', $var_scheme_watch_emails, 'scheme_watch');

            return Response::json([
                'success' => 'Saved chnges',
            ]);
        } catch (Exception $e) {
            return Response::json([
                'error' => $e->getMessage().' ('.$e->getLine().')',
            ]);
        }
    }
}
