<?php

class CSVBaseController extends BaseController {

    protected function convertDateToFormat($format, $date)
    {
        return date($format, strtotime($date));
    }

}