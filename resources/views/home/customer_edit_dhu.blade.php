
</div>

<div><br/></div>
<h1>Edit DHU: @if($customer) {!! $customer->id !!} - {!! $customer->username !!} - @endif DHU #{!! $dhu->id !!} | {!! $date !!}</h1>


<div class="admin2">
	
	@include('includes.notifications')
 
	<table width="100%" class="table table-bordered">
	
		@if($customer)
		<tr>
			<td width="10%"><b>ID</b></td>
			<td width="90%">
				<a href="{!! URL::to('customer_tabview_controller/show', ['customer_id' => $customer->id]) !!}">
					{!! $customer->id !!}
				</a>
			</td>
		</tr>
		<tr>
			<td width="10%"><b>Customer</b></td>
			<td width="90%">
				<a href="{!! URL::to('customer_tabview_controller/show', ['customer_id' => $customer->id]) !!}">
					{!! $customer->username !!}
				</a>
			</td>
		</tr>
		@endif

	</table>
	
	<table width="100%">
		<tr>
			<td><h4>Spread Cost</h4></td>
		</tr>
		<tr>
			
			<td>
				
				<form action="{!! URL::to('edit_dhu/spread/' . $dhu->id) !!}" method="POST">
				<table class="table table-bordered" width="100%">
					<tr>
						<td colspan='2'>
							<b> <center> Enter the number of days you'd like to spread the cost over. NOTE: The current DHU entry also counts as a day.</center> </b>
						</td>
					</tr>
					<tr>
						<td width="20%">
							<input type="number" placeholder='Days e.g 2' value="2" min="2" name="days">
						</td>
						<td width="80%">
							<input type="submit" class="btn btn-primary" value="Spread">
						</td>
					</tr>
				</table>
				</form>
				
			</td>
		</tr>
	</table>
	<table width="100%" class="table table-bordered">
		@if(Session::get('affected_dhu'))
			<tr>
				<td><b><center> Affected the following DHU: </center></b></td>
			</tr>	
			@foreach(Session::get('affected_dhu') as $a)
				<tr>
					<td><b>DHU <a target="_blank" href="{!! URL::to('edit_dhu/' . $a) !!}">#{!! $a !!}</a> </b></td>
				</tr>
			@endforeach	
		@endif
	</table>
	
	<hr/>
	
	<form action="" method="POST">
	<table width="100%" class="table table-bordered">
	
	@foreach($dhu->getAttributes() as $key => $value)
		
		@if($key == 'id' || $key == 'customer_id' || $key == 'scheme_number' || $key == 'date'
		|| $key == 'cost_of_day' || $key == 'start_day_reading' || $key == 'end_day_reading'
		|| $key == 'total_usage' || $key == 'standing_charge' || $key == 'unit_charge' 
		|| $key == 'arrears_repayment' || $key == 'manual' || $key == 'o_cod')
		<tr>
			
			<td width="10%"><b>{!! $key !!}</b></td>
			<td width="90%">
				<input type="text" value="{!! $value !!}" name="{!! $key !!}">
			</td>
		</tr>
		@endif

	@endforeach
		<tr>	
			<td width="100%" colspan='2'><button style='float:right;padding-left:50px;padding-right:50px;' type="submit" class="btn btn-success">Save</button></td>
		</tr>
		
	</table>
	</form>
		
</div>

	