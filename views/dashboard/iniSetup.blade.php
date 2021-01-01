
<div class="title"><div class="ico_setup_title"></div><h1>Initial Setup</h1></div>

<div class="max-container">
{{ Form::open(array('url' => 'iniSetup')) }}
	<div class="iniSetup-container">

		<div class="iniSetupTitle">Base Address</div>

		{{ Form::label('town', 'Town') }}
		{{ Form::text('town') }}
		{{ $errors->first('town', '<span class="formerror">:message</span>') }}

		{{ Form::label('county', 'County') }}
		{{ Form::text('county') }}
		{{ $errors->first('county', '<span class="formerror">:message</span>') }}

		{{ Form::label('country', 'Country') }}
		{{ Form::text('country') }}
		{{ $errors->first('country', '<span class="formerror">:message</span>') }}

		<div class="iniSetupTitle">Scheme Prefix</div>
		{{ Form::text('scheme_prefix') }}
		{{ $errors->first('scheme_prefix', '<span class="formerror">:message</span>') }}


		<div class="clear"></div>
	</div>

	<div class="iniSetup-container">

		<div class="iniSetupTitle">Comms Types Used</div>

		{{ Form::customradio('commType', 'mbus', 'M-Bus', true) }}
		{{ Form::customradio('commType', 'bacnet', 'Bacnet') }}
		{{ Form::customradio('commType', 'modbus', 'Modbus') }}
		{{ Form::customradio('commType', 'anynet', 'Anynet') }}

		<div class="iniSetupTitle">Default Meter Baud Rate</div>
		{{ Form::text('meterBaudRate') }}
		{{ $errors->first('meterBaudRate', '<span class="formerror">:message</span>') }}


		<div class="clear"></div>
	</div>

	<div class="clear"></div>

	<div class="iniSetup-container">

		<div class="iniSetupTitle">Add Meter Details</div>

		{{ Form::label('md_make', 'Make') }}
		{{ Form::text('md_make') }}
		{{ $errors->first('md_make', '<span class="formerror">:message</span>') }}

		{{ Form::label('md_model', 'Model') }}
		{{ Form::text('md_model') }}
		{{ $errors->first('md_model', '<span class="formerror">:message</span>') }}

		{{ Form::label('md_manufacturer', 'Manufacturer') }}
		{{ Form::text('md_manufacturer') }}
		{{ $errors->first('md_manufacturer', '<span class="formerror">:message</span>') }}

		<div class="clear"></div>
	</div>


	<div class="iniSetup-container">

		<div class="iniSetupTitle">Add HIU Details</div>

		{{ Form::label('hd_make', 'Make') }}
		{{ Form::text('hd_make') }}
		{{ $errors->first('hd_make', '<span class="formerror">:message</span>') }}

		{{ Form::label('hd_model', 'Model') }}
		{{ Form::text('hd_model') }}
		{{ $errors->first('hd_model', '<span class="formerror">:message</span>') }}

		{{ Form::label('hd_manufacturer', 'Manufacturer') }}
		{{ Form::text('hd_manufacturer') }}
		{{ $errors->first('hd_manufacturer', '<span class="formerror">:message</span>') }}

		<div class="clear"></div>
	</div>

	<div class="iniSetup-container">

		<div class="iniSetupTitle">Add Valve Details</div>

		{{ Form::label('vd_make', 'Make') }}
		{{ Form::text('vd_make') }}
		{{ $errors->first('vd_make', '<span class="formerror">:message</span>') }}

		{{ Form::label('vd_model', 'Model') }}
		{{ Form::text('vd_model') }}
		{{ $errors->first('vd_model', '<span class="formerror">:message</span>') }}

		{{ Form::label('vd_manufacturer', 'Manufacturer') }}
		{{ Form::text('vd_manufacturer') }}
		{{ $errors->first('vd_manufacturer', '<span class="formerror">:message</span>') }}

		<div class="clear"></div>
	</div>

	@if($message = Session::get('iniSetupError'))
        <span class="formerror">{{ $message }}</span>
    @endif

	<div class="clear"></div>
	
	{{ Form::submit('Create initial account', array('class' => 'btn_iniSetup', 'id' => 'iniSetup')) }}
	{{ Form::close() }}

</div>