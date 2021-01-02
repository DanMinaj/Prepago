
</div>

<div><br/></div>
<h1>{{ Scheme::find(Auth::user()->scheme_number)->scheme_nickname }} - Scheme Settings</h1>


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
    
	  @if(Auth::user()->isUserTest())
       <li class="active"><a href="#1" data-toggle="tab">DataLogger Settings</a></li>
       <li><a href="#2" data-toggle="tab">Meter Dictionary Settings</a></li>
       <li><a href="#3" data-toggle="tab">Operators</a></li>
	   <li><a href="#6" data-toggle="tab">Setup</a></li>
       <li><a href="#5" data-toggle="tab">Shut-off periods</a></li>
	   <li><a href="#4" data-toggle="tab">Units</a></li>
	  @else
		 <li class="active"><a href="#4" data-toggle="tab">Units</a></li>
	  @endif

	  
   </ul>
   
   <div class="tab-content">
   <!-- Start tab content -->
   
   
	<div class="tab-pane @if(Auth::user()->isUserTest()) active @endif" id="1" style="text-align: left">	

	
	<form action="" method="POST">
		<table width="100%">
			<tr>
				<input type="hidden" name="dl_id" value="{{ $dl->id }}"/>
				<input type="hidden" name="toggle_cme3100" value="1"/>
				<td width="15%">
					<b>CMe3100 In Use</b>
				</td>
				<td width="10%">
					<input name="cme3100" type="checkbox" @if($dl->cme3100_in_use) checked='true' @else @endif data-toggle="toggle" data-onstyle="primary">
				</td>
				<td width="35%">
					<button type="submit" class="btn btn-primary">Save</button>
				</td>
				
				<td width="30%">
					
					<div class=""></div>
					<a href="{{ URL::to('/datalogger?s=' . $dl->scheme_number . '&type=meter') }}">
						<button type="button" class="btn btn-primary">Read Meters</button>
					</a>
					&nbsp;
					<a href="{{ URL::to('/datalogger?s=' . $dl->scheme_number . '&type=scu') }}">
						<button type="button" class="btn btn-warning">Read SCUs</button>
					</a>
					&nbsp;
					<a href="{{ URL::to('/settings/ping') }}">
						<button type="button" class="btn btn-success">Ping SIMs</button>
					</a>
								
				</td>	
			</tr>
		</table>
	</form>
	
	<form action="" method="POST">
		<table width="100%">
				
				@if($dl->cme3100_in_use)
				<tr>
				<td style='vertical-align:top;' width="100%">
				<table width="100%">	
					<tr>
						<!-- Left col -->
						<td style="vertical-align:top;" width="70%">
							<table width="100%">
								<tr>
									<td>
										<h3> CMe3100 settings</h3>
									</td>
								</tr>
								<tr>
									<td>
										<b> Broadband WAN IP Address </b>
									</td>
								</tr>
								<tr>
									<td>
										<input type="text" placeholder="e.g 0.0.0.0" value="{{ $dl->cme3100_ip }}" name="cme3100_ip">
									</td>
								</tr>
								<tr>
									<td>
										<b> Port </b>
									</td>
								</tr>
								<tr>
									<td>
										<input type="text" placeholder="e.g 80" value="{{ $dl->cme3100_port }}" name="cme3100_port">
									</td>
								</tr>
							</table>
						</td>
						
						<!-- Right col p-->
						<td style="vertical-align:top;" width="30%">
							<table width="100%">
								
								<tr>
									<td>
										<a target="_blank" href="http://{{ $dl->cme3100_ip }}:{{ $dl->cme3100_port }}/secured/admin/webserversettings.jsp"><button type="button" class="btn btn-primary">CMe3100 Webservice</button></a>
										<br/><br/>
									</td>
								</tr>
								
								<tr>
									<td>
										<a target="_blank" href="http://{{ $dl->cme3100_ip }}:{{ $dl->cme3100_port }}/servicemanager?view=fullpage&page=servicesettings"><button type="button" class="btn btn-warning">CMe3100 Services</button></a>
										<br/><br/>
									</td>
								</tr>
								
								
								<tr>
									<td>
										<a target="_blank" href="{{ URL::to('request') }}"><button type="button" class="btn btn-info">Test requests</button></a>
										<br/><br/>
									</td>
								</tr>
								
							</table>
						</td>
					</tr>
			
				</table>
				</td>
				</tr>
				@endif
				
				<tr>
					
					
					<td style='vertical-align:top;' width="70%">
					
					<h3> DataLogger </h3>
					<hr>
					<table width="100%">
						
						<tr>
							<td><b>ID</b></td>
						</tr>
						<tr>
							<input type="hidden" name="datalogger_id" value="{{ $dl->id }}">
							<td><input type="text" placeholder="Datalogger ID" disabled name="d_datalogger_id" value="{{ $dl->id }}"></td>
						</tr>
						
						<tr>
							<td><b>Name</b></td>
						</tr>
						<tr>
							<input type="hidden" name="datalogger_name" value="{{ $dl->name }}">
							<td><input type="text" placeholder="Datalogger Name" disabled name="d_datalogger_name" value="{{ $dl->name }}"></td>
						</tr>
						
						<tr>
							<td><b>Sim ID</b></td>
						</tr>
						<tr>
							<input type="hidden" name="old_sim_id" value="{{ $dl->sim_id }}">
							<td><input type="text" placeholder="Datalogger SIM ID" name="new_sim_id" value="{{ $dl->sim_id }}"></td>
						</tr>
						
						<tr>
							<td><b>Port 1</b></td>
						</tr>
						<tr>
							<td><input type="text" placeholder="Datalogger SIM Port 1" name="dl_sim_port1" value="{{ $dl->port1 }}"></td>
						</tr>
						
						<tr>
							<td><b>Port 2</b></td>
						</tr>
						<tr>
							<td><input type="text" placeholder="Datalogger SIM Port 2" name="dl_sim_port2" value="{{ $dl->port2 }}"></td>
						</tr>
						
					</table>			
					</td>
					
					<td style='vertical-align:top;' width="30%">
					
					<h3> DataLogger SIM </h3>
					<hr>
					<table width="100%">
							
						<tr>
							<td><b>SIM ID</b></td>
						</tr>
						<tr>
							<input type="hidden" name="datalogger_sim_id" value="{{ $dl_sim->id }}">
							<td><input type="text" placeholder="Datalogger SIM ID" disabled name="dl_sim_ID" value="{{ $dl_sim->ID }}"></td>
						</tr>
						
						<tr>
							<td><b>SIM Name</b></td>
						</tr>
						<tr>
							<td><input type="text" placeholder="Datalogger SIM Name" name="dl_sim_Name" value="{{ $dl_sim->Name }}"></td>
						</tr>
						
						<tr>
							<td><b>SIM IP Address</b></td>
						</tr>
						<tr>
							<td><input type="text" placeholder="Datalogger SIM IP Address" name="dl_sim_IP_Address" value="{{ $dl_sim->IP_Address }}"></td>
						</tr>
						
						<tr>
							<td><b>SIM ICCID</b></td>
						</tr>
						<tr>
							<td><input type="text" placeholder="Datalogger SIM ICCID" name="dl_sim_ICCID" value="{{ $dl_sim->ICCID }}"></td>
						</tr>
						
						<tr>
							<td><b>SIM MSISDN/PHONE #</b></td>
						</tr>
						<tr>
							<td><input type="text" placeholder="Datalogger SIM MSISDN / Phone #" name="dl_sim_MSISDN" value="{{ $dl_sim->MSISDN }}"></td>
						</tr>
						
						
					</table>
					
					
					
					</td>
				
				
				</tr>
		
				
				<tr>
				
					<td><br/><br/> <button style="width:30%;height:60px;" type="submit" class="btn btn-primary">Save changes</button> </td>
				
				</tr>
		
		</table>

	
	</div>
	
	
	
	<div class="tab-pane" id="2" style="text-align: left">
		
		<table width="100%">
				
				
				<tr>
						
					<td style='vertical-align:top;' width="70%">
					
						<h3> Dictionary Lookup </h3>
					
						<table width="100%">
							
							
							@if($meter)
								
								<tr>
									<td>
										<a href="{{ URL::to('settings/meter_lookup', ['id' => $meter->id]) }}">
											<button type="button" class="btn btn-primary">Edit</button>
										</a>
									</td>
								</tr>
								<tr>	
									
									<!-- Left -->
									<td>
									
										<input type="hidden" name="save" value="1">
										<table width="100%">
											<tr>
												<td><h4>Meter</h4></td>
											</tr>
											<tr>
												<td><b>Last 8 digits</b></td>
											</tr>
											<tr>
												<td><input disabled value="{{ $meter->last_eight }}" name="last_eight" type="text"></td>
											</tr>
											<tr>
												<td><b>Meter make</b></td>
											</tr>
											<tr>
												<td><input disabled value="{{ $meter->meter_make }}" name="meter_make" type="text"></td>
											</tr>
											<tr>
												<td><b>Meter model</b></td>
											</tr>
											<tr>
												<td><input disabled value="{{ $meter->meter_model }}" name="meter_model" type="text"></td>
											</tr>
										</table>
									</td>
									
									<!-- Right -->
									<td>
										<table width="100%">
											<tr>
												<td><h4>SCU</h4></td>
											</tr>
											<tr>
												<td><b>Last 8 digits</b></td>
											</tr>
											<tr>
												<td><input disabled value="{{ $meter->scu_last_eight }}" name="scu_last_eight" type="text"></td>
											</tr>
											<tr>
												<td><b>SCU make</b></td>
											</tr>
											<tr>
												<td><input disabled value="{{ $meter->scu_make }}" name="scu_make" type="text"></td>
											</tr>
											<tr>
												<td><b>SCU model</b></td>
											</tr>
											<tr>
												<td><input disabled value="{{ $meter->scu_model }}" name="scu_model" type="text"></td>
											</tr>
										</table>
									</td>
									
									<!-- Far Right -->
									<td>
										
										<input type="hidden" name="save" value="1">
										
										<table width="100%">
											@foreach($meter_lookup as $ml)
												<tr>
													<td>
														<input name="ml[{{ $ml->id }}]" value="{{ $ml->applied(Auth::user()->scheme_number) }}" type="checkbox" @if ($ml->applied(Auth::user()->scheme_number)) checked @else @endif >
													</td>
													<td>
														{{ $ml->meter_make }} {{ $ml->meter_model }}  [{{ $ml->last_eight }}]
													</td>
												</tr>
											@endforeach
												<tr>
													<td><br/></td>
												</tr>
												<tr>
													<td colspan='2'><button type="submit" class="btn btn-success">Set as default meter configuration</button></td>
												</tr>
										</table>
									</td>
									
								</tr>
							@else
								
							<tr>
								<table width="100%">
									<tr>
										<td colspan="2">		
											<div class="alert alert-info alert-block">
												<button type="button" class="close" data-dismiss="alert">&times;</button>
												This scheme does not yet have a meter associated with it, please select an existing one from the dropdown menu - or create a new one below.
											</div>
										</td>
									</tr>
									<tr>
									
										<td width="50%" style="vertical-align:top;">
										<!-- Start left -->
											<h5>Create a <u>new</u> meter type</h5>
											<input type="text" name="n_meter_make" placeholder="Meter make e.g Danfoss">
											<br/>
											<input type="text" name="n_meter_model" placeholder="Meter model e.g SonoCollect 110">
											<br/>
											<input type="text" name="n_meter_HIU" placeholder="Meter HIU e.g Danfoss">
											<br/>
											<input type="text" name="n_last_eight" placeholder="Meter last 8 e.g D3102004">
											<br/>
											<input type="text" name="n_scu_make" placeholder="SCU make e.g elvaco" value="elvaco">
											<br/>
											<input type="text" name="n_scu_model" placeholder="SCU model e.g AMZ112" value="AMZ112">
											<br/>
											<input type="text" name="n_scu_last_eight" placeholder="SCU last 8 e.g 96152800" value="96152800">
											<br/>
											<button type="submit" name="create_meter" value="yes" class="btn btn-primary">Create meter & set</button>
										<!-- End left -->
										</td>
										
										<td width="50%" style="vertical-align:top;">
										<!-- Start right -->
											<input type="hidden" name="save" value="0">
											<h5>Select <u>existing</u> meter type</h5>
											
											<select name="meter_selected">
												<option value="0">-</option>
												@foreach($existing_meters as $m)
													<option value="{{ $m->id }}"> {{ $m->meter_model }} - {{ $m->meter_make }} - ********{{ $m->last_eight }} </option>
												@endforeach
											</select>
											
											<br/>
											<button type="submit" name="select_meter" value='yes' class="btn btn-primary">Set as schemes' meter</button>
										<!-- End right -->
										</td>
										
									</tr>
								</table>
							</tr>
							
							@endif
						
						</table>
						
					</td>
				
				</tr>
		</table>
		</form>
	</div>
	
	
	<div class="tab-pane" id="3" style="text-align: left">		
	
	<form action="{{ URL::to('settings/scheme_settings/add_operator') }}" method="POST">
		<div class="row-fluid">
			<div class="span3">
				<input type="text" name="username" placeholder="Operator's username">
			</div>
			<div class="span3">
				<button type="submit" class="btn btn-success"><i class="fa fa-plus"></i> Add Operator to this Scheme</button>
			</div>
			<div class="span3">
				<a href="/settings/utility_user_setup"><button type="button" class="btn btn-primary"><i class="fa fa-plus"></i> Create new Operator</button></a>
			</div>
		</div>
	</form>
		<table class="table table-bordered tablesorter sortthistable2" width="100%">	
		<thead>
			<th width="5%" style='vertical-align:middle;'>
				<b>ID</b>
			</th>
			<th width="10%" style='vertical-align:middle;'>
				<b>Username</b>
			</th>
			<th width="5%" style='vertical-align:middle;'>
				<b>Employee name</b>
			</th>
			<th width="5%" style='vertical-align:middle;'>
				<b>Installer</b>
			</th>
			<th width="30%" style='vertical-align:middle;'>
				<b>Last online</b>
			</th>
			<th width="45%" style='vertical-align:middle;'>
				<b>Tools</b>
			</th>
		</thead>
		<tbody>
		@foreach($operators as $operator) 
			
				<tr @if($operator->locked) style='background: #d9d9d9' @endif>
					
					<td style='vertical-align:middle;'>
						{{ $operator->id }}
					</td>
					
					<td style='vertical-align:middle;'>
						{{ $operator->username }}
					</td>
					
					<td style='vertical-align:middle;'>
						{{ $operator->employee_name }}
					</td>
					
					<td style='vertical-align:middle;'>
						{{ (($operator->isInstaller) ? 'installer' : 'normal') }}
					</td>
					
					
					<td style='vertical-align:middle;'>
						@if(strlen($operator->is_online_time) <= 4 || $operator->is_online_time == "0000-00-00 00:00:00" || $operator->is_online_time == null)
							N/A
						@else
						{{ $operator->is_online_time }} - {{ Carbon\Carbon::parse($operator->is_online_time)->diffForHumans() }}
						@endif
					</td>
					
					<td style='vertical-align:middle;'>
						@if(Auth::user()->isUserTest())
						<a href="{{ URL::to('settings/scheme_settings/manage_operator/' . $operator->id) }}"><button type="button" class="btn btn-success"><i class="fa fa-user-edit"></i>&nbsp;Edit</button></a>
						@if($operator->locked)
							<a href="{{URL::to('settings/scheme_settings/manage_operator/lock/' . $operator->id)}}"><button type="button" class="btn btn-danger"><i class="fa fa-lock-open"></i>&nbsp;Unlock</button></a>
						@else
							<a href="{{URL::to('settings/scheme_settings/manage_operator/lock/' . $operator->id)}}"><button type="button" class="btn btn-warning"><i class="fa fa-lock"></i>&nbsp;Lock</button></a>
						@endif
						@endif
						<form style="display:inline-block" action="{{ URL::to('settings/scheme_settings/remove_operator') }}" method="POST">
							<input type="hidden" name="operator_id" value="{{ $operator->id }}">
							<button type="submit" class="btn btn-danger"><i class="fa fa-user-minus"></i> Remove from Scheme</button>
						</form>
					</td>
				</tr>
		@endforeach
		</tbody>
		</table>
	</div>
	
	
	<div class="tab-pane @if(!Auth::user()->isUserTest()) active @endif" id="4" style="text-align: left">	
		
		<table class="table" width="100%">
			<form method="POST" action="{{ URL::to('settings/scheme_settings/manage_extra') }}">
			<input type="hidden" name="scheme_number" value="{{ Auth::user()->scheme_number }}"/>
			<tr>
				<td style="vertical-align:middle;" width="10%"><b> Scheme Daily readings </b></td>
				<td width="20%"><input type="text" value="{{ $scheme_daily_readings }}" name="scheme_daily_readings"/></td>
				<td width="70%"><input type="submit" class="btn btn-primary" value="Save"></td>
			</tr>
			</form>
		</table>

		<h2>CMe3100 Unit(s) ({{ count($cme3100_meters) }})</h2>
		@foreach($cme3100_meters as $meter) 
			<table class="table table-bordered" width="100%">
				
					<tr>
						<th width="10%" style='vertical-align:middle;'>
							<b>Username</b>
						</th>
						<th width="10%" style='vertical-align:middle;'>
							<b>Meter number</b>
						</th>
						<th width="10%" style='vertical-align:middle;'>
							<b>SCU number</b>
						</th>
						<th width="7%" style='vertical-align:middle;'>
							<b>House #</b>
						</th>
						<th width="7%" style='vertical-align:middle;'>
							<b>Reading</b>
						</th>
						<th width="7%" style='vertical-align:middle;'>
							<b>Temp</b>
						</th>
						<th width="10%" style='vertical-align:middle;'>
							<b>SCU</b>
						</th>
						<th width="28%" style='vertical-align:middle;'>
							<b>Manage</b>
						</th>
						<th width="10%" style='vertical-align:middle;'>
							<b>Status</b>
						</th>
					</tr>
				<tr style="@if($meter->installation_confirmed == 0) background:#ff9d9d; @else @endif">				
					<td style='vertical-align:middle;'>
						@if($meter->assigned) 
							<a href="{{URL::to('customer_tabview_controller/show/' . $meter->assigned->id)}}" target="_blank"><b><u>{{ $meter->username }}</u></b></a>
						@else
							{{ $meter->username }} 
						@endif
					</td>				
					
					<td style='vertical-align:middle;'>
						{{ $meter->meter_number }}
					</td> 
					
					<td style='vertical-align:middle;'>
						{{ $meter->scu_number }}
					</td> 
					
					<td style='vertical-align:middle;'>
						{{ $meter->house_name_number }}
					</td>
					
					<td style='vertical-align:middle;'>
						@if($meter->districtMeter)
							<span id="m{{$meter->ID}}">{{ $meter->districtMeter->latest_reading }} kWh</span>
						@else
							<span id="m{{$meter->ID}}">{{ $meter->last_reading }} kWh</span>
						@endif
					</td>
					
					<td style='vertical-align:middle;'>
						@if($meter->districtMeter)
							<span id="m{{$meter->ID}}">{{ $meter->last_temp }} &deg;C</span>
						@else
							<span>{{ $meter->last_temp }}&deg;C</span>
						@endif
					</td>
					
					
					<td style='vertical-align:middle;'>
						@if($meter->last_valve != null)
							
							<span id="m{{$meter->ID}}S">
							<span style="font-weight:bold;color: {{ ($meter->last_valve == 'open') ? 'green' : 'red' }}">{{ $meter->last_valve }}</span>
							<br/>({{ (Carbon\Carbon::parse($meter->last_valve_time)->diffForHumans()) }})</span>
						@else
							<span id="m{{$meter->ID}}S">unchecked</span>
						@endif
					</td>
					
					<td style='vertical-align:middle;'>
						
						@if($meter->customer)
						<a href="{{ URL::to('customer_tabview_controller/show/' . $meter->customer->id) }}" target="_blank"><button type="button" class="btn btn-info">View</button></a>
						@endif
						<input type="hidden" id="baseInstallerURL" value="{{ URL::to('prepago_installer') }}">
						<a href="{{ URL::to('settings/scheme_settings/manage_meter/' . $meter->ID) }}"><button type="button" class="btn btn-info">Edit</button></a>
						@if($meter->districtMeter || 1==1) 
							<button type="button" onclick="meter_read_test_selective(m{{$meter->ID}}, {{$meter->ID}})" class="btn btn-primary btn_setup">Read</button>
						@endif
						<button type="button" onclick="service_control_test_on({{$meter->ID}})" class="btn btn-success btn_setup">Open</button>
						<button type="button" onclick="service_control_test_off({{$meter->ID}})" class="btn btn-danger btn_setup">Close</button>
					</td>
					
					<td style='vertical-align:middle;'>
						@if(empty($meter->getMBus('scu'))) 
							no scu <br/> 
						@endif
						@if(empty($meter->getMBus('meter'))) 
							no meter <br/> 
						@endif
						@if($meter->installation_confirmed == 0) 
							<font color='red'><i class="fas fa-exclamation-triangle"></i> installation unconfirmed</font>
						@endif
					</td>
					
				</tr>
			</table>
		@endforeach
		<div style='height:1px;width:100%;background:black;'>
		</div>
		<h2>Units ({{ count($meters) }})</h2>
		<table class="table table-bordered tablesorter sortthistable" width="100%">				
		<thead>
		<th width="8%" style='vertical-align:middle;'>
			<b>Username</b>
		</th>
		<th width="10%" style='vertical-align:middle;'>
			<b>Meter number</b>
		</th>
		<th width="10%" style='vertical-align:middle;'>
			<b>SCU number</b>
		</th>
		<th width="5%" style='vertical-align:middle;'>
			<b>House #</b>
		</th>
		<th width="11%" style='vertical-align:middle;'>
			<b>Reading</b>
		</th>
		<th width="7%" style='vertical-align:middle;'>
			<b>Temp</b>
		</th>
		<th width="10%" style='vertical-align:middle;'>
			<b>SCU</b>
		</th>
		<th width="28%" style='vertical-align:middle;'>
			<b>Manage</b>
		</th>
		<th width="10%" style='vertical-align:middle;'>
			<b>Status</b>
		</th>
		</thead>
		<tbody>
		@foreach($meters as $meter) 	
			
				<tr style="@if($meter->installation_confirmed == 0) background:#ff9d9d; @else  @if($meter->assigned) background: #a4daa7; @endif @endif">				
					<td style='vertical-align:middle;'>
						@if($meter->assigned) 
							<a href="{{URL::to('customer_tabview_controller/show/' . $meter->assigned->id)}}" target="_blank"><b><u>{{ $meter->username }}</u></b></a>
						@else
							{{ $meter->username }} 
						@endif
					</td>				
					
					<td style='vertical-align:middle;'>
						{{ $meter->meter_number }}
					</td> 
					
					<td style='vertical-align:middle;'>
						{{ $meter->scu_number }}
					</td> 
					
					<td style='vertical-align:middle;'>
						{{ $meter->house_name_number }}
					</td>
					
					<td style='vertical-align:middle;'>
						@if($meter->districtMeter)
							<span id="m{{$meter->ID}}">{{ $meter->districtMeter->latest_reading }} kWh</span>
						@else
							<span id="m{{$meter->ID}}">{{ $meter->last_reading }} kWh</span>
						@endif
					</td>
					
					<td style='vertical-align:middle;'>
						@if($meter->districtMeter)
							<span id="m{{$meter->ID}}_temp">{{ $meter->last_temp }}&deg;C</span>
						@else
							<span>{{ $meter->last_temp }}&deg;C</span>
						@endif
					</td>
					
					
					<td style='vertical-align:middle;'>
						@if($meter->last_valve != null)
							
							<span id="m{{$meter->ID}}S">
							<span style="font-weight:bold;color: {{ ($meter->last_valve == 'open') ? 'green' : 'red' }}">{{ $meter->last_valve }}</span>
							<br/>({{ (Carbon\Carbon::parse($meter->last_valve_time)->diffForHumans()) }})</span>
						@else
							<span id="m{{$meter->ID}}S">unchecked</span>
						@endif
					</td>
					
					<td style='vertical-align:middle;'>

						<input type="hidden" id="baseInstallerURL" value="{{ URL::to('prepago_installer') }}">
						<a href="{{ URL::to('settings/scheme_settings/manage_meter/' . $meter->ID) }}"><button type="button" class="btn btn-info">Edit</button></a>
						@if($meter->districtMeter || 1==1) 
							<button type="button" onclick="meter_read_test_selective(m{{$meter->ID}}, {{$meter->ID}})" class="btn btn-primary btn_setup">Read</button>
						@endif
						<button type="button" onclick="service_control_test_on({{$meter->ID}})" class="btn btn-success btn_setup">Open</button>
						<button type="button" onclick="service_control_test_off({{$meter->ID}})" class="btn btn-danger btn_setup">Close</button>
					</td>
					
					<td style='vertical-align:middle;'>
						@if(empty($meter->getMBus('scu'))) 
							no scu <br/> 
						@endif
						@if(empty($meter->getMBus('meter'))) 
							no meter <br/> 
						@endif
						@if($meter->installation_confirmed == 0) 
							<font color='red'><i class="fas fa-exclamation-triangle"></i> installation unconfirmed</font>
						@endif
					</td>
					
				</tr>
			
		@endforeach
		</tbody>
		</table>
	</div>
	
	<div class="tab-pane" id="6" style="text-align: left">		
	
	<div id="success_msg" style="display:none;" class="alert alert-success alert-block"></div>
	<div id="error_msg" style="display:none;" class="alert alert-danger alert-block"></div>
	<div id="warning_msg" style="display:none;" class="alert alert-warning alert-block"></div>

		<table width="100%" class="table table-bordered">
			<tr>
				<td>
					<input type="text" name="custom_cmd" id="custom_cmd" style="width:98%;" placeholder="Custom command e.g status">
				</td>
			</tr>
			<tr>
				<td>
					<input type="submit" style="width:98%;" class="btn btn-primary custom_cmd_send" value="Send">
				</td>
			</tr>
		</table>
		<table width="100%" class="table table-bordered">
			<tr>
				<td>
					<button class="command btn btn-primary">qset console on 12000</button>
				</td>
			</tr>
			<tr>
				<td>
					Start console telnet service (alternative to using SMS)
				</td>
			</tr>
			<tr>
				<td>
					<button class="command btn btn-primary">qset net eseye.com user pass</button>
				</td>
			</tr>
			<tr>
				<td>
					Start eseye anynet v5: Current
				</td>
			</tr>
		
			<tr>
				<td>
					<button class="command btn btn-primary">qset net eseye1 user pass</button>
				</td>
			</tr>
			<tr>
				<td>
					Starts the eseye.com connection to the Cme2100 device.
				</td>
			</tr>
			<tr>
				<td>
					<button class="command btn btn-primary">qset tmbus2 on 2400 2221</button>
				</td>
			</tr>
			<tr>
				<td>
					Starts listening on port 2221 to accept MBUS communication.
				</td>
			</tr>
			<tr>
				<td>
					<button class="command btn btn-primary">set common.tcp.tmbus2.timeout=3</button>
				</td>
			</tr>
			<tr>
				<td>
					Sets the MBUS timeout to drop MBUS connections to help connection flood error.
				</td>
			</tr>
		</table>
		
	
	</div>
	
	<div class="tab-pane" id="5" style="text-align: left">		
	
		<form action="{{ URL::to('settings/scheme_settings/shut_off', ['scheme_number' => Auth::user()->scheme_number]) }}" method="POST">
		<table width="100%" class="table table-bordered">
		<tr>
			<td colspan="4">
				<button type="submit" class="btn btn-success">Save changes</button>
			</td>
		</tr>
		<tr>
			<th width="10%"><b>Day</b></th>
			<th width="10%"><b>Shut_Off_Start</b></th>
			<th width="10%"><b>Shut_Off_End</b></th>
			<th width="10%"><b>Active</b></th>
		</tr>
		@if($shut_off_periods && $shut_off_periods->Days)
			@foreach($shut_off_periods->Days as $key => $s) 
				<tr>
					<td><input type="hidden" name="{{ $key }}|Day" value="{{ $s->Day }}">{{ $s->Day }}</td>
					<td><input type="text" name="{{ $key }}|Shut_Off_Start" placeholder="Shut_Off_Start" value="{{ $s->Shut_Off_Start }}"></td>
					<td><input type="text" name="{{ $key }}|Shut_Off_End" placeholder="Shut_Off_End" value="{{ $s->Shut_Off_End }}"></td>
					<td>
						<input type="checkbox" name="{{ $key }}|Active" value="{{ $s->Active }}" @if($s->Active) checked @endif>
					</td>
				</tr>
			@endforeach
		@else
			No shut-off periods set.
		@endif
		</table>
		</form>
	
	</div>
	
	{{ HTML::script('resources/js/installer.js?2828') }}
   <!-- End tab content -->
   </div>

