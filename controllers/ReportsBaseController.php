<?php

use Carbon\Carbon;

class ReportsBaseController extends BaseController {

    protected $fromDate;
    protected $toDate;
    protected $csvURL;
	
	public function __construct()
    {
        $this->beforeFilter('canAccessSystemReports');
    }

}