<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;


class ErrorController extends Controller
{
    protected $layout = 'layouts.admin_website';

    public function page_not_found()
    {
        $page = Session::get('page');

        if (Auth::check()) {
            $this->layout->page = view('error/404', [
                'page' => $page,
            ]);
        } else {
            return view('error/404_guest', [
                'page' => $page,
            ]);
        }
    }

    public function exceptions()
    {
        $date = (Input::get('date')) ? Input::get('date') : date('Y-m-d');

        $exceptions = null;
        $exception_file = null;
        $last_exception = '';

        $exception_file = $exceptions_file_1 = storage_path("logs/log-apache2handler-$date.txt");

        if (! file_exists($exceptions_file_1)) {
            $exceptions = null;
        } else {
            $lines = file($exceptions_file_1);

            $exceptions = [];
            $index = 0;

            foreach ($lines as $l) {
                if (strpos($l, "[$date ") !== false) {
                    // create a new array index

                    if (isset($exceptions[$index])) {
                        $exceptions[$index] .= "\n\n";
                    }

                    $index++;
                }

                if (! isset($exceptions[$index])) {
                    $exceptions[$index] = $l;
                    $exceptions[$index][0] = explode('] ', $l)[0];
                } else {
                    $exceptions[$index] .= (''.$l);
                }

                $exceptions[$index] = str_replace($_SERVER['REMOTE_ADDR'], 'You', $exceptions[$index]);
                $last_exception = $exceptions[$index];
            }
        }

        $this->layout->page = view('home/exceptions', [

            'exception_file' => $exception_file,
            'exceptions' => $exceptions,
            'date' => $date,
            'last_exception' => $last_exception,

        ]);
    }
}
