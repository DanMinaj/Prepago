@if ($message = Session::get('errorMessage'))
	<div style="color: #A94442; margin-top: 15px; padding: 10px; background-color: #F2DEDE; font-weight: bold">{!! $message !!}</div>
@endif

@if ($message = Session::get('successMessage'))
	<div style="color: #69763D; margin-top: 15px; padding: 10px; background-color: #DFF0D8; font-weight: bold">{!! $message !!}</div>
@endif

<?php
	$recommend_confirm = ($unit['installation_confirmed'] == 0 && strcmp($unit->lastReading, 'n/a') < 0 && (strlen($unit->last_valve) > 3));
?>
@if($recommend_confirm)
	<div style="color: #76653d; margin-top: 15px; padding: 10px; background-color: #f0f0d8; font-weight: bold">
		This unit has received a recommendation to 'force mark as complete'.
	</div>
@endif

<div class="title"><div class="ico_units"></div><h1>Edit @if ($unit['ev_rs_code']) EV @endif Unit</h1></div>

<div class="iniSetup-container">

	<div class="iniSetupTitle">Installation:</div>
	
	@if($unit['installation_confirmed'] == 1)
	<div class="test_msg" style="color: #4caf50;">Confirmed</div>
		{!! Form::open(array('url' => 'prepago_installer/incomplete-install')) !!}
		{!! Form::hidden('unitID', $unitID) !!}
		{!! Form::submit('Mark incomplete', array('style' => 'font-size:0.8em;background-color: #ffdbcf;; border: 1px solid #ca0000; color: #ca0000; font-weight: bold;', 'class' => 'btn_setup')) !!}
		{!! Form::close() !!}
	@else
	<div class="test_msg" style="color: #fc766c;">Incomplete</div>
	@if($recommend_confirm || 1==1)
		{!! Form::open(array('url' => 'prepago_installer/complete-install')) !!}
		{!! Form::hidden('unitID', $unitID) !!}
		{!! Form::submit('Force mark as complete', array('style' => 'background-color: #f0f0d8; border: 1px solid #776544; color: #776544; font-weight: bold;', 'class' => 'btn_setup')) !!}
		{!! Form::close() !!}
	@endif

	@endif
	<div class="clear"></div>
</div>

<div id="meter_read_test" class="iniSetup-container">

	<div class="iniSetupTitle">Meter read test</div>
	
	<div style='font-size: 11px;'>Previous: {!! $unit->lastReading !!} kWh | {!! $unit->lastTemp !!}&deg; @if($unit->lastPoll != 'n/a') ({!! Carbon\Carbon::parse($unit->lastPoll)->diffForHumans() !!}) @endif</div>

	<div class="test_msg"></div>
	
	<button class="btn_setup" onclick="meter_read_test()">Test</button>

	<div class="clear"></div>
</div>

<div id="service_control_test" class="iniSetup-container">

	<input type="hidden" name="command_port" value="2221">
	<input type="hidden" name="command_attempts" value="3">
	<div class="iniSetupTitle">Service Control Port</div>
	
	@if(strlen($unit->last_valve) > 3) 
		<span style="font-size:11px;">
		<b>Last valve status:</b> 
			@if($unit->last_valve == 'closed') 
				<span style="color: red">Closed</span>
			@else 
				<span style="color: green">Open</span>
			@endif 
			({!! Carbon\Carbon::parse($unit->last_valve_time)->diffForHumans() !!})
		</span>
	@else
		@if($unit->scu_fails > 0) 
			<span style="font-size:11px;">
				<span style="color: red">Problem hardware problem with SCU</span><br/>
				Communication attempts failed <b>{!! $unit->scu_fails !!}</b> times in a row
			</span>
		@endif
	@endif
	<div class="test_msg"></div>
	
	<button class="btn_setup_other" onclick="service_control_test_on()">Test On</button>
	<button class="btn_setup_other" onclick="service_control_test_off()">Test Off</button>
	
	@if ($unit['scu_type'] == 'm')
		<button class="btn_setup_other" onclick="telegramAndCheckValveBtnsTest('check_valve')">Check Valve</button>
	@endif

	<div class="clear"></div>
</div>

@if($unit['heat_port'] != -1)
<div id="heat_control_test" class="iniSetup-container">

	<div class="iniSetupTitle">Heat Control Port </div>

	<div class="test_msg"></div>
	
	<button class="btn_setup_other" onclick="heat_control_test_on()">Test On</button>
	<button class="btn_setup_other" onclick="heat_control_test_off()">Test Off</button>

	<div class="clear"></div>
</div>
@endif


<div id="completeInstall" class="iniSetup-container">

	<div class="iniSetupTitle">Complete Installation</div>

	{!! Form::open(array('url' => 'prepago_installer/complete-install')) !!}
	{!! Form::hidden('unitID', $unitID) !!}
	{!! Form::submit('Mark as complete', array('class' => 'btn_setup')) !!}
	{!! Form::close() !!}

	<div class="clear"></div>

</div>

<div class="clear"></div>

