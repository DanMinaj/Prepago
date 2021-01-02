<div class="title"><div class="ico_plus_title"></div><h1>Add Unit</h1></div>

<div class="clear"></div>

<div id="mbussim" class="max-container">
	{!! Form::open(array('url' => 'prepago_installer/add-unit')) !!}

	<div class="iniSetup-container">

		<div class="iniSetupTitle">Address</div>

		{!! Form::label('house_apartment_number', 'House/Apartment Number') !!}
		{!! Form::text('house_apartment_number') !!}
		{!! $errors->first('house_apartment_number', '<span class="formerror">:message</span>') !!}

		{!! Form::label('building_street_name', 'Building/Street Name') !!}
		{!! Form::text('building_street_name') !!}
		{!! $errors->first('building_street_name', '<span class="formerror">:message</span>') !!}
		
		{!! Form::label('street2', 'Street2') !!}
		{!! Form::text('street2') !!}
		{!! $errors->first('street2', '<span class="formerror">:message</span>') !!}
		
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
		{!! Form::select('scu_type', ['' => 'SELECT', 'm' => 'M-Bus Meter + M-Bus Relay', 'a' => 'M-Bus Meter + SIM Relay', 'd' => 'SIM Meter + SIM Relay']) !!}
		{!! $errors->first('scu_type', '<span class="formerror">:message</span>') !!}

		<div class="iniSetupTitle">SCU</div>

		{!! Form::label('scu_number', 'SCU Number') !!}
		{!! Form::text('scu_number', '00000000') !!}
		{!! $errors->first('scu_number', '<span class="formerror">:message</span>') !!}

		{!! Form::label('iccid', 'ICCID (long sim number)') !!}
		{!! Form::text('iccid', '', ['id' => 'iccid']) !!}
		{!! $errors->first('iccid', '<span class="formerror">:message</span>') !!}

	</div>
	<div id="mbussim" class="iniSetup-container">

		<div class="iniSetupTitle">Ports</div>

		{!! Form::label('service_control_port', 'Service Control Port') !!}
		{!! Form::select('service_control_port', array( '' => '', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7' ), '', ['id' => 'service_control_port']) !!}
		{!! $errors->first('service_control_port', '<span class="formerror">:message</span>') !!}

		{!! Form::label('heat_control_port', 'Heat Control Port ') !!}
		{!! Form::select('heat_control_port', array( '' => '', '-1' => 'None', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ), '', ['id' => 'heat_control_port']) !!}
		{!! $errors->first('heat_control_port', '<span class="formerror">:message</span>') !!}

	</div>
	<div class="clear"></div>
	<div id="mbussim" class="iniSetup-container">

		<div class="iniSetupTitle">Meter Details</div>

		{!! Form::label('meter_number', 'Meter Number') !!}
		{!! Form::text('meter_number') !!}
		{!! $errors->first('meter_number', '<span class="formerror">:message</span>') !!}
		
		{!! Form::label('meter_number2', 'Meter Number 2 (Optional)') !!}
		{!! Form::text('meter_number2') !!}
		{!! $errors->first('meter_number2', '<span class="formerror">:message</span>') !!}

		{!! Form::label('baud_rate', 'Meter Baud Rate') !!}
		{!! Form::text('baud_rate') !!}
		{!! $errors->first('baud_rate', '<span class="formerror">:message</span>') !!}

		{!! Form::label('readings_per_day', 'Readings per day') !!}
		{!! Form::select('readings_per_day', array( '' => '', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '6' => '6', '8' => '8', '12' => '12', '16' => '16', '24' => '24', '48' => '48' )) !!}
		{!! $errors->first('readings_per_day', '<span class="formerror">:message</span>') !!}

		{!! Form::label('md_make', 'Make') !!}
		{!! Form::text('md_make') !!}
		{!! $errors->first('md_make', '<span class="formerror">:message</span>') !!}

		{!! Form::label('md_model', 'Model') !!}
		{!! Form::text('md_model') !!}
		{!! $errors->first('md_model', '<span class="formerror">:message</span>') !!}

		{!! Form::label('md_manufacturer', 'Manufacturer') !!}
		{!! Form::text('md_manufacturer') !!}
		{!! $errors->first('md_manufacturer', '<span class="formerror">:message</span>') !!}

		{!! Form::customcheckbox('is_boiler_meter', 'default', 'Is Boiler Room Meter ') !!}
		{!! $errors->first('is_boiler_meter', '<span class="formerror">:message</span>') !!}
		
		{!! Form::customcheckbox('is_bill_paid_customer', 'default', 'Is Bill Paid Customer ') !!}
		{!! $errors->first('is_bill_paid_customer', '<span class="formerror">:message</span>') !!}

	</div>
	<div id="mbussim" class="iniSetup-container">

		<div class="iniSetupTitle">HIU Details</div>

		{!! Form::label('hd_make', 'Make') !!}
		{!! Form::text('hd_make') !!}
		{!! $errors->first('hd_make', '<span class="formerror">:message</span>') !!}

		{!! Form::label('hd_model', 'Model') !!}
		{!! Form::text('hd_model') !!}
		{!! $errors->first('hd_model', '<span class="formerror">:message</span>') !!}

		{!! Form::label('hd_manufacturer', 'Manufacturer') !!}
		{!! Form::text('hd_manufacturer') !!}
		{!! $errors->first('hd_manufacturer', '<span class="formerror">:message</span>') !!}

	</div>
	<div id="mbussim" class="iniSetup-container">

		<div class="iniSetupTitle">Valve Details</div>

		{!! Form::label('vd_make', 'Make') !!}
		{!! Form::text('vd_make') !!}
		{!! $errors->first('vd_make', '<span class="formerror">:message</span>') !!}

		{!! Form::label('vd_model', 'Model') !!}
		{!! Form::text('vd_model') !!}
		{!! $errors->first('vd_model', '<span class="formerror">:message</span>') !!}

		{!! Form::label('vd_manufacturer', 'Manufacturer') !!}
		{!! Form::text('vd_manufacturer') !!}
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
