<table width="100%" class="table table-bordered">
<tr>
	<th><b>Customer</b></th>
	<th><b>Balance</b></th>
	<th><b>Temp &deg;C</b></th>
</tr>
@foreach(System::getCustomers()->blue as $k => $v)
<tr>
	<td><a href="{{ URL::to('customer_tabview_controller/show/' . $v->id) }}">Customer {{ $v->id }} {{ $v->username }}</a></td>
	<td>&euro;{{ $v->balance }}</td>
	<td>
		@if($v->districtMeter) 
			{{ $v->districtMeter->last_flow_temp }}&deg;C
		@else
			N/A
		@endif
	</td>
</tr>
@endforeach
</table>