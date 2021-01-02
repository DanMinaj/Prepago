
@if ($message = Session::get('errorMessage'))
	<div style="color: #A94442; margin-top: 15px; padding: 10px; background-color: #F2DEDE; font-weight: bold">{{ $message }}</div>
@endif

@if ($message = Session::get('successMessage'))
	<div style="color: #69763D; margin-top: 15px; padding: 10px; background-color: #DFF0D8; font-weight: bold">{{ $message }}</div>
@endif

<div class="title"><div class="ico_tools_title"></div><h1>Unit Test</h1></div>

<div class="iniSetup-container">

	<div class="iniSetupTitle">Installation:</div>
	
	@if($unit['installation_confirmed'] == 1)
	<div class="test_msg">Confirmed</div>
	@else
	<div class="test_msg">Unsuccessful</div>
	@endif
	<div class="clear"></div>
</div>

<div id="meter_read_test" class="iniSetup-container">

	<div class="iniSetupTitle">Meter read test</div>

	<div class="test_msg"></div>
	
	<button class="btn_setup" onclick="meter_read_test()">Test</button>

	<div class="clear"></div>
</div>

<div id="service_control_test" class="iniSetup-container">

	<div class="iniSetupTitle">Service Control Port</div>

	<div class="test_msg"></div>
	
	<button class="btn_setup_other" onclick="service_control_test_on()">Test On</button>
	<button class="btn_setup_other" onclick="service_control_test_off()">Test Off</button>

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

	{{ Form::open(array('url' => 'prepago_installer/complete-install')) }}
	{{ Form::hidden('unitID', $unitID) }}
	{{ Form::submit('Mark as complete', array('class' => 'btn_setup')) }}
	{{ Form::close() }}

	<div class="clear"></div>

</div>

<input type="hidden" id="base" value="{{ URL::to('prepago_installer') }}">
<input type="hidden" id="unitID" value="{{ $unitID }}">
<input type="hidden" id="meter_read_test_success" value="0">
<input type="hidden" id="service_control_teston_success" value="0">
<input type="hidden" id="service_control_testoff_success" value="0">

{{ HTML::script('resources/js/installer.js') }}
<script>
	jQuery(document).ready(function(){
		// jQuery('.btn_setup').hide();
		jQuery('#completeInstall').hide();

		// meter_read_test();
		// service_control_test();
		// heat_control_test();
	});
</script>