<div id="mbusmbus" class="max-container" style="margin-top: 1em;">
	{!! Form::open(array('method' => 'POST', 'url' => '/prepago_installer/edit-unit/' . $unitID)) !!}
		<div class="iniSetup-container">

			@if ($unit['ev_rs_address'])
				<div class="iniSetupTitle">EV Recharge Station</div>

				<div class="edit-unit">
					<p>Address:</p>
					{{ $unit['ev_rs_address'] }}
				</div>

				<div class="edit-unit">
					<p>Code:</p>
					<input type="text" name="ev_rs_code" value="{{ $unit['ev_rs_code'] }}" />
				</div>
			@else
				<div class="iniSetupTitle">Address</div>

				<div class="edit-unit">
					<p>House/Apartment Number:</p>
					{{ $unit['house_name_number'] }}
				</div>

				<div class="edit-unit">
					<p>Building/Street Name:</p>
					{{ $unit['street1'] }}
				</div>

				<div class="edit-unit">
					<p>Street2:</p>
					{{ $unit['street2'] ? $unit['street2'] : $schemeStreet2 }}
				</div>
			@endif

		</div>
		<div id="mbussim" class="iniSetup-container">
		
			<div class="iniSetupTitle">SCU Type:</div>
			<div>
				@if ($unit['scu_type'] == 'm')
					M-Bus Meter + M-Bus Relay
				@elseif ($unit['scu_type'] == 'a')
					M-Bus Meter + SIM Relay
				@elseif ($unit['scu_type'] == 'd')
					SIM Meter + SIM Relay
				@else
					{{ $unit['scu_type'] }}
				@endif
			</div>

			<div class="iniSetupTitle">SCU</div>

			<div class="edit-unit">
				<p>SCU Number:</p>
				<input type="text" name="scu_number" value="{{ $unit['scu_number'] }}" />
				@if ($unit['scu_type'] == 'm')
					@if ($SCUReady) 
						<span style="color:green;">(Ready)</span> 
					@else 
						<span style="color:red;">(Not Ready)</span>
					@endif
				@endif
			</div>

			<div class="edit-unit">
				<p>ICCID (long sim number):</p>
				{{ $simcard['ICCID'] }}
			</div>

		</div>
		<div id="mbussim" class="iniSetup-container">

			<div class="iniSetupTitle">Ports</div>

			<div class="edit-unit">
				<p>Service Control Port:</p>
				{{ $unit['scu_port'] }}
			</div>

			<div class="edit-unit">
				<p>Heat Control Port:</p>
				{{ $unit['heat_port'] === -1 ? 'None' : $unit['heat_port'] }}
			</div>

			<div class="iniSetupTitle">Secondary addresses</div>
			<div class="edit-unit">
				<p>Meter Secondary Address</p>
				{{ $unit->getAddress('16digit', 'meter') }}
			</div>
			<div class="edit-unit">
				<p>SCU Secondary Address</p>
				{{ $unit->getAddress('16digit', 'scu') }}
			</div>
		</div>
		<div class="clear"></div>
		<div id="mbussim" class="iniSetup-container">

			<div class="iniSetupTitle">Meter Details</div>

			<div class="edit-unit">
				<p>Meter Number:</p>
				<input type="text" name="meter_number" value="{{ $unit['meter_number'] }}" />
				@if ($unit['scu_type'] == 'm')
					@if ($MeterReady) 
						<span style="color:green;">(Ready)</span> 
					@else 
						<span style="color:red;">(Not Ready)</span>
					@endif
				@endif
			</div>

			<div class="edit-unit">
				<p>Meter Number 2:</p>
				{{ $unit['meter_number2'] }}
			</div>
			
			<div class="edit-unit">
				<p>Meter Baud Rate:</p>
				{{ $unit['meter_baud_rate'] }}
			</div>

			<div class="edit-unit">
				<p>Readings per day:</p>
				{{ $unit['readings_per_day'] }}
			</div>

			<div class="edit-unit">
				<p>Make:</p>
				{{ $unit['meter_make'] }}
			</div>

			<div class="edit-unit">
				<p>Model:</p>
				{{ $unit['meter_model'] }}
			</div>

			<div class="edit-unit">
				<p>Manufacturer:</p>
				{{ $unit['meter_manufacturer'] }}
			</div>

			@if ($unit['is_boiler_room_meter'] != 0)
				<div class="edit-unit"><b>Boiler Room Meter</b></div>
			@endif
			
			@if ($unit['is_bill_paid_customer'] != 0)
				<div class="edit-unit"><b>Bill Paid Customer</b></div>
			@endif

		</div>
		<div id="mbussim" class="iniSetup-container">

			<div class="iniSetupTitle">Valve Details</div>

			<div class="edit-unit">
				<p>Make:</p>
				{{ $unit['valve_make'] }}
			</div>

			<div class="edit-unit">
				<p>Model:</p>
				{{ $unit['valve_model'] }}
			</div>

			<div class="edit-unit">
				<p>Manufacturer:</p>
				{{ $unit['valve_manufacturer'] }}
			</div>

			<div class="clear"></div>

		</div>

		<div class="clear"></div>
		
		<div style="float:left"><a href="{!! URL::to('prepago_installer') !!}" class="btn btn-info">&laquo; Back</a></div>

		<div style="float:left; margin-left: 20px">{!! Form::submit('Save', array('class' => 'btn btn-primary')) !!}</div>
	{!! Form::close() !!}
	
	<div style="clear:both"></div>
</div>

<input type="hidden" id="base" value="{!! URL::to('prepago_installer') !!}">
<input type="hidden" id="unitID" value="{!! $unit['ID'] !!}">
<input type="hidden" id="meter_read_test_success" value="0">
<input type="hidden" id="service_control_teston_success" value="0">
<input type="hidden" id="service_control_testoff_success" value="0">

<script type="text/javascript" src="{!!asset('resources/js/installer.js')!!}?<?php echo time(); ?>"></script>
<script>
	jQuery(document).ready(function(){
		jQuery('#completeInstall').hide();
	});
</script>