</div>

		<script>
		$(document).ready(function() {
			$(function() {
				$(".sortthistable").tablesorter({
					
					headers: {
					  1: { sorter: "digit", empty : "top" }, // sort empty cells to the top
					  2: { sorter: "digit", string: "max" }, // non-numeric content is treated as a MAX value
					  3: { sorter: "digit", string: "min" }  // non-numeric content is treated as a MIN value
					},
					sortList: [[3, 0]]
					
				});
				$(".sortthistable2").tablesorter({
					
					headers: {
					  1: { sorter: "digit", empty : "top" }, // sort empty cells to the top
					  2: { sorter: "digit", string: "max" }, // non-numeric content is treated as a MAX value
					  3: { sorter: "digit", string: "min" }  // non-numeric content is treated as a MIN value
					},
					sortList: [[0, 0]]
					
				});
			});
		});
	</script>
<script>
	$(function(){
		
		function sendCommand(command, scheme) {
			
			$('#success_msg').hide();
			$('#warning_msg').html("Processing command..: '" + command + "'");
			$('#warning_msg').show();
					
			$.ajax({
				url: '/settings/customcmd',
				data: {command: command, scheme: scheme},
				type: 'POST',
				success: function(){
					
					console.log('Sent ' + command);
					
					$('#warning_msg').hide();
			
					$('#success_msg').html("Successfully sent command: '" + command + "'");
					$('#success_msg').show();
					
					//
				}, error: function() {
					console.log('unable to send command');
				}
			});
		}
		
		$('.command').on('click', function(){
			var command = $(this).text();
			var scheme = {{ Auth::user()->scheme_number }};
			sendCommand(command, scheme);
		});
		
		$('#custom_cmd').on('keyup', function(e){
			if(e.which == 13) {
				var command = $(this).val();
				var scheme = {{ Auth::user()->scheme_number }};
				sendCommand(command, scheme);
				$(this).val('');
			}
		});
		
		$('.custom_cmd_send').on('click', function(e){
			var command = $('#custom_cmd').val();
			var scheme = {{ Auth::user()->scheme_number }};
			sendCommand(command, scheme);
			$('#custom_cmd').val('');
		});
		
		
		
	});
</script>
