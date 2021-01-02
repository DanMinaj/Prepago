
@if(!$meter)
	</div>
	<div><br/></div>
	<h1>{{ Scheme::find(Auth::user()->scheme_number)->company_name }} - Scheme Settings > Meter not found</h1>

	<div class="admin2">
	<div class="alert alert-danger alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	Meter not found in this scheme.
	</div>
	</div>
@else
</div>

<div><br/></div>
<h1>{{ Scheme::find(Auth::user()->scheme_number)->company_name }} - Scheme Settings > Manage Meter {{($meter->username)}}</h1>


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
	
	<a href="{{URL::to('settings/scheme_settings')}}">
	<button type="button" class="btn btn-primary">
		&lt;&lt; Go back
	</button>
	</a>
	
	<br/><br/>
	
	@if(empty($meter->getMBus('meter'))) 
		<div class="alert alert-warning alert-block">
			<table width="100%">
				<tr>
					<td>There is no mbus_address_translations insertion for <b>meter_number</b>.</td>
					<td>
						<form style='margin:0px;display:inline-block;float:right;' action="" method="POST">
							<input type="hidden" name="pmd_id" value="{{$meter->ID}}">
							<input type="hidden" name="fix_meter" value="1">
							<input type="hidden" name="meter_number" value="{{ $meter->meter_number }}">
							<button type="submit" class="btn btn-success">Fix</button>
						</form>
					</td>
				</tr>
			</table>
		</div>
	@endif
	
	@if(empty($meter->getMBus('scu'))) 
		<div class="alert alert-warning alert-block">
			<table width="100%">
				<tr>
					<td>There is no mbus_address_translations insertion for <b>scu_number</b>.</td>
					<td>
						<form style='margin:0px;display:inline-block;float:right;' action="" method="POST">
							<input type="hidden" name="pmd_id" value="{{$meter->ID}}">
							<input type="hidden" name="fix_scu" value="1">
							<input type="hidden" name="scu_number" value="{{ $meter->scu_number }}">
							<button type="submit" class="btn btn-success">Fix</button>
						</form>
					</td>
				</tr>
			</table>
		</div>
	@endif
	<form action="" method="POST">
		
		<input type="hidden" name="pmd_id" value="{{$meter->ID}}">
		<table class="table table-bordered" width="100%">
			
			<tr>
				<td style='vertical-align:middle' width="50%" colspan='2'><button type="submit" class="btn btn-success">Save changes</button></td>
			</tr>
			
			<tr>
				<td style='vertical-align:middle' width="50%"><b>Username</b></td>	
				<td style='vertical-align:middle' width="50%"><input type="text" name="username" placeholder="Username" value="{{ $meter->username }}"></td>
			</tr>
			
			<tr>
				<td style='vertical-align:middle' width="50%"><b>Meter #</b></td>	
				<td style='vertical-align:middle' width="50%">
					<input type="hidden" name="o_meter_number" placeholder="Meter #" value="{{ $meter->meter_number }}">
					<input type="text" name="meter_number" placeholder="Meter #" value="{{ $meter->meter_number }}">	
					<input type="text" name="meter_sixteen" readonly placeholder="Meter # 16" @if($meter->getMBus('meter')) value="{{ $meter->getMBus('meter')['16digit'] }}" @else value=""  @endif>
					<input type="hidden" name="o_meter_sixteen" placeholder="Meter # 16" @if($meter->getMBus('meter')) value="{{ $meter->getMBus('meter')['16digit'] }}" @else value=""  @endif>
				</td>
			</tr>
			
			<tr>
				<td style='vertical-align:middle' width="50%"><b>SCU #</b></td>	
				<td style='vertical-align:middle' width="50%">
					<input type="hidden" name="o_scu_number" placeholder="SCU #" value="{{ $meter->scu_number }}">
					<input type="text" name="scu_number" placeholder="SCU #" value="{{ $meter->scu_number }}">
					<input type="text" readonly name="scu_sixteen" placeholder="SCU # 16" @if($meter->getMBus('scu')) value="{{ $meter->getMBus('scu')['16digit'] }}" @else value=""  @endif>
				</td>
			</tr>
			
			<tr>
				<td style='vertical-align:middle' width="50%"><b>House #</b></td>	
				<td style='vertical-align:middle' width="50%"><input type="text" name="house_name_number" placeholder="House #" value="{{ $meter->house_name_number }}"></td>
			</tr>
			
			<tr>
				<td style='vertical-align:middle' width="50%"><b>Street</b></td>	
				<td style='vertical-align:middle' width="50%"><input type="text" name="street1" placeholder="Street" value="{{ $meter->street1 }}"></td>
			</tr>
			
			<tr>
				<td style='vertical-align:middle' width="50%"><b>In use</b></td>	
				<td style='vertical-align:middle' width="50%"><input type="text" name="in_use" placeholder="In use" value="{{ $meter->in_use }}"></td>
			</tr>	
			
			<tr>
				<td style='vertical-align:middle' width="50%"><b>Installation confirmed</b></td>	
				<td style='vertical-align:middle' width="50%"><input type="text" name="installation_confirmed" placeholder="Installation confirmed" value="{{ $meter->installation_confirmed }}"></td>
			</tr>	
			<input type="hidden" id="baseInstallerURL" value="{{ URL::to('prepago_installer') }}">
			@if(Auth::user()->isUserTest())
			<tr>
				<td style='vertical-align:middle' width="50%"><b>Install date</b></td>	
				<td style='vertical-align:middle' width="50%"><input type="text" name="install_date" placeholder="Install date" value="{{ $meter->install_date }}"></td>
			</tr>	
					
			<tr>
				<td style='vertical-align:middle' width="50%"><b>Readings per day</b></td>	
				<td style='vertical-align:middle' width="50%"><input type="text" name="readings_per_day" placeholder="Readings per day" value="{{ $meter->readings_per_day }}"></td>
			</tr>	
			
			<tr>
				<td style='vertical-align:middle' width="50%"><b>CMe3100</b></td>	
				<td style='vertical-align:middle' width="50%"><input type="text" name="is_cme3100" placeholder="CMe3100" value="{{ $meter->is_cme3100 }}"></td>
			</tr>	
			@else
			<input type="hidden" name="install_date" value="{{ $meter->install_date }}">
			<input type="hidden" name="readings_per_day" value="{{ $meter->readings_per_day }}">
			<input type="hidden" name="is_cme3100" value="{{ $meter->is_cme3100 }}">
			@endif
			
		</table>
	
	</form>
	
</div>
@endif