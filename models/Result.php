<?php

class Result{

	/**
	 * Return a failure message
	 * @param msg string, Specifies the message of the failure. 
	 * @return json
	 */
	public static function fail($msg = ''){
		$return = array(
			'success' => 0,
			'msg' => $msg
			);

		return json_encode($return);
	}
	
	/**
	 * Return a successful message
	 * @param ai array, Additional information that needs to be returned to the client.
	 * @return json
	 */
	public static function success($ai = array()){
		$return = array(
			'success' => 1,
			'ai' => $ai
			);

		return json_encode($return);
	}


}