</div>

<div><br/></div>
<h1>{!! $scheme->scheme_nickname !!} SMS LOGS</h1>

</div>
<div class="cl"></div>
<div class="admin2">
	
		
	<a href="/settings/ping">
		<button class="btn btn-info">
			<i class="fa fa-arrow-left"></i> Back
		</button>
	</a>
	<hr/>
	
	@if(Session::has('successMessage'))
	<div class="alert alert-success" style="padding:2%;font-size:1.2em;">
		{!! Session::get('successMessage') !!}
	</div>
	@endif
	
	@if(Session::has('errorMessage'))
	<div class="alert alert-danger" style="padding:2%;font-size:1.2em;">
		{!! Session::get('errorMessage') !!}
	</div>
	@endif
	
	<a href='{!! URL::to("settings/" . $iccid . "/sms/toggle/block") !!}'>
	@if($scheme->block_reboots == 1)
		<button class="btn btn-warning">
			<i class="fa fa-unlock"></i> Unblock automatic reboot
		</button>
	@else
		<button class="btn btn-danger">
			<i class="fa fa-lock"></i> Block automatic reboot
		</button>
	@endif
	</a>
	
	<button onclick="window.location.reload()" class="btn btn-primary pull-right">
		<i class="fa fa-sync"></i>
	</button>
	
	<hr/>
	
	<table width="100%" class="table table-bordered">
		<tr>
			<th><b>ID</b></th>
			<th width="30%"><b>Time</b></th>
			<th><b>Text</b></th>
			<th><b>Status</b></th>
			<th><b>Direction</b></th>
		</tr>
		@foreach($sms as $k => $s)
		
		<tr>
			<td> {!! $k !!} </td>
			<td> {!! $s->timestamp !!} &horbar; ({!! Carbon\Carbon::parse($s->timestamp)->diffForHumans() !!})</td>
			<td> {!! $s->text !!} </td>
			<td> {!! $s->status !!} </td>
			<td> {!! ($s->direction == 'MT SMS') ? '<b>outgoing</b>' : 'reply/incoming' !!} </td>
		</tr>
	
		@endforeach
	
	</table>

</div>

</div>