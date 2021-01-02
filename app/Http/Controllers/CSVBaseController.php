<?php

namespace App\Http\Controllers;

class CSVBaseController extends Controller
{
    protected function convertDateToFormat($format, $date)
    {
        return date($format, strtotime($date));
    }
}
