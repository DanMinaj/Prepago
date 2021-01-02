
</div>

<div><br/></div>
<h1>Away modes currently active ({!! count($away_modes) !!})</h1>


<div class="admin2">
	
	@include('includes.notifications')
	
	<table width="100%" class="table table-bordered">
		
		<tr>
			<th width="10%"><b>PMD ID</b></th>
			<th width="10%"><b>Customer</b></th>
			<th width="10%"><b>Started</b></th>
			<th width="10%"><b>Permanent</b></th>
			<th width="10%"><b>Temperature</b></th>
			<th width="20%"><b>Actions</b></th>
		</tr>
		
		@foreach($away_modes as $a)
		<tr>
			<td> {!! $a->pmd->ID !!} </td>
			<td> @if($a->customer) <a href="{!! URL::to('customer_tabview_controller/show', ['customer_id' => $a->customer->id]) !!}">{!! $a->customer->username !!}</a> @else no customer. @endif </td>
			<td> @if($a->last_start) {!! $a->last_start->date_time !!} @else undefined @endif </td>
			<td> {!! $a->away_mode_permanent !!} </td>
			<td> 
				@if($a->customer) 
					@if($a->customer->districtMeter)
						{!! $a->customer->districtMeter->last_flow_temp !!}&deg;C
					@else
						no meter.
					@endif
				@else
					no customer.
				@endif
			</td>
			<td> 
				
				@if($a->customer)
					<a href="{!! URL::to('customer_tabview_controller/clear_away_mode', ['customer_id' => $a->customer->id]) !!}"><button type="button" class="btn btn-danger"><i class="fa fa-power-off"></i> End</button></a> 
				@else
					None
				@endif
				
			</td>
		</tr>
		@endforeach
		
		
	</table>
	
</div>


 <script type = "text/javascript">
 
      </script>

	