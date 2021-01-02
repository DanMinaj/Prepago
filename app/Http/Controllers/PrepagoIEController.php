<?php

class PrepagoIEController extends BaseController
{
    protected $layout = 'prepago_ie.layouts.main';

    public function index()
    {
        $this->layout->page = View::make('prepago_ie.index', [

        ]);
    }
}
