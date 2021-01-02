<table width="100%" class="table table-bordered">
<tr>
	<th><b>Customer</b></th>
	<th><b>Scheme</b></th>
	<th><b>Installation complete</b></th>
</tr>
@foreach(System::getCustomers()->white as $k => $v)
<tr>
	<td><a href="{!! URL::to('customer_tabview_controller/show/' . $v->username) !!}">{!! $v->username !!}</a></td>
	<td>
		@if($v->scheme)
			{!! $v->scheme->scheme_nickname !!} ({!! $v->scheme_number !!})
		@else
			n/a
		@endif
	</td>
	<td>{!! $v->installation_confirmed !!}</td>
</tr>
@endforeach
</table>