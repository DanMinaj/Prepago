
</div>

<div><br/></div>
<h1>Shut offs ({!! count($shut_offs) !!})</h1>


<div class="admin2">
	
	@include('includes.notifications')
	
	<div class="alert alert-info alert-block">
	Please be aware that some customers may be restored from this list, as a result of a top-up after their shut-off. Such customers are marked green.
	</div>

	
	<!--
	Disabled temporarily 14/04/2019: Need to have it read the log files for shut off
	<form action="" method="POST">
		<table width="100%">
			
			<tr>
				<td> <b> Get shut-offs from: </b> </td>
			</tr>
			<tr>
				<td width="10%" style="vertical-align:top;"><input type="text" name="date" value="{!! $date->format('Y-m-d') !!}" placeholder="Date"></td>
				<td style="vertical-align:top;"><button type="submit" class="btn btn-primary">Search</button></td>
			</tr>
		
		</table>
	</form>
	-->
	
	
	
	<table width="100%" class="table table-bordered">
		
		<tr>
			<th width="10%"><b>Customer</b></th>
			<th width="10%"><b>Balance</b></th>
			<th width="10%"><b>Current Temp</b></th>
			<th width="10%"><b>Last Topup</b></th>
			<th width="10%"><b>Shut Off At</b></th>
			<th width="10%"><b>IOU</b></th>
		</tr>
		
		@foreach($shut_offs as $s)
		<tr style='{!! $s->style !!}'>	
			<td> 
				@if ($s->customer)
					<a href="{!! URL::to('customer_tabview_controller/show', ['customer_id' => $s->customer->id]) !!}" target="_blank">({!! $s->customer->id !!}) {!! $s->customer->username !!}</a>
				@else
					No customer associated with this meter.
				@endif
			</td>
			<td>
			   @if ($s->customer)
					&euro;{!! $s->customer->balance !!}
				@else
					No customer associated with this meter.
				@endif
			</td>
			<td>
				{!! $s->last_flow_temp !!}&deg;C
			</td>
			<td>
				@if($s->customer)
					@if($s->customer->lastTop)
						&euro;{!! $s->customer->lastTop->amount !!} ({!! $s->customer->lastTop->time_date !!})
					@else
						None
					@endif
				@endif
			</td>
			<td> {!! $s->last !!} </td>
			<td>
				@if($s->customer) 
					
					@if(!$s->customer->IOU_used) 
							Not used
					@else
						@if($s->scheme)
							Exceeded &euro;-{!! number_format($s->scheme->IOU_amount, 2) !!}
						@else
							Exceeded &euro;-5.00 (scheme not found)
						@endif
					@endif
					
				@endif
			</td>
		</tr>
		@endforeach
		
	</table>
	
	
	
</div>

	