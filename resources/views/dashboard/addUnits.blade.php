<div class="title"><div class="@if ($is_ev) ico_plus_title_red @else ico_plus_title @endif"></div><h1>Add @if ($is_ev) EV @endif Unit</h1></div>

<div class="clear"></div>

<div id="mbussim" class="max-container">
	{!! Form::open(array('url' => 'prepago_installer/add-unit')) !!}
	
	{!! Form::hidden('is_ev', $is_ev) !!}

	<div class="iniSetup-container">
		
		
		@if ($is_ev)
			<div class="iniSetupTitle">EV Recharge Station</div>
			{!! Form::label('ev_rs_address', 'Address') !!}
			{!! Form::text('ev_rs_address') !!}
			{!! $errors->first('ev_rs_address', '<span class="formerror">:message</span>') !!}

			{!! Form::label('ev_rs_code', 'Code') !!}
			{!! Form::text('ev_rs_code') !!}
			{!! $errors->first('ev_rs_code', '<span class="formerror">:message</span>') !!}
		@else
			<div class="iniSetupTitle">Address</div>

			{!! Form::label('house_apartment_number', 'House/Apartment Number') !!}
			{!! Form::text('house_apartment_number') !!}
			{!! $errors->first('house_apartment_number', '<span class="formerror">:message</span>') !!}

			{!! Form::label('building_street_name', 'Building/Street Name') !!}
			{!! Form::text('building_street_name', $last_installed_unit->street1) !!}
			{!! $errors->first('building_street_name', '<span class="formerror">:message</span>') !!}
			
			{!! Form::label('street2', 'Street2') !!}
			{!! Form::text('street2', $schemeStreet2) !!}
			{!! $errors->first('street2', '<span class="formerror">:message</span>') !!}
		@endif	
		
		@if (count($dataLoggers) > 1)
			{!! Form::label('dataLogger', 'Data Loggers') !!}
			{!! Form::select('dataLogger', (['' => ''] + $dataLoggers)) !!}
			{!! $errors->first('dataLogger', '<span class="formerror">:message</span>') !!}
		@else
			{!! Form::hidden('dataLogger', count($dataLoggers) > 0 ? key($dataLoggers) : 0) !!}
		@endif
		
	</div>
	<div id="mbussim" class="iniSetup-container">
	
		{!! Form::label('scu_type', 'SCU TYPE', ['style' => 'font-weight: 700']) !!}
		
		{!! Form::select('scu_type', [/*'' => 'SELECT', */'m' => 'M-Bus Meter + M-Bus Relay' /*, 'a' => 'M-Bus Meter + SIM Relay', 'd' => 'SIM Meter + SIM Relay'*/]) !!}
		
		{!! $errors->first('scu_type', '<span class="formerror">:message</span>') !!}

		<div class="iniSetupTitle">SCU</div>

		{!! Form::label('scu_number', 'SCU Number') !!}
		{!! Form::text('scu_number', '00000000', ['maxlength' => '8']) !!}
		{!! $errors->first('scu_number', '<span class="formerror">:message</span>') !!}
		{!! Form::hidden('iccid', '', ['id' => 'iccid']) !!}
		
		<!--
		{!! Form::label('iccid', 'ICCID (long sim number)') !!}
		{!! Form::text('iccid', '', ['id' => 'iccid']) !!}
		{!! $errors->first('iccid', '<span class="formerror">:message</span>') !!}
		-->
			
	</div>
	
		
	{!! Form::hidden('service_control_port', 1) !!}
	{!! Form::hidden('heat_control_port', -1) !!}
	
	<div id="mbussim" class="iniSetup-container">
		
		<div class="iniSetupTitle">Translation Insertion</div>
		<br/>
		
		SCU<div class="iniSCU"></div>{!! Form::hidden('iniSCU', 00000000) !!}
		<br/>
		Meter<div class="iniMeter"></div>{!! Form::hidden('iniMeter', 00000000) !!}
		
	</div>
	
	<style>
	.loading {    
		background-color: #ffffff;
		background-image: url("http://loadinggif.com/images/image-selection/3.gif");
		background-size: 25px 25px;
		background-position:right center;
		background-repeat: no-repeat;
	}
	</style>
	
	<script>
		$(function(){
			
			
			var iniSCU = $('.iniSCU');
			var iniMeter = $('.iniMeter');
			var iniSCU_i = $('input[name=iniSCU]');
			var iniMeter_i = $('input[name=iniMeter');
			
			iniSCU.html($('input[name=scu_number]').val());
			iniMeter.html($('input[name=meter_number]').val());
			
			var typingTimer;                
			var doneTypingInterval = 5000;

			function handleEight(cur_val, name_attr) {
				$.ajax({
						url: "{!! URL::to('prepago_installer/fetch_eight') !!}",
						dataType: "json",
						error: function(jqXHR, textStatus, errorThrown ) {
							
							console.log('An error occured\n' + errorThrown);
							
						}
				}).done(function(data){
					
					$('input').attr('class', '');
					
					if(name_attr == 'scu_number') {
						var full_scu_number = cur_val + "" + data.scu;
						iniSCU.html(full_scu_number);
						iniSCU_i.val(full_scu_number);
					}
					
					if(name_attr == 'meter_number') {
						var full_meter_number = cur_val + "" + data.meter;
						iniMeter.html(full_meter_number);
						iniMeter_i.val(full_meter_number);
						$(this).toggleClass('loading');
					}
					
				});
			}
			
			$('input[name=scu_number], input[name=meter_number]').on('keyup', function(){
				
				$(this).attr('class', 'loading');
				var name_attr = $(this).attr('name');
				var cur_val = $('input[name=' + name_attr + ']').val();
				
				clearTimeout(typingTimer);
				typingTimer = setTimeout(handleEight(cur_val, name_attr), doneTypingInterval);
  
			});
			
			$('input[name=scu_number], input[name=meter_number]').on('keydown', function(){
				
				clearTimeout(typingTimer);
  
			});
			
		});
	</script>	
	<!--
	<div id="mbussim" class="iniSetup-container">
		
		<div class="iniSetupTitle">Ports</div>

		{!! Form::label('service_control_port', 'Service Control Port') !!}
		{!! Form::select('service_control_port', array( '' => '', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7' ), '', ['id' => 'service_control_port']) !!}
		{!! $errors->first('service_control_port', '<span class="formerror">:message</span>') !!}

		{!! Form::label('heat_control_port', 'Heat Control Port ') !!}
		{!! Form::select('heat_control_port', array( '' => '', '-1' => 'None', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ), '', ['id' => 'heat_control_port']) !!}
		{!! $errors->first('heat_control_port', '<span class="formerror">:message</span>') !!}

	</div>
	-->
	
	<div class="clear"></div>
	<div id="mbussim" class="iniSetup-container">

		<div class="iniSetupTitle">Meter Details</div>
	
		{!! Form::label('meter_number', 'Meter Number') !!}
		{!! Form::text('meter_number', '00000000', ['maxlength' => '8']) !!}
		{!! $errors->first('meter_number', '<span class="formerror">:message</span>') !!}
		
		<!--
		{!! Form::label('meter_number2', 'Meter Number 2 (Optional)') !!}
		{!! Form::text('meter_number2') !!}
		{!! $errors->first('meter_number2', '<span class="formerror">:message</span>') !!}
		-->
		
		{!! Form::label('baud_rate', 'Meter Baud Rate') !!}
		{!! Form::text('baud_rate', $last_installed_unit->meter_baud_rate) !!}
		{!! $errors->first('baud_rate', '<span class="formerror">:message</span>') !!}

		{!! Form::label('readings_per_day', 'Readings per day') !!}
		{!! Form::select('readings_per_day', array( '' => '', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '6' => '6', '8' => '8', '12' => '12', '16' => '16', '24' => '24', '48' => '48' ), $last_installed_unit->readings_per_day) !!}
		{!! $errors->first('readings_per_day', '<span class="formerror">:message</span>') !!}

		{!! Form::label('md_make', 'Make') !!}
		{!! Form::text('md_make', $last_installed_unit->meter_make) !!}
		{!! $errors->first('md_make', '<span class="formerror">:message</span>') !!}

		{!! Form::label('md_model', 'Model') !!}
		{!! Form::text('md_model', $last_installed_unit->meter_model) !!}
		{!! $errors->first('md_model', '<span class="formerror">:message</span>') !!}

		{!! Form::label('md_manufacturer', 'Manufacturer') !!}
		{!! Form::text('md_manufacturer', $last_installed_unit->meter_manufacturer) !!}
		{!! $errors->first('md_manufacturer', '<span class="formerror">:message</span>') !!}

		{!! Form::customcheckbox('is_boiler_meter', 'default', 'Is Boiler Room Meter ') !!}
		{!! $errors->first('is_boiler_meter', '<span class="formerror">:message</span>') !!}
		
		{!! Form::customcheckbox('is_bill_paid_customer', 'default', 'Is Bill Paid Customer ') !!}
		{!! $errors->first('is_bill_paid_customer', '<span class="formerror">:message</span>') !!}

	</div>
	{!! Form::hidden('hd_make', $last_installed_unit->HIU_make) !!}
	{!! Form::hidden('hd_manufacturer', $last_installed_unit->HIU_manufacturer) !!}
	{!! Form::hidden('hd_model', $last_installed_unit->HIU_model) !!}
	<!--
	<div id="mbussim" class="iniSetup-container">

		<div class="iniSetupTitle">HIU Details</div>

		{!! Form::label('hd_make', 'Make') !!}
		{!! Form::text('hd_make', $last_installed_unit->HIU_make) !!}
		{!! $errors->first('hd_make', '<span class="formerror">:message</span>') !!}

		{!! Form::label('hd_model', 'Model') !!}
		{!! Form::text('hd_model', $last_installed_unit->HIU_model) !!}
		{!! $errors->first('hd_model', '<span class="formerror">:message</span>') !!}

		{!! Form::label('hd_manufacturer', 'Manufacturer') !!}
		{!! Form::text('hd_manufacturer', $last_installed_unit->HIU_manufacturer) !!}
		{!! $errors->first('hd_manufacturer', '<span class="formerror">:message</span>') !!}

	</div>
	-->
	<div id="mbussim" class="iniSetup-container">

		<div class="iniSetupTitle">Valve Details</div>

		{!! Form::label('vd_make', 'Make') !!}
		{!! Form::text('vd_make', $last_installed_unit->valve_make) !!}
		{!! $errors->first('vd_make', '<span class="formerror">:message</span>') !!}

		{!! Form::label('vd_model', 'Model') !!}
		{!! Form::text('vd_model', $last_installed_unit->valve_model) !!}
		{!! $errors->first('vd_model', '<span class="formerror">:message</span>') !!}

		{!! Form::label('vd_manufacturer', 'Manufacturer') !!}
		{!! Form::text('vd_manufacturer', $last_installed_unit->valve_manufacturer) !!}
		{!! $errors->first('vd_manufacturer', '<span class="formerror">:message</span>') !!}

		<div class="clear"></div>

	</div>

	@if($message = Session::get('unitAddError'))
        <span class="formerror">{!! $message !!}</span>
    @endif

	<div class="clear"></div>

	{!! Form::submit('Add Unit', array('class' => 'btn_iniSetup')) !!}
	{!! Form::close() !!}
</div>


<script>

	jQuery(document).ready(function(){

		if ($("#scu_type").val() === 'm')
		{
			$("#iccid").val("").attr('disabled', true);
			$("#service_control_port").attr('disabled', true).val("1");
			$("#heat_control_port").attr('disabled', true).val('-1');
		}

		$("#scu_type").change(function()
		{
			if ($("#scu_type").val() === 'm')
			{
				$("#iccid").val("").attr('disabled', true);
				$("#service_control_port").attr('disabled', true).val("1");
				$("#heat_control_port").attr('disabled', true).val('-1');
			}
			else
			{
				$("#iccid").attr('disabled', false);
				$("#service_control_port").attr('disabled', false);
				$("#heat_control_port").attr('disabled', false);
			}
		});
	
		jQuery('#service_control_port').change(function(){
			
			var selected = jQuery('#service_control_port').val();
			if(selected)
			{
				if(jQuery('#heat_control_port').val() == selected)
				{
					jQuery('#service_control_port').val('');
					alert('Heat control port is already using this port. Please choose another port.');
				}
			}

		});

		jQuery('#heat_control_port').change(function(){
			
			var selected = jQuery('#heat_control_port').val();
			if(selected)
			{
				if(jQuery('#service_control_port').val() == selected)
				{
					jQuery('#heat_control_port').val('');
					alert('Service control port is already using this port. Please choose another port.');
				}
			}

		});

	});


</script>
