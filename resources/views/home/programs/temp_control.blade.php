</div>
<div><br/></div>
<h1>Temperature Control Panel</h1>
<div class="admin">
   @include('includes.notifications')
   <button onclick="runAllActions()" type="button" class="btn btn-primary"> Run all actions automatically </button>
   
   <table width="100%">
	<tr>
		<td>
		<br/>
		<b>Next command schedule:</b> @if($next_tc) @if($next_tc->tomorrow) Tomorrow at 00:00 @else {!! Carbon\Carbon::parse($next_tc->time)->diffForHumans() !!} @endif @else <font color='red'>Error occured retrieving command schedule</font> @endif
		</td>
	</tr>
	<tr>
		<td>
		<br/>
		<b>Restore Progress:</b> <span class='restore_pg'>0/0</span> 
		</td>
	</tr>
	<tr>
		<td>
		<br/>
		<b>Shut Progress:</b> <span class='shut_pg'>0/0</span> 
		</td>
	</tr>
   </table>
   
   <ul class="nav nav-tabs" style="margin: 30px 0">
      <li class="active"><a href="#1" data-toggle="tab">Restore ({!! $restored->count() !!})</a></li>
      <li><a href="#2" data-toggle="tab">Shut-off ({!! $shutOff->count() !!})</a></li>
      <li><a href="#5" data-toggle="tab">Away-mode ({!! $shutOffMetersAwayMode->count() !!})</a></li>
      <li style='border-left:1px dotted #ccc;border-radius:0px;'><a href="#3" data-toggle="tab">Valve settings</a></li>
      <li style='border-left:1px dotted #ccc;border-radius:0px;'><a href="#4" data-toggle="tab">Automatic command schedule</a></li>
   </ul>
   <div class="tab-content">
     
	 <div class="tab-pane active" id="1" style="">
		 <table width="100%" class="table table-bordered">
			<tr>
				<th>
					<b>Username</b>
				</th>
				<th>
					<b>Temp</b>
				</th>
				<th>
					<b>Valve</b>
				</th>
				<th>
					<b>Opened</b>
				</th>
				<th>
					<b>Status</b>
				</th>
			</tr>
			 @foreach($restored as $m)
			<tr scheme_number="{!!$m->scheme_number!!}" id="M-{!! $m->meter_ID !!}">
				<td width="10%">
					<a target="_blank" href="{!! URL::to('customer_tabview_controller/show/' . $m->username) !!}">{!! $m->username !!}</a>
				</td>
				<td width="20%">
					<div style='cursor:pointer;' onclick="check_temp({!! $m->meter_ID !!}, {!! $m->permanent_meter_ID !!}, 3)"><i class="fa fa-thermometer-quarter"></i> {!! number_format($m->last_flow_temp, 0) !!}&deg;C <font size='1dp'>- {!! Carbon\Carbon::parse($m->last_temp_time)->diffForHumans()  !!}</font></div>
				</td>
				<td width="20%">
					<div style=''>{!! $m->last_valve_status !!} <font size='1dp'>- {!! Carbon\Carbon::parse($m->last_valve_status_time)->diffForHumans()  !!}</font></div>
				</td>
				<td width="20%">
					<div style=''><i class="fa fa-chevron-up"></i> <font size='1dp'>{!! Carbon\Carbon::parse($m->last_command_sent_time)->diffForHumans()  !!}</font></div>
				</td>
				<td width="20%">
					 @if($m->last_command_sent_time != null)
					 @if($m->last_command_sent_time > $m->last_temp_time) 
							<span style="cursor:pointer;" id="restore" onclick="resend_on({!! $m->meter_ID !!}, {!! $m->permanent_meter_ID !!}, true, this)"><i class="fa fa-spinner"></i> <b>Updating..</b></span>
					 @else
							<span style="cursor:pointer;" id="restore" onclick="resend_on({!! $m->meter_ID !!}, {!! $m->permanent_meter_ID !!}, true, this)"><i class="fa fa-thermometer-full"></i> Rising..</span>
					 @endif 
					 @endif
				</td>
			</tr>
			 @endforeach
		 </table>
	 </div>
	 
	  <div class="tab-pane" id="2" style="">
		 <table width="100%" class="table table-bordered">
			<tr>
				<th>
					<b>Username</b>
				</th>
				<th>
					<b>Temp</b>
				</th>
				<th>
					<b>Valve</b>
				</th>
				<th>
					<b>Closed</b>
				</th>
				<th>
					<b>Status</b>
				</th>
			</tr>
			 @foreach($shutOff as $m)
			<tr scheme_number="{!!$m->scheme_number!!}" id="M-{!! $m->meter_ID !!}">
				<td width="10%">
					<a target="_blank" href="{!! URL::to('customer_tabview_controller/show/' . $m->username) !!}">{!! $m->username !!}</a>
				</td>
				<td width="20%">
					<div style='cursor:pointer;' onclick="check_temp({!! $m->meter_ID !!}, {!! $m->permanent_meter_ID !!}, 3)" ><i class="fa fa-thermometer-full"></i> {!! number_format($m->last_flow_temp, 0) !!}&deg;C <font size='1dp'>- {!! Carbon\Carbon::parse($m->last_temp_time)->diffForHumans()  !!}</font></div>
				</td>
				<td width="20%">
					<div style=''>{!! $m->last_valve_status !!} <font size='1dp'>- {!! Carbon\Carbon::parse($m->last_valve_status_time)->diffForHumans()  !!}</font></div>
				</td>
				<td width="20%">
					<div style=''><i class="fa fa-chevron-up"></i> <font size='1dp'>{!! Carbon\Carbon::parse($m->last_command_sent_time)->diffForHumans()  !!}</font></div>
				</td>
				<td width="20%">
					 @if($m->last_command_sent_time != null)
					 @if($m->last_command_sent_time > $m->last_temp_time) 
							<span style="cursor:pointer;" id="resend_shut_off" onclick="resend_off({!! $m->meter_ID !!}, {!! $m->permanent_meter_ID !!}, true, this)"><i class="fa fa-spinner"></i> <b>Updating..</b></span>
					 @else
							<span style="cursor:pointer;" id="resend_shut_off" onclick="resend_off({!! $m->meter_ID !!}, {!! $m->permanent_meter_ID !!}, true, this)"><i class="fa fa-thermometer-empty"></i> Dropping..</span>
					 @endif 
					 @endif
				</td>
			</tr>
			 @endforeach
		 </table>
	 </div>
	  
	   <div class="tab-pane" id="5" style="">
		 <table width="100%" class="table table-bordered">
			<tr>
				<th>
					<b>Username</b>
				</th>
				<th>
					<b>Temp</b>
				</th>
				<th>
					<b>Valve</b>
				</th>
				<th>
					<b>Used</b>
				</th>
				<th>
					<b>Status</b>
				</th>
			</tr>
			 @foreach($shutOffMetersAwayMode as $m)
			<tr scheme_number="{!!$m->scheme_number!!}" id="M-{!! $m->meter_ID !!}">
				<td width="10%">
					<a target="_blank" href="{!! URL::to('customer_tabview_controller/show/' . $m->username) !!}">{!! $m->username !!}</a>
				</td>
				<td width="20%">
					<div style='cursor:pointer;' onclick="check_temp({!! $m->meter_ID !!}, {!! $m->permanent_meter_ID !!}, 3)" ><i class="fa fa-thermometer-full"></i> {!! number_format($m->last_flow_temp, 0) !!}&deg;C <font size='1dp'>- {!! Carbon\Carbon::parse($m->last_temp_time)->diffForHumans()  !!}</font></div>
				</td>
				<td width="20%">
					<div style=''>{!! $m->last_valve_status !!} <font size='1dp'>- {!! Carbon\Carbon::parse($m->last_valve_status_time)->diffForHumans()  !!}</font></div>
				</td>
				<td width="20%">
					<div style=''><i class="fa fa-chevron-up"></i> <font size='1dp'>{!! Carbon\Carbon::parse($m->away_mode_time)->diffForHumans()  !!}</font></div>
				</td>
				<td width="20%">
					 @if($m->last_command_sent_time != null)
					 @if($m->last_command_sent_time > $m->last_temp_time) 
							<span style="cursor:pointer;" id="resend_shut_off" away_mode='1' onclick="resend_off({!! $m->meter_ID !!}, {!! $m->permanent_meter_ID !!}, true, this)"><i class="fa fa-spinner"></i> <b>Updating..</b></span>
					 @else
							<span style="cursor:pointer;" id="resend_shut_off" away_mode='1' onclick="resend_off({!! $m->meter_ID !!}, {!! $m->permanent_meter_ID !!}, true, this)"><i class="fa fa-thermometer-empty"></i> Dropping..</span>
					 @endif 
					 @endif
				</td>
			</tr>
			 @endforeach
		 </table>
	 </div>
	 
	 
	  <div class="tab-pane" id="3" style="text-align: left">
         <div class="alert alert-info alert-block">
            <i class="fa fa-info-circle"></i> Edit valve temperature settings
         </div>
         <table class="table table-bordered">
            <tr>
               <th width='30%'><b>Name</b></th>
               <th width='30%'><b>Value</b></th>
               <th width='30%'><b>Edit</b></th>
            </tr>
            @foreach($settings as $s)
            <tr>
               <form action="{!!URL::to('settings/system_settings/save', $s->id)!!}" method="POST">
                  <td>
                     <textarea name="name" style="width:70%" placeholder="Setting Name">{!!$s->name!!}</textarea>
                     <br/>{!!$s->desc!!}
                  </td>
                  <td>
                     <textarea name="value" style="width:70%" placeholder="Setting Value">{!!$s->value!!}</textarea>
                  </td>
                  <td>
                     <a href="{!!URL::to('settings/system_settings/remove', $s->id)!!}">
                     <button type="button" class="btn btn-danger">Delete</button>
                     </a>
                     <button type="submit" class="btn btn-success">Save</button>
                  </td>
               </form>
            </tr>
            @endforeach
         </table>
      </div>
      <div class="tab-pane" id="4" style="text-align: left">
         <div class="alert alert-info alert-block">
            <i class="fa fa-info-circle"></i> Show the schedule for automatic temperature control commands 
         </div>
         <form action="{!! URL::to('temperature_control/edit_schedule') !!}" method="POST">
            <button type="submit" class="btn btn-primary">Save</button><br/><br/>
            <table width="100%" class="table table-bordered">
               <tr>
                  <th>
                     <center>Time</center>
                  </th>
               </tr>
               @foreach($schedule as $s) 
               <tr>
                  <td>
                     <center><input name="{!! $s->id !!}_input" type="text" style="text-align:center" value="{!! $s->time !!}"></center>
                  </td>
               </tr>
               @endforeach
            </table>
         </form>
      </div>
   
   
   </div>
</div>
<input type="hidden" id="baseInstallerURL" value="{!! URL::to('prepago_installer') !!}">
{!! HTML::script('resources/js/util/remote_control_panel.js?jsjsjs') !!}