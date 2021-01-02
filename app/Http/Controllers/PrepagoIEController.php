<?php

namespace App\Http\Controllers;


class PrepagoIEController extends Controller
{
    protected $layout = 'prepago_ie.layouts.main';

    public function index()
    {
        $this->layout->page = view('prepago_ie.index', [

        ]);
    }
}
