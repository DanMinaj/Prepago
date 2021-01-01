<?php

class SupportController extends BaseController {

	protected $layout = 'layouts.admin_website';
	private $names = [
		"test"
	];
	
	
	public function index()
	{
		
		if(!in_array(Auth::user()->username, $this->names))
		return Redirect::back();
		
		$un_viewed = SupportIssue::where('started', false)->where('resolved', false)->orderBy('id', 'DESC')->get();
		$started = SupportIssue::where('started', true)->where('resolved', false)->orderBy('id', 'DESC')->get();
		$resolved = SupportIssue::where('resolved', true)->orderBy('id', 'DESC')->get();
		
		$this->layout->page = View::make('home/support/index', ['un_viewed' => $un_viewed, 'started' => $started, 'resolved' => $resolved]);
		
	}
	
	public function view_ticket($id)
	{
		
		$issue = SupportIssue::where('id', $id)->first();
		
		
		if(!$issue) {
			return Redirect::back()->with('errorMessage', 'This support issue does not exist!');
		}
		
		Session::put('scheme_number', $issue->scheme_ID);
		
		if($issue->operator_ID == Auth::user()->id) {
			$this->layout->page = View::make('home/support/view_ticket', ['issue' => $issue]);
		}

		
		if(!$issue->viewed) {
			$issue->viewed = true;
			$issue->started = true;
			if($issue->receive_email && !$issue->resolved)
				$this->emailStartedIssue($issue);
			$issue->save();
		}
		
		$this->layout->page = View::make('home/support/view_ticket', ['issue' => $issue]);
		
		
	}
	
	public function mark_solved($id)
	{
		
		$issue = SupportIssue::where('id', $id)->first();
		
		if(!$issue) {
				return Redirect::back()->with('errorMessage', 'The support issue #' . $id . ' does not exist!');
		}
		
		Session::put('scheme_number', $issue->scheme_ID);
		
		if($issue->operator_ID != Auth::user()->id && !in_array(Auth::user()->username, $this->names))
		return Redirect::back();
		
		$issue->resolved = true;
		$issue->save();
		
		if($issue->receive_email)
		$this->emailSolvedIssue($issue);
		
		return Redirect::to('support')->with('successMessage', "The support issue #" . $id . " has been marked as 'solved'");
	}
	
		
	public function mark_reopened($id)
	{
		
		$issue = SupportIssue::where('id', $id)->first();
		
		if($issue->operator_ID != Auth::user()->id && !in_array(Auth::user()->username, $this->names))
		return Redirect::back();
		
		
		if(!$issue) {
				return Redirect::back()->with('errorMessage', 'The support issue #' . $id . ' does not exist!');
		}
		
		$issue->resolved = false;
		$issue->save();
		
		if($issue->receive_email)
		$this->emailReopenedIssue($issue);
		
		return Redirect::to('support')->with('successMessage', "The support issue #" . $id . " has been re-opened.'");
	}


	public function submit_issue()
	{
			
			if(empty(Input::get('issue')))
				return "<b>Error submitting issue:</b> You must fill in the description of the issue!";
		
			if(empty(Input::get('operator_email')))
				return "<b>Error submitting issue:</b> You must fill in your email!";
			
			if(empty(Input::get('issue_title')))
				return "<b>Error submitting issue:</b> You must enter a title!";
			
			$new_issue = new SupportIssue();
			
			if(!empty(Input::get('customer_ID'))) {
				$new_issue->customer_ID = Input::get('customer_ID');
				$new_issue->customer = Input::get('customer');
				$new_issue->page = 'customer_tabview';
				$new_issue->scheme_ID = Auth::user()->scheme_number;
			}
			else {
				$new_issue->customer_ID = 0;
				$new_issue->customer = 'n/a';
				$new_issue->scheme_ID = Input::get('scheme_ID');
				$new_issue->page = Input::get('page');
			}
			
			$new_issue->operator_ID = Input::get('operator_ID');
			$new_issue->operator = Input::get('operator');
			$new_issue->operator_email = Input::get('operator_email');
			$new_issue->issue_title = Input::get('issue_title');
			$new_issue->issue = Input::get('issue');
			$new_issue->receive_email = (Input::get('receive_email') == 'on') ? true : false;
			$new_issue->save_email = (Input::get('save_email') == 'on') ? true : false;
			$new_issue->viewed = false;
			$new_issue->started = false;
			$new_issue->resolved = false;
			$new_issue->save();
			
			if($new_issue->save_email) {
					Auth::user()->email_address = $new_issue->operator_email;
					Auth::user()->save();
			}
			
			if($new_issue->receive_email) {
				$this->emailCopyOfIssue($new_issue);
			}
			
			return "Your ticket has been submitted. #" . $new_issue->id . ". It is now being looked at. <a href='" . URL::to('support/tickets') . "'>View my tickets.</a>";
		
	}
	
