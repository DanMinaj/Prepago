
<div class="title"><div class="ico_settings_title"></div><h1>Settings</h1></div>


<div class="iniSetup-container">

	<div class="iniSetupTitle">Meters Installed</div>

	<div class="metercount">{{ $installed_units->count() }} / {{ $settings->maximum_physical_address }}</div>

	<div class="clear"></div>
</div>

<div class="clear"></div>

@if($message = Session::get('settingsError'))
    <span class="formerror">{{ $message }}</span>
@endif

<div class="iniSetup-container">

	<div class="iniSetupTitle">Scheme Prefix</div>
	{{ Form::open(array('url' => 'change-scheme-prefix')) }}
	{{ Form::text('scheme_prefix', $settings->meter_prefix) }}
	{{ $errors->first('scheme_prefix', '<span class="formerror">:message</span>') }}
	{{ Form::submit('Change', array('class' => 'btn_iniSetup')) }}
	{{ Form::close() }}
	
	<div class="clear"></div>
</div>

<div class="iniSetup-container">

	<div class="iniSetupTitle">Adjust Maximum Meters</div>

	{{ Form::open(array('url' => 'change-max-meter-count')) }}
	{{ Form::text('max_meter_count', $settings->maximum_physical_address) }}
	{{ $errors->first('max_meter_count', '<span class="formerror">:message</span>') }}
	{{ Form::submit('Change', array('class' => 'btn_iniSetup')) }}
	{{ Form::close() }}

	<div class="clear"></div>
</div>

<div class="iniSetup-container">

	<div class="iniSetupTitle">Baud Rate</div>

	{{ Form::open(array('url' => 'change-baud-rate')) }}
	{{ Form::text('baud_rate', $settings->base_baud_rate) }}
	{{ $errors->first('baud_rate', '<span class="formerror">:message</span>') }}
	{{ Form::submit('Change', array('class' => 'btn_iniSetup')) }}
	{{ Form::close() }}

	<div class="clear"></div>
</div>

<div class="iniSetup-container">

	<div class="iniSetupTitle">Base Address</div>

	{{ Form::label('town', 'Town') }}
	{{ Form::text('town', $settings->base_town) }}

	{{ Form::label('county', 'County') }}
	{{ Form::text('county', $settings->base_county) }}

	{{ Form::label('country', 'Country') }}
	{{ Form::text('country', $settings->base_country) }}

	<button class="btn_setup">Change</button>

	<div class="clear"></div>
</div>

<div class="iniSetup-container">

	<div class="iniSetupTitle">Username &amp; Password</div>

	{{ Form::label('username', 'Username') }}
	{{ Form::text('username', Auth::user()->username) }}

	{{ Form::label('password', 'Password') }}
	{{ Form::text('password') }}

	{{ Form::label('c_password', 'Confirm Password') }}
	{{ Form::text('c_password') }}

	<button class="btn_setup">Change</button>

	<div class="clear"></div>
</div>

<div class="iniSetup-container">

	<div class="iniSetupTitle">Add new user</div>

	{{ Form::label('username', 'Username') }}
	{{ Form::text('username') }}

	{{ Form::label('password', 'Password') }}
	{{ Form::text('password') }}

	{{ Form::label('c_password', 'Confirm Password') }}
	{{ Form::text('c_password') }}

	<button class="btn_setup">Add User</button>

	<div class="clear"></div>
</div>
	

	<div class="clear"></div>
