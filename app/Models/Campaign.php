<?php

class Campaign extends Eloquent{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'corporate_citizenship_campaigns';
	
	public function getPreviewAttribute()
	{
		$preview = substr($this->body, 0, 35);	
		$preview = str_replace("<br/>", "", $preview);
		$preview = str_replace("<p>", "", $preview);
		$preview = str_replace("</p>", "", $preview);
		$preview = str_replace("<br />", "", $preview);
		$preview = str_replace("<br>", "", $preview);
		
		return $preview;
	}
	
	public function getSeenByAttribute()
	{
		return 0;
	}
	
	public function getAnnouncementAttribute()
	{
		return Announcement::where('campaign_id', $this->id)->first();
	}
	
}