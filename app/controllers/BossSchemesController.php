<?php

class BossSchemesController extends BOSSController {

    /**
     * Display the initial screen listing the assigned schemes
     */
    public function welcome()
    {
        $this->layout = View::make('layouts.admin_welcome_schemes');

        $schemes = Auth::user()->activeSchemes() ?  : Auth::user()->scheme_number;

        if (!$schemes || $schemes->count() <= 1)
        {
            if(Auth::user()->isInstaller == 1)
            {
                return Redirect::intended('prepago_installer');
            }
            return Redirect::to('welcome');
        }

        $schemesInfo = [];
        foreach ($schemes as $scheme)
        {
            $schemesInfo[] = [
                'id'   => $scheme->scheme_number,
                'scheme_name'   => $scheme->scheme_nickname ?  : $scheme->company_name,
                'scheme_desc'   => $scheme->scheme_description,
                'simulator'   => $scheme->simulator,
                'dl'   => $scheme->dataLogger,
                'status_debug'   => $scheme->status_debug,
                'scheme_number' => $scheme->scheme_number,
				'status_checked' => $scheme->status_checked,
				'scheme_status' => $scheme->status_ok,
				'status'		=> $scheme->status,
				'statusCss'		=> $scheme->statusCss,
                'green'         => Customer::getNormalCustomers($scheme->id)->count(),
                'yellow'        =>  Customer::getPendingCustomers($scheme->id)->count(),
                'red'           => Customer::getShutOffCustomers($scheme->id)->count(),
                'white'           => Customer::getEmptyCustomers($scheme->id)->count(),
            ];
        }

        $this->layout->page = View::make('boss/welcome', ['schemes' => $schemesInfo]);
    }

	public function welcome_scheme_info()
	{
			
		$scheme_id = Input::get('scheme_id');
		
		$scheme_data = Scheme::where('scheme_number', $scheme_id)->first();
		$scheme_data->sim = $scheme_data->sim;
		$scheme_data->track = $scheme_data->tracking->first();
		$scheme_data->track_log = unserialize($scheme_data->getTrackLog());
		$scheme_data->statusCode = $scheme_data->statusCode;
		$scheme_data->track->uptime_percentage = $scheme_data->onlinePercentage['percent']; // new way to calculate last 24hrs percent 
		$scheme_data->track_log = $scheme_data->onlinePercentage['logs']; // new way to calculate last 24hrs percent 
		
		try {
			if($scheme_data->sim) {
				$scheme_data->extra = $scheme_data->sim->extra;
				if(!empty($scheme_data->extra)) {
					$scheme_data->extra = unserialize($scheme_data->extra);
					if(!is_object($scheme_data->extra) && !is_array($scheme_data->extra)) {
						if(isset($scheme_data->extra->last_network_time) && isset($scheme_data->extra->last_mcc_mnc))
							$scheme_data->extra->last_network_time = $scheme_data->extra->last_mcc_mnc;
						
						$scheme_data->extra->last_network_time_formatted = \Carbon\Carbon::parse($scheme_data->extra->last_mcc_mnc)->diffForHumans();
					}
				}
			}
			
		} catch(Exception $e) {}
		
		return $scheme_data;
	}
	
    /**
     * Set the selected scheme and use that new scheme throughout the admin
     */
    public function setScheme()
    {
        Session::put('scheme_number', Input::get('scheme_number'));

        if(Auth::user()->isInstaller == 1)
        {
            return Redirect::intended('prepago_installer');
        }
		
		if(Session::has('last_link')) {
			$last_link = Session::get('last_link');
			Session::forget('last_link');
			return Redirect::to($last_link);
		}
		
        return Redirect::to('welcome');
    }
	
	public function setSchemeAndLoadPayoutReport($schemeNumber)
    {
        Session::put('scheme_number', $schemeNumber);

        return Redirect::to('system_reports/payout_reports');
    }
	
	public function displaySchemeRSCodesPage($userID, $schemeNumber)
    {
        $user = User::findOrFail($userID);

        $scheme = Scheme::withSchemeNumber($schemeNumber)->first();
        $schemeName = $scheme ? $scheme->getSchemeDisplayName() : '';

        $rsCodes = PermanentMeterData::inScheme($schemeNumber)->isEV()->lists('ev_rs_code');

        $this->layout->page = View::make('boss/scheme_rs_codes', [
            'user'        => $user,
            'scheme_name' => $schemeName,
            'rs_codes'    => $rsCodes
        ]);
    }

    /**
     * Display the list of available schemes along with the schemes assigned to the current user
     */
    public function displayEditSchemesPage($userID)
    {
        //get operator Name
        $user = User::findOrFail($userID);

        //get a list of all scemes
        $schemes = Scheme::withoutArchived()->get();

        //get schemes for the current operator
        $userSchemes = User::find($userID)->schemes()->select('schemes.id')->lists('id');

        $this->layout->page = View::make('boss/user_schemes', [
            'schemes'           => $schemes,
            'userSchemes'       => $userSchemes,
            'user'              => $user
        ]);
    }

    /**
     * Update the user's schemes
     */
    public function updateSchemes($userID)
    {
        $schemeIDs = Input::get('schemes') ? : [];
        $user = User::findOrFail($userID);

        if ($schemeIDs)
        {
            //check schemes restrictions
            $settings = getSettings();

            $parent = User::where('id', $user->parent_id)->first();
            $children = getImmediateChildren($parent, $userID);
            $childrenSchemesCount = 0;
            foreach ($children as $child)
            {
                $childrenSchemesCount += $child->schemes()->count();
            }

            if (($childrenSchemesCount + count($schemeIDs)) > $settings['number_schemes_per_level'])
            {
                return Redirect::to('/boss' . ($userID !== Auth::user()->id ? '/' . $userID : '') . '/schemes')
                        ->with('errorMessage', 'The schemes count exceeds the maximum allowed for this level');
            }

            if ($user->schemes()->sync($schemeIDs))
            {
                return Redirect::to('/boss' . ($userID !== Auth::user()->id ? '/' . $userID : ''))
                        ->with('successMessage', 'The schemes were saved successfully');
            }
            return Redirect::to('/boss' . ($userID !== Auth::user()->id ? '/' . $userID : ''))
                    ->with('errorMessage', 'The schemes cannot be saved');
        }

        return Redirect::to('/boss' . ($userID !== Auth::user()->id ? '/' . $userID : ''));
    }
	
	public function statusCodeInfo($status)
	{
		
		
		$this->layout->page = View::make('boss.status_code_info', [
			'status' => $status,
		]);
	}

}