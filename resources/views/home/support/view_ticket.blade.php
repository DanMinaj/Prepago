<br />
<div class="cl"></div>
<h1>Viewing issue #{!!$issue->id!!}</h1>
<div class="admin">
		
@if(Session::has('successMessage'))
<div class="alert alert-success alert-block" id="support-success">
<button type="button" class="close" data-dismiss="alert">&times;</button>
{!!Session::get('successMessage')!!}
</div>
@endif

@if(Session::has('errorMessage'))
<div class="alert alert-danger alert-block" id="support-success">
<button type="button" class="close" data-dismiss="alert">&times;</button>
{!!Session::get('errorMessage')!!}
</div>
@endif

<style>
.operator_avatar{
	background:black;
	border-radius:2px;
	border:1px solid black;
	width:100px;
	height:100px;
}
.my_reply{
	background: #006dcc;
	color: white;
	border-radius: 5px;
}
.reply{
	text-align: left;
    padding-top: 5px;
    padding-left: 13px;
    border-left: 1px solid #ccc;
}
</style>

		<table width="100%">
				
				<tr>
					<td>
						<a href="{!! URL::to('support') !!}">
							<button type='button' class='btn '>
							&lt;&lt; Go back
							</button>
						</a><br/><br/>
					 </td>
				</tr>
				
				<tr>
					<td>
						<h2>
							{!!$issue->issue_title!!}
							@if(!$issue->resolved)
							<a style="float:right;" href="{!! URL::to('support/mark_solved', ['id' => $issue->id]) !!}">
								<button type='button' class='btn btn-success'>
									<i class='fa fa-check'></i> Mark solved
								</button>
							</a>
							@else
							<a style="float:right;" href="{!! URL::to('support/mark_reopened', ['id' => $issue->id]) !!}">
								<button type='button' class='btn btn-warning'>
									<i class='fa fa-lock-open'></i> Re-open
								</button>
							</a>
							@endif
						</h2> 
						<hr/>
						<h4>Customer: <a href="{!!$issue->customerLink!!}"> {!! $issue->customer !!} </a> </h4>
						<h4>Scheme: {!! $issue->scheme !!} </h4>
						@if(!empty($issue->page)) <h4>Page: {!! $issue->page !!} </h4> @endif
						<h4>Current status: <span {!! $issue->statusCss() !!} >{!! $issue->status !!}</span> </h4>
						<hr/>		
					</td>
				</tr>
							
		</table>
		
		<table  @if($issue->operator_ID == Auth::user()->id) class='my_reply' @endif  width='100%'>
			
			<tr>
				<td width='20%'>
						<table width='100%'>
								<!--<tr>
										<td>  
											<div class='operator_avatar'></div>
										</td>
								</tr>-->
								<tr>
										<td>
											<br/>
											<center>
											<b style='font-size:24px;'>{!!$issue->operator!!}</b><hr/>
											<b>Ticket(s) created</b>: {!!SupportIssue::where('operator_ID', $issue->operator_ID)->count()!!}<br/>
											<b>Last active</b>: {!!Carbon\Carbon::parse(User::find($issue->operator_ID)->is_online_time)->diffForHumans()!!}
											</center>
											<br/>
										</td>
								</tr>
						</table>
				</td>
				
				<td width='55%' class='reply' valign='top'>
					<span style='margin-left:2%;'>
						{!!$issue->issue!!}
					</span>
				</td>
				
				<td width='25%' valign='bottom'>
				{!!$issue->created_at!!} (<b>{!!Carbon\Carbon::parse($issue->created_at)->diffForHumans()!!}</b>)
				<br/>
				</td>
				
			</tr>
			
		</table>
		
		
		<hr/>
		
		@foreach($issue->replies as $k=>$reply)
			
			<table @if($reply->operator_ID == Auth::user()->id) class='my_reply' @endif @if($k == count($issue->replies)-1) id='lastest_reply' @endif  width='100%'>
			
			<tr>
				<td width='20%'>
						<table width='100%'>
								<!--<tr>
										<td>  
											<div class='operator_avatar'></div>
										</td>
								</tr>-->
								<tr>
										<td>
											<br/>
											<center>
											<b style='font-size:24px;'>{!!$reply->operator!!}</b> <hr/>
											<b>Ticket(s) created</b>: {!!SupportIssue::where('operator_ID', $reply->operator_ID)->count()!!}<br/>
											<b>Last active</b>: {!!Carbon\Carbon::parse(Auth::user()->is_online_time)->diffForHumans()!!}
											</center>
											<br/>
										</td>
								</tr>
						</table>
				</td>
				
				<td width='55%' valign='top' class='reply'>
					<br/>
					<span>
						{!!$reply->reply!!}
					</span>
				</td>
				
				<td width='25%' valign='bottom'>
					{!!$reply->created_at!!} (<b>{!!Carbon\Carbon::parse($reply->created_at)->diffForHumans()!!}</b>)
					<br/><br/>
				</td>
				
			
			</tr>
			
			</table>
			
			<hr/>
		
		@endforeach
		
		
			<table width='100%'>
				<tr>
					<td><h3>Reply</h3></td>
				</tr>
				<form action="{!! URL::to('support/reply', ['id' => $issue->id]) !!}" method='POST'>
				<tr>
					<td>
					
						<textarea required style='width:100%;' rows='5' @if($issue->resolved) disabled placeholder='This issue is marked solved. You cannot reply to it.' @else placeholder='Type your reply here' @endif name="reply"></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<button @if($issue->resolved) disabled @endif style='width:100%;padding-top:2%;padding-bottom:2%;' type='submit' class='btn btn-primary'>Submit reply</button>
					</td>
					
				</tr>
				</form>
			</table>
	
		
</div>

<div class="admin2">
	
</div>