	public function submit_reply($id)
	{
		
		$issue = SupportIssue::where('id', $id)->first();
		
		if(!$issue) {
			return Redirect::back();
		}
		
		$reply = nl2br(Input::get('reply'));
		$reply = $issue->reply($reply, Auth::user()->id);
		
		if(!is_object($reply)) {
		
			return Redirect::back()->with('errorMessage', 'Failed to reply to issue #' . $issue->id . ': ' . $reply);
			
		}
		
		$reply->operator = Auth::user()->employee_name;
		$reply->operator_email = Auth::user()->email_address;
		
		$this->emailReplyIssue($issue, $reply);
		
		return Redirect::back()->with('successMessage', 'Successfully replied to issue #' . $issue->id);
	}
	
	public function view_my_tickets()
	{
		
		
	
		$un_viewed = SupportIssue::where('started', false)->where('operator_ID', Auth::user()->id)->where('resolved', false)->orderBy('id', 'DESC')->get();
		
		$started = SupportIssue::where('started', true)->where('operator_ID', Auth::user()->id)->where('resolved', false)->orderBy('id', 'DESC')->get();
		$resolved = SupportIssue::where('resolved', true)->where('operator_ID', Auth::user()->id)->orderBy('id', 'DESC')->get();
		
		$this->layout->page = View::make('home/support/my_tickets', ['un_viewed' => $un_viewed, 'started' => $started, 'resolved' => $resolved]);
		
		
	}
	
	
	public function emailCopyOfIssue($new_issue) 
	{
		
		if($new_issue->operator == "tester")
				return;
			
		$email_to = $new_issue->operator_email;
		
		$subject = "Prepago - Created ticket Issue #" . $new_issue->id;
		$from = SystemSetting::get('email_default_from');
		$who = SystemSetting::get('email_default_name');
			
		$emailInfo = [];
        $emailInfo['email_addresses'] = [$email_to];
		$data = [];
		$data['name'] = Auth::user()->employee_name;
		$data['issue'] = $new_issue;
		$email_template = "emails.support.created_new_issue";
		
		$emailInfo_r = [];
		
		
		
		$new_ticket_email_recipients = SystemSetting::get('new_ticket_email_recipients');
		$emails = explode(PHP_EOL, $new_ticket_email_recipients);
		$emailInfo_r['email_addresses'] = [];
		foreach($emails as $r)
		{
			array_push($emailInfo_r['email_addresses'], trim(str_replace('\r', '', $r)));
		}
		
		$data_r = [];
		$data_r['name'] = 'Aidan';
		$data_r['issue'] = $new_issue;
		$email_received_template = "emails.support.received_new_issue";
		
		// Send email to Daniel and Aidan first
		Mail::send($email_received_template, $data_r, function($message) use ($emailInfo_r, $subject, $from, $who) {
            $message->from($from, $who)->subject($subject);
            $message->to($emailInfo_r['email_addresses']);
        });
		
		// Send email to the creator of the ticket
        return Mail::send($email_template, $data, function($message) use ($emailInfo, $subject, $from, $who) {
            $message->from($from, $who)->subject($subject);
            $message->to($emailInfo['email_addresses']);
        });
	}

	public function emailSolvedIssue($new_issue) 
	{
		
		$email_to = $new_issue->operator_email;
		
		$subject = "Prepago - Created ticket Issue #" . $new_issue->id;
		$from = SystemSetting::get('email_default_from');
		$who = SystemSetting::get('email_default_name');
			
		$emailInfo = [];
        $emailInfo['email_addresses'] = [$email_to];
		
		$data = [];
		$data['name'] = $new_issue->operator;
		$data['issue'] = $new_issue;

		$email_template = "emails.support.solved_issue";
		
        return Mail::send($email_template, $data, function($message) use ($emailInfo, $subject, $from, $who) {
            $message->from($from, $who)->subject($subject);
            $message->to($emailInfo['email_addresses']);
        });
	}
	
	public function emailReopenedIssue($new_issue) 
	{
		
		$email_to = $new_issue->operator_email;
		
		$subject = "Prepago - Created ticket Issue #" . $new_issue->id;
		$from = SystemSetting::get('email_default_from');
		$who = SystemSetting::get('email_default_name');
			
		$emailInfo = [];
        $emailInfo['email_addresses'] = [$email_to];
		
		$data = [];
		$data['name'] = $new_issue->operator;
		$data['issue'] = $new_issue;

		$email_template = "emails.support.reopened_issue";
		
        return Mail::send($email_template, $data, function($message) use ($emailInfo, $subject, $from, $who) {
            $message->from($from, $who)->subject($subject);
            $message->to($emailInfo['email_addresses']);
        });
	}
	
	
	public function emailStartedIssue($new_issue) 
	{
		$email_to = $new_issue->operator_email;
		
		$subject = "Prepago - Created ticket Issue #" . $new_issue->id;
		$from = SystemSetting::get('email_default_from');
		$who = SystemSetting::get('email_default_name');
			
		$emailInfo = [];
        $emailInfo['email_addresses'] = [$email_to];
	   
		$data = [];
		$data['name'] = $new_issue->operator;
		$data['issue'] = $new_issue;

		$email_template = "emails.support.started_issue";
		
        return Mail::send($email_template, $data, function($message) use ($emailInfo, $subject, $from, $who) {
            $message->from($from, $who)->subject($subject);
            $message->to($emailInfo['email_addresses']);
        });
	}
	
