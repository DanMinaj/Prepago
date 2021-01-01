<br />
<div class="cl"></div>
<h1>Watchdogs @if(!$show_all) for {{ $customer['username'] }} @else - all watchdogs @endif </h1>

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
	
	
		<table width="100%" class="table table-bordered">
				
				<tr>
					<th width="5%">ID</th>
					<th width="23%">Customer</th>
					<th width="20%">Status</th>
					<th width="10%">Duration</th>
					<th width="15%">Completed</th>
					<th width="7%"><i class="fa fa-eye"></i></th>
					<th width="20%">Options</th>
				</tr>
				
				@foreach($watchdogs as $wd)
				<tr>
					<td> #{{ $wd->id }} </td>
					<td>
						@if($wd->customer)
							<a href='/customer/{{ $wd->customer_id}}'>{{ $wd->customer->username }} &horbar; #{{ $wd->customer_id }} </a>
						@else
							<a href='/customer/{{ $wd->customer_id}}'>{{ $wd->customer_id }}</a>
						@endif
					</td>
					<td> {{ $wd->getStatusCss() }} </td>
					<td> {{ $wd->ran_times }} / {{ ($wd->run_times) }} runs</td>
					<td> {{ ($wd->completed_at != null) ? Carbon\Carbon::parse($wd->completed_at)->diffForHumans() : "No" }} </td>
					<td> {{ ($wd->operator_viewed) ? "Yes" : "No" }} </td>
					<td> 
						<a class="btn btn-primary" href="{{ URL::to('view_watchdog', ['id' => $wd->id]) }}"><i class="fa fa-eye"></i></a>
						
						<a class="btn btn-primary" href="{{ URL::to('view_csv_watchdog', ['id' => $wd->id]) }}"><i class="fa fa-download"></i></a>
						
						<a data-toggle="modal" data-id="{{$wd->id}}" data-target="#watchdog-email"   class="btn btn-primary email_to" href='#' class="btn btn-primary" ><i class="fa fa-envelope"></i></a>
					
					</td>
				</tr>
				@endforeach
				
		</table>
	
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
