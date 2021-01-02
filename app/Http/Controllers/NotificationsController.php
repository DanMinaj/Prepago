<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\InAppNotification;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response as Response;



class NotificationsController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function create_notifications()
    {
        try {
            $notifications = InAppNotification::where('all_schemes', 1)
            ->groupBy('body')
            ->get();

            $this->layout->page = view('home.notifications_create', [

            ]);
        } catch (Exception $e) {
        }
    }

    public function create_notifications_submit()
    {
        try {
            $title = Input::get('title');
            $body = Input::get('body');
            $image = Input::get('image');
            $dismiss_txt = Input::get('dismiss_txt');
            $dismiss_txt_url = Input::get('dismiss_txt_url');

            if ($image == 'default') {
                $image = null;
            }

            $customers = Customer::select('customers.id as id', 'customers.scheme_number as scheme_number')->join('schemes', 'customers.scheme_number', '=', 'schemes.id')
            ->whereRaw('(customers.deleted_at IS NULL AND schemes.archived = 0 OR customers.id = 1)')
            ->whereRaw('(customers.status = 1 OR customers.simulator > 0)')->get();

            $sent_to = 0;

            foreach ($customers as $k => $c) {
                $notification = new InAppNotification();
                $notification->customer_id = $c->id;
                $notification->scheme_number = $c->scheme_number;
                $notification->all_schemes = true;
                $notification->img = $image;
                $notification->title = $title;
                $notification->body = $body;
                $notification->dismiss_txt = $dismiss_txt;
                $notification->dismiss_txt_url = $dismiss_txt_url;
                $notification->save();
                $sent_to++;
            }

            return Redirect::back()->with([
                'successMessage' => "Successfully sent notification to $sent_to customers!",
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    public function all_notifications()
    {
        try {
            $notifications = InAppNotification::where('all_schemes', 1)
            ->groupBy('body')
            ->get();

            $this->layout->page = view('home.notifications_all', [
                'notifications' => $notifications,
            ]);
        } catch (Exception $e) {
        }
    }
}
