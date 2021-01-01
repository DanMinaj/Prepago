<br />
<div class="cl"></div>
<h1>Viewing watchdog #{{ $watchdog->id }}</h1>

<div class="admin">

@if(Session::has('successMessage'))
<div class="alert alert-success alert-block" id="support-success">
<button type="button" class="close" data-dismiss="alert">&times;</button>
{{Session::get('successMessage')}}
</div>
@endif

@if(Session::has('errorMessage'))
<div class="alert alert-danger alert-block" id="support-success">
<button type="button" class="close" data-dismiss="alert">&times;</button>
{{Session::get('errorMessage')}}
</div>
@endif
	
</div>

<div class="admin2">
	
	
	<a href='/watchdogs'>
	<button class='btn btn-primary'>
	&lt; View all
	</button>
	</a>
	
	<hr/>
	
	<a class="btn btn-primary" href="{{ URL::to('view_csv_watchdog', ['id' => $watchdog->id]) }}"><i class="fa fa-download"></i> Download as CSV</a>
	
	<a data-toggle="modal" data-id="{{$watchdog->id}}" data-target="#watchdog-email"  class="btn btn-primary email_to" href='#' class="btn btn-primary" ><i class="fa fa-envelope"></i> Send as email</a>
					
	<h2> Information </h3>
	
	<table class='table table-bordered' width="100%">
	
		<tr>
			<td>
				<b>Customer: </b>
			</td>
			<td>
				@if($watchdog->customer)
					<a href='/customer/{{ $watchdog->customer_id}}'>{{ $watchdog->customer->username }} &horbar; #{{ $watchdog->customer_id }} </a>
				@else
					<a href='/customer/{{ $watchdog->customer_id}}'>{{ $watchdog->customer_id }}</a>
				@endif
			</td>
		</tr>
		<tr>
			<td>
				<b>Started: </b>
			</td>
			<td>
			{{ $watchdog->created_at }} ({{ Carbon\Carbon::parse($watchdog->created_at)->diffForHumans() }}) by operator <b>{{ (User::find($watchdog->operator_id)) ? User::find($watchdog->operator_id)->username : '' }}</b>
			</td>
		</tr>
		<tr>
			<td>
				<b>Mode: </b>
			</td>
			<td>
				Run <b>{{ $watchdog->run_times }}</b> times, every <b>{{ $watchdog->run_every }}</b> hours.
			</td>
		</tr>
		<tr>
			<td>
				<b>Next run time: </b>
			</td>
			<td>
				@if($watchdog->completed)
					<b>Completed</b> &horbar; {{ $watchdog->completed_at }} &horbar; ({{ Carbon\Carbon::parse($watchdog->completed_at)->diffForHumans() }})</b>
				@else
					@if($watchdog->nextIteration != 'n/a')
						<b>{{ $watchdog->nextIteration }} ({{ Carbon\Carbon::parse($watchdog->nextIteration)->diffForHumans() }})</b>
					@else
						n/a
					@endif
				@endif
			</td>
		</tr>
		<tr>
			<td>
				<b>Consecutive failure attempts: </b>
			</td>
			<td>
			{{  $watchdog->failed_attempts }}/{{ $watchdog->max_failed_attempts }}
			</td>
		</tr>
		<tr>
			<td>
				<b>Progress: </b>
			</td>
			<td>
			{{ $watchdog->ran_times }}/{{ $watchdog->run_times }} &horbar; 
			@if($watchdog->run_times > 0)
				({{ (($watchdog->ran_times / $watchdog->run_times)*100) }}%)
			@else
				(0%)
			@endif
			</td>
		</tr>
	</table>
	
	<h2> Output </h2>
	
	<textarea rows='20' style='width:95%'>{{ $watchdog->telegram_returned }}</textarea>
	
</div>


<div id="watchdog-email" class="modal fade" role="dialog">
	  <div class="modal-dialog">
	  
	  <!-- Modal content-->
	  <div class="modal-content">
		
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&times;</button>
		<h4 class="modal-title">Email watchdog</h4>
	  </div>
	
		<form class="form-control" id="watchdog-email-form">
		
		<div class="modal-body">
			  
			  <input class="form-control" type="text" name="title" placeholder="Title" value="">
			  <br/>
			  <input class="form-control" type="email" name="email" placeholder="Email address">
			  
		</div>
	
		
		<div class="modal-footer">
			<button class="btn btn-primary" type="submit">Submit</button>
		</div>

		</form>
		
		</div>
		</div>
</div>
			
{{HTML::script('resources/js/util/watchdog/watchdog_tools.js')}}
