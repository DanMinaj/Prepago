<?php

class Announcement extends Eloquent{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'announcements';

		
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
	
	public function getViewsAttribute()
	{
		return $this->total_views;
		/*
		return AnnouncementView::where('announcement_id', $this->id)
		->count();
		*/
	}
	
	public function getCommentsAttribute()
	{
		$comments = AnnouncementComment::where('announcement_id', $this->id)
		->orderBy('ID', 'DESC')
		->get();
		
		return $comments;
	}
	
	public static function latest()
	{
		
		
		$today = date('Y-m-d');
		$latest_announcements = Announcement::orderBy('show_at', 'DESC')->whereRaw("( '$today' >= show_at AND '$today' <= stop_show_at )")->first();
		
		return $latest_announcements;
		
	}
}