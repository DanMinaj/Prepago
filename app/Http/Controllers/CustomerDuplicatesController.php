<?php

use \Illuminate\Support\Facades\Redirect;

class CustomerDuplicatesController extends \BaseController {

	protected $layout = 'layouts.admin_website';
	private $validator;
	
	public function __construct(BaseValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	
    public function index()
    {
        $schemes = Scheme::orderBy('scheme_number', 'ASC')->get();
		
		$dups = DB::select('SELECT username, COUNT(*) c FROM customers where username != "" and deleted_at is not null GROUP BY username HAVING c > 1');
		
		$output = array();
		foreach ($dups as $dup){
			
			$username = $dup->username;
			$results = DB::select('SELECT * FROM customers, district_heating_meters where customers.username = ? and customers.meter_ID = district_heating_meters.meter_ID;', [$dup->username]);
			
			$output[] = $results;
			//print_r($results);echo '<br/><br/><br/><br/>';
		}
		View::share('fromSystemReports', 1);
        $this->layout->page = View::make('customerduplicates/index', array('customerduplicates' => $output));
    }


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}


}