	public function emailReplyIssue($issue, $reply)
	{
		
		$data = [];
		$emailInfo = [];
        $emailInfo['email_addresses'] = [];
	   
	    $data['owner'] = false;
	   
		if($reply->operator_ID == $issue->operator_ID)
		{
			// don't email yourself  email last emailers
			$last_repliers = DB::table('support_issues_replies')->where('issue_ID', $issue->id)->groupBy('operator_ID')->get();
			foreach($last_repliers as $replier) {
				
				$user = User::where('id', $replier->operator_ID)->first();
				
				if($user->id == $issue->operator_ID)
					continue;
				
				if(!$user)
					continue;
				
				$email = $user->email_address;
				
				if(empty($email))
					continue;
				
				array_push($emailInfo['email_addresses'], $email);
				
			}
			
			$data['owner'] = false;
		}
		else {
			
			
			$emailInfo['email_addresses'] = [$issue->operator_email];
			$data['owner'] = true;
			
		}
		
		$subject = "Prepago - Created ticket Issue #" . $issue->id;
		$from = SystemSetting::get('email_default_from');
		$who = SystemSetting::get('email_default_name');
			

		$data['issue'] = $issue;
		$data['reply'] = $reply;
		
		$email_template = "emails.support.reply_issue";
		
        return Mail::send($email_template, $data, function($message) use ($emailInfo, $subject, $from, $who) {
            $message->from($from, $who)->subject($subject);
            $message->to($emailInfo['email_addresses']);
        });
		
	}
	
	
	public function report_a_bug()
	{
		
		
		if(!Auth::user()->isUserTest()) {
			return Redirect::to('welcome')->with([
				'You do not have permission to view this',
			]);
		}
		
		$bugs = ReportABug::orderBy('id', 'DESC')
		->where('resolved', 0)->get();
		
		$solved_bugs = ReportABug::orderBy('id', 'DESC')
		->where('resolved', 1)->get();
		
		$bugs_prepaygo = ReportABugPrepayGO::orderBy('id', 'DESC')
		->where('resolved', 0)->get();
		
		$solved_bugs_prepaygo = ReportABugPrepayGO::orderBy('id', 'DESC')
		->where('resolved', 1)->get();
		
		
		$this->layout->page = View::make('app.report_a_bug', [
			'bugs' => $bugs,
			'solved_bugs' => $solved_bugs,
			'bugs_prepaygo' => $bugs_prepaygo,
			'solved_bugs_prepaygo' => $solved_bugs_prepaygo,
		]);
		
	}

	
	public function report_a_bug_view($id, $platform = null)
	{
		
		$bug = ReportABug::find($id);
		
		if($platform == null || $platform == 'snugzone')
			$bug = ReportABug::find($id);
		else if($platform == 'prepaygo') 
			$bug = ReportABugPrepayGO::find($id);
		
		if(!$bug)
			return Redirect::to('bug/reports')->with([
				"errorMessage" => "This bug report does not exist or was deleted!",
			]);
		
		$sms_responses = SMSMessage::where('bug_report', $id)->orderBy('id', 'DESC')->get();
		
		$this->layout->page = View::make('app.report_a_bug_view', [
			'bug' => $bug,
			'sms_responses' => $sms_responses,
		]);
		
	}
	
	
	public function report_a_bug_view_mark($id, $mark, $platform = null) 
	{
		
		try {
			
			$bug = ReportABug::find($id);

			if($platform == null || $platform == 'snugzone')
				$bug = ReportABug::find($id);
			else if($platform == 'prepaygo')
				$bug = ReportABugPrepayGO::find($id);
			
			
			$bug->completed_at = date('Y-m-d H:i:s');
			$bug->resolved = $mark;
			$bug->save();
			
			$bug->sendFollowUpEmail();
			
			return Redirect::to('bug/reports')->with([
				'successMessage' => 'Successfully marked Bug #' . $bug->id . ' as resolved.',
			]);
			
		} catch(Exception $e) {
			return Redirect::back()->with([
				'errorMessage' => $e->getMessage(),
			]);
		}
		
	}
	
	public function report_a_bug_get_presets()
	{
		return Response::json([
			'presets' => SMSMessagePreset::getCategoryPresets(Input::get('category'), Input::get('customer_id')),
		]);
	}
	
	public function report_a_bug_reply($id)
	{
		
		$bug = ReportABug::find($id);

		$customer = $bug->customer;
		
		$reply = Input::get('reply');
		$charge = Input::get('charge');
		$amount = 0.00;
		
		if($charge == 'premium')
			$amount = 0.25;
		elseif($charge == 'normal')
			$amount = 0.08;
			
		$sms = $customer->sms($reply, $amount, true);
		if($sms) {
			$sms->bug_report = $id;
			$sms->save();
		}
		return Redirect::back()->with([
				'successMessage' => 'Successfully replied to Bug #' . $bug->id . '; ' . $reply,
			]);
	}
	
}