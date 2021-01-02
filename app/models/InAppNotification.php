<?php

class InAppNotification extends Eloquent{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'in_app_notifications';

	public function getStats()
	{
		
		$total = InAppNotification::where('body', $this->body)->where('all_schemes', 1)->count();
		$views = InAppNotification::where('body', $this->body)->where('all_schemes', 1)->where('delivered', 1)->count();
		
		return (object)[
			'total' => $total,
			'views' => $views,
		];
	}
	
}