<?php

class MessageController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function single_customer()
    {
        $customers = Customer::where('scheme_number', '=', Auth::user()->scheme_number)->get();

        $this->layout->page = View::make('home/customer_messaging_single_customer', ['customers' => $customers]);
    }

    public function search_single_customer()
    {
        $search_key = Input::get('search_box');

        $customers = DB::table('customers')
        ->where('scheme_number', '=', Auth::user()->scheme_number)
        ->where('username', 'like', '%'.$search_key.'%')
        ->orWhere('first_name', 'like', '%'.$search_key.'%')
        ->orWhere('barcode', 'like', '%'.$search_key.'%')
        ->orWhere('surname', 'like', '%'.$search_key.'%')
        ->orWhere('street1', 'like', '%'.$search_key.'%')
        ->orWhere('street2', 'like', '%'.$search_key.'%')
        ->orWhere('email_address', 'like', '%'.$search_key.'%')
        ->orWhere('mobile_number', 'like', '%'.$search_key.'%')
        ->orWhere('town', 'like', '%'.$search_key.'%')
        ->orWhere('county', 'like', '%'.$search_key.'%')
        ->orWhere('nominated_telephone', 'like', '%'.$search_key.'%')
        ->get();

        $this->layout->page = View::make('home/customer_messaging_single_customer', ['customers' => $customers]);
    }

    public function rem_smslist($customer_id)
    {
        $sms_list = Session::get('sms_list');
        $keytracker = 0;
        foreach ($sms_list as $k => $v) {
            if ($v['id'] != $customer_id) {
                $new_sms_list[$keytracker]['id'] = $v['id'];
                $new_sms_list[$keytracker]['email'] = $v['email'];
                $keytracker++;
            }
        }
        if (empty($new_sms_list)) {
            Session::forget('sms_list');
        } else {
            Session::put('sms_list', $new_sms_list);
        }

        return Redirect::to('customer_messaging/single_customer');
    }

    public function add_smslist($customer_id, $username)
    {
        if (! Session::has('sms_list')) {
            $sms_list[0]['id'] = $customer_id;
            $sms_list[0]['email'] = $username;
            Session::put('sms_list', $sms_list);

            return Redirect::to('customer_messaging/single_customer');
        } else {
            $sms_list = Session::get('sms_list');
            $keytracker = 0;
            foreach ($sms_list as $k => $v) {
                $new_sms_list[$keytracker]['id'] = $v['id'];
                $new_sms_list[$keytracker]['email'] = $v['email'];

                $keytracker++;
            }
            $new_sms_list[$keytracker]['id'] = $customer_id;
            $new_sms_list[$keytracker]['email'] = $username;
            Session::put('sms_list', $new_sms_list);

            return Redirect::to('customer_messaging/single_customer');
        }
    }

    public function check_sms_login($password)
    {
        $scheme_sms_password = Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->get()->first();

        if ($password == 'disabled') {
            return 'valid';
        }

        if ($password == $scheme_sms_password->sms_password) {
            return 'valid';
        } else {
            return 'invalid';
        }
    }

    public function send_single_sms_post()
    {
        try {
            $customer_id = Input::get('customer_id');
            $message = Input::get('message');
            $sms_charge = ((Input::get('sms_charge')) ? true : false);
            $sms_charge_premium = ((Input::get('sms_charge_premium')) ? true : false);

            $customer = Customer::find($customer_id);
            $mobile_number = $customer->mobile_number;
            $charge = 0;

            if ($sms_charge) {
                $scheme = Scheme::find($customer->scheme_number);
                $charge = $scheme->prepage_SMS_charge;
            }

            if ($sms_charge_premium) {
                $scheme = Scheme::find($customer->scheme_number);
                $charge = 0.50;
            }

            if ($customer) {
                $customer->sms($message, $charge);
            }

            return Redirect::back()->with([
                'successMessage' => 'Successfully sent SMS to '.$mobile_number.'!',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with([
                'errorMessage' => 'An error occured: '.$e->getMessage(),
            ]);
        }
    }

    public function send_single_sms($message)
    {
        $error = false;

        $scheme_sms_password = Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->get()->first();
        $password = $scheme_sms_password->sms_password;

        $sms_list = Session::get('sms_list');
        foreach ($sms_list as $k => $v) {
            $details_url = 'http://localhost/prepago_admin/sms/user_specific_message/'.$v['id'].'/'.Auth::user()->scheme_number.'/'.$password.'/'.urlencode($message).'/';

            $options = [
                CURLOPT_RETURNTRANSFER => true, // Setting cURL's option to return the webpage data
                CURLOPT_FOLLOWLOCATION => true, // Setting cURL to follow 'location' HTTP headers
                CURLOPT_AUTOREFERER => true, // Automatically set the referer where following 'location' HTTP headers
                CURLOPT_CONNECTTIMEOUT => 120, // Setting the amount of time (in seconds) before the request times out
                CURLOPT_TIMEOUT => 120, // Setting the maximum amount of time for cURL to execute queries
                CURLOPT_MAXREDIRS => 10, // Setting the maximum number of redirections to follow
                CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1a2pre) Gecko/2008073000 Shredder/3.0a2pre ThunderBrowse/3.2.1.8', // Setting the useragent
                CURLOPT_URL => $details_url, // Setting cURL's URL option with the $url variable passed into the function
            ];
            $ch = curl_init(); // Initialising cURL
            curl_setopt_array($ch, $options); // Setting cURL's options using the previously assigned array data in $options
            $data = curl_exec($ch); // Executing the cURL request and assigning the returned data to the $data variable
            curl_close($ch); // Closing cURL
            $dataJson = json_decode($data);
            if (isset($dataJson->success) && $dataJson->success == 0) {
                $error = true;
            }
        }

        $sms = new SMSMessage();
        $sms->customer_id = $v['id'];
        $sms->mobile_number = Customer::find($v['id'])->mobile_number;
        $sms->message = $message;
        $sms->date_time = date('Y-m-d H:i:s');
        $sms->scheme_number = $scheme_sms_password->id;
        $sms->charge = 0.00;
        $sms->paid = 0;
        $sms->message_sent = 1;
        $sms->save();

        //$this->layout->page = View::make('home/message_sent');
        $selectedCustomerID = Session::get('sms_list')[0]['id'];
        if ($error) {
            return Redirect::to('customer_tabview_controller/show/'.$selectedCustomerID)->with('errorMessage', 'Your SMS was not sent.');
        }

        return Redirect::to('customer_tabview_controller/show/'.$selectedCustomerID)->with('successMessage', 'Your SMS have been sent.');
    }

    public function scheme()
    {
        $currentScheme = Auth::user()->schemes()->where('scheme_number', Auth::user()->scheme_number)->first();
        $currentSchemeName = '';
        if ($currentScheme) {
            $currentSchemeName = $currentScheme->scheme_nickname ?: $currentScheme->company_name;
        }
        $all = stripos(Route::getCurrentRoute()->getPath(), 'all') ? true : false;

        $this->layout->page = View::make('home/customer_messaging_scheme', [
            'currentScheme' => $currentSchemeName,
            'all'           => $all,
        ]);
    }

    public function send_scheme_sms()
    {
        $all = stripos(Route::getCurrentRoute()->getPath(), 'all') ? true : false;
        $message = Input::get('sms');
        $userSchemes = $all ? implode(',', Auth::user()->schemes->lists('scheme_number')) : Auth::user()->scheme_number;

        if (strlen($message) > 105) {
            die('Message must be less than or equal to 105 characters!');
        }

        //$scheme_sms_password = Scheme::where('scheme_number', '=', Auth::user()->scheme_number)->get()->first();
        //$password = $scheme_sms_password->sms_password;

        //$details_url ='http://localhost/prepago_admin/sms/scheme_specific_message/' . Auth::user()->scheme_number . '/' . $password .'/'. urlencode($message) .'/';
        //$details_url ='http://www.prepago-admin.biz/prepago_admin/sms/scheme_specific_message/' . Auth::user()->scheme_number . '/' . $password .'/'. urlencode($message) .'/';
        $details_url = 'https://prepagoplatform.com/prepago_admin/sms/scheme_specific_message';

        if ($all) {
            $schemes = Auth::user()->schemes->lists('scheme_number');
            if (is_array($schemes) || $schemes instanceof Illuminate\Database\Eloquent\Collection) {
                foreach ($schemes as $k => $s) {
                    try {
                        $customers = $s->customers;
                        if ($customers instanceof Illuminate\Database\Eloquent\Collection) {
                            foreach ($customers as $k => $c) {
                                //echo $c->id . "<br/>";
                                try {
                                    $c->sms($message, 0.08);
                                } catch (Exception $p) {
                                }
                            }
                        } else {
                            return 0;
                        }
                    } catch (Exception $e) {
                    }
                }
            } else {
                return 0;
            }

            return 1;
        } else {
            $scheme = Scheme::find(Auth::user()->scheme_number);

            $customers = $scheme->customers;
            if ($customers instanceof Illuminate\Database\Eloquent\Collection) {
                foreach ($customers as $k => $c) {
                    //echo $c->id . "<br/>";
                    try {
                        $c->sms($message, 0.08);
                    } catch (Exception $e) {
                    }
                }
            } else {
                var_dump($customers);

                return 0;
            }

            return 1;
        }

        // $client = new \GuzzleHttp\Client();
         // $req = $client->post(
            // $details_url,
            // array(
                // 'body' => array(
                    // 'schemes' => $userSchemes,
                    // 'message' => $message,
                // )
            // )
        // );

        // $data = $req->getBody();

        // $dataJson = json_decode($data);
        // if (isset($dataJson->error))
        // {
            // /*$this->layout->page = View::make('home/message_sent_error')->with('customer_emails', $dataJson->customer_names);
            // return;*/
            // return $dataJson->customer_names;
        // }

        //$this->layout->page = View::make('home/message_sent');
    }

    public function send_scheme_sms_result()
    {
        $this->layout->page = View::make('home/message_sent');
    }
}
