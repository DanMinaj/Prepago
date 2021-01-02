</div>

<div><br/></div>
<h1>Ping Schemes</h1>

</div>
<div class="cl"></div>
<div class="admin2">

	@if(Session::has('successMessage'))
	<div class="alert alert-success" style="padding:2%;font-size:1.2em;">
		{{ Session::get('successMessage') }}
	</div>
	@endif
	
	@if(Session::has('errorMessage'))
	<div class="alert alert-danger" style="padding:2%;font-size:1.2em;">
		{{ Session::get('errorMessage') }}
	</div>
	@endif
	
	<a href="/welcome-schemes">
		<button class="btn btn-info">
			<i class="fa fa-arrow-left"></i> Back
		</button>
	</a>
	
	
	<br/>

	<div class="alert alert-success success_msg" style="display:none;"></div>
	<div class="alert alert-danger error_msg" style="display:none;"></div>
	<hr/>
	<div class="row-fluid">
		<div class="span7">
			<button class="btn btn-primary ping_all">
				Ping all
			</button>
		</div>
		<div class="span5">
			<button data-toggle="modal" data-target="#sim_setup" style="float:right;" class="btn btn-info">
				<i class="fa fa-sim-card"></i>&nbsp;&nbsp;Activate SIM
			</button>
		</div>
	</div>
	<hr/>
	<table class="table table-bordered">
	<tr>
		<th><b>IP</b></th>
		<th><b>Scheme</b></th>
		<th><b>Last Status</b></th>
		<th><b>Actions</b></th>
	</tr>
	@foreach($schemes as $s) 
	<tr>
		<td width="10%"> {{ $s->IP }} </td>
		<td width="30%"> {{ $s->scheme_nickname }} </td>
		<td class="ping_old_status_{{ $s->scheme_number }} last_status" scheme_number="{{ $s->scheme_number }}" ip="{{ $s->IP }}"width="20%"> 
			<span class="ping_response_{{ $s->scheme_number }}"  style='{{ $s->statusCss }}'>{{ $s->status }}</span> &#8211;
			<font class="ping_time_{{ $s->scheme_number }}" style='font-size:10px;color: grey;'>{{ \Carbon\Carbon::parse($s->status_checked)->diffForHumans() }}</font>
		</td>
		<td width="44%">
			<center>
				<button class="btn btn-success ping" ip="{{ $s->IP }}" scheme_number="{{ $s->scheme_number }}"> <i class="fa fa-circle-notch"></i>&nbsp;Ping</button>
				
				<button data-toggle="modal" disabled data-target="#sim_settings" class="btn btn sim_settings" ip="{{ $s->IP }}" scheme_name="{{ $s->scheme_nickname }}" scheme_number="{{ $s->scheme_number }}"> 
				<i class="fa fa-network-wired"></i> Settings</button>
				
				<button class="btn btn-danger reboot" dl_type="{{ $s->datalogger->dl_company }}" ip="{{ $s->IP }}"> 
				<i class="fa fa-sync"></i>&nbsp;Reboot</button>
			</center>
		</td>
	</tr>
	@endforeach
	</table>
	
@include('modals.sim_setup')
@include('modals.sim_settings')


</div>