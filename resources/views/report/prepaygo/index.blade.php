
</div>

<div><br/></div>
<h1>PrepayGO Reports</h1>


<div class="admin2">

@if ($message1 = Session::get('successMessage'))
<div class="alert alert-success alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{{ $message1 }}
</div>
@endif

@if ($message2 = Session::get('warningMessage'))
<div class="alert alert-warning alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{{ $message2 }}
</div>
@endif

@if ($message3 = Session::get('errorMessage'))
<div class="alert alert-danger alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{{ $message3 }}
</div>
@endif

	
	 
   <ul class="nav nav-tabs" style="margin: 30px 0">
    
	   <li class="active"><a href="#recharges" data-toggle="tab">Recharges ({{ count($recharges) }})</a></li>
       <li><a href="#topups" data-toggle="tab">Top ups ({{ count($topups) }})</a></li>
      
	  
   </ul>
   
   <!-- Start tab content -->

   <div class="tab-content">
   
   
		<div class="tab-pane active" id="recharges" style="text-align: left">	
			<table width="100%" class="table table-bordered">
				<tr>
					<th><b>Customer</b></th>
					<th><b>Station</b></th>
					<th><b>Cost of charge</b></th>
					<th><b>Total Usage</b></th>
					<th><b>Duration</b></th>
					<th><b>Start Reading</b></th>
					<th><b>End Reading</b></th>
					<th><b>Timestamp</b></th>
				</tr>
				@foreach($recharges as $k => $r) 
					<tr @if($r->inProgress) style="background: #ccffcc;" @endif>
						<td>
							@if($r->customer)
								<a href="/customer/{{ $r->customer_id }}" target="_blank">{{ $r->customer->username }}</a>
							@else
								<a href="/customer/{{ $r->customer_id }}" target="_blank">Customer not found ({{ $r->customer_id }})</a>
							@endif
						</td>
						<td>
							@if($r->station)
								<a href="/prepaygo/stations/{{ $r->ev_meter_ID }}" target="_blank">{{ $r->station->name }} ({{ $r->station->code }})</a>
							@else
								<a href="/prepaygo/stations/{{ $r->ev_meter_ID }}" target="_blank">Station not found ({{ $r->ev_meter_ID }})</a>
							@endif
						</td>
						<td>
							&euro;{{ number_format($r->cost_of_day, 3) }}
						</td>
						<td>
							{{ number_format($r->total_usage, 3) }} kWh
						</td>
						<td>
							{{ $r->duration }}
						</td>
						<td>
							{{ $r->start_day_reading }} kWh
						</td>
						<td>
							{{ $r->end_day_reading }} kWh 
						</td>
						<td>
							{{ $r->ev_timestamp }} &horbar; {{ Carbon\Carbon::parse($r->ev_timestamp)->diffForHumans() }} 
						</td>
					</tr>
				@endforeach
			</table>
		</div>
		
		
		<div class="tab-pane" id="topups" style="text-align: left">	

			<table width="100%" class="table table-bordered">
				<tr>
					<th><b> Customer </b></th>
					<th><b> Amount </b></th>
					<th><b> Transaction # </b></th>
					<th><b> Timestamp </b></th>
				</tr>
				@foreach($topups as $k => $t)
				<tr>
					<td>@if($t->customer) <a href="/customer/{{ $t->customer_id  }}">{{ $t->customer->username }}</a> @else <a href="/customer/{{ $t->customer_id  }}">Customer not found ({{ $t->customer_id }})</a> @endif</td>
					<td> &euro;{{ $t->amount }} </td>
					<td> <a href="https://dashboard.stripe.com/payments/{{ $t->ref_number }}" target="_blank">{{ $t->ref_number }}</a> </td>
					<td> {{ $t->time_date }} &horbar; {{ \Carbon\Carbon::parse($t->time_date)->diffForHumans() }}  </td>
				</tr>
				@endforeach
			</table>
			
		</div>
		
   </div